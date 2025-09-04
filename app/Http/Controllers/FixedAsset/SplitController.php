<?php

namespace App\Http\Controllers\FixedAsset;

use Exception;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetSplit;
use App\Models\FixedAssetSplitHistory;
use App\Models\FixedAssetRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\FinancialPostingHelper;
use App\Models\ErpAssetCategory;
use App\Models\FixedAssetSub;
use App\Models\ErpStore;
use App\Models\Group;
use App\Models\Ledger;
use App\Helpers\InventoryHelper;

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

        $data = FixedAssetSplit::orderBy('id', 'desc');
        if ($request->filter_asset)
            $data = $data->where('asset_id', $request->filter_asset);
        if ($request->filter_ledger)
            $data = $data->where('ledger_id', $request->filter_ledger);
        if ($request->filter_status)
            $data = $data->where('document_status', $request->filter_status);
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
        $assetCodes = FixedAssetSplit::pluck('asset_id')->unique();
        $assetCodes = FixedAssetRegistration::whereIn('id', $assetCodes)->get();
        $ledgers = FixedAssetSplit::pluck('ledger_id')->unique();
        $ledgers = Ledger::whereIn('id', $ledgers)->get();
       
        return view('fixed-asset.split.index', compact('data', 'assetCodes', 'ledgers'));
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
        $assets = FixedAssetRegistration::whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->whereHas('assets')->select('id', 'name')->get();
        $new_categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                    }
                });
        })->get();
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $organization = Helper::getAuthenticatedUser()->organization;
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;
        $dep_method = $organization->dep_method;
        $locations = InventoryHelper::getAccessibleLocations();
        $groups = Group::whereIn('id', $allChildIds)->get();
        $fy_months = Helper::getCurrentFinancialYearMonths();


        return view('fixed-asset.split.create', compact('groups', 'locations', 'series', 'assets', 'categories', 'new_categories', 'ledgers', 'financialEndDate', 'financialStartDate', 'dep_percentage', 'dep_type', 'dep_method', 'fy_months'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $grouped = collect(json_decode($request->sub_assets))->groupBy('asset_code');
        foreach ($grouped as $assetCode => $items) {
            $existingAsset = FixedAssetRegistration::where('asset_code', $assetCode)->first();

            if ($existingAsset) {
                return redirect()
                    ->route('finance.fixed-asset.split.create')
                    ->withInput()
                    ->withErrors('Asset Code# ' . $existingAsset->asset_code . ' already exists.');
            }
        }
        $status = $request->document_status;

        $additionalData = [
            'created_by' => $user->auth_user_id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'currency_id' => $user->organization->currency_id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
            'document_status' => $status,
            'approval_level' => 1,
            'revision_number' => 0,
        ];

        $data = array_merge($request->all(), $additionalData);




        DB::beginTransaction();

        try {
            $asset = FixedAssetSplit::create($data);

            if ($asset->document_status == ConstantHelper::SUBMITTED) {
                $doc = Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
                $asset->document_status = $doc['approvalStatus'] ?? $asset->document_status;
                $asset->save();
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
    public function show(Request $r, string $id)
    {
        $organization = Helper::getAuthenticatedUser()->organization;
        $parentURL = "fixed-asset_split";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $data = FixedAssetSplit::with([
            'subAsset' => function ($query) {
                $query->withTrashed();
            }
        ])->findorFail($id);

        $currNumber = $r->has('revisionNumber');
        if ($currNumber && $data->revision_number != $r->revisionNumber) {
            $currNumber = $r->revisionNumber;
            $data = FixedAssetSplitHistory::where('source_id', $id)
                ->where('revision_number', $currNumber)->first();
        } else {
            $data = FixedAssetSplit::with([
                'subAsset' => function ($query) {
                    $query->withTrashed();
                }
            ])->findorFail($id);
        }
        $revision_number = $data->revision_number;
        $userType = Helper::userCheck();

        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $id,
            $data->current_value,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );
      

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        $revNo = $data->revision_number;
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $id, $revNo, $data->current_value, $data->created_by);
        $locations = InventoryHelper::getAccessibleLocations();

        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;

        $ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                    }
                });
        })->get();
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;
        $dep_method = $organization->dep_method;
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $groups = Group::whereIn('id', $allChildIds)->get();





        return view('fixed-asset.split.show', compact('groups', 'ledgers', 'categories', 'locations', 'data', 'buttons', 'docStatusClass', 'approvalHistory', 'revision_number', 'dep_percentage', 'dep_method', 'dep_type', 'financialStartDate', 'financialEndDate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = FixedAssetSplit::find($id);
        $parentURL = "fixed-asset_split";
        $series = [];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $assets = FixedAssetRegistration::whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                    }
                });
        })->get();
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $organization = Helper::getAuthenticatedUser()->organization;
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;
        $dep_method = $organization->dep_method;
        $locations = InventoryHelper::getAccessibleLocations();
        $groups = Group::whereIn('id', $allChildIds)->get();
        $revision_number = $data->revision_number;
        $userType = Helper::userCheck();

        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $id,
            $data->current_value,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );

        return view('fixed-asset.split.edit', compact('buttons', 'groups', 'locations', 'data', 'series', 'assets', 'categories', 'ledgers', 'financialEndDate', 'financialStartDate', 'dep_percentage', 'dep_type', 'dep_method'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $asset = FixedAssetSplit::findOrFail($id);
        $user = Helper::getAuthenticatedUser();
        $status = $request->document_status;

        $additionalData = [
            'document_status' => $status,
        ];

        $data = array_merge($request->all(), $additionalData);
        $grouped = collect(json_decode($request->sub_assets))->groupBy('asset_code');
        foreach ($grouped as $assetCode => $items) {
            $existingAsset = FixedAssetRegistration::where('asset_code', $assetCode)->first();

            if ($existingAsset) {
                return redirect()
                    ->route('finance.fixed-asset.split.edit', $id)
                    ->withInput()
                    ->withErrors('Asset Code# ' . $existingAsset->asset_code . ' already exists.');
            }
        }

        DB::beginTransaction();

        try {
            if ($request->action_type == "amendment") {
                $revisionData = [
                    [
                        "model_type" => "header",
                        "model_name" => "FixedAssetSplit",
                        "relation_column" => "",
                    ],
                ];
                Helper::documentAmendment($revisionData, $id);
                Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, $request->amend_remarks, $request->file('amend_attachment'), $asset->approval_level, 'amendment', 0, get_class($asset));
                $data['revision_number'] = $asset->revision_number + 1;
                $data['revision_date']=now();
            }

            $asset->update($data);

            if ($asset->document_status == ConstantHelper::SUBMITTED) {
                $doc = Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
                $asset->document_status = $doc['approvalStatus'] ?? $asset->document_status;
                $asset->save();
            }

            DB::commit();
            return redirect()->route("finance.fixed-asset.split.index")->with('success', 'Asset Split updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("finance.fixed-asset.split.edit", $id)->with('error', $e->getMessage());
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
            $doc = FixedAssetSplit::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = $doc->current_value;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
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
            $data = FinancialPostingHelper::financeVoucherPosting((int) $request->book_id ?? 0, $request->document_id ?? 0, $request->type ?? 'get');
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
        DB::beginTransaction();
        try {

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
        $asset_id = FixedAssetSplit::find($id);
        if (!$asset_id) {
            return response()->json([
                "data" => [],
                "message" => "Split not found.",
                "status" => 404,
            ]);
        }

        $revisionData = [
            [
                "model_type" => "header",
                "model_name" => "FixedAssetSplit",
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
