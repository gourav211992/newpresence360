<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Helpers\NumberHelper;
use App\Helpers\TransactionReportHelper;
use App\Http\Requests\ErpSaleOrderRequest;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Bom;
use App\Models\ErpLorryReceipt;
use App\Models\BomDetail;
use App\Models\CashCustomerDetail;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpInvoiceItem;
use App\Models\ErpSaleOrderHistory;
use App\Models\ErpSoDynamicField;
use App\Models\ErpSoItemBom;
use App\Models\ErpSoJobWorkItem;
use App\Models\ErpSoJobWorkItemAttribute;
use App\Models\ErpSoMedia;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\ErpItemAttribute;
use App\Models\ErpSaleOrder;
use App\Models\ErpSaleOrderTed;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemAttribute;
use App\Models\ErpSoItemDelivery;
use App\Models\ItemSpecification;
use App\Models\JobOrder\JoBomMapping;
use App\Models\JobOrder\JobOrder;
use App\Models\JobOrder\JoProduct;
use App\Models\Organization;
use App\Models\OrganizationGroup;
use App\Models\PoItem;
use App\Models\ProductionRouteDetail;
use App\Models\PurchaseOrder;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Models\State;
use Carbon\Carbon;
use DB;
use PDF;
use Exception;
use stdClass;

