<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\Inventory\StockReservation;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TransactionReportHelper;
use App\Helpers\UserHelper;
use App\Http\Requests\ErpMaterialIssueRequest;
use App\Lib\Services\WHM\WhmJob;
use App\Models\Address;
use App\Helpers\DynamicFieldHelper;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Configuration;
use App\Models\ErpMiDynamicField;
use App\Models\AuthUser;
use App\Models\Country;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpInvoiceItem;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMaterialIssueHeaderHistory;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpMiItem;
use App\Models\ErpMiItemAttribute;
use App\Models\ErpMiItemLocation;
use App\Models\ErpMiItemLotDetail;
use App\Models\ErpMrItem;
use App\Models\ErpProductionSlip;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpPwoItem;
use App\Models\ErpRack;
use App\Models\ErpSaleOrder;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpVendor;
use App\Models\Item;
use App\Models\JobOrder\JobOrder;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoProduct;
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
use App\Services\MaterialIssue\MaterialIssue;
use Carbon\Carbon;
use PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use Yajra\DataTables\DataTables;
use App\Helpers\Configuration\Helper as ConfigurationHelper;
use App\Helpers\Configuration\Constants as ConfigurationConstant;
use App\Models\ErpPslipItem;
use App\Services\MaterialIssue\MiDelete;

