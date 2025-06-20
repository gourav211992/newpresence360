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
use App\Http\Requests\ErpPlRequest;
use App\Models\Address;
use App\Helpers\DynamicFieldHelper;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\ErpPlDynamicField;
use App\Models\AuthUser;
use App\Models\Country;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpInvoiceItem;
use App\Models\ErpPlHeader;
use App\Models\ErpPlHeaderHistory;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpPlItem;
use App\Models\ErpPlItemAttribute;
use App\Models\ErpPlItemLocation;
use App\Models\ErpPlItemLotDetail;
use App\Models\ErpMrItem;
use App\Models\ErpProductionSlip;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpPwoItem;
use App\Models\ErpRack;
use App\Models\ErpSaleOrder;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemDelivery;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpVendor;
use App\Models\Hsn;
use App\Models\Item;
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

class ErpPlController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PL_SERVICE_ALIAS;
        $redirectUrl = route('PL.index');
        $createRoute = route('PL.create');
        $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $autoCompleteFilters = self::getBasicFilters();
        //Date Filters
        $dateRange = $request -> date_range ?? null;
        $typeName = "Pick List";
        if ($request -> ajax()) {
            try {
                $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
                $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
                $autoCompleteFilters = self::getBasicFilters();
                //Date Filters
                $dateRange = $request -> date_range ?? null;
                
                $docs = ErpPlHeader::withDefaultGroupCompanyOrg() ->  bookViewAccess($pathUrl) ->  
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
                    $editRoute = route('PL.edit', ['id' => $row -> id]); 
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
        return view('PL.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'create_button' => $create_button,'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::PL_SERVICE_ALIAS],
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
        $redirectUrl = route('PL.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::PL_SERVICE_NAME;
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
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
            'vendors' => $vendors,
            'stations' => $stations,
            'departments' => $departments['departments'],
            'selectedDepartmentId' => $departments['selectedDepartmentId'],
            'requesters' => $users,
            'selectedUserId' => null,
            'current_financial_year' => $selectedfyYear,
            'redirect_url' => $redirectUrl
        ];
        return view('PL.layout', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('PL.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpPlHeaderHistory::with(['book']) -> with('items', function ($query) {
                    $query -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> where('source_id', $id)->first();
                $ogDoc = ErpPlHeader::find($id);
            } else {
                $doc = ErpPlHeader::with(['book']) -> with('items', function ($query) {
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
            $books = Helper::getBookSeriesNew(ConstantHelper::PL_SERVICE_ALIAS, ) -> get();
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
                'typeName' => $typeName,
                'stores' => $stores,
                'vendors' => $vendors,
                'stations' => $stations,
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
            return view('PL.layout', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }
    public function store(ErpPlRequest $request)
    {
        try {
            //Reindex
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            if($request->selected_deliveries && count($request->selected_deliveries) == 0)
            {
                return response()->json([
                    'message' => "Select Atleast One Delivery",
                    'error' => "",
                ], 422);
            }
            //Auth credentials
            $organization = Organization::find($user -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $itemAttributeIds = [];
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($organization -> currency -> id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422); 
            }

            if (!$request -> pl_header_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpPlHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $PL = null;
            $store = ErpStore::find($request -> store_id);
            $subStore = ErpSubStore::find($request -> sub_store_id??null);
            if ($request -> pl_header_id) { //Update
                $PL = ErpPlHeader::find($request -> pl_header_id);
                $PL -> document_date = $request -> document_date;
                //Store and department keys
                $PL -> store_id = $request -> store_id ?? null;
                $PL -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup
                if(($PL -> document_status == ConstantHelper::APPROVED || $PL -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpPlHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpPlItem', 'relation_column' => 'pl_header_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpPlItemAttribute', 'relation_column' => 'pl_item_id'],
                    ];
                    $a = Helper::documentAmendment($revisionData, $PL->id);

                }
                $keys = ['deletedSiItemIds', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }

                if (count($deletedData['deletedSiItemIds'])) {
                    $PLItems = ErpPlItem::whereIn('id',$deletedData['deletedSiItemIds'])->get();
                    # all ted remove item level
                    foreach($PLItems as $PLItem) {
                        $PLItem->attributes()->delete();
                        $PLItem->delete();
                    }
                }
            } else { //Create
                $PL = ErpPlHeader::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'store_id' => $request->store_id ?? null,
                    'store_code' => $store?->store_name ?? null,
                    'sub_store_id' => $request->sub_store_id ?? null,
                    'sub_store_code' => $substore?->name ?? null,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_number' => $document_number,
                    'document_date' => $request->document_date,
                    'document_status' => ConstantHelper::DRAFT,
                    'revision_number' => 0,
                    'revision_date' => null,
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

                // Shipping Address
                // $vendorShippingAddress = ErpAddress::find($request -> vendor_address_id);
                // if (isset($vendorShippingAddress)) {
                //     $shippingAddress = $PL -> vendor_shipping_address() -> create([
                //         'address' => $vendorShippingAddress -> address,
                //         'country_id' => $vendorShippingAddress -> country_id,
                //         'state_id' => $vendorShippingAddress -> state_id,
                //         'city_id' => $vendorShippingAddress -> city_id,
                //         'type' => 'shipping',
                //         'pincode' => $vendorShippingAddress -> pincode,
                //         'phone' => $vendorShippingAddress -> phone,
                //         'fax_number' => $vendorShippingAddress -> fax_number
                //     ]);
                // }
            }
                $PL -> save();
                //Seperate array to store each item calculation
                $itemsData = array();
                if ($request->selected_deliveries && count($request->selected_deliveries) > 0) {
                    $itemsToDelete = ErpPlItem::where('pl_header_id', $PL->id)->get();
                    foreach ($itemsToDelete as $item) {
                        $soItem = $item->soItem; // Access the related ErpSoItem
                        if ($soItem) {
                            $soItem->picked_qty -= $item->picked_qty; // Adjust the picked_qty
                            $soItem->save(); // Save the updated ErpSoItem
                        }
                    }
                    foreach ($request->selected_deliveries as $Dkey => $deliveryId) {
                        if($request->picked_qty[$Dkey]<=0)
                        {
                            return response()->json([
                                'message' => "Picked quantity must be greater than zero.",
                                'error' => "",
                            ], 422);
                        }
                        $delivery = ErpSoItemDelivery::find($deliveryId);
                        if (isset($delivery)) {
                            $item = ErpSoItem::find($delivery->so_item_id);
                            $order = ErpSaleOrder::find($delivery->sale_order_id);
                            $uom = Unit::find($item->uom_id);
                            $hsn = Hsn::find($item->hsn_id);
                            $base_uom_qty = ItemHelper::convertToBaseUom($item->item_id,$item->uom_id,$request->picked_qty[$Dkey]);
                            $PLItemData = [
                                'pl_header_id' => $PL->id,
                                'order_id' => $order->id,
                                'order_item_id' => $item->id,
                                'order_item_delivery_id' => $delivery->id,
                                'item_id' => $item->item_id,
                                'item_code' => $item->item_code,
                                'item_name' => $item->item_name,
                                'hsn_id' => isset($item->hsn_id) ? $item->hsn_id : null,
                                'hsn_code' => isset($hsn) ? $hsn->code : null,
                                'uom_id' => isset($item->uom_id) ? $item->uom_id : null,
                                'uom_code' => isset($uom) ? $uom->name : null,
                                'inventory_uom_id' => isset($item->inventory_uom_id) ? $item->inventory_uom_id : null,
                                'inventory_uom_code' => isset($item) ? $item->inventory_uom_code : null,
                                'inventory_uom_qty' => isset($base_uom_qty) ? $base_uom_qty : null,
                                'order_qty' => $item->order_qty,
                                'picked_qty' => $request->picked_qty[$Dkey],
                                'delivery_date' => $delivery->delivery_date,
                                'rate' => $item->rate,
                                'total_amount' => $request->picked_qty[$Dkey]*$item->rate,
                                'remarks' => isset($request->item_remarks[$Dkey]) ? $request->item_remarks[$Dkey] : null,
                            ];

                            $PLItem = ErpPlItem::updateOrCreate(
                                ['order_item_delivery_id' => $request->selected_deliveries[$Dkey]],
                                $PLItemData
                            );
                            if($item)
                            {
                                $item->picked_qty +=$request->picked_qty[$Dkey];
                                $item->save();
                            }
                            if (method_exists($item, 'item_attributes_array') && is_callable([$item, 'item_attributes_array'])) {
                                $attributesArray = json_decode(json_encode($item->item_attributes_array()), true);
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
                                        if(isset($attributeVal) && $attributeValId){
    
                                            $itemAttribute = ErpPlItemAttribute::updateOrCreate(
                                                [
                                                    'pl_id' => $PL -> id,
                                                    'pl_item_id' => $PLItem -> id,
                                                    'item_attribute_id' => $attribute['id'],
                                                ],
                                                [
                                                    'item_code' => $PLItem -> item_code,
                                                    'attribute_name' => $attribute['group_name'],
                                                    'attr_name' => $attribute['attribute_group_id'],
                                                    'attribute_value' => $attributeVal,
                                                    'attr_value' => $attributeValId,
                                                    ]
                                                );
                                                array_push($itemAttributeIds, $itemAttribute -> id);
                                        }
                                    }
                                } else {
                                    return response() -> json([
                                        'message' => 'Item No. ' . ($Dkey + 1) . ' has invalid attributes',
                                        'error' => ''
                                    ], 422);
                                }
                            }
                        }
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Deliveries',
                        'error' => "",
                    ], 422);
                } //Approval check
                ErpPlItemAttribute::where([
                    'pl_id' => $PL -> id,
                    'pl_item_id' => $PLItem -> id,
                ]) -> whereNotIn('id', $itemAttributeIds) -> delete();

                ErpPlItem::whereNotIn('order_item_delivery_id', $request->selected_deliveries)
                    ->where('pl_header_id', $PL->id)
                    ->delete();
                if ($request->pl_header_id) { // Update condition
                    $bookId = $PL->book_id;
                    $docId = $PL->id;
                    $amendRemarks = $request->remarks ?? null;
                    $remarks = $PL->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachments');
                    $currentLevel = $PL->approval_level;
                    $modelName = get_class($PL);
                    $actionType = $request->action_type ?? "";

                    if (($PL->document_status == ConstantHelper::APPROVED || $PL->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                        $revisionNumber = $PL->revision_number + 1;
                        $actionType = 'amendment';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                        $PL->revision_number = $revisionNumber;
                        $PL->approval_level = 1;
                        $PL->revision_date = now();
                        $amendAfterStatus = $PL->document_status;
                        $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);

                        if (isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                            $totalValue = $PL->grand_total_amount ?? 0;
                            $amendAfterStatus = Helper::checkApprovalRequired($request->book_id, $totalValue);
                        } else {
                            $actionType = 'approve';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }

                        if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }

                        $PL->document_status = $amendAfterStatus;
                        $PL->save();
                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $PL->revision_number ?? 0;
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                            $totalValue = $PL->grand_total_amount ?? 0;
                            $document_status = Helper::checkApprovalRequired($request->book_id, $totalValue);
                            $PL->document_status = $document_status;
                        } else {
                            $PL->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { // Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $PL->book_id;
                        $docId = $PL->id;
                        $remarks = $PL->remarks;
                        $attachments = $request->file('attachments');
                        $currentLevel = $PL->approval_level;
                        $revisionNumber = $PL->revision_number ?? 0;
                        $actionType = 'submit';
                        $modelName = get_class($PL);
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    if ($request->document_status == 'submitted') {
                        $totalValue = $PL->total_amount ?? 0;
                        $document_status = Helper::checkApprovalRequired($request->book_id, $totalValue);
                        $PL->document_status = $document_status;
                    } else {
                        $PL->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                    $PL->save();
                }

                $PL->save();

                // Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $PL->uploadDocuments($singleFile, 'PL_header', false);
                    }
                }
                // Maintain Stock Ledger
                // if($PL->document_status == ConstantHelper::APPROVED || $PL->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED)
                // {
                //     $status = self::maintainStockLedger($PL);
                //     if (!$status) {     
                //         DB::rollBack();
                //         return response() -> json([
                //                 'message' => 'Stock not available'
                //             ], 422);
                //     }
                // }
                //Dynamic Fields
                $status = DynamicFieldHelper::saveDynamicFields(ErpPlDynamicField::class, $PL -> id, $request -> dynamic_field ?? []);
                if ($status && !$status['status'] ) {
                    DB::rollBack();
                    return response() -> json([
                        'message' => $status['message'],
                        'error' => ''
                    ], 422);
                }
                DB::commit();
                $module = "Pick List";
                return response() -> json([
                    'message' => $module .  " created successfully",
                    'redirect_url' => route('PL.index')
                ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex -> getLine() . ' in ' . $ex -> getFile(),
            ], 500);
        }
    }

    private static function maintainStockLedger(ErpPlHeader $PL)
    {
        $items = $PL->items;
        $issueDetailIds = $items -> where('adjusted_qty',"<",0) -> pluck('id') -> toArray();
        $receiptDetailIds = $items -> where('adjusted_qty',">",0) -> pluck('id') -> toArray();
        $issueRecords = InventoryHelper::settlementOfInventoryAndStock($PL->id, $issueDetailIds, ConstantHelper::PL_SERVICE_ALIAS, $PL->document_status, 'issue');
        $receiptRecords = InventoryHelper::settlementOfInventoryAndStock($PL->id, $receiptDetailIds, ConstantHelper::PL_SERVICE_ALIAS, $PL->document_status, 'receipt');
        
        if((!empty($issueRecords['data']) && (count($issueRecords['data']) > 0 && count($issueDetailIds)>0))||(($receiptRecords['message'] == 'success' || $receiptRecords['message']['status'] == 'success') && count($receiptDetailIds)>0) ){
            // $stockLedgers = StockLedger::where('book_type',ConstantHelper::PL_SERVICE_ALIAS)
            //                     ->where('document_header_id',$PL->id)
            //                     ->where('organization_id',$PL->organization_id)
            //                     ->where('transaction_type','issue')
            //                     ->selectRaw('document_detail_id,sum(org_currency_cost) as cost')
            //                     ->groupBy('document_detail_id')
            //                     ->get();

            // foreach($stockLedgers as $stockLedger) {
            //     $PLItem = ErpPlItem::find($stockLedger->document_detail_id);
            //     // dd(floatval($stockLedger->cost) , floatval($PLItem->issue_qty));
            //     $PLItem->confirmed_qty = $PLItem->verified_qty;
            //     $PLItem->save();
            // }
            return true;
        } else {
            return false;
        }
    }

    public function revokePL(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpPlHeader::find($request -> id);
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
            $storeids = $request->store_id ?? null;
            $orderItems = ErpSoItemDelivery::with(['item' => function ($query) {
                $query->with(['header' => function ($subQuery) {
                    $subQuery->with(['store', 'customer']);
                }, 'uom']);
            }])
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
                $query->whereRaw('order_qty > (short_close_qty + dnote_qty + picked_qty)');
            })
            ->orderBy('delivery_date')->get();

            foreach ($orderItems as $orderItem) {
                $orderItem->attributes = collect($orderItem->item->item_attributes_array())->map(function ($attrArr) {
                    $short = $attrArr['short_name'] ?? null;
                    $groupName = $attrArr['group_name'] ?? '';
                    $selectedValue = collect($attrArr['values_data'])->firstWhere('selected', true)['value'] ?? '';
                    $displayName = $short ?? $groupName;
                    return "<span class='badge rounded-pill badge-light-primary'><strong>{$displayName}: {$selectedValue}</strong></span>";
                })->implode(' ');
                $orderItem->avl_stock = $orderItem->item->getStockBalanceQty();
                $orderItem->store_location_code = $orderItem->item->header?->store_location?->store_name;
                $orderItem->department_code = $orderItem->item->header?->department?->name;
                $orderItem->station_name = $orderItem->item->header?->station?->name;
            }
            return response()->json([
                'data' => $orderItems
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
            $mx = ErpPlHeader::with(
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
        //     $issue_data = ErpPlHeader::where('issue_type', 'Consumption')
        //         ->withWhereHas('items', function ($query) {
        //             $query->whereHas('attributes', function ($subQuery) {
        //                 $subQuery->where('attribute_name', 'TYPE'); // Ensure the attribute name is 'TYPE'
        //             }, '=', 1); // Ensure only one attribute exists
        //         })
        //         ->get();
        //     $issue_items_ids = ErpPlItem::whereIn('pl_header_id',[$issue_data->pluck('id')])->pluck('id');
        //     $return_data = ErpMrItem::whereIn('pl_item_id',[$issue_items_ids])->get();
        //     return view('PL.report',[
        //         'issues' =>$issue_data,
        //         'return' =>$return_data,
        //     ]);
        // }
    public function report(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PL_SERVICE_ALIAS;
        $redirectUrl = route('PL.report');
        $requesters = ErpPlHeader::with(['requester'])->withDefaultGroupCompanyOrg()->bookViewAccess($pathUrl)->orderByDesc('id')->where('issue_type','Consumption')->where('requester_type',"User")->get()->unique('user_id')
        ->map(function ($item) {
            return [
                'id' => $item->requester()->first()->id ?? null,
                'name' => $item->requester()->first()->name ?? 'N/A',
            ];
        });
        if ($request->ajax()) {
            try {
                // Fetch Material Issues with Related Items and Attributes
                $docs = ErpPlHeader::with('requester')->where('issue_type', 'Consumption')
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
                $issue_data = ErpPlItem::with(['header'])->whereIn('pl_header_id', $docs->pluck('id'))->orderByDesc('id')->get();
                $issue_item_ids = $issue_data -> pluck('id');
                // Fetch corresponding return data
                $return_data = ErpMrItem::whereIn('pl_item_id', $issue_item_ids)
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
                            $used = $return_data->where('pl_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'RETURN OLD';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();

                            $returned = $return_data->where('pl_item_id', $row->id)
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
    return view('PL.report',['requesters'=>$requesters]);
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
        $pathUrl = route('PL.index');
        $orderType = [ConstantHelper::PL_SERVICE_ALIAS];
        $PL = ErpPlItem::whereHas('header', function ($headerQuery) use($orderType, $pathUrl, $request) {
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
        return DataTables::of($PL)
            ->addIndexColumn()
            ->editColumn('document_number', fn($pl) => $pl->header->document_number)
            ->editColumn('document_date', fn($pl) => $pl->header->document_date)
            ->editColumn('book_code', fn($pl) => $pl->header->book_code)
            ->addColumn('so_no', fn($pl) => $pl->so->book_code."-".$pl->so->document_number)
            ->addColumn('so_date', fn($pl) => $pl->so->document_date)
            ->addColumn('store_name', fn($pl) => $pl->header->store?->store_name)
            ->addColumn('sub_store_name', fn($pl) => $pl->header->subStore?->name)
            ->editColumn('item_name', fn($pl) => $pl->item_name)
            ->editColumn('item_code', fn($pl) => $pl->item_code)
            ->editColumn('hsn_code', fn($pl) => $pl->item->hsn?->code)
            ->editColumn('uom_name', fn($pl) => $pl->item->uom?->name)
            ->editColumn('picked_qty', fn($pl) => number_format($pl->picked_qty, 2))
            ->editColumn('order_qty', fn($pl) => number_format($pl->order_qty, 2))
            ->editColumn('rate', fn($pl) => number_format($pl->rate, 2))
            ->editColumn('total_amount', fn($pl) => number_format($pl->total_amount, 2))
            ->editColumn('item_attributes', function ($pl) {
                if (count($pl->item_attributes) > 0) {
                    return collect($pl->item_attributes)->map(fn($attr) => "<span class='badge rounded-pill badge-light-primary'>{$attr->attribute_name} : {$attr->attribute_value}</span>")->implode(' ');
                }
                return 'N/A';
            })
            ->editColumn('status', function ($pl) use ($orderType) {
                $status = $pl->header->document_status ?? ConstantHelper::DRAFT;
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$status];
                $editRoute = route('PL.edit', ['id' => $pl->id]);

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
            $saleInvoice = ErpPlHeader::find($request->document_id);
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
}