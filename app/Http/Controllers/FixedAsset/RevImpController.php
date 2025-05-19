<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetRegistration;
use App\Models\ErpAssetCategory;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\FixedAssetRevImp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\FinancialPostingHelper;
use App\Models\FixedAssetRevImpHistory;
use App\Models\FixedAssetSub;
use App\Models\ErpStore;
use Exception;


class RevImpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentURL = "fixed-asset_revaluation-impairement";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }

        $data=FixedAssetRevImp::withDefaultGroupCompanyOrg()->orderBy('id','desc');
        if($request->filter_asset)
        $data=$data->where('id',(int)$request->filter_asset);
        if($request->filter_ledger)
        $data=$data->where('ledger_id',$request->filter_ledger);
        if($request->filter_status)
        $data=$data->where('document_status',$request->filter_status);
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = date('Y-m-d', strtotime($dates[1]));
            $data = $data->whereDate('document_date', '>=', $start)
                ->whereDate('document_date', '<=', $end);
        }
        else{
           $fyear = Helper::getFinancialYear(date('Y-m-d'));

            $data = $data->whereDate('document_date', '>=',$fyear['start_date'])
                ->whereDate('document_date', '<=',$fyear['end_date']);
                $start = $fyear['start_date'];
                $end = $fyear['end_date'];
            
        
        }

        
        
        
        
        
        $data=$data->get();
        $assetCodes = FixedAssetRevImp::withDefaultGroupCompanyOrg()->get();
        
        return view('fixed-asset.revaluation-impairement.index',compact('data','assetCodes'));
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_revaluation-impairement";
        $series = [];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        $categories = ErpAssetCategory::withDefaultGroupCompanyOrg()->where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        
        $group = Group::withDefaultGroupCompanyOrg()->where('name', $group_name)->first() ?: Group::where('edit',0)->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                    }
                });
                })->get(); 
                $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
                $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
                $organization = Helper::getAuthenticatedUser()->organization;
                $dep_percentage = $organization->dep_percentage;
                $dep_type = $organization->dep_type;
                $dep_method = $organization->dep_method;
                $locations = ErpStore::withDefaultGroupCompanyOrg()->where('status','active')->get();
       
                return view('fixed-asset.revaluation-impairement.create', compact('locations','assets','series','assets', 'categories','ledgers','financialEndDate','financialStartDate','dep_percentage','dep_type','dep_method'));
       
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $status = ($request->document_status === ConstantHelper::SUBMITTED)
            ? Helper::checkApprovalRequired($request->book_id)
            : $request->document_status;

        $additionalData = [
            'created_by' => $user->auth_user_id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'currency_id'=>$user->organization->currency_id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
            'document_status' => $status,
            'approval_level' => 1,
            'revision_number' => 0,
        ];

        $data = array_merge($request->all(), $additionalData);
        
        DB::beginTransaction();
        
        try {
            $asset = FixedAssetRevImp::create($data);

            if ($asset->document_status == ConstantHelper::SUBMITTED) {
                Helper::approveDocument($request->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
            }

            if ($asset->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED || $asset->document_status == ConstantHelper::APPROVED) {
                Helper::approveDocument($request->book_id, $asset->id, $asset->revision_number, "", null, 1, 'approve', 0, get_class($asset));
            }

            DB::commit();
            return redirect()->route("finance.fixed-asset.revaluation-impairement.index")->with('success', 'Asset Rev/Imp successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("finance.fixed-asset.revaluation-impairement.create")->with('error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $r, string $id)
    {
        $parentURL = "fixed-asset_revaluation-impairement";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $currNumber = $r->revisionNumber;
        if ($currNumber) {
            $data= FixedAssetRevImpHistory::withDefaultGroupCompanyOrg()->findorFail($id);
        } else {
            $data= FixedAssetRevImp::withDefaultGroupCompanyOrg()->findorFail($id);
        }
        $revision_number = $data->revision_number;
        
        $userType = Helper::userCheck();
        
        $buttons = Helper::actionButtonDisplay($data->book_id,$data->document_status , $data->id, $data->current_value, 
        $data->approval_level, $data -> created_by ?? 0, $userType['type'], $revision_number);
        
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        $revNo = $data->revision_number;
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $data->id, $revNo,$data->current_value,$data->created_by);
        
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        
        $locations = ErpStore::withDefaultGroupCompanyOrg()->where('status','active')->get();
       
        return view('fixed-asset.revaluation-impairement.show', compact('locations','assets','data', 'buttons', 'docStatusClass', 'approvalHistory','revision_number'));
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,$id)
    {
        $parentURL = "fixed-asset_revaluation-impairement";
        $series = [];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        $categories = ErpAssetCategory::withDefaultGroupCompanyOrg()->where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;

        
        $group = Group::withDefaultGroupCompanyOrg()->where('name', $group_name)->first() ?: Group::where('edit',0)->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                    }
                });
                })->get(); 
                $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
                $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
                $organization = Helper::getAuthenticatedUser()->organization;
                $dep_percentage = $organization->dep_percentage;
                $dep_type = $organization->dep_type;
                $dep_method = $organization->dep_method;
                $data = FixedAssetRevImp::find($id);
                $locations = ErpStore::withDefaultGroupCompanyOrg()->where('status','active')->get();
       
                return view('fixed-asset.revaluation-impairement.edit', compact('locations','data','assets','series','assets', 'categories','ledgers','financialEndDate','financialStartDate','dep_percentage','dep_type','dep_method'));
       
        
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $user = Helper::getAuthenticatedUser();
    $asset = FixedAssetRevImp::findOrFail($id);

    $status = ($request->document_status === ConstantHelper::SUBMITTED)
        ? Helper::checkApprovalRequired($asset->book_id)
        : $request->document_status;

    $additionalData = [
        'document_status' => $status,
    ];

    $data = array_merge($request->all(), $additionalData);

    DB::beginTransaction();

    try {
        $asset->update($data);

        if ($status == ConstantHelper::SUBMITTED) {
            Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
        }

        if ($status == ConstantHelper::APPROVAL_NOT_REQUIRED || $status == ConstantHelper::APPROVED) {
            Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'approve', 0, get_class($asset));
        }

        DB::commit();
        return redirect()->route("finance.fixed-asset.revaluation-impairement.index")->with('success', 'Asset Rev/Imp updated successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route("finance.fixed-asset.revaluation-impairement.edit", $id)->with('error', $e->getMessage());
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = FixedAssetRevImp::find($request->id);
            $bookId = $doc->book_id; 
            $docId = $doc->id;
            $docValue = $doc->current_value;
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
        DB::beginTransaction();
        $register = FixedAssetRevImp::makeRegistration((int)$request -> document_id);
        if($register['status']){
        try {

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
    else{
        DB::rollBack();
        return response() -> json([
            'status' => false,
            'message' => $register['message'],
            'error' =>  $register['message']
        ]);

    }
    }
    public function amendment(Request $request, $id)
    {
        $asset_id = FixedAssetRevImp::find($id);
        if (!$asset_id) {
            return response()->json([
                "data" => [],
                "message" => "Rev/Imp not found.",
                "status" => 404,
            ]);
        }

        $revisionData = [
            [
                "model_type" => "header",
                "model_name" => "FixedAssetRevImp",
                "relation_column" => "",
            ],
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
