<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetRegistration;
use App\Helpers\InventoryHelper;
use DateTime;

class ITDepreciationController extends Controller
{
    public function index()
    {
        $parentURL = "fixed-asset_it-dep";
        $organization = Helper::getAuthenticatedUser()->organization;
        $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        $dep_type = $organization->dep_type;

        $periods = $this->getPeriods($financialYear['start_date'], $financialYear['end_date'], 'yearly');
        $fy = date('Y', strtotime($financialYear['start_date'])) . "-" . date('Y', strtotime($financialYear['end_date']));
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];


        $locations = InventoryHelper::getAccessibleLocations();

        
        return view('fixed-asset.it_depreciation.create', compact('financialEndDate', 'financialStartDate', 'locations', 'periods', 'fy', 'dep_type'));
    }

    public function getAssets(Request $request)
    {
        $startDate = $endDate = null;
        if ($request->filled('date_range')) {
            $dateRange = explode(' to ', $request->input('date_range'));
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse($dateRange[0])->format('Y-m-d');
                $endDate = Carbon::parse($dateRange[1])->format('Y-m-d');
            }
        }
        $asset_details = [];
        $asset_details = FixedAssetRegistration::where('last_dep_date', '<', $endDate)
            ->withWhereHas('subAsset', function ($query) {
                $query->where('current_value_after_dep', '>', 0);
                $query->whereNotNull('expiry_date');
                $query->whereColumn('expiry_date', '!=', 'last_dep_date');
            })
            ->whereNotNull('depreciation_percentage')
            ->withWhereHas('ledger')
           ->whereNotNull('capitalize_date')
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            })
            ->withWhereHas('category.setup')
            ->withWhereHas('it_category.setup')
            ->orderBy('last_dep_date','asc')
             ->whereNotNull('it_category_id')
            ->get()->values();

        return response()->json($asset_details);
    }
    function getPeriods($startDate, $endDate, $period)
    {
        $periods = [];

        // Convert to DateTime objects
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        switch ($period) {
            case 'yearly':
                $periods[] = (object) [
                    "value" => $start->format("d-m-Y") . " to " . $end->format("d-m-Y"),
                    "label" => $end->format("jS F Y")
                ];
                break;


            case 'half_yearly':
                $half1_end = (clone $start)->modify('+5 months')->modify('last day of this month');
                $half2_start = (clone $half1_end)->modify('+1 day');

                $periods[] = (object) [
                    "value" => $start->format("d-m-Y") . " to " . $half1_end->format("d-m-Y"),
                    "label" => $half1_end->format("jS F Y")
                ];
                $periods[] = (object) [
                    "value" => $half2_start->format("d-m-Y") . " to " . $end->format("d-m-Y"),
                    "label" => $end->format("jS F Y")
                ];
                break;

            case 'quarterly':
                $quarterStart = clone $start;
                while ($quarterStart <= $end) {
                    $quarterEnd = (clone $quarterStart)->modify('+2 months')->modify('last day of this month');
                    if ($quarterEnd > $end) $quarterEnd = clone $end;

                    $periods[] = (object) [
                        "value" => $quarterStart->format("d-m-Y") . " to " . $quarterEnd->format("d-m-Y"),
                        "label" => $quarterEnd->format("jS F Y")
                    ];
                    $quarterStart = (clone $quarterEnd)->modify('+1 day');
                }
                break;

            case 'monthly':
                $monthStart = clone $start;
                while ($monthStart <= $end) {
                    $monthEnd = (clone $monthStart)->modify('last day of this month');
                    if ($monthEnd > $end) $monthEnd = clone $end;

                    $periods[] = (object) [
                        "value" => $monthStart->format("d-m-Y") . " to " . $monthEnd->format("d-m-Y"),
                        "label" => $monthEnd->format("jS F Y")
                    ];
                    $monthStart->modify('+1 month');
                }
                break;

            default:
                return "Invalid period type. Choose from 'yearly', 'half_yearly', 'quarterly', or 'monthly'.";
        }

        return $periods;
       }
 }
