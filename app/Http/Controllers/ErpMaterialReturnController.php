<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TransactionReportHelper;
use App\Helpers\UserHelper;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\ErpMrDynamicField;
use App\Http\Requests\ErpMaterialReturnRequest;
use App\Models\Address;
use App\Models\AuthUser;
use App\Models\Country;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpInvoiceItem;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpMaterialReturnHeaderHistory;
use App\Models\ErpMiItem;
use App\Models\ErpMrItem;
use App\Models\ErpMrItemAttribute;
use App\Models\ErpMrItemLocation;
use App\Models\ErpMrItemLot;
use App\Models\ErpProductionSlip;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpPwoItem;
use App\Models\ErpRack;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpVendor;
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
use Exception;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use Yajra\DataTables\DataTables;

class ErpMaterialReturnController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME;
        $redirectUrl = route('material.return.index');
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $createRoute = route('material.return.create');
        $typeName = ConstantHelper::MATERIAL_RETURN_SERVICE_NAME;
        $autoCompleteFilters = self::getBasicFilters();
        
        if ($request -> ajax()) {
            try {
            $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
            $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
            //Date Filters
            $dateRange = $request -> date_range ??  null;
            $docs = ErpMaterialReturnHeader::withDefaultGroupCompanyOrg() ->  bookViewAccess($pathUrl) ->  withDraftListingLogic() ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) -> whereIn('store_id',$accessible_locations) ->  when($request -> customer_id, function ($custQuery) use($request) {
                    $custQuery -> where('customer_id', $request -> customer_id);
                }) -> when($request -> book_id, function ($bookQuery) use($request) {
                    $bookQuery -> where('book_id', $request -> book_id);
                }) -> when($request -> document_number, function ($docQuery) use($request) {
                    $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
                }) -> when($request -> from_location_id, function ($docQuery) use($request) {
                    $docQuery -> where('store_id', $request -> from_location_id);
                }) -> when($request -> to_location_id, function ($docQuery) use($request) {
                    $docQuery -> where('to_store_id', $request -> to_location_id);
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
                })  -> orderByDesc('id');
            return DataTables::of($docs) ->addIndexColumn()
            ->editColumn('document_status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = $row -> display_status;
                $editRoute = route('material.return.edit', ['id' => $row -> id]); 
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
        return view('materialReturn.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME],
            'autoCompleteFilters' => $autoCompleteFilters, 'create_button' => $create_button]);
    
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
        $redirectUrl = route('material.return.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME;
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK,ConstantHelper::VENDOR,ConstantHelper::SHOP_FLOOR]);
        $vendors = Vendor::select('id', 'display_name') -> withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) -> get();
        $departments = UserHelper::getDepartments($user -> auth_user_id);
        $users = AuthUser::select('id', 'name') -> where('organization_id', $user -> organization_id) 
        -> where('status', ConstantHelper::ACTIVE) -> get();
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
        $stockTypes = InventoryHelper::getStockType();
        $data = [
            'user' => $user,
            'users' => $users,
            'departments' => $departments['departments'],
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => array(),
            'countries' => $countries,
            'typeName' => $typeName,
            'current_financial_year' => $selectedfyYear,
            'stores' => $stores,
            'vendors' => $vendors,
            'redirect_url' => $redirectUrl,
            'stockTypes' => $stockTypes,
        ];
        return view('materialReturn.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('material.return.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpMaterialReturnHeaderHistory::with(['book']) -> with('items', function ($query) {
                    $query -> with(['item_locations','department','user']) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> where('source_id', $id)->first();
                $ogDoc = ErpMaterialReturnHeader::find($id);
            } else {
                $doc = ErpMaterialReturnHeader::with(['book']) -> with('items', function ($query) {
                    $query -> with(['item_locations','department','user','erpMrItemLot']) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> find($id);
                $ogDoc = $doc;
            }
            $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK,ConstantHelper::VENDOR,ConstantHelper::SHOP_FLOOR]);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias);
            }            
            $revision_number = $doc->revision_number;
            $totalValue = ($doc -> total_item_value - $doc -> total_discount_value) + $doc -> total_tax_value + $doc -> total_expense_value;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, $doc->approval_level, $doc -> created_by ?? 0, $userType['type'], $revision_number);
            $books = Helper::getBookSeriesNew(ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME, ) -> get();
            $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
            $revNo = $doc->revision_number;
            $departments = UserHelper::getDepartments($user -> auth_user_id);
            $users = AuthUser::select('id', 'name') -> where('organization_id', $user -> organization_id) 
            -> where('status', ConstantHelper::ACTIVE) -> get();
        
            if($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $doc->revision_number;
            }
            $selectedfyYear = Helper::getFinancialYear($order->document_date ?? Carbon::now()->format('Y-m-d'));
            $docValue = $doc->total_amount ?? 0;
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $ogDoc->id, $revNo, $docValue, $doc -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
            $typeName = ConstantHelper::MATERIAL_ISSUE_SERVICE_NAME;
            $vendors = Vendor::withDefaultGroupCompanyOrg()->where('id', $doc -> vendor_id) -> get();
            foreach ($doc -> items as $docItem) {
                $docItem -> max_qty_attribute = 9999999;
                if ($docItem -> mi_item_id) {
                    $moItem = ErpMiItem::find($docItem -> mi_item_id);
                    if (isset($moItem)) {
                        $balQty =  $moItem -> mi_balance_qty;
                        $docItem -> max_qty_attribute = $docItem -> qty + $balQty;
                    }
                }
            }
            // $toSubStores = InventoryHelper::getAccesibleSubLocations($doc -> to_store_id, 0, ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES);
            // $fromSubStores = InventoryHelper::getAccesibleSubLocations($doc -> from_store_id, 0, [ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
            $dynamicFieldsUI = $doc -> dynamicfieldsUi();

            $data = [
                'user' => $user,
                'users' => $users,
                'departments' => $departments['departments'],
                'series' => $books,
                'order' => $doc,
                'countries' => $countries,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'stores' => $stores,
                'current_financial_year' => $selectedfyYear,
                'vendors' => $vendors,
                'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($doc -> media_files)) : 10,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'services' => $servicesBooks['services'],
                // 'toSubStores' => $toSubStores,
                // 'fromSubStores' => $fromSubStores,
                'redirect_url' => $redirect_url
            ];
            return view('materialReturn.create_edit', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }
    // make ErpMaterialReturnRequest 
    public function store(ErpMaterialReturnRequest $request)
    {
        try {
            //Reindex
            $request -> item_qty =  array_values($request -> item_qty);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id);
            $request -> item_rate =  array_values($request -> item_rate);
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
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

            if (!$request -> material_return_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpMaterialReturnHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $materialReturn = null;
            $fromStore = ErpStore::find($request -> store_from_id);
            $toStore = ErpStore::find($request -> return_location);
            if ($request -> material_return_id) { //Update
                $materialReturn = ErpMaterialReturnHeader::find($request -> material_return_id);
                $materialReturn -> document_date = $request -> document_date;
                $materialReturn -> reference_number = $request -> reference_no;
                //Store and department keys
                $materialReturn -> store_id = $request -> return_location ?? null;
                $materialReturn -> store_code = $fromStore ?-> store_name ?? null;
                $materialReturn -> to_store_id = $request -> store_from_id ?? null;
                $materialReturn -> to_store_code = $toStore ?-> store_name ?? null;
                $materialReturn -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup

                if(($materialReturn -> document_status == ConstantHelper::APPROVED || $materialReturn -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpMaterialReturnHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpMrItem', 'relation_column' => 'material_return_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpMrItemAttribute', 'relation_column' => 'mr_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpMrItemLocation', 'relation_column' => 'mr_item_id'],
                    ];
                    $a = Helper::documentAmendment($revisionData, $materialReturn->id);
                }
                $keys = ['deletedSiItemIds', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }
                if (count($deletedData['deletedSiItemIds'])) {
                    $miItems = ErpMrItem::whereIn('id',$deletedData['deletedSiItemIds'])->get();
                    # all ted remove item level
                    foreach($miItems as $miItem) {
                        if (isset($miItem -> mi_item_id)) {
                            //Back update in MO ITEM
                            $moItem = ErpMiItem::find($miItem -> mi_item_id);
                            if (isset($moItem)) {
                                $moItem -> mr_qty = $moItem -> mr_qty - $miItem -> qty;
                                if($moItem->mr_qty<= $moItem -> issue_qty){

                                    $moItem -> save();
                                }
                                else{
                                    DB::rollBack();
                                    return response() -> json([
                                        'message' => 'Return Qty Exceeded Issue Qty ',
                                        'error' => ''
                                    ], 422);
                                }
                            }
                        }
                        // if (isset($miItem -> pwo_item_id)) {
                        //     //Back update in PWO ITEM
                        //     $pwoItem = ErpPwoItem::find($miItem -> pwo_item_id);
                        //     if (isset($pwoItem)) {
                        //         $pwoItem -> mr_qty = $pwoItem -> mr_qty - $miItem -> qty;
                        //         $pwoItem -> save();
                        //     }
                        // }
                        // if (isset($miItem -> pi_item_id)) {
                        //     //Back update in PI ITEM
                        //     $piItem = PiItem::find($miItem -> pi_item_id);
                        //     if (isset($piItem)) {
                        //         $piItem -> mr_qty = $piItem -> mr_qty - $miItem -> qty;
                        //         $piItem -> save();
                        //     }
                        // }
                        # all attr remove
                        $miItem->attributes()->delete();
                        $miItem->delete();
                    }
                }
            } else { //Create

                $department = null;
                if ($request->filled('department_id') && $request->issue_type == "Consumption") {
                    $department = Department::find($request->department_id)->first();
                }

                $user = null;
                if ($request->filled('user_id') && $request->issue_type == "Consumption") {
                    $user = AuthUser::find($request->user_id)->first();
                }

                $vendor = null;
                if ($request->filled('vendor_id') && $request->issue_type == "Sub Contracting") {
                    $vendor = Vendor::find($request->vendor_id);
                }
                $materialReturn = ErpMaterialReturnHeader::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'return_type' => $request->issue_type,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request->document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'reference_number' => $request->reference_no,

                    // Conditionally include department data
                    // 'department_id' => $request->filled('department_id') ? $request->department_id : null,
                    // 'department_code' => $department?->name,

                    // Conditionally include user data
                    // 'user_id' => $request->filled('user_id') ? $request->user_id : null,
                    // 'user_name' => $user?->name,

                    // Conditionally include vendor data
                    'vendor_id' =>  $request->filled('vendor_id') ? $vendor?->id : null,
                    'vendor_code' =>  $request->filled('vendor_id') ? $vendor?->vendor_code : null,

                    'store_id' => $request->store_from_id ?? null,
                    'store_code' => $fromStore?->store_code ?? null,
                    'to_store_id' => $request->return_location ?? null,
                    'to_store_code' => $toStore?->store_code ?? null,

                    'from_sub_store_id' => $request->from_sub_store_id ?? null,
                    'from_sub_store_code' => $request->from_sub_store_code ?? null,
                    'to_sub_store_id' => $request->to_sub_store_id ?? null,
                    'to_sub_store_code' => $request->to_sub_store_code ?? null,

                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => $request->final_remarks,

                    // Currency fields
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
                $vendorShippingAddress = ErpAddress::find($request -> vendor_address_id);
                if (isset($vendorShippingAddress)) {
                    $shippingAddress = $materialReturn -> vendor_shipping_address() -> create([
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
                //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpMrDynamicField::class, $materialReturn -> id, $request -> dynamic_field ?? []);
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
                //Initialize item discount to 0
                $itemTotalDiscount = 0;
                $itemTotalValue = 0;
                $totalTax = 0;
                $totalItemValueAfterDiscount = 0;
                $materialReturn -> save();
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
                            $inventoryUomQty = isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0;
                            $requestUomId = isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null;
                            if($requestUomId != $item->uom_id) {
                                $alUom = $item->alternateUOMs()->where('uom_id',$requestUomId)->first();
                                if($alUom) {
                                    $inventoryUomQty= intval(isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0) * $alUom->conversion_to_inventory;
                                }
                            }
                            $uom = Unit::find($request -> uom_id[$itemKey] ?? null);
                            $fromStoreItem = ErpStore::find($request -> store_from_id);
                            $toStoreItem = ErpStore::find( $request ?-> return_location);
                            $fromStation = Station::find($request->from_station_id ?? null);
                            $toStation = Station::find($request->to_station_id ?? null);
                            $department = Department::find($request ?-> department_id[$itemKey] ?? NULL);
                            $miItem = ErpMiItem::find($request ?-> mi_item_id[$itemKey] ?? NULL); 
                            if (isset($miItem)) {
                                $fromSubStore = ErpSubStore::find($miItem -> from_sub_store_id);
                                $toSubStore = ErpSubStore::find($miItem -> to_sub_store_id);
                            }
                            $user = AuthUser::find($request ?-> user_id[$itemKey] ?? NULL);
                            array_push($itemsData, [
                                'material_return_id' => $materialReturn -> id,
                                'item_id' => $item -> id,
                                'mi_item_id' => isset($request -> mi_item_id[$itemKey]) ? $request -> mi_item_id[$itemKey] : null,
                                'item_code' => $item -> item_code,
                                'item_name' => $item -> item_name,
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null,
                                'uom_code' => isset($uom) ? $uom -> name : null,
                                'department_id' => $department ?-> id && $request->issue_type == "Consumption" ?$department ?-> id : null,
                                'department_code' => $department ?-> name && $request->issue_type == "Consumption" ? $department ?-> name : null,
                                'user_id' => $user ?-> id && $request->issue_type == "Consumption" ? $user ?-> id : null,
                                'user_name' => $user ?-> name && $request->issue_type == "Consumption" ?  $user ?-> name : null,
                                'store_id' => $materialReturn->return_type === 'Consumption' ? null : ($fromStoreItem?->id ?? null),
                                'store_code' => $materialReturn->return_type === 'Consumption' ? null : ($fromStoreItem?->store_name ?? null),
                                'to_store_id'   => $materialReturn->return_type === 'Consumption' ? ($fromStoreItem?->id ?? null) : ($toStoreItem?->id ?? null),
                                'to_store_code' => $materialReturn->return_type === 'Consumption' ? ($fromStoreItem?->store_name ?? null) : ($toStoreItem?->store_name ?? null),
                                'from_station_id' => $materialReturn->return_type === 'Consumption' ? null : ($fromStation?->id ?? null),
                                'from_station_code' => $materialReturn->return_type === 'Consumption' ? null : ($fromStation?->name ?? null),
                                'to_station_id'   => $materialReturn->return_type === 'Consumption' ? ($fromStation?->id ?? null) : ($toStation?->id ?? null),
                                'to_station_code' => $materialReturn->return_type === 'Consumption' ? ($fromStation?->name ?? null) : ($toStation?->name ?? null),
                                'from_sub_store_id' => $miItem -> from_sub_store_id ?? null,
                                'from_sub_store_code' => $fromSubStore->name ?? null,
                                'to_sub_store_id' => $miItem -> to_sub_store_id ?? null,
                                'to_sub_store_code' => $toSubStore->name ?? null,
                                'qty' => isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0,
                                'inventory_uom_id' => $item -> uom ?-> id,
                                'inventory_uom_code' => $item -> uom ?-> name,
                                'inventory_uom_qty' => $inventoryUomQty,
                                'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
                                'item_discount_amount' => $itemDiscount,
                                'header_discount_amount' => 0,
                                'item_expense_amount' => 0,
                                'header_expense_amount' => 0,
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
                        //Update or create
                        $itemRowData = [
                            'material_return_id' => $materialReturn -> id,
                            'item_id' => $itemDataValue['item_id'],
                            'mi_item_id' => $itemDataValue['mi_item_id'],
                            'item_code' => $itemDataValue['item_code'],
                            'item_name' => $itemDataValue['item_name'],
                            'hsn_id' => $itemDataValue['hsn_id'],
                            'hsn_code' => $itemDataValue['hsn_code'],
                            'uom_id' => $itemDataValue['uom_id'],
                            'uom_code' => $itemDataValue['uom_code'],
                            'store_id' => $itemDataValue['store_id'],
                            'user_id' => $itemDataValue['user_id'],
                            'user_name' => $itemDataValue['user_name'],
                            'department_id' => $itemDataValue['department_id'],
                            'department_code' => $itemDataValue['department_code'],
                            'to_store_id' => $itemDataValue['to_store_id'],
                            'to_store_code' => $itemDataValue['to_store_code'],
                            'to_station_id' => $itemDataValue['to_station_id'],
                            'to_station_code' => $itemDataValue['to_station_code'],
                            'from_station_id' => $itemDataValue['from_station_id'],
                            'from_station_code' => $itemDataValue['from_station_code'],
                            'from_sub_store_id' => $itemDataValue['from_sub_store_id'],
                            'from_sub_store_code' => $itemDataValue['from_sub_store_code'],
                            'to_sub_store_id' => $itemDataValue['to_sub_store_id'],
                            'to_sub_store_code' => $itemDataValue['to_sub_store_code'],
                            'store_code' => $itemDataValue['store_code'],
                            'qty' => $itemDataValue['qty'],
                            'inventory_uom_id' => $itemDataValue['inventory_uom_id'],
                            'inventory_uom_code' => $itemDataValue['inventory_uom_code'],
                            'inventory_uom_qty' => $itemDataValue['inventory_uom_qty'],
                            'rate' => $itemDataValue['rate'],
                            'item_discount_amount' => $itemDataValue['item_discount_amount'],
                            'header_discount_amount' => $headerDiscount,
                            'item_expense_amount' => $itemExpenseAmount,
                            'header_expense_amount' => $itemHeaderExpenseAmount,
                            'total_item_amount' => ($itemDataValue['qty'] * $itemDataValue['rate']) - ($itemDataValue['item_discount_amount'] + $headerDiscount) + ($itemExpenseAmount + $itemHeaderExpenseAmount) + $itemTax,
                            'tax_amount' => $itemTax,
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        if (isset($request -> mr_item_id[$itemDataKey])) {
                            $oldMrItem = ErpMrItem::find($request -> mr_item_id[$itemDataKey]);
                            $miItem = ErpMrItem::updateOrCreate(['id' => $request -> mr_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $miItem = ErpMrItem::create($itemRowData);
                        }
                        $lot_data = null;
                        //Order Pulling condition 
                        if (isset($request -> mi_item_id[$itemDataKey])) {
                            $issue_item = ErpMiItem::with(['header'])->find($request -> mi_item_id[$itemDataKey]);
                            $lot_data = InventoryHelper::getIssueTransactionLotNumbers('mi', $issue_item->header->id, $issue_item->id,$issue_item->uom_id);

                            //Back update in MO item
                            $moItem = ErpMiItem::find($request -> mi_item_id[$itemDataKey]);
                            if (isset($moItem)) {
                                $moItem -> mr_qty = ($moItem -> mr_qty - (isset($oldMrItem) ? $oldMrItem -> qty : 0)) + $itemDataValue['qty'];
                                if($moItem->mr_qty<= $moItem -> issue_qty){

                                    $moItem -> save();
                                }
                                else{
                                    DB::rollBack();
                                    return response() -> json([
                                        'message' => 'Return Qty Exceeded Issue Qty ',
                                        'error' => ''
                                    ], 422);
                                }
                            }
                        }
                        // if (isset($request -> pwo_item_id[$itemKey])) {
                        //     //Back update in PWO item
                        //     $pwoItem = ErpPwoItem::find($request -> pwo_item_id[$itemKey]);
                        //     if (isset($pwoItem)) {
                        //         $pwoItem -> mi_qty = ($pwoItem -> mi_qty - (isset($oldMrItem) ? $oldMrItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                        //         $pwoItem -> save();
                        //     }
                        // }
                        // if (isset($request -> pi_item_id[$itemKey])) {
                        //     //Back update in PI item
                        //     $piItem = PiItem::find($request -> pi_item_id[$itemKey]);
                        //     if (isset($piItem)) {
                        //         $piItem -> mi_qty = ($piItem -> mi_qty - (isset($oldMrItem) ? $oldMrItem -> issue_qty : 0)) + $itemDataValue['issue_qty'];
                        //         $piItem -> save();
                        //     }
                        // }
                        //Item Lot Data
                        if (isset($request -> item_lots[$itemDataKey])) {
                            $lotArray = json_decode($request -> item_lots[$itemDataKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($lotArray)) {
                                foreach ($lotArray as $key => $lot) {
                                    $so_lot = null ;
                                    if(isset($lot_data[$key]['so_no'])){
                                        $lot['so_no'] = $lot_data[$key]['so_no'] ?? null;
                                        $so_lot = $lot['so_no'] ?? null;
                                    }
                                    $miItemLot = ErpMrItemLot::updateOrCreate(
                                        [
                                            'mr_item_id' => $miItem -> id,
                                            'lot_number' => $lot['lot_number'],
                                        ],
                                        [
                                            'so_lot_number' => $so_lot,
                                            'lot_qty' => $lot['lot_qty'],
                                            'total_lot_qty' => $lot['total_lot_qty'],
                                            'inventory_uom_qty' => ItemHelper::convertToBaseUom($miItem -> item_id, $miItem -> uom_id, (float)$lot['lot_qty']),
                                            'original_receipt_date' => $lot['original_receipt_date'],
                                        ]
                                    );
                                }
                            } else {
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid lot data',
                                    'error' => ''
                                ], 422);
                            }
                        }
                        else
                        {
                            $lot_number = date('Y/M/d', strtotime($materialReturn->document_date)) . '/' . $materialReturn->book_code . '/' . $materialReturn->document_number;
                            ErpMrItemLot::updateOrCreate(
                                [
                                    'mr_item_id' => $miItem->id,
                                    'lot_number' => strtoupper($lot_number),
                                ],
                                [
                                    'so_lot_number' => null,
                                    'lot_qty' => $miItem->qty,
                                    'total_lot_qty' => $miItem->qty,
                                    'inventory_uom_qty' => ItemHelper::convertToBaseUom($miItem -> item_id, $miItem -> uom_id, $miItem->qty),
                                    'original_receipt_date' => $miItem->header->document_date,
                                ]
                            );
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
                                    $itemAttribute = ErpMrItemAttribute::updateOrCreate(
                                        [
                                            'material_return_id' => $materialReturn -> id,
                                            'mr_item_id' => $miItem -> id,
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
                        // Item Locations (only in case of DN and Inv CUM DN)
                        if (isset($request -> return_location) || (isset($request-> store_from_id) && $request ->issue_type == "Consumption")) {

                            //To Location
                            ErpMrItemLocation::where('material_return_id', $materialReturn -> id) -> where('mr_item_id', $miItem -> id) ->where('type','to') -> delete();
                            $location=ErpMrItemLocation::create([
                                'material_return_id' => $materialReturn -> id,
                                'mr_item_id' => $miItem -> id,
                                'item_id' => $miItem -> item_id,
                                'item_code' => $miItem -> item_code,
                                'store_id' =>  $miItem -> to_store_id,
                                'store_code' => $miItem -> toErpStore -> store_name,
                                'sub_store_id' =>  $miItem -> to_sub_store_id,
                                'sub_store_code' => $miItem -> toErpSubStore -> name,
                                'station_id' =>  $miItem -> to_station_id,
                                'station_code' => $miItem -> toErpStation -> name,
                                'rack_id' => null,
                                'rack_code' =>null,
                                'shelf_id' => null,
                                'shelf_code' => null,
                                'bin_id' => null,
                                'bin_code' => null,
                                'type' => 'to',
                                'quantity' => $miItem-> qty,
                                'inventory_uom_qty' => ItemHelper::convertToBaseUom($miItem -> item_id, $miItem -> uom_id, (float)$miItem -> qty)
                            ]);
                            // if($request->issue_type == 'Location Transfer' || $request->issue_type == 'Sub Contracting'){
                            //     $fromLocation = ErpStore::find($request -> store_from_id);
                            //     ErpMrItemLocation::where('material_return_id', $materialReturn -> id) -> where('mr_item_id', $miItem -> id) ->where('type','from') -> delete();
                            //     ErpMrItemLocation::create([
                            //         'material_return_id' => $materialReturn -> id,
                            //         'mr_item_id' => $miItem -> id,
                            //         'item_id' => $miItem -> item_id,
                            //         'item_code' => $miItem -> item_code,
                            //         'store_id' => $fromLocation -> id,
                            //         'store_code' => $fromLocation -> store_name,
                            //         'rack_id' => null,
                            //         'rack_code' =>null,
                            //         'shelf_id' => null,
                            //         'shelf_code' => null,
                            //         'bin_id' => null,
                            //         'bin_code' => null,
                            //         'type' => 'from',
                            //         'quantity' => $miItem-> qty,
                            //         'inventory_uom_qty' => ItemHelper::convertToBaseUom($miItem -> item_id, $miItem -> uom_id, (float)$miItem -> qty)
                            //     ]);
                                
                            // }
                            // if (isset($request -> item_locations[$itemDataKey])) {
                            //     $toLocationsArray = json_decode($request -> item_locations[$itemDataKey], true);
                            //     if (json_last_error() === JSON_ERROR_NONE && is_array($toLocationsArray)) {
                            //         foreach ($toLocationsArray as $toLoc) {
                            //             ErpMrItemLocation::create([
                            //                 'material_issue_id' => $materialReturn -> id,
                            //                 'mi_item_id' => $miItem -> id,
                            //                 'item_id' => $miItem -> item_id,
                            //                 'item_code' => $miItem -> item_code,
                            //                 'store_id' => $toLoc['store_id'],
                            //                 'store_code' => $toLoc['store_code'],
                            //                 'rack_id' => $toLoc['rack_id'],
                            //                 'rack_code' => $toLoc['rack_code'],
                            //                 'shelf_id' => $toLoc['shelf_id'],
                            //                 'shelf_code' => $toLoc['shelf_code'],
                            //                 'bin_id' => $toLoc['bin_id'],
                            //                 'bin_code' => $toLoc['bin_code'],
                            //                 'quantity' => $toLoc['qty'],
                            //                 'inventory_uom_qty' => ItemHelper::convertToBaseUom($miItem -> item_id, $miItem -> uom_id, (float)$toLoc['qty'])
                            //             ]);
                            //         }  
                            //     }
                            // }
                        }
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);
                }
                ErpMrItemAttribute::where([
                    'material_return_id' => $materialReturn -> id,
                    'mr_item_id' => $miItem -> id,
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
                $materialReturn -> total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
                $materialReturn -> total_item_value = $itemTotalValue;
                $materialReturn -> total_tax_value = $totalTax;
                $materialReturn -> total_expense_value = $totalExpenseAmount;
                $materialReturn -> total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;
                //Approval check
                //Approval check
                if ($request -> material_return_id) { //Update condition
                    $bookId = $materialReturn->book_id; 
                    $docId = $materialReturn->id;
                    $amendRemarks = $request->amend_remarks ?? null;
                    $remarks = $materialReturn->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachment');
                    $currentLevel = $materialReturn->approval_level;
                    $modelName = get_class($materialReturn);
                    $actionType = $request -> action_type ?? "";
                    if(($materialReturn -> document_status == ConstantHelper::APPROVED || $materialReturn -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $materialReturn->revision_number + 1;
                        $actionType = 'amendment';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                        $materialReturn->revision_number = $revisionNumber;
                        $materialReturn->approval_level = 1;
                        $materialReturn->revision_date = now();
                        $amendAfterStatus = $materialReturn->document_status;
                        $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                        if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                            $totalValue = $materialReturn->grand_total_amount ?? 0;
                            $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        } else {
                            $actionType = 'approve';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }
                        if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }
                        $materialReturn->document_status = $amendAfterStatus;
                        $materialReturn->save();

                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $materialReturn->revision_number ?? 0;
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                            $totalValue = $materialReturn->grand_total_amount ?? 0;
                            $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                            $materialReturn->document_status = $document_status;
                        } else {
                            $materialReturn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { //Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $materialReturn->book_id;
                        $docId = $materialReturn->id;
                        $remarks = $materialReturn->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $materialReturn->approval_level;
                        $revisionNumber = $materialReturn->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($materialReturn);
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    if ($request->document_status == 'submitted') {
                        $totalValue = $materialReturn->total_amount ?? 0;
                        $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        $materialReturn->document_status = $document_status;
                    } else {
                        $materialReturn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                    $materialReturn -> save();
                }
                $materialReturn -> save();
                //Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $materialReturn->uploadDocuments($singleFile, 'material_return', false);
                    }
                }
                $status = self::maintainStockLedger($materialReturn);
                if (!$status) {     
                    DB::rollBack();
                    return response() -> json([
                        'message' => 'Stock not available'
                    ], 422);
                }
                DB::commit();
                $module = ConstantHelper::MATERIAL_RETURN_SERVICE_NAME;
                return response() -> json([
                    'message' => $module .  " created successfully",
                    'redirect_url' => route('material.return.index')
                ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex -> getLine() . ' in ' . $ex -> getFile(),
            ], 500);
        }
    }

    private static function maintainStockLedger(ErpMaterialReturnHeader $materialReturn)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $materialReturn->items->pluck('id')->toArray();
        if ($materialReturn -> return_type == "Location Transfer" || $materialReturn -> return_type == 'Sub Contracting') { //Only in case of location transfer or sub contracting
            $issueRecords = InventoryHelper::settlementOfInventoryAndStock($materialReturn->id, $detailIds, ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME, $materialReturn->document_status, 'issue');
        }
        InventoryHelper::settlementOfInventoryAndStock($materialReturn->id, $detailIds, ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME, $materialReturn->document_status, 'receipt');
        if(isset($issueRecords) && !empty($issueRecords['data'])){
            ErpMrItemLocation::where('material_return_id', $materialReturn->id)
                ->whereIn('mr_item_id', $detailIds)
                ->where('type', 'from')
                ->delete();

            foreach($issueRecords['data'] as $key => $val){

                $mrItem = ErpMrItem::where('id', @$val->issuedBy->document_detail_id)->first();
                
                ErpMrItemLocation::create([
                    'material_return_id' => $materialReturn -> id,
                    'mr_item_id' => @$val->issuedBy->document_detail_id,
                    'item_id' => $val -> issuedBy -> item_id,
                    'item_code' => $val -> issuedBy -> item_code,
                    'store_id' => $val -> issuedBy -> store_id,
                    'store_code' => $val -> issuedBy -> store,
                    'rack_id' => $val -> issuedBy -> rack_id,
                    'rack_code' => $val -> issuedBy -> rack,
                    'shelf_id' => $val -> issuedBy -> shelf_id,
                    'shelf_code' => $val -> issuedBy -> shelf,
                    'bin_id' => $val -> issuedBy -> bin_id,
                    'bin_code' => $val -> issuedBy -> bin,
                    'quantity' => ItemHelper::convertToAltUom($val -> issuedBy -> item_id, $mrItem ?-> uom_id ?? $val->issuedBy?->inventory_uom_id, $val -> issuedBy -> issue_qty),
                    'type' => "from",
                    'inventory_uom_qty' => $val -> issuedBy -> issue_qty
                ]);
            }
            $stockLedgers = StockLedger::where('book_type',ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS)
                ->where('document_header_id',$materialReturn->id)
                ->where('organization_id',$materialReturn->organization_id)
                ->selectRaw('document_detail_id,sum(org_currency_cost) as cost')
                ->groupBy('document_detail_id')
                ->get();

            foreach($stockLedgers as $stockLedger) {
                $miItem = ErpMrItem::find($stockLedger->document_detail_id);
                $miItem->rate = floatval($stockLedger->cost) / floatval($miItem->qty);
                $miItem->save();
            }
            return true;
        }
        else {
            if($materialReturn->return_type == 'Consumption'){
                return true;
            }
            else{
                return false;
            }
        }
        
    }

    public function revokeMaterialReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpMaterialReturnHeader::find($request -> id);
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
    public function getVendorAddresses(Request $request)
    {
        try {
            $vendor = Vendor::find($request -> vendor_id);
            $addresses = $vendor ?-> shipping_addresses;
            return response() -> json([
                'data' => $addresses,
                'status' => 'success'
            ]);
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
        //Function to get all items of pwo
    public function getMiItemsForPulling(Request $request)
    {
        try {
            $selectedIds = $request -> selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
            if ($request -> doc_type === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
                $referedHeaderId = ErpMaterialIssueHeader::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
                $order = ErpMiItem::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    })->when($request->store_id_from, function ($q) use ($request) {
                        $q->where(function ($subQ) use ($request) {
                            $subQ->where(function ($innerQ) use ($request) {
                                $innerQ->where('from_store_id', $request->store_id_from)
                                       ->whereNot('issue_type', 'Consumption');
                            })->orWhere('from_store_id', $request->store_id_from)
                            ->where('issue_type','Consumption');
                        });                    
                    })-> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
                    -> when($request -> book_id, function ($bookQuery) use($request) {
                        $bookQuery -> where('book_id', $request -> book_id);
                    }) -> when($request -> document_id, function ($docQuery) use($request) {
                        $docQuery -> where('id', $request -> document_id);
                    }) -> when($request -> location_id, function ($docQuery) use($request) {
                        $docQuery -> where('from_store_id', $request -> location_id);
                    });
                }) -> when($request -> department_id, function ($departmentQuery) use($request) {
                    $departmentQuery -> where('department_id', $request -> department_id);
                })-> when($request -> requester_id, function ($requesterQuery) use($request) {
                    $requesterQuery -> where('user_id', $request -> requester_id);
                }) -> with(['attributes','department','user']) -> with('uom') -> with('header') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                }) -> whereColumn('issue_qty', ">", 'mr_qty');
            }
                // else if ($request -> doc_type === ConstantHelper::PWO_SERVICE_ALIAS) {
            //     $referedHeaderId = ErpProductionSlip::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
            //     $order = ErpPwoItem::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId) {
            //         $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
            //             $refQuery -> where('id', $referedHeaderId);
            //         })-> when($request -> store_id, function ($storeQuery) use($request) {
            //             $storeQuery -> where('location_id', $request -> store_id);
            //         })-> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
            //         -> when($request -> book_id, function ($bookQuery) use($request) {
            //             $bookQuery -> where('book_id', $request -> book_id);
            //         }) -> when($request -> document_id, function ($docQuery) use($request) {
            //             $docQuery -> where('id', $request -> document_id);
            //         });
            //     }) -> with('attributes') -> with('uom') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
            //         $refQuery -> whereNotIn('id', $selectedIds);
            //     }) -> whereColumn('qty', ">", 'mr_qty');
            // }  else if ($request -> doc_type === ConstantHelper::PI_SERVICE_ALIAS || $request -> doc_type === "pi") {
            //     $referedHeaderId = PurchaseIndent::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
            //     $order = PiItem::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId) {
            //         $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
            //             $refQuery -> where('id', $referedHeaderId);
            //         })
            //         // -> when($request -> store_id, function ($storeQuery) use($request) {
            //         //     $storeQuery -> where('location_id', $request -> store_id);
            //         // })
            //         -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
            //         -> when($request -> book_id, function ($bookQuery) use($request) {
            //             $bookQuery -> where('book_id', $request -> book_id);
            //         }) -> when($request -> document_id, function ($docQuery) use($request) {
            //             $docQuery -> where('id', $request -> document_id);
            //         });
            //     }) -> with('attributes') -> with('uom') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
            //         $refQuery -> whereNotIn('id', $selectedIds);
            //     }) -> whereColumn(DB::raw('indent_qty - order_qty'), '>', 'mi_qty');
            // }
            else {
                $order = null;
            }
            if ($request -> item_id && isset($order)) {
                $order = $order -> where( function($itemquery) use($request){
                    $itemquery -> where('item_code','like', "%" . $request -> item_id . "%" )-> orWhere('item_name','like', "%" . $request -> item_id . "%" );
            
                });
            }
            $order = isset($order) ? $order -> get() : new Collection();
            foreach ($order as $currentOrder) {
                $currentOrder -> store_location_code = $currentOrder -> header -> store_location ?-> store_name;
                $currentOrder -> avl_stock = $currentOrder -> getAvlStock($currentOrder -> to_store_id);
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
            $headers = ErpMaterialIssueHeader::with(['items' => function ($mappingQuery) use($request) {
                $mappingQuery ->with(['department','user']) -> whereIn('id', $request -> items_id) -> with(['item' => function ($itemQuery) {
                    $itemQuery -> with(['specifications' ,'alternateUoms.uom' ,'uom', 'hsn']);
                }]);
            }])->where('id', $request->order_id)->get();
            foreach ($headers as &$header) {
                foreach ($header -> items as &$item) {
                    $item -> item_attributes_array = $item -> item_attributes_array();
                    $item -> avl_stock = $item -> getAvlStock($request -> to_store_id);
                    $lotdata = InventoryHelper::getIssueTransactionLotNumbers('mi', $header->id, $item->id,$item->uom_id);
                    $item->lotdata =$lotdata;
                }
                if($header->issue_type == 'Location Transfer'){
                    $header->extra_field = $header->from_store;
                    $header->extra_header = "Return To Address";
                }
                else if($header -> issue_type == 'Sub Contracting'){
                    $header->extra_field = $header->vendor->display_name;
                    $header->extra_header = "Vendor Name";
                }
                else if ($header->issue_type == 'Consumption') {
                    if ($header->requester_type == 'Department') {
                        $header->department_code = Department::where('id', $header->department_id)->value('name');
                    }
                    else if ($header->requester_type == 'User') {
                        $user = AuthUser::find($header->user_id);
                        $header->user_name = $user?->name ?? null; // Safe access with null coalescing
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
            $mx = ErpMaterialReturnHeader::with(
                [
                    'store',
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
            $shippingAddress = $mx?->items?->first()?->toErpStore?->address;
            $billingAddress = $mx?->items?->first()->erpStore?->address;

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
                'approvedBy' => $approvedBy,
            ];
            $pdf = PDF::loadView(

                // return view(
                'pdf.material_return',
                $data_array
            );

            return $pdf->stream('Material_Return.pdf');
        }
    public function materialReturnReport(Request $request)
    {
        $pathUrl = route('material.return.index');
        $orderType = [ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME];
        $materialReturn = ErpMaterialReturnHeader::with('items')-> withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> orderByDesc('id');
        //Customer Filter
        $materialReturn = $materialReturn -> when($request -> vendor_id, function ($custQuery) use($request) {
            $custQuery -> where('vendor_id', $request -> vendor_id);
        });
        //Book Filter
        $materialReturn = $materialReturn -> when($request -> book_id, function ($bookQuery) use($request) {
            $bookQuery -> where('book_id', $request -> book_id);
        });
        //Document Id Filter
        $materialReturn = $materialReturn -> when($request -> document_number, function ($docQuery) use($request) {
            $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
        });
        //From Location Filter
        $materialReturn = $materialReturn -> when($request -> type, function ($docQuery) use($request) {
            $docQuery -> where('return_type', 'LIKE', "%".$request -> type . "%");
        });
        //From Location Filter
        $materialReturn = $materialReturn -> when($request -> location_id, function ($docQuery) use($request) {
            $docQuery -> where('store_id', $request -> location_id);
        });
        //To Location Filter
        $materialReturn = $materialReturn -> when($request -> to_location_id, function ($docQuery) use($request) {
            $docQuery -> where('to_store_id', $request -> to_location_id);
        });
        //Company Filter
        $materialReturn = $materialReturn -> when($request -> company_id, function ($docQuery) use($request) {
            $docQuery -> where('store_id', $request -> company_id);
        });
        //Organization Filter
        $materialReturn = $materialReturn -> when($request -> organization_id, function ($docQuery) use($request) {
            $docQuery -> where('organization_id', $request -> organization_id);
        });
        //Document Status Filter
        $materialReturn = $materialReturn -> when($request -> doc_status, function ($docStatusQuery) use($request) {
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
        $materialReturn = $materialReturn -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
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
        $materialReturn = $materialReturn -> when($request -> item_id, function ($itemQuery) use($request) {
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
        //issue No Filter
        $materialReturn = $materialReturn -> when($request -> mi_no, function ($issueNoQuery) use($request) {
            $issueNoQuery -> whereHas('items', function ($mrItemQuery) use($request) {
                $mrItemQuery -> whereHas('issue_item', function ($issueQuery) use($request) {
                    $issueQuery -> whereHas('header', function ($headerQuery) use($request) {
                        $headerQuery -> where('document_number', 'LIKE', '%' . $request -> mi_no . '%')
                        -> orWhere('book_code', 'LIKE', '%' . $request -> mi_no . '%')
                        ;
                    });
                });
            });
        });
        //mi Date Range Filter
        $materialReturn = $materialReturn -> when($request -> mi_dt, function ($issueDtQuery) use($request) {
            $issueDtQuery -> whereHas('items', function ($mrItemQuery) use($request) {
                $mrItemQuery -> whereHas('issue_item', function ($issueQuery) use($request) {
                    $issueQuery -> whereHas('header', function ($headerQuery) use($request) {
                    if (count($request -> mi_dt) == 2) {
                            $fromDate = Carbon::parse(trim($request -> mi_dt[0])) -> format('Y-m-d');
                            $toDate = Carbon::parse(trim($request -> mi_dt[1])) -> format('Y-m-d');
                            $headerQuery -> whereDate('document_date', ">=" , $fromDate) -> where('document_date', '<=', $toDate);
                    }
                    else{
                            $fromDate = Carbon::parse(trim($request -> mi_dt[0])) -> format('Y-m-d');
                            $headerQuery -> whereDate('document_date', $fromDate);
                        }
                    }); 
                });
            });
        });
        $materialReturn = $materialReturn -> get();
        $processedSalesOrder = collect([]);
        foreach ($materialReturn as $materialReturn) {
            foreach ($materialReturn -> items as $mrItem) {
                $reportRow = new stdClass();
                //Header Details
                $header = $mrItem -> header;
                $reportRow -> id = $header -> id;
                $reportRow -> book_name = $header -> book_code;
                $reportRow -> document_number = $header -> document_number;
                $reportRow -> document_date = $header -> document_date;
                $reportRow -> return_type = $header -> return_type;
                $reportRow -> store_name = $header -> erpStore ?-> store_name;
                $reportRow -> vendor_name = $header -> vendor ?-> company_name ?? " ";
                $reportRow -> customer_currency = $header -> org_currency_code ?? $header ?-> vendor ?-> currency ?-> short_name ?: $header ?-> vendor ?-> name ;
                $reportRow -> payment_terms_name = $header -> payment_term_code;
                $reportRow -> from_store_name = $header -> store ?-> store_name;
                $reportRow -> to_store_name = $header -> toErpStore ?-> store_name;
                $reportRow -> from_sub_store_name = $mrItem ?-> from_sub_store_code;
                $reportRow -> to_sub_store_name = $mrItem ?-> to_sub_store_code;
                $reportRow -> requester = $header -> return_type == 'Consumption' ? $mrItem -> requester_name() : " ";
                //Item Details
                $reportRow -> item_name = $mrItem -> item_name;
                $reportRow -> item_code = $mrItem -> item_code;
                $reportRow -> hsn_code = $mrItem -> hsn ?-> code;
                $reportRow -> uom_name = $mrItem -> uom ?-> name;
                //Amount Details
                $reportRow -> qty = number_format($mrItem -> qty, 2);
                $reportRow -> mi_qty = number_format($mrItem -> issue_item ?-> issue_qty ?? 0.00, 2);
                $reportRow -> mi_date = $mrItem ?-> issue_item ?-> header ?-> document_date ?? " ";
                $reportRow -> mi_no = $mrItem -> issue_item ?-> header ? $mrItem -> issue_item ?-> header ?-> book_code."-".$mrItem -> issue_item ?-> header ?-> document_number : " ";
                $reportRow -> rate = number_format($mrItem -> rate, 2);
                $reportRow -> total_discount_amount = number_format($mrItem -> header_discount_amount + $mrItem -> item_discount_amount, 2);
                $reportRow -> tax_amount = number_format($mrItem -> tax_amount, 2);
                $reportRow -> taxable_amount = number_format($mrItem -> total_item_amount - $mrItem -> tax_amount, 2);
                $reportRow -> total_item_amount = number_format($mrItem -> total_item_amount, 2);
                //Delivery Schedule UI
                // $deliveryHtml = '';
                // if (count($mrItem -> item_deliveries) > 0) {
                //     foreach ($mrItem -> item_deliveries as $itemDelivery) {
                //         $deliveryDate = Carbon::parse($itemDelivery -> delivery_date) -> format('d-m-Y');
                //         $deliveryQty = number_format($itemDelivery -> qty, 2);
                //         $deliveryHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$deliveryDate</strong> : $deliveryQty</span>";
                //     }
                // } else {
                //     $parsedDeliveryDate = Carbon::parse($mrItem -> delivery_date) -> format('d-m-Y');
                //     $deliveryHtml .= "$parsedDeliveryDate";
                // }
                // $reportRow -> delivery_schedule = $deliveryHtml;
                //Attributes UI
                $attributesUi = '';
                if (count($mrItem -> item_attributes) > 0) {
                    foreach ($mrItem -> item_attributes as $soAttribute) {
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
            $editRoute = route('material.return.edit', ['id' => $row->id]);
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
