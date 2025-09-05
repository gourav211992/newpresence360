<?php

namespace App\Http\Controllers\FixedAsset;

use App\Models\Ledger;

use App\Models\FixedAssetDepreciation;
use App\Models\FixedAssetSub;
use App\Models\FixedAssetDepreciationHistory;
use App\Helpers\FinancialPostingHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FixedAssetSetup;
use App\Models\ErpAssetCategory;
use App\Helpers\Helper;
use App\Models\ErpStore;
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetRegistration;
use App\Helpers\InventoryHelper;
use App\Models\ErpFinancialYear;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Exception;
use App\Http\Requests\DepreciationRequest;
use App\Models\ApprovalWorkflow;

class DepreciationController extends Controller
{
    public function index(Request $request)
    {
        $parentURL = request()->segments()[0];
        $parentURL = "fixed-asset_depreciation";


        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $data = FixedAssetDepreciation::orderBy('id', 'desc');
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = date('Y-m-d', strtotime($dates[1]));
            $data = $data->whereDate('document_date', '>=', $start)
                ->whereDate('document_date', '<=', $end);
        } else {
            $fyear = Helper::getFinancialYear(date('Y-m-d'));

            $data = $data->whereDate('document_date', '>=', $fyear['start_date'])
                ->whereDate('document_date', '<=', $fyear['end_date']);
            $start = $fyear['start_date'];
            $end = $fyear['end_date'];
        }
        $data = $data->get();
        return view('fixed-asset.depreciation.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_depreciation";
        $organization = Helper::getAuthenticatedUser()->organization;
        $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        $dep_type = $organization->dep_type;

        $periods = $this->getPeriods($financialYear['start_date'], $financialYear['end_date'], $dep_type);

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $fy = date('Y', strtotime($financialYear['start_date'])) . "-" . date('Y', strtotime($financialYear['end_date']));
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $fy_months = Helper::getCurrentFinancialYearMonths();


        $locations = InventoryHelper::getAccessibleLocations();

        
        return view('fixed-asset.depreciation.create', compact('financialEndDate', 'financialStartDate', 'locations', 'series', 'periods', 'fy', 'dep_type', 'fy_months'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepreciationRequest $request)
    {
        $validator = $request->validated();
        if (!$validator) {
            return redirect()
                ->route('finance.fixed-asset.depreciation.create')
                ->withInput()
                ->withErrors($request->errors());
        }

        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::SUBMITTED;
        $additionalData = [
            'created_by' => $user->auth_user_id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'group_id' => $user->organization->group_id,
            'revision_number' => 0,
            'company_id' => $user->organization->company_id,
            'approval_level' => 1,
            'assets' => json_encode($request->assets),
            'currency_id' => $user?->organization?->currency_id,
            'document_status' => $status,
        ];
        $data = array_merge($request->all(), $additionalData);

        DB::beginTransaction();

        try {
            $insert = FixedAssetDepreciation::create($data);
            $doc = Helper::approveDocument($insert->book_id, $insert->id, $insert->revision_number, "", null, 1, 'submit', 0, get_class($insert));
            $insert->document_status = $doc['approvalStatus'] ?? $insert->document_status;
            $insert->save();
            $sub_assets = json_decode($request->asset_details, true);
            $assets = array_unique(array_column($sub_assets, 'asset_id'));

            foreach ($assets as $asset) {
                $index = array_search($asset, array_column($sub_assets, 'asset_id'));
                $asset = $sub_assets[$index];
                $assetReg = FixedAssetRegistration::find($asset['asset_id']);
                if ($assetReg) {
                    $assetReg->posted_days += $asset['days'] ?? 0;
                    $assetReg->last_dep_date = Carbon::createFromFormat('d-m-Y', $asset['to_date'])->addDay()->format('Y-m-d');
                    $assetReg->save();
                }
            }

            foreach ($sub_assets as $sub_asset) {
                $subAsset = FixedAssetSub::find((int)$sub_asset['sub_asset_id']);
                if ($subAsset) {
                    $subAsset->total_depreciation += (float)$sub_asset['dep_amount'] ?? 0;
                    $subAsset->current_value_after_dep = (float)$sub_asset['after_dep_value']??0;
                    $subAsset->last_dep_date = Carbon::createFromFormat('d-m-Y', $sub_asset['to_date'])->addDay()->format('Y-m-d');
                    $subAsset->save();
                }
            }

            foreach ($assets as $asset) {
                $index = array_search($asset, array_column($sub_assets, 'asset_id'));
                $asset = $sub_assets[$index];
                $assetReg = FixedAssetRegistration::find($asset['asset_id']);
                $assetReg->updateTotalDep();
            }
            DB::commit();

            return redirect()->route("finance.fixed-asset.depreciation.index")
                ->with('success', 'Depreciation created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("finance.fixed-asset.depreciation.create")
                ->withInput()
                ->with('error', $e->getMessage() . $e->getLine());
        }
    }
    public function show(Request $r, string $id)
    {
        $currNumber = $r->revisionNumber;
        if ($currNumber) {
            $data = FixedAssetDepreciationHistory::findorFail($id);
        } else {
            $data = FixedAssetDepreciation::findorFail($id);
        }
        $parentURL = "fixed-asset_depreciation";
        $organization = Helper::getAuthenticatedUser()->organization;
        $dep_type = $organization->dep_type;
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;

        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $data->id,
            $data->grand_total_after_dep_value,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        list($startDate, $endDate) = explode(" to ", $data->period);
        $totalDays = (new DateTime($startDate))->diff(new DateTime($endDate))->days + 1;

        $fy = date('Y', strtotime($startDate)) . "-" . date('Y', strtotime($endDate));
        $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $data->created_at);

        // Step 2: Convert to d-m-Y format
        $today = $createdAt->format('d-m-Y');
        if ($today < $startDate || $today > $endDate) {
            $resultDate = $endDate; // If today is outside the range, use the end date
        } else {
            $resultDate = $today; // Otherwise, use today
        }

        // Format the result date as needed
        $endDate = $resultDate;
        $assetDetails = json_decode($data->asset_details, true);

        $revNo = $data->revision_number;
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }
        // Split the period range string into from/to dates
        [$fromDateRaw, $toDateRaw] = explode(' to ', $data->period);

        // Convert the 'from' date to Y-m-d for comparison
        $formattedStartDate = \Carbon\Carbon::createFromFormat('d-m-Y', $fromDateRaw)->format('Y-m-d');

        // Fetch all depreciation records where the period is before the start date
        $olderRecords = FixedAssetDepreciation::whereRaw("STR_TO_DATE(period, '%d-%m-%Y') < ?", [$formattedStartDate])
            ->get();

        // Disable the post button if any older record is not posted
        if ($olderRecords->isNotEmpty() && !$olderRecords->every(fn($item) => $item->document_status === 'posted')) {
            $buttons['post'] = false;
        }


        $approvalHistory = Helper::getApprovalHistory($data->book_id, $id, $revNo, $data->grand_total_current_value, $data->created_by);
        $locations = InventoryHelper::getAccessibleLocations();



        return view('fixed-asset.depreciation.show', compact('locations', 'data', 'series', 'buttons', 'docStatusClass', 'endDate', 'fy', 'totalDays', 'assetDetails', 'revision_number', 'currNumber', 'approvalHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function getLedgerGroups($ledgerId)
    {
        $ledger = Ledger::find($ledgerId);

        if ($ledger) {
            $groups = $ledger->group();

            if ($groups && $groups instanceof \Illuminate\Database\Eloquent\Collection) {
                $groupItems = $groups->map(function ($group) {
                    return ['id' => $group->id, 'name' => $group->name];
                });
            } else if ($groups) {
                $groupItems = [
                    ['id' => $groups->id, 'name' => $groups->name],
                ];
            } else {
                $groupItems = [];
            }

            return response()->json($groupItems);
        }

        return response()->json([], 404);
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
            ->orderBy('last_dep_date','asc')
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



        $depreciationPeriods = FixedAssetRegistration::withWhereHas('subAsset', function ($query) {
                $query->where('current_value_after_dep', '>', 0)
                    ->whereNotNull('expiry_date')
                    ->whereColumn('expiry_date', '>', 'last_dep_date');
            })
            ->whereNotNull('depreciation_percentage')
            ->withWhereHas('ledger')
            ->whereNotNull('capitalize_date')
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            })
            ->pluck('last_dep_date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();



        $periods = array_filter($periods, function ($period) use ($depreciationPeriods) {
            $parts = explode(' to ', $period->value);
            $endDateRaw = trim($parts[1] ?? '');
            $startDateRaw = trim($parts[0] ?? '');

            if (!$endDateRaw || !$startDateRaw) return false;

            try {
                $endDate = Carbon::createFromFormat('d-m-Y', $endDateRaw)->format('Y-m-d');
                $startDate = Carbon::createFromFormat('d-m-Y', $startDateRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }

            // Check if ANY depreciation date is LESS than this period's end date
            foreach ($depreciationPeriods as $depDate) {
                if ($depDate >= $startDate && $depDate <= $endDate) {
                    return true;
                }
            }

            return false;
        });
        return $periods;
       }
    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = FixedAssetDepreciation::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = $doc->grand_total_after_dep_value;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::financeVoucherPosting((int)$request->book_id ?? 0, $request->document_id ?? 0, $request->type ?? 'get');
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage() . $ex->getFile() . $ex->getLine()
            ]);
        }
    }

