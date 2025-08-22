<?php

namespace App\Http\Controllers;

use App\Exports\LedgerReportExport;
use App\Exports\TrialBalanceReportExport;
use App\Helpers\Helper;
use App\Models\Group;
use App\Helpers\InventoryHelper;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use Illuminate\Http\Request;
use App\Models\TrialBalance;
use App\Models\CostCenterOrgLocations;

use App\Models\Voucher;
use Auth;
use Carbon\Carbon;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;
use App\Models\CostGroup;
use App\Models\ErpStore;
use Maatwebsite\Excel\Facades\Excel;

class TrialBalanceController extends Controller
{
    public function updateLedgerOpening(Request $r)
    { ///// temp method to reset opening
        DB::table('erp_item_details')
            ->whereIn('ledger_id', Ledger::where('status', 1)->pluck('id'))
            ->orderBy('document_date')
            ->limit(Ledger::where('status', 1)->count())
            ->update(['opening' => 0, 'opening_type' => null]);
    }

    public function exportTrialBalanceReport(Request $r)
    {
        $dateRange = $r->date;
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
            $dateRange = $startDate . ' to ' . $endDate;
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
        }

        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }

        if ($r->group_id) {
            $groups = Group::where('id', $r->group_id) // Ensuring the specific group_id condition
                ->select('id', 'name')
                ->with('children.children') // Eager loading children and grandchildren
                ->get();
        } else {
            $groups = Helper::getGroupsQuery($organizations)
                ->whereNull('parent_group_id')
                ->select('id', 'name')
                ->with('children.children') // Ensures eager loading of children & grandchildren
                ->get();
        }
         $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }

        // Get Reserves & Surplus
        $profitLoss = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'trialBalance', $currency, $cost_center_ids,$r->location_id);
        $trialData = Helper::getGroupsData($groups, $startDate, $endDate, $organizations, $currency, $cost_center_ids,$r->location_id);
        $grandDebitTotal = 0;
        $grandCreditTotal = 0;
        $grandClosingTotal = 0;
        foreach ($trialData as $trialGroup) {

            $total_debit = $trialGroup->total_debit;
            $total_credit = $trialGroup->total_credit;

            $opening = $trialGroup->open;
            $opening_type = $opening < 0 ? 'Cr' : 'Dr';
            $closingText = '';
            $closing = $opening + ($total_debit - $total_credit);
            if ($closing != 0) {
                $closingText = $closing < 0 ? 'Cr' : 'Dr';
            }
            $closing = $closing > 0 ? $closing : -$closing;


            $grandDebitTotal = $grandDebitTotal + $total_debit;
            $grandClosingTotal += $opening + ($total_debit - $total_credit);


            if ($grandClosingTotal < 0) {
                $closing_type = "Cr";
            } else {
                $closing_type = "Dr";
            }
            $grandCreditTotal = $grandCreditTotal + $total_credit;
            $data[] = [$trialGroup->name, '', '', Helper::formatIndianNumber($opening) . $opening_type, Helper::formatIndianNumber($total_debit), Helper::formatIndianNumber($total_credit), Helper::formatIndianNumber($closing) . $closingText];

            if ($r->level == 2 || $r->level == 3) {
                $groupLedgers = Helper::getTrialBalanceGroupLedgers($trialGroup->id, $startDate, $endDate, $organizations, $currency, $cost_center_ids,$r->location_id);
                $groupLedgersData = $groupLedgers['data'];
                foreach ($groupLedgersData as $groupLedger) {
                    if ($groupLedgers['type'] == 'group') {
                        if ($groupLedger->name == "Reserves & Surplus") {
                            $data[] = ['', 'Reserves & Surplus', '', Helper::formatIndianNumber($profitLoss['closingFinal']) . $profitLoss['closing_type'], 0, 0, Helper::formatIndianNumber($profitLoss['closingFinal']) . $profitLoss['closing_type']];
                        } else {
                            $ledgerClosingText = '';
                            $ledgerClosing = $groupLedger->total_debit - $groupLedger->total_credit;
                            if ($ledgerClosing != 0) {
                                $ledgerClosingText = $ledgerClosing > 0 ? 'Dr' : 'Cr';
                            }
                            $data[] = ['', $groupLedger->name, '', Helper::formatIndianNumber($groupLedger->opening) . $groupLedger->opening_type, Helper::formatIndianNumber($groupLedger->total_debit), Helper::formatIndianNumber($groupLedger->total_credit), Helper::formatIndianNumber($ledgerClosing > 0 ? $ledgerClosing : -$ledgerClosing) . $ledgerClosingText];
                        }
                    } else {
                        $ledgerClosingText = '';
                        $ledgerClosing = $groupLedger->details_sum_debit_amt - $groupLedger->details_sum_credit_amt;
                        if ($ledgerClosing != 0) {
                            $ledgerClosingText = $ledgerClosing > 0 ? 'Dr' : 'Cr';
                        }
                        $data[] = ['', $groupLedger->name, '', Helper::formatIndianNumber($groupLedger->opening) . $groupLedger->opening_type, Helper::formatIndianNumber($groupLedger->details_sum_debit_amt), Helper::formatIndianNumber($groupLedger->details_sum_credit_amt), Helper::formatIndianNumber($ledgerClosing > 0 ? $ledgerClosing : -$ledgerClosing) . $ledgerClosingText];
                    }

                    if ($r->level == 3) {
                        if ($groupLedger->name == "Reserves & Surplus") {
                            $data[] = ['', '', 'Profit & Loss', Helper::formatIndianNumber($profitLoss['closingFinal']) . $profitLoss['closing_type'], 0, 0, Helper::formatIndianNumber($profitLoss['closingFinal']) . $profitLoss['closing_type']];
                        } else {
                            $subGroupLedgers = Helper::getTrialBalanceGroupLedgers($groupLedger->id, $startDate, $endDate, $organizations, $currency,$cost_center_ids,$r->location_id);
                            $subGroupLedgersData = $subGroupLedgers['data'];
                            foreach ($subGroupLedgersData as $subGroupLedger) {
                                if ($subGroupLedgers['type'] == 'group') {
                                    $subLedgerClosingText = '';
                                    $subLedgerClosing = $subGroupLedger->total_debit - $subGroupLedger->total_credit;
                                    if ($subLedgerClosing != 0) {
                                        $subLedgerClosingText = $subLedgerClosing > 0 ? 'Dr' : 'Cr';
                                    }
                                    $data[] = ['', '', $subGroupLedger->name, Helper::formatIndianNumber($subGroupLedger->opening) . $subGroupLedger->opening_type, Helper::formatIndianNumber($subGroupLedger->total_debit), Helper::formatIndianNumber($subGroupLedger->total_credit), Helper::formatIndianNumber($subLedgerClosing > 0 ? $subLedgerClosing : -$subLedgerClosing) . $subLedgerClosingText];
                                } else {
                                    $subLedgerClosingText = '';
                                    $subLedgerClosing = $subGroupLedger->details_sum_debit_amt - $subGroupLedger->details_sum_credit_amt;
                                    if ($subLedgerClosing != 0) {
                                        $subLedgerClosingText = $subLedgerClosing > 0 ? 'Dr' : 'Cr';
                                    }
                                    $data[] = ['', '', $subGroupLedger->name, Helper::formatIndianNumber($subGroupLedger->opening) . $subGroupLedger->opening_type, Helper::formatIndianNumber($subGroupLedger->details_sum_debit_amt), Helper::formatIndianNumber($subGroupLedger->details_sum_credit_amt), Helper::formatIndianNumber($subLedgerClosing > 0 ? $subLedgerClosing : -$subLedgerClosing) . $subLedgerClosingText];
                                }
                            }
                        }
                    }
                }
            }
        }
        $grandClosingTotal = $grandClosingTotal > 0 ? $grandClosingTotal : -$grandClosingTotal;
        //$data[] = ['', '', 'Grand Total', '', Helper::formatIndianNumber($grandDebitTotal), Helper::formatIndianNumber($grandCreditTotal), Helper::formatIndianNumber($grandClosingTotal) . $closing_type];

        $organizationName = DB::table('organizations')->where('id', $r->organization_id)->value('name');
        return Excel::download(new TrialBalanceReportExport($organizationName, $dateRange, $data), 'tiralBalanceReport.xlsx');
    }

    public function exportLedgerReport(Request $r)
    {

        $dateRange = $r->date;
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        $loc = null;
        $cost = null;
        $org = null;

        if ($r->has('location_id') && $r->location_id!="")
            $loc = $r->location_id;

        if ($r->has('cost_center_id') && $r->cost_center_id!="")
            $cost = $r->cost_center_id;

        if ($r->has('organization_id') && $r->organization_id!="")
            $org = $r->organization_id;


        $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }
        $organizationName = DB::table('organizations')->where('id', $r->organization_id)->value('name');
        $ledgerName = Ledger::where('id', $r->ledger_id)->where('status', 1)->value('name');

        $dates = explode(' to ', $r->date);
        $startDate = date('Y-m-d', strtotime($dates[0]));
        $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
        $fy = Helper::getFinancialYear($startDate);

        $non_carry = Helper::getNonCarryGroups();
        if (in_array($r->ledger_group, $non_carry))
            $carry = 0;
        else
            $carry = 1;
        $ledgerData = Helper::getLedgerData($r->ledger_id, $startDate, $endDate, $r->company_id, $org, $r->ledger_group, $currency, $cost_center_ids,$loc);
        $totalDebit = 0;
        $totalCredit = 0;
        $data = [['', '', '', '', '', '', '']];

        // Get first opening of ledger
           $openingData = ItemDetail::where('ledger_id', $r->ledger_id)
            ->where('ledger_parent_id', $r->ledger_group)
            ->when(!is_null($cost_center_ids), function ($q) use ($cost_center_ids) {
                    if (is_array($cost_center_ids)) {
                        $q->whereIn('cost_center_id', $cost_center_ids);
                    } else {
                        $q->where('cost_center_id', $cost_center_ids);
                    }
                    // $q->where('cost_center_id', $cost);
                    })
            ->whereHas('voucher', function ($query) use ($startDate, $fy, $carry,$loc,$org) {
                
                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->when(!is_null($loc), function ($q) use ($loc) {
                        $q->where('location', $loc);
                    })
                    ->when(!is_null($org), function ($q) use ($org) {
                        $q->where('organization_id', $org);
                    });
                $query->where('document_date', '<', $startDate);
                if (!$carry)
                    $query->where('document_date', '>=', $fy['start_date']);
            })
            ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
            ->first();
        $opening = $openingData;
        $opening->opening = ($openingData->total_debit - $openingData->total_credit) ?? 0;
        $opening->opening_type = ($openingData->total_debit > $openingData->total_credit) ? 'Dr' : 'Cr';
        // dd($opening,$opening->opening, $opening->opening_type);
        if ($opening && $opening->opening > 0) {
            $data[] = [
                '',
                '',
                '',
                '',
                '',
                'Opening Balance',
                Helper::formatIndianNumber($opening->opening),
                ''
            ];
        } else {
            $data[] = [
                '',
                '',
                '',
                '',
                '',
                'Opening Balance',
                Helper::formatIndianNumber($opening->opening),
                ''
            ];
        }

        foreach ($ledgerData as $voucher) {
            $dateFormatted = \Carbon\Carbon::parse($voucher->date)->format('d-m-y');
            $seriesName = $voucher->series->service->name ?? '';
            $bookCode = $voucher->series->book_code ?? '';
            $voucherNo = $voucher->voucher_no ?? '';
            $firstRow = true;

            $currentDebit = 0;
            $currentCredit = 0;

            // 1. Get current debit/credit once
            foreach ($voucher->items as $item) {
                if ($item->ledger_id == $r->ledger_id) {
                    $currentDebit = $item->debit_amt;
                    $currentCredit = $item->credit_amt;
                    $totalDebit += $currentDebit;
                    $totalCredit += $currentCredit;
                    break;
                }
            }

            // 2. Build rows for export
            foreach ($voucher->items as $item) {
                if ($item->ledger_id == $r->ledger_id) {
                    continue; // skip ledger line itself
                }

                $row = [];
                $currentBalance = $item->debit_amt - $item->credit_amt;
                $currentBalanceType = $currentBalance >= 0 ? 'Dr' : 'Cr';
                $formattedBalance = Helper::formatIndianNumber(abs($currentBalance)) . ' ' . $currentBalanceType;

                if ($firstRow) {
                    $row[] = $dateFormatted;
                    $row[] = $item->ledger->name ?? '';
                    $row[] = $formattedBalance;
                    $row[] = $seriesName;
                    $row[] = $bookCode;
                    $row[] = $voucherNo;
                    $row[] = Helper::formatIndianNumber($currentDebit);   // <- Add debit once
                    $row[] = Helper::formatIndianNumber($currentCredit);  // <- Add credit once
                    $firstRow = false;
                } else {
                    $row[] = ''; // Date
                    $row[] = $item->ledger->name ?? '';
                    $row[] = $formattedBalance;
                    $row[] = ''; // Series
                    $row[] = ''; // Book Code
                    $row[] = $voucherNo;
                    $row[] = ''; // Debit (only in first row)
                    $row[] = ''; // Credit (only in first row)
                }

                $data[] = $row; // append to final Excel export array
            }
        }

        // Add empty row
        $data[] = ['', '', '', '', '', '', '', ''];

        // Footer - Current Total
        $data[] = [
            '',
            '',
            '',
            '',
            '',
            'Current Total',
            Helper::formatIndianNumber($totalDebit),
            Helper::formatIndianNumber($totalCredit)
        ];



        // Footer - Closing Balance
        $closing = ($opening->opening ?? 0) + $totalDebit - $totalCredit;
        $closingType = $closing < 0 ? 'Cr' : 'Dr';
        $closingFormatted = Helper::formatIndianNumber(abs($closing));

        $data[] = [
            '',
            '',
            '',
            '',
            '',
            'Closing Balance',
            $closingType === 'Dr' ? $closingFormatted : '',
            $closingType === 'Cr' ? $closingFormatted : ''
        ];
        return Excel::download(new LedgerReportExport($organizationName, $ledgerName, $dateRange, $data), 'ledgerReport.xlsx');
    }

    public function filterLedgerReport(Request $r)
    {

        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        $loc = null;
        $cost = null;
        $org = null;

        if ($r->has('location_id') && $r->location_id!="")
            $loc = $r->location_id;

        if ($r->has('cost_center_id') && $r->cost_center_id!="")
            $cost = $r->cost_center_id;

        if ($r->has('organization_id') && $r->organization_id!="")
            $org = $r->organization_id;

        $dates = explode(' to ', $r->date);
        $startDate = date('Y-m-d', strtotime($dates[0]));
        $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
        $fy = Helper::getFinancialYear($startDate);
         $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }

        $data = Helper::getLedgerData($r->ledger_id, $startDate, $endDate, $r->company_id, $org, $r->ledger_group, $currency, $cost_center_ids,$loc);
        $id = $r->ledger_id;
        $group = $r->ledger_group;

        $non_carry = Helper::getNonCarryGroups();
        if (in_array($r->ledger_group, $non_carry))
            $carry = 0;
        else
            $carry = 1;

        $openingData = ItemDetail::where('ledger_id', $id)
            ->where('ledger_parent_id', $group)
            ->when(!is_null($cost_center_ids), function ($q) use ($cost_center_ids) {
                    // $q->where('cost_center_id', $cost_center_ids);
                    if (is_array($cost_center_ids)) {
                        $q->whereIn('cost_center_id', $cost_center_ids);
                    } else {
                        $q->where('cost_center_id', $cost_center_ids);
                    }
                    })
            ->whereHas('voucher', function ($query) use ($startDate, $fy, $carry,$loc,$org) {
                
                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->when(!is_null($loc), function ($q) use ($loc) {
                        $q->where('location', $loc);
                    })
                    ->when(!is_null($org), function ($q) use ($org) {
                        $q->where('organization_id', $org);
                    });
                $query->where('document_date', '<', $startDate);
                if (!$carry)
                    $query->where('document_date', '>=', $fy['start_date']);
            })
            ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
            ->first();

        $opening = $openingData;
        $opening->opening = ($openingData->total_debit - $openingData->total_credit) ?? 0;
        $opening->opening_type = ($openingData->total_debit > $openingData->total_credit) ? 'Dr' : 'Cr';

