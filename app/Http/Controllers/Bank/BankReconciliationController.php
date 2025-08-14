<?php

namespace App\Http\Controllers\Bank;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BankDetail;
use App\Models\BankReconciliation\BankStatement;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\Organization;
use App\Models\PaymentVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;
class BankReconciliationController extends Controller
{
    public function index(Request $request,$id){

        $authUser = Helper::getAuthenticatedUser();
        $authOrganization = Organization::find($authUser->organization_id);
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        $organizationId = $authOrganization?->id;
        
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        } else {
            $fyear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $fyear['start_date'];
            $endDate = $fyear['end_date'];
        }

        $bank = BankDetail::with([
            'ledger' => function($q){
                $q->select('id','name');
            },
            'bankInfo' => function($q){
                $q->select('id','bank_name');
            }
        ])->find($id);

        $partyItemSubquery = $this->partyItemSubquery($startDate, $endDate, $organizationId);
        $vouchers = Voucher::join('erp_item_details','erp_item_details.voucher_id','=','erp_vouchers.id')
                    ->join('erp_payment_vouchers','erp_payment_vouchers.id','=','erp_vouchers.reference_doc_id')
                    ->join('erp_books','erp_books.id','=','erp_vouchers.book_id')
                    ->leftJoinSub($partyItemSubquery, 'party_details', function($join) {
                        $join->on('erp_vouchers.id', '=', 'party_details.voucher_id');
                    })
                    ->whereNull('erp_item_details.statement_uid')
                    ->whereNull('erp_item_details.bank_date')
                    ->where('erp_vouchers.organization_id', $organizationId)
                    ->whereDate('erp_vouchers.document_date', '<' ,$endDate)
                    // ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
                    ->whereIn('erp_vouchers.approvalStatus',['approved','approval_not_required'])
                    ->whereIn('erp_vouchers.reference_service',['receipts','payments'])
                    ->where('erp_item_details.ledger_parent_id',$bank->ledger_group_id)
                    ->where('erp_item_details.ledger_id', $bank->ledger_id)
                    ->when($request->has('search') && $request->search != '', function($query) use ($request) {
                        $search = $request->search;
                        self::voucherFilter($query,$search);
                    })
                    ->select(
                        'erp_vouchers.voucher_no',
                        'erp_vouchers.voucher_name',
                        'erp_vouchers.reference_service',
                        'erp_vouchers.document_date',
                        'erp_item_details.credit_amt_org',
                        'erp_item_details.debit_amt_org',
                        'erp_books.book_code',
                        'erp_payment_vouchers.payment_mode',
                        'erp_payment_vouchers.payment_date',
                        'erp_payment_vouchers.reference_no',
                        'erp_item_details.id',
                        'party_details.name as party_name',
                    )
                    ->get();

        // Get Opening Balance before start date
        $openingQuery = $this->openingBalance($bank->ledger_id,$bank->ledger_group_id,$startDate,$organizationId);
        $debitQuery = $this->debitQuery($bank->ledger_id,$bank->ledger_group_id,$startDate,$endDate,$organizationId);
        $creditQuery = $this->creditQuery($bank->ledger_id,$bank->ledger_group_id,$startDate,$endDate,$organizationId);
        $closingAmt = ($openingQuery->total_opening_debit - $openingQuery->total_opening_credit) + ($debitQuery->sum_debit_amt + $creditQuery->sum_credit_amt);

        // Calculate total of unmatched vouchers
        $unreflectedDr = $vouchers->sum('debit_amt_org');
        $unreflectedCr = $vouchers->sum('credit_amt_org');

