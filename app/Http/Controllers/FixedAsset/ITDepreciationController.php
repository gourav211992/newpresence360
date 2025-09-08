<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use App\Helpers\FinanceModule;
use App\Models\FixedAssetRegistration;
use App\Helpers\InventoryHelper;
use App\Models\ErpFinancialYear;
use App\Models\FixedAssetSub;


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
        $asset_details = FixedAssetRegistration::where('capitalize_date', '<', $endDate)
            ->withWhereHas('subAsset', function ($query) {
                $query->where('current_value', '>', 0)
                    ->whereNotNull('expiry_date');
            })->withWhereHas('ledger')
            ->whereNotNull('capitalize_date')
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            })
            ->withWhereHas('category.setup')
            ->withWhereHas('it_category.setup')
            ->orderBy('capitalize_date', 'asc')
            ->whereNotNull('it_category_id')
            ->with('subAsset') // ensure subAsset is eager loaded
            ->get()
            ->map(function ($asset) {
                // Compute and assign the value here
                foreach ($asset->subAsset as $sub) {
                    $sub->rdv = self::getIncomeTaxRDV(
                        $sub->capitalize_date,
                        $asset->it_category->setup->dep_percentage ?? 0,
                        $sub->current_value
                    );
                }
                return $asset;
            })->values();

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
                    if ($quarterEnd > $end)
                        $quarterEnd = clone $end;

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
                    if ($monthEnd > $end)
                        $monthEnd = clone $end;

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

    public static function getIncomeTaxRDV(string $date, $depPercentage, $value)
    {
       
        $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        $capDate = new DateTime($date);
        $type = null;
        $month = (int) $capDate->format('m');
        $day = (int) $capDate->format('d');
        $mmdd = ($month * 100) + $day;
        if (($mmdd >= 1004 && $mmdd <= 1231) || ($mmdd >= 101 && $mmdd <= 331)) {
            $type = "half";
        }


        $startFormatted = date('d-m-Y', strtotime($financialYear['start_date']));
        $endFormatted = date('d-m-Y', strtotime($financialYear['end_date']));
        $range = $startFormatted . ' to ' . $endFormatted;
        $rdv_value = $value;

        while (true) {
            $financialYearDate = ErpFinancialYear::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();
            
                if (!$financialYearDate) {
                break;
                }

            $start = date('d-m-Y', strtotime($financialYearDate->start_date));
            $end = date('d-m-Y', strtotime($financialYearDate->end_date));
            $frange = $start . ' to ' . $end;

            if ($range === $frange) {
                break;
            }

            $totalDepreciation = ($depPercentage / 100) * $rdv_value;
            if ($type == "half")
                $totalDepreciation = $totalDepreciation / 2;

            $rdv_value = $rdv_value - $totalDepreciation;
            $date = (new DateTime($date))->modify('+1 year')->format('Y-m-d');
        }

        return $rdv_value;
    }

    public function getFixedAssetRDV(Request $request){
    try {
            // Validate request parameters
            $request->validate([
                'uid' => 'nullable|string',
                'asset_code' => 'nullable|string',
                'asset_name' => 'nullable|string',
                'sub_asset_code' => 'nullable|string',
            ]);

            $uid = $request->input('uid');
            $assetCode = $request->input('asset_code');
            $subAssetCode = $request->input('sub_asset_code');
            $assetName = $request->input('asset_name');

            // Validate that at least one parameter is provided
            if (empty($uid) && empty($assetCode) && empty($subAssetCode) && empty($assetName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either uid or asset_code,asset_name parameter is required',
                    'data' => null
                ], 400);
            }

            // Call the helper function to get RDV calculation
            $result = FinanceModule::getFixedAssetRDV($uid, $assetCode, $subAssetCode, $assetName);

            // Return appropriate HTTP status code based on success
            $statusCode = $result['success'] ? 200 : 404;

            return response()->json($result, $statusCode);
            
        } catch (Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }

    public function getFixedAssetRDVResponse(Request $request)
    {
        try {

            $data = $request->validate([
                'sub_asset_code'      => 'nullable|array',
                'uid'                 => 'nullable|array',
                'sales_date'          => 'nullable|string',    
                'status'              => 'nullable|string',
                'total_sales_value'   => 'required|numeric',   
                'profit_loss_value'  => 'nullable|integer',
            ]);

            // Ensure at least one identifier is provided
            if (empty($data['sub_asset_code']) && empty($data['uid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either sub_asset_code array or uid array is required',
                    'data'    => null
                ], 400);
            }

            $assets = [];
            $totalSalesValue = $data['total_sales_value'];
            
            // Collect assets based on provided identifiers
            if (!empty($data['sub_asset_code'])) {
                foreach ($data['sub_asset_code'] as $subAssetCode) {
                    $asset = FixedAssetSub::where('sub_asset_code', $subAssetCode)->first();
                    if ($asset) {
                        $assets[] = $asset;
                    }
                }
            }

            if (!empty($data['uid'])) {
                foreach ($data['uid'] as $uid) {
                    $asset = FixedAssetSub::where('uid', $uid)->first();
                    if ($asset) {
                        $assets[] = $asset;
                    }
                }
            }

            if (empty($assets)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assets found with provided identifiers',
                    'data'    => null
                ], 404);
            }

            // Calculate individual sales value for each asset
            $individualSalesValue = $totalSalesValue / count($assets);
            $updatedAssets = [];
            $errors = [];

            foreach ($assets as $asset) {
                try {
                    // Get RDV for this asset using the helper
                    $rdvResult = FinanceModule::getFixedAssetRDV($asset->uid, null, $asset->sub_asset_code);
                    
                    if (!$rdvResult['success']) {
                        $errors[] = "Failed to calculate RDV for asset {$asset->sub_asset_code}: " . $rdvResult['message'];
                        continue;
                    }

                    $rdvValue = $rdvResult['rdv'] ?? 0;
                    
                    // Calculate profit/loss: sales_value - rdv
                    $profitLossValue = $individualSalesValue - $rdvValue;

                    // Update asset with calculated values
                    $asset->sales_date = $data['sales_date'] ?? $asset->sales_date;
                    $asset->status = $data['status'] ?? $asset->status;
                    $asset->sales_value = $individualSalesValue;
                    $asset->profit_loss_value = $profitLossValue;
                    $asset->save();

                    $updatedAssets[] = [
                        'uid' => $asset->uid,
                        'sub_asset_code' => $asset->sub_asset_code,
                        'sales_date' => $asset->sales_date,
                        'status' => $asset->status,
                        'sales_value' => $asset->sales_value,
                        'rdv_value' => $rdvValue,
                        'profit_loss_value' => $asset->profit_loss_value,
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error processing asset {$asset->sub_asset_code}: " . $e->getMessage();
                }
            }

            $response = [
                'success' => true,
                'message' => 'Assets processed successfully',
                'data' => [
                    'total_sales_value' => $totalSalesValue,
                    'individual_sales_value' => $individualSalesValue,
                    'total_assets_processed' => count($updatedAssets),
                    'updated_assets' => $updatedAssets
                ]
            ];

            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error in getFixedAssetRDVResponse: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing assets',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



}