class ErpSaleOrderController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        if ($pathUrl === 'sales-quotation') {
            $orderType = ConstantHelper::SQ_SERVICE_ALIAS;
            $redirectUrl = route('sale.quotation.index');
            $createRoute = route('sale.quotation.create');
        }
        if ($pathUrl === 'sales-order') {
            $orderType = ConstantHelper::SO_SERVICE_ALIAS;
            $redirectUrl = route('sale.order.index');
            $createRoute = route('sale.order.create');

        }
        $autoCompleteFilters = self::getBasicFilters();
        request() -> merge(['type' => $orderType]);
        $authUser = Helper::getAuthenticatedUser();
        $organization = Organization::find($authUser ?-> organization_id);
        $salesOrderBulkUploadVersion = "v2";
        //Shufab special case for custom import
        $userGroup = OrganizationGroup::find($organization ?-> group_id);
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        if ($userGroup) {
            $groupName = strtolower($userGroup -> name);
            if (str_contains($groupName, 'shufab')) {
                $salesOrderBulkUploadVersion = "v1";
            }
        }
        if ($request -> ajax()) {
            $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
            //Date Filters
            $dateRange = $request -> date_range ??  null;
            
            $salesOrder = ErpSaleOrder::where('document_type', $orderType) -> whereIn('store_id',$accessible_locations) 
            -> bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic() 
            -> whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']])
            -> when($request -> customer_id, function ($custQuery) use($request) {
                $custQuery -> where('customer_id', $request -> customer_id);
            }) -> when($request -> book_id, function ($bookQuery) use($request) {
                $bookQuery -> where('book_id', $request -> book_id);
            }) -> when($request -> document_number, function ($docQuery) use($request) {
                $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
            }) -> when($request -> location_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> location_id);
            }) -> when($request -> company_id, function ($docQuery) use($request) {
                $docQuery -> where('company_id', $request -> company_id);
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
            return DataTables::of($salesOrder) ->addIndexColumn()
            ->editColumn('document_status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = $row -> display_status;   
                $editRoute = null;
                if ($orderType == ConstantHelper::SO_SERVICE_ALIAS) {
                    $editRoute = route('sale.order.edit', ['id' => $row->id]);
                }
                if ($orderType == ConstantHelper::SQ_SERVICE_ALIAS) {
                    $editRoute = route('sale.quotation.edit', ['id' => $row->id]);
                }     
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
                return $row->currency ? ($row->currency?->short_name ?? $row->currency?->name) : 'N/A';
            })
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
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
                return number_format(abs($row->total_tax_value),2);
            })
            ->editColumn('total_expense_value', function ($row) {
                return number_format($row->total_expense_value,2);
            })
            ->editColumn('grand_total_amount', function ($row) {
                return number_format($row->total_amount,2);
            })
            ->rawColumns(['document_status'])
            ->make(true);
        }
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        $create_button = (isset($servicesBooks['services'])  && count($servicesBooks['services']) > 0 && isset($selectedfyYear['authorized']) && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        return view('salesOrder.index', [
            'redirect_url' => $redirectUrl,
            'create_route' => $createRoute,
            'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::SO_SERVICE_ALIAS],
            'autoCompleteFilters' => $autoCompleteFilters,
            'bulk_upload_version' => $salesOrderBulkUploadVersion,
            'create_button' => $create_button,
        ]);
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
        $pathUrl = request()->segments()[0];
        $redirectUrl = route('sale.order.index');
        $orderType = ConstantHelper::SO_SERVICE_ALIAS;
        if ($pathUrl === 'sales-quotation') {
            $orderType = ConstantHelper::SQ_SERVICE_ALIAS;
            $redirectUrl = route('sale.quotation.index');
        }
        request() -> merge(['type' => $orderType]);
        $orderType = $request -> input('type', ConstantHelper::SO_SERVICE_ALIAS);
        //Get the menu 
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $bookTypeAlias = ConstantHelper::SO_SERVICE_ALIAS;
        if ($orderType == ConstantHelper::SO_SERVICE_ALIAS) {
            $bookTypeAlias = ConstantHelper::SO_SERVICE_ALIAS;
        } else {
            $bookTypeAlias = ConstantHelper::SQ_SERVICE_ALIAS;
        }
        $books = [];
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $organization = Organization::where('id', $user->organization_id)->first();
        $departments = Department::where('organization_id', $organization->id)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->get();
        $itemImportFile = asset('templates/SalesOrderItemImport.xlsx');
        $orderTypes = SaleModuleHelper::ORDER_TYPES;
        $data = [
            'series' => $books,
            'countries' => $countries,
            'type' => $orderType,
            'user' => $user,
            'stores' => $stores,
            'departments' => $departments,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'redirectUrl' => $redirectUrl,
            'itemImportFile' => $itemImportFile,
            'orderTypes' => $orderTypes
        ];
        return view('salesOrder.create_edit', $data);
    }
    public function edit(Request $request, string $id)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::SO_SERVICE_ALIAS;
        $redirectUrl = route('sale.order.index');
        if ($pathUrl === 'sales-quotation') {
            $orderType = ConstantHelper::SQ_SERVICE_ALIAS;
            $redirectUrl = route('sale.quotation.index');
        }
        request() -> merge(['type' => $orderType]);
        $servicesBooks = [];
        $user = Helper::getAuthenticatedUser();
        $orderType = $request->input('type', ConstantHelper::SO_SERVICE_ALIAS);
        $bookTypeAlias = ConstantHelper::SO_SERVICE_ALIAS;
        if ($orderType === ConstantHelper::SQ_SERVICE_ALIAS) {
            $bookTypeAlias = ConstantHelper::SQ_SERVICE_ALIAS;
        }
        if (isset($request->revisionNumber)) {
            $order = ErpSaleOrderHistory::with(['media_files', 'discount_ted', 'expense_ted', 
                'billing_address_details', 'shipping_address_details', 'location_address_details'])
                ->with('items', function ($query) {
                    $query->with('custom_bom_details','discount_ted', 'tax_ted', 'item_deliveries')->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                })->bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()
                -> where('source_id', $id)->where('revision_number', $request->revisionNumber)->firstOrFail();
            $ogOrder = ErpSaleOrder::where('id', $id) -> bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() 
                -> withDraftListingLogic() -> firstOrFail();
        } else {
            $order = ErpSaleOrder::with(['media_files', 'discount_ted', 'expense_ted', 
                'billing_address_details', 'shipping_address_details', 'location_address_details'])
                ->with('items', function ($query) {
                    $query->with('custom_bom_details','discount_ted', 'tax_ted', 'item_deliveries')->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                }) -> where('id', $id) ->bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()->firstOrFail();
            $ogOrder = $order;
        }
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $organization = Organization::where('id', $user->organization_id)->first();
        $departments = Department::where('organization_id', $organization->id)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->get();
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $order -> book ?-> master_service ?-> alias);
        foreach ($order -> items as &$soItem) {
            $referencedAmount = ErpSoItem::where('sq_item_id', $soItem -> id) -> sum('order_qty');
            if (isset($referencedAmount) && $referencedAmount > 0) {
                $soItem -> min_attribute = $referencedAmount;
                $soItem -> is_editable = false;
                $soItem -> restrict_delete = true;
            }
            else if ($soItem -> sq_item_id !== null) {
                $pulled = ErpSoItem::find($soItem -> sq_item_id);
                if (isset($pulled)) {
                    $availableTotalQty = $soItem -> order_qty + $pulled -> balance_qty;
                    $soItem -> max_attribute = $availableTotalQty;
                    $soItem -> is_editable = false;
                } else {
                    $soItem -> max_attribute = 999999;
                    $soItem -> is_editable = true;
                }
            } else {
                $soItem->max_attribute = 999999;
                $soItem->is_editable = true;
            }
        }
        $revision_number = $order->revision_number;
        $totalValue = ($order -> total_item_value - $order -> total_discount_value) + $order -> total_tax_value + $order -> total_expense_value;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($order->book_id,$order->document_status , $order->id, $totalValue, $order->approval_level, $order -> created_by ?? 0, $userType['type'], $revision_number);
        $books = Helper::getBookSeriesNew($bookTypeAlias) -> get();
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $revNo = $order->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $order->revision_number;
        }
        $docValue = $order->total_amount ?? 0;
        $approvalHistory = Helper::getApprovalHistory($order->book_id, $ogOrder->id, $revNo, $docValue, $order -> created_by);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$order->document_status] ?? '';
        $shortClose = 0;
        if(intval($ogOrder->revision_number) > 0) {
            $shortClose = 1;
        } else {
            if($ogOrder->document_status == ConstantHelper::APPROVED || $ogOrder->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) {
                $shortClose = 1;
            }
        }
        $pendingOrder = ErpSoItem::where('sale_order_id', $ogOrder->id)
            ->whereRaw('order_qty > (dnote_qty + short_close_qty)')
            ->count();
        if($pendingOrder) {
            $shortClose = 1;
        } else {
            $shortClose = 0;
        }
        $dynamicFieldsUI = $order -> dynamicfieldsUi();
        $itemImportFile = asset('templates/SalesOrderItemImport.xlsx');
        $orderTypes = SaleModuleHelper::ORDER_TYPES;
        $data = [
            'user' => $user,
            'series' => $books,
            'order' => $order,
            'countries' => $countries,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'type' => $orderType,
            'stores' => $stores,
            'departments' => $departments,
            'revision_number' => $revision_number,
            'docStatusClass' => $docStatusClass,
            'shortClose' => $shortClose,
            'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($order -> media_files)) : 10,
            'services' => $servicesBooks['services'],
            'redirectUrl' => $redirectUrl,
            'itemImportFile' => $itemImportFile,
            'dynamicFieldsUi' => $dynamicFieldsUI,
            'orderTypes' => $orderTypes
        ];
        return view('salesOrder.create_edit', $data);
    }

    public function store(ErpSaleOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            //Auth credentials
            $organization = Organization::find($user -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $store = ErpStore::find($request -> store_id);
            //Tax Country and State
            $firstAddress = $organization->addresses->first();
            $companyCountryId = null;
            $companyStateId = null;
            $location = ErpStore::find($request -> store_id);
            if ($location && isset($location -> address)) {
                $companyCountryId = $location->address?->country_id??null;
                $companyStateId = $location->address?->state_id??null;
            } else {
                return response()->json([
                    'message' => 'Please create an organization first'
                ], 422);
            }
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request -> currency_id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422); 
            }

            $itemTaxIds = [];
            $itemAttributeIds = [];
            if (!$request -> sale_order_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpSaleOrder::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $saleOrder = null;
            //Reset Customer Fields 
            $customer = Customer::find($request -> customer_id);
            $customerPhoneNo = $request -> customer_phone_no ?? null;
            $customerEmail = $request -> customer_email ?? null;
            $customerGSTIN = $request -> customer_gstin ?? null;
            $customerName = $request -> consignee_name ?? null;
            //If Customer is Regular, pick from Customer Master
            if ($customer -> customer_type === ConstantHelper::REGULAR) {
                $customerPhoneNo = $customer -> mobile ?? null;
                $customerEmail = $customer -> email ?? null;
                $customerGSTIN = $customer -> compliances ?-> gstin_no ?? null;
            } else {
                //Check for customer details in Cash
                if (!$customerPhoneNo || !$customerEmail || !$customerName) {
                    DB::rollBack();
                    return response() -> json([
                        'message' => 'Please enter all details of the customer',
                        'error' => ''
                    ], 422);
                }
                if ($customerGSTIN) { // Validate Customer
                    $validatedGSTIN = EInvoiceHelper::validateGstNumber($customerGSTIN);
                    if (isset($validatedGSTIN) && isset($validatedData['status'])) {
                        if ($validatedGSTIN['Status'] == 1) {
                            $gstResponse = json_decode($validatedGSTIN['checkGstIn'], true);
                            $addresses = $gstResponse['addresses'] ?? [];
                            //Check the GSTIN with state
                            if (!empty($addresses)) {
                                $firstAddress = $addresses[0];  
                                if (isset($firstAddress['state_id'])) {
                                    $shipAddrStateId = null;
                                    $shipAddr = ErpAddress::find($request -> shipping_address);
                                    if (!isset($shipAddr)) {
                                        $shipAddrStateId = $request -> new_shipping_state_id;
                                    } else {
                                        $shipAddrStateId = $shipAddr -> state_id;
                                    }
                                    $shipState = State::find($shipAddrStateId);
                                    if (isset($shipState) && $shipState -> state_code != $firstAddress['state_id']) {
                                        DB::rollBack();
                                        return response() -> json([
                                            'message' => 'Entered GST Number does not match Shipping Address',
                                            'error' => ''
                                        ], 422);
                                    }
                                }
                            }
                        } else {
                            DB::rollBack();
                            return response() -> json([
                                'message' => 'Entered GST Number is invalid',
                                'error' => ''
                            ], 422);
                        }
                    } else {
                        DB::rollBack();
                        return response() -> json([
                            'message' => 'Entered GST Number is invalid',
                            'error' => ''
                        ], 422);
                    }
                }
            }
            if ($request -> sale_order_id) { //Update
                $saleOrder = ErpSaleOrder::find($request -> sale_order_id);
                $saleOrder -> document_date = $request -> document_date;
                $saleOrder -> reference_number = $request -> reference_no;
                $saleOrder -> consignee_name = $request -> consignee_name;
                $saleOrder -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup
                if(($saleOrder -> document_status == ConstantHelper::APPROVED || $saleOrder -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpSaleOrder', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpSoDynamicField', 'relation_column' => 'header_id'],
                        ['model_type' => 'detail', 'model_name' => 'ErpSoItem', 'relation_column' => 'sale_order_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSoItemAttribute', 'relation_column' => 'so_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSoItemDelivery', 'relation_column' => 'so_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleOrderTed', 'relation_column' => 'so_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSoItemBom', 'relation_column' => 'so_item_id']
                    ];
                    Helper::documentAmendment($revisionData, $saleOrder->id);
                }
                $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedSoItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }

                if (count($deletedData['deletedHeaderExpTedIds'])) {
                    ErpSaleOrderTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
                }

                if (count($deletedData['deletedHeaderDiscTedIds'])) {
                    ErpSaleOrderTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
                }

                if (count($deletedData['deletedItemDiscTedIds'])) {
                    ErpSaleOrderTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
                }

                if (count($deletedData['deletedDelivery'])) {
                    ErpSoItemDelivery::whereIn('id',$deletedData['deletedDelivery'])->delete();
                }

                if (count($deletedData['deletedAttachmentIds'])) {
                    $files = ErpSoMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
                    foreach ($files as $singleMedia) {
                        $filePath = $singleMedia -> file_name;
                        if (Storage::exists($filePath)) {
                            Storage::delete($filePath);
                        }
                        $singleMedia -> delete();
                    }
                }

                if (count($deletedData['deletedSoItemIds'])) {
                    $soItems = ErpSoItem::whereIn('id',$deletedData['deletedSoItemIds'])->get();
                    foreach($soItems as $soItem) {
                        if ($soItem -> sq_item_id) {
                            $qtItem = ErpSoItem::find($soItem -> sq_item_id);
                            if (isset($qtItem)) {
                                $qtItem -> quotation_order_qty -= $soItem -> order_qty;
                                $qtItem -> save();
                            }
                        }
                        if ($soItem -> po_item_id) {
                            $poItem = PoItem::find($soItem -> po_item_id);
                            if (isset($poItem)) {
                                $poItem -> inter_org_so_qty -= $soItem -> order_qty;
                                $poItem -> save();
                            }
                        }
                        if ($soItem -> jo_product_id) {
                            $joProduct = JoProduct::find($soItem -> jo_product_id);
                            if (isset($joProduct)) {
                                $joProduct -> inter_org_so_qty -= $soItem -> order_qty;
                                $joProduct -> save();
                            }
                        }
                        $soItem->custom_bom_details()->delete();
                        $soItem->teds()->delete();
                        $soItem->item_deliveries()->delete();
                        $soItem->attributes()->delete();
                        $soItem->delete();
                    }
                }

            } else { //Create
                $department = Department::find($request -> department_id);
                $saleOrder = ErpSaleOrder::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request -> book_id,
                    'book_code' => $request -> book_code,
                    'document_type' => $request -> type,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request -> document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'order_type' => $request -> sale_order_type ?? SaleModuleHelper::ORDER_TYPE_DEFAULT,
                    'reference_number' => $request -> reference_no,
                    'store_id' => $request -> store_id ?? null,
                    'store_code' => $store ?-> store_name ?? null,
                    'department_id' => $request -> department_id ?? null,
                    'department_code' => $department ?-> name ?? null,
                    'customer_id' => $request -> customer_id,
                    'customer_email' => $customerEmail,
                    'customer_phone_no' => $customerPhoneNo,
                    'customer_gstin' => $customerGSTIN,
                    'customer_code' => $request -> customer_code,
                    'consignee_name' => $request -> consignee_name,
                    'billing_address' => null,
                    'shipping_address' => null,
                    'currency_id' => $request -> currency_id,
                    'currency_code' => $request -> currency_code,
                    'payment_term_id' => $request -> payment_terms_id,
                    'payment_term_code' => $request -> payment_terms_code,
                    'credit_days' => $request -> credit_days ?? 0,
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
                //Billing Address
                $customerBillingAddress = ErpAddress::find($request -> billing_address);
                if (isset($customerBillingAddress)) {
                    $billingAddress = $saleOrder -> billing_address_details() -> create([
                        'address' => $customerBillingAddress -> address,
                        'country_id' => $customerBillingAddress -> country_id,
                        'state_id' => $customerBillingAddress -> state_id,
                        'city_id' => $customerBillingAddress -> city_id,
                        'type' => 'billing',
                        'pincode' => $customerBillingAddress -> pincode,
                        'phone' => $customerBillingAddress -> phone,
                        'fax_number' => $customerBillingAddress -> fax_number
                    ]);
                } else {
                    $billingAddress = $saleOrder -> billing_address_details() -> create([
                        'address' => $request -> new_billing_address,
                        'country_id' => $request -> new_billing_country_id,
                        'state_id' => $request -> new_billing_state_id,
                        'city_id' => $request -> new_billing_city_id,
                        'type' => 'billing',
                        'pincode' => $request -> new_billing_pincode,
                        'phone' => $request -> new_billing_phone,
                        'fax_number' => null
                    ]);
                }
                // Shipping Address
                $customerShippingAddress = ErpAddress::find($request -> shipping_address);
                if (isset($customerShippingAddress)) {
                    $shippingAddress = $saleOrder -> shipping_address_details() -> create([
                        'address' => $customerShippingAddress -> address,
                        'country_id' => $customerShippingAddress -> country_id,
                        'state_id' => $customerShippingAddress -> state_id,
                        'city_id' => $customerShippingAddress -> city_id,
                        'type' => 'shipping',
                        'pincode' => $customerShippingAddress -> pincode,
                        'phone' => $customerShippingAddress -> phone,
                        'fax_number' => $customerShippingAddress -> fax_number
                    ]);
                } else {
                    $shippingAddress = $saleOrder -> shipping_address_details() -> create([
                        'address' => $request -> new_shipping_address,
                        'country_id' => $request -> new_shipping_country_id,
                        'state_id' => $request -> new_shipping_state_id,
                        'city_id' => $request -> new_shipping_city_id,
                        'type' => 'shipping',
                        'pincode' => $request -> new_shipping_pincode,
                        'phone' => $request -> new_shipping_phone,
                        'fax_number' => null
                    ]);
                }
                //Location Address
                $orgLocationAddress = ErpStore::with('address') -> find($request -> store_id);
                if (!isset($orgLocationAddress) || !isset($orgLocationAddress -> address)) {
                    DB::rollBack();
                    return response() -> json([
                        'message' => 'Location Address not assigned',
                        'error' => ''
                    ], 422);
                }
                $locationAddress = $saleOrder -> location_address_details() -> create([
                    'address' => $orgLocationAddress -> address -> address,
                    'country_id' => $orgLocationAddress -> address -> country_id,
                    'state_id' => $orgLocationAddress -> address -> state_id,
                    'city_id' => $orgLocationAddress -> address -> city_id,
                    'type' => 'location',
                    'pincode' => $orgLocationAddress -> address -> pincode,
                    'phone' => $orgLocationAddress -> address -> phone,
                    'fax_number' => $orgLocationAddress -> address -> fax_number
                ]);
            }
                //Dynamic Fields
                $status = DynamicFieldHelper::saveDynamicFields(ErpSoDynamicField::class, $saleOrder -> id, $request -> dynamic_field ?? []);
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
                if (isset($request -> order_discount_value) && count($request -> order_discount_value) > 0)
                foreach ($request -> order_discount_value as $orderHeaderDiscountKey => $orderDiscountValue) {
                    $totalHeaderDiscount += $orderDiscountValue;
                    array_push($totalHeaderDiscountArray, [
                        'id' => isset($request -> order_discount_master_id[$orderHeaderDiscountKey]) ? $request -> order_discount_master_id[$orderHeaderDiscountKey] : null,
                        'amount' => $orderDiscountValue
                    ]);
                }
                //Initialize item discount to 0
                $itemTotalDiscount = 0;
                $itemTotalValue = 0;
                $totalTax = 0;
                $totalItemValueAfterDiscount = 0;

                $saleOrder -> billing_address = isset($billingAddress) ? $billingAddress -> id : null;
                $saleOrder -> shipping_address = isset($shippingAddress) ? $shippingAddress -> id : null;
                $saleOrder -> save();
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
                            if (isset($request -> item_discount_value[$itemKey]) && count($request -> item_discount_value[$itemKey]) > 0)
                            {
                                foreach ($request -> item_discount_value[$itemKey] as $itemDiscountValue) {
                                    $itemDiscount += $itemDiscountValue;
                                }
                            }
                            $itemTotalValue += $itemValue;
                            $itemTotalDiscount += $itemDiscount;
                            $itemValueAfterDiscount = $itemValue - $itemDiscount;
                            $totalValueAfterDiscount += $itemValueAfterDiscount;
                            $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                            //Check if discount exceeds item value
                            if ($totalItemValueAfterDiscount < 0) {
                                DB::rollBack();
                                return response() -> json([
                                    'message' => '',
                                    'errors' => array(
                                        'item_name.' . $itemKey => "Discount more than value"
                                    )
                                ], 422);
                            }
                            $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0);
                            array_push($itemsData, [
                                'sale_order_id' => $saleOrder -> id,
                                'bom_id' => isset($request -> item_bom_id[$itemKey]) ? $request -> item_bom_id[$itemKey] : null,
                                'item_id' => $item -> id,
                                'item_code' => $item -> item_code,
                                'item_name' => $item -> item_name,
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null, //Need to change
                                'uom_code' => isset($request -> item_uom_code[$itemKey]) ? $request -> item_uom_code[$itemKey] : null,
                                'order_qty' => isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0,
                                'invoice_qty' => 0,
                                'inventory_uom_id' => $item -> uom ?-> id,
                                'inventory_uom_code' => $item -> uom ?-> name,
                                'inventory_uom_qty' => $inventoryUomQty,
                                'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
                                'delivery_date' => isset($request -> delivery_date[$itemKey]) ? $request -> delivery_date[$itemKey] : null,
                                'item_discount_amount' => $itemDiscount,
                                'header_discount_amount' => 0,
                                'item_expense_amount' => 0, //Need to change
                                'header_expense_amount' => 0, //Need to change
                                'tax_amount' => 0,
                                'company_currency_id' => null,
                                'company_currency_exchange_rate' => null,
                                'group_currency_id' => null,
                                'group_currency_exchange_rate' => null,
                                'remarks' => isset($request -> item_remarks[$itemKey]) ? $request -> item_remarks[$itemKey] : null,
                                'value_after_discount' => $itemValueAfterDiscount,
                                'item_value' => $itemValue
                            ]);
                        }
                    }
                    foreach ($itemsData as $itemDataKey => $itemDataValue) {
                        //Discount
                        $headerDiscount = 0;
                        $headerDiscount = ($itemDataValue['value_after_discount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                        $itemHeaderDiscountArray = $totalHeaderDiscountArray;
                        foreach ($itemHeaderDiscountArray as &$itemDiscountHeader) {
                            $itemDiscountHeader['amount'] = $itemDiscountHeader['amount'] / $totalHeaderDiscount * $headerDiscount;
                        }
                        $valueAfterHeaderDiscount = $itemDataValue['value_after_discount'] - $headerDiscount;
                        //Expense
                        $itemExpenseAmount = 0;
                        $itemHeaderExpenseAmount = 0;
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($itemDataValue['item_value'] + $headerDiscount + $itemDataValue['item_discount_amount']) / $itemDataValue['order_qty'];
                        $partyCountryId = isset($billingAddress) ? $billingAddress -> country_id : null;
                        $partyStateId = isset($billingAddress) ? $billingAddress -> state_id : null;
                        $taxDetails = SaleModuleHelper::checkTaxApplicability($request -> customer_id, $request -> book_id) ? TaxHelper::calculateTax($itemDataValue['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> shipping_country_id, $partyStateId ?? $request -> shipping_state_id, 'sale') : [];
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                            if($taxDetail['applicability_type']=="collection")
                            {
                                $totalTax += $itemTax;
                            }
                            else
                            {
                                $totalTax -= $itemTax;
                            }
                        }
                        //Check if update or create
                        $itemRowData = [
                            'sale_order_id' => $saleOrder -> id,
                            'bom_id' => $itemDataValue['bom_id'],
                            'item_id' => $itemDataValue['item_id'],
                            'item_code' => $itemDataValue['item_code'],
                            'item_name' => $itemDataValue['item_name'],
                            'hsn_id' => $itemDataValue['hsn_id'],
                            'hsn_code' => $itemDataValue['hsn_code'],
                            'uom_id' => $itemDataValue['uom_id'], //Need to change
                            'uom_code' => $itemDataValue['uom_code'],
                            'order_qty' => $itemDataValue['order_qty'],
                            'invoice_qty' => $itemDataValue['invoice_qty'],
                            'inventory_uom_id' => $itemDataValue['inventory_uom_id'],
                            'inventory_uom_code' => $itemDataValue['inventory_uom_code'],
                            'inventory_uom_qty' => $itemDataValue['inventory_uom_qty'],
                            'rate' => $itemDataValue['rate'],
                            'delivery_date' => $itemDataValue['delivery_date'],
                            'item_discount_amount' => $itemDataValue['item_discount_amount'],
                            // 'header_discounts' => $itemHeaderDiscountArray,
                            'header_discount_amount' => $headerDiscount,
                            'item_expense_amount' => $itemExpenseAmount, //Need to change
                            'header_expense_amount' => $itemHeaderExpenseAmount, //Need to change
                            'tax_amount' => $itemTax,
                            'total_item_amount' => ($itemDataValue['order_qty'] * $itemDataValue['rate']) - ($itemDataValue['item_discount_amount'] + $headerDiscount) + ($itemExpenseAmount + $itemHeaderExpenseAmount) + $itemTax,
                            'company_currency_id' => null,
                            'company_currency_exchange_rate' => null,
                            'group_currency_id' => null,
                            'group_currency_exchange_rate' => null,
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        if (isset($request -> so_item_id[$itemDataKey])) {
                            $oldSoItem = ErpSoItem::find($request -> so_item_id[$itemDataKey]);
                            $soItem = ErpSoItem::updateOrCreate(['id' => $request -> so_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $soItem = ErpSoItem::create($itemRowData);
                        }
                        //BOM 
                        if ($saleOrder -> document_type == ConstantHelper::SO_SERVICE_ALIAS) {
                            $bomDetails = isset($request -> item_bom_details[$itemDataKey]) ? json_decode($request -> item_bom_details[$itemDataKey], true) : [];
                            if (isset($bomDetails) && count($bomDetails) > 0) {
                                foreach ($bomDetails as $bomDetail) {
                                    if (isset($bomDetail['id'])) {
                                        $soItemBom = ErpSoItemBom::find($bomDetail['id']);
                                        if (isset($soItemBom)) {
                                            $soItemBom -> item_attributes = ($bomDetail['bom_attributes']);
                                            $soItemBom -> save();
                                        }
                                    } else {
                                        $currentBomDetail = BomDetail::find($bomDetail['bom_detail_id']);
                                        ErpSoItemBom::create([
                                            'sale_order_id' => $saleOrder -> id,
                                            'so_item_id' => $soItem -> id,
                                            'bom_id' => $currentBomDetail ?-> bom ?-> id,
                                            'bom_detail_id' => $currentBomDetail ?-> id,
                                            'uom_id' => $bomDetail['uom_id'],
                                            'item_id' => $bomDetail['item_id'],
                                            'item_code' => $bomDetail['item_code'],
                                            'item_attributes' => ($bomDetail['bom_attributes']),
                                            'qty' => $bomDetail['qty'],
                                            'station_id' => $bomDetail['station_id'],
                                            'station_name' => $bomDetail['station_name'],
                                            'remark' => isset($bomDetail['remark']) ? $bomDetail['remark'] : null
                                        ]);
                                    }
                                }
                            }
                        }
                        //Quotation 
                        if ($request -> quotation_item_ids && isset($request -> quotation_item_ids[$itemDataKey])) {
                            $qtItem = ErpSoItem::find($request -> quotation_item_ids[$itemDataKey]);
                            if (isset($qtItem)) {
                                $qtItem -> quotation_order_qty = ($qtItem -> quotation_order_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                $qtItem -> save();
                                $soItem -> order_quotation_id = $qtItem -> header ?-> id;
                                $soItem -> sq_item_id = $qtItem -> id;
                                $soItem -> save();
                            }
                        }
                        if ($request -> po_item_ids && isset($request -> po_item_ids[$itemDataKey])) {
                            $poItem = PoItem::find($request -> po_item_ids[$itemDataKey]);
                            if (isset($poItem)) {
                                $poItem -> inter_org_so_qty = ($poItem -> inter_org_so_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                $poItem -> save();
                                $soItem -> po_item_id = $poItem -> id;
                                $soItem -> save();
                            }
                        }
                        if ($request -> jo_product_ids && isset($request -> jo_product_ids[$itemDataKey])) {
                            $joProduct = JoProduct::find($request -> jo_product_ids[$itemDataKey]);
                            if (isset($joProduct)) {
                                $joProduct -> inter_org_so_qty = ($joProduct -> inter_org_so_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                $joProduct -> save();
                                $soItem -> jo_product_id = $joProduct -> id;
                                $soItem -> save();
                                //Save the item
                                //Only Save in case of create
                                if (!$request -> sale_order_id && $saleOrder -> order_type === SaleModuleHelper::ORDER_TYPE_SUB_CONTRACTING) {
                                    $joBomMapping = JoBomMapping::where('jo_product_id', $joProduct -> id) -> get();
                                    foreach ($joBomMapping as $joBomMapping) {
                                        # code...
                                        $jobWorkItem = ErpSoJobWorkItem::updateOrCreate([
                                            'sale_order_id' => $saleOrder -> id,
                                            'so_item_id' => $soItem -> id,
                                            'jo_id' => $joBomMapping -> jo_id,
                                            'bom_detail_id' => $joBomMapping -> bom_detail_id,
                                            'station_id' => $joBomMapping -> station_id,
                                            'rm_type' => $joBomMapping -> rm_type,
                                            'item_id' => $joBomMapping -> item_id,
                                            'item_code' => $joBomMapping -> item_code,
                                            'uom_id' => $joBomMapping -> uom_id,
                                            'qty' => $joBomMapping -> qty,
                                            'inventory_uom_id' => $joBomMapping ?-> item ?-> uom_id,
                                            'inventory_uom_code' => $joBomMapping ?-> item ?-> uom ?-> name,
                                            'inventory_uom_qty' => ItemHelper::convertToBaseUom($joBomMapping -> item_id, $joBomMapping -> uom_id, $joBomMapping -> qty) 
                                        ]);
                                        foreach ($joBomMapping -> attributes as $joBomMappingAttribute) {
                                            $attribute = AttributeGroup::find($joBomMappingAttribute['attribute_name']);
                                            $attributeValue = Attribute::find($joBomMappingAttribute['attribute_value']);
                                            ErpSoJobWorkItemAttribute::updateOrCreate([
                                                'sale_order_id' => $saleOrder -> id,
                                                'job_work_item_id' => $jobWorkItem -> id,
                                                'item_id' => $jobWorkItem -> item_id,
                                                'item_code' => $jobWorkItem -> item_code,
                                                'item_attribute_id' => $joBomMappingAttribute['attribute_id'],
                                                'attribute_name' => $attribute ?-> name,
                                                'attr_name' => $attribute ?-> id,
                                                'attribute_value' => $attributeValue ?-> value,
                                                'attr_value' => $attributeValue ?-> id
                                            ]);
                                        }
                                    }
                                }
                                
                            }
                        }
                        //TED Data (DISCOUNT)
                        if (isset($request -> item_discount_value[$itemDataKey]))
                        {
                            foreach ($request -> item_discount_value[$itemDataKey] as $itemDiscountKey => $itemDiscountTed){
                                $itemDiscountRowData = [
                                    'sale_order_id' => $saleOrder -> id,
                                    'so_item_id' => $soItem -> id,
                                    'ted_type' => 'Discount',
                                    'ted_level' => 'D',
                                    'ted_id' => isset($request -> item_discount_master_id[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_master_id[$itemDataKey][$itemDiscountKey] : null,
                                    'ted_name' => isset($request -> item_discount_name[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_name[$itemDataKey][$itemDiscountKey] : null,
                                    'assessment_amount' => $itemDataValue['rate'] * $itemDataValue['order_qty'],
                                    'ted_percentage' => isset($request -> item_discount_percentage[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_percentage[$itemDataKey][$itemDiscountKey] : null,
                                    'ted_amount' => $itemDiscountTed,
                                    'applicable_type' => 'Deduction',
                                ];
                                if (isset($request -> item_discount_id[$itemDataKey][$itemDiscountKey])) {
                                    $soItemTedForDiscount = ErpSaleOrderTed::updateOrCreate(['id' => $request -> item_discount_id[$itemDataKey][$itemDiscountKey]], $itemDiscountRowData);
                                } else {
                                    $soItemTedForDiscount = ErpSaleOrderTed::create($itemDiscountRowData);
                                }
                            }
                        }
                        //TED Data (TAX)
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $soItemTedForDiscount = ErpSaleOrderTed::updateOrCreate(
                                    [
                                        'sale_order_id' => $saleOrder -> id,
                                        'so_item_id' => $soItem -> id,
                                        'ted_type' => 'Tax',
                                        'ted_level' => 'D',
                                        'ted_id' => $taxDetail['id'],
                                    ],
                                    [
                                        'ted_group_code' => $taxDetail['tax_group'],
                                        'ted_name' => $taxDetail['tax_type'],
                                        'assessment_amount' => $valueAfterHeaderDiscount,
                                        'ted_percentage' => (double)$taxDetail['tax_percentage'],
                                        'ted_amount' => ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount),
                                        'applicable_type' => $taxDetail['applicability_type'],
                                    ]
                                );
                                array_push($itemTaxIds,$soItemTedForDiscount -> id);
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
                                    $itemAttribute = ErpSoItemAttribute::updateOrCreate(
                                        [
                                            'sale_order_id' => $saleOrder -> id,
                                            'so_item_id' => $soItem -> id,
                                            'item_attribute_id' => $attribute['id'],
                                        ],
                                        [
                                            'item_code' => $soItem -> item_code,
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
                        //Item Deliveries
                        if (isset($request -> item_delivery_schedule_qty[$itemDataKey])) {
                            foreach ($request -> item_delivery_schedule_qty[$itemDataKey] as $itemDeliveryKey => $itemDeliveryQty) {
                                if (isset($request -> item_delivery_schedule_date[$itemDataKey][$itemDeliveryKey])) {
                                    if (Carbon::parse($request -> item_delivery_schedule_date[$itemDataKey][$itemDeliveryKey]) -> startOfDay() -> lt(Carbon::parse($saleOrder -> created_at) -> startOfDay())) {
                                        DB::rollBack();
                                        return response() -> json([
                                            'message' => '',
                                            'errors' => array(
                                                'item_name.' . $itemKey => "Past Delivery Date is not allowed"
                                            )
                                        ], 422);
                                    }
                                }
                                $itemDeliveryRowData = [
                                    'sale_order_id' => $saleOrder -> id,
                                    'so_item_id' => $soItem -> id,
                                    'ledger_id' => null,
                                    'qty' => $itemDeliveryQty,
                                    'invoice_qty' => 0,
                                    'delivery_date' => isset($request -> item_delivery_schedule_date[$itemDataKey][$itemDeliveryKey]) ? ($request -> item_delivery_schedule_date[$itemDataKey][$itemDeliveryKey]) : Carbon::now() -> format('Y-m-d'),
                                ];
                                if (isset($request -> item_delivery_schedule_id[$itemDataKey][$itemDeliveryKey])) {
                                    ErpSoItemDelivery::updateOrCreate(['id' => $request -> item_delivery_schedule_id[$itemDataKey][$itemDeliveryKey]], $itemDeliveryRowData);
                                } else {
                                    ErpSoItemDelivery::create($itemDeliveryRowData);
                                }
                            }
                        } 
                        else {
                            if (Carbon::parse($soItem -> delivery_date) -> startOfDay() -> lt(Carbon::parse($saleOrder -> created_at) -> startOfDay())) {
                                DB::rollBack();
                                return response() -> json([
                                    'message' => '',
                                    'errors' => array(
                                        'item_name.' . $itemKey => "Past Delivery Date is not allowed"
                                    )
                                ], 422);
                            }
                            $itemDeliveryRowData = [
                                'sale_order_id' => $saleOrder -> id,
                                'so_item_id' => $soItem -> id,
                                'ledger_id' => null,
                                'qty' => $soItem -> order_qty,
                                'invoice_qty' => 0,
                                'delivery_date' => $soItem -> delivery_date
                            ];
                            ErpSoItemDelivery::updateOrCreate(['sale_order_id' => $saleOrder -> id, 'so_item_id' => $soItem -> id], $itemDeliveryRowData);
                        }
                        ErpSaleOrderTed::where([
                            'sale_order_id' => $saleOrder -> id,
                            'so_item_id' => $soItem -> id,
                            'ted_type' => 'Tax',
                            'ted_level' => 'D',
                        ]) -> whereNotIn('id', $itemTaxIds) -> delete();
                        ErpSoItemAttribute::where([
                            'sale_order_id' => $saleOrder -> id,
                            'so_item_id' => $soItem -> id,
                        ]) -> whereNotIn('id', $itemAttributeIds) -> delete();
                        
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);
                }
                //Header TED (Discount)
                if (isset($request -> order_discount_value) && count($request -> order_discount_value) > 0) {
                    foreach ($request -> order_discount_value as $orderDiscountKey => $orderDiscountVal) {
                        $headerDiscountRowData = [
                            'sale_order_id' => $saleOrder -> id,
                            'so_item_id' => null,
                            'ted_type' => 'Discount',
                            'ted_level' => 'H',
                            'ted_id' => isset($request -> order_discount_master_id[$orderDiscountKey]) ? $request -> order_discount_master_id[$orderDiscountKey] : null,
                            'ted_name' => isset($request -> order_discount_name[$orderDiscountKey]) ? $request -> order_discount_name[$orderDiscountKey] : null,
                            'assessment_amount' => $totalItemValueAfterDiscount,
                            'ted_percentage' => isset($request -> order_discount_percentage[$orderDiscountKey]) ? ($request -> order_discount_percentage[$orderDiscountKey]) : null,
                            'ted_amount' => $orderDiscountVal,
                            'applicable_type' => 'Deduction',
                        ];
                        if (isset($request -> order_discount_id[$orderDiscountKey])) {
                            ErpSaleOrderTed::updateOrCreate(['id' => $request -> order_discount_id[$orderDiscountKey]], $headerDiscountRowData);
                        } else {
                            ErpSaleOrderTed::create($headerDiscountRowData);
                        }
                    }
                }
                //Header TED (Expense)
                $totalValueAfterTax = $totalItemValueAfterDiscount + $totalTax;
                $totalExpenseAmount = 0;
                if (isset($request -> order_expense_value) && count($request -> order_expense_value) > 0) {
                    foreach ($request -> order_expense_value as $orderExpenseKey => $orderExpenseVal) {
                        $headerExpenseRowData = [
                            'sale_order_id' => $saleOrder -> id,
                            'so_item_id' => null,
                            'ted_type' => 'Expense',
                            'ted_level' => 'H',
                            'ted_id' => isset($request -> order_expense_master_id[$orderExpenseKey]) ? $request -> order_expense_master_id[$orderExpenseKey] : null,
                            'ted_name' => isset($request -> order_expense_name[$orderExpenseKey]) ? $request -> order_expense_name[$orderExpenseKey] : null,
                            'assessment_amount' => $totalItemValueAfterDiscount,
                            'ted_percentage' => isset($request -> order_expense_percentage[$orderExpenseKey]) ? $request -> order_expense_percentage[$orderExpenseKey] : null, // Need to change
                            'ted_amount' => $orderExpenseVal,
                            'applicable_type' => 'Collection',
                        ];
                        if (isset($request -> order_expense_id[$orderExpenseKey])) {
                            ErpSaleOrderTed::updateOrCreate(['id' => $request -> order_expense_id[$orderExpenseKey]], $headerExpenseRowData);
                        } else {
                            ErpSaleOrderTed::create($headerExpenseRowData);
                        }
                        $totalExpenseAmount += $orderExpenseVal;
                    }
                }

                //Check all total values
                if ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)+ $totalExpenseAmount < 0)
                {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => 'Document Value cannot be less than 0'
                    ], 422);
                }
                
                $saleOrder -> total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
                $saleOrder -> total_item_value = $itemTotalValue;
                $saleOrder -> total_tax_value = $totalTax;
                $saleOrder -> total_expense_value = $totalExpenseAmount;
                $saleOrder -> total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;
                //Approval check

                if ($request -> sale_order_id) { //Update condition
                    $bookId = $saleOrder->book_id; 
                    $docId = $saleOrder->id;
                    $amendRemarks = $request->amend_remarks ?? null;
                    $remarks = $saleOrder->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachment');
                    $currentLevel = $saleOrder->approval_level;
                    $modelName = get_class($saleOrder);
                    $actionType = $request -> action_type ?? "";
                    if(($saleOrder -> document_status == ConstantHelper::APPROVED || $saleOrder -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $saleOrder->revision_number + 1;
                        $actionType = 'amendment';
                        $totalValue = $saleOrder->grand_total_amount ?? 0;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                        $saleOrder->revision_number = $revisionNumber;
                        $saleOrder->approval_level = 1;
                        $saleOrder->revision_date = now();
                        $amendAfterStatus = $approveDocument['approvalStatus'] ?? $saleOrder -> document_status;
                        // $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                        // if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                        //     $totalValue = $saleOrder->grand_total_amount ?? 0;
                        //     $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        // } else {
                        //     $actionType = 'approve';
                        //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        // }
                        // if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                        //     $actionType = 'submit';
                        //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        // }
                        $saleOrder->document_status = $amendAfterStatus;
                        $saleOrder->save();

                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $saleOrder->revision_number ?? 0;
                            $actionType = 'submit';
                            $totalValue = $saleOrder->grand_total_amount ?? 0;
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                            if ($approveDocument['message']) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => $approveDocument['message'],
                                    'error' => "",
                                ], 422);
                            }

                            $document_status = $approveDocument['approvalStatus'] ?? $saleOrder -> document_status;
                            $saleOrder->document_status = $document_status;
                        } else {
                            $saleOrder->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { //Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $saleOrder->book_id;
                        $docId = $saleOrder->id;
                        $remarks = $saleOrder->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $saleOrder->approval_level;
                        $revisionNumber = $saleOrder->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($saleOrder);
                        $totalValue = $saleOrder->total_amount ?? 0;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                        $saleOrder->document_status = $approveDocument['approvalStatus'] ?? $saleOrder->document_status;

                    }

                    // if ($request->document_status == 'submitted') {
                    //     $totalValue = $saleOrder->total_amount ?? 0;
                    //     $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    //     $saleOrder->document_status = $document_status;
                    // } else {
                    //     $saleOrder->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    // }
                    $saleOrder -> save();
                }

                // $bookId = $po->book_id; 
                // $docId = $po->id;
                // $amendRemarks = $request->amend_remarks ?? null;
                // $remarks = $po->remarks;
                // $amendAttachments = $request->file('amend_attachment');
                // $attachments = $request->file('attachment');
                // $currentLevel = $po->approval_level;
                // $modelName = get_class($po);
                // if ($request->document_status == 'submitted') {
                //     $revisionNumber = $saleOrder->revision_number ?? 0;
                //     $actionType = 'submit'; // Approve // reject // submit
                //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                //     $totalValue = $saleOrder -> total_amount;

                //     $document_status = Helper::checkApprovalRequired($request->book_id);
                //     $saleOrder->document_status = $document_status;
                // } else {
                //     $saleOrder->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                // }
                $saleOrder -> save();
                //Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $saleOrder->uploadDocuments($singleFile, 'sale_order', false);
                    }
                }
                SaleModuleHelper::cashCustomerMasterData($saleOrder);
                SaleModuleHelper::updateEInvoiceDataFromHelper($saleOrder, false);
                SaleModuleHelper::updateOrCreateSoPaymentTerms($saleOrder -> id, $saleOrder -> payment_term_id, $saleOrder -> credit_days);
                DB::commit();
                return response() -> json([
                    'message' => ($saleOrder -> document_type == ConstantHelper::SQ_SERVICE_ALIAS 
                    ? "Sale Quotation" : "Sale Order") . " created successfully",
                    'redirect_url' => ($saleOrder -> document_type == ConstantHelper::SQ_SERVICE_ALIAS
                    ? route('sale.quotation.index') : route('sale.order.index'))
                ]);

            
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => 'Server Error',
                'exception' => $ex -> getMessage()
            ], 500); 
        }
    }

    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $saleOrder = ErpSaleOrder::where('id', $id)->first();
            if (!$saleOrder) {
                return response()->json(['data' => [], 'message' => "Sale Order not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'ErpSaleOrder', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ErpSoItem', 'relation_column' => 'sale_order_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSoItemAttribute', 'relation_column' => 'so_item_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSoItemDelivery', 'relation_column' => 'so_item_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleOrderTed', 'relation_column' => 'so_item_id']
            ];

            $a = Helper::documentAmendment($revisionData, $id);
            if ($a) {
                //*amendmemnt document log*/
                $bookId = $saleOrder->book_id;
                $docId = $saleOrder->id;
                $remarks = 'Amendment';
                $attachments = $request->file('attachment');
                $currentLevel = $saleOrder->approval_level;
                $revisionNumber = $saleOrder->revision_number;
                $actionType = 'amendment'; // Approve // reject // submit // amend
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType);


                $saleOrder->document_status = ConstantHelper::DRAFT;
                $saleOrder->revision_number = $saleOrder->revision_number + 1;
                $saleOrder->approval_level = 1;
                $saleOrder->revision_date = now();
                $saleOrder->save();
            }

            DB::commit();
            return response()->json(['data' => [], 'message' => "Amendment done!", 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Amendment Submit Error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => "An unexpected error occurred. Please try again.", 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    public function getCustomerAddresses(Request $request, string $customerId)
    {
        try {
            $customer = Customer::find($customerId);
            if (!$customer) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Customer not found'
                        )
                    ]);
                }
            if ($customer -> customer_type === ConstantHelper::CASH) {
                $phoneNo = $request -> phone_no ?? null;
                $cashCustomerDetail = CashCustomerDetail::where('phone_no', $phoneNo) -> first();
                $billingAddresses = ErpAddress::where('addressable_id', $cashCustomerDetail ?-> id)->where('addressable_type', CashCustomerDetail::class)->whereIn('type', ['billing', 'both'])->get();
                $shippingAddresses = ErpAddress::where('addressable_id', $cashCustomerDetail ?-> id)->where('addressable_type', CashCustomerDetail::class)->whereIn('type', ['shipping', 'both'])->get();
            } else {
                $billingAddresses = ErpAddress::where('addressable_id', $customerId)->where('addressable_type', Customer::class)
                ->where(function ($subQuery) {
                    $subQuery -> whereIn('type', ['billing', 'both']) -> orWhere('is_billing', 1);
                }) -> get();
                $shippingAddresses = ErpAddress::where('addressable_id', $customerId)->where('addressable_type', Customer::class)
                ->where(function ($subQuery) {
                    $subQuery -> whereIn('type', ['shipping', 'both']) -> orWhere('is_shipping', 1);
                }) -> get();
            }
            foreach ($billingAddresses as $billingAddress) {
                $billingAddress->value = $billingAddress->id;
                $billingAddress->label = $billingAddress->display_address;
            }
            foreach ($shippingAddresses as $shippingAddress) {
                $shippingAddress->value = $shippingAddress->id;
                $shippingAddress->label = $shippingAddress->display_address;
            }
            if ($customer -> customer_type === ConstantHelper::REGULAR) {
                if (count($shippingAddresses) == 0) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Shipping Address not found for ' . $customer?->company_name
                        )
                    ]);
                }
                if (count($billingAddresses) == 0) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Billing Address not found for ' . $customer?->company_name
                        )
                    ]);
                }
                if (!isset($customer?->currency_id)) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Currency not found for ' . $customer?->company_name
                        )
                    ]);
                }
                if (!isset($customer?->payment_terms_id)) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Payment Terms not found for ' . $customer?->company_name
                        )
                    ]);
                }
            }
            //Currency Helper
            $currencyData = CurrencyHelper::getCurrencyExchangeRates($customer?->currency_id ?? 0, $request->document_date ?? '');
            return response()->json([
                'data' => array(
                    'billing_addresses' => $billingAddresses,
                    'shipping_addresses' => $shippingAddresses,
                    'currency_exchange' => $currencyData
                )
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getItemAttributes(string $itemId)
    {
        try {
            $itemAttributes = ErpItemAttribute::with(['group'])->where('item_id', $itemId)->get();
            $item = Item::find($itemId);
            if (!isset($item)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item Not Found'
            ], 500);
            }
            $itemAttributes = ErpItemAttribute::with(['group'])->where('item_id', $itemId)->get();
            foreach ($itemAttributes as $attribute) {
                $attributesArray = array();
                $attribute_ids = [];
                if ($attribute->all_checked) {
                    $attribute_ids = Attribute::where('attribute_group_id', $attribute->attribute_group_id)->get()->pluck('id')->toArray();
                } else {
                    $attribute_ids = json_decode($attribute->attribute_id);
                }
                $attribute->group_name = $attribute->group?->name;
                foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = Attribute::where('id', $attributeValue)->select('id', 'value')->where('status', 'active')->first();
                    if (isset($attributeValueData)) {
                        $attributeValueData->selected = false;
                        array_push($attributesArray, $attributeValueData);
                    }
                }
                $attribute->values_data = $attributesArray;
                $attribute->short_name = $attribute->group?->short_name;
                $attribute->only(['id', 'group_name', 'values_data', 'short_name']);
            }
            return response()->json([
                'data' => array(
                    'attributes' => $itemAttributes,
                    'item' => $item,
                    'item_hsn' => $item->hsn?->code
                )
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getCustomerAddress(Request $request, string $id)
    {
        try {
            $address = ErpAddress::find($id);
            return response()->json([
                'address' => $address
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function getItemDetails(Request $request)
    {
        try {
            $item = Item::with(['alternateUoms.uom', 'category', 'subCategory'])->find($request->item_id);
            $customerItemDetails = ItemHelper::getCustomerItemDetails((int)$request -> item_id, (int) $request->customer_id);
            $selectedUom = $request->uom_id ?? null;
            $totalStockData = InventoryHelper::totalInventoryAndStock($request->item_id, $request->selectedAttr ?? [], 
            $selectedUom, $request->store_id ?? null, $request -> sub_store_id ?? null, $request -> so_item_id ?? null, 
            $request -> station_id ?? null, $request -> stock_type ?? InventoryHelper::STOCK_TYPE_REGULAR,
            $request -> wip_station_id ?? null);
            if (isset($item)) {
                $inventoryUomQty = $request->quantity ?? 0;
                $requestUomId = $selectedUom;
                if ($requestUomId != $item->uom_id) {
                    $alUom = $item->alternateUOMs()->where('uom_id', $requestUomId)->first();
                    if ($alUom) {
                        $inventoryUomQty = intval(isset($request->quantity) ? $request->quantity : 0) * $alUom->conversion_to_inventory;
                    }
                }
            }
            $headerId = $request -> header_id ?? null;
            $detailId = $request -> detail_id ?? null;
            $serviceAlias = $request -> service_alias ?? null;
            $lotNoDetails = [];
            $lrDetails = [];
            if (isset($headerId) && isset($detailId)) {
                $lotNoDetails = InventoryHelper::getIssueTransactionLotNumbers($serviceAlias, $headerId, $detailId, $selectedUom);
            }
           if ($request->type === ConstantHelper::LR_SERVICE_ALIAS) {
                $lorryReceiptDetails = ErpLorryReceipt::with([
                    'locations', 
                    'source', 
                    'destination', 
                    'vehicle'
                ])
                    ->whereIn('id', (array) $request->lrId)
                    ->get();

                      $lrDetails = $lorryReceiptDetails->map(function ($lr) {
                        $totalArticles = $lr->locations->sum('no_of_articles');
                        $totalWeight = $lr->locations->sum('weight');
                        $totalPointCharges = $lr->locations->sum('amount');
                        $totalPoints = $lr->locations->count();

                        return [
                            'lr_no' => $lr->document_number ?? '',
                            'book_code' => $lr->book->book_code,
                            'document_date' =>Carbon::parse($lr->document_date)->format('d-m-Y') ?? '',
                            'source' => $lr->source->name ?? '',
                            'destination' => $lr->destination->name ?? '',
                            'no_of_article' => $totalArticles,
                            'total_weight' => $totalWeight,
                            'points' => $totalPoints,
                            'freight_charges' => $lr->freight_charges ?? 0,
                            'points_charges' => $totalPointCharges ?? 0,
                            'total_charges' => ($lr->freight_charges ?? 0) + ($totalPointCharges ?? 0),
                        ];
                    });
               }

            return response()->json([
                'message' => 'Item details found',
                'item' => $item,
                'inv_qty' => $item->type === ConstantHelper::SERVICE ? 0 : $inventoryUomQty ?? 0,
                'inv_uom' => $item->type === ConstantHelper::SERVICE ? null : $item->uom?->alias,
                'customer_item_details' => $customerItemDetails,
                'lot_details' => $lotNoDetails,
                'stocks' => $totalStockData,
                'lrDetails' => $lrDetails
            ]);
        } catch (Exception $ex) { 
            return response()->json([
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
    public function checkItemBomExists(Request $request)
    {
        $attributes = $request->item_attributes ?? [];
        $itemDetails = ItemHelper::checkItemBomExists($request->item_id, $attributes);
        return array(
            'data' => $itemDetails
        );

    }
    public function getItemStoreData(Request $request)
    {
        try {
            $baseUomQty = ItemHelper::convertToBaseUom($request -> item_id, $request -> uom_id, $request -> quantity ?? 0);
            $storeWiseStockData = InventoryHelper::fetchStockSummary($request -> item_id, $request -> selectedAttr ?? [], 
            $request -> uom_id ?? null, $baseUomQty ?? 0, $request -> store_id ?? null, $request -> sub_store_id ?? null,
            $request -> station_id ?? null, $request -> stock_type ?? InventoryHelper::STOCK_TYPE_REGULAR,
            $request -> wip_station_id ?? null);
            
            return response() -> json([
                'message' => 'Item details found',
                'stores' => [
                    'code' => 200,
                    'message' => '',
                    'status' => 'success',
                    'records' => $storeWiseStockData['records']
                ]
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occured',
                'error' => $ex->getLine()
            ], 500);
        }
    }

    public function processQuotation(Request $request)
    {
        try {
            if ($request -> doc_type === ConstantHelper::PO_SERVICE_ALIAS) {
                $pathUrl = route('po.index', ['po']);
                $quotation = PurchaseOrder::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->with(['discount_ted', 'expense_ted'])->whereHas('items', function ($subQuery) use ($request) {
                    $subQuery->whereIn('id', $request->items_id);
                })->with('items', function ($itemQuery) use ($request) {
                    $itemQuery->whereIn('id', $request->items_id)->with(['discount_ted', 'tax_ted'])->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }
                    ]);
                }) -> bookViewAccess($pathUrl) -> whereIn('id', $request->quotation_id)->get();
                foreach ($quotation as &$header) {
                    $customer = Customer::with(['payment_terms', 'currency']) -> withDefaultGroupCompanyOrg() 
                    -> where('related_party', 'Yes') -> where('enter_company_org_id', $header -> organization_id)
                    -> first();
                    $header -> customer = $customer;
                    $header -> customer_id = $customer ?-> id;
                    $header -> customer_code = $customer ?-> customer_code;
                    $header -> customer_phone_no = $customer ?-> mobile;
                    $header -> customer_email = $customer ?-> email;
                    $header -> customer_gstin = $customer ?-> compliances ?-> gstin_no;
    
                    // $customerShipping = $customer ?-> addresses() -> whereIn('type', ['shipping', 'both']) -> with(['city', 'state', 'country']) -> first();
                    $customerShipping = $header -> store_address() -> with(['city', 'state', 'country']) -> first();
                    // $customerBilling = $customer ?-> addresses() -> whereIn('type', ['billing', 'both']) -> with(['city', 'state', 'country']) -> first();
                    $customerBilling = $header -> bill_address_details() -> with(['city', 'state', 'country']) -> first();    
                    $header -> billing_address_details = $customerBilling;
                    $header -> billing_address = $customerBilling ?-> id;
                    $header -> shipping_address_details = $customerShipping;
                    $header -> shipping_address = $customerShipping ?-> id;
                    foreach ($header->items as &$orderItem) {
                        $orderItem->item_attributes_array = $orderItem->item_attributes_array();
                        $orderItem->quotation_balance_qty = $orderItem->inter_org_so_bal_qty;
                    }
                }
            } else if ($request -> doc_type === ConstantHelper::JO_SERVICE_ALIAS) {
                $pathUrl = route('jo.index');
                $quotation = JobOrder::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->with(['discount_ted', 'expense_ted'])->whereHas('items', function ($subQuery) use ($request) {
                    $subQuery->whereIn('id', $request->items_id);
                })->with('items', function ($itemQuery) use ($request) {
                    $itemQuery->whereIn('id', $request->items_id)->with(['discount_ted', 'tax_ted'])->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }
                    ]);
                }) -> bookViewAccess($pathUrl) -> whereIn('id', $request->quotation_id)->get();
                foreach ($quotation as &$header) {
                    $customer = Customer::with(['payment_terms', 'currency']) -> withDefaultGroupCompanyOrg() 
                    -> where('related_party', 'Yes') -> where('enter_company_org_id', $header -> organization_id)
                    -> first();
                    $header -> customer = $customer;
                    $header -> customer_id = $customer ?-> id;
                    $header -> customer_code = $customer ?-> customer_code;
                    $header -> customer_phone_no = $customer ?-> mobile;
                    $header -> customer_email = $customer ?-> email;
                    $header -> customer_gstin = $customer ?-> compliances ?-> gstin_no;
    
                    // $customerShipping = $customer ?-> addresses() -> whereIn('type', ['shipping', 'both']) -> with(['city', 'state', 'country']) -> first();
                    $customerShipping = $header -> store_address() -> with(['city', 'state', 'country']) -> first();
                    // $customerBilling = $customer ?-> addresses() -> whereIn('type', ['billing', 'both']) -> with(['city', 'state', 'country']) -> first();
                    $customerBilling = $header -> bill_address_details() -> with(['city', 'state', 'country']) -> first();    
                    $header -> billing_address_details = $customerBilling;
                    $header -> billing_address = $customerBilling ?-> id;
                    $header -> shipping_address_details = $customerShipping;
                    $header -> shipping_address = $customerShipping ?-> id;
                    foreach ($header->items as &$orderItem) {
                        $orderItem->item_attributes_array = $orderItem->item_attributes_array();
                        $orderItem->quotation_balance_qty = $orderItem->inter_org_so_bal_qty;
                    }
                }
            } else {
                $pathUrl = route('sale.quotation.index');
                $quotation = ErpSaleOrder::with(['discount_ted', 'expense_ted', 
                'billing_address_details', 'shipping_address_details'])->with('customer', function ($sQuery) {
                    $sQuery->with(['currency', 'payment_terms']);
                })->whereHas('items', function ($subQuery) use ($request) {
                    $subQuery->whereIn('id', $request->items_id);
                })->with('items', function ($itemQuery) use ($request) {
                    $itemQuery->whereIn('id', $request->items_id)->with(['discount_ted', 'tax_ted', 'item_deliveries'])->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }
                    ]);
                }) -> bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()
                ->where('document_type', ConstantHelper::SQ_SERVICE_ALIAS)->whereIn('id', $request->quotation_id)->get();
            }
            return response()->json([
                'message' => 'Data found',
                'data' => $quotation
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getQuotations(Request $request)
    {
        try {
            $orgId = Helper::getAuthenticatedUser() ?-> organization_id;
            $pathUrl = route('sale.quotation.index');
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
            if ($request -> doc_type === ConstantHelper::PO_SERVICE_ALIAS) {
                $quotation = PoItem::withWhereHas('header', function ($subQuery) use ($request, $applicableBookIds, $pathUrl, $orgId) {
                    $subQuery->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                    ->whereIn('book_id', $applicableBookIds)->when($request->book_id, function ($bookQuery) use ($request) {
                        $bookQuery->where('book_id', $request->book_id);
                    })->when($request->document_id, function ($docQuery) use ($request) {
                        $docQuery->where('id', $request->document_id);
                    })
                    ->where('organization_id', '!=', $orgId)
                    -> whereHas('vendor', function ($vendorQuery) use($orgId) {
                        $vendorQuery -> where('related_party', 'Yes') -> where('enter_company_org_id', $orgId);
                    });
                })-> with('attributes') -> with('uom') -> whereRaw('(order_qty - short_close_qty) > inter_org_so_qty');
    
                if ($request->item_id) {
                    $quotation = $quotation->where('item_id', $request->item_id);
                }
    
                $quotation = $quotation->get();
                foreach ($quotation as $qt) {
                    $customer = Customer::with(['payment_terms', 'currency']) -> withDefaultGroupCompanyOrg() ->
                     where('related_party', 'Yes') -> where('enter_company_org_id', $qt ?-> header ?-> organization_id)-> 
                     first();
                    $qt -> customer = $customer;
                }
            } else if ($request -> doc_type === ConstantHelper::JO_SERVICE_ALIAS) {
                $joOrderType = SaleModuleHelper::getJoOrderTypeFromSoOrderType($request -> order_type);
                $quotation = JoProduct::withWhereHas('header', function ($subQuery) use ($request, $applicableBookIds, $pathUrl, $orgId, $joOrderType) {
                    $subQuery->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) ->where('job_order_type', $joOrderType)
                    ->whereIn('book_id', $applicableBookIds)->when($request->book_id, function ($bookQuery) use ($request) {
                        $bookQuery->where('book_id', $request->book_id);
                    })->when($request->document_id, function ($docQuery) use ($request) {
                        $docQuery->where('id', $request->document_id);
                    })
                    ->where('organization_id', '!=', $orgId)
                    -> whereHas('vendor', function ($vendorQuery) use($orgId) {
                        $vendorQuery -> where('related_party', 'Yes') -> where('enter_company_org_id', $orgId);
                    });
                })-> with('attributes') -> with('uom') -> whereRaw('(order_qty - short_close_qty) > inter_org_so_qty');
    
                if ($request->item_id) {
                    $quotation = $quotation->where('item_id', $request->item_id);
                }
    
                $quotation = $quotation->get();
                foreach ($quotation as $qt) {
                    $customer = Customer::with(['payment_terms', 'currency']) -> withDefaultGroupCompanyOrg() ->
                     where('related_party', 'Yes') -> where('enter_company_org_id', $qt ?-> header ?-> organization_id)-> 
                     first();
                    $qt -> customer = $customer;
                }
            } else {
                $quotation = ErpSoItem::whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $pathUrl) {
                    $subQuery->where('document_type', ConstantHelper::SQ_SERVICE_ALIAS)->
                    whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                    ->whereIn('book_id', $applicableBookIds)->when($request->customer_id, function ($custQuery) use ($request) {
                        $custQuery->where('customer_id', $request->customer_id);
                    })->when($request->book_id, function ($bookQuery) use ($request) {
                        $bookQuery->where('book_id', $request->book_id);
                    })->when($request->document_id, function ($docQuery) use ($request) {
                        $docQuery->where('id', $request->document_id);
                    }) -> bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg();
                })-> with('attributes') -> with('uom') -> with(['header.customer']) -> whereColumn('quotation_order_qty', "<", "order_qty");
    
                if ($request->item_id) {
                    $quotation = $quotation->where('item_id', $request->item_id);
                }
    
                $quotation = $quotation->get();
            }

            return response()->json([
                'data' => $quotation
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function addAddress(Request $request)
    {
        try {
            $customer = Customer::find($request -> customer_id);
            if (!isset($customer)) {
                return response()->json([
                    'message' => 'Customer Not found',
                    'error' => 'Customer not found'
                ], 500);
            }
            $address = ErpAddress::where([
                ['addressable_id', $request->customer_id],
                ['addressable_type', Customer::class],
                ['country_id', $request->country_id],
                ['state_id', $request->state_id],
                ['city_id', $request->city_id],
                ['address', $request->address],
                ['type', $request->type],
                ['pincode', $request->pincode],
                ['phone', $request->phone],
            ])->get()->first();
            if (isset($address)) {
                return response()->json([
                    'data' => $address
                ]);
            }
            $address = new ErpAddress([
                'addressable_id' => $request->customer_id,
                'addressable_type' => Customer::class,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'address' => $request->address,
                'type' => $request->type,
                'pincode' => $request->pincode,
                'phone' => $request->phone,
                'fax_number' => $request->fax_no ?? null,
                'is_billing' => $request->type == 'billing' ? 1 : 0,
                'is_shipping' => $request->type == 'shipping' ? 1 : 0
            ]);
            return response()->json([
                'data' => $address
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    // genrate pdf
    public function generatePdf(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $pathUrl = "sales-order";
        if ($request -> document_type === ConstantHelper::SQ_SERVICE_ALIAS) {
            $pathUrl = 'sales-quotation';
        }
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();

        $order = ErpSaleOrder::with(
                [
                    'customer',
                    'currency',
                    'discount_ted',
                    'expense_ted',
                    'billing_address_details',
                    'shipping_address_details',
                    'location_address_details'
                ]
            ) ->with('items', function ($query) {
                $query->with('discount_ted', 'tax_ted', 'item_deliveries')->with([
                    'item' => function ($itemQuery) {
                        $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                    }
                ]);
            }) -> bookViewAccess($pathUrl) -> withDraftListingLogic() -> where('id', $id)
            ->firstOrFail();
            $pdfFile = $request -> type == 'grouped' ? "pdf.sales-document-attribute-wise" : "pdf.sales-document";
            $maxAttributeCount = 0;
            $allAttributeValues = [];
            $soItemAttributes = ErpSoItemAttribute::where('sale_order_id', $order -> id)
                -> select('attribute_name') -> distinct() -> get() -> pluck('attribute_name') -> toArray();

            $orderItems = ErpSoItem::where('sale_order_id', $order->id)
            ->select(
                'item_id', 'item_code', 'item_name', 'hsn_id', 'hsn_code', 'uom_id', 'rate',
                DB::raw('SUM(order_qty) AS order_qty'),
                DB::raw('SUM(item_discount_amount) AS item_discount_amount'),
                DB::raw('SUM(header_discount_amount) AS header_discount_amount'),
                DB::raw('SUM(tax_amount) AS tax_amount'),
                DB::raw('COUNT(id) AS attribute_count')
            )
            ->groupBy('item_id', 'item_code', 'item_name', 'hsn_id', 'hsn_code', 'uom_id', 'rate')
            ->get();

            if (count($soItemAttributes) == 1 && $request -> type == 'grouped' && count($order -> items) > count($orderItems)) {
                $pdfFile = "pdf.sales-document-attribute-wise";
    
                foreach ($orderItems as $orderItem) {
                    if ($orderItem -> attribute_count > $maxAttributeCount) {
                        $maxAttributeCount = $orderItem -> attribute_count;
                    }
                    $soItems = ErpSoItem::where('sale_order_id', $order -> id)
                     -> where('item_id', $orderItem -> item_id) -> where('uom_id', $orderItem -> uom_id)
                     -> where('rate', $orderItem -> rate) -> with('tax_ted') -> get();
                    foreach ($soItems as $soItem) {
                        $itemAttributeVal = implode(" ", $soItem -> attributes -> pluck('attribute_value') -> toArray());
                        if (!in_array($itemAttributeVal, $allAttributeValues)) {
                            array_push($allAttributeValues, $itemAttributeVal);
                        }
                        $quantity = $soItem -> order_qty;
                        if (isset($orderItem -> attribute_wise_qty)) {
                            $previousArray = $orderItem -> attribute_wise_qty;
                            array_push($previousArray, [
                                'attribute_value' => $itemAttributeVal,
                                'qty' => $quantity
                            ]);
                            $orderItem -> attribute_wise_qty = $previousArray;
                            $previousTaxTed = $orderItem -> tax_ted;
                            $previousTaxTed = $previousTaxTed -> concat($soItem -> tax_ted);
                            $orderItem -> tax_ted = $previousTaxTed;
                        } else {
                            $orderItem -> attribute_wise_qty = [[
                                'attribute_value' => $itemAttributeVal,
                                'qty' => $quantity
                            ]];
                            $orderItem -> tax_ted = $soItem -> tax_ted;
                        }
                    }
                }
            } else {
                $pdfFile = "pdf.sales-document";
                $orderItems = $order -> items;
            }

        $type = ConstantHelper::SERVICE_LABEL[$order->document_type];

        $shippingAddress = $order->shipping_address_details;
        $billingAddress = $order->billing_address_details;

        $totalItemValue = $order->total_item_value ?? 0.00;
        $totalDiscount = $order->total_discount_value ?? 0.00;
        $totalTaxes = $order->total_tax_value ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $order->total_expense_value ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $approvedBy = Helper::getDocStatusUser(get_class($order), $order -> id, $order -> document_status);
        $dynamicFields = $order -> dynamic_fields;
        $dataArray = [
            'type' => $type,
            'user' => $user,
            'approvedBy' => $approvedBy,
            'order' => $order,
            'items' => $orderItems,
            'maxAttributeCount' => $maxAttributeCount,
            'attributeName' => isset($soItemAttributes[0]) ? $soItemAttributes[0] : null,
            'allAttributeValues' => $allAttributeValues,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'organization' => $organization,
            'amountInWords' => $amountInWords,
            'organizationAddress' => $organizationAddress,
            'totalItemValue' => $totalItemValue,
            'totalDiscount' => $totalDiscount,
            'totalTaxes' => $totalTaxes,
            'totalTaxableValue' => $totalTaxableValue,
            'totalAfterTax' => $totalAfterTax,
            'totalExpense' => $totalExpense,
            'totalAmount' => $totalAmount,
            'imagePath' => $imagePath,
            'dynamicFields' => $dynamicFields
        ];
        $pdf = PDF::loadView(
            $pdfFile,
            $dataArray
        );
        if ($order->document_type == 'so') {
            return $pdf->stream('Sales-Order.pdf');
        } else {
            return $pdf->stream('Sales-Quotation.pdf');
            
        }       
    }

    public function revokeSalesOrderOrQuotation(Request $request)
    {
        DB::beginTransaction();
        try {
            $saleDocument = ErpSaleOrder::find($request -> id);
            if (isset($saleDocument)) {
                $revoke = Helper::approveDocument($saleDocument -> book_id, $saleDocument -> id, $saleDocument -> revision_number, '', [], 0, ConstantHelper::REVOKE, $saleDocument -> total_amount, get_class($saleDocument));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $saleDocument -> document_status = $revoke['approvalStatus'];
                    $saleDocument -> save();
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

    public function rePopulateStoresDropdown(Request $request)
    {
        try {
            $storeId = $request -> store_id ?? null;
            $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK, $storeId);
            return response() -> jsno([
                'stores' => $stores,
                'message' => 'Locations found !'
            ]);
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }

    public static function getProductionBomOfItem(Request $request)
    {
        try {
            $itemId = $request -> item_id ?? null;
            $attributes = [];
            //Process Attributes
            $attributes = $request->item_attributes ?? [];
            $bomAttributes = json_decode($request->bom_attributes ?? [], true);
            //Get the Item BOM
            $bomDetails = ItemHelper::checkItemBomExists($itemId, $attributes);
            if (isset($bomDetails['bom_id'])) {
                // if ($bomDetails['customizable'] == "yes") { //Only check for customizable BOM
                    $customizable = $bomDetails['customizable'] == "yes" ? true : false;
                    $bom = Bom::find($bomDetails['bom_id']);
                    if (isset($bom)) {
                        //Bom found
                        $productionRouteId = $bom -> production_route_id;
                        $processedData = collect([]);
                        $productionStations = ProductionRouteDetail::where('production_route_id', $productionRouteId) 
                        -> where('consumption', "yes") -> orderBy('level') -> get();
                        foreach ($productionStations as $prodLevel) {
                            //Create a new level
                            $level = new stdClass();
                            $level -> id = $prodLevel -> station_id;
                            $level -> name = $prodLevel ?-> station ?-> name;
                            $stationId = $prodLevel -> station_id;
                            //Get the bom components for corresponding station Ids
                            $bomDetails = BomDetail::with('attributes') -> where('bom_id', $bom -> id) -> where('station_id', $stationId) -> get();
                            foreach ($bomDetails as &$bomDetail) {
                                $bomDetail -> item_name = $bomDetail -> item ?-> item_name;
                                $bomDetail -> uom_name = $bomDetail -> uom ?-> name;
                                $bomDetail -> item_attributes_array = $bomDetail -> item_attributes_array();
                                $bomDetail -> qty = $bomDetail -> qty;
                                $bomDetail -> remark = $bomDetail -> remark;
                                //If request has BOM attributes -> check for the selected value
                                if (isset($bomAttributes) && count($bomAttributes) > 0)
                                //Get the current BOM from the request
                                $selectBomAttributeVal = array_filter($bomAttributes, function ($bomAttr) use($bomDetail) {
                                    return $bomAttr['bom_detail_id'] == $bomDetail -> id;
                                });
                                if (isset($selectBomAttributeVal) && count($selectBomAttributeVal) > 0) {
                                    //Bom Item found
                                    $selectBomAttributeVal = array_values($selectBomAttributeVal);
                                    foreach ($bomDetail -> item_attributes_array as &$currentBomAttr) {
                                        foreach ($currentBomAttr['values_data'] as $currentBomAttrValData) {
                                            $newSelectedVal = array_filter($selectBomAttributeVal[0]['bom_attributes'], function ($selectedBomAttri) use($currentBomAttrValData, $currentBomAttr) {
                                                return $selectedBomAttri['attribute_value_id'] == $currentBomAttrValData -> id && $selectedBomAttri['attribute_id'] == $currentBomAttr['id'];
                                            });
                                            //Selected attribute value is found from request
                                            if (isset($newSelectedVal) && count($newSelectedVal) > 0) {
                                                $newSelectedVal = array_values($newSelectedVal);
                                                $currentBomAttrValData['selected'] = true;
                                            } else {
                                                $currentBomAttrValData['selected'] = false;
                                            }
                                        }
                                    }
                                }
                            }
                            $level -> bom_details = $bomDetails;
                            //Push all details to processed data
                            $processedData -> push($level); 
                        }
                        return response() -> json([
                            'status' => 'success',
                            'message' => '',
                            'data' => array(
                                'levels' => $processedData,
                                'customizable' => $customizable
                            )
                        ]);
                    } else {
                        //Bom not found
                        return response() -> json([
                            'status' => 'success',
                            'message' => 'No Customizable BOM found',
                            'data' => []
                        ]);
                    }
                // } else {
                //     //Customizable Bom not found
                //     return response() -> json([
                //         'status' => 'success',
                //         'message' => 'No Customizable BOM found',
                //         'data' => []
                //     ]);
                // }
            } else {
                //Bom id not found
                return response() -> json([
                    'status' => 'success',
                    'message' => 'BOM not found',
                    'data' => []
                ]);
            }
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }

    public function shortCloseSubmit(Request $request)
    {
        DB::beginTransaction();
        try {
            if($request->short_close_ids) {
                $shortCloseIds = explode(',',$request->short_close_ids) ?? [];
                $shortCloseItems =  ErpSoItem::where('id',$shortCloseIds)->get();
                $so = null;
                foreach($shortCloseItems as $shortCloseItem) {
                    $shortCloseItem->short_close_qty = $shortCloseItem->short_bal_qty;
                    $shortCloseItem->save();
                    $so = $shortCloseItem?->header;
                }
                if($so) {
                    $bookId = $so->book_id;
                    $docId = $so->id; 
                    $revisionNumber = $so->revision_number;
                    $amendRemarks = $request->amend_remark ?? '';
                    $currentLevel = $so->approval_level ?? 1;
                    $actionType = 'short close';
                    $totalValue = $so->grand_total_amount;
                    $modelName = get_class($so);
                    $amendAttachments = $request->file('amend_attachment');
                    Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                }
            }

            DB::commit();

            return response() -> json([
                'status' => 'success',
                'message' => 'Short Close Succesfully!',
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex -> getMessage());
        }
    }

    public function salesOrderReport(Request $request)
    {
        $pathUrl = route('sale.order.index');
        $orderType = ConstantHelper::SO_SERVICE_ALIAS;
        $soItems = ErpSoItem::whereHas('header', function ($headerQuery) use($orderType, $pathUrl, $request) {
            $headerQuery -> where('document_type', $orderType)->  bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic();
            //Customer Filter
            $headerQuery = $headerQuery -> when($request -> customer_id, function ($custQuery) use($request) {
                $custQuery -> where('customer_id', $request -> customer_id);
            });
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
            });
            //Document Status Filter
            $headerQuery = $headerQuery -> when($request -> doc_status, function ($docStatusQuery) use($request) {
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
            $datatables = DataTables::of($soItems) ->addIndexColumn()
            ->editColumn('status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->header->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = ucfirst($row -> header -> document_status);   
                $editRoute = null;
                if ($orderType == ConstantHelper::SO_SERVICE_ALIAS) {
                    $editRoute = route('sale.order.edit', ['id' => $row->header->id]);
                }
                if ($orderType == ConstantHelper::SQ_SERVICE_ALIAS) {
                    $editRoute = route('sale.quotation.edit', ['id' => $row->header->id]);
                }     
                return "
                <div style='text-align:right;'>
                    <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <a href='" . $editRoute . "'>
                            <i class='cursor-pointer' data-feather='eye'></i>
                        </a>
                </div>
            ";
            })
            ->addColumn('book_name', function ($row) {
                return $row -> header -> book_code;
            })
            ->addColumn('document_number', function ($row) {
                return $row -> header -> document_number;
            })
            ->addColumn('document_date', function ($row) {
                return $row -> header -> document_date;
            })
            ->addColumn('store_name', function ($row) {
                return $row -> header ?-> store ?-> store_name;
            })
            ->addColumn('store_name', function ($row) {
                return $row -> header ?-> store ?-> store_name;
            })
            ->addColumn('customer_name', function ($row) {
                return $row -> header ?-> customer ?-> company_name;
            })
            ->addColumn('customer_currency', function ($row) {
                return $row -> header -> currency_code;
            })
            ->addColumn('payment_terms_name', function ($row) {
                return $row -> header -> payment_term_code;
            })
            ->addColumn('hsn_code', function ($row) {
                return $row -> hsn ?-> code;
            })
            ->addColumn('uom_name', function ($row) {
                return $row -> uom ?-> name;
            })
            ->addColumn('so_qty', function ($row) {
                return number_format($row -> order_qty, 2);
            })
            ->editColumn('mi_qty', function ($row) {
                return number_format($row -> mi_qty, 2);
            })
            ->editColumn('pwo_qty', function ($row) {
                return number_format($row -> pwo_qty, 2);
            })
            ->editColumn('pslip_qty', function ($row) {
                return number_format($row -> pslip_qty, 2);
            })
            ->editColumn('invoice_qty', function ($row) {
                return number_format($row -> invoice_qty, 2);
            })
            ->editColumn('srn_qty', function ($row) {
                return number_format($row -> srn_qty, 2);
            })
            ->editColumn('rate', function ($row) {
                return number_format($row -> srn_qty, 2);
            })
            ->addColumn('total_discount_amount', function ($row) {
                return number_format($row -> header_discount_amount + $row -> item_discount_amount, 2);
            })
            ->editColumn('tax_amount', function ($row) {
                return number_format($row -> tax_amount, 2);
            })
            ->addColumn('taxable_amount', function ($row) {
                return number_format($row -> total_item_amount - $row -> tax_amount, 2);
            })
            ->editColumn('total_item_amount', function ($row) {
                return number_format($row -> total_item_amount, 2);
            })
            ->editColumn('short_close_qty', function ($row) {
                return number_format($row -> short_close_qty, 2);
            })
            ->editColumn('pending_qty', function ($row) {
                return number_format($row -> pending_qty, 2);
            })
            ->addColumn('delivery_schedule', function ($row) {
                $deliveryHtml = '';
                if (count($row -> item_deliveries) > 0) {
                    foreach ($row -> item_deliveries as $itemDelivery) {
                        $deliveryDate = Carbon::parse($itemDelivery -> delivery_date) -> format('d-m-Y');
                        $deliveryQty = number_format($itemDelivery -> qty, 2);
                        $deliveryHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$deliveryDate</strong> : $deliveryQty</span>";
                    }
                } else {
                    $parsedDeliveryDate = Carbon::parse($row -> delivery_date) -> format('d-m-Y');
                    $deliveryHtml .= "$parsedDeliveryDate";
                }
                return $deliveryHtml;
            })
            ->addColumn('item_attributes', function ($row) {
                $attributesUi = '';
                if (count($row -> item_attributes) > 0) {
                    foreach ($row -> item_attributes as $soAttribute) {
                        $attrName = $soAttribute -> attribute_name;
                        $attrValue = $soAttribute -> attribute_value;
                        $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName : $attrValue </span>";
                    }
                } else {
                    $attributesUi = 'N/A';
                }
                return $attributesUi;
            });
            foreach ($dynamicFields as $field) {
                $datatables = $datatables->addColumn($field -> name, function ($row) use ($field) {
                    $value = '';
                    $actualDynamicFields = $row -> header ?-> dynamic_fields;
                    foreach ($actualDynamicFields as $actualDynamicField) {
                        if ($field -> id == $actualDynamicField -> dynamic_field_detail_id) {
                            $value = $actualDynamicField -> value;
                            return $value;
                        }
                    }
                });
            }
            $datatables = $datatables
            ->rawColumns(['item_attributes','delivery_schedule','status'])
            ->make(true);
            return $datatables;
    }

    public function salesOrderReportAttributeGrouped(Request $request)
    {
        $pathUrl = route('sale.order.index');
        $orderType = ConstantHelper::SO_SERVICE_ALIAS;
        $soItems = ErpSoItem::whereHas('header', function ($headerQuery) use($orderType, $pathUrl, $request) {
            $headerQuery -> where('document_type', $orderType) -> bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic();
            //Customer Filter
            $headerQuery = $headerQuery -> when($request -> customer_id, function ($custQuery) use($request) {
                $custQuery -> where('customer_id', $request -> customer_id);
            });
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
            });
            //Document Status Filter
            $headerQuery = $headerQuery -> when($request -> doc_status, function ($docStatusQuery) use($request) {
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
        }) -> select(
            'sale_order_id','item_id', 'item_code', 'item_name', 'hsn_id', 'hsn_code', 'uom_id', 'rate',
            DB::raw('SUM(order_qty) AS order_qty'),
            DB::raw('SUM(item_discount_amount) AS item_discount_amount'),
            DB::raw('SUM(header_discount_amount) AS header_discount_amount'),
            DB::raw('SUM(total_item_amount) AS total_item_amount'),
            DB::raw('SUM(tax_amount) AS tax_amount'),
            DB::raw('SUM(pwo_qty) AS pwo_qty'),
            DB::raw('SUM(pslip_qty) AS pslip_qty'),
            DB::raw('SUM(invoice_qty) AS invoice_qty'),
            DB::raw('SUM(srn_qty) AS srn_qty'),
            DB::raw('SUM(short_close_qty) AS short_close_qty'),
            DB::raw('GROUP_CONCAT(id) AS so_item_ids')
        )
        ->groupBy('sale_order_id','item_id', 'item_code', 'item_name', 'hsn_id', 'hsn_code', 'uom_id', 'rate')
        ->orderByDesc('id');
            $dynamicFields = DynamicFieldHelper::getServiceDynamicFields(ConstantHelper::SO_SERVICE_ALIAS);
            $datatables = DataTables::of($soItems) ->addIndexColumn()
            ->editColumn('status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->header->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = ucfirst($row -> header -> document_status);   
                $editRoute = null;
                if ($orderType == ConstantHelper::SO_SERVICE_ALIAS) {
                    $editRoute = route('sale.order.edit', ['id' => $row->header->id]);
                }
                if ($orderType == ConstantHelper::SQ_SERVICE_ALIAS) {
                    $editRoute = route('sale.quotation.edit', ['id' => $row->header->id]);
                }     
                return "
                <div style='text-align:right;'>
                    <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <a href='" . $editRoute . "'>
                            <i class='cursor-pointer' data-feather='eye'></i>
                        </a>
                </div>
            ";
            })
            ->addColumn('book_name', function ($row) {
                return $row -> header -> book_code;
            })
            ->addColumn('document_number', function ($row) {
                return $row -> header -> document_number;
            })
            ->addColumn('document_date', function ($row) {
                return $row -> header -> document_date;
            })
            ->addColumn('store_name', function ($row) {
                return $row -> header ?-> store ?-> store_name;
            })
            ->addColumn('store_name', function ($row) {
                return $row -> header ?-> store ?-> store_name;
            })
            ->addColumn('customer_name', function ($row) {
                return $row -> header ?-> customer ?-> company_name;
            })
            ->addColumn('customer_currency', function ($row) {
                return $row -> header -> currency_code;
            })
            ->addColumn('payment_terms_name', function ($row) {
                return $row -> header -> payment_term_code;
            })
            ->addColumn('hsn_code', function ($row) {
                return $row -> hsn ?-> code;
            })
            ->addColumn('uom_name', function ($row) {
                return $row -> uom ?-> name;
            })
            ->addColumn('item_category', function ($row) {
                return $row -> item ?-> category ?-> name;
            })
            ->addColumn('so_qty', function ($row) {
                return number_format($row -> order_qty, 2);
            })
            ->editColumn('mi_qty', function ($row) {
                return number_format($row -> mi_qty, 2);
            })
            ->editColumn('pwo_qty', function ($row) {
                return number_format($row -> pwo_qty, 2);
            })
            ->editColumn('pslip_qty', function ($row) {
                return number_format($row -> pslip_qty, 2);
            })
            ->editColumn('invoice_qty', function ($row) {
                return number_format($row -> invoice_qty, 2);
            })
            ->editColumn('srn_qty', function ($row) {
                return number_format($row -> srn_qty, 2);
            })
            ->editColumn('rate', function ($row) {
                return number_format($row -> rate, 2);
            })
            ->addColumn('total_discount_amount', function ($row) {
                return number_format($row -> header_discount_amount + $row -> item_discount_amount, 2);
            })
            ->editColumn('tax_amount', function ($row) {
                return number_format($row -> tax_amount, 2);
            })
            ->addColumn('taxable_amount', function ($row) {
                return number_format($row -> total_item_amount - $row -> tax_amount, 2);
            })
            ->editColumn('total_item_amount', function ($row) {
                return number_format($row -> total_item_amount, 2);
            })
            ->editColumn('short_close_qty', function ($row) {
                return number_format($row -> short_close_qty, 2);
            })
            ->editColumn('pending_qty', function ($row) {
                return number_format(((($row -> order_qty - $row -> short_close_qty) - $row -> invoice_qty) + $row -> srn_qty), 2);
            })
            ->addColumn('doc_remarks', function ($row) {
                return $row -> header -> remarks;
            })
            ->addColumn('delivery_schedule', function ($row) {
                $deliveryHtml = '';
                $soItemIds = explode(',',$row -> so_item_ids);
                $itemDeliveries = ErpSoItemDelivery::whereIn('so_item_id', $soItemIds) -> get();
                if (count($itemDeliveries) > 0) {
                    foreach ($itemDeliveries as $itemDelivery) {
                        $deliveryDate = Carbon::parse($itemDelivery -> delivery_date) -> format('d-m-Y');
                        $deliveryQty = number_format($itemDelivery -> qty, 2);
                        $deliveryHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$deliveryDate</strong> : $deliveryQty</span>";
                    }
                } else {
                    $parsedDeliveryDate = Carbon::parse($row -> delivery_date) -> format('d-m-Y');
                    $deliveryHtml .= "$parsedDeliveryDate";
                }
                return $deliveryHtml;
            });
            foreach ($dynamicFields as $field) {
                $datatables = $datatables->addColumn($field -> name, function ($row) use ($field) {
                    $value = '';
                    $actualDynamicFields = $row -> header ?-> dynamic_fields;
                    foreach ($actualDynamicFields as $actualDynamicField) {
                        if ($field -> id == $actualDynamicField -> dynamic_field_detail_id) {
                            $value = $actualDynamicField -> value;
                            return $value;
                        }
                    }
                });
            }
            $headers = $request -> columns;
            $meanArray = [];
            foreach ($headers as $header) {
                if (str_contains($header['data'], '_CUSTOMATTRCODE')) {
                    //Attributes fields
                    $data = explode('_', $header['data']);
                    if (count($data) == 3) {
                        $attributeName = $data[0];
                        $attributeValue = $data[1];
                        $datatables->addColumn($header['name'], function ($row) use ($field, $attributeName, $attributeValue, &$meanArray) {
                            $soItemIds = explode(',',$row -> so_item_ids);
                            $itemAttributes = ErpSoItemAttribute::whereIn('so_item_id', $soItemIds) -> get();
                            foreach ($itemAttributes as $itemAttr) {
                                if ($itemAttr -> attribute_name == $attributeName && $itemAttr -> attribute_value == $attributeValue) {
                                    $row -> mean_sizes += (int) $attributeValue;
                                    $row -> mean_count += 1;
                                    return $itemAttr -> soItem ?-> order_qty;
                                }
                            }
                        });
                    }
                }
                if (str_contains($header['data'], '_CUSTOMSPECCODE')) {
                    $specData = explode('_', $header['data']);
                    if (count($specData) == 2) {
                        $datatables->addColumn($header['name'], function ($row) use ($field, $specData) {
                            $itemId = $row -> item_id;
                            $specValue = ItemSpecification::where('item_id', $itemId) -> whereRaw('LOWER(specification_name) = ?', [$specData[0]]) -> first();
                            if (isset($specValue)) {
                                return $specValue -> value;
                            } else {
                                return '';
                            }
                        });
                    }
                }
                if (str_contains($header['data'],'_CUSTOMONLY')) {
                    $valData = explode('_', $header['data']);
                    if (count($valData) == 2) {
                        $datatables->addColumn($header['name'], function ($row) use ($field, $valData) {
                            if ($valData[0] == 'finalcrd') {
                                $finalCRDHtml = '';
                                $soItemIds = explode(',',$row -> so_item_ids);
                                $invoiceItems = ErpInvoiceItem::whereIn('so_item_id', $soItemIds) -> withWhereHas('header', function ($headerQuery) {
                                    $headerQuery -> whereIn('document_status', [ConstantHelper::APPROVED, 
                                    ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                                }) -> get();
                                foreach ($invoiceItems as $invoiceItem) {
                                    $docData = Carbon::parse($invoiceItem -> header -> document_date)  -> format('d-m-Y');
                                    $docQty = number_format($invoiceItem -> order_qty, 2);
                                    $finalCRDHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$docData</strong> : $docQty  </span>";
                                }
                                return $finalCRDHtml;
                            }
                        });
                        
                    } 
                }
            }
            $datatables->addColumn('mean_size', function ($row) use ($field) {
                if ($row -> mean_sizes && $row -> mean_count) {
                    return number_format($row -> mean_sizes / $row -> mean_count, 2);
                }
            });
            $datatables = $datatables
            ->rawColumns(['item_attributes','delivery_schedule', 'finalcrd_CUSTOMONLY','status'])
            ->make(true);
            return $datatables;
    }

    public function getItemOrgLocationStoreWiseStock(Request $request)
    {
        try {
            $itemId = $request -> item_id ?? null;
            $locationId = $request -> loc_id ?? null;
            $organizationId = $request -> org_id ?? null;
            $subStoreId = $request -> sub_store_id ?? null;
            $selectedAttributes = $request -> item_attributes ?? [];
            $filterItemIds = $request -> filter_item_ids ?? [];
            $item = Item::where('id', $itemId) -> first();
            // $location = ErpStore::where('id', $locationId) -> first();
            if (!$item) {
                return response() -> json([
                    'status' => 'error',
                    'message' => 'Item Not Found',
                ], 422);
            }
            $uomId = $item -> uom_id;
            $processedItems = ItemHelper::generateOrgLocStoreWiseItemStock($item, $selectedAttributes, $uomId, $organizationId, $locationId, $subStoreId, $filterItemIds);
            return view('components.inventory.partials.item_stock_details', [
                'processedItems' => $processedItems
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'exception',
                'message' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
            ], 500);
        }
    }

    function getCurrentItemStock(Request $request)
    {
        try {
            $itemId = $request -> item_id ?? null;
            $locationId = $request -> location_id ?? null;
            $sub_store_id = $request -> sub_store_id ?? null;
            $organization_id = $request -> organization_id ?? null;
            $selectedAttributes = $request -> item_attributes ?? [];
            $item = Item::withDefaultGroupCompanyOrg() -> where('id', $itemId) -> first();
            $location = ErpStore::where('id', $locationId) -> first();
            if (!$item) {
                return response() -> json([
                    'status' => 'error',
                    'message' => 'Item Or Location Not Found',
                ], 422);
            }
            $totalStocks = InventoryHelper::totalInventoryAndStockV1($organization_id, $item -> id, $selectedAttributes, $item -> uom_id, $locationId, $sub_store_id);
            $confirmedStocks = isset($totalStocks['confirmedStocks']) ? $totalStocks['confirmedStocks'] : 0.00;
            $unconfirmedStocks = isset($totalStocks['pendingStocks']) ? $totalStocks['pendingStocks'] : 0.00;
            return response() -> json([
                'status' => 'success',
                'message' => 'Stocks retrieved',
                'data' => array(
                    'confirmed' => number_format($confirmedStocks, 2),
                    'unconfirmed' => number_format($unconfirmedStocks, 2)
                )
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'exception',
                'message' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
            ], 500);
        }
    }

    public function getItemSalePrice(Request $request)
    {
        try {
            $itemId = $request -> item_id ?? 0;
            $uomId = $request -> uom_id ?? 0;
            $attributes = $request -> attributes_data ?? [];
            $currencyId = $request -> currency_id;
            $customerId = $request -> customer_id;
            $itemQty = 100;
            $documentDate = $request -> document_date;
            $type = $request -> price_type;
            if ($type == 'selling') {
                $itemPrice = ItemHelper::getItemSalePrice($itemId, $attributes, $uomId, $currencyId, $documentDate, $customerId, $itemQty);
            } else if ($type == 'cost') {
                $itemPrice = ItemHelper::getItemCostPrice($itemId, $attributes, $uomId, $currencyId, $documentDate, $customerId, $itemQty);
            } else {
                $itemPrice = 0;
            }
            return response() -> json([
                'status' => 'success',
                'data' => $itemPrice
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'exception',
                'message' => $ex -> getMessage()
            ], 500);
        }
    }
    
}