        // Compute bank balance
        $statement = BankStatement::where('ledger_id', $bank->ledger_id)
                    ->where('ledger_group_id',$bank->ledger_group_id)
                    ->whereDate('date',$endDate)
                    ->orderBy('date','DESC')
                    ->first();
        $bankBalance = $statement ? $statement->balance : 0;

        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        return view('bank-reconciliation.reconciliation.index',[
            'bank' => $bank,
            'vouchers' => $vouchers,
            'dateRange' => $dateRange,
            'companyBookBalance' => $closingAmt,
            'unreflectedDr' => $unreflectedDr,
            'unreflectedCr' => $unreflectedCr,
            'bankBalance' => $bankBalance,
        ]);
    }

    private function openingBalance($ledgerId,$ledgerParentId,$startDate,$organizationId){
        $openingQuery = ItemDetail::select(
                DB::raw('SUM(debit_amt_org) as total_opening_debit'),
                DB::raw('SUM(credit_amt_org) as total_opening_credit')
            )
            ->join('erp_vouchers', 'erp_item_details.voucher_id', '=', 'erp_vouchers.id')
            ->where('erp_item_details.ledger_parent_id', $ledgerParentId)
            ->whereIn('erp_vouchers.approvalStatus', ['approved', 'approval_not_required'])
            ->where('erp_vouchers.document_date', '<', $startDate)
            ->where(function ($q) use ($organizationId) {
                $q->where('erp_vouchers.organization_id', $organizationId);
            })
            ->where('ledger_id',$ledgerId)
            ->first();

            return $openingQuery;
    }

    private function debitQuery($ledgerId,$ledgerParentId,$startDate,$endDate,$organizationId){
        $debitquery = ItemDetail::select(
                DB::raw('SUM(erp_item_details.debit_amt_org) as sum_debit_amt')
            )
            ->join('erp_vouchers', 'erp_item_details.voucher_id', '=', 'erp_vouchers.id')
            ->where('erp_item_details.ledger_parent_id', $ledgerParentId)
            ->whereIn('erp_vouchers.approvalStatus', ['approved', 'approval_not_required'])
            ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('erp_vouchers.organization_id')->orWhere('erp_vouchers.organization_id', $organizationId);
            })
            ->where('ledger_id',$ledgerId)
            ->first();

            return $debitquery;
    }

    private function creditQuery($ledgerId,$ledgerParentId,$startDate,$endDate,$organizationId){
        $creditquery = ItemDetail::select(
                DB::raw('SUM(erp_item_details.credit_amt_org) as sum_credit_amt')
            )
            ->join('erp_vouchers', 'erp_item_details.voucher_id', '=', 'erp_vouchers.id')
            ->where('erp_item_details.ledger_parent_id', $ledgerParentId)
            ->whereIn('erp_vouchers.approvalStatus', ['approved', 'approval_not_required'])
            ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('erp_vouchers.organization_id')->orWhere('erp_vouchers.organization_id', $organizationId);
            })
            ->where('ledger_id',$ledgerId)
            ->first();

            return $creditquery;
    }

    private function voucherFilter($query,$search){
        $query->where(function($q) use ($search) {
            $q->where('voucher_no', 'like', '%' . $search . '%')
                ->orWhere('voucher_name', 'like', '%' . $search . '%');
        });
        return $query;
    }

    public function storeBankDates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_date' => 'required|array',
        ]);

        $validator->after(function ($validator) use ($request) {
            $hasAtLeastOneDate = collect($request->bank_date)->filter(function ($date) {
                return !empty($date);
            })->isEmpty();

            if ($hasAtLeastOneDate) {
                $validator->errors()->add('bank_date', 'Please enter at least one Bank Date.');
            }

            foreach ($request->bank_date as $voucherId => $bankDate) {
                if(!$bankDate){
                    continue;
                }

                $itemDetail = ItemDetail::join('erp_vouchers','erp_vouchers.id','=','erp_item_details.voucher_id')
                            ->join('erp_payment_vouchers','erp_payment_vouchers.id','=','erp_vouchers.reference_doc_id')
                            ->select(
                                'erp_item_details.credit_amt_org',
                                'erp_item_details.debit_amt_org',
                                'erp_payment_vouchers.reference_no'
                            )
                            ->find($voucherId);

                if (!$itemDetail) {
                    $validator->errors()->add('bank_date', "Invalid voucher ID: $voucherId");
                    continue;
                }

                $bankStatement = BankStatement::select('id','date','credit_amt','debit_amt')
                            ->where('date',$bankDate)
                            ->where('debit_amt',$itemDetail->credit_amt_org)
                            ->where('credit_amt',$itemDetail->debit_amt_org)
                            ->where('ref_no',$itemDetail->reference_no)
                            ->whereNull('matched')
                            ->first();
                if (!$bankStatement) {
                    $validator->errors()->add('bank_date', 'No bank statement found matching the given date.');
                }
                
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        foreach ($request->bank_date as $voucherId => $bankDate) {
            if (!$bankDate) {
                continue;
            }

            $itemDetail = ItemDetail::join('erp_vouchers','erp_vouchers.id','=','erp_item_details.voucher_id')
                        ->join('erp_payment_vouchers','erp_payment_vouchers.id','=','erp_vouchers.reference_doc_id')
                        ->select(
                            'erp_item_details.credit_amt_org',
                            'erp_item_details.debit_amt_org',
                            'erp_payment_vouchers.reference_no'
                        )
                        ->find($voucherId);
            if (!$itemDetail) {
                continue;
            }

            $bankStatement = BankStatement::select('id','date','credit_amt','debit_amt','uid')
                            ->where('date',$bankDate)
                            ->where('debit_amt',$itemDetail->credit_amt_org)
                            ->where('credit_amt',$itemDetail->debit_amt_org)
                            ->where('ref_no',$itemDetail->reference_no)
                            ->whereNull('matched')
                            ->first();

            $itemDetail->statement_uid = $bankStatement->uid;
            $itemDetail->bank_date = $bankDate;
            $itemDetail->save();
        }

        return [
            "data" => null,
            "message" => "Reconciliation saved successfully!"
        ];

    }

    private function partyItemSubquery($startDate, $endDate, $organizationId){
        $partyItemSubquery = ItemDetail::select(
                'erp_item_details.ledger_id',
                'erp_item_details.voucher_id',
                'erp_ledgers.name',
            )
            ->join('erp_ledgers','erp_ledgers.id','=','erp_item_details.ledger_id')
            ->join('erp_vouchers','erp_vouchers.id','=','erp_item_details.voucher_id')
            ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
            ->where('erp_vouchers.organization_id', $organizationId)
            ->where('erp_item_details.entry_type', 'party')
            ->groupBy('voucher_id');

            return $partyItemSubquery;
    }
}
