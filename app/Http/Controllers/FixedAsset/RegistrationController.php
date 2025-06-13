<?php


namespace App\Http\Controllers\FixedAsset;

use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\CostCenterOrgLocations;
use App\Models\ErpAssetCategory;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\MrnDetail;
use App\Models\MrnHeader;
use App\Models\Vendor;
use App\Models\FixedAssetInsurance;
use App\Http\Requests\FixedAssetRegistrationRequest;
use App\Models\FixedAssetRegistration;
use App\Models\FixedAssetRegistrationHistory;
use App\Models\FixedAssetSub;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\FixedAssetMerger;
use App\Models\FixedAssetSplit;
use App\Models\FixedAssetDepreciation;
use App\Models\FixedAssetSubHistory;
use App\Models\FixedAssetRevImp;
use App\Models\CostCenter;
use Exception;
use App\Models\ErpStore;
use App\Helpers\InventoryHelper;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FixedAssetReportExport;
use App\Models\Group;


class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentURL = request()->segments()[0];
        $parentURL = "fixed-asset_registration";

        $data = FixedAssetRegistration::withDefaultGroupCompanyOrg()->orderBy('id', 'desc');

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
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
        return view('fixed-asset.registration.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = request()->segments()[0];
        $parentURL = "fixed-asset_registration";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        //  if (count($servicesBooks['services']) == 0) {
        //     return redirect() -> route('/');
        // }
        $organization = Helper::getAuthenticatedUser()->organization;
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child);
                    }
                });
        })->get();
        $categories = ErpAssetCategory::withDefaultGroupCompanyOrg()->where('status', 1)->whereHas('setup')->select('id', 'name')->get();

        $grns = MrnHeader::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->whereHas('items', function ($q) {
                $q->whereHas('item.subTypes.subType', function ($q) {
                    $q->where('name', 'Asset');
                })->doesntHave('asset');
                $q->where('basic_value', '>', 0);
            })
            ->whereHas('vendor')
            ->with(['items.item', 'vendor'])
            ->get();
        $grn_details = MrnDetail::with([
            'header.vendor',
            'item'
        ])->whereHas('header', function ($q) {
            $q->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->whereHas('item.subTypes.subType', function ($q) {
            $q->where('name', 'Asset');
        })->where('basic_value', '>', 0)->doesntHave('asset')->get();

        $vendors = Vendor::withDefaultGroupCompanyOrg()->select('id', 'display_name as name')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'short_name as name')->get();
        $dep_method = $organization->dep_method;
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;

        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $locations = InventoryHelper::getAccessibleLocations();


        return view('fixed-asset.registration.create', compact('locations', 'series', 'ledgers', 'categories', 'grns', 'vendors', 'currencies', 'grn_details', 'dep_method', 'dep_percentage', 'dep_type', 'financialEndDate', 'financialStartDate'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(FixedAssetRegistrationRequest $request)
    {
        // Validation is automatically handled by the FormRequest
        $validator = $request->validated();

        if (!$validator) {
            return redirect()
                ->route('finance.fixed-asset.registration.create')
                ->withInput()
                ->withErrors($request->errors());
        }
        $existingAsset = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $request->asset_code)->first();

        if ($existingAsset) {
            return redirect()
                ->route('finance.fixed-asset.registration.create')
                ->withInput()
                ->withErrors('Asset Code ' . $existingAsset->asset_code . ' already exists.');
        }

        $user = Helper::getAuthenticatedUser();
        $additionalData = [
            'created_by' => $user->auth_user_id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
            'last_dep_date' => $request->capitalize_date,
            'approval_level' => 1,
            'revision_number' => 0,
            'current_value_after_dep' => $request->current_value,
        ];

        $data = array_merge($request->all(), $additionalData);

        DB::beginTransaction();

        try {
            $asset = FixedAssetRegistration::create($data);
            FixedAssetSub::generateSubAssets($asset->id, $asset->asset_code, $asset->quantity, $asset->current_value, $asset->salvage_value);

            if ($asset->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
                $asset->document_status = $doc['approvalStatus'] ?? $asset->document_status;
                $asset->save();
            }

            DB::commit();
            return redirect()->route("finance.fixed-asset.registration.index")->with('success', 'Asset created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("finance.fixed-asset.registration.create")->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $r, string $id)
    {
        $parentURL = request()->segments()[0];
        $parentURL = "fixed-asset_registration";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $currNumber = $r->has('revisionNumber');
        if ($currNumber) {
            $currNumber = $r->revisionNumber;
            $data = FixedAssetRegistrationHistory::where('source_id', $id)
                ->where('revision_number', $currNumber)->first();
        } else {
            $data = FixedAssetRegistration::withDefaultGroupCompanyOrg()->findorFail($id);
        }





        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;

        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $data->id,
            $data->current_value,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';


        // if ($data->depreciations->count() != 0)
        //$buttons['amend'] = true;


        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child);
                    }
                });
        })->get();
        $categories = ErpAssetCategory::withDefaultGroupCompanyOrg()->where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $grns = MrnHeader::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->whereHas('items')->whereHas('vendor')->get();
        $grn_details = MrnDetail::withwhereHas('header', function ($query) {
            $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->get();
        $vendors = Vendor::withDefaultGroupCompanyOrg()->select('id', 'display_name as name')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'short_name as name')->get();
        $sub_assets = FixedAssetSub::where('parent_id', $id)->get();
        $revNo = $data->revision_number;
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }

        $approvalHistory = Helper::getApprovalHistory($data->book_id, $data->id, $revNo, $data->current_value, $data->created_by);


        $locations = InventoryHelper::getAccessibleLocations();

        $ref_view_route = "#";
        $buttons['reference'] = false;

        if ($data->reference_doc_id && $data->reference_series) {
            $model = Helper::getModelFromServiceAlias($data->reference_series);
            if ($model != null) {
                $referenceDoc = $model::find($data->reference_doc_id);
                if ($referenceDoc != null) {
                    $approvalHistory = Helper::getApprovalHistory($referenceDoc->book_id, $referenceDoc->id, $referenceDoc->revision_number);
                    $ref_view_route = Helper::getRouteNameFromServiceAlias($data->reference_series, $data->reference_doc_id);
                    $buttons['reference'] = true;
                    $buttons['post'] = false;
                    $buttons['amend'] = false;
                }
            }
        }

        return view('fixed-asset.registration.show', compact('ref_view_route', 'locations', 'sub_assets', 'series', 'data', 'ledgers', 'categories', 'grns', 'vendors', 'currencies', 'grn_details', 'buttons', 'docStatusClass', 'revision_number', 'currNumber', 'approvalHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $parentURL = request()->segments()[0];
        $parentURL = "fixed-asset_registration";



        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $data = FixedAssetRegistration::withDefaultGroupCompanyOrg()->findorFail($id);
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $organization = Helper::getAuthenticatedUser()->organization;
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child);
                    }
                });
        })->get();
        $categories = ErpAssetCategory::withDefaultGroupCompanyOrg()->where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        $grns = MrnHeader::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->whereHas('vendor')->get();
        $grn_details = MrnDetail::with('header')->whereHas('header', function ($query) {
            $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->get();
        $vendors = Vendor::withDefaultGroupCompanyOrg()->select('id', 'display_name as name')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'short_name as name')->get();
        $sub_assets = FixedAssetSub::where('parent_id', $id)->get();
        $dep_method = $organization->dep_method;
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $locations = InventoryHelper::getAccessibleLocations();

        return view('fixed-asset.registration.edit', compact('locations', 'sub_assets', 'series', 'data', 'ledgers', 'categories', 'grns', 'vendors', 'currencies', 'grn_details', 'financialEndDate', 'dep_type', 'dep_method', 'dep_percentage', 'financialStartDate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FixedAssetRegistrationRequest $request, $id)
    {
        $asset = FixedAssetRegistration::find($id);

        if (!$asset) {
            return redirect()
                ->route('finance.fixed-asset.registration.index')
                ->with('error', 'Asset not found.');
        }

        $validator = $request->validated();

        if (!$validator) {
            return redirect()
                ->route('finance.fixed-asset.registration.edit', $id)
                ->withInput()
                ->withErrors($request->errors());
        }
        $existingAsset = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $request->asset_code)->where('id', '!=', $id)->first();

        if ($existingAsset) {
            redirect()
                ->route('finance.fixed-asset.registration.edit', $id)
                ->withInput()
                ->withErrors('Asset Code ' . $existingAsset->asset_code . ' already exists.');
        }

        $request->merge(['last_dep_date' => $request->capitalize_date]);
        $request->merge(['current_value_after_dep' => $request->current_value]);
        $data = $request->all();
        $data['last_dep_date'] = $request->capitalize_date;
        DB::beginTransaction();


        // Update the asset
        try {
            $asset->update($data);
            if ($asset->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
                $asset->document_status = $doc['approvalStatus'] ?? $asset->document_status;
                $asset->save();
            }
            FixedAssetSub::regenerateSubAssets($asset->id, $asset->asset_code, $asset->quantity, $asset->current_value, $asset->salvage_value);
            DB::commit();
            return redirect()->route("finance.fixed-asset.registration.index")->with('success', 'Asset updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions
            return redirect()->route("finance.fixed-asset.registration.edit", $id)->with('error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getLedgerGroups(Request $request)
    {
        $ledgerId = $request->input('ledger_id');
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
    public function subAsset(Request $request)
    {
        $oldAssets = FixedAssetSub::oldSubAssets();
        if ($request->merger)
            $oldAssets = FixedAssetSub::oldSubAssets($request->merger, null);
        if ($request->split)
            $oldAssets = FixedAssetSub::oldSubAssets(null, $request->split);
        $Id = $request->input('id');
        $sub_asset = FixedAssetSub::where('parent_id', $Id)
            ->whereNotIn('id', $oldAssets)->with('asset');

        if ($sub_asset->count() > 0) {

            return response()->json($sub_asset->get());
        }

        return response()->json([], 404);
    }
    public function subAssetDetails(Request $request)
    {
        $Id = $request->input('id');
        $sub_asset_id = $request->input('sub_asset_id');
        $sub_asset = FixedAssetSub::where('parent_id', $Id)->where('id', $sub_asset_id)->with('asset')->first();
        if ($sub_asset) {
            return response()->json($sub_asset);
        }
        return response()->json([], 404);
    }
    public function fetchGrnData(Request $request)
    {
        $query = MrnDetail::with([
            'header.vendor',
            'item',
            'taxes'
        ])->whereHas('header', function ($q) {
            $q->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->whereHas('item.subTypes.subType', function ($q) {
            $q->where('name', 'Asset');
        })->doesntHave('asset')->where('basic_value', '>', 0);


        if ($request->grn_no) {
            $query->whereHas('header', function ($q) use ($request) {
                $q->where('document_number', $request->grn_no);
            });
        }

        if ($request->vendor_code) {
            $query->whereHas('header', function ($q) use ($request) {
                $q->where('vendor_code', $request->vendor_code);
            });
        }

        if ($request->vendor_name) {
            $query->whereHas('header.vendor', function ($q) use ($request) {
                $q->where('company_name', $request->vendor_name);
            });
        }

        if ($request->item_name) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('item_id', $request->item_name);
            });
        }

        $grn_details = $query->get();
        if ($request->grn_id) {
            $grn_details[] = MrnDetail::with([
                'header.vendor',
                'item',
                'taxes'
            ])->whereHas('header', function ($q) {
                $q->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
            })->where('basic_value', '>', 0)->find($request->grn_id);
        }
        $selected_grn_id = $request->grn_id ?? null;
        $html = view('fixed-asset.registration.grn_rows', compact('grn_details', 'selected_grn_id'))->render();

        return response()->json(['html' => $html]);
    }
    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = FixedAssetRegistration::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = $doc->current_value;
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
    public function amendment(Request $request, $id)
    {
        $asset_id = FixedAssetRegistration::find($id);
        if (!$asset_id) {
            return response()->json([
                "data" => [],
                "message" => "Fixed Asset not found.",
                "status" => 404,
            ]);
        }

        $revisionData = [
            [
                "model_type" => "header",
                "model_name" => "FixedAssetRegistration",
                "relation_column" => "",
            ],
            [
                "model_type" => "sub_detail",
                "model_name" => "FixedAssetSub",
                "relation_column" => "parent_id",
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


    public function assetSearch(Request $request)
    {
        $q = $request->input('q');
        $ids = $request->input('ids');
        $category = $request->input('category');
        $location = $request->input('location');
        $cost_center = $request->input('cost_center');

        $oldAssets = FixedAssetSub::oldSubAssets();

        if ($request->merger) {
            $oldAssets = FixedAssetSub::oldSubAssets($request->merger, null);
        }

        if ($request->split) {
            $oldAssets = FixedAssetSub::oldSubAssets(null, $request->split);
        }

        $query = FixedAssetRegistration::withDefaultGroupCompanyOrg()
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            })
            //->whereNotNull('capitalize_date')
            ->where('asset_code', 'like', "%$q%")
            ->withWhereHas('subAsset', function ($query) use ($oldAssets) {
                $query->whereNotIn('id', $oldAssets);
                $query->where('current_value_after_dep', '>', 0);
                //$query->whereNotNull('expiry_date');
                //$query->where('expiry_date','>','last_dep_date');
            });

        if (!empty($ids)) {
            $ids = array_map('intval', $ids);
            $query->whereNotIn('id', $ids);
        }

        if (!empty($category)) {
            $query->where('category_id', $category);
        }
        if (!empty($location)) {
            $query->where('location_id', $location);
        }
        if (!empty($cost_center)) {
            $query->where('cost_center_id', $cost_center);
        }

        return $query->limit(20)->get();
    }
    public function categorySearch(Request $request)
    {
        $q = $request->input('q');
        $query = ErpAssetCategory::withDefaultGroupCompanyOrg()->where('status', 1)->withWhereHas('setup')
            ->where('name', 'like', "%$q%");
        return $query->limit(20)->get();
    }
    public function checkCode(Request $request)
    {
        if ($request->edit_id)
            $exists = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $request->code)->where('id', '!=', $request->edit_id)->exists();
        else
            $exists = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $request->code)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function subAssetSearch(Request $request)
    {
        $Id = $request->id;
        $q = $request->q;

        $oldAssets = FixedAssetSub::oldSubAssets();
        if ($request->merger)
            $oldAssets = FixedAssetSub::oldSubAssets($request->merger, null);
        if ($request->split)
            $oldAssets = FixedAssetSub::oldSubAssets(null, $request->split);

        $Id = $request->input('id');

        return FixedAssetSub::where('parent_id', $Id)
            ->whereNotIn('id', $oldAssets)->with('asset')
            ->where('current_value_after_dep', '>', 0)
            ->where('sub_asset_code', 'like', "%$q%")
            //->whereNotNull('expiry_date')
            //->whereColumn('expiry_date', '>', 'last_dep_date')
            ->limit(20)
            ->get();
    }
    public function getCategories(Request $request)
    {
        $query = FixedAssetRegistration::withDefaultGroupCompanyOrg()
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            });

        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->cost_center_id) {
            $query->where('cost_center_id', $request->cost_center_id);
        }

        $categoryIds = $query->pluck('category_id')->unique()->toArray();

        $categories = ErpAssetCategory::whereIn('id', $categoryIds)
            ->get(['id', 'name']);

        return response()->json($categories);
    }
    public function refereshAssetsData()
    {
        FixedAssetRegistration::truncate();
        FixedAssetSub::truncate();
        FixedAssetMerger::truncate();
        FixedAssetSplit::truncate();
        FixedAssetDepreciation::truncate();
        FixedAssetRevImp::truncate();
    }
    public function getLocations(Request $request)
    {
        $categoryId = $request->input('category_id');
        $locationIds = FixedAssetRegistration::withDefaultGroupCompanyOrg()
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            })
            ->where('category_id', $categoryId)->pluck('location_id')->unique()->toArray();
        $locations = InventoryHelper::getAccessibleLocations()->map(function ($store) {
            return [
                'id' => $store['id'],
                'name' => $store['store_name'],
            ];
        });

        return response()->json($locations);
    }
    public function getCostCenters(Request $request)
    {
        $categoryId = $request->input('category_id');
        $locationId =  $request->input('location_id');
        $locationIds = FixedAssetRegistration::withDefaultGroupCompanyOrg()
            ->where(function ($query) {
                $query->where('document_status', ConstantHelper::POSTED)
                    ->orWhereNotNull('reference_doc_id');
            })
            ->where('category_id', $categoryId)
            ->where('location_id', $locationId)
            ->pluck('cost_center_id')->unique()->toArray();
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->whereIn('id', $locationIds)
            ->where('status', 'active')
            ->get(['id', 'name']);

        return response()->json($costCenters);
    }
    public function export(Request $r)
    {
        $data = FixedAssetRegistration::withDefaultGroupCompanyOrg()->get();
        return Excel::download(new FixedAssetReportExport($data), 'FixedAsset.xlsx');
    }
}
