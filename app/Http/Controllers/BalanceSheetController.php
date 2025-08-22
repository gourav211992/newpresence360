<?php

namespace App\Http\Controllers;

use App\Exports\balanceSheetReportExport;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Organization;
use App\Models\PLGroups;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CostCenterOrgLocations;
use App\Models\CostGroup;
use App\Models\ErpStore;

class BalanceSheetController extends Controller
{
    public function exportBalanceSheet(Request $r)
    {

        $dateRange = $r->date;
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };

        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $today = Carbon::today();
            $endDate = Carbon::parse($financialYear['end_date']);

            if ($endDate->greaterThan($today)) {
                $endDate = $today;
            }

            $endDate = $endDate->format('Y-m-d');
            $dateRange = $startDate . ' to ' . $endDate;
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
            $today = date('Y-m-d');
        }
        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }
        $organizationName = implode(",", DB::table('organizations')->whereIn('id', $organizations)->pluck('name')->toArray());

        $liabilities_group = Helper::getGroupsQuery($organizations)
            ->where('name', 'Liabilities')->value('id');

        $assets_group = Helper::getGroupsQuery($organizations)->where('name', 'Assets')
            ->value('id');

        $liabilities = Helper::getGroupsQuery($organizations)
            ->where('parent_group_id', $liabilities_group)
            ->select('id', 'name')->get();


        $assets = Helper::getGroupsQuery($organizations)
            ->where('parent_group_id', $assets_group)
            ->select('id', 'name')->get();

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
        $reservesSurplus = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'balanceSheet', $currency, $cost_center_ids, $r->location_id);

        $liabilitiesData = Helper::getBalanceSheetData($liabilities, $startDate, $endDate, $organizations, 'liabilities', $currency, $cost_center_ids, $r->location_id);
        $assetsData = Helper::getBalanceSheetData($assets, $startDate, $endDate, $organizations, 'assets', $currency, $cost_center_ids, $r->location_id);

        $data = [];
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
        $liabilitiesTotal = 0;
        $assetsTotal = 0;
        $loopLength = Helper::checkCount($liabilitiesData) > Helper::checkCount($assetsData) ? Helper::checkCount($liabilitiesData) : Helper::checkCount($assetsData);
        for ($i = 0; $i < $loopLength; $i++) {

            $secName1 = $liabilitiesData->get($i)->name ?? '';

            if ($secName1 == "Reserves & Surplus") {
                $secAmount1 = $reservesSurplus;
            } else {
                $secAmount1 = $liabilitiesData->get($i)->closing ?? 0;
            }

            $secName2 = $assetsData->get($i)->name ?? '';
            $secAmount2 = $assetsData->get($i)->closing ?? 0;

            $liabilitiesTotal = $liabilitiesTotal + $secAmount1;
            $assetsTotal = $assetsTotal + $secAmount2;

            $data[] = [$secName1, '', '', self::formatValue($secAmount1), $secName2, '', '', self::formatValue($secAmount2)];

            if ($r->level == 2) {
                $liabilitiesGroupId = $liabilitiesData->get($i)->id ?? '';
                $assetsGroupId = $assetsData->get($i)->id ?? '';
                $liabilitiesLedgerData = [];
                $assetsLedgerData = [];
                if ($secName1 == "Reserves & Surplus") {
                    $liabilitiesLedgerData = collect([
                        ['name' => 'Profit & Loss', 'closing' => $reservesSurplus]
                    ]);
                } else {
                    if ($liabilitiesGroupId) {
                        $liabilitiesLedgerData = Helper::getBalanceSheetLedgers($liabilitiesGroupId, $startDate, $endDate, $organizations, $currency, $cost_center_ids, $r->location_id);
                    }
                }

                if ($assetsGroupId) {
                    $assetsLedgerData = Helper::getBalanceSheetLedgers($assetsGroupId, $startDate, $endDate, $organizations, $currency, $cost_center_ids, $r->location_id)->values();
                }

                $loopLengthLevel2 = Helper::checkCount($liabilitiesLedgerData) > Helper::checkCount($assetsLedgerData) ? Helper::checkCount($liabilitiesLedgerData) : Helper::checkCount($assetsLedgerData);
                $profitLossInserted = false;
                for ($j = 0; $j < $loopLengthLevel2; $j++) {

                    if ($secName1 === "Reserves & Surplus") {
                        if ($profitLossInserted) {
                            // Keep liabilities side empty for additional rows
                            $ledgerName1 = '';
                            $ledgerClosing1 = '';
                        } else {
                            $ledgerName1 = 'Profit & Loss';
                            $ledgerClosing1 = $reservesSurplus;
                            $profitLossInserted = true;
                        }
                    } else {
                        $ledgerName1 = $liabilitiesLedgerData->get($j)->name ?? '';
                        $ledgerClosing1 = $liabilitiesLedgerData->get($j)->closing ?? '';
                    }

                    $ledgerName2 = $assetsLedgerData[$j]->name ?? '';
                    $ledgerClosing2 = $assetsLedgerData[$j]->closing ?? '';
                    // dump($ledgerClosing1);
                    $data[] = [
                        '',
                        $ledgerName1,
                        $ledgerName1 !== '' ? self::formatValue($ledgerClosing1) : '',
                        '',
                        '',
                        $ledgerName2,
                        $ledgerName2 !== '' ? self::formatValue($ledgerClosing2)  : '',
                        ''
                    ];
                }
            }
        }
        // dd($data);
        $data[] = ['Total', '', '', Helper::formatIndianNumber($liabilitiesTotal), 'Total', '', '', Helper::formatIndianNumber($assetsTotal)];

        return Excel::download(new balanceSheetReportExport($organizationName, $dateRange, $data), 'balanceSheetReport.xlsx');
    }

    public static function formatValue($value)
    {
        $value = (float) $value;
        if ($value < 0) {
            return '(' . number_format(abs($value), 2, '.', ',') . ')';
        } else {
            return number_format($value, 2, '.', ',');
        }
    }

    public function balanceSheet(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $userId = $user->id;
        $organizationId = $user->organization_id;
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        array_push($orgIds, $user?->organization_id);
        $companies = Helper::access_org();
        
        $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->value('name');
        $fyear = Helper::getFinancialYear(date('Y-m-d'));

        if ($fyear) {
            $startDate = $fyear['start_date'];
            $today = Carbon::today();
            $endDate = Carbon::parse($fyear['end_date']);

            if ($endDate->greaterThan($today)) {
                $endDate = $today;
            }
            $endDate = $endDate->format('Y-m-d');
        } else {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
            $today = date('Y-m-d');
        }

        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status','active')->get()->toArray();

        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        $date2 = \Carbon\Carbon::parse($startDate)->format('jS-F-Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('jS-F-Y');
        $locations = InventoryHelper::getAccessibleLocations();


        return view('balanceSheet.balanceSheet', compact('cost_centers','cost_groups', 'organizationId', 'companies', 'organization', 'dateRange', 'date2', 'locations'));
    }

    public function balanceSheetInitialGroups(Request $r)
    {
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $today = Carbon::today();
            $endDate = Carbon::parse($financialYear['end_date']);

            if ($endDate->greaterThan($today)) {
                $endDate = $today;
            }

            $endDate = $endDate->format('Y-m-d');
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
            $today = date('Y-m-d');

            // if ($endDate > $today) {
            //     $endDate = $today;
            // }
        }

        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }
        $liabilities_group =  Helper::getGroupsQuery($organizations)
            ->where('name', "Liabilities")
            ->value('id');

        $assets_group = Helper::getGroupsQuery($organizations)->where('name', "Assets")
            ->value('id');


        $liabilities = Helper::getGroupsQuery($organizations)
            ->where('parent_group_id', $liabilities_group)
            ->select('id', 'name')->get();


        $assets = Helper::getGroupsQuery($organizations)
            ->where('parent_group_id', $assets_group)
            ->select('id', 'name')->get();

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
        $reservesSurplus = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'balanceSheet', $currency, $cost_center_ids, $r->location_id);

        $liabilitiesData = Helper::getBalanceSheetData($liabilities, $startDate, $endDate, $organizations, 'liabilities', $currency, $cost_center_ids, $r->location_id);
        $assetsData = Helper::getBalanceSheetData($assets, $startDate, $endDate, $organizations, 'assets', $currency, $cost_center_ids, $r->location_id);
        // dd($reservesSurplus);
        return response()->json(['liabilitiesData' => $liabilitiesData, 'assetsData' => $assetsData, 'startDate' => date('d-M-Y', strtotime($startDate)), 'endDate' => date('d-M-Y', strtotime($endDate)), 'reservesSurplus' => $reservesSurplus]);
    }

    public function getBalanceSheetLedgers(Request $r)
    {
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $today = Carbon::today();
            $endDate = Carbon::parse($financialYear['end_date']);

            if ($endDate->greaterThan($today)) {
                $endDate = $today;
            }

            $endDate = $endDate->format('Y-m-d');
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
            $today = date('Y-m-d');

            // if ($endDate > $today) {
            //     $endDate = $today;
            // }
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
        $data = Helper::getBalanceSheetLedgers($r->id, $startDate, $endDate, $organizations, $currency, $cost_center_ids,$r->location_id);

        return response()->json(['data' => $data]);
    }

    public function getBalanceSheetLedgersMultiple(Request $r)
    {
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };
        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $today = Carbon::today();
            $endDate = Carbon::parse($financialYear['end_date']);

            if ($endDate->greaterThan($today)) {
                $endDate = $today;
            }

            $endDate = $endDate->format('Y-m-d');
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $startDate;
            $today = date('Y-m-d');

            // if ($endDate > $today) {
            //     $endDate = $today;
            // }
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

        $allData = [];
        foreach ($r->ids as $id) {

            $data = Helper::getBalanceSheetLedgers($id, $startDate, $endDate, $organizations, $currency, $cost_center_ids, $r->location_id);

            $gData['id'] = $id;
            $gData['data'] = $data;
            $allData[] = $gData;
        }
        // dd($allData);

        return response()->json(['data' => $allData]);
    }
}
