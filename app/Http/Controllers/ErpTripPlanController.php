<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\Inventory\StockReservation;
use App\Helpers\TransactionReportHelper;
use App\Helpers\UserHelper;
use App\Http\Requests\ErpTripPlanRequest;
use App\Models\Address;
use App\Helpers\DynamicFieldHelper;
use App\Lib\Services\WHM\planingJob;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\AuthUser;
use App\Models\Country;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpInvoiceItem;
use App\Models\ErpTripDynamicField;
use App\Models\ErpTripPlanHeader;
use App\Models\ErpTripPlanDetail;
use App\Models\ErpTripPlanHeaderHistory;
use App\Models\ErpTripPlanMedia;
use App\Models\ErpTripDynamicFields;
use App\Models\ErpMrItem;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpSaleOrder;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemDelivery;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\EwayBillMaster;
use App\Models\Hsn;
use App\Models\Item;
use App\Models\MfgOrder;
use App\Models\MoItem;
use App\Models\Organization;
use App\Models\PurchaseIndent;
use App\Models\Station;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\Vendor;
use Carbon\Carbon;
use PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration\Helper as ConfigurationHelper;
use App\Helpers\Configuration\Constants as ConfigurationConstant;
use App\Models\Configuration;
use Yajra\DataTables\DataTables;

