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
use App\Models\FixedAssetMerger;
use Illuminate\Support\Facades\DB;
use Exception;


class MergerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentURL = "fixed-asset_merger";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }

        $data=FixedAssetMerger::withDefaultGroupCompanyOrg()->orderBy('id','desc');
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

        
        
        
        
        
        $data=$data->get();
        $assetCodes = FixedAssetMerger::withDefaultGroupCompanyOrg()->get();
        $ledgers = FixedAssetMerger::withDefaultGroupCompanyOrg()->pluck('ledger_id')->unique();
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->whereIn('id', $ledgers)->get();
        
        return view('fixed-asset.merger.index',compact('data','assetCodes','ledgers',));
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_merger";
        $series = [];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->where('organization_id', Helper::getAuthenticatedUser()->organization_id)->select('id', 'name')->get();
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
                $financialEndDate = Helper::getFinancialYear(\Carbon\Carbon::parse(date('Y-m-d'))->subYear()->format('Y-m-d'))['end_date'];
                $financialStartDate = Helper::getFinancialYear(\Carbon\Carbon::parse(date('Y-m-d'))->subYear()->format('Y-m-d'))['start_date'];
                $organization = Helper::getAuthenticatedUser()->organization;
                $dep_percentage = $organization->dep_percentage;
                $dep_type = $organization->dep_type;
                $dep_method = $organization->dep_method;
                return view('fixed-asset.merger.create', compact('assets','series','assets', 'categories','ledgers','financialEndDate','financialStartDate','dep_percentage','dep_type','dep_method'));
       
        
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
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
            'document_status' => $status,
            'approval_level' => 1,
            'revision_number' => 0,
        ];

        $data = array_merge($request->all(), $additionalData);
        $grouped = collect(json_decode($request->sub_assets))->groupBy('asset_code');
        $parentURL = request() -> segments()[0];
        $parentURL = "fixed-asset_merger";
        
        
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];

        
        DB::beginTransaction();
        $existingAsset = FixedAssetMerger::withDefaultGroupCompanyOrg()->where('asset_code', $request->asset_code)->exists();
    
    if($existingAsset){
        return redirect()
        ->route('finance.fixed-asset.merger.create')
        ->withInput()
        ->withErrors('Asset Code '.$existingAsset->asset_code . ' already exists.');
    }

        try {
            $asset = FixedAssetMerger::create($data);

            if ($asset->document_status == ConstantHelper::SUBMITTED) {
                Helper::approveDocument($request->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
            }

            if ($asset->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED || $asset->approvalStatus == ConstantHelper::APPROVED) {
                Helper::approveDocument($request->book_id, $asset->id, $asset->revision_number, "", null, 1, 'approve', 0, get_class($asset));
            }
            

            DB::commit();
            return redirect()->route("finance.fixed-asset.merger.index")->with('success', 'Asset Merge successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("finance.fixed-asset.merger.create")->with('error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parentURL = "fixed-asset_merger";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $data = FixedAssetMerger::withDefaultGroupCompanyOrg()->findOrFail($id);
        $revision_number = $data->revision_number;
        $userType = Helper::userCheck();
        
        $buttons = Helper::actionButtonDisplay($data->book_id,$data->document_status , $data->id, $data->current_value, 
        $data->approval_level, $data -> created_by ?? 0, $userType['type'], $revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        $revNo = $data->revision_number;
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $data->id, $revNo,$data->current_value,$data->created_by);
        
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        
        
        return view('fixed-asset.merger.show', compact('assets','data', 'buttons', 'docStatusClass', 'approvalHistory'));
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
            $doc = FixedAssetMerger::find($request->id);
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
}
