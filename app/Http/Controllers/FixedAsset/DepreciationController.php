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
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetRegistration;
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
        $parentURL = request() -> segments()[0];
        $parentURL = "fixed-asset_depreciation";
        
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
       $data=FixedAssetDepreciation::withDefaultGroupCompanyOrg()->orderBy('id','desc')->get();
        return view('fixed-asset.depreciation.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_depreciation";
        $organization = Helper::getAuthenticatedUser()->organization;
        $financialYear = Helper::getFinancialYear(Carbon::now()
        //->subYear()
        ->format('Y-m-d'));
         $dep_type = $organization->dep_type; 
        
        $periods = $this->getPeriods($financialYear['start_date'], $financialYear['end_date'], $dep_type);
        
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        $fy = date('Y', strtotime($financialYear['start_date']))."-".date('Y', strtotime($financialYear['end_date']));
        
    
       
        
        return view('fixed-asset.depreciation.create',compact('series', 'periods','fy','dep_type'));
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
    $status = Helper::checkApprovalRequired($request->book_id);
    $additionalData = [
        'created_by' => $user->auth_user_id,
        'type' => get_class($user),
        'organization_id' => $user->organization->id,
        'group_id' => $user->organization->group_id,
        'revision_number' => 0,
        'company_id' => $user->organization->company_id,
        'assets' => json_encode($request->assets),
        'currency_id' => $user?->organization?->currency_id,
        'document_status' => $status,
    ];
    $data = array_merge($request->all(), $additionalData);

    DB::beginTransaction();

    try {
        $insert = FixedAssetDepreciation::create($data);
        if ($request->document_status == ConstantHelper::SUBMITTED) {
            $insert->document_status = Helper::checkApprovalRequired($request->book_id);
            if (Helper::checkApprovalRequired($request->book_id) == ConstantHelper::SUBMITTED) {
                if (self::check_approved($request->book_id))
                    $insert->document_status = 'approved';
                    $insert->save();
            }
        }
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
            $subAsset = FixedAssetSub::find($sub_asset['sub_asset_id']);
            if ($subAsset) {
                $subAsset->total_depreciation += $sub_asset['dep_amount'] ?? 0;
                $subAsset->current_value_after_dep = $sub_asset['after_dep_value'] ?? null;
                $subAsset->save();
            }
        }
        
        if ($insert->document_status == ConstantHelper::SUBMITTED) {
            Helper::approveDocument($request->book_id, $insert->id, $insert->revision_number, "", null, 1, 'submit', 0, get_class($insert));
        }

        if ($insert->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED || $insert->approvalStatus == ConstantHelper::APPROVED) {
            Helper::approveDocument($request->book_id, $insert->id, $insert->revision_number, "", null, 1, 'approve', 0, get_class($insert));
        }

        DB::commit();

        return redirect()->route("finance.fixed-asset.depreciation.index")
                         ->with('success', 'Depreciation created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route("finance.fixed-asset.depreciation.create")
                         ->withInput()
                         ->with('error', $e->getMessage().$e->getLine());
    }
}

    public function check_approved($book_id)
    {
        $workflow = ApprovalWorkflow::where('book_id', $book_id);
        $user = Helper::getAuthenticatedUser();

        if ($workflow && $workflow->count() == 1) {
            $workflow = $workflow->first();
            if ($workflow->user_id === $user->auth_user_id) {
                return true;
            } else return false;
        } else return false;
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $r,string $id)
    {
        $currNumber = $r->revisionNumber;
        if ($currNumber) {
            $data= FixedAssetDepreciationHistory::withDefaultGroupCompanyOrg()->findorFail($id);
        } else {
            $data= FixedAssetDepreciation::withDefaultGroupCompanyOrg()->findorFail($id);
        }
        $parentURL = "fixed-asset_depreciation";
        $organization = Helper::getAuthenticatedUser()->organization;
        $dep_type = $organization->dep_type; 
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;
            
        $buttons = Helper::actionButtonDisplay($data->book_id,$data->document_status , $data->id, $data->grand_total_after_dep_value, 
        $data->approval_level, $data -> created_by ?? 0, $userType['type'], $revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        list($startDate, $endDate) = explode(" to ", $data->period);
        $totalDays = (new DateTime($startDate))->diff(new DateTime($endDate))->days + 1;
      
        $fy = date('Y', strtotime($startDate))."-".date('Y', strtotime($endDate));
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
        $allPosted = FixedAssetDepreciation::where('created_at', '<', $data->created_at)
        ->get()
        ->every(fn($item) => $item->document_status === 'posted');

        if(!$allPosted) {
           $buttons['post'] = false;
        }

        $approvalHistory = Helper::getApprovalHistory($data->book_id, $id, $revNo, $data->grand_total_current_value, $data -> created_by);
            


        return view('fixed-asset.depreciation.show', compact('data','series','buttons','docStatusClass','endDate','fy','totalDays','assetDetails','revision_number', 'currNumber','approvalHistory'));
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
        $asset_details=[];
        $asset_details = FixedAssetRegistration::withDefaultGroupCompanyOrg()
            ->whereHas('subAsset')
            ->with('subAsset')
            ->whereNotNull('depreciation_percentage')
            ->whereNotNull('depreciation_percentage_year')
            ->with('ledger')
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->with('category')
            ->get()
            ->where('last_dep_date', '<', $endDate)
            ->filter(function ($asset) {
                // Calculate the capitalized date + useful life in years
                $usefulLifeInYears = $asset->useful_life; // Assuming `useful_life_years` is the field
                $capitalizedDate = Carbon::parse($asset->capitalize_date); // Assuming `capitalized_date` is the field
                $capitalizedDateWithLife = $capitalizedDate->addYears($usefulLifeInYears); // Add useful life years to capitalized date
                
                // Compare if the new date is greater than today's date
                return $capitalizedDateWithLife->greaterThanOrEqualTo(Carbon::today());
            })->values();
        
            
        return response()->json($asset_details);
    }
    function getPeriods($startDate, $endDate, $period) {
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
        
    
        $depreciationPeriods = FixedAssetDepreciation::withDefaultGroupCompanyOrg()->get()->pluck('period')->toArray();
        
        $periods = array_filter($periods, function ($period) use ($depreciationPeriods) {
            return !in_array($period->value, $depreciationPeriods);
        });
        
        // Reset keys if needed
        return array_values($periods);
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
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
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
        $data = FinancialPostingHelper::financeVoucherPosting((int)$request -> book_id ?? 0, $request -> document_id ?? 0, $request -> type ?? 'get');
            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
            ]);
        }
    }

    public function postInvoice(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = FinancialPostingHelper::financeVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, "post");
            if ($data['status']) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response() -> json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex -> getMessage()
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


    
    
    
    
    
}
