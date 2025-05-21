<?php

namespace App\Http\Controllers\Bank;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BankDetail;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\Organization;
use App\Models\PaymentVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        // $vouchers = PaymentVoucher::where('bank_id', $bank->bank_id)
        // ->where('ledger_id', $bank->ledger_id)
        // ->where('account_id', $bank->id)
        // ->where('ledger_group_id', $bank->ledger_group_id)
        // ->where('statement_uid', null)
        // ->whereDate('document_date', '>=', $startDate)
        // ->whereDate('document_date', '<=', $endDate)
        // ->get();

        // // Get Opening Balance before start date
        // $openingDr = PaymentVoucher::where('bank_id', $bank->bank_id)
        //     ->where('ledger_id', $bank->ledger_id)
        //     ->where('account_id', $bank->id)
        //     ->where('ledger_group_id', $bank->ledger_group_id)
        //     ->where('document_date', '<', $startDate)
        //     ->where('document_type', CommonHelper::RECEIPTS)
        //     ->sum('amount');

        // $openingCr = PaymentVoucher::where('bank_id', $bank->bank_id)
        //     ->where('ledger_id', $bank->ledger_id)
        //     ->where('account_id', $bank->id)
        //     ->where('ledger_group_id', $bank->ledger_group_id)
        //     ->where('document_date', '<', $startDate)
        //     ->where('document_type', CommonHelper::PAYMENTS)
        //     ->sum('amount');

        // $openingBalance = $openingDr - $openingCr;

        // // Total Receipts and Payments between the given date range
        // $totalDr = PaymentVoucher::where('bank_id', $bank->bank_id)
        //     ->where('ledger_id', $bank->ledger_id)
        //     ->where('account_id', $bank->id)
        //     ->where('ledger_group_id', $bank->ledger_group_id)
        //     ->whereDate('document_date', '>=', $startDate)
        //     ->whereDate('document_date', '<=', $endDate)
        //     ->where('document_type', CommonHelper::RECEIPTS)
        //     ->sum('amount');

        // $totalCr = PaymentVoucher::where('bank_id', $bank->bank_id)
        //     ->where('ledger_id', $bank->ledger_id)
        //     ->where('account_id', $bank->id)
        //     ->where('ledger_group_id', $bank->ledger_group_id)
        //     ->whereDate('document_date', '>=', $startDate)
        //     ->whereDate('document_date', '<=', $endDate)
        //     ->where('document_type', CommonHelper::PAYMENTS)
        //     ->sum('amount');

        // // Final Company Book Closing Balance
        // $companyBookBalance = $openingBalance + $totalDr - $totalCr;

        // // Calculate total of unmatched vouchers
        // $unreflectedDr = $vouchers->where('document_type', CommonHelper::RECEIPTS)->sum('amount');
        // $unreflectedCr = $vouchers->where('document_type', CommonHelper::PAYMENTS)->sum('amount');

        // // Compute bank balance
        // $bankBalance = $companyBookBalance - $unreflectedDr + $unreflectedCr;
        $vouchers = Voucher::join('erp_item_details','erp_item_details.voucher_id','=','erp_vouchers.id')
                    ->join('erp_payment_vouchers','erp_payment_vouchers.id','=','erp_vouchers.reference_doc_id')
                    ->join('erp_books','erp_books.id','=','erp_vouchers.book_id')
                    ->whereNull('erp_item_details.statement_uid')
                    ->whereNull('erp_item_details.bank_date')
                    ->where('erp_item_details.organization_id', $organizationId)
                    ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
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
                        'erp_payment_vouchers.reference_no',
                        'erp_item_details.id',
                    )
                    ->get();
                // dd($vouchers->toArray());

        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        return view('bank-reconciliation.reconciliation.index',[
            'bank' => $bank,
            'vouchers' => $vouchers,
            'dateRange' => $dateRange,
            // 'companyBookBalance' => $companyBookBalance,
            // 'unreflectedDr' => $unreflectedDr,
            // 'unreflectedCr' => $unreflectedCr,
            // 'bankBalance' => $bankBalance,
        ]);
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
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        foreach ($request->bank_date as $voucherId => $bankDate) {
            if ($bankDate) {
                ItemDetail::where('id', $voucherId)->update(['bank_date' => $bankDate]);
            }
        }

        return [
            "data" => null,
            "message" => "Reconciliation saved successfully!"
        ];

    }
}
