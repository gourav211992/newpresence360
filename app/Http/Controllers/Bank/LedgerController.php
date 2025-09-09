<?php

namespace App\Http\Controllers\Bank;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankDetail;
use App\Models\Group;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class LedgerController extends Controller
{
    public function index(Request $request){
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $authUser = Helper::getAuthenticatedUser();
        $authOrganization = Organization::find($authUser->organization_id);
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        $organizationId = $authOrganization?->id;
        // $mappedOrganizations = $authUser->access_rights_org;
        $mappedOrganizations = $authUser->organizations;


        $ledgers = Ledger::join('erp_bank_details','erp_bank_details.ledger_id','=','erp_ledgers.id')
                            ->select('erp_ledgers.id','erp_ledgers.name','erp_ledgers.status')->get();

        $date = $request->date ? $request->date : null;
        $dateRange = $this->getDateRange($date);

        $erpGroup = Group::select('id')->where('name','Bank Accounts')->where('status', 'active')->first();
        $bankAccountGroupId = $erpGroup->id ?? 0;

        $openingSubquery = $this->openingSubquery($bankAccountGroupId, $groupId, $dateRange['startDate'], $dateRange['endDate'], $companyId, $organizationId);
        $debitSubquery = $this->debitSubquery($bankAccountGroupId, $groupId, $dateRange['startDate'], $dateRange['endDate'], $companyId, $organizationId);
        $creditSubquery = $this->creditSubquery($bankAccountGroupId, $groupId, $dateRange['startDate'], $dateRange['endDate'], $companyId, $organizationId);

        $data = Ledger::join('erp_bank_details','erp_bank_details.ledger_id','=','erp_ledgers.id')
                ->join('erp_banks','erp_banks.id','=','erp_bank_details.bank_id')
                ->join('erp_item_details','erp_item_details.ledger_id','=','erp_ledgers.id')
                ->join('erp_vouchers','erp_vouchers.id','=','erp_item_details.voucher_id')
                ->leftJoinSub($openingSubquery, 'opening_balance', function($join) {
                    $join->on('erp_ledgers.id', '=', 'opening_balance.ledger_id');
                })
                ->leftJoinSub($debitSubquery, 'debit_summary', function($join) {
                    $join->on('erp_ledgers.id', '=', 'debit_summary.ledger_id');
                })
                ->leftJoinSub($creditSubquery, 'credit_summary', function($join) {
                    $join->on('erp_ledgers.id', '=', 'credit_summary.ledger_id');
                })
                ->where(function ($query) use ($bankAccountGroupId) {
                    $query->whereJsonContains('erp_ledgers.ledger_group_id', (string) $bankAccountGroupId)
                        ->orWhere('erp_ledgers.ledger_group_id', $bankAccountGroupId);
                })
                ->where('erp_ledgers.status',1)
                ->whereIn('erp_vouchers.approvalStatus',['approved','approval_not_required'])
                ->whereIn('erp_vouchers.reference_service',['receipts','payments'])
                ->whereDate('erp_vouchers.document_date', '>=', $dateRange['startDate'])
                ->whereDate('erp_vouchers.document_date', '<=', $dateRange['endDate'])
                ->where('erp_ledgers.group_id', $groupId)
                ->when($companyId, function ($query) use ($companyId) {
                    $query->where(function($q) use ($companyId) {
                        $q->whereNull('erp_ledgers.company_id')
                        ->orWhere('erp_ledgers.company_id', $companyId);
                    });
                })
                ->when($request->has('search'), function($query) use ($request) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('erp_banks.bank_name', 'like', '%' . $search . '%')
                            ->orWhere('erp_bank_details.account_number', 'like', '%' . $search . '%')
                            ->orWhere('erp_ledgers.name', 'like', '%' . $search . '%');
                    });
                })
                ->when($request->has('organization_id'), function($query) use ($request) {
                    $query->where('erp_ledgers.organization_id', $request->organization_id);
                }, function($query) use ($organizationId) {
                    $query->where(function ($q) use ($organizationId) {
                        $q->whereNull('erp_ledgers.organization_id')
                        ->orWhere('erp_ledgers.organization_id', $organizationId);
                    });
                })
                ->when($request->has('ledger_id') && $request->ledger_id != '', function($query) use ($request) {
                    $query->where('erp_ledgers.id', $request->ledger_id);
                })
                ->groupBy(
                    'erp_ledgers.id',
                    'erp_ledgers.name',
                    'erp_banks.bank_name',
                    'erp_bank_details.account_number'
                )
                ->select(
                    'erp_ledgers.id as ledger_id',
                    'erp_ledgers.name',
                    'erp_banks.bank_name',
                    'erp_bank_details.account_number',
                    'erp_bank_details.id as account_id',
                    'debit_summary.sum_debit_amt as debit_amount',
                    'credit_summary.sum_credit_amt as credit_amount',
                    DB::raw('
                        (COALESCE(opening_balance.total_opening_debit, 0) - COALESCE(opening_balance.total_opening_credit, 0))
                        as opening
                    '),
                    DB::raw('
                        (
                            (COALESCE(opening_balance.total_opening_debit, 0) - COALESCE(opening_balance.total_opening_credit, 0))
                            +
                            (COALESCE(debit_summary.sum_debit_amt, 0) - COALESCE(credit_summary.sum_credit_amt, 0))
                        ) as closing
                    ')
                )
                ->paginate($length);


        $dateRange = \Carbon\Carbon::parse($dateRange['startDate'])->format('d-m-Y') . " to " . \Carbon\Carbon::parse($dateRange['endDate'])->format('d-m-Y');
        return view('bank-reconciliation.ledger.index',[
            'data' => $data,
            'mappedOrganizations' => $mappedOrganizations,
            'ledgers' => $ledgers,
            'authUser' => $authUser,
            'dateRange' => $dateRange,
        ]);
    }

    private function debitSubQuery($ledgerParentId,$groupId,$startDate, $endDate,$companyId,$organizationId){
        $debitSubquery = ItemDetail::select(
                'ledger_id',
                DB::raw('SUM(erp_item_details.debit_amt_org) as sum_debit_amt')
            )
            ->join('erp_vouchers', 'erp_item_details.voucher_id', '=', 'erp_vouchers.id')
            ->where('erp_item_details.ledger_parent_id', $ledgerParentId)
            ->where('erp_vouchers.group_id', $groupId)
            ->whereIn('erp_vouchers.approvalStatus', ['approved', 'approval_not_required'])
            ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
            ->where(function ($q) use ($companyId) {
                $q->whereNull('erp_vouchers.company_id')->orWhere('erp_vouchers.company_id', $companyId);
            })
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('erp_vouchers.organization_id')->orWhere('erp_vouchers.organization_id', $organizationId);
            })
            ->groupBy('ledger_id');

            return $debitSubquery;
    }

    private function openingSubquery($ledgerParentId,$groupId,$startDate, $endDate,$companyId,$organizationId){
        $openingSubquery = ItemDetail::select(
                'ledger_id',
                DB::raw('SUM(debit_amt_org) as total_opening_debit'),
                DB::raw('SUM(credit_amt_org) as total_opening_credit')
            )
            ->join('erp_vouchers', 'erp_item_details.voucher_id', '=', 'erp_vouchers.id')
            ->where('erp_item_details.ledger_parent_id', $ledgerParentId)
            ->where('erp_vouchers.group_id', $groupId)
            ->whereIn('erp_vouchers.approvalStatus', ['approved', 'approval_not_required'])
            ->where('erp_vouchers.document_date', '<', $startDate)
            ->where(function ($q) use ($companyId) {
                $q->whereNull('erp_vouchers.company_id')->orWhere('erp_vouchers.company_id', $companyId);
            })
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('erp_vouchers.organization_id')->orWhere('erp_vouchers.organization_id', $organizationId);
            })
            ->groupBy('ledger_id');

            return $openingSubquery;
    }

    private function creditSubquery($ledgerParentId,$groupId,$startDate, $endDate,$companyId,$organizationId){
        $creditSubquery = DB::table('erp_item_details')
            ->select(
                'ledger_id',
                DB::raw('SUM(erp_item_details.credit_amt_org) as sum_credit_amt')
            )
            ->join('erp_vouchers', 'erp_item_details.voucher_id', '=', 'erp_vouchers.id')
            ->where('erp_item_details.ledger_parent_id', $ledgerParentId)
            ->where('erp_vouchers.group_id', $groupId)
            ->whereIn('erp_vouchers.approvalStatus', ['approved', 'approval_not_required'])
            ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
            ->where(function ($q) use ($companyId) {
                $q->whereNull('erp_vouchers.company_id')->orWhere('erp_vouchers.company_id', $companyId);
            })
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('erp_vouchers.organization_id')->orWhere('erp_vouchers.organization_id', $organizationId);
            })
            ->groupBy('ledger_id');

            return $creditSubquery;
    }

    private function getDateRange($date){
        // Default date range
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        $startDate = $fyear['start_date'];
        $endDate = $fyear['end_date'];


        if ($date) {
            $dates = explode(' to ', $date);
            $startDate = Carbon::parse($dates[0])->format('Y-m-d');
            $endDate = Carbon::parse($dates[1])->format('Y-m-d');
        }
        // dd($startDate,$endDate);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

}
