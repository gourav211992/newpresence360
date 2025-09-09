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
use App\Exports\FailedFAExport;
use App\Imports\FAImport;
use App\Exports\FAExport;
use App\Mail\ImportComplete;
use Illuminate\Support\Facades\Mail;
use App\Services\FAImportExportService;



use App\Models\UploadFAMaster;
use App\Models\Group;
use App\Models\Book;
use App\Models\FixedAssetSetup;

class RegistrationController extends Controller
{
    protected $FAImportExportService;

    public function __construct(FAImportExportService $FAImportExportService)
    {
        $this->FAImportExportService = $FAImportExportService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $parentURL = "fixed-asset_registration";

        $data = FixedAssetRegistration::orderBy('id', 'desc');

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        if ($request->filter_asset)
            $data = $data->where('id', $request->filter_asset);
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
        $assetCodes = FixedAssetRegistration::get();
        $ledgers = FixedAssetRegistration::pluck('ledger_id')->unique();
        $ledgers = Ledger::whereIn('id', $ledgers)->get();
        return view('fixed-asset.registration.index', compact('data', 'assetCodes', 'ledgers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = request()->segments()[0];
        $parentURL = "fixed-asset_registration";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $organization = Helper::getAuthenticatedUser()->organization;
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $fy_months = Helper::getCurrentFinancialYearMonths();
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


        $grns = MrnHeader::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->whereHas('items', function ($q) {
                $q->whereHas('item', function ($q) {
                    $q->where('is_asset', 1);
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
        })->whereHas('item', function ($q) {
            $q->where('is_asset', 1);
        })->where('basic_value', '>', 0)->doesntHave('asset')->get();

        $vendors = Vendor::select('id', 'display_name as name','currency_id')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'short_name as name')->get();
        $dep_method = $organization->dep_method;
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;

        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $categories = ErpAssetCategory::where('status', 1)
            ->whereHas('setup', function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('act_type', '!=', 'income_tax')
                        ->orWhereNull('act_type');
                });
            })
            ->select('id', 'name')
            ->get();
        $it_categories = ErpAssetCategory::where('status', 1)
            ->whereHas('setup', function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('act_type', 'income_tax');
                });
            })->get();

        $locations = InventoryHelper::getAccessibleLocations();


        return view('fixed-asset.registration.create', compact('locations', 'series', 'ledgers', 'categories', 'it_categories', 'grns', 'vendors', 'currencies', 'grn_details', 'dep_method', 'dep_percentage', 'dep_type', 'financialEndDate', 'financialStartDate','fy_months'));
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
        //$code = self::generateAssetCode($request);
        $code = $request->asset_code;
        $existingAsset = FixedAssetRegistration::where('asset_code', $code)->first();

        if ($existingAsset) {
            return redirect()
                ->route('finance.fixed-asset.registration.create')
                ->withInput()
                ->withErrors('Asset Code ' . $code . ' already exists.');
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
            'asset_code' => $code,
            'current_value_after_dep' => $request->current_value,
            'brand_name'=>$request->brand_name,
            'model_no'=>$request->model_no,
            'batch_number'=>$request->batch_number,
            'manufactering_year'=>$request->manufactering_year,
            'vendor_id'=>$request->vendor_id,
            'tax'=>$request->tax,
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
            if ($request->has('prefix') && $request->prefix != "")
                FixedAssetSetup::updatePrefix($asset->id, $request->prefix);

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
        $data = FixedAssetRegistration::findorFail($id);
        $currNumber = $r->has('revisionNumber');
        if ($currNumber && $data->revision_number!=$r->revisionNumber) {
            $currNumber = $r->revisionNumber;
            $data = FixedAssetRegistrationHistory::where('source_id', $id)
                ->where('revision_number', $currNumber)->first();
        } else {
            $data = FixedAssetRegistration::findorFail($id);
        }





        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;

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

        $grns = MrnHeader::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->whereHas('items')->whereHas('vendor')->get();
        $grn_details = MrnDetail::withwhereHas('header', function ($query) {
            $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->get();
        $vendors = Vendor::select('id', 'display_name as name')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'short_name as name')->get();
        $sub_assets = FixedAssetSub::where('parent_id', $id)->get();
        $revNo = $data->revision_number;
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $id, $revNo, $data->current_value,$data->created_by);

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
        $categories = ErpAssetCategory::where('status', 1)
            ->whereHas('setup', function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('act_type', '!=', 'income_tax')
                        ->orWhereNull('act_type');
                });

            })
            ->select('id', 'name')
            ->get();
        $it_categories = ErpAssetCategory::where('status', 1)
            ->whereHas('setup', function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('act_type', 'income_tax');
                });
            })->get();

        return view('fixed-asset.registration.show', compact('categories', 'ref_view_route', 'locations', 'sub_assets', 'series', 'data', 'ledgers', 'categories', 'it_categories', 'grns', 'vendors', 'currencies', 'grn_details', 'buttons', 'docStatusClass', 'revision_number', 'currNumber', 'approvalHistory'));
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
        $data = FixedAssetRegistration::findorFail($id);
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $organization = Helper::getAuthenticatedUser()->organization;
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

        $grns = MrnHeader::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->whereHas('vendor')->get();
        $grn_details = MrnDetail::with('header')->whereHas('header', function ($query) {
            $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->get();
        $vendors = Vendor::select('id', 'display_name as name')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'short_name as name')->get();
        $sub_assets = FixedAssetSub::where('parent_id', $id)->get();
        $dep_method = $organization->dep_method;
        $dep_percentage = $organization->dep_percentage;
        $dep_type = $organization->dep_type;
        $categories = ErpAssetCategory::where('status', 1)
            ->whereHas('setup', function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('act_type', '!=', 'income_tax')
                        ->orWhereNull('act_type');
                });

            })
            ->select('id', 'name')
            ->get();
        $it_categories = ErpAssetCategory::where('status', 1)
            ->whereHas('setup', function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('act_type', 'income_tax');
                });
            })
            ->select('id', 'name')
            ->get();
        $financialEndDate = Helper::getFinancialYear(date('Y-m-d'))['end_date'];
        $financialStartDate = Helper::getFinancialYear(date('Y-m-d'))['start_date'];
        $locations = InventoryHelper::getAccessibleLocations();
        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;
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
        return view('fixed-asset.registration.edit', compact('buttons', 'locations', 'sub_assets', 'series', 'data', 'ledgers', 'categories', 'it_categories', 'grns', 'vendors', 'currencies', 'grn_details', 'financialEndDate', 'dep_type', 'dep_method', 'dep_percentage', 'financialStartDate'));
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
        $request->merge([
            'asset_id' => $id,
        ]);
        $code = $request->asset_code;
        $existingAsset = FixedAssetRegistration::where('asset_code', $code)->where('id', '!=', $id)->first();

        if ($existingAsset) {
            redirect()
                ->route('finance.fixed-asset.registration.edit', $id)
                ->withInput()
                ->withErrors('Asset Code ' . $existingAsset->asset_code . ' already exists.');
        }

        $request->merge(['last_dep_date' => $request->capitalize_date]);
        $request->merge(['current_value_after_dep' => $request->current_value]);
        $request->merge(['asset_code' => $code]);
        $data = $request->all();
        $data['last_dep_date'] = $request->capitalize_date;
        DB::beginTransaction();


        // Update the asset
        try {
            if ($request->action_type == "amendment") {
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
                Helper::documentAmendment($revisionData, $id);
                Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, $request->amend_remarks, $request->file('amend_attachment'), $asset->approval_level, 'amendment', 0, get_class($asset));
                $data['revision_number'] = $asset->revision_number + 1;
                $data['revision_date']=now();
            }
            
            
            $asset->update($data);
            if ($asset->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($asset->book_id, $asset->id, $asset->revision_number, "", null, 1, 'submit', 0, get_class($asset));
                $asset->document_status = $doc['approvalStatus'] ?? $asset->document_status;
                $asset->save();
            }
            FixedAssetSub::regenerateSubAssets($asset->id, $asset->asset_code, $asset->quantity, $asset->current_value, $asset->salvage_value);
            if ($request->has('prefix') && $request->prefix != "")
                FixedAssetSetup::updatePrefix($asset->id, $request->prefix);
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
        })->whereHas('item', function ($q) {
            $q->where('is_asset', 1);
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
        $referrer = $request->headers->get('referer'); // full referrer URL
        $module = "";
        if ($referrer) {
            $path = parse_url($referrer, PHP_URL_PATH); // e.g. "/fixed-asset/split/5/edit"
            $segments = explode('/', trim($path, '/')); // ['fixed-asset', 'split', '5', 'edit']
            $module = $segments[1] ?? null;
        }
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

        $query = FixedAssetRegistration::where(function ($query) {
            $query->where('document_status', ConstantHelper::POSTED)
                ->orWhereNotNull('reference_doc_id');
        })
            //->whereNotNull('capitalize_date')
            ->where('asset_code', 'like', "%$q%")
            ->withWhereHas('subAsset', function ($query) use ($oldAssets, $module) {
                $query->whereNotIn('id', $oldAssets);
                $query->where('current_value_after_dep', '>', 0);

                if ($module == "split") {
                    $query->whereNotNull('capitalize_date');
                    $query->whereNotNull('expiry_date');
                    $query->whereColumn('expiry_date', '>', 'last_dep_date');
                } else if ($module == "merger") {
                    $query->whereNull('capitalize_date');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('expiry_date')
                            ->orWhereColumn('expiry_date', '>', 'last_dep_date');
                    });
                }
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
        $query = ErpAssetCategory::where('status', 1)->withWhereHas('setup')
            ->where('name', 'like', "%$q%");
        return $query->limit(20)->get();
    }
    public function checkCode(Request $request)
    {
        if ($request->edit_id)
            $exists = FixedAssetRegistration::where('asset_code', $request->code)->where('id', '!=', $request->edit_id)->exists();
        else
            $exists = FixedAssetRegistration::where('asset_code', $request->code)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function subAssetSearch(Request $request)
    {

        $Id = $request->id;
        $q = $request->q;
        $referrer = $request->headers->get('referer'); // full referrer URL
        $module = "";
        if ($referrer) {
            $path = parse_url($referrer, PHP_URL_PATH); // e.g. "/fixed-asset/split/5/edit"
            $segments = explode('/', trim($path, '/')); // ['fixed-asset', 'split', '5', 'edit']
            $module = $segments[1] ?? null;
        }

        $oldAssets = FixedAssetSub::oldSubAssets();
        if ($request->merger)
            $oldAssets = FixedAssetSub::oldSubAssets($request->merger, null);
        if ($request->split)
            $oldAssets = FixedAssetSub::oldSubAssets(null, $request->split);

        $Id = $request->input('id');

        $query = FixedAssetSub::where('parent_id', $Id)
            ->whereNotIn('id', $oldAssets)->with('asset')
            ->where('current_value_after_dep', '>', 0)
            ->where('sub_asset_code', 'like', "%$q%");

        if ($module == "split") {
            $query->whereNotNull('capitalize_date');
            $query->whereNotNull('expiry_date');
            $query->whereColumn('expiry_date', '>', 'last_dep_date');
        } else if ($module == "merger") {
            $query->whereNull('capitalize_date');
        } else {
            $query->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhereColumn('expiry_date', '>', 'last_dep_date');
            });
        }

        return $query->limit(20)
            ->get();
    }
    public function getCategories(Request $request)
    {
        $query = FixedAssetRegistration::where(function ($query) {
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
        $locationIds = FixedAssetRegistration::where(function ($query) {
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
        $locationId = $request->input('location_id');
        $locationIds = FixedAssetRegistration::where(function ($query) {
            $query->where('document_status', ConstantHelper::POSTED)
                ->orWhereNotNull('reference_doc_id');
        })
            ->where('category_id', $categoryId)
            ->where('location_id', $locationId)
            ->pluck('cost_center_id')->unique()->toArray();
        $costCenters = CostCenter::whereIn('id', $locationIds)
            ->where('status', 'active')
            ->get(['id', 'name']);

        return response()->json($costCenters);
    }
    public function export(Request $request)
    {
        $data = FixedAssetSub::whereHas('asset', function ($query) use ($request) {
            if ($request->filled('filter_asset')) {
                $query->where('id', $request->filter_asset);
            }

            if ($request->filled('filter_ledger')) {
                $query->where('ledger_id', $request->filter_ledger);
            }

            if ($request->filled('filter_status')) {
                $query->where('document_status', $request->filter_status);
            }

            if ($request->filled('date')) {
                [$start, $end] = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($start));
                $end = date('Y-m-d', strtotime($end));
            } else {
                $fyear = Helper::getFinancialYear(now());
                $start = $fyear['start_date'];
                $end = $fyear['end_date'];
            }

            $query->whereBetween('document_date', [$start, $end]);
            $query->orderBy('document_date', 'desc');
        });
        $data = $data->get();

        return Excel::download(new FixedAssetReportExport($data), 'FixedAsset.xlsx');
    }
    public function exportSuccessfulItems()
    {
        $uploadItems = UploadFAMaster::where('import_status', 'Success')
            ->get();
        $codes = $uploadItems->pluck('asset_code') ?? [];

        $data = FixedAssetSub::whereHas('asset', function ($query) use ($codes) {
            $query->whereIn('asset_code', $codes);
            $query->orderBy('document_date', 'desc');
        });
        $data = $data->get();
        return Excel::download(new FixedAssetReportExport($data), 'success-asset-import.xlsx');
    }

    public function exportFailedItems()
    {
        $failedItems = UploadFAMaster::where('import_status', 'Failed')
            ->get();
        return Excel::download(new FailedFAExport($failedItems), "failed-asset-items.xlsx");
    }
    public function showImportForm()
    {
        return view('fixed-asset.registration.import');
    }

    public function import(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:30720',
            ]);
            if (!$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }
            $file = $request->file('file');
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(filename: $file);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file format is incorrect or corrupted. Please upload a valid Excel file.',
                ], 400);
            }

            $sheet = $spreadsheet->getActiveSheet();
            $rowCount = $sheet->getHighestRow() - 1;
            if ($rowCount > 10000) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file contains more than 10000 items. Please upload a file with 10000 or fewer items.',
                ], 400);
            }
            if ($rowCount < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.',
                ], 400);
            }
            $expectedSeries = trim($sheet->getCell('A3')->getValue());

            for ($row = 4; $row <= $sheet->getHighestRow(); $row++) {
                $currentSeries = trim($sheet->getCell('A' . $row)->getValue());

                if ($currentSeries !== $expectedSeries) {
                    return response()->json([
                        'status' => false,
                        'message' => "Series mismatch at row {$row}. All rows must have the same Series",
                    ], 400);
                }
            }
            $parentURL = "fixed-asset_registration";

            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
            if (count($servicesBooks['services']) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Series not accesible.",
                ], 400);
            }
            $firstService = $servicesBooks['services'][0];
            $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
            if ($series->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => "Series not found.",
                ], 400);
            }
            $s_exists = in_array($expectedSeries, $series->pluck('book_code')->toArray());
            if (!$s_exists) {
                return response()->json([
                    'status' => false,
                    'message' => "Series '{$expectedSeries}' does not exist in the system. Expected series are: [" . implode(', ', $series->pluck('book_code')->toArray()) . ']',
                ], 400);
            }

            $book = $series->where('book_code', $expectedSeries)->first();


            $deleteQuery = UploadFAMaster::where('created_by', $user->id);
            $deleteQuery->delete();

            $import = new FAImport($this->FAImportExportService, $user, $book);
            Excel::import($import, $request->file('file'));

            $successfulItems = $import->getSuccessfulItems();
            $failedItems = $import->getFailedItems();
            $mailData = [
                'modelName' => 'FixedAssetRegistration',
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
                'export_successful_url' => route('finance.fixed-asset.export.successful'),
                'export_failed_url' => route('finance.fixed-asset.export.failed'),
            ];
            if (count($failedItems) > 0) {
                $message = 'Items import failed.';
                $status = 'failure';
            } else {
                $message = 'Items imported successfully.';
                $status = 'success';
            }
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new ImportComplete($mailData));
                } catch (\Exception $e) {
                    $message .= " However, there was an error sending the email notification.";
                }
            }
            return response()->json([
                'status' => $status,
                'message' => $message,
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 30MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import items: ' . $e->getMessage(),
            ], 500);
        }
    }
    public static function genrateDocNo($book)
    {
        if (!isset($book))
            return null;

        $numberPatternData = Helper::generateDocumentNumberNew($book->id, date('y-m-d'));
        if (!isset($numberPatternData))
            return null;

        return [
            'document_number' => $numberPatternData['document_number'],
            'book_id' => $book->id,
            'document_date' => date('Y-m-d'),
            'doc_number_type' => $numberPatternData['type'],
            'doc_reset_pattern' => $numberPatternData['reset_pattern'],
            'doc_prefix' => $numberPatternData['prefix'],
            'doc_suffix' => $numberPatternData['suffix'],
            'doc_no' => $numberPatternData['doc_no'],
        ];
    }
    public static function generateAssetCode(Request $request)
    {
        if (!$request->has('category') || $request->category == "")
            return "";

        $itemInitials = FixedAssetSetup::getPrefix($request->category);
        $itemId = $request->input('asset_id');
        $baseCode = $itemInitials;
        if ($itemId) {
            $existingItem = FixedAssetRegistration::find($itemId);
            if ($existingItem) {
                $existingItemCode = $existingItem->asset_code;
                $currentBaseCode = substr($existingItemCode, 0, strlen($baseCode));
                if ($currentBaseCode === $baseCode) {
                    return response()->json(['code' => $existingItemCode]);
                }
            }
        }

        $nextSuffix = '001';
        $finalItemCode = $baseCode . $nextSuffix;

        while (
            FixedAssetRegistration::where('asset_code', $finalItemCode)
                ->exists()
        ) {
            $nextSuffix = str_pad(intval($nextSuffix) + 1, 3, '0', STR_PAD_LEFT);
            $finalItemCode = $baseCode . $nextSuffix;
        }

        return response()->json(['code' => $finalItemCode, 'prefix' => $baseCode]);

    }
    public static function generateUniquePrefix(string $name): ?string
    {
        $clean = preg_replace('/[^A-Za-z\s]/', '', $name);
        $words = array_values(array_filter(explode(' ', strtoupper(trim($clean)))));

        if (empty($words)) {
            return null;
        }

        $take = static function (string $word, int $len): string {
            return substr($word . str_repeat('X', $len), 0, $len);
        };

        switch (count($words)) {
            case 1:
                return $take($words[0], 3);
            case 2:
                return $take($words[0], 2) . $take($words[1], 1);
            default:
                return $take($words[0], 1) . $take($words[1], 1) . $take($words[2], 1);
        }
    }


}
