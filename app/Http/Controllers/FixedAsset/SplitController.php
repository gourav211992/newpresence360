<?php

namespace App\Http\Controllers\FixedAsset;
use Exception;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetSplit;
use App\Models\FixedAssetRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ErpAssetCategory;
use App\Models\FixedAssetSub;
use App\Models\Group;
use App\Models\Ledger;
class SplitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentURL = "fixed-asset_split";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $data=FixedAssetSplit::withDefaultGroupCompanyOrg()->orderBy('id','desc');
        if($request->filter_asset)
        $data=$data->where('asset_id',$request->filter_asset);
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
        $assetCodes = FixedAssetSplit::withDefaultGroupCompanyOrg()->pluck('asset_id')->unique();
        $assetCodes = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('id', $assetCodes)->get();
        $ledgers = FixedAssetSplit::withDefaultGroupCompanyOrg()->pluck('ledger_id')->unique();
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->whereIn('id', $ledgers)->get();
        
        return view('fixed-asset.split.index',compact('data','assetCodes','ledgers',));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_split";
        $series = [];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::SUBMITTED])->get();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->where('organization_id', Helper::getAuthenticatedUser()->organization_id)->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Group::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->where('name', $group_name)->first() ?: Group::whereNull('organization_id')->where('name', $group_name)->first();
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
        return view('fixed-asset.split.create', compact('series','assets', 'categories','ledgers','financialEndDate','financialStartDate','dep_percentage','dep_type','dep_method'));
       
        
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
        $parentURL = "fixed-asset_registration";
        
        
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->first();
        

        
        DB::beginTransaction();

        try {
            $asset = FixedAssetSplit::create($data);

            if ($asset->document_status == ConstantHelper::SUBMITTED) {
                Helper::approveDocument($request->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
            }

            if ($asset->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED || $asset->approvalStatus == ConstantHelper::APPROVED) {
                Helper::approveDocument($request->book_id, $asset->id, $asset->revision_number, "", null, 1, 'approve', 0, get_class($asset));
            }
            foreach ($grouped as $assetCode => $items) {
                $firstItem = $items->first();
                if($series!=null){
                $book = Helper::generateDocumentNumberNew($series->id, date('Y-m-d'));
                if($book['document_number']!=null){
                $existingAsset = FixedAssetRegistration::where('asset_code', $assetCode)
                    ->where('organization_id', $user->organization->id)
                    ->where('group_id', $user->organization->group_id)
                    ->first();
                
                if($existingAsset){
                    return redirect()
                    ->route('finance.fixed-asset.split.create')
                    ->withInput()
                    ->withErrors('Asset Code '.$existingAsset->asset_code . ' already exists.');
                }

                $asset = FixedAssetRegistration::find($request->asset_id);
                

                    
                // Step 1: Create main asset registration (only once per asset_code)
                $mainAsset = FixedAssetRegistration::create([
                    'organization_id' => $user->organization->id,
                    'group_id' => $user->organization->group_id,
                    'company_id' => $user->organization->company_id,
                    'book_id' => $series->id,
                    'document_number'=>$book['document_number'],
                    'document_date' => date('Y-m-d'),
                    'doc_number_type' => $book['type'],
                    'doc_reset_pattern' => $book['reset_pattern'],
                    'doc_prefix' => $book['prefix'],
                    'doc_suffix' => $book['suffix'],
                    'doc_no' => $book['doc_no'],
                    'asset_code' => $assetCode,
                    'asset_name' => $firstItem->asset_name,
                    'quantity' => $items->sum('quantity'),
                    'catrgory_id'=>$request->category_id,
                    'ledger_id' => $request->ledger_id,
                    'ledger_group_id' => $request->ledger_group_id,
                    'mrn_header_id'=> $asset->mrn_header_id,
                    'mrn_detail_id'=> $asset->mrn_detail_id,
                    'capitalize_date' => $request->capitalize_date,
                    'last_dep_date'=> $request->capitalize_date,
                    'vendor_id'=> $asset->vendor_id,
                    'currency_id'=> $asset->currency_id,
                    'supplier_invoice_no'=> $asset->supplier_invoice_no,
                    'supplier_invoice_date'=> $asset->supplier_invoice_date,
                    'book_date'=>$asset->book_date,
                    'maintenance_schedule' => $request->maintenance_schedule,
                    'depreciation_method' => $request->depreciation_method,
                    'useful_life' => $request->useful_life,
                    'salvage_value' => $request->salvage_value,
                    'depreciation_percentage' => $request->depreciation_percentage,
                    'depreciation_percentage_year' => $request->depreciation_percentage,
                    'total_depreciation' => $request->total_depreciation,
                    'dep_type' => $asset->dep_type,
                    'current_value' => $items->sum('current_value'),
                    'current_value_after_dep' => $items->sum('current_value'),
                    'document_status' => Helper::checkApprovalRequired($series->id),
                    'approval_level' => 1,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'created_by' => $user->auth_user_id,
                    'type' => get_class($user),
                    'status' => 'active',

            ]);
    
                // Step 2: Create sub-assets under main asset
                foreach ($items as $subAsset) {
                    FixedAssetSub::create([
                        'parent_id' => $mainAsset->id,
                        'sub_asset_code' => $subAsset->sub_asset_id,
                        'quantity' => $subAsset->quantity,
                        'current_value' => $subAsset->current_value,
                        'current_value_after_dep' => $subAsset->current_value,
                    ]);
                }
            }
        }
            }

            DB::commit();
            return redirect()->route("finance.fixed-asset.split.index")->with('success', 'Asset Split successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("finance.fixed-asset.split.create")->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parentURL = "fixed-asset_split";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $data = FixedAssetSplit::withDefaultGroupCompanyOrg()->findOrFail($id);
        $revision_number = $data->revision_number;
        $userType = Helper::userCheck();
        
        $buttons = Helper::actionButtonDisplay($data->book_id,$data->document_status , $data->id, $data->current_value, 
        $data->approval_level, $data -> created_by ?? 0, $userType['type'], $revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        $revNo = $data->revision_number;
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $data->id, $revNo,$data->current_value,$data->created_by);
        

        
        return view('fixed-asset.split.show', compact('data', 'buttons', 'docStatusClass', 'approvalHistory'));
        
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
            $doc = FixedAssetSplit::find($request->id);
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