// dd($data);
        $html = view('ledgers.filterLedgerData', compact('data', 'id', 'opening'))->render();
        return response()->json($html);
    }

    public function getLedgerReport()
    {
        $user = Helper::getAuthenticatedUser();
        $orgIds = $user->organizations->pluck('organizations.id')->toArray();
        array_push($orgIds, $user?->organization_id);
        $companies = OrganizationCompany::whereIn('id', Organization::whereIn('id', $orgIds)->pluck('company_id')->toArray())
            ->with('organizations', function ($orgQuery) use ($orgIds) {
                $orgQuery->whereIn('id', $orgIds);
            })->select('id', 'name')->get();
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status','active')->get()->toArray();
        $locations = InventoryHelper::getAccessibleLocations();

        return view('ledgers.getLedgerReport', compact('cost_centers','cost_groups', 'companies','locations'));
    }

    public function get_org_ledgers($id)
    {
        $data = Ledger::where('status', 1)
        ->select('id', 'name')
        ->orderBy('name', 'asc')
        ->get();

        return response()->json($data);
    }



    public function index(Request $request, $id = null)
    {
        $user = Helper::getAuthenticatedUser();
        $userId = $user->id;
        $organizationId = $user->organization_id;
        $companies = Helper::access_org();
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
        } else {
            $fyear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $fyear['start_date'];
            $endDate = $fyear['end_date'];
        }
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status','active')->get()->toArray();

        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        $orgname=Organization::where('id',Helper::getAuthenticatedUser()->organization_id)->value('name');
        $locations = InventoryHelper::getAccessibleLocations();
        $date2 = \Carbon\Carbon::parse($startDate)->format('jS-F-Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('jS-F-Y');
        return view('trialBalance.view-trial-balance', compact('orgname','cost_centers','cost_groups', 'companies', 'organizationId', 'id', 'date2', 'dateRange','locations'));
    }

    public function getInitialGroups(Request $r)
    {
        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
        }

        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };

        if ($r->group_id) {
            $groups = Group::where('id', $r->group_id) // Ensuring the specific group_id condition
                ->select('id', 'name')
                ->with('children.children') // Eager loading children and grandchildren
                ->get();
        } else {
            $groups = Helper::getGroupsQuery($organizations)->whereNull('parent_group_id')->select('id', 'name')
                ->with('children.children') // Ensures eager loading of children & grandchildren
                ->get();
        }
        // dd($r->all());
        $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }
        // Get Reserves & Surplus
        // dd($cost_center_ids);
        //         dd([
        //     'raw' => $cost_center_ids,
        //     'type' => gettype($cost_center_ids),
        //     'is_array' => is_array($cost_center_ids),
        //     'values' => $cost_center_ids
        // ]);
        $profitLoss = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'trialBalance', $currency, $cost_center_ids,$r->location_id);

        $data = Helper::getGroupsData($groups, $startDate, $endDate, $organizations, $currency,  $cost_center_ids,$r->location_id);
        return response()->json(['currency' => $currency, 'data' => $data, 'type' => 'group', 'startDate' => date('d-M-Y', strtotime($startDate)), 'endDate' => date('d-M-Y', strtotime($endDate)), 'profitLoss' => $profitLoss, 'groups' => $groups]);
    }

    public function getSubGroups(Request $r)
    {
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };

        if ($r->date) {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        } elseif ($r->fmonth) {
            $startDate = Carbon::parse($r->fmonth)->startOfMonth()->toDateString();
            $endDate = Carbon::parse($r->fmonth)->endOfMonth()->toDateString();
        } else {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
        }      

        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }
        $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }

        $groupLedgers = Helper::getTrialBalanceGroupLedgers($r->id, $startDate, $endDate, $organizations, $currency,$cost_center_ids,$r->location_id);

        return response()->json($groupLedgers);
    }

    public function getSubGroupsMultiple(Request $r)
    {
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        if ($r->date) {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
        } elseif ($r->fmonth) {
            $startDate = Carbon::parse($r->fmonth)->startOfMonth()->toDateString();
            $endDate = Carbon::parse($r->fmonth)->endOfMonth()->toDateString();
        } else {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
        }      

        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }

        // dd($r);
        $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }
        $allData = [];
        foreach ($r->ids as $id) {
            $groupLedgers = Helper::getTrialBalanceGroupLedgers($id, $startDate, $endDate, $organizations, $currency, $cost_center_ids,$r->location_id);
            $gData['id'] = $id;
            $gData['type'] = $groupLedgers['type'];
            $gData['data'] = $groupLedgers['data'];
            $allData[] = $gData;
        }



        return response()->json(['data' => $allData, 'date0' => $startDate, 'date1' => $endDate]);
    }

    public function trailLedger($id, Request $r, $group)
    {
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };

         $loc = null;
        $cost = null;
        $org = null;

        if ($r->has('location_id') && $r->location_id!="")
            $loc = $r->location_id;

        if ($r->has('cost_center_id') && $r->cost_center_id!="")
            $cost = $r->cost_center_id;

        if ($r->has('organization_id') && $r->organization_id!="")
            $org = $r->organization_id;

        // Fetch companies based on the user's organization group
        $companies = Helper::access_org();

        $organization = DB::table('organizations')->where('id', Helper::getAuthenticatedUser()->organization_id)->value('name');
        $ledger = Ledger::where('id', $id)->where('status', 1)->value('name');
        // Determine the date range
        if ($r->date) {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        } else {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
        }
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status','active')->get()->toArray();

        $cost_center_ids = null;
        if (!empty($r->cost_center_id)) {
            $cost_center_ids = $r->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($r->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $r->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }

        $data = Helper::getLedgerData($id, $startDate, $endDate, $r->company_id, Helper::getAuthenticatedUser()->organization_id, $group, $currency, $cost_center_ids,$r->location_id);

        $fy = Helper::getFinancialYear($startDate);
        if (in_array($group, Helper::getNonCarryGroups()))
            $carry = 0;
        else
            $carry = 1;

       

             $openingData = ItemDetail::where('ledger_id', $id)
            ->where('ledger_parent_id', $group)
            ->when(!is_null($cost), function ($q) use ($cost_center_ids) {
                    if (is_array($cost_center_ids)) {
                        $q->whereIn('cost_center_id', $cost_center_ids);
                    } else {
                        $q->where('cost_center_id', $cost_center_ids);
                    }
                    // $q->where('cost_center_id', $cost);
                    })
            ->whereHas('voucher', function ($query) use ($startDate, $fy, $carry,$loc,$org) {
                
                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->when(!is_null($loc), function ($q) use ($loc) {
                        $q->where('location', $loc);
                    })
                    ->when(!is_null($org), function ($q) use ($org) {
                        $q->where('organization_id', $org);
                    });
                $query->where('document_date', '<', $startDate);
                if (!$carry)
                    $query->where('document_date', '>=', $fy['start_date']);
            })
            ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
            ->first();

        $opening = $openingData;
        $opening->opening = ($openingData->total_debit - $openingData->total_credit) ?? 0;
        $opening->opening_type = ($openingData->total_debit > $openingData->total_credit) ? 'Dr' : 'Cr';
        $organizationId = Helper::getAuthenticatedUser()->organization_id;
         $locations = InventoryHelper::getAccessibleLocations();



  
         return view('trialBalance.trail_ledger', compact('locations','cost_centers','cost_groups' ,'data', 'companies', 'id', 'startDate', 'endDate', 'organization', 'ledger', 'opening', 'group','organizationId'));
    }
}