    public function postInvoice(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post");
            if ($data['status']) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ]);
        }
    }
    public function amendment(Request $request, $id)
    {
        $asset_id = FixedAssetDepreciation::find($id);
        if (!$asset_id) {
            return response()->json([
                "data" => [],
                "message" => "Depreciation not found.",
                "status" => 404,
            ]);
        }

        $revisionData = [
            [
                "model_type" => "header",
                "model_name" => "FixedAssetDepreciation",
                "relation_column" => "",
            ]
        ];

        $a = Helper::documentAmendment($revisionData, $id);
        DB::beginTransaction();
        try {
            if ($a) {
                Helper::approveDocument(
                    $asset_id->book_id,
                    $asset_id->id,
                    $asset_id->revision_number,
                    "Amendment",
                    $request->file("attachment"),
                    $asset_id->approval_level,
                    "amendment"
                );

                $asset_id->document_status = ConstantHelper::DRAFT;
                $asset_id->revision_number = $asset_id->revision_number + 1;
                $asset_id->revision_date = now();
                $asset_id->save();
            }

            DB::commit();
            return response()->json([
                "data" => [],
                "message" => "Amendment done!",
                "status" => 200,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Amendment Submit Error: " . $e->getMessage());
            return response()->json([
                "data" => [],
                "message" => "An unexpected error occurred. Please try again.",
                "status" => 500,
            ]);
        }
    }
    public function getAssetsDepreciationNew(Request $request)
    {
        $id = $request->id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $sub = FixedAssetSub::find($id);
        $fy              = session('fy');
        $finStart        = session('financial_start_date');
        $finEnd          = session('financial_end_date');

        $rows = [];
        $asset = $sub->asset;
        // adjust our “to” date if asset has expired
        $days = $fromDate->diffInDays($toDate) + 1;

        // pick base value
        if ($asset->depreciation_method === 'SLM') {
            $baseValue = $sub->current_value;
        } else {
            $inFinYear = Carbon::parse($asset->capitalize_date)
                ->between(Carbon::parse($finStart), Carbon::parse($finEnd));
            $baseValue = $inFinYear
                ? $sub->current_value
                : $sub->current_value_after_dep;
        }

        // prorated depreciation
        $annualRate  = $asset->depreciation_percentage_year / 100;
        $depAmount   = round($annualRate * $baseValue * ($days / 365), 4);
        $afterValue  = $sub->current_value_after_dep - $depAmount;

        // ensure we never drop below salvage for WDV
        if (
            $asset->depreciation_method === 'WDV'
            && $afterValue > $sub->salvage_value
        ) {
            $excess     = $afterValue - $sub->salvage_value;
            $depAmount += $excess;
            $afterValue -= $excess;
        }

        $rows[] = [
            'asset_id'                   => $asset->id,
            'category'                   => $asset->category->name,
            'asset_code'                 => $asset->asset_code,
            'sub_asset_id'               => $sub->id,
            'sub_asset_code'             => $sub->sub_asset_code,
            'asset_name'                 => $asset->asset_name,
            'ledger_name'                => $asset->ledger->name,
            'fy'                         => $fy,
            'from_date'                  => $fromDate->format('d-m-Y'),
            'to_date'                    => $toDate->format('d-m-Y'),
            'days'                       => $days,
            'current_value'              => $sub->current_value,
            'current_value_after_dep'    => $sub->current_value_after_dep,
            'dep_amount'                 => $depAmount,
            'after_dep_value'            => $afterValue,
        ];

        return response()->json($rows);
    }
     

}