class ErpTripPlanController extends Controller
{
    //
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::TRIP_SERVICE_ALIAS;
        $redirectUrl = route('trip-plan.index');
        $createRoute = route('trip-plan.create');
        $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $autoCompleteFilters = self::getBasicFilters();
        //Date Filters
        $dateRange = $request -> date_range ?? null;
        $typeName = "Trip Planning";
        if ($request -> ajax()) {
            try {
                $docs = ErpTripPlanHeader::withDefaultGroupCompanyOrg() ->  bookViewAccess($pathUrl) ->  
                withDraftListingLogic() -> whereIn('store_id',$accessible_locations)  -> when($request -> book_id, function ($bookQuery) use($request) {
                $bookQuery -> where('book_id', $request -> book_id);
            }) -> when($request -> document_number, function ($docQuery) use($request) {
                $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
            }) -> when($request -> location_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> location_id);
            }) -> when($request -> company_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> company_id);
            }) -> when($request -> organization_id, function ($docQuery) use($request) {
                $docQuery -> where('organization_id', $request -> organization_id);
            }) -> when($request -> status, function ($docStatusQuery) use($request) {
                $searchDocStatus = [];
                if ($request -> status === ConstantHelper::DRAFT) {
                    $searchDocStatus = [ConstantHelper::DRAFT];
                } else if ($request -> status === ConstantHelper::SUBMITTED) {
                    $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
                } else {
                    $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
                }
                $docStatusQuery -> whereIn('document_status', $searchDocStatus);
            }) -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
            $dateRanges = explode('to', $dateRange);
            if (count($dateRanges) == 2) {
                $fromDate = Carbon::parse(trim($dateRanges[0])) -> format('Y-m-d');
                $toDate = Carbon::parse(trim($dateRanges[1])) -> format('Y-m-d');
                $dateRangeQuery -> whereDate('document_date', ">=" , $fromDate) -> where('document_date', '<=', $toDate);
            }
            else{
                $fromDate = Carbon::parse(trim($dateRanges[0])) -> format('Y-m-d');
                $dateRangeQuery -> whereDate('document_date', $fromDate);
            }
            }) -> when($request -> item_id, function ($itemQuery) use($request) {
                $itemQuery -> withWhereHas('items', function ($itemSubQuery) use($request) {
                    $itemSubQuery -> where('item_id', $request -> item_id)
                    //Compare Item Category
                    -> when($request -> item_category_id, function ($itemCatQuery) use($request) {
                        $itemCatQuery -> whereHas('item', function ($itemRelationQuery) use($request) {
                            $itemRelationQuery -> where('category_id', $request -> category_id)
                            //Compare Item Sub Category
                            -> when($request -> item_sub_category_id, function ($itemSubCatQuery) use($request) {
                                $itemSubCatQuery -> where('subcategory_id', $request -> item_sub_category_id);
                            });
                        });
                    });
                });
            }) -> orderByDesc('id');
                return DataTables::of($docs) ->addIndexColumn()
                ->editColumn('document_status', function ($row) use($orderType) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                    $displayStatus = $row -> display_status;
                    $editRoute = route('trip-plan.edit', ['id' => $row -> id]); 
                    return "
                        <div style='text-align:right;'>
                            <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                            <div class='dropdown' style='display:inline;'>
                                <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                    <i data-feather='more-vertical'></i>
                                </button>
                                <div class='dropdown-menu dropdown-menu-end'>
                                    <a class='dropdown-item' href='" . $editRoute . "'>
                                        <i data-feather='edit-3' class='me-50'></i>
                                        <span>View/ Edit Detail</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    ";
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book_code ? $row->book_code : 'N/A';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? 'N/A';
                })
                ->addColumn('store',function($row){
                    return $row?->store?->store_name??" ";
                })
                ->addColumn('main_sub_store',function($row){
                    return $row?->main_sub_store?->name??" ";
                })
                ->addColumn('staging_sub_store',function($row){
                    return $row?->staging_sub_store?->name??" ";
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('items_count', function ($row) {
                    return $row->items->count();
                })
                ->rawColumns(['document_status'])
                ->make(true);
            }
            catch (Exception $ex) {
                return response() -> json([
                    'message' => $ex -> getMessage()
                ]);
            }
        }
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);

        $create_button = (isset($servicesBooks['services'])  && count($servicesBooks['services']) > 0 && isset($selectedfyYear['authorized']) && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        return view('trip-plan.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'create_button' => $create_button,'filterArray' => [],
            'autoCompleteFilters' => $autoCompleteFilters,]);
    }
    public function getBasicFilters()
    {
        //Get the common filters
        $user = Helper::getAuthenticatedUser();
        $categories = Category::select('id AS value', 'name AS label') -> withDefaultGroupCompanyOrg() 
        -> whereNull('parent_id') -> get();
        $subCategories = Category::select('id AS value', 'name AS label') -> withDefaultGroupCompanyOrg() 
        -> whereNotNull('parent_id') -> get();
        $items = Item::select('id AS value', 'item_name AS label') -> withDefaultGroupCompanyOrg()->get();
        $users = AuthUser::select('id AS value', 'name AS label') -> where('organization_id', $user -> organization_id)->get();
        $attributeGroups = AttributeGroup::select('id AS value', 'name AS label')->withDefaultGroupCompanyOrg()->get();

        //Custom filters (to be restr)

        return array(
            'itemCategories' => $categories,
            'itemSubCategories' => $subCategories,
            'items' => $items,
            'users' => $users,
            'attributeGroups' => $attributeGroups 
        );
    }
    public function create(Request $request)
    {
        //Get the menu 
        $parentURL = request() -> segments()[0];
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $redirectUrl = route('trip-plan.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::PL_SERVICE_NAME;
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK]);
        $vendors = Vendor::select('id', 'company_name') -> withDefaultGroupCompanyOrg() 
        -> where('status', ConstantHelper::ACTIVE) -> get();
        $departments = UserHelper::getDepartments($user -> auth_user_id);
        $users = AuthUser::select('id', 'name') -> where('organization_id', $user -> organization_id) 
        -> where('status', ConstantHelper::ACTIVE) -> get();
        $stations = Station::withDefaultGroupCompanyOrg()
        ->where('status', ConstantHelper::ACTIVE)
        ->get();
        $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
        $data = [
            'user' => $user,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => array(),
            'countries' => $countries,
            'typeName' => $typeName,
            'stores' => $stores,
            'vendors' => $vendors,
            'transportationModes' => $transportationModes,
            'stations' => $stations,
            'departments' => $departments['departments'],
            'selectedDepartmentId' => $departments['selectedDepartmentId'],
            'requesters' => $users,
            'selectedUserId' => null,
            'current_financial_year' => $selectedfyYear,
            'redirect_url' => $redirectUrl
        ];
        return view('trip-plan.layout', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('trip-plan.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpTripPlanHeaderHistory::with(['book']) -> with('items', function ($query) {
                    $query -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> where('source_id', $id)->first();
                $ogDoc = ErpTripPlanHeader::find($id);
            } else {
                $doc = ErpTripPlanHeader::with(['book']) -> with('items', function ($query) {
                    $query -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> find($id);
                $ogDoc = $doc;
            }
            $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias);
            }            
            $revision_number = $doc->revision_number;
            $totalValue = ($doc -> total_item_value - $doc -> total_discount_value) + 
            $doc -> total_tax_value + $doc -> total_expense_value;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, 
            $doc->approval_level, $doc -> created_by ?? 0, $userType['type'], $revision_number);
            $books = Helper::getBookSeriesNew(ConstantHelper::TRIP_SERVICE_ALIAS, ) -> get();
            $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
            $revNo = $doc->revision_number;
            if($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $doc->revision_number;
            }
            $docValue = $doc->total_amount ?? 0;
            $typeName = ConstantHelper::PL_SERVICE_NAME;
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $ogDoc->id, $revNo, $docValue, $doc -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
            $typeName = ConstantHelper::PL_SERVICE_NAME;
            $selectedfyYear = Helper::getFinancialYear($doc->document_date ?? Carbon::now()->format('Y-m-d'));
            $vendors = Vendor::select('id', 'company_name') -> withDefaultGroupCompanyOrg()->where('status', ConstantHelper::ACTIVE) 
            -> get();
            $stations = Station::withDefaultGroupCompanyOrg()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
            foreach ($doc -> items as $docItem) {
                $docItem -> max_qty_attribute = 9999999;
                if ($docItem -> mo_item_id) {
                    $moItem = MoItem::find($docItem -> mo_item_id);
                    if (isset($moItem)) {
                        $avlStock = $moItem -> getAvlStock($doc -> from_store_id);
                        $balQty = min($avlStock, $moItem -> mi_balance_qty);
                        $docItem -> max_qty_attribute = $docItem -> issue_qty + $balQty;
                    }
                }
            }
            $transportationModes = EwayBillMaster::where('status', 'active')
                ->where('type', '=', 'transportation-mode')
                ->orderBy('id', 'ASC')
            ->get();
            $departments = UserHelper::getDepartments($user -> auth_user_id);
            $users = AuthUser::select('id', 'name') -> where('organization_id', $user -> organization_id) 
            -> where('status', ConstantHelper::ACTIVE) -> get();   
            $SubStores = InventoryHelper::getAccesibleSubLocations($doc -> store_id, 0, ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES);
            $dynamicFieldsUI = $doc -> dynamicfieldsUi();

            $data = [
                'user' => $user,
                'series' => $books,
                'order' => $doc,
                'typeName' => $typeName,
                'countries' => $countries,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'stores' => $stores,
                'vendors' => $vendors,
                'stations' => $stations,
                'transportationModes' => $transportationModes,
                'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($doc -> media_files)) : 10,
                'services' => $servicesBooks['services'],
                'departments' => $departments['departments'],
                'selectedDepartmentId' => $doc ?-> department_id,
                'requesters' => $users,
                'selectedUserId' => $doc ?-> user_id,
                'sub_stores' => $SubStores,                
                'current_financial_year' => $selectedfyYear,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'redirect_url' => $redirect_url
            ];
            return view('trip-plan.layout', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }
   public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();

            // Basic validations
            $selectedDeliveries = (array) ($request->selected_deliveries ?? []);
            $plannedQtys = (array) ($request->planned_qty ?? []);

            if (count($selectedDeliveries) === 0) {
                return response()->json([
                    'message' => "Select Atleast One Delivery",
                    'error' => "",
                ], 422);
            }

            // Auth / org context (null-safe)
            $organization = Organization::find($user?->organization_id ?? null);
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;

            // Currency lookup (original logic) - guarded
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($organization?->currency?->id ?? null, $request->document_date);
            if (!($currencyExchangeData['status'] ?? false)) {
                return response()->json([
                    'message' => $currencyExchangeData['message'] ?? 'Currency lookup failed'
                ], 422);
            }
            $cx = $currencyExchangeData['data'] ?? [];

            // Document number generation for CREATE
            $numberPatternData = null;
            $document_number = $request->document_no ?? null;
            if (!$request->trip_header_id) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ?? $document_number;

                // duplicate check
                $regeneratedDocExist = ErpTripPlanHeader::withDefaultGroupCompanyOrg()
                    ->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)
                    ->first();
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }

            // Resolve stores/substores & config
            $store = ErpStore::find($request->store_id ?? null);
            $mainSubStore = ErpSubStore::find($request->main_sub_store_id ?? null);
            $stagingSubStore = ErpSubStore::find($request->staging_sub_store_id ?? null);
            $enforceUicScanning = ConfigurationHelper::getConfigurationValueOfOrg(ConfigurationConstant::ORG_CONFIG_ENFORCE_UIC_SCANNING, $organizationId);
            $transportdata = EwayBillMaster::find($request->transport_mode ?? null);
            // Prepare header payload (covering fillables; null-safe)
            $headerPayload = [
                'organization_id'       => $organizationId,
                'group_id'              => $groupId,
                'company_id'            => $companyId,
                'book_id'               => $request->book_id ?? null,
                'book_code'             => $request->book_code ?? null,

                'store_id'              => $request->store_id ?? null,
                'store_code'            => $store?->store_name ?? $request->store_code ?? null,

                // 'main_sub_store_id'     => $request->main_sub_store_id ?? null,
                // 'main_sub_store_code'   => $mainSubStore?->name ?? $request->main_sub_store_code ?? null,

                // 'staging_sub_store_id'  => $request->staging_sub_store_id ?? null,
                // 'staging_sub_store_code'=> $stagingSubStore?->name ?? $request->staging_sub_store_code ?? null,

                // 'enforce_uic_scanning'  => $enforceUicScanning ?? 0,

                // doc pattern fields (only when generated)
                'doc_number_type'       => $numberPatternData['type'] ?? $request->doc_number_type ?? null,
                'doc_reset_pattern'     => $numberPatternData['reset_pattern'] ?? $request->doc_reset_pattern ?? null,
                'doc_prefix'            => $numberPatternData['prefix'] ?? $request->doc_prefix ?? null,
                'doc_suffix'            => $numberPatternData['suffix'] ?? $request->doc_suffix ?? null,
                'doc_no'                => $numberPatternData['doc_no'] ?? $request->doc_no ?? null,

                'document_number'       => $document_number,
                'document_date'         => $request->document_date ?? now(),
                'document_status'       => $request->document_status ?? ConstantHelper::DRAFT,

                'revision_number'       => $request->revision_number ?? 0,
                'revision_date'         => $request->revision_date ?? null,
                'approval_level'        => $request->approval_level ?? 1,

                'reference_number'      => $request->reference_number ?? null,

                // transport fields (typo-safe for tranporter_name)
                'transport_mode_id'     => $transportdata->id ?? null,
                'transport_mode'        => $transportdata->description ?? null,
                'vehicle_number'        => $request->vehicle_number ?? null,
                'transporter_name'      => $request->transporter_name ?? $request->tranporter_name ?? null,
                'challan_number'        => $request->challan_number ?? null,

                // currency block
                'currency_id'           => $cx['org_currency_id'] ?? $request->currency_id ?? null,
                'currency_code'         => $cx['org_currency_code'] ?? $request->currency_code ?? null,
                'org_currency_id'       => $cx['org_currency_id'] ?? $request->org_currency_id ?? null,
                'org_currency_code'     => $cx['org_currency_code'] ?? $request->org_currency_code ?? null,
                'org_currency_exg_rate' => $cx['org_currency_exg_rate'] ?? $request->org_currency_exg_rate ?? 1,

                'comp_currency_id'      => $cx['comp_currency_id'] ?? $request->comp_currency_id ?? null,
                'comp_currency_code'    => $cx['comp_currency_code'] ?? $request->comp_currency_code ?? null,
                'comp_currency_exg_rate'=> $cx['comp_currency_exg_rate'] ?? $request->comp_currency_exg_rate ?? 1,

                'group_currency_id'     => $cx['group_currency_id'] ?? $request->group_currency_id ?? null,
                'group_currency_code'   => $cx['group_currency_code'] ?? $request->group_currency_code ?? null,
                'group_currency_exg_rate'=> $cx['group_currency_exg_rate'] ?? $request->group_currency_exg_rate ?? 1,

                // counts (will update after details)
                // 'total_item_count'      => 0,
                // 'total_verified_count'  => $request->total_verified_count ?? 0,
                // 'total_discrepancy_count'=> $request->total_discrepancy_count ?? 0,

                // remarks mapping - support both 'remarks' and 'remark'
                'remarks'               => $request->final_remarks ?? $request->remarks ?? $request->remark ?? null,

                'created_by'            => $user?->id ?? null,
                'updated_by'            => $user?->id ?? null,
            ];

            // CREATE or LOAD existing trip header
            if ($request->trip_header_id) {
                // === UPDATE path ===
                $trip = ErpTripPlanHeader::find($request->trip_header_id);
                if (!$trip) {
                    return response()->json(['message' => 'Trip Plan not found for update.'], 404);
                }

                // amend backup if required (original logic)
                $actionType = $request->action_type ?? '';
                if (($trip->document_status == ConstantHelper::APPROVED || $trip->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpTripPlanHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpTripPlanDetail', 'relation_column' => 'trip_header_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpPlItemAttribute', 'relation_column' => 'trip_item_id'],
                    ];
                    Helper::documentAmendment($revisionData, $trip->id);
                }

                // Reverse existing details' planned_qty on SO items (safe)
                $existingDetails = ErpTripPlanDetail::where('trip_header_id', $trip->id)->get();
                foreach ($existingDetails as $oldDetail) {
                    try {
                        if ($oldDetail?->order_item_id) {
                            // lock the SO item for update
                            $soItem = ErpSoItem::where('id', $oldDetail->order_item_id)->lockForUpdate()->first();
                            if ($soItem) {
                                $soItem->planned_qty = max(0, (float)($soItem->planned_qty ?? 0) - (float)($oldDetail->planned_qty ?? 0));
                                $soItem->save();
                            }
                        }
                    } catch (\Throwable $e) {
                        // continue but keep safe: if something odd, rollback later
                    }
                }

                // Remove old details & related attributes
                foreach ($existingDetails as $oldDetail) {
                    // if there are sub-attributes or relations, remove them safely
                    try {
                        $oldDetail->delete();
                    } catch (\Throwable $e) {
                        // ignore if relation not present
                    }
                }
                ErpTripPlanDetail::where('trip_header_id', $trip->id)->delete();

                // Update header fields
                $trip->fill($headerPayload);
                $trip->document_date = $request->document_date ?? $trip->document_date;
                $trip->save();
            } else {
                // === CREATE path ===
                $trip = ErpTripPlanHeader::create(array_merge($headerPayload, [
                    'document_status' => ConstantHelper::DRAFT,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'approval_level' => 1,
                ]));

                // attach generated doc pattern fields if we have them
                if (isset($numberPatternData)) {
                    $trip->doc_number_type = $numberPatternData['type'] ?? $trip->doc_number_type;
                    $trip->doc_reset_pattern = $numberPatternData['reset_pattern'] ?? $trip->doc_reset_pattern;
                    $trip->doc_prefix = $numberPatternData['prefix'] ?? $trip->doc_prefix;
                    $trip->doc_suffix = $numberPatternData['suffix'] ?? $trip->doc_suffix;
                    $trip->doc_no = $numberPatternData['doc_no'] ?? $trip->doc_no;
                    $trip->document_number = $document_number ?? $trip->document_number;
                    $trip->save();
                }
            }

            // INSERT / VALIDATE DETAILS
            $insertedCount = 0;
            foreach ($selectedDeliveries as $idx => $deliveryId) {
                $planned = (float) ($plannedQtys[$idx] ?? 0);
                // Planned qty must be > 0
                if ($planned <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Planned quantity must be greater than zero for delivery ID {$deliveryId}.",
                        'error' => ""
                    ], 422);
                }
                // Delivery must exist
                $delivery = ErpSoItemDelivery::find($deliveryId);
                if (!$delivery) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Invalid delivery selected (ID: {$deliveryId}).",
                        'error' => ""
                    ], 422);
                }

                // SO Item must exist
                $soItem = ErpSoItem::find($delivery->so_item_id ?? null);
                if (!$soItem) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Associated Sales Order Item not found for delivery ID {$deliveryId}.",
                        'error' => ""
                    ], 422);
                }

                // Lock SO item row to avoid race conditions when reading/updating planned_qty
                $soItem = ErpSoItem::where('id', $soItem->id)->lockForUpdate()->first();

                $alreadyPlanned = (float) ($soItem->planned_qty ?? 0);
                $orderQty = (float) ($soItem->order_qty ?? 0);
                $balanceQty = max(0, $orderQty - $alreadyPlanned);
                $picked = $soItem->picked_qty ?? 0;

                // If planned > order_qty -> error
                if ($planned > $orderQty) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Planned qty {$planned} exceeds order qty {$orderQty} for item " . ($soItem->item_name ?? $soItem->item_code),
                        'error' => ""
                    ], 422);
                }

                // If planned > balanceQty -> error
                if ($planned > $balanceQty) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Planned qty {$planned} exceeds available balance qty {$balanceQty} for item " . ($soItem->item_name ?? $soItem->item_code),
                        'error' => ""
                    ], 422);
                }

                // If planned < picked -> error
                if($planned < $picked)
                {
                    DB::rollBack();
                    return response()->json([
                        'message' => "{$picked} qty already Picked for item " . ($soItem->item_name ?? $soItem->item_code),
                        'error' => ""
                    ], 422);
                }
                // Build detail payload (null-safe)
                $order = ErpSaleOrder::find($delivery->sale_order_id ?? null);
                $uom = Unit::find($soItem->uom_id ?? null);
                $hsn = Hsn::find($soItem->hsn_id ?? null);
                $base_uom_qty = ItemHelper::convertToBaseUom($soItem->item_id ?? null, $soItem->uom_id ?? null, $planned) ?? $planned;
                $rate = (float) ($soItem->rate ?? 0);

                $detailPayload = [
                    'trip_header_id' => $trip->id,
                    'order_id' => $order?->id ?? null,
                    'order_item_id' => $soItem->id ?? null,
                    'order_item_delivery_id' => $delivery->id ?? null,
                    'item_id' => $soItem->item_id ?? null,
                    'item_code' => $soItem->item_code ?? null,
                    'item_name' => $soItem->item_name ?? null,
                    'attributes' => $soItem->item_attributes_array() ?? null,
                    'hsn_id' => $soItem->hsn_id ?? null,
                    'hsn_code' => $hsn?->code ?? null,
                    'uom_id' => $soItem->uom_id ?? null,
                    'uom_code' => $uom?->name ?? null,
                    'inventory_uom_id' => $soItem->inventory_uom_id ?? null,
                    'inventory_uom_code' => $soItem->inventory_uom_code ?? null,
                    'inventory_uom_qty' => $base_uom_qty,
                    'order_qty' => $soItem->order_qty ?? 0,
                    'planned_qty' => $planned,
                    'delivery_date' => $delivery->delivery_date ?? null,
                    'rate' => $rate,
                    'total_amount' => $planned * $rate,
                    'remarks' => is_array($request->item_remarks ?? null) ? ($request->item_remarks[$idx] ?? null) : ($request->item_remarks ?? null),
                ];

                // If trip_item_id is provided for this index, update that specific detail; else create new
                if (isset($request->trip_item_id[$idx]) && !empty($request->trip_item_id[$idx])) {
                    $existingDetail = ErpTripPlanDetail::find($request->trip_item_id[$idx]);
                    if ($existingDetail) {
                        $existingDetail->update($detailPayload);
                        $tripDetail = $existingDetail;
                    } else {
                        $tripDetail = ErpTripPlanDetail::create($detailPayload);
                    }
                } else {
                    $tripDetail = ErpTripPlanDetail::create($detailPayload);
                }

                // Update SO item planned_qty
                $soItem->planned_qty = (float)($soItem->planned_qty ?? 0) + $planned;
                $soItem->save();

                $insertedCount++;
            } // end foreach deliveries

            // Remove any details not part of this submission (original behavior)
            ErpTripPlanDetail::whereNotIn('order_item_delivery_id', $selectedDeliveries)
                ->where('trip_header_id', $trip->id)
                ->delete();

            // // Rebuild item summary / totals (original)
            // self::buildItemSummary($trip->id);

            // Approval & amendment logic (preserve original behavior)
            if ($request->trip_header_id) { // Update condition
                $bookId = $trip->book_id;
                $docId = $trip->id;
                $amendRemarks = $request->remarks ?? null;
                $remarks = $trip->remarks;
                $amendAttachments = $request->file('amend_attachments');
                $attachments = $request->file('attachments');
                $currentLevel = $trip->approval_level;
                $modelName = get_class($trip);
                $actionType = $request->action_type ?? "";

                if (($trip->document_status == ConstantHelper::APPROVED || $trip->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionNumber = $trip->revision_number + 1;
                    $actionType = 'amendment';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                    $trip->revision_number = $revisionNumber;
                    $trip->approval_level = 1;
                    $trip->revision_date = now();
                    $amendAfterStatus = $trip->document_status;
                    $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);

                    if (isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                        $totalValue = $trip->grand_total_amount ?? 0;
                        $amendAfterStatus = Helper::checkApprovalRequired($request->book_id, $totalValue);
                    } else {
                        $actionType = 'approve';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                        $actionType = 'submit';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    $trip->document_status = $amendAfterStatus;
                    $trip->save();
                } else {
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $revisionNumber = $trip->revision_number ?? 0;
                        $actionType = 'submit';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                        $totalValue = $trip->grand_total_amount ?? 0;
                        $document_status = Helper::checkApprovalRequired($request->book_id, $totalValue);
                        $trip->document_status = $document_status;
                    } else {
                        $trip->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                }
            } else { // Create condition
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $bookId = $trip->book_id;
                    $docId = $trip->id;
                    $remarks = $trip->remarks;
                    $attachments = $request->file('attachments');
                    $currentLevel = $trip->approval_level;
                    $revisionNumber = $trip->revision_number ?? 0;
                    $actionType = 'submit';
                    $modelName = get_class($trip);
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                }

                if ($request->document_status == 'submitted') {
                    $totalValue = $trip->total_amount ?? 0;
                    $document_status = Helper::checkApprovalRequired($request->book_id, $totalValue);
                    $trip->document_status = $document_status;
                } else {
                    $trip->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
                $trip->save();
            }

            // Save header (final)
            // $trip->total_item_count = $insertedCount;
            $trip->updated_by = $user?->id ?? $trip->updated_by;
            $trip->save();

            // Media - attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $singleFile) {
                    $trip->uploadDocuments($singleFile, 'PL_header', false);
                }
            }

            // Dynamic fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpTripDynamicField::class, $trip->id, $request->dynamic_field ?? []);
            if ($status && !$status['status']) {
                DB::rollBack();
                return response()->json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }

            // // Maintain Stock Ledger (original)
            // $errorMessage = self::maintainStockLedger($trip);
            // if ($errorMessage) {
            //     DB::rollBack();
            //     return response()->json([
            //         'message' => $errorMessage
            //     ], 422);
            // }

            DB::commit();
            $module = "Trip Planning";
            return response()->json([
                'message' => $module .  " created successfully",
                'redirect_url' => route('trip-plan.index')
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex->getLine() . ' in ' . $ex->getFile(),
            ], 500);
        }
    }

    private static function maintainStockLedger(ErpTripPlanHeader $header)
    {
        $items = $header->inv_items;
        $issueDetailIds = $items -> pluck('id') -> toArray();
        if ($header -> enforce_uic_scanning == 'yes') {
            $stockReservation = StockReservation::stockReservation(ConstantHelper::TRIP_SERVICE_ALIAS, $header -> id, $items);
            if ($stockReservation['status'] == 'error') {
                return $stockReservation['message'];
            }
        } else {
            $issueRecords = InventoryHelper::settlementOfInventoryAndStock($header->id, $issueDetailIds, ConstantHelper::TRIP_SERVICE_ALIAS, $header->document_status, 'issue');
            if ($issueRecords['status'] === 'error') {
                return $issueRecords['message'];
            }
            $receiptRecords = InventoryHelper::settlementOfInventoryAndStock($header->id, $issueDetailIds, ConstantHelper::TRIP_SERVICE_ALIAS, $header->document_status, 'receipt');
            if ($receiptRecords['status'] === 'error') {
                return $receiptRecords['message'];
            }
        }
        return "";
    }

    public function revokePL(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpTripPlanHeader::find($request -> id);
            if (isset($doc)) {
                $revoke = Helper::approveDocument($doc -> book_id, $doc -> id, $doc -> revision_number, '', [], 0, ConstantHelper::REVOKE, $doc -> total_amount, get_class($doc));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $doc -> document_status = $revoke['approvalStatus'];
                    $doc -> save();
                    DB::commit();
                    return response() -> json([
                        'status' => 'success',
                        'message' => 'Revoked succesfully',
                    ]);
                }
            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch(Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex -> getMessage());
        }
    }
    public function getVendorStores(Request $request)
    {
        try {
            $stores = ErpStore::select('id', 'store_name') -> withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE)
                -> whereHas('vendor_stores', function ($subQuery) use($request) {
                    $subQuery -> where('vendor_id', $request -> vendor_id);
                }) -> get();
            return response() -> json([
                'data' => $stores,
                'status' => 'success'
            ]);
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
    //Function to get all items of pwo
    public function getSoItemsForPulling(Request $request)
    {
        try {
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
            $storeids = $request->store_id ?? null;
            $subStoreId = $request->sub_store_id ?? null;
            $showAll = $request->show_all ?? "true";
            $orderItems = ErpSoItemDelivery::withWhereHas('item', function ($query) use($applicableBookIds) {
                $query->with('uom') ->withWhereHas('header', function ($subQuery) use($applicableBookIds) {
                    $subQuery->with(['store', 'customer']) -> withDefaultGroupCompanyOrg() -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                    ->whereIn('book_id', $applicableBookIds);
                });
            })
            ->when($request->to_date, function ($query) use ($request) {
                $query->whereHas('item.header', function ($subQuery) use ($request) {
                    $subQuery->whereDate('document_date', '<=', Carbon::parse($request->to_date));
                });
            })
            ->when($request->book_id, function ($query) use ($request) {
                $query->whereHas('item.header', function ($subQuery) use ($request) {
                    $subQuery->where('book_id', $request->book_id);
                });
            })
            ->when($request->store_id, function ($query) use ($request) {
                $query->whereHas('item.header', function ($subQuery) use ($request) {
                    $subQuery->where('store_id', $request->store_id);
                });
            }, function ($query) {
                $query->whereRaw('1 = 0'); // Ensures no results are returned if store_id is not provided
            })
            ->when($request->so_book_code, function ($query) use ($request) {
                $query->whereHas('item.header', function ($subQuery) use ($request) {
                    $subQuery->where('book_code', 'LIKE', '%' . $request->so_book_code . '%');
                });
            })
            ->when($request->so_document_no, function ($query) use ($request) {
                $query->whereHas('item.header', function ($subQuery) use ($request) {
                    $subQuery->where('document_number', 'LIKE', '%' . $request->so_document_no . '%');
                });
            })
            ->when($request->document_date, function ($query) use ($request) {
                $dateRange = explode('to', $request->document_date);
                $endDate = Carbon::parse(trim($dateRange[0]));
                $query->whereHas('item.header', function ($subQuery) use ($endDate) {
                    $subQuery->where('document_date', '>=' ,$endDate);
                });
            })
            ->when($request->delivery_date, function ($query) use ($request) {
                $dateRange = explode('to', $request->delivery_date);
                $endDate = Carbon::parse(trim($dateRange[0]));
                $query->where('delivery_date', '>=' ,$endDate);
            })
            ->when($request->customer_code, function ($query) use ($request) {
                $query->whereHas('item.header.customer', function ($subQuery) use ($request) {
                    $subQuery->where('customer_code', 'LIKE', '%' . $request->customer_code . '%');
                });
            })
            ->whereHas('item', function ($query) {
                $query->whereRaw('order_qty > (short_close_qty + dnote_qty + planned_qty)');
            })
            ->orderBy('delivery_date')->get();

            $processedItems = collect([]);

            foreach ($orderItems as $orderItem) {
                $orderItem->attributes = collect($orderItem->item->item_attributes_array())->map(function ($attrArr) {
                    $short = $attrArr['short_name'] ?? null;
                    $groupName = $attrArr['group_name'] ?? '';
                    $selectedValue = collect($attrArr['values_data'])->firstWhere('selected', true)['value'] ?? '';
                    $displayName = $short ?? $groupName;
                    return "<span class='badge rounded-pill badge-light-primary'><strong>{$displayName}: {$selectedValue}</strong></span>";
                })->implode(' ');
                $orderItem->avl_stock = $orderItem->item->getStockBalanceQty($storeids);
                $orderItem->store_location_code = $orderItem->item->header?->store_location?->store_name;
                $orderItem->department_code = $orderItem->item->header?->department?->name;
                $orderItem->station_name = $orderItem->item->header?->station?->name;
                if ($showAll == 'false') {
                    if ($orderItem -> avl_stock > 0) {
                        $processedItems -> push($orderItem);
                    }
                } else {
                    $processedItems -> push($orderItem);
                }
            }
            return response()->json([
                'data' => $processedItems
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage() . $ex->getFile() . $ex->getLine()
            ]);
        }
    }
    //Function to get all items of pwo module
    public function processPulledItems(Request $request)
    {
        try {
            $headers = collect([]);
            if ($request -> doc_type === ConstantHelper::MO_SERVICE_ALIAS) {
                $headers = MfgOrder::with(['items' => function ($mappingQuery) use($request) {
                    $mappingQuery -> whereIn('id', $request -> items_id) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                    }]);
                }]) -> get();
            } else if ($request -> doc_type === ConstantHelper::PWO_SERVICE_ALIAS) {
                $headers = ErpProductionWorkOrder::with(['items' => function ($mappingQuery) use($request) {
                    $mappingQuery -> whereIn('id', $request -> items_id) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                    }]);
                }]) -> get();
            } else if ($request -> doc_type === ConstantHelper::PI_SERVICE_ALIAS || $request -> doc_type === "pi") {
                $headers = PurchaseIndent::with(['items' => function ($mappingQuery) use($request) {
                    $mappingQuery -> whereIn('id', $request -> items_id) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                    }]);
                }]) -> get();
            }
            foreach ($headers as &$header) {
                foreach ($header -> items as &$item) {
                    $item -> item_attributes_array = $item -> item_attributes_array();
                    $item -> avl_stock = $item -> getAvlStock($request -> store_id);
                }
            }
            return response() -> json([
                'message' => 'Data found',
                'data' => $headers
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'message' => 'Some internal error occurred',
                'error' => $ex -> getMessage()
            ]);
        }
    }
    public function generatePdf(Request $request, $id, $pattern)
        {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $organizationAddress = Address::with(['city', 'state', 'country'])
                ->where('addressable_id', $user->organization_id)
                ->where('addressable_type', Organization::class)
                ->first();
            $mx = ErpTripPlanHeader::with(
                [
                    'from_store',
                    'to_store',
                    'vendor',
                ]
            )
                ->with('items', function ($query) {
                    $query->with('from_item_locations','to_item_locations')->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                })
                ->find($id);
            // $creator = AuthUser::with(['authUser'])->find($mx->created_by);
            // dd($creator,$mx->created_by);
            $shippingAddress = $mx?->from_store?->address;
            $billingAddress = $mx?->to_store?->address;

            $approvedBy = Helper::getDocStatusUser(get_class($mx), $mx -> id, $mx -> document_status);

            // dd($user);
            // $type = ConstantHelper::SERVICE_LABEL[$mx->document_type];
            $totalItemValue = $mx->total_item_value ?? 0.00;
            $totalTaxes = $mx->total_tax_value ?? 0.00;
            $totalAmount = ($totalItemValue + $totalTaxes);
            $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
            // $storeAddress = ErpStore::with('address')->where('id',$mx->store_id)->get();
            // dd($mx->location->address);
            // Path to your image (ensure the file exists and is accessible)
            $approvedBy = Helper::getDocStatusUser(get_class($mx), $mx -> id, $mx -> document_status);
            $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
            $data_array = [
                'print_type' => $pattern,
                'mx' => $mx,
                'user' => $user,
                'shippingAddress' => $shippingAddress,
                'billingAddress' => $billingAddress,
                'organization' => $organization,
                'amountInWords' => $amountInWords,
                'organizationAddress' => $organizationAddress,
                'totalItemValue' => $totalItemValue,
                'totalTaxes' => $totalTaxes,
                'totalAmount' => $totalAmount,
                'imagePath' => $imagePath,
                'approvedBy' => $approvedBy,
            ];
            $pdf = PDF::loadView(

                // return view(
                'pdf.material_document',
                $data_array
            );

            return $pdf->stream('PL_header.pdf');
        }
        // public function report(){
        //     $issue_data = ErpTripPlanHeader::where('issue_type', 'Consumption')
        //         ->withWhereHas('items', function ($query) {
        //             $query->whereHas('attributes', function ($subQuery) {
        //                 $subQuery->where('attribute_name', 'TYPE'); // Ensure the attribute name is 'TYPE'
        //             }, '=', 1); // Ensure only one attribute exists
        //         })
        //         ->get();
        //     $issue_items_ids = ErpTripPlanDetail::whereIn('trip_header_id',[$issue_data->pluck('id')])->pluck('id');
        //     $return_data = ErpMrItem::whereIn('trip_item_id',[$issue_items_ids])->get();
        //     return view('trip-plan.report',[
        //         'issues' =>$issue_data,
        //         'return' =>$return_data,
        //     ]);
        // }
    public function report(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::TRIP_SERVICE_ALIAS;
        $redirectUrl = route('trip-plan.report');
        $requesters = ErpTripPlanHeader::with(['requester'])->withDefaultGroupCompanyOrg()->bookViewAccess($pathUrl)->orderByDesc('id')->where('issue_type','Consumption')->where('requester_type',"User")->get()->unique('user_id')
        ->map(function ($item) {
            return [
                'id' => $item->requester()->first()->id ?? null,
                'name' => $item->requester()->first()->name ?? 'N/A',
            ];
        });
        if ($request->ajax()) {
            try {
                // Fetch Material Issues with Related Items and Attributes
                $docs = ErpTripPlanHeader::with('requester')->where('issue_type', 'Consumption')
                    ->withWhereHas('items', function ($query) {
                        $query->whereHas('attributes', function ($subQuery) {
                            $subQuery->where('attribute_name', 'TYPE');
                        }, '=', 1);
                    })
                    ->when(!empty($request->issue_to), function($query) use($request){
                        $query->where('user_id',$request->issue_to);
                    })
                    ->when(!empty($request->time_period), function ($query) use ($request) {
                        if (strpos($request->time_period, 'to') !== false) {
                            [$start_date, $end_date] = explode('to', $request->time_period);
                            $start_date = trim($start_date); // Remove extra spaces
                            $end_date = trim($end_date);
                    
                            // Apply filtering between start and end date
                            $query->whereBetween('document_date', [$start_date, $end_date]);
                        } else {
                            $start_date = trim($request->time_period);
                            $query->where('document_date', '=', $start_date);
                        }
                    })                    
                    ->withDefaultGroupCompanyOrg()
                    ->bookViewAccess($pathUrl)
                    ->orderByDesc('id')
                    ->get();

                // Get all issue item IDs
                $issue_data = ErpTripPlanDetail::with(['header'])->whereIn('trip_header_id', $docs->pluck('id'))->orderByDesc('id')->get();
                $issue_item_ids = $issue_data -> pluck('id');
                // Fetch corresponding return data
                $return_data = ErpMrItem::whereIn('trip_item_id', $issue_item_ids)
                    ->with(['attributes' => function ($query) {
                        $query->where('attribute_name', 'TYPE');
                    }])
                    ->get();

                return DataTables::of($issue_data) ->addIndexColumn()
                    ->editColumn('document_date', function ($row) {
                        return $row->header->getFormattedDate('document_date') ?? 'N/A';
                    })
                    ->addColumn('document_number', function ($row) {
                        return $row->header->book_code ? $row->header->book_code . '-' . $row->header->document_number : 'N/A';
                    })
                    ->addColumn('coach_name', function ($row) {
                        return $row->header->requester_name() ?? "N/A";
                    })
                    ->addColumn('items', function ($row) use ($return_data) {
                            $itemsHtml = "";
                            $row->used_in_training = 0;
                            $row->return = 0;
                            $row->scrap = 0;
                            // Calculate Used in Training, Return, and Scrap
                            $used = $return_data->where('trip_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'RETURN OLD';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();

                            $returned = $return_data->where('trip_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'NEW';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();
                            $scrap = (int) $row->issue_qty - ((int) $used + (int) $returned);

                            // Store values at the row level for later use
                            $row->issue_qty = (int) $row->issue_qty;
                            $row->used_in_training = (int) $used;
                            $row->return = (int) $returned;
                            $row->scrap = (int) $scrap;

                            // Add item name
                            $itemsHtml .= "<div>" . $row->item_name . "</div>";
                        return $itemsHtml;
                    })
                    ->addColumn('attribute', function ($row) {
                        $attributesHtml = '';

                            foreach ($row->item_attributes_array() as $att_data) {
                                $selectedValues = collect($att_data['values_data'])
                                    ->where('selected', true)
                                    ->pluck('value')
                                    ->implode(', ');

                                $attributesHtml .= "<span class='badge rounded-pill badge-light-secondary badgeborder-radius'>"
                                    . $selectedValues . "</span> ";
                            }

                        return "<div>" . $attributesHtml . "</div>";
                    })
                    ->editColumn('issue_qty', function ($row) {
                        return (string)$row->issue_qty;
                    })
                    ->addColumn('used_in_training', function ($row) {
                        return (string)$row->used_in_training;
                    })
                    ->addColumn('return', function ($row) {
                        return (string)$row->return;
                    })
                    ->addColumn('scrap', function ($row) {
                        return (string)$row->scrap;
                    })
                    ->rawColumns(['items', 'attribute']) // Allow HTML rendering in DataTables
                    ->make(true);
            } catch (Exception $ex) {
                return response()->json([
                    'message' => $ex->getMessage()
                ]);
            }
        }
    return view('trip-plan.report',['requesters'=>$requesters]);
    }

    public function getLocationsWithMultipleStores(Request $request)
    {
        try {
            $multiStoreLoc = $request -> type == 'Sub Location Transfer' ? true : false;
            $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK, null, $multiStoreLoc);
            return response() -> json([
                'status' => 200,
                'message' => 'Records retrieved successfully',
                'data' => $locations
            ], 200);
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
    public function PLReport(Request $request)
    {
        $pathUrl = route('trip-plan.index');
        $orderType = [ConstantHelper::TRIP_SERVICE_ALIAS];
        $trip = ErpTripPlanDetail::whereHas('header', function ($headerQuery) use($orderType, $pathUrl, $request) {
            $headerQuery -> withDefaultGroupCompanyOrg() -> withDraftListingLogic();
            //Book Filter
            $headerQuery = $headerQuery -> when($request -> book_id, function ($bookQuery) use($request) {
                $bookQuery -> where('book_id', $request -> book_id);
            });
            //Document Id Filter
            $headerQuery = $headerQuery -> when($request -> document_number, function ($docQuery) use($request) {
                $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
            });
            //Location Filter
            $headerQuery = $headerQuery -> when($request -> location_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> location_id);
            });
            //Company Filter
            $headerQuery = $headerQuery -> when($request -> company_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> company_id);
            });
            //Organization Filter
            $headerQuery = $headerQuery -> when($request -> organization_id, function ($docQuery) use($request) {
                $docQuery -> where('organization_id', $request -> organization_id);
            });$headerQuery = $headerQuery -> when($request -> doc_status, function ($docStatusQuery) use($request) {
                $searchDocStatus = [];
                if ($request -> doc_status === ConstantHelper::DRAFT) {
                    $searchDocStatus = [ConstantHelper::DRAFT];
                } else if ($searchDocStatus === ConstantHelper::SUBMITTED) {
                    $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
                } else {
                    $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
                }
                $docStatusQuery -> whereIn('document_status', $searchDocStatus);
            });
            //Date Filters
            $dateRange = $request -> date_range ??  Carbon::now()->startOfMonth()->format('Y-m-d') . " to " . Carbon::now()->endOfMonth()->format('Y-m-d');
            $headerQuery = $headerQuery -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
            $dateRanges = explode('to', $dateRange);
            if (count($dateRanges) == 2) {
                    $fromDate = Carbon::parse(trim($dateRanges[0])) -> format('Y-m-d');
                    $toDate = Carbon::parse(trim($dateRanges[1])) -> format('Y-m-d');
                    $dateRangeQuery -> whereDate('document_date', ">=" , $fromDate) -> where('document_date', '<=', $toDate);
            }
            else{
                $fromDate = Carbon::parse(trim($dateRanges[0])) -> format('Y-m-d');
                $dateRangeQuery -> whereDate('document_date', $fromDate);
            }
            });
            //Item Id Filter
            $headerQuery = $headerQuery -> when($request -> item_id, function ($itemQuery) use($request) {
                $itemQuery -> withWhereHas('items', function ($itemSubQuery) use($request) {
                    $itemSubQuery -> where('item_id', $request -> item_id)
                    //Compare Item Category
                    -> when($request -> item_category_id, function ($itemCatQuery) use($request) {
                        $itemCatQuery -> whereHas('item', function ($itemRelationQuery) use($request) {
                            $itemRelationQuery -> where('category_id', $request -> category_id)
                            //Compare Item Sub Category
                            -> when($request -> item_sub_category_id, function ($itemSubCatQuery) use($request) {
                                $itemSubCatQuery -> where('subcategory_id', $request -> item_sub_category_id);
                            });
                        });
                    });
                });
            });
        }) -> orderByDesc('id');
        $dynamicFields = DynamicFieldHelper::getServiceDynamicFields(ConstantHelper::SO_SERVICE_ALIAS);   
        $processedSalesOrder = collect([]);
        return DataTables::of($trip)
            ->addIndexColumn()
            ->editColumn('document_number', fn($trip) => $trip->header->document_number)
            ->editColumn('document_date', fn($trip) => $trip->header->document_date)
            ->editColumn('book_code', fn($trip) => $trip->header->book_code)
            ->addColumn('so_no', fn($trip) => $trip->so->book_code."-".$trip->so->document_number)
            ->addColumn('so_date', fn($trip) => $trip->so->document_date)
            ->addColumn('store_name', fn($trip) => $trip->header->store?->store_name)
            ->addColumn('sub_store_name', fn($trip) => $trip->header->subStore?->name)
            ->editColumn('item_name', fn($trip) => $trip->item_name)
            ->editColumn('item_code', fn($trip) => $trip->item_code)
            ->editColumn('hsn_code', fn($trip) => $trip->item->hsn?->code)
            ->editColumn('uom_name', fn($trip) => $trip->item->uom?->name)
            ->editColumn('planned_qty', fn($trip) => number_format($trip->planned_qty, 2))
            ->editColumn('order_qty', fn($trip) => number_format($trip->order_qty, 2))
            ->editColumn('rate', fn($trip) => number_format($trip->rate, 2))
            ->editColumn('total_amount', fn($trip) => number_format($trip->total_amount, 2))
            ->editColumn('item_attributes', function ($trip) {
                if (count($trip->item_attributes) > 0) {
                    return collect($trip->item_attributes)->map(fn($attr) => "<span class='badge rounded-pill badge-light-primary'>{$attr->attribute_name} : {$attr->attribute_value}</span>")->implode(' ');
                }
                return 'N/A';
            })
            ->editColumn('status', function ($trip) use ($orderType) {
                $status = $trip->header->document_status ?? ConstantHelper::DRAFT;
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$status];
                $editRoute = route('trip-plan.edit', ['id' => $trip->id]);

                return "
                    <div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClass badgeborder-radius'>" . ucfirst($status) . "</span>
                        <a href='$editRoute'>
                            <i class='cursor-pointer' data-feather='eye'></i>
                        </a>
                    </div>";
            })
            ->rawColumns(['item_attributes', 'status'])
            ->make(true);
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

    public function postPL(Request $request)
    {
        try {
            DB::beginTransaction();
            $saleInvoice = ErpTripPlanHeader::find($request->document_id);
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
            ], 500);
        }
    }
    public function import(Request $request)
    {
        try {
            // Validate the uploaded file
            $request->validate([
                'file' => 'required|file|mimes:xls,xlsx|max:30720', // Max size: 30MB
            ]);

            // Load the file
            $file = $request->file('file');
            $data = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $data->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            // Validate the file structure
            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded file is empty or invalid.',
                ], 422);
            }

            // Extract header row
            $header = array_map('strtolower', $rows[1]);

            // Required columns
            $requiredColumns = ['item_code', 'item_name', 'uom', 'confirmed_qty', 'rate'];
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $header)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "The file is missing the required column: $column.",
                    ], 422);
                }
            }

            // Process rows
            $importedItems = [];
            $failedItems = [];
            foreach (array_slice($rows, 1) as $rowIndex => $row) {
                try {
                    $itemCode = $row[array_search('item_code', $header)];
                    $itemName = $row[array_search('item_name', $header)];
                    $uom = $row[array_search('uom', $header)];
                    $confirmedQty = $row[array_search('confirmed_qty', $header)];
                    $rate = $row[array_search('rate', $header)];

                    // Validate data
                    if (empty($itemCode) || empty($itemName) || empty($uom) || empty($confirmedQty) || empty($rate)) {
                        throw new \Exception('Missing required fields.');
                    }

                    // Check if the item exists in the database
                    $item = Item::where('item_code', $itemCode)->first();
                    if (!$item) {
                        throw new \Exception("Item with code $itemCode not found.");
                    }

                    // Check if UOM is valid
                    $uomModel = Unit::where('name', $uom)->first();
                    if (!$uomModel) {
                        throw new \Exception("UOM $uom is invalid.");
                    }

                    // Add to imported items
                    $importedItems[] = [
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'uom_code' => $uom,
                        'confirmed_qty' => $confirmedQty,
                        'rate' => $rate,
                        'value' => $confirmedQty * $rate,
                    ];
                } catch (\Exception $e) {
                    // Add to failed items
                    $failedItems[] = [
                        'row' => $rowIndex + 2, // Add 2 to account for header row and 0-based index
                        'reason' => $e->getMessage(),
                    ];
                }
            }

            // Return response
            return response()->json([
                'status' => 'success',
                'message' => 'File processed successfully.',
                'successful_items' => $importedItems,
                'failed_items' => $failedItems,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private static function buildItemSummary(int $headerId)
    {
        $tripItems = ErpTripPlanDetail::with('item_attributes') -> where('trip_header_id', $headerId) -> get();
        $grouped = $tripItems->groupBy(function ($item) {
            // Convert related attributes to a sorted associative array
            $attributeArray = $item->item_attributes
                ->sortBy('attr_name')
                ->pluck('attr_value', 'attr_name')
                ->toArray();

            // Build a unique grouping key from item_id, uom_id, and attribute JSON
            return json_encode([
                'item_id' => $item->item_id,
                'uom_id' => $item->uom_id,
                'attributes' => $attributeArray
            ]);
        });
        // $grouped = DB::table(function ($query) use($headerId) {
        //         $query->from('erp_pl_item_details')
        //             ->leftJoin('erp_pl_item_attributes', 'erp_pl_item_details.id', '=', 'erp_pl_item_attributes.trip_item_id') // Use LEFT JOIN
        //             ->select(
        //                 'erp_pl_item_details.item_id',
        //                 'erp_pl_item_details.uom_id',
        //                 'erp_pl_item_details.inventory_uom_qty',
        //                 DB::raw("GROUP_CONCAT(
        //                     CONCAT(erp_pl_item_attributes.item_attribute_id, ':', erp_pl_item_attributes.attribute_value)
        //                     ORDER BY erp_pl_item_attributes.item_attribute_id SEPARATOR ', '
        //                 ) as attributes"),
        //             )
        //             ->where('erp_pl_item_details.trip_header_id', $headerId)
        //             ->groupBy('erp_pl_item_details.item_id', 'erp_pl_item_details.uom_id');
        //     })
        //     ->select(
        //         'item_id',
        //         'uom_id',
        //         DB::raw("IFNULL(attributes, '') as attributes"),
        //         DB::raw("SUM(inventory_uom_qty) as total_qty")
        //     )
        //     ->groupBy('item_id', 'uom_id', 'attributes')
        //     ->get();

        foreach ($grouped as $groupedItemKey => $groupedItems) {
            $groupedItem = $groupedItems[0];
            $totalInvQty = 0;
            foreach ($groupedItems as $item) {
                $totalInvQty += $item -> inventory_uom_qty;
            }
            // $existingPlItem = ErpPlItem::where('item_id', $groupedItem -> item_id) 
            // -> where('trip_header_id', $headerId) -> where('inventory_uom_id', $groupedItem -> inventory_uom_id) -> first();
            // if ($existingPlItem) {
            //     //NEED TO ADD LOGIC
            // } else {
                $attributesArray = [];
                foreach ($groupedItem -> item_attributes as $attribute) {
                    array_push($attributesArray, [
                        'item_attribute_id' => $attribute -> item_attribute_id,
                        'attribute_group_id' => $attribute -> attr_name,
                        'attribute_group' => $attribute -> attribute_name,
                        'attribute_value_id' => $attribute -> attr_value,
                        'attribute_value' => $attribute -> attribute_value,
                    ]);
                }
                $inventoryUom = Unit::find($groupedItem -> inventory_uom_id);
                $data = ErpPlItem::create([
                    'trip_header_id' => $headerId,
                    'item_id' => $groupedItem -> item_id,
                    'item_code' => $groupedItem -> item_code,
                    'item_name' => $groupedItem -> item_name,
                    'attributes' => $attributesArray,
                    'inventory_uom_id' => $inventoryUom ?-> id,
                    'inventory_uom_code' => $inventoryUom ?-> name,
                    'inventory_uom_qty' => $totalInvQty,
                ]);
                foreach ($groupedItems as $item) {
                    ErpTripPlanDetail::where('id', $item -> id) -> update(['trip_item_id' => $data -> id]);
                }
                // ErpTripPlanDetail::where('item_id', $groupedKeys['item_id']) -> where('uom_id', $groupedKeys['uom_id']) -> where('')
            // }
        }
    }
}