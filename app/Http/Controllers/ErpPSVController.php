<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TransactionReportHelper;
use App\Helpers\UserHelper;
use App\Http\Requests\ErpPSVRequest;
use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Helpers\DynamicFieldHelper;
use App\Models\Category;
use App\Models\ErpPsvDynamicField;
use App\Models\Country;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpInvoiceItem;
use App\Models\ErpPsvHeader;
use App\Models\ErpPsvHeaderHistory;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpPsvItem;
use App\Models\ErpPsvItemAttribute;
use App\Models\ErpPsvItemLocation;
use App\Models\ErpPsvItemLotDetail;
use App\Models\ErpMrItem;
use App\Models\ErpProductionSlip;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpPwoItem;
use App\Models\ErpRack;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpVendor;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\MfgOrder;
use App\Models\MoItem;
use App\Models\Organization;
use App\Models\PiItem;
use App\Models\PurchaseIndent;
use App\Models\PwoSoMapping;
use App\Models\Station;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\Vendor;
use Carbon\Carbon;
use PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use Yajra\DataTables\DataTables;

class ErpPSVController extends Controller
{
    public function index(Request $request)
    {
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());

        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PSV_SERVICE_ALIAS;
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $redirectUrl = route('psv.index');
        $createRoute = route('psv.create');
        $typeName = "Physical Stock Verification ";
        $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
        $parentURL = request() -> segments()[0];
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $autoCompleteFilters = self::getBasicFilters();
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        $create_button = (isset($servicesBooks['services'])  && count($servicesBooks['services']) > 0 && isset($selectedfyYear['authorized']) && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        //Date Filters
        $dateRange = $request -> date_range ?? null;
        if ($request -> ajax()) {
            try {
                $docs = ErpPsvHeader::withDefaultGroupCompanyOrg() -> whereIn('store_id',$accessible_locations) -> bookViewAccess($pathUrl) ->  withDraftListingLogic()->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) -> when($request -> vendor_id, function ($custQuery) use($request) {
                    $custQuery -> where('vendor_id', $request -> vendor_id);
                }) -> when($request -> book_id, function ($bookQuery) use($request) {
                    $bookQuery -> where('book_id', $request -> book_id);
                }) -> when($request -> document_number, function ($docQuery) use($request) {
                    $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
                }) -> when($request -> type, function ($docQuery) use($request) {
                    $docQuery -> where('issue_type', 'LIKE', "%".$request -> type . "%");
                })-> when($request -> location_id, function ($docQuery) use($request) {
                    $docQuery -> where('from_store_id', $request -> location_id);
                })-> when($request -> to_location_id, function ($docQuery) use($request) {
                    $docQuery -> where('to_store_id', $request -> to_location_id);
                })-> when($request -> company_id, function ($docQuery) use($request) {
                    $docQuery -> where('from_store_id', $request -> company_id);
                })-> when($request -> organization_id, function ($docQuery) use($request) {
                    $docQuery -> where('organization_id', $request -> organization_id);
                })-> when($request -> status, function ($docStatusQuery) use($request) {
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
                    $editRoute = route('psv.edit', ['id' => $row -> id]); 
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
                ->addColumn('sub_store',function($row){
                    return $row?->sub_store?->name??" ";
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('items_count', function ($row) {
                    return $row?->items?->count() ?? '0';
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
        return view('PSV.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl,'create_route' => $createRoute, 'create_button' => $create_button, 'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::PSV_SERVICE_ALIAS],
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
        $user = Helper::getAuthenticatedUser();
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL,'',$user);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
        $redirectUrl = route('psv.index');
        $firstService = $servicesBooks['services'][0];
        $typeName = ConstantHelper::PSV_SERVICE_ALIAS;
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR , ConstantHelper::VENDOR_STORE]);
        $vendors = Vendor::select('id', 'company_name') -> withDefaultGroupCompanyOrg() 
        -> where('status', ConstantHelper::ACTIVE) -> get();
        $departments = UserHelper::getDepartments($user -> auth_user_id);
        $users = AuthUser::select('id', 'name') -> where('organization_id', $user -> organization_id) 
        -> where('status', ConstantHelper::ACTIVE) -> get();
        $stations = Station::withDefaultGroupCompanyOrg()
        ->where('status', ConstantHelper::ACTIVE)
        ->get();
        
        $data = [
            'user' => $user,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => array(),
            'countries' => $countries,
            'typeName' => $typeName,
            'stores' => $stores,
            'stations' => $stations,
            'redirect_url' => $redirectUrl,
            'current_financial_year' => $selectedfyYear,
        ];
        return view('PSV.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $currentfyYear = Helper::getCurrentFinancialYear();
            $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('psv.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpPsvHeaderHistory::with(['book'])
                ->first();
        
            $ogDoc = ErpPsvHeader::find($id);
            } else {
                $doc = ErpPsvHeader::with(['book'])
                ->find($id);
                $ogDoc = $doc;
            }
            $items = self::pullItems($doc);
            $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias,$user);
            }            
            $revision_number = $doc->revision_number;
            $selectedfyYear = Helper::getFinancialYear($doc->document_date ?? Carbon::now()->format('Y-m-d'));
            $totalValue = ($doc -> total_item_value - $doc -> total_discount_value) + 
            $doc -> total_tax_value + $doc -> total_expense_value;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, 
            $doc->approval_level, $doc -> created_by ?? 0, $userType['type'], $revision_number);
            $books = Helper::getBookSeriesNew(ConstantHelper::PSV_SERVICE_ALIAS, ) -> get();
            $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
            $revNo = $doc->revision_number;
            if($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $doc->revision_number;
            }
            $docValue = $doc->total_amount ?? 0;
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $ogDoc->id, $revNo, $docValue, $doc -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
            $typeName = "Physical Stock Verification";
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
            $departments = UserHelper::getDepartments($user -> auth_user_id);
            $users = AuthUser::select('id', 'name') -> where('organization_id', $user -> organization_id) 
            -> where('status', ConstantHelper::ACTIVE) -> get();   
            $SubStores = InventoryHelper::getAccesibleSubLocations($doc -> store_id, 0, ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES);
            $dynamicFieldsUI = $doc -> dynamicfieldsUi();

            $data = [
                'user' => $user,
                'series' => $books,
                'order' => $doc,
                'items' => $items,
                'countries' => $countries,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'stores' => $stores,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'stations' => $stations,
                'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($doc -> media_files)) : 10,
                'services' => $servicesBooks['services'],
                'departments' => $departments['departments'],
                'selectedDepartmentId' => $doc ?-> department_id,
                'requesters' => $users,
                'selectedUserId' => $doc ?-> user_id,
                'sub_stores' => $SubStores,
                'redirect_url' => $redirect_url,
                'current_financial_year' => $selectedfyYear,

            ];
            return view('PSV.create_edit', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }
    public function store(Request $request)
    {
        try {
            if(!$request->filled('store_id')){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Location is required.',
                ], 400);
            }
            if(!$request->filled('sub_store_id')){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Store is required.',
                ], 400);
            }
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::find($user->organization_id);
    
            $organizationId = $organization?->id;
            $groupId = $organization?->group_id;
            $companyId = $organization?->company_id;
    
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($organization->currency->id, $request->document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }
    
            $itemAttributeIds = [];
            $isUpdate = $request->psv_header_id ? true : false;
    
            if (!$isUpdate) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ?? $request->document_no;
                $regeneratedDocExist = ErpPsvHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                if ($regeneratedDocExist) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
    
            $store = ErpStore::find($request->store_id);
            $sub_store = ErpSubStore::find($request->sub_store_id);
            $vendor = Vendor::find($request->vendor_id);
    
            if ($request->requester_type == 'User') {
                $user = AuthUser::find($request->user_id);
            } else {
                $department = Department::find($request->department_id);
            }
    
            if ($isUpdate) {
                $psv = ErpPsvHeader::find($request->psv_header_id);
                $psv -> document_date = $request -> document_date;
                //Store and department keys
                $psv -> store_id = $request -> store_id ?? null;
                $psv -> sub_store_id = $request -> sub_store_id ?? null;
                $psv -> remarks = $request -> final_remarks;
                $actionType = $request->action_type ?? '';
    
                if (($psv->document_status == ConstantHelper::APPROVED || $psv->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpPsvHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpPsvItem', 'relation_column' => 'psv_header_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpPsvItemAttribute', 'relation_column' => 'psv_item_id'],
                    ];
                    Helper::documentAmendment($revisionData, $psv->id);
                }
    
                $psv->fill([
                    'document_date' => $request->document_date,
                    'store_id' => $request->store_id,
                    'sub_store_id' => $request->sub_store_id,
                    'remarks' => $request->final_remarks,
                ])->save();
    
                $deletedData = [
                    'deletedSiItemIds' => json_decode($request->input('deletedSiItemIds', '[]'), true),
                    'deletedAttachmentIds' => json_decode($request->input('deletedAttachmentIds', '[]'), true)
                ];
    
                foreach ($deletedData['deletedSiItemIds'] as $deletedId) {
                    $psvItem = ErpPsvItem::find($deletedId);
                    $psvItem?->attributes()->delete();
                    $psvItem?->delete();
                }
            } else {
                $psv = ErpPsvHeader::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'store_id' => $request->store_id,
                    'sub_store_id' => $request->sub_store_id,
                    'store_code' => $store?->store_name,
                    'sub_store_code' => $sub_store?->name,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_number' => $document_number,
                    'document_date' => $request->document_date,
                    'document_status' => ConstantHelper::DRAFT,
                    'revision_number' => 0,
                    'approval_level' => 1,
                    'reference_number' => $request->reference_number ?? null,
                    'currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                    'remarks' => $request->final_remarks,
                ]);
            }
            
            if ($request->item_id && count($request->item_id) > 0) {
                $data = $request->item_id;
            } else if($request->generated == 1) {
                $data = self::getAllItems($request);
            } else {
                $data = [];
            }
            if (empty($data)) {
                if (!$request -> generated == "1")
                {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);

                }
            }
            foreach ($data as $itemKey => $items) {
                $uom = Unit::find($items['uom_id'] ?? ($request->uom_id[$itemKey] ?? null));
                if(!isset($items['psv_item_id']) && !isset($request->psv_item_id[$itemKey])){
                    $item = Item::find($items['item_id'] ?? ($request->item_id[$itemKey] ?? null));
                }
                else{
                    $item = ErpPsvItem::find($items['psv_item_id'] ?? ($request->psv_item_id[$itemKey] ?? null));
                }
                $confirmedQty = $item->confirmed_qty ?? (isset($request->item_confirmed_qty[$itemKey]) ? $request->item_confirmed_qty[$itemKey] : 0);
                $unconfirmedQty = $item->unconfirmed_qty ?? (isset($request->item_unconfirmed_qty[$itemKey]) ? $request->item_unconfirmed_qty[$itemKey] : 0);
                $verifiedQty = isset($request->item_physical_qty[$itemKey]) ? $request->item_physical_qty[$itemKey] : 0;
                $adjustedQty = $verifiedQty - $confirmedQty;
                $rate = $item->rate ?? (isset($request->item_rate[$itemKey]) ? $request->item_rate[$itemKey] : 0);
                $total_amount = $rate * $verifiedQty;
    
                $psvItemData = [
                    'psv_header_id'   => $psv->id,
                    'item_id'         => $item->item_id ?? $item->id,
                    'item_code'       => $item->item_code,
                    'item_name'       => $item->item_name,
                    'uom_id'          => $uom->id ?? null,
                    'uom_code'        => $uom->name ?? null,
                    'confirmed_qty'   => $confirmedQty,
                    'unconfirmed_qty' => $unconfirmedQty,
                    'verified_qty'    => $verifiedQty,
                    'adjusted_qty'    => $adjustedQty,
                    'rate'            => $rate,
                    'total_amount'    => $total_amount,
                    'remarks'         => $items['item_remarks'] ?? ($request->item_remarks[$itemKey] ?? null),
                ];
                // dd($request->psv_item_id[$itemKey]);
                    $psvItemId = $request->psv_item_id[$itemKey] ?? null;
                
                if ($psvItemId) {
                    $psvItem = ErpPsvItem::find($psvItemId);
                
                    // Check if item exists before updating
                    if ($psvItem) {
                        $psvItem->fill($psvItemData);
                        $psvItem->save();
                    } else {
                        // Fallback to create if somehow item is not found
                        $psvItem = ErpPsvItem::create($psvItemData);
                    }
                } else {
                    // Create new record
                    $psvItem = ErpPsvItem::create($psvItemData);
                }

                   
                // Determine item attributes from request or items array
                
                if (is_array($items)) {
                    $itemAtts = $items['item_attributes'] ?? [];
                } else {
                    $itemAtts = isset($request->item_attributes[$itemKey]) 
                        ? json_decode($request->item_attributes[$itemKey], true) 
                        : ($items['item_attributes'] ?? []);

                    // Check if JSON decoding failed
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($itemAtts)) {
                        return response()->json([
                            'message' => 'Item No. ' . ($itemKey + 1) . ' has invalid attributes',
                            'error' => ''
                        ], 422);
                    }
                }

                // Check if item requires attributes
                $itemRequiredAtts = $item->itemAttributes ?: [];
                if (!empty($itemRequiredAtts)) {
                    // Compare counts or better yet, compare keys/names to ensure all required attributes are selected
                    if (count($itemRequiredAtts) !== count($itemAtts)) {
                        DB::rollBack(); // rollback transaction if using one
                        return response()->json([
                            'message' => 'Item No. ' . ($itemKey + 1) . ' does not have all required attributes selected',
                            'error' => ''
                        ], 422);
                    }
                }

                // Proceed with saving or processing $item and $itemAtts
                foreach ($itemAtts as $attribute) {
                    $attribute = is_array($attribute)? $attribute : $attribute->toArray();  
                    $attributeVal = "";
                    $attributeValId = null;
                    $attributeGrp = "";
                    $attributeGrpId = null;
                    if(isset($attribute['values_data']))
                    {
                        foreach ($attribute['values_data'] as $valData) {
                            if ($valData['selected']) {
                                $attributeVal = $valData['value'];
                                $attributeValId = $valData['id'];
                                $attributeGrp = $attribute['group_name'];
                                $attributeGrpId = $attribute['attribute_group_id'];
                                break;
                            }
                        }
                    }
                    else {
                        $attributeVal = $attribute['attribute_value'] ?? null;
                        $attributeValId = $attribute['attribute_id'] ?? null;
                        $attributeGrp = $attribute['attribute_name'] ?? null;
                        $attributeGrpId = $attribute['group_id'] ?? null;
                    }
                    $itemAttribute = ErpPsvItemAttribute::updateOrCreate([
                        'psv_id' => $psv->id,
                        'psv_item_id' => $psvItem->id,
                        'item_attribute_id' => $attribute['item_attribute_id'] ?? $attribute['id'],
                    ], [
                        'item_code' => $psvItem->item_code,
                        'attribute_name' => $attributeGrp,
                        'attr_name' => $attributeGrpId,
                        'attribute_value' => $attributeVal,
                        'attr_value' => $attributeValId,
                    ]);
                    $itemAttributeIds[] = $itemAttribute->id;
                }
            }
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approvalLogic = self::handleApprovalLogic($request, $psv);
            }
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $psv->uploadDocuments($file, 'psv_header', false);
                }
            }
    
            if (in_array($psv->document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                $stock = self::maintainStockLedger($psv);
                if ($stock) {
                    DB::rollBack();
                    return response()->json(['message' => $stock], 422);
                }
                else{
                    $items = $psv->items->where('adjusted_qty',0);
                    foreach ($items as $item) {
                        $atts_data = $item->attributes;
                        foreach ($atts_data as $att) {
                            $att->delete();
                        }
                        $item->delete();
                    }
                }
            }
            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpPsvDynamicField::class, $psv -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            DB::commit();
            return response()->json([
                'message' => "Physical Stock Verification created successfully",
                'redirect_url' => route('psv.index')
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex->getLine() . ' in ' . $ex->getFile(),
            ], 500);
        }
    }
    
    private function handleApprovalLogic(Request $request, ErpPsvHeader $psv)
    {
        $bookId = $psv->book_id;
        $docId = $psv->id;
        $currentLevel = $psv->approval_level;
        $revisionNumber = $psv->revision_number ?? 0;
        $modelName = get_class($psv);
        $attachments = $request->file('attachments');
        $actionType = $request->action_type ?? '';
        $remarks = $psv->remarks;

        if (($psv->document_status === ConstantHelper::APPROVED ||
            $psv->document_status === ConstantHelper::APPROVAL_NOT_REQUIRED) && 
            $actionType === 'amendment') {

            $revisionNumber++;
            $psv->revision_number = $revisionNumber;
            $psv->approval_level = 1;
            $psv->revision_date = now();

            $amendRemarks = $request->amend_remarks ?? $remarks;
            $amendAttachments = $request->file('amend_attachments') ?? $attachments;

            Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, 'amendment', 0, $modelName);

            $checkAmendment = Helper::checkAfterAmendApprovalRequired($bookId);
            if (isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                $totalValue = $psv->grand_total_amount ?? 0;
                $psv->document_status = Helper::checkApprovalRequired($bookId, $totalValue);
            } else {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'approve', 0, $modelName);
                $psv->document_status = ConstantHelper::APPROVED;
            }

            if ($psv->document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'submit', 0, $modelName);
            }
        } else {
            if ($request->document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'submit', 0, $modelName);
                $totalValue = $psv->grand_total_amount ?? $psv->total_amount ?? 0;
                $psv->document_status = Helper::checkApprovalRequired($bookId, $totalValue);
            } else {
                $psv->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
        }

        $psv->save();
    }


    private static function maintainStockLedger(ErpPsvHeader $psv)
    {
        $items = $psv->items;
        $issueDetailIds = $items -> where('adjusted_qty',"<",0) -> pluck('id') -> toArray();
        $receiptDetailIds = $items -> where('adjusted_qty',">",0) -> pluck('id') -> toArray();
        if($issueDetailIds)
        {
            $issueRecords = InventoryHelper::settlementOfInventoryAndStock($psv->id, $issueDetailIds, ConstantHelper::PSV_SERVICE_ALIAS, $psv->document_status, 'issue');
            if(!$issueRecords['status'] == 'success')
            {
                return $issueRecords['message'];                    
            }
        }
        if($receiptDetailIds)
        {
            $receiptRecords = InventoryHelper::settlementOfInventoryAndStock($psv->id, $receiptDetailIds, ConstantHelper::PSV_SERVICE_ALIAS, $psv->document_status, 'receipt');
            if(!$receiptRecords['status'] == 'success')
            {
                return $receiptRecords['message'];                    
            }
        }
        return null;
        
    }

    public function revokePSV(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpPsvHeader::find($request -> id);
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
    //Function to get all items of pwo
    public function getMoItemsForPulling(Request $request)
    {
        try {
            $selectedIds = $request -> selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
            if ($request -> doc_type === ConstantHelper::MO_SERVICE_ALIAS) {
                $referedHeaderId = MfgOrder::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
                $order = MoItem::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    })-> when($request -> store_id, function ($storeQuery) use($request) {
                        $storeQuery -> where('store_id', $request -> store_id);
                    }) -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
                    -> when($request -> book_id, function ($bookQuery) use($request) {
                        $bookQuery -> where('book_id', $request -> book_id);
                    }) -> when($request -> document_id, function ($docQuery) use($request) {
                        $docQuery -> where('id', $request -> document_id);
                    }) -> when($request -> location_id, function ($docQuery) use($request) {
                        $docQuery -> where('store_id', $request -> location_id);
                    }) -> when($request -> station_id, function ($docQuery) use($request) {
                        $docQuery -> where('station_id', $request -> station_id);
                    });
                }) -> with('attributes') -> with('uom') -> with('mo') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                }) -> whereColumn('qty', ">", 'mi_qty');
            } else if ($request -> doc_type === ConstantHelper::PWO_SERVICE_ALIAS) {
                $referedHeaderId = ErpProductionSlip::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
                $order = ErpPwoItem::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    })-> when($request -> store_id, function ($storeQuery) use($request) {
                        $storeQuery -> where('location_id', $request -> store_id);
                    })-> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
                    -> when($request -> book_id, function ($bookQuery) use($request) {
                        $bookQuery -> where('book_id', $request -> book_id);
                    }) -> when($request -> document_id, function ($docQuery) use($request) {
                        $docQuery -> where('id', $request -> document_id);
                    });
                }) -> with('attributes') -> with('uom') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                }) -> whereColumn('order_qty', ">", 'mi_qty');
            }  else if ($request -> doc_type === ConstantHelper::PI_SERVICE_ALIAS || $request -> doc_type === "pi") {
                $requesterType = $request -> requester_type ?? 'Department';
                $referedHeaderId = PurchaseIndent::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
                $order = PiItem::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId, $requesterType) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    }) -> where('requester_type', $request -> requester_type ?? 'Department')
                    -> when($requesterType == 'Department', function ($storeQuery) use($request) {
                        $storeQuery -> when($request -> requester_department_id, function ($departQuery) use($request) {
                            $departQuery -> where('department_id', $request -> requester_department_id);
                        });
                    })
                    -> when($requesterType == 'User', function ($storeQuery) use($request) {
                        $storeQuery -> when($request -> requester_user_id, function ($userQuery) use($request) {
                            $userQuery -> where('user_id', $request -> requester_user_id);
                        });
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
                    -> when($request -> book_id, function ($bookQuery) use($request) {
                        $bookQuery -> where('book_id', $request -> book_id);
                    }) -> when($request -> document_id, function ($docQuery) use($request) {
                        $docQuery -> where('id', $request -> document_id);
                    });
                }) -> with('attributes') -> with('uom') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                }) -> whereColumn('indent_qty', '>', 'mi_qty');
            }
            else {
                $order = null;
            }
            if ($request -> item_id && isset($order)) {
                $order = $order -> where('item_id', $request -> item_id);
            }
            $order = isset($order) ? $order -> get() : new Collection();
            foreach ($order as $currentOrder) {
                $currentOrder -> store_location_code = $currentOrder -> header -> store_location ?-> store_name;
                $currentOrder -> avl_stock = $currentOrder -> getAvlStock($request -> store_id_from);
                $currentOrder -> department_code = $currentOrder ?-> header ?-> department ?-> name;
                $currentOrder -> station_name = $currentOrder ?-> header ?-> station ?-> name;
            }
            $order = $order -> values();
            return response() -> json([
                'data' => $order
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'message' => 'Some internal error occurred',
                'error' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
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
    public function generatePdf(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
        ->where('addressable_id', $user->organization_id)
        ->where('addressable_type', Organization::class)
        ->first();
        $mx = ErpPsvHeader::with([
            'store',
            'sub_store',
            'book',
            'items.item.specifications',
            'items.item.alternateUoms.uom',
            'items.item.uom',
        ])
        ->find($id);

        // Add item_attributes to each item
        if ($mx && $mx->items) {
            foreach ($mx->items as $item) {
                $item->item_attributes = $item->get_attributes_array();
            }
        }

        // $creator = AuthUser::with(['authUser'])->find($mx->created_by);
        // dd($creator,$mx->created_by);
        $shippingAddress = $mx?->from_store?->address;
        $billingAddress = $mx?->to_store?->address;

        $approvedBy = Helper::getDocStatusUser(get_class($mx), $mx -> id, $mx -> document_status);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mx->document_status] ?? '';

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
            'psv' => $mx,
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
            'docStatusClass' => $docStatusClass,
            'approvedBy' => $approvedBy,
        ];
        $pdf = PDF::loadView('PSV.psv', $data_array);
        // $pdfPath = storage_path('app/temp.pdf');
        // file_put_contents($pdfPath, $pdf->output());

        // $linearizedPath = storage_path('app/linearized.pdf');

        // // Run Ghostscript to linearize
        // exec("gs -dNOPAUSE -dBATCH -dFastWebView=true -sDEVICE=pdfwrite -sOutputFile={$linearizedPath} {$pdfPath}");

        // return response()->file($linearizedPath, [
        //     'Content-Type' => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="' . $mx->book_code . '-' . $mx->document_number . '.pdf"',
        // ]);
        return $pdf->stream('Physical Stock Verification.pdf');
    }
    // public function report(){
    //     $issue_data = ErpPsvHeader::where('issue_type', 'Consumption')
    //         ->withWhereHas('items', function ($query) {
    //             $query->whereHas('attributes', function ($subQuery) {
    //                 $subQuery->where('attribute_name', 'TYPE'); // Ensure the attribute name is 'TYPE'
    //             }, '=', 1); // Ensure only one attribute exists
    //         })
    //         ->get();
    //     $issue_items_ids = ErpPsvItem::whereIn('psv_header_id',[$issue_data->pluck('id')])->pluck('id');
    //     $return_data = ErpMrItem::whereIn('psv_item_id',[$issue_items_ids])->get();
    //     return view('psv.report',[
    //         'issues' =>$issue_data,
    //         'return' =>$return_data,
    //     ]);
    // }
    public function report(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PSV_SERVICE_ALIAS;
        $redirectUrl = route('psv.report');
        $requesters = ErpPsvHeader::with(['requester'])->withDefaultGroupCompanyOrg()->bookViewAccess($pathUrl)->orderByDesc('id')->where('issue_type','Consumption')->where('requester_type',"User")->get()->unique('user_id')
        ->map(function ($item) {
            return [
                'id' => $item->requester()->first()->id ?? null,
                'name' => $item->requester()->first()->name ?? 'N/A',
            ];
        });
        if ($request->ajax()) {
            try {
                // Fetch Material Issues with Related Items and Attributes
                $docs = ErpPsvHeader::with('requester')->where('issue_type', 'Consumption')
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
                $issue_data = ErpPsvItem::with(['header'])->whereIn('psv_header_id', $docs->pluck('id'))->orderByDesc('id')->get();
                $issue_item_ids = $issue_data -> pluck('id');
                // Fetch corresponding return data
                $return_data = ErpMrItem::whereIn('psv_item_id', $issue_item_ids)
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
                            $used = $return_data->where('psv_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'RETURN OLD';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();

                            $returned = $return_data->where('psv_item_id', $row->id)
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
    return view('psv.report',['requesters'=>$requesters]);
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

    public function postPsv(Request $request)
    {
        try {
            DB::beginTransaction();
            $saleInvoice = ErpPsvHeader::find($request->document_id);
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
    public function itemList(Request $request)
    {
        $items = StockLedger::whereNull('utilized_id')->where('store_id',$request->store_id)->where('sub_store_id',$request->sub_store_id)->where('transaction_type',"receipt");

        $items = $items->get()->groupBy(function ($item) {
            return $item->item_id . '-' . json_encode($item->item_attributes);
        })->map(function ($group) {
            $firstItem = $group->first();
            $confirmedQty = 0;
            $unconfirmedQty = 0;
            $attributes = json_decode($firstItem->item_attributes, true);
            foreach ($attributes as $index => $attribute) {
                $att_grp = $attribute['attr_name'] ?? null;
                $att_g = AttributeGroup::find($att_grp);
                $attributes[$index]['short_name'] = $att_g->short_name ?? $att_g->name ?? null;
            }
            $firstItem->item_attributes = json_encode($attributes);
            foreach ($group as $item) {
                if (in_array($item->document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                    $confirmedQty += $item->receipt_qty;
                } else {
                    $unconfirmedQty += $item->receipt_qty;
                }
            }

            $firstItem->confirmed_qty = $confirmedQty;
            $firstItem->unconfirmed_qty = $unconfirmedQty;

            return $firstItem;
        })->values();
        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function getAllItems(Request $request){
        $user = Helper::getAuthenticatedUser();
        if($request?->generated)
        {
            $model= Item::class;
        }
        else{
            $model = ErpPsvItem::class;
        }
        $items = $model::with(['subTypes', 'hsn', 'uom', 'category', 'subcategory','itemAttributes'])
            ->when($request->filled('hsn_id'), function ($query) use ($request) {
            $query->where('hsn_id', $request->hsn_id);
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
            $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('subcategory_id'), function ($query) use ($request) {
            $query->where('subcategory_id', $request->subcategory_id);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
            $query->whereHas('subtype', function ($subQuery) use ($request) {
                $subQuery->where('sub_type_id', $request->type);
            });
            })
            ->when($request->filled('item'), function ($query) use ($request) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('item_name', 'like', '%' . $request->item . '%')
                ->orWhere('item_code', 'like', '%' . $request->item . '%');
            });
            })
            ->where('status', ConstantHelper::ACTIVE)
            ->where('type', ConstantHelper::GOODS)
            ->withDefaultGroupCompanyOrg()
            ->get();
        $index=0;
        $ledgerReport = self::getCurrentLedgerReport();
        $itemCombinations = $ledgerReport;
        // Code to get all item attribute combination 
        // $itemCombinations = $items->flatMap(function ($item) use ($request, &$index) {
        //     $combinations = [];
        //     $attributes = json_decode($item->itemAttributes, true) ?? [];
        
        //     $groupedAttributes = collect($attributes)->groupBy('attribute_group_id');
        //     $attributeCombinations = [[]]; // Start with a base empty combination
        
        //     foreach ($groupedAttributes as $groupId => $groupAttributes) {
        //         $attgroup = AttributeGroup::find($groupId);
        
        //         // Resolve actual attributes for this group
        //         $attributeOptions = collect();
        
        //         $attributeOptions = collect();

        //         foreach ($groupAttributes as $attribute) {
        //             // Get all attributes from the group, based on all_checked or specific ids
        //             if (!empty($attribute['all_checked'])) {
        //                 $options = Attribute::where('attribute_group_id', $groupId)
        //                     ->get(['id', 'value']);
        //             } else {
        //                 $options = Attribute::whereIn('id', $attribute['attribute_id'] ?? [])
        //                     ->get(['id', 'value']);
        //             }
        //             // Get the item_attribute_id for the current group
        //             $options = $options->map(function ($option) use ($attribute) {
        //                 return [
        //                     'id' => $option->id,
        //                     'value' => $option->value,
        //                     'item_attribute_id' => $attribute['id'] ?? null,
        //                 ];
        //             });
        //             $attributeOptions = $attributeOptions->merge($options);
        //         }

        //         $newCombinations = [];
        //         foreach ($attributeCombinations as $combination) {
        //             foreach ($attributeOptions as $attr) {
        //                 $newCombinations[] = array_merge($combination, [
        //                     [
        //                         'group_id' => $groupId,
        //                         'attribute_id' => $attr['id'],
        //                         'attribute_value' => $attr['value'],
        //                         'attribute_name' => $attgroup?->name,
        //                         'short_name' => $attgroup?->short_name,
        //                         'item_attribute_id' => $attr['item_attribute_id'] ?? null,
        //                     ]
        //                 ]);
        //             }
        //         }
        
        //         $attributeCombinations = $newCombinations;
        //     }
        //     foreach ($attributeCombinations as $combination) {
        //         $attributeIds = array_column($combination, 'attribute_id');
        //         $stock = InventoryHelper::totalInventoryAndStock($item->id, $attributeIds, $item->uom_id, $request->store_id, $request->sub_store_id);
                
        //         $combinations[] = [
        //             'item_id' => $item->id,
        //             'item_name' => $item->item_name,
        //             'item_code' => $item->item_code,
        //             'item_attributes' => $combination,
        //             'selected' => $attributeIds,
        //             'item' => $item,
        //             'uom_id' => $item->uom_id,
        //             'inventory_uom_id' => $item->uom_id,
        //             'inventory_uom' => $item->uom->name,
        //             'confirmed_qty'=> $stock['confirmedStocks'] ?? 0,
        //             'unconfirmed_qty'=> $stock['pendingStocks'] ?? 0,
        //             'reserve_qty'=> $stock['reserve_qty'] ?? 0,
        //             'rate' => $stock['rate'] ?? null,
        //         ];
        //     }
            
        //     $index++;
        //     return $combinations;
        // });
        $paginatedCombinations = collect($itemCombinations);
        return $paginatedCombinations->toArray();

    }
    public function getCurrentLedgerReport()
    {

    }
    public function pullItems($doc)
    {
         // Now manually load paginated items (25 per page)
         $items = ErpPsvItem::with([
            'item.specifications',
            'item.alternateUoms.uom',
            'item.uom',
            'item.hsn',
        ])
        ->where('psv_header_id', $doc->id)
        ->paginate(25); // Laravel paginator

        $items->getCollection()->transform(function ($item) {
            $item->attributes_array = $item->get_attributes_array();
            return $item;
        });
    // Pass $items separately to the view or return response accordingly
            return $items;
    }
    public function searchItems(Request $request)
    {
        $items = ErpPsvItem::with([
            'item.specifications',
            'item.alternateUoms.uom',
            'item.uom'
        ])
        ->where('psv_header_id', $request->document_id)
        ->when($request->filled('item_name_code'), function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('item_id', $request->item_name_code);
            });
        })
        ->when($request->filled('hsn'), function ($query) use ($request) {
            $query->whereHas('item.hsn', function ($subQuery) use ($request) {
                $subQuery->where('id', $request->hsn);
            });
        })
        ->when($request->filled('category'), function ($query) use ($request) {
            $query->whereHas('item.category', function ($subQuery) use ($request) {
                $subQuery->where('id', $request->category);
            });
        })
        ->when($request->filled('sub_category'), function ($query) use ($request) {
            $query->whereHas('item.subCategory', function ($subQuery) use ($request) {
                $subQuery->where('id', $request->sub_category);
            });
        })
        ->when($request->filled('sub_type'), function ($query) use ($request) {
            $query->whereHas('item.subTypes', function ($subQuery) use ($request) {
                $subQuery->where('id', $request->sub_type);
            });
        })
        ->when($request->filled('selected_attributes'), function ($query) use ($request) {
            foreach ($request->selected_attributes as $attributeId) {
                $query->whereHas('attributes', function ($subQuery) use ($attributeId) {
                    $subQuery->where('attr_value', $attributeId);
                });
            }
        });

        if($request->filled('changed_item'))
        {
            $item = ErpPsvItem::whereIn('id',array_keys($request->changed_item))->get();
            foreach($item as $i)
            {
                $i->verified_qty = $request->changed_item[$i->id]['physical_qty'];
                $balanceQty = $i->verified_qty - $i->confirmed_qty;
                $i->adjusted_qty = $balanceQty;
                $i->rate = $request->changed_item[$i->id]['rate'];
                $i->total_amount = $i->verified_qty * $i->rate;
                $i->save();
            }
        }

        // Paginate the results
        $paginatedItems = $items->paginate(20);

        // Transform each item to include 'attributes_array'
        $paginatedItems->getCollection()->transform(function ($item) {
            $item->attributes_array = $item->get_attributes_array();
            return $item;
        });

        return response()->json($paginatedItems, 200);
    }

   

    public function PSVReport(Request $request)
    {
        $pathUrl = route('psv.index');
        $orderType = [ConstantHelper::PSV_SERVICE_ALIAS];
        $PSV = ErpPsvHeader::with('items') -> bookViewAccess($pathUrl) 
        -> withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> orderByDesc('id');
        //Book Filter
        $PSV = $PSV -> when($request -> book_id, function ($bookQuery) use($request) {
            $bookQuery -> where('book_id', $request -> book_id);
        });
        //Document Id Filter
        $PSV = $PSV -> when($request -> document_number, function ($docQuery) use($request) {
            $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
        });
        //Location Filter
        $PSV = $PSV -> when($request -> location_id, function ($docQuery) use($request) {
            $docQuery -> where('store_id', $request -> location_id);
        });
        //Company Filter
        $PSV = $PSV -> when($request -> company_id, function ($docQuery) use($request) {
            $docQuery -> where('store_id', $request -> company_id);
        });
        //Organization Filter
        $PSV = $PSV -> when($request -> organization_id, function ($docQuery) use($request) {
            $docQuery -> where('organization_id', $request -> organization_id);
        });
        //Document Status Filter
        $PSV = $PSV -> when($request -> doc_status, function ($docStatusQuery) use($request) {
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
        $PSV = $PSV -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
        $dateRanges = explode('to', $dateRange);
        if (count($dateRanges) == 2) {
                $fromDate = Carbon::parse(trim($dateRanges[0])) -> format('Y-m-d');
                $toDate = Carbon::parse(trim($dateRanges[1])) -> format('Y-m-d');
                $dateRangeQuery -> whereDate('document_date', ">=" , $fromDate) -> where('document_date', '<=', $toDate);
        }
        });
        //Item Id Filter
        $PSV = $PSV -> when($request -> item_id, function ($itemQuery) use($request) {
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
        $PSV = $PSV -> whereNot('document_status',ConstantHelper::DRAFT) -> get();
        $processedSalesOrder = collect([]);
        foreach ($PSV as $p) {
            foreach ($p -> items as $psvItem) {
                $reportRow = new stdClass();
                //Header Details
                $header = $psvItem -> header;
                $reportRow -> id = $header -> id;
                $reportRow -> book_name = $header -> book_code;
                $reportRow -> document_number = $header -> document_number;
                $reportRow -> document_date = $header -> document_date;
                $reportRow -> store_name = $header -> store_code;
                $reportRow -> sub_store_name = $header -> sub_store_code;
                //Item Details
                $reportRow -> item_name = $psvItem -> item_name;
                $reportRow -> item_code = $psvItem -> item_code;
                $reportRow -> hsn_code = $psvItem -> item -> hsn ?-> code;
                $reportRow -> uom_name = $psvItem -> uom ?-> name;
                $reportRow -> customer_currency = $psvItem -> header ?-> currency_code ?? " ";
                //Amount Details
                $reportRow -> physical_qty = number_format($psvItem -> verified_qty, 2);
                $reportRow -> confirmed_stock = number_format($psvItem -> confirmed_qty ?? 0.00, 2);
                $reportRow -> unconfirmed_stock = number_format($psvItem -> unconfirmed_qty ?? 0.00, 2);
                $reportRow -> adjusted_qty = $psvItem ?-> adjusted_qty ?? " ";
                $reportRow -> rate = number_format($psvItem -> rate, 2);
            $reportRow -> total_amount = number_format($psvItem -> total_amount, 2);
                //Delivery Schedule UI
                // $deliveryHtml = '';
                // if (count($psvItem -> item_deliveries) > 0) {
                //     foreach ($psvItem -> item_deliveries as $itemDelivery) {
                //         $deliveryDate = Carbon::parse($itemDelivery -> delivery_date) -> format('d-m-Y');
                //         $deliveryQty = number_format($itemDelivery -> qty, 2);
                //         $deliveryHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$deliveryDate</strong> : $deliveryQty</span>";
                //     }
                // } else {
                //     $parsedDeliveryDate = Carbon::parse($psvItem -> delivery_date) -> format('d-m-Y');
                //     $deliveryHtml .= "$parsedDeliveryDate";
                // }
                // $reportRow -> delivery_schedule = $deliveryHtml;
                //Attributes UI
                $attributesUi = '';
                if (count($psvItem -> attributes) > 0) {
                    foreach ($psvItem -> attributes as $psvAttribute) {
                        $attrName = $psvAttribute -> attribute_name;
                        $attrValue = $psvAttribute -> attribute_value;
                        $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName : $attrValue </span>";
                    }
                } else {
                    $attributesUi = 'N/A';
                }
                $reportRow -> item_attributes = $attributesUi;
                //Main header Status
                $reportRow -> status = $header -> document_status;
                $processedSalesOrder -> push($reportRow);
            }
        }
            return DataTables::of($processedSalesOrder) ->addIndexColumn()
        ->editColumn('status', function ($row) use($orderType) {
            $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->status ?? ConstantHelper::DRAFT];    
            $displayStatus = ucfirst($row -> status);   
            $editRoute = null;
            $editRoute = route('psv.edit', ['id' => $row->id]);
            return "
            <div style='text-align:right;'>
                <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                    <a href='" . $editRoute . "'>
                        <i class='cursor-pointer' data-feather='eye'></i>
                    </a>
            </div>
        ";
        })
        ->rawColumns(['item_attributes','delivery_schedule','status'])
        ->make(true);
    }
}