class ErpMaterialIssueController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME;
        $redirectUrl = route('material.issue.index');
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $createRoute = route('material.issue.create');
        $typeName = ConstantHelper::MATERIAL_ISSUE_SERVICE_NAME;
        $autoCompleteFilters = self::getBasicFilters();
        if ($request -> ajax()) {
            try {
                $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
                $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
                //Date Filters
                $dateRange = $request -> date_range ??  null;
                $docs = ErpMaterialIssueHeader::withDefaultGroupCompanyOrg() ->  bookViewAccess($pathUrl) ->  
                withDraftListingLogic() ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) 
                -> whereIn('from_store_id',$accessible_locations) ->  when($request -> customer_id, function ($custQuery) use($request) {
                    $custQuery -> where('customer_id', $request -> customer_id);
                }) -> when($request -> book_id, function ($bookQuery) use($request) {
                    $bookQuery -> where('book_id', $request -> book_id);
                }) -> when($request -> document_number, function ($docQuery) use($request) {
                    $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
                }) -> when($request -> location_id, function ($docQuery) use($request) {
                    $docQuery -> where('from_store_id', $request -> location_id);
                }) -> when($request -> company_id, function ($docQuery) use($request) {
                    $docQuery -> where('from_store_id', $request -> company_id);
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
                })  -> orderByDesc('id');
                return DataTables::of($docs) ->addIndexColumn()
                ->editColumn('document_status', function ($row) use($orderType) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                    $displayStatus = $row -> display_status;
                    $editRoute = route('material.issue.edit', ['id' => $row -> id]); 
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
                ->addColumn('curr_name', function ($row) {
                    return $row->org_currency ? ($row->org_currency?->short_name ?? $row->org_currency?->name) : 'N/A';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? 'N/A';
                })
                ->editColumn('requester_type', function ($row) {
                    if ($row -> issue_type === 'Consumption')
                    {
                        return $row -> requester_type;
                    }
                    else
                    {
                        return 'N/A';
                    }
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->customer?->company_name ?? 'NA';
                })
                ->addColumn('items_count', function ($row) {
                    return $row->items->count();
                })
                ->editColumn('total_item_value', function ($row) {
                    return number_format($row->total_item_value,2);
                })
                ->editColumn('total_discount_value', function ($row) {
                    return number_format($row->total_discount_value,2);
                })
                ->editColumn('total_tax_value', function ($row) {
                    return number_format($row->total_tax_value,2);
                })
                ->editColumn('total_expense_value', function ($row) {
                    return number_format($row->total_expense_value,2);
                })
                ->editColumn('grand_total_amount', function ($row) {
                    return number_format($row->total_amount,2);
                })
                ->editColumn('from_store_code',function($row){
                    return $row?->from_store?->store_name ?? "N/A";
                })
                ->editColumn('from_sub_store_code',function($row){
                    return $row?->from_sub_store?->name ?? "N/A";
                })
                ->editColumn('to_store_code',function($row){
                    return $row?->to_store?->store_name ?? "N/A";
                })
                ->editColumn('to_sub_store_code',function($row){
                    return $row?->to_sub_store?->name ?? "N/A";
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
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($pathUrl);
        $create_button = (isset($servicesBooks['services'])  && count($servicesBooks['services']) > 0 && 
        isset($selectedfyYear['authorized']) && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        return view('materialIssue.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 
            'create_button' => $create_button,
            'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME],
            'autoCompleteFilters' => $autoCompleteFilters]);
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
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $redirectUrl = route('material.issue.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME;
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
        $vendors = Vendor::select('id', 'company_name') -> withDefaultGroupCompanyOrg() 
        -> where('status', ConstantHelper::ACTIVE) -> get();
        $departments = UserHelper::getDepartments($user -> auth_user_id);
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
        $stockTypes = InventoryHelper::getStockType();
        $data = [
            'user' => $user,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => array(),
            'typeName' => $typeName,
            'stores' => $stores,
            'vendors' => $vendors,
            'departments' => $departments['departments'],
            'selectedDepartmentId' => $departments['selectedDepartmentId'],
            'selectedUserId' => null,
            'redirect_url' => $redirectUrl,
            'current_financial_year' => $selectedfyYear,
            'stockTypes' => $stockTypes
        ];
        return view('materialIssue.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        $parentUrl = request() -> segments()[0];
        $redirect_url = route('material.issue.index');
        $user = Helper::getAuthenticatedUser();
        $servicesBooks = [];
        if (isset($request -> revisionNumber))
        {
            $doc = ErpMaterialIssueHeaderHistory::with(['book', 'media_files']) -> with('items', function ($query) {
                $query -> with(['from_item_locations', 'to_item_locations']) -> with(['item' => function ($itemQuery) {
                    $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                }]);
            }) -> where('source_id', $id)->firstOrFail();
            $ogDoc = ErpMaterialIssueHeader::where('id',$id) -> firstOrFail();
        } else {
            $doc = ErpMaterialIssueHeader::with(['book', 'media_files']) -> with('items', function ($query) {
                $query -> with(['from_item_locations', 'to_item_locations']) -> with(['item' => function ($itemQuery) {
                    $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                }]);
            }) -> where("id", $id) -> firstOrFail();
            $ogDoc = $doc;
        }
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias);
        $revision_number = $doc->revision_number;
        $totalValue = ($doc -> total_item_value - $doc -> total_discount_value) + 
        $doc -> total_tax_value + $doc -> total_expense_value;
        $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, 
        $doc->approval_level, $doc -> created_by ?? 0, 'user', $revision_number);
        $books = Helper::getBookSeriesNew(ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, ) -> get();
        $revNo = $doc->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $doc->revision_number;
        }
        $docValue = $doc->total_amount ?? 0;
        $approvalHistory = collect([]);
        if ($doc -> document_status != ConstantHelper::DRAFT) {
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $ogDoc->id, $revNo, $docValue, $doc -> created_by);
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
        $typeName = ConstantHelper::MATERIAL_ISSUE_SERVICE_NAME;
        $vendors = Vendor::select('id', 'company_name') -> withDefaultGroupCompanyOrg()->where('status', ConstantHelper::ACTIVE) 
        -> get();
        $selectedfyYear = Helper::getFinancialYear($doc->document_date ?? Carbon::now()->format('Y-m-d'));
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
        $stockTypes = InventoryHelper::getStockType();
        $dynamicFieldsUI = $doc -> dynamicfieldsUi();
        $data = [
            'user' => $user,
            'series' => $books,
            'order' => $doc,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'revision_number' => $revision_number,
            'docStatusClass' => $docStatusClass,
            'typeName' => $typeName,
            'stores' => $stores,
            'vendors' => $vendors,
            'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($doc -> media_files)) : 10,
            'services' => $servicesBooks['services'],
            'departments' => $departments['departments'],
            'selectedDepartmentId' => $doc ?-> department_id,
            'current_financial_year' => $selectedfyYear,
            'selectedUserId' => $doc ?-> user_id,
            'dynamicFieldsUi' => $dynamicFieldsUI,
            'redirect_url' => $redirect_url,
            'stockTypes' => $stockTypes
        ];
        return view('materialIssue.create_edit', $data);  
        
    }
    public function store(ErpMaterialIssueRequest $request)
    {
        try {
            //Reindex
            $request -> item_qty =  array_values($request -> item_qty ?? []);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id ?? []);
            $request -> item_rate =  array_values($request -> item_rate ?? []);

            //Create Service
            $miService = new MaterialIssue();

            DB::beginTransaction();
            $authUser = Helper::getAuthenticatedUser();
            //Auth credentials
            $organization = Organization::find($authUser -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $itemAttributeIds = [];
            //Currency Check
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($organization -> currency -> id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422); 
            }
            //Create Case
            if (!$request -> material_issue_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpMaterialIssueHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $enforceUicScanning = ConfigurationHelper::getConfigurationValueOfOrg(ConfigurationConstant::ORG_CONFIG_ENFORCE_UIC_SCANNING, $organizationId);
            $materialIssue = null;
            $fromStore = ErpStore::find($request -> store_from_id);
            $toStoreId = ($request->store_to_id);
            $toSubStoreId = ($request -> issue_type === 'Sub Contracting' || $request -> issue_type === ConstantHelper::TYPE_JOB_ORDER ? $request -> vendor_store_id : $request -> sub_store_to_id);
            $toStore = ErpStore::find($toStoreId);
            $vendor = Vendor::find($request -> vendor_id);
            if($request -> requester_type == 'User') {
                $user = AuthUser::find($request->user_id);
            } else {
                $department = Department::find($request -> department_id);
            }
                        
            if ($request -> material_issue_id) { //Update
                $materialIssue = ErpMaterialIssueHeader::find($request -> material_issue_id);
                $materialIssue -> document_date = $request -> document_date;
                //Store and department keys
                //From
                $materialIssue -> from_store_id = $request -> store_from_id ?? null;
                $materialIssue -> from_sub_store_id = $request -> sub_store_from_id ?? null;
                $materialIssue -> from_station_id = $request -> station_from_id ?? null;
                $materialIssue -> from_store_code = $fromStore ?-> from_store_code ?? null;
                //To
                $materialIssue -> to_store_id = $toStoreId;
                $materialIssue -> to_sub_store_id = $toSubStoreId ?? null;
                $materialIssue -> to_station_id = $request -> station_to_id ?? null;
                $materialIssue -> to_store_code = $toStore ?-> to_store_code ?? null;
                $materialIssue -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup
                if(($materialIssue -> document_status == ConstantHelper::APPROVED || $materialIssue -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpMaterialIssueHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpMiItem', 'relation_column' => 'material_issue_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpMiItemAttribute', 'relation_column' => 'mi_item_id'],
                    ];
                    $a = Helper::documentAmendment($revisionData, $materialIssue->id);

                }
                $keys = ['deletedItemIds', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }
                //Delete Items
                $deletedItemIds = $deletedData['deletedItemIds'];
                $miDeleteService = new MiDelete();
                $miDeleteService -> deleteByRequest($deletedItemIds, $materialIssue);

            } else { //Create
                $materialIssue = ErpMaterialIssueHeader::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request -> book_id,
                    'book_code' => $request -> book_code,
                    'issue_type' => $request -> issue_type,
                    'enforce_uic_scanning' => $enforceUicScanning,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request -> document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    //FROM
                    'from_store_id' => $request -> store_from_id ?? null,
                    'from_sub_store_id' => $request -> sub_store_from_id ?? null,
                    'from_station_id' => $request -> station_from_id ?? null,
                    'from_store_code' => $fromStore ?-> store_name ?? null,
                    //TO
                    'to_store_id' => $toStoreId ?? null,
                    'to_store_code' => $toStore ?-> store_name ?? null,
                    'to_sub_store_id' => $toSubStoreId ?? null,
                    'to_station_id' => $request -> station_to_id ?? null,

                    'vendor_id' => $request -> issue_type == "Sub Contracting" ? ($request -> vendor_id) : null,
                    'vendor_code' => $request -> issue_type == "Sub Contracting" ? ($vendor ?-> company_name) : null,
                    'department_id' => $request -> department_id ?? null,
                    'department_code' =>isset($department) ? $department->name : null,
                    'requester_type' => $request -> requester_type ?? 'Department',
                    'user_id' => $request -> user_id ?? null,
                    'user_name' =>  isset($user) ? $user->name : null,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => $request -> final_remarks,
                    'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                    'total_item_value' => 0,
                    'total_discount_value' => 0,
                    'total_tax_value' => 0,
                    'total_expense_value' => 0,
                ]);
                // Shipping Address
                if ($materialIssue -> issue_type === "Sub Contracting" || $materialIssue -> issue_type === "Job Work") {
                    if ($materialIssue -> issue_type === "Sub Contracting" && isset($request -> jo_item_id[0])) {
                        $joItem = JoItem::find($request -> jo_item_id[0]);
                    } else if (isset($request -> jo_product_id[0])) {
                        $joItem = JoProduct::find($request -> jo_product_id[0]);
                    }
                    if (isset($joItem)) {
                        $vendorShippingAddress = $joItem ?-> header ?-> ship_address;
                        if (isset($vendorShippingAddress)) {
                            $vendorShippingAddress = $materialIssue -> vendor_shipping_address() -> create([
                                'address' => $vendorShippingAddress -> address,
                                'country_id' => $vendorShippingAddress -> country_id,
                                'state_id' => $vendorShippingAddress -> state_id,
                                'city_id' => $vendorShippingAddress -> city_id,
                                'type' => 'shipping',
                                'pincode' => $vendorShippingAddress -> pincode,
                                'phone' => $vendorShippingAddress -> phone,
                                'fax_number' => $vendorShippingAddress -> fax_number
                            ]);
                        }
                    }
                }
            }
            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpMiDynamicField::class, $materialIssue -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
                //Get Header Discount
                $totalHeaderDiscount = 0;
                $totalHeaderDiscountArray = [];
                //'Initialize' item discount to 0
                $itemTotalDiscount = 0;
                $itemTotalValue = 0;
                $totalTax = 0;
                $totalItemValueAfterDiscount = 0;
                $materialIssue -> save();
                //Seperate array to store each item calculation
                $itemsData = array();
                if ($request -> item_id && count($request -> item_id) > 0) {
                    //Items
                    $totalValueAfterDiscount = 0;
                    foreach ($request -> item_id as $itemKey => $itemId) {
                        $item = Item::find($itemId);
                        if (isset($item))
                        {
                            $itemValue = (isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0) * (isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0);
                            $itemDiscount = 0;
                            //Item Level Discount
                            $itemTotalValue += $itemValue;
                            $itemTotalDiscount += $itemDiscount;
                            $itemValueAfterDiscount = $itemValue - $itemDiscount;
                            $totalValueAfterDiscount += $itemValueAfterDiscount;
                            $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                            //Check if discount exceeds item value
                            if ($totalItemValueAfterDiscount < 0) {
                                return response() -> json([
                                    'message' => '',
                                    'errors' => array(
                                        'item_name.' . $itemKey => "Discount more than value"
                                    )
                                ], 422);
                            }
                            $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0);
                            $uom = Unit::find($request -> uom_id[$itemKey] ?? null);
                            array_push($itemsData, [
                                'material_issue_id' => $materialIssue -> id,
                                'item_id' => $item -> id,
                                'mo_item_id' => isset($request -> mo_item_id[$itemKey]) ? $request -> mo_item_id[$itemKey] : null,
                                'pwo_item_id' => isset($request -> pwo_item_id[$itemKey]) ? $request -> pwo_item_id[$itemKey] : null,
                                'pi_item_id' => isset($request -> pi_item_id[$itemKey]) ? $request -> pi_item_id[$itemKey] : null,
                                'jo_item_id' => isset($request -> jo_item_id[$itemKey]) ? $request -> jo_item_id[$itemKey] : null,
                                'jo_product_id' => isset($request -> jo_product_id[$itemKey]) ? $request -> jo_product_id[$itemKey] : null,
                                'pslip_item_id' => isset($request -> pslip_item_id[$itemKey]) ? $request -> pslip_item_id[$itemKey] : null,
                                'pslip_issue_type' => isset($request -> pslip_issue_type[$itemKey]) ? $request -> pslip_issue_type[$itemKey] : null,
                                'user_id' => isset($request -> item_user_id[$itemKey]) ? $request -> item_user_id[$itemKey] : null,
                                'department_id' => isset($request -> item_department_id[$itemKey]) ? $request -> item_department_id[$itemKey] : null,
                                'item_code' => $item -> item_code,
                                'item_name' => $item -> item_name,
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null, //Need to change
                                'uom_code' => isset($uom) ? $uom -> name : null,
                                //FROM
                                'from_store_id' => isset($request -> item_store_from[$itemKey]) ? $request -> item_store_from[$itemKey] : null,
                                'from_sub_store_id' => isset($request -> item_sub_store_from[$itemKey]) ? $request -> item_sub_store_from[$itemKey] : null,
                                'from_station_id' => isset($request -> item_station_from[$itemKey]) ? $request -> item_station_from[$itemKey] : null,
                                'from_store_code' => $fromStore ?-> store_code,
                                //TO
                                'to_store_id' => $toStore ?-> id,
                                'to_sub_store_id' => $toSubStoreId ?? null,
                                'to_station_id' => isset($request -> item_station_to[$itemKey]) ? $request -> item_station_to[$itemKey] : null,
                                'to_store_code' => $toStore ?-> store_code,
                                //Stock Type
                                'stock_type' => isset($request -> stock_type[$itemKey]) ? $request -> stock_type[$itemKey] : InventoryHelper::STOCK_TYPE_REGULAR,
                                'wip_station_id' => isset($request -> wip_station_id[$itemKey]) ? $request -> wip_station_id[$itemKey] : null,

                                'issue_qty' => isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0,
                                'inventory_uom_id' => $item -> uom ?-> id,
                                'inventory_uom_code' => $item -> uom ?-> name,
                                'inventory_uom_qty' => $inventoryUomQty,
                                'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
                                'item_discount_amount' => $itemDiscount,
                                'header_discount_amount' => 0,
                                'item_expense_amount' => 0, //Need to change
                                'header_expense_amount' => 0, //Need to change
                                'tax_amount' => 0,
                                'remarks' => isset($request -> item_remarks[$itemKey]) ? $request -> item_remarks[$itemKey] : null,
                                'value_after_discount' => $itemValueAfterDiscount,
                                'item_value' => $itemValue
                            ]);
                            
                        }
                    }
                    foreach ($itemsData as $itemDataKey => $itemDataValue) {
                        //Discount
                        $headerDiscount = 0;
                        $valueAfterHeaderDiscount = $itemDataValue['value_after_discount'] - $headerDiscount;
                        //Expense
                        $itemExpenseAmount = 0;
                        $itemHeaderExpenseAmount = 0;
                        //Tax
                        $itemTax = 0;
                        $totalTax += $itemTax;
                        $itemDepartment = Department::find($itemDataValue['department_id']);
                        $itemUser = AuthUser::find($itemDataValue['user_id']);
                        //Update or create
                        $itemRowData = [
                            'material_issue_id' => $materialIssue -> id,
                            'item_id' => $itemDataValue['item_id'],
                            'user_id' => $itemDataValue['user_id'],
                            'user_name' => $itemUser ?-> name,
                            'department_id' => $itemDataValue['department_id'],
                            'department_code' => $itemDepartment ?-> name,
                            'mo_item_id' => $itemDataValue['mo_item_id'],
                            'pwo_item_id' => $itemDataValue['pwo_item_id'],
                            'pi_item_id' => $itemDataValue['pi_item_id'],
                            'jo_item_id' => $itemDataValue['jo_item_id'],
                            'jo_product_id' => $itemDataValue['jo_product_id'],
                            'pslip_item_id' => $itemDataValue['pslip_item_id'],
                            'pslip_issue_type' => $itemDataValue['pslip_issue_type'],
                            'item_code' => $itemDataValue['item_code'],
                            'item_name' => $itemDataValue['item_name'],
                            'hsn_id' => $itemDataValue['hsn_id'],
                            'hsn_code' => $itemDataValue['hsn_code'],
                            'uom_id' => $itemDataValue['uom_id'], //Need to change
                            'uom_code' => $itemDataValue['uom_code'],
                            'from_store_id' => $itemDataValue['from_store_id'],
                            'from_sub_store_id' => $itemDataValue['from_sub_store_id'],
                            'from_staion_id' => $itemDataValue['from_station_id'],
                            'from_store_code' => $itemDataValue['from_store_code'],
                            'to_store_id' => $itemDataValue['to_store_id'],
                            'to_sub_store_id' => $itemDataValue['to_sub_store_id'],
                            'to_station_id' => $itemDataValue['to_station_id'],
                            'to_store_code' => $itemDataValue['to_store_code'],
                            'stock_type' => $itemDataValue['stock_type'],
                            'wip_station_id' => $itemDataValue['wip_station_id'],
                            'issue_qty' => $itemDataValue['issue_qty'],
                            'inventory_uom_id' => $itemDataValue['inventory_uom_id'],
                            'inventory_uom_code' => $itemDataValue['inventory_uom_code'],
                            'inventory_uom_qty' => $itemDataValue['inventory_uom_qty'],
                            'rate' => $itemDataValue['rate'],
                            'item_discount_amount' => $itemDataValue['item_discount_amount'],
                            'header_discount_amount' => $headerDiscount,
                            'item_expense_amount' => $itemExpenseAmount, //Need to change
                            'header_expense_amount' => $itemHeaderExpenseAmount, //Need to change
                            'total_item_amount' => ($itemDataValue['issue_qty'] * $itemDataValue['rate']) - ($itemDataValue['item_discount_amount'] + $headerDiscount) + ($itemExpenseAmount + $itemHeaderExpenseAmount) + $itemTax,
                            'tax_amount' => $itemTax,
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        if (isset($request -> mi_item_id[$itemDataKey])) {
                            $oldMiItem = ErpMiItem::find($request -> mi_item_id[$itemDataKey]);
                            $miItem = ErpMiItem::updateOrCreate(['id' => $request -> mi_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $miItem = ErpMiItem::create($itemRowData);
                        }
                        //Order Pulling condition 
                        if (isset($request -> mo_item_id[$itemDataKey])) {
                            //Back update in MO item
                            $moItem = MoItem::find($request -> mo_item_id[$itemDataKey]);
                            if (isset($moItem)) {
                                $moItem -> mi_qty = ($moItem -> mi_qty - (isset($oldMiItem) ? $oldMiItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                                $moItem -> save();
                            }
                        }
                        if (isset($request -> pwo_item_id[$itemDataKey])) {
                            //Back update in PWO item
                            $pwoItem = ErpPwoItem::find($request -> pwo_item_id[$itemDataKey]);
                            if (isset($pwoItem)) {
                                $pwoItem -> mi_qty = ($pwoItem -> mi_qty - (isset($oldMiItem) ? $oldMiItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                                $pwoItem -> save();
                            }
                        }
                        if (isset($request -> pi_item_id[$itemDataKey])) {
                            //Back update in PI item
                            $piItem = PiItem::find($request -> pi_item_id[$itemDataKey]);
                            if (isset($piItem)) {
                                $piItem -> mi_qty = ($piItem -> mi_qty - (isset($oldMiItem) ? $oldMiItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                                $piItem -> save();
                            }
                        }
                        if (isset($request -> jo_item_id[$itemDataKey])) {
                            //Back update in JO item
                            $joItem = JoItem::find($request -> jo_item_id[$itemDataKey]);
                            if (isset($joItem)) {
                                $joItem -> mi_qty = ($joItem -> mi_qty - (isset($oldMiItem) ? $oldMiItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                                $miItem -> jo_id = $joItem -> jo_id;
                                $miItem -> save();
                                $joItem -> save();
                            }
                        }
                        if (isset($request -> jo_product_id[$itemDataKey])) {
                            //Back update in JO product
                            $joProduct = JoProduct::find($request -> jo_product_id[$itemDataKey]);
                            if (isset($joProduct)) {
                                $joProduct -> mi_qty = ($joProduct -> mi_qty - (isset($oldMiItem) ? $oldMiItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                                $miItem -> jo_id = $joProduct -> jo_id;
                                $miItem -> save();
                                $joProduct -> save();
                            }
                        }
                        if (isset($request -> pslip_item_id[$itemDataKey]) && isset($request -> pslip_issue_type[$itemDataKey])) {
                            //Back update in Pslip Item
                            $pslipItem = ErpPslipItem::find($request -> pslip_item_id[$itemDataKey]);
                            if (isset($pslipItem)) {
                                $qtyKey = 'mi_accepted_qty';
                                if ($request -> pslip_issue_type[$itemDataKey] === 'B') {
                                    $qtyKey = 'mi_subprime_qty';
                                }
                                $pslipItem -> {$qtyKey} = ($pslipItem -> mi_qty - (isset($oldMiItem) ? $oldMiItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                                $pslipItem -> save();
                            }
                        }
                        //Item Attributes
                        if (isset($request -> item_attributes[$itemDataKey])) {
                            $attributesArray = json_decode($request -> item_attributes[$itemDataKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($attributesArray)) {
                                foreach ($attributesArray as $attributeKey => $attribute) {
                                    $attributeVal = "";
                                    $attributeValId = null;
                                    foreach ($attribute['values_data'] as $valData) {
                                        if ($valData['selected']) {
                                            $attributeVal = $valData['value'];
                                            $attributeValId = $valData['id'];
                                            break;
                                        }
                                    }
                                    $itemAttribute = ErpMiItemAttribute::updateOrCreate(
                                        [
                                            'material_issue_id' => $materialIssue -> id,
                                            'mi_item_id' => $miItem -> id,
                                            'item_attribute_id' => $attribute['id'],
                                        ],
                                        [
                                            'item_code' => $miItem -> item_code,
                                            'attribute_name' => $attribute['group_name'],
                                            'attr_name' => $attribute['attribute_group_id'],
                                            'attribute_value' => $attributeVal,
                                            'attr_value' => $attributeValId,
                                        ]
                                    );
                                    array_push($itemAttributeIds, $itemAttribute -> id);
                                }
                            } else {
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid attributes',
                                    'error' => ''
                                ], 422);
                            }
                        }
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);
                }
                ErpMiItemAttribute::where([
                    'material_issue_id' => $materialIssue -> id,
                    'mi_item_id' => $miItem -> id,
                ]) -> whereNotIn('id', $itemAttributeIds) -> delete();
                //Header TED (Discount)

                //Header TED (Expense)
                $totalValueAfterTax = $totalItemValueAfterDiscount + $totalTax;
                $totalExpenseAmount = 0;

                //Check all total values
                if ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount) + $totalExpenseAmount < 0)
                {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => 'Document Value cannot be less than 0'
                    ], 422);
                }
                $materialIssue -> total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
                $materialIssue -> total_item_value = $itemTotalValue;
                $materialIssue -> total_tax_value = $totalTax;
                $materialIssue -> total_expense_value = $totalExpenseAmount;
                $materialIssue -> total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;
                //Approval check
                //Approval check
                if ($request -> material_issue_id) { //Update condition
                    $bookId = $materialIssue->book_id; 
                    $docId = $materialIssue->id;
                    $amendRemarks = $request->amend_remarks ?? null;
                    $remarks = $materialIssue->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachment');
                    $currentLevel = $materialIssue->approval_level;
                    $modelName = get_class($materialIssue);
                    $actionType = $request -> action_type ?? "";
                    if(($materialIssue -> document_status == ConstantHelper::APPROVED || $materialIssue -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $materialIssue->revision_number + 1;
                        $actionType = 'amendment';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                        $materialIssue->revision_number = $revisionNumber;
                        $materialIssue->approval_level = 1;
                        $materialIssue->revision_date = now();
                        $amendAfterStatus = $materialIssue->document_status;
                        $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                        if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                            $totalValue = $materialIssue->grand_total_amount ?? 0;
                            $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        } else {
                            $actionType = 'approve';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }
                        if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }
                        $materialIssue->document_status = $amendAfterStatus;
                        $materialIssue->save();

                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $materialIssue->revision_number ?? 0;
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                            $totalValue = $materialIssue->grand_total_amount ?? 0;
                            $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                            $materialIssue->document_status = $document_status;
                        } else {
                            $materialIssue->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { //Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $materialIssue->book_id;
                        $docId = $materialIssue->id;
                        $remarks = $materialIssue->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $materialIssue->approval_level;
                        $revisionNumber = $materialIssue->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($materialIssue);
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    if ($request->document_status == 'submitted') {
                        $totalValue = $materialIssue->total_amount ?? 0;
                        $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        $materialIssue->document_status = $document_status;
                    } else {
                        $materialIssue->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                    $materialIssue -> save();
                }
                $materialIssue -> save();
                //Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $materialIssue->uploadDocuments($singleFile, 'material_issue', false);
                    }
                }
                //Stock Ledger Impact
                $errorMessage = $miService->maintainStockLedger($materialIssue);
                if ($errorMessage) {     
                    DB::rollBack();
                    return response() -> json([
                        'message' => $errorMessage
                    ], 422);
                }
                //Job - (Ignore amendment case)
                $miService -> createWhmJob($materialIssue, $authUser);
                
                DB::commit();
                $printOption = 'Material Issue';
                if ($materialIssue -> issue_type == "Location Transfer" || $materialIssue -> issue_type == "Sub Contracting" || $materialIssue -> issue_type == "Job Work")
                {
                    $printOption = 'Delivery Challan';
                }
                $redirect_url = route('material.issue.index');
                if(in_array($materialIssue->document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED] )) {
                    $redirect_url = route('material.issue.generate-pdf', ['id' => $materialIssue -> id, 'pattern' => $printOption]);
                }
                $module = ConstantHelper::MATERIAL_ISSUE_SERVICE_NAME;
                return response() -> json([
                    'message' => $module .  " created successfully",
                    'redirect_url' => $redirect_url
                ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }

    public function revokeMaterialIssue(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpMaterialIssueHeader::find($request -> id);
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
            $stores = ErpSubStore::select('id', 'name') -> where('status', ConstantHelper::ACTIVE)
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
    public function getMoItemsForPulling(Request $request)
    {
        try {
            $selectedIds = $request->selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
            $pslipType = $request -> pslip_pull_type ?? "";

            // Build the base query
            $baseQuery = match ($request->doc_type) {
                ConstantHelper::MO_SERVICE_ALIAS => MoItem::with(['header.store_location', 'header.sub_store', 'header.station', 'item', 'so', 'station', 'attributes', 'uom', 'mo'])
                    ->whereHas('header', function ($query) use ($request, $applicableBookIds, $selectedIds) {
                        $referedHeaderId = MfgOrder::whereIn('id', $selectedIds)->first()?->header?->id;
                        $query->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                            // ->when($request->location_id, fn($q) => $q->where('store_id', $request->location_id))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
                            ->when($request->station_id, fn($q) => $q->where('station_id', $request->station_id))
                            ->when($request->sub_store_id, fn($q) => $q->where('sub_store_id', $request->sub_store_id))
                            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                            ->whereIn('book_id', $applicableBookIds);
                    })
                    ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
                    ->when($request->so_id, fn($q) => $q->where('so_id', $request->so_id))
                    ->whereColumn('qty', '>', 'mi_qty')
                    ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds)),

                ConstantHelper::PWO_SERVICE_ALIAS => ErpPwoItem::with(['header', 'attributes', 'uom'])
                    ->whereHas('header', function ($query) use ($request, $applicableBookIds, $selectedIds) {
                        $referedHeaderId = ErpProductionWorkOrder::whereIn('id', $selectedIds)->first()?->header?->id;
                        $query->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                            ->when($request->store_id, fn($q) => $q->where('location_id', $request->store_id))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                            ->whereIn('book_id', $applicableBookIds);
                    })
                    ->whereColumn('order_qty', '>', 'mi_qty')
                    ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds)),

                ConstantHelper::PI_SERVICE_ALIAS, 'pi' => PiItem::with(['header', 'attributes', 'uom'])
                    ->whereHas('header', function ($query) use ($request, $applicableBookIds, $selectedIds) {
                        $requesterType = $request->requester_type ?? 'Department';
                        $referedHeaderId = PurchaseIndent::whereIn('id', $selectedIds)->first()?->header?->id;
                        $query->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                            ->where('requester_type', $requesterType)
                            ->when($requesterType === 'Department', fn($q) => $q->when($request->requester_department_id, fn($d) => $d->where('department_id', $request->requester_department_id)))
                            ->when($requesterType === 'User', fn($q) => $q->when($request->requester_user_id, fn($u) => $u->where('user_id', $request->requester_user_id)))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                            ->whereIn('book_id', $applicableBookIds);
                    })
                    ->whereColumn('indent_qty', '>', 'mi_qty')
                    ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds)),

                ConstantHelper::JO_SERVICE_ALIAS => match ($request->mi_type ?? ConstantHelper::TYPE_SUBCONTRACTING) {
                    ConstantHelper::TYPE_SUBCONTRACTING, 'Sub Contracting' => JoItem::with(['attributes', 'uom', 'jo'])
                        ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $selectedIds) {
                            $referedHeaderId = JobOrder::whereIn('id', $selectedIds)->first()?->header?->id;
                            $subQuery->where('job_order_type', ConstantHelper::TYPE_SUBCONTRACTING)->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                                ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
                                ->when($request->location_id, fn($q) => $q->where('store_id', $request->location_id))
                                ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                                ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                                ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                                ->whereIn('book_id', $applicableBookIds);
                        })
                        ->whereColumn('qty', '>', 'mi_qty')
                        ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds)),

                    ConstantHelper::TYPE_JOB_ORDER => JoProduct::with(['attributes', 'uom', 'jo'])
                        ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $selectedIds) {
                            $referedHeaderId = JobOrder::whereIn('id', $selectedIds)->first()?->header?->id;
                            $subQuery->where('job_order_type', ConstantHelper::TYPE_JOB_ORDER) ->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                                ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
                                ->when($request->location_id, fn($q) => $q->where('store_id', $request->location_id))
                                ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                                ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                                ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                                ->whereIn('book_id', $applicableBookIds);
                        })
                        ->whereRaw('mi_qty < order_qty - short_close_qty')
                        ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds)),

                    default => null,
                },
                ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS => ErpPslipItem::with(['header', 'attributes', 'uom'])
                    ->whereHas('header', function ($query) use ($request, $applicableBookIds, $selectedIds) {
                        $referedHeaderId = ErpProductionSlip::whereIn('id', $selectedIds)->first()?->header?->id;
                        $query->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                            ->whereIn('book_id', $applicableBookIds)
                            ->where('fg_sub_store_id', $request->store_id);
                    })
                    ->when($pslipType === 'A', function ($aStockQuery) {
                        $aStockQuery -> whereColumn('accepted_qty', '>', 'mi_accepted_qty');
                    })->when($pslipType === 'B', function ($bStockQuery) {
                        $bStockQuery -> whereColumn('subprime_qty', '>', 'mi_subprime_qty');
                    })
                    ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds)),

                default => null,
            };

            if (!$baseQuery) {
                return DataTables::of(collect())->make(true);
            }

            if ($request->doc_type === ConstantHelper::JO_SERVICE_ALIAS && isset($baseQuery)) {
                if ($request->item_id) {
                    $baseQuery = $baseQuery->where('item_id', $request->item_id);
                }
                $baseQuery = $baseQuery->get();

                foreach ($baseQuery as &$currentOrder) {
                    // $currentOrder->store_location_code = $currentOrder->header?->store_location?->store_name;
                    // $currentOrder->sub_store_code = $currentOrder->header?->sub_store?->name;
                    // $currentOrder->department_code = $currentOrder->header?->department?->name;
                    // $currentOrder->station_name = $currentOrder->header?->station?->name;
                    // $currentOrder->item_name = $currentOrder->item?->item_name;
                    // $currentOrder->avl_stock = $currentOrder->getAvlStock(
                    //     $request->store_id_from,
                    //     $request->sub_store_id_from ?? null,
                    //     $request->station_id_from ?? null
                    // );

                    // SO Number for JO
                    // if ($request->doc_type === ConstantHelper::JO_SERVICE_ALIAS) {
                    //     $so = ErpSaleOrder::withDefaultGroupCompanyOrg()->where('id', $currentOrder->so_id)->first();
                    //     if ($so) {
                    //         $currentOrder->so_no = $so ? $so->book_code . '-' . $so->document_number : '';
                    //     } else {
                    //         $currentOrder->so_no = "";
                    //     }
                    // }
                    // // Adjust qty for Job Order
                    // if ($request->mi_type === ConstantHelper::TYPE_JOB_ORDER) {
                    //     $currentOrder->qty = $currentOrder->order_qty;
                    // }

                    // Append station name for 'sf' RM type
                    // if ($request->doc_type === ConstantHelper::MO_SERVICE_ALIAS || ($request->doc_type === ConstantHelper::JO_SERVICE_ALIAS && $request->mi_type === "Sub Contracting")) {
                    //     if ($currentOrder->rm_type === 'sf') {
                    //         $currentOrder->item_name .= '-' . ($currentOrder->station?->name ?? '');
                    //     }
                    // }

                    // Add attribute_name/value mapping
                    // if (in_array($request->doc_type, [ConstantHelper::MO_SERVICE_ALIAS, ConstantHelper::PI_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS, 'pi'])) {
                    //     foreach ($currentOrder->attributes as $itemAttr) {
                    //         $itemAttr->attribute_name = $itemAttr->attr_name;
                    //         $itemAttr->attribute_value = $itemAttr->attr_value;
                    //     }
                    // }
                }

                // return DataTable?s::of($baseQuery)->make(true);
            }

            // For all other doc_types using $baseQuery
            if ($request->item_id) {
                $baseQuery->where('item_id', $request->item_id);
            }
            return DataTables::of($baseQuery)
                ->addColumn('book_code', fn($item) => $item?->header?->book_code ?? ($item->header->book?->book_code ?? ''))
                ->addColumn('document_number', fn($item) => $item?->header?->document_number)
                ->addColumn('document_date', fn($item) => $item->header->getFormattedDate("document_date"))
                ->addColumn('so_no', fn($item) => $item?->so?->book_code . '-' . $item?->so?->document_number)
                ->addColumn('item_name', function ($item) use ($request) {
                    $name = $item->item->item_name ?? '';
                    if (
                        $request->doc_type === ConstantHelper::MO_SERVICE_ALIAS ||
                        ($request->doc_type === ConstantHelper::JO_SERVICE_ALIAS && $request->mi_type === "Sub Contracting")
                    ) {
                        if ($item->rm_type === 'sf') {
                            $name .= '-' . ($item->station?->name ?? '');
                        }
                    }
                    return $name;
                })
                ->addColumn('store_location_code', function($item) use($request) {
                    if ($request -> doc_type === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
                        return $item->header?->store?->store_name ?? '';
                    }
                    return $item->header?->store_location?->store_name ?? '';
                })
                ->addColumn('sub_store_code', function ($item) use($request) {
                    if ($request -> doc_type === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
                        return $item->header?->sub_fg_store?->name ?? '';
                    }
                    return $item->header?->sub_store?->name ?? '';
                })
                ->addColumn('department_code', fn($item) => $item->header?->department?->name ?? '')
                ->addColumn('requester_name', fn($item) => $item?->header && method_exists($item->header, 'requester_name') ? $item->header->requester_name() : '')
                ->addColumn('station_name', function ($item) use($request) {
                    if ($request->doc_type === ConstantHelper::MO_SERVICE_ALIAS) {
                        if ($item?->rm_type === 'sf') {
                            return ($item?->item?->item_name . '-' . ($item->station?->name ?? ''));
                        }
                    }
                    return $item->header?->station?->name ?? '';
                })
                ->addColumn('avl_stock', function ($item) use ($request) {
                         return number_format($item->getAvlStock(
                            $request->store_id_from,
                            $request->sub_store_id_from ?? null,
                            $request->station_id_from ?? null
                        ),6);
                })
                ->editColumn('qty', function ($item) use ($request, $pslipType) {
                    if ($request->mi_type === ConstantHelper::TYPE_JOB_ORDER) {
                        return number_format($item->order_qty, 6);
                    }else if ($request->doc_type === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
                        if ($pslipType === 'B') {
                            return number_format($item->subprime_qty, 6);
                        } else {
                            return number_format($item->accepted_qty, 6);
                        }
                    } else {
                        return (number_format($item->qty,6));
                    }
                })
                ->editColumn('mi_balance_qty', function ($item) use ($request, $pslipType) {
                    if ($request -> doc_type === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
                        if ($pslipType === 'B') {
                            return (number_format($item->mi_balance_subprime_qty,6));
                        } else {
                            return (number_format($item->mi_balance_accepted_qty,6));
                        }
                    } else {
                        return (number_format($item->mi_balance_qty,6));
                    }
                })
                ->addColumn('attributes_array', function ($item) use ($request) {
                    if(in_array($request->doc_type, [ConstantHelper::JO_SERVICE_ALIAS])){
                        return $item->attributes->map(fn($attr) => [
                            'attribute_name' => $attr->headerAttribute?->name,
                            'attribute_value' => $attr->headerAttributeValue?->value,
                        ])->values();
                    }
                    return $item->attributes->map(fn($attr) => [
                        'attribute_name' => $attr->attr_name,
                        'attribute_value' => $attr->attr_value,
                    ])->values();
                })
                ->make(true);
            } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Internal error occurred.',
                'error' => $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine(),
            ], 500);
        }
    }

    //Function to get all items of pwo module
    public function processPulledItems(Request $request)
    {
        try {
            $headers = collect([]);
            if ($request -> doc_type === ConstantHelper::MO_SERVICE_ALIAS) {
                $headers = MfgOrder::whereHas('items', function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id);
                })
                ->with(['items' => function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id)
                        ->with(['item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }]);
                }])
                ->get();

            } else if ($request -> doc_type === ConstantHelper::PWO_SERVICE_ALIAS) {
                $headers = ErpProductionWorkOrder::whereHas('items', function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id);
                })
                ->with(['items' => function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id)
                        ->with(['item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }]);
                }])
                ->get();

            } else if ($request -> doc_type === ConstantHelper::JO_SERVICE_ALIAS) {
                if ($request->mi_type === "Sub Contracting") {
                    $headers = JobOrder::whereHas('joItems', function ($mappingQuery) use ($request) {
                            $mappingQuery->whereIn('id', $request->items_id);
                        })
                        ->with(['joItems' => function ($mappingQuery) use ($request) {
                            $mappingQuery->whereIn('id', $request->items_id)
                                ->with(['item' => function ($itemQuery) {
                                    $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                                }]);
                        }])
                        ->get();

                } else if ($request->mi_type === ConstantHelper::TYPE_JOB_ORDER) {
                    $headers = JobOrder::whereHas('joProducts', function ($mappingQuery) use ($request) {
                            $mappingQuery->whereIn('id', $request->items_id);
                        })
                        ->with(['joProducts' => function ($mappingQuery) use ($request) {
                            $mappingQuery->whereIn('id', $request->items_id)
                                ->with(['item' => function ($itemQuery) {
                                    $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                                }]);
                        }])
                        ->get();
                }
                else {
                    $headers = [];
                }
                
            } else if ($request -> doc_type === ConstantHelper::PI_SERVICE_ALIAS || $request -> doc_type === "pi") {
                $headers = PurchaseIndent::whereHas('items', function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id);
                })
                ->with(['items' => function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id)
                        ->with(['item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }]);
                }])
                ->get();
            } else if ($request -> doc_type === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
                $headers = ErpProductionSlip::whereHas('items', function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id);
                })
                ->with(['items' => function ($mappingQuery) use ($request) {
                    $mappingQuery->whereIn('id', $request->items_id)
                        ->with(['item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }]);
                }])
                ->get();
            }
            foreach ($headers as &$header) {
                if ($request -> doc_type === ConstantHelper::JO_SERVICE_ALIAS) {
                    if ($request -> mi_type === "Sub Contracting") {
                        $header -> items = $header -> joItems;
                    } else if ($request -> mi_type === ConstantHelper::TYPE_JOB_ORDER) {
                        $header -> items = $header -> joProducts;
                    }
                }
                foreach ($header -> items as &$item) {
                    $item -> item_attributes_array = $item -> item_attributes_array();
                    $item -> avl_stock = $item -> getAvlStock($request -> store_id, $request -> sub_store_id ?? null, $request -> station_id ?? null);
                    if ($request -> doc_type === ConstantHelper::MO_SERVICE_ALIAS) {
                        if ($item -> rm_type === 'sf') {
                            $item -> item -> item_name .= ('-' . $item -> station ?-> name);
                        }
                    $item -> station_name = $item ?-> station ?-> name;
                    }
                    if ($request -> doc_type === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS){
                        if ($request -> pslip_issue_type === 'B') {
                            $item -> mi_balance_qty = $item -> mi_subprime_balance_qty;
                        } else {
                            $item -> mi_balance_qty = $item -> mi_accepted_balance_qty;
                        }
                        $item -> pslip_issue_type = $request -> pslip_issue_type;
                    }
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
            $mx = ErpMaterialIssueHeader::with(
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
            $shippingAddress = $mx?->from_store?->address;
            $jobOrderNos = "";
            if ($mx -> issue_type === "Sub Contracting" || $mx -> issue_type === "Job Work") {
                $billingAddress = $mx?->vendor_shipping_address;
                foreach ($mx -> items as $mxItemIndex => $mxItem) {
                    $joItemHeader = $mxItem ?-> jo_item ?-> header;
                    $joProductHeader = $mxItem ?-> jo_product ?-> header;
                    if ($joItemHeader) {
                        $jobOrderNos .= ($mxItemIndex == 0 ? "" : ", ") . $joItemHeader -> book_code . "-" . $joItemHeader -> document_number;
                    }
                    if ($joProductHeader) {
                        $jobOrderNos .= ($mxItemIndex == 0 ? "" : ", ") . $joProductHeader -> book_code . "-" . $joProductHeader -> document_number;
                    }
                }
            } else {
                $billingAddress = $mx?->to_store?->address;
            }

            $approvedBy = Helper::getDocStatusUser(get_class($mx), $mx -> id, $mx -> document_status);

            // $type = ConstantHelper::SERVICE_LABEL[$mx->document_type];
            $totalItemValue = $mx->total_item_value ?? 0.00;
            $totalTaxes = $mx->total_tax_value ?? 0.00;
            $totalAmount = ($totalItemValue + $totalTaxes);
            $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
            // $storeAddress = ErpStore::with('address')->where('id',$mx->store_id)->get();
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
                'jobOrderNos' => $jobOrderNos,
                'approvedBy' => $approvedBy,
            ];
            $pdf = PDF::loadView(

                // return view(
                'pdf.material_document',
                $data_array
            );

            return $pdf->stream('Material_Issue.pdf');
    }
    public function report(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME;
        $redirectUrl = route('material.issue.report');
        $requesters = ErpMaterialIssueHeader::with(['requester'])->withDefaultGroupCompanyOrg()->bookViewAccess($pathUrl)->orderByDesc('id')->where('issue_type','Consumption')->where('requester_type',"User")->get()->unique('user_id')
        ->map(function ($item) {
            return [
                'id' => $item->requester()->first()->id ?? null,
                'name' => $item->requester()->first()->name ?? 'N/A',
            ];
        });
        if ($request->ajax()) {
            try {
                // Fetch Material Issues with Related Items and Attributes
                $docs = ErpMaterialIssueHeader::with('requester')->where('issue_type', 'Consumption')
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
                $issue_data = ErpMiItem::with(['header'])->whereIn('material_issue_id', $docs->pluck('id'))->orderByDesc('id')->get();
                $issue_item_ids = $issue_data -> pluck('id');
                // Fetch corresponding return data
                $return_data = ErpMrItem::whereIn('mi_item_id', $issue_item_ids)
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
                            $used = $return_data->where('mi_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'RETURN OLD';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();

                            $returned = $return_data->where('mi_item_id', $row->id)
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
    return view('materialIssue.report',['requesters'=>$requesters]);
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
    public function materialIssueReport(Request $request)
    {
        $pathUrl = route('material.issue.index');
        $orderType = [ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME];
        $materialIssue = ErpMaterialIssueHeader::with('items')-> withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> orderByDesc('id');
        //Customer Filter
        $materialIssue = $materialIssue -> when($request -> vendor_id, function ($custQuery) use($request) {
            $custQuery -> where('vendor_id', $request -> vendor_id);
        });
        //Book Filter
        $materialIssue = $materialIssue -> when($request -> book_id, function ($bookQuery) use($request) {
            $bookQuery -> where('book_id', $request -> book_id);
        });
        //Document Id Filter
        $materialIssue = $materialIssue -> when($request -> document_number, function ($docQuery) use($request) {
            $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
        });
        //From Location Filter
        $materialIssue = $materialIssue -> when($request -> type, function ($docQuery) use($request) {
            $docQuery -> where('issue_type', 'LIKE', "%".$request -> type . "%");
        });
        //From Location Filter
        $materialIssue = $materialIssue -> when($request -> location_id, function ($docQuery) use($request) {
            $docQuery -> where('from_store_id', $request -> location_id);
        });
        //To Location Filter
        $materialIssue = $materialIssue -> when($request -> to_location_id, function ($docQuery) use($request) {
            $docQuery -> where('to_store_id', $request -> to_location_id);
        });
        //Company Filter
        $materialIssue = $materialIssue -> when($request -> company_id, function ($docQuery) use($request) {
            $docQuery -> where('from_store_id', $request -> company_id);
        });
        //Organization Filter
        $materialIssue = $materialIssue -> when($request -> organization_id, function ($docQuery) use($request) {
            $docQuery -> where('organization_id', $request -> organization_id);
        });
        //Document Status Filter
        $materialIssue = $materialIssue -> when($request -> doc_status, function ($docStatusQuery) use($request) {
            $searchDocStatus = [];
            if ($request -> doc_status === ConstantHelper::DRAFT) {
                $searchDocStatus = [ConstantHelper::DRAFT];
            } else if ($request -> doc_status === ConstantHelper::SUBMITTED) {
                $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
            } else {
                $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
            }
            $docStatusQuery -> whereIn('document_status', $searchDocStatus);
        });
        //Date Filters
        $dateRange = $request -> date_range ??  Carbon::now()->startOfMonth()->format('Y-m-d') . " to " . Carbon::now()->endOfMonth()->format('Y-m-d');
        $materialIssue = $materialIssue -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
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
        $materialIssue = $materialIssue -> when($request -> item_id, function ($itemQuery) use($request) {
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
        $materialIssue = $materialIssue -> get();
        $processedSalesOrder = collect([]);
        foreach ($materialIssue as $materialIssue) {
            foreach ($materialIssue -> items as $miItem) {
                $reportRow = new stdClass();
                //Header Details
                $header = $miItem -> header;
                $reportRow -> id = $header -> id;
                $reportRow -> book_name = $header -> book_code;
                $reportRow -> document_number = $header -> document_number;
                $reportRow -> document_date = $header -> document_date;
                $reportRow -> issue_type = $header -> issue_type;
                $reportRow -> store_name = $header -> erpStore ?-> store_name;
                $reportRow -> vendor_name = $header -> vendor ?-> company_name ?? " ";
                $reportRow -> customer_currency = $header -> org_currency_code ?? $header ?-> vendor ?-> currency ?-> short_name ?: $header ?-> vendor ?-> currency ?-> name ;
                $reportRow -> payment_terms_name = $header -> payment_term_code;
                $reportRow -> from_store_name = $header -> from_store ?-> store_name;
                $reportRow -> to_store_name = $header -> to_store ?-> store_name;
                $reportRow -> from_sub_store_name = $miItem ?-> fromErpSubStore ?-> name;
                $reportRow -> to_sub_store_name = $miItem ?-> toErpSubStore ?-> name;
                $reportRow -> requester = $header -> issue_type == "Consumption" ? $header -> requester_name() : " ";
                //Item Details
                $reportRow -> item_name = $miItem -> item_name;
                $reportRow -> item_code = $miItem -> item_code;
                $reportRow -> hsn_code = $miItem -> hsn ?-> code;
                $reportRow -> uom_name = $miItem -> uom ?-> name;
                //Amount Details
                $reportRow -> qty = number_format($miItem -> issue_qty, 2);
                $reportRow -> mr_qty = number_format($miItem -> mr_qty, 2);
                $reportRow -> rate = number_format($miItem -> rate, 2);
                $reportRow -> total_discount_amount = number_format($miItem -> header_discount_amount + $miItem -> item_discount_amount, 2);
                $reportRow -> tax_amount = number_format($miItem -> tax_amount, 2);
                $reportRow -> taxable_amount = number_format($miItem -> total_item_amount - $miItem -> tax_amount, 2);
                $reportRow -> total_item_amount = number_format($miItem -> total_item_amount, 2);
                //Delivery Schedule UI
                // $deliveryHtml = '';
                // if (count($miItem -> item_deliveries) > 0) {
                //     foreach ($miItem -> item_deliveries as $itemDelivery) {
                //         $deliveryDate = Carbon::parse($itemDelivery -> delivery_date) -> format('d-m-Y');
                //         $deliveryQty = number_format($itemDelivery -> qty, 2);
                //         $deliveryHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$deliveryDate</strong> : $deliveryQty</span>";
                //     }
                // } else {
                //     $parsedDeliveryDate = Carbon::parse($miItem -> delivery_date) -> format('d-m-Y');
                //     $deliveryHtml .= "$parsedDeliveryDate";
                // }
                // $reportRow -> delivery_schedule = $deliveryHtml;
                //Attributes UI
                $attributesUi = '';
                if (count($miItem -> item_attributes) > 0) {
                    foreach ($miItem -> item_attributes as $soAttribute) {
                        $attrName = $soAttribute -> attribute_name;
                        $attrValue = $soAttribute -> attribute_value;
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
            $editRoute = route('sale.return.edit', ['id' => $row->id]);
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
