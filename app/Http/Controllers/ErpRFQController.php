<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TransactionReport\rfqReportHelper;
use App\Helpers\UserHelper;
use App\Jobs\SendEmailJob;
use App\Models\Address;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Country;
use App\Models\ErpRfqDynamicField;
use App\Models\ErpRfqHeader;
use App\Models\ErpRfqHeaderHistory;
use App\Models\ErpRfqItem;
use App\Models\ErpRfqItemAttribute;
use App\Models\ErpRfqPiMapping;
use App\Models\ErpRfqPiMappingDetail;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\Organization;
use App\Models\PiItem;
use App\Models\PurchaseIndent;
use App\Models\Station;
use App\Models\Unit;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Storage;
use Yajra\DataTables\DataTables;

class ErpRFQController extends Controller
{
    //
    public function index(Request $request)
    {
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());

        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::RFQ_SERVICE_ALIAS;
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $redirectUrl = route('rfq.index');
        $createRoute = route('rfq.create');
        $typeName = "Request For Quotation";
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
                $docs = ErpRfqHeader::withDefaultGroupCompanyOrg() -> whereIn('store_id',$accessible_locations) -> bookViewAccess($pathUrl) ->  withDraftListingLogic()->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) 
                -> when($request -> vendor_id, function ($custQuery) use($request) {
                    $custQuery -> whereIn('suppliers', $request -> vendor_id);
                }) -> when($request -> book_id, function ($bookQuery) use($request) {
                    $bookQuery -> where('book_id', $request -> book_id);
                }) -> when($request -> document_number, function ($docQuery) use($request) {
                    $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
                })-> when($request -> location_id, function ($docQuery) use($request) {
                    $docQuery -> where('store_id', $request -> location_id);
                })-> when($request -> company_id, function ($docQuery) use($request) {
                    $docQuery -> where('store_id', $request -> company_id);
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
                    if(isset($row->selected_pq))
                    {
                        $row->document_status = ConstantHelper::CLOSED;
                    }
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                    $displayStatus = $row -> display_status;
                    $editRoute = route('rfq.edit', ['id' => $row -> id]); 
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
                    return $row->book_code ? $row->book_code : ' ';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? ' ';
                })
                ->addColumn('store',function($row){
                    return $row?->store?->store_name??" ";
                })
                ->addColumn('vendor', function($row) {
                    $vendors = $row?->vendors();
                    if (!$vendors || $vendors->isEmpty()) {
                        return ' ';
                    }
                    $badges = ' ';
                    $count = 0;
                    foreach ($vendors as $vendor) {
                        if ($count < 2) {
                            $badges .= "<span class='badge rounded-pill badge-light-primary me-25'>{$vendor->company_name}</span>";
                        }
                        $count++;
                    }
                    if ($count > 2) {
                        $extra = $count - 2;
                        $badges .= "<span class='badge rounded-pill badge-light-secondary'>+{$extra} more</span>";
                    }
                    return $badges;
                })
                ->addColumn('due_date', function ($row) {
                    return $row->getFormattedDate('due_date') ?? ' ';
                })
                ->addColumn('contact_person', function ($row) {
                    return $row->contact_name ?? ' ';
                })
                ->addColumn('email', function ($row) {
                    return $row->contact_email ?? ' ';
                })
                ->addColumn('phone', function ($row) {
                    return $row->contact_phone ?? ' ';
                })
                ->addColumn('quoted', function ($row) {
                return (string)$row->pqs->count() ?? ' ';
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('items_count', function ($row) {
                    return $row->items->count();
                })
                ->rawColumns(['document_status','vendor'])
                ->make(true);
            }
            catch (Exception $ex) {
                return response() -> json([
                    'message' => $ex -> getMessage()
                ]);
            }
        }
        return view('rfq.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl,'create_route' => $createRoute, 'create_button' => $create_button, 'filterArray' => rfqReportHelper::RFQ_FILTERS,
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
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
        $redirectUrl = route('rfq.index');
        $firstService = $servicesBooks['services'][0];
        // dd($firstService,$servicesBooks);
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::RFQ_SERVICE_ALIAS;
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
        $suppliers = Vendor::select('id', 'company_name') -> withDefaultGroupCompanyOrg() 
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
            'suppliers' => $suppliers,
            'stations' => $stations,
            'redirect_url' => $redirectUrl,
            'current_financial_year' => $selectedfyYear,
        ];
        return view('rfq.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $currentfyYear = Helper::getCurrentFinancialYear();
            $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('rfq.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpRfqHeaderHistory::with(['book'])
                ->first();
        
            $ogDoc = ErpRfqHeader::find($id);
            } else {
                $doc = ErpRfqHeader::with(['book'])
                ->find($id);
                $ogDoc = $doc;
            }
            $items = self::pullItems($doc);
            $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias);
            }            
            $totalValue = 0;
            $suppliers = Vendor::select('id', 'company_name','email') -> withDefaultGroupCompanyOrg() 
            -> where('status', ConstantHelper::ACTIVE) -> get();
            
            $revision_number = $doc->revision_number;
            $selectedfyYear = Helper::getFinancialYear($doc->document_date ?? Carbon::now()->format('Y-m-d'));
            $doc -> total_tax_value + $doc -> total_expense_value;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, 
            $doc->approval_level, $doc -> created_by ?? 0, $userType['type'], $revision_number);
            $books = Helper::getBookSeriesNew(ConstantHelper::RFQ_SERVICE_ALIAS, ) -> get();
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
            $typeName = "Request For Quotation";
            $stations = Station::withDefaultGroupCompanyOrg()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
            foreach ($doc -> items as $docItem) {
                $docItem -> max_qty_attribute = 9999999;
                // if ($docItem -> mo_item_id) {
                //     $moItem = MoItem::find($docItem -> mo_item_id);
                //     if (isset($moItem)) {
                //         $avlStock = $moItem -> getAvlStock($doc -> from_store_id);
                //         $balQty = min($avlStock, $moItem -> mi_balance_qty);
                //         $docItem -> max_qty_attribute = $docItem -> issue_qty + $balQty;
                //     }
                // }
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
                'suppliers' => $suppliers,
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
            return view('rfq.create_edit', $data);  
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
            // if (!$request->filled('supplier_ids') || empty($request->supplier_ids)) {
            //     // Handle missing or empty supplier_ids}
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Select Suppliers.',
            //     ], 400);
            // }
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
            $isUpdate = $request->rfq_header_id ? true : false;
    
            if (!$isUpdate) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ?? $request->document_no;
                $regeneratedDocExist = ErpRfqHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                if ($regeneratedDocExist) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
    
            $store = ErpStore::find($request->store_id);
            $vendors = Vendor::whereIn('id',$request->supplier_ids ?? [])->get()->toArray();
    
            if ($isUpdate) {
                $rfq = ErpRfqHeader::find($request->rfq_header_id);
                $rfq -> document_date = $request -> document_date;
                $rfq -> instructions = $request -> instructions;
                $rfq -> suppliers = $request -> suppliers;
                //Store and department keys
                $rfq -> store_id = $request -> store_id ?? null;
                $rfq -> remark = $request -> final_remarks;
                $actionType = $request->action_type ?? '';
    
                if (($rfq->document_status == ConstantHelper::APPROVED || $rfq->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpRfqHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpRfqItem', 'relation_column' => 'rfq_header_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpRfqItemAttribute', 'relation_column' => 'rfq_item_id'],
                    ];
                    Helper::documentAmendment($revisionData, $rfq->id);
                }
    
                $rfq->fill([
                    'document_date' => $request->document_date,
                    'store_id' => $request->store_id,
                    'remark' => $request->final_remarks,
                ])->save();
    
                $deletedData = [
                    'deletedSiItemIds' => json_decode($request->input('deletedSiItemIds', '[]'), true),
                    'deletedAttachmentIds' => json_decode($request->input('deletedAttachmentIds', '[]'), true)
                ];
    
                foreach ($deletedData['deletedSiItemIds'] as $deletedId) {
                    $rfqItem = ErpRfqItem::find($deletedId);
                    $rfqItem?->attributes()->delete();
                    $del_item_qty = $rfqItem->required_qty; 
                    $rfqItemId = $rfqItem->id;
                    $piItemId = $rfqItem->pi_item_ids;
                    if($rfqItemId && $piItemId)
                    {
                        ErpRfqPiMappingDetail::where('rfq_item_id',$rfqItemId)->where('pi_item_id',$piItemId)->delete();
                        ErpRfqPiMapping::whereIn('pi_item_id',$piItemId)->decrement('rfq_qty',$del_item_qty);
                    }
                    $rfqItem?->delete(); 
                }
            } else {
                $rfq = ErpRfqHeader::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'store_id' => $request->store_id,
                    // 'sub_store_id' => $request->sub_store_id,
                    'store_code' => $store?->store_name,
                    // 'sub_store_code' => $sub_store?->name,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_number' => $document_number,
                    'document_date' => $request->document_date,
                    'due_date' => $request->due_date,
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
                    'total_item_count' => $request->item_id ? count($request->item_id) : 0,
                    'instructions' => $request->instructions ? $request->instructions : 0,
                    'contact_name' => $request->contact_name ? $request->contact_name : 0,
                    'contact_email' => $request->email ? $request->email : 0,
                    'contact_phone' => $request->phone ? $request->phone : 0,
                    'suppliers' => $request->supplier_ids ? json_encode($request->supplier_ids) : null,
                    'remark' => $request->final_remarks,
                ]);
                $orgLocationAddress = ErpStore::with('address') -> find($request -> store_id);
                if (!isset($orgLocationAddress) || !isset($orgLocationAddress -> address)) {
                    DB::rollBack();
                    return response() -> json([
                        'message' => 'Location Address not assigned',
                        'error' => ''
                    ], 422);
                }
                $locationAddress = $rfq -> location_address_details() -> create([
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
            if ($request->item_id && count($request->item_id) > 0) {
                $data = $request->item_id;
            } else if($request->generated == 1) {
                $data = self::getAllItems($request);
            } else {
                $data = [];
            }
            
            if (empty($data)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please select Items',
                    'error' => "",
                ], 422);
            }
            foreach ($data as $itemKey => $items) {
                $uom = Unit::find($items['uom_id'] ?? ($request->uom_id[$itemKey] ?? null));
                if (isset($request->rfq_item_id[$itemKey])) {
                    // Updating existing RFQ Item
                    $item = ErpRfqItem::find($items['rfq_item_id'] ?? ($request->rfq_item_id[$itemKey] ?? null));
                } else {
                    // Creating new RFQ Item
                    $item = Item::find($request->item_id[$itemKey] ?? null);
                }
                $requiredQty = isset($request->item_req_qty[$itemKey]) ? $request->item_req_qty[$itemKey] : 0;
                if($requiredQty <= 0){
                    DB::rollBack();
                    return response()->json(['message' => 'Item Qty cannot be 0 or less.',
                            'error' => ''
                        ], 422);
                }
                // ✅ Determine correct source of pi_item_ids (stored as JSON string in DB)
                if ($item instanceof ErpRfqItem) {
                    $piItemIdsJson = $item->pi_item_ids ?? null;
                } else {
                    $piItemIdsJson = $request->pi_item_ids[$itemKey] ?? null;
                }


                $rfqItemData = [
                    'rfq_header_id' => $rfq->id,
                    'item_id'       => $item->item_id ?? $item->id,
                    'item_code'     => $item->item_code,
                    'item_name'     => $item->item_name,
                    'uom_id'        => $uom->id ?? null,
                    'uom_code'      => $uom->name ?? null,
                    'request_qty'   => $requiredQty,
                    'remarks'       => $request->item_remarks[$itemKey] ?? ($item->item_remarks ?? null),
                    'pi_item_ids'   => $piItemIdsJson, // ✔️ Cleanly resolved above
                ];

                $rfqItemId = $request->rfq_item_id[$itemKey] ?? null;

                if ($rfqItemId) {
                    $rfqItem = ErpRfqItem::find($rfqItemId);
                    if ($rfqItem) {
                        $rfqItem->fill($rfqItemData);
                        $rfqItem->save();
                    } else {
                        $rfqItem = ErpRfqItem::create($rfqItemData);
                    }
                } else {
                    $rfqItem = ErpRfqItem::create($rfqItemData);
                }
                
                // Optional debug
                
                if (!empty($piItemIdsJson)) {
                    $piItemIds =  json_decode($piItemIdsJson) ?? [];
                    $piIds = $item instanceof ErpRfqItem ? $item->pi_ids() : PiItem::whereIn('id', $piItemIds)->pluck('pi_id')->toArray();
                    if (count($piItemIds) !== count($piIds)) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'PI Items and PI IDs count mismatch.',
                            'error' => ''
                        ], 422);
                    }
                    
                    $remainingQty = (float) $requiredQty;
                    
                    foreach ($piItemIds as $i => $piItemId) {
                        $piId = $piIds[$i] ?? null;
                        
                        if (!$piItemId || !$piId || $remainingQty <= 0) {
                            continue;
                        }
                        
                        $piItem = PiItem::find($piItemId);
                        if (!$piItem) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'PI Item not found (ID: ' . $piItemId . ')',
                                'error' => ''
                            ], 422);
                        }

                        // Fetch or create mapping based only on pi_id and pi_item_id
                        $mapping = ErpRfqPiMapping::firstOrCreate(
                            [
                                'pi_id' => $piId,
                                'pi_item_id' => $piItemId,
                            ],
                            [
                                'pi_qty' => $piItem->indent_qty,
                                'rfq_qty' => 0,
                            ]
                        );

                        if (!$mapping || !$mapping->id) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Failed to create PI mapping.',
                                'error' => ''
                            ], 500);
                        }

                        // Calculate current available qty from detail rows
                        $totalMappedQty = ErpRfqPiMappingDetail::where('pi_rfq_id', $mapping->id)->sum('rfq_qty');
                        $availableQty =  $mapping->pi_qty - $totalMappedQty;

                        if ($availableQty <= 0)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Total allocated RFQ quantity exceeds available PI quantity for PI Item : ' . $piItem->item_name,
                                    'error' => ''
                                ], 422);
                            } 

                        $qtyToAllocate = min($availableQty, $remainingQty);
                        $remainingQty -= $qtyToAllocate;

                        // Insert or update detail
                        $detail = ErpRfqPiMappingDetail::updateOrCreate(
                            [
                                'pi_rfq_id'   => $mapping->id,
                                'pi_id'       => $piId,
                                'pi_item_id'  => $piItemId,
                                'rfq_id'      => $rfq->id,
                                'rfq_item_id' => $rfqItem->id,
                            ],
                            [
                                'pi_qty'  => $piItem->indent_qty,
                                'rfq_qty' => $qtyToAllocate,
                            ]
                        );

                        if (!$detail || !$detail->id) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Failed to insert RFQ-PI Mapping Detail.',
                                'error' => ''
                            ], 500);
                        }

                        // Update mapping's rfq_qty after detail insert
                        $totalRfqQty = ErpRfqPiMappingDetail::where('pi_item_id', $mapping->id)->sum('rfq_qty');
                        // ❗ Ensure total rfq_qty does not exceed pi_qty
                        if ($totalRfqQty > $mapping->pi_qty) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Total allocated RFQ quantity exceeds available PI quantity for PI Item ID: ' . $piItemId,
                                'error' => ''
                            ], 422);
                        }

                        $mapping->rfq_qty = $totalRfqQty;
                        $mapping->save();
                    }

                    // Step 4: Update PiItem's rfq_qty based on total mapped qty
                    foreach ($piItemIds as $i => $piItemId) {
                        $piId = $piIds[$i] ?? null;
                        if (!$piItemId || !$piId) continue;

                        $totalMappedQty = ErpRfqPiMapping::where('pi_id', $piId)
                            ->where('pi_item_id', $piItemId)
                            ->sum('rfq_qty');
                        $updateSuccess = PiItem::where('id', $piItemId)->update([
                            'rfq_qty' => $totalMappedQty,
                        ]);
                        if (!$updateSuccess) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Failed to update PiItem::rfq_qty for PI Item ID: ' . $piItemId,
                                'error' => ''
                            ], 500);
                        }
                    }
                }
                // === Item Attributes Handling ===
                if (is_array($items)) {
                    $itemAtts = $items['item_attributes'] ?? [];
                } else {
                    $itemAtts = isset($request->item_attributes[$itemKey]) ? json_decode($request->item_attributes[$itemKey], true) : ($items['item_attributes'] ?? []);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($itemAtts)) {
                        return response()->json([
                            'message' => 'Item No. ' . ($itemKey + 1) . ' has invalid attributes',
                            'error' => ''
                        ], 422);
                    }
                }

                foreach ($itemAtts as $attribute) {
                    $attribute = is_array($attribute) ? $attribute : $attribute->toArray();

                    $attributeVal = "";
                    $attributeValId = null;
                    $attributeGrp = "";
                    $attributeGrpId = null;

                    if (isset($attribute['values_data'])) {
                        foreach ($attribute['values_data'] as $valData) {
                            if ($valData['selected']) {
                                $attributeVal = $valData['value'];
                                $attributeValId = $valData['id'];
                                $attributeGrp = $attribute['group_name'];
                                $attributeGrpId = $attribute['attribute_group_id'];
                                break;
                            }
                        }
                    } else {
                        $attributeVal = $attribute['attribute_value'] ?? null;
                        $attributeValId = $attribute['attribute_id'] ?? null;
                        $attributeGrp = $attribute['attribute_name'] ?? null;
                        $attributeGrpId = $attribute['group_id'] ?? null;
                    }

                    $itemAttribute = ErpRfqItemAttribute::updateOrCreate([
                        'rfq_id' => $rfq->id,
                        'rfq_item_id' => $rfqItem->id,
                        'item_attribute_id' => $attribute['item_attribute_id'] ?? $attribute['id'],
                    ], [
                        'item_code' => $rfqItem->item_code,
                        'attribute_name' => $attributeGrpId,
                        'attr_name' => $attributeGrp,
                        'attribute_value' => $attributeValId,
                        'attr_value' => $attributeVal,
                    ]);

                    $itemAttributeIds[] = $itemAttribute->id;
                }
            }
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approvalLogic = self::handleApprovalLogic($request, $rfq);
            }
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $rfq->uploadDocuments($file, 'rfq_header', false);
                }
            }
    
            // if (in_array($rfq->document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
            //     if (!self::maintainStockLedger($rfq)) {
            //         DB::rollBack();
            //         return response()->json(['message' => 'Stock not available'], 422);
            //     }
            //     else{
            //         $items = $rfq->items->where('adjusted_qty',0);
            //         foreach ($items as $item) {
            //             $atts_data = $item->attributes;
            //             foreach ($atts_data as $att) {
            //                 $att->delete();
            //             }
            //             $item->delete();
            //         }
            //     }
            // }
            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpRfqDynamicField::class, $rfq -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            DB::commit();
            return response()->json([
                'message' => "Request For Quotation created successfully",
                'redirect_url' => route('rfq.index')
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex->getLine() . ' in ' . $ex->getFile(),
            ], 500);
        }
    }
    
    private function handleApprovalLogic(Request $request, ErpRfqHeader $rfq)
    {
        $bookId = $rfq->book_id;
        $docId = $rfq->id;
        $currentLevel = $rfq->approval_level;
        $revisionNumber = $rfq->revision_number ?? 0;
        $modelName = get_class($rfq);
        $attachments = $request->file('attachments');
        $actionType = $request->action_type ?? '';
        $remarks = $rfq->remark;

        if (($rfq->document_status === ConstantHelper::APPROVED ||
            $rfq->document_status === ConstantHelper::APPROVAL_NOT_REQUIRED) && 
            $actionType === 'amendment') {

            $revisionNumber++;
            $rfq->revision_number = $revisionNumber;
            $rfq->approval_level = 1;
            $rfq->revision_date = now();

            $amendRemarks = $request->amend_remarks ?? $remarks;
            $amendAttachments = $request->file('amend_attachments') ?? $attachments;

            Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, 'amendment', 0, $modelName);

            $checkAmendment = Helper::checkAfterAmendApprovalRequired($bookId);
            if (isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                $totalValue = $rfq->grand_total_amount ?? 0;
                $rfq->document_status = Helper::checkApprovalRequired($bookId, $totalValue);
            } else {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'approve', 0, $modelName);
                $rfq->document_status = ConstantHelper::APPROVED;
            }

            if ($rfq->document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'submit', 0, $modelName);
            }
        } else {
            if ($request->document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'submit', 0, $modelName);
                $totalValue = $rfq->grand_total_amount ?? $rfq->total_amount ?? 0;
                $rfq->document_status = Helper::checkApprovalRequired($bookId, $totalValue);
            } else {
                $rfq->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
        }

        $rfq->save();
    }

    public function revokeRFQ(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpRfqHeader::find($request -> id);
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
    public function generatePdf(Request $request, $id,$pattern,$download = false,$returnRaw = false)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
        ->where('addressable_id', $user->organization_id)
        ->where('addressable_type', Organization::class)
        ->first();
        $rfq = ErpRfqHeader::with([
            'store',
            'book',
            'items.item.specifications',
            'items.item.alternateUoms.uom',
            'items.item.uom',
        ])
        ->find($id);

        // Add item_attributes to each item
        if ($rfq && $rfq->items) {
            foreach ($rfq->items as $item) {
                $item->item_attributes = $item->get_attributes_array();
            }
        }

        // $creator = AuthUser::with(['authUser'])->find($rfq->created_by);
        // dd($creator,$rfq->created_by);
        $shippingAddress = $rfq?->from_store?->address;
        $billingAddress = $rfq?->to_store?->address;
        $approvedBy = Helper::getDocStatusUser(get_class($rfq), $rfq -> id, $rfq -> document_status);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$rfq->document_status] ?? '';

        // dd($user);
        // $type = ConstantHelper::SERVICE_LABEL[$rfq->document_type];
        // $totalItemValue = $rfq->total_item_value ?? 0.00;
        // $totalTaxes = $rfq->total_tax_value ?? 0.00;
        // $totalAmount = ($totalItemValue + $totalTaxes);
        // $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // // $storeAddress = ErpStore::with('address')->where('id',$rfq->store_id)->get();
        // dd($rfq->location->address);
        // Path to your image (ensure the file exists and is accessible)
        $approvedBy = Helper::getDocStatusUser(get_class($rfq), $rfq -> id, $rfq -> document_status);
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dynamicFields = $rfq -> dynamic_fields;

        $html = view('rfq.rfq', [
            'rfq' => $rfq,
            'user' => $user,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'organization' => $organization,
            'organizationAddress' => $organizationAddress,
            'imagePath' => $imagePath,
            'docStatusClass' => $docStatusClass,
            'approvedBy' => $approvedBy,
            'dynamicFields' => $dynamicFields,
            // Add any additional required values here
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = $rfq->book_code . '-' . $rfq->document_number;
        $pdfPath = 'rfq/pdfs/rfq_' . $fileName . '.pdf';

        Storage::disk('local')->put($pdfPath, $dompdf->output());

        if ($download) {
            return $dompdf->stream($fileName . '.pdf', ['Attachment' => true]);
        }

        if ($returnRaw) {
            return $dompdf->output();
        }

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Request_for_Quotation_' . $fileName . '.pdf"');
    }
    public function report(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::RFQ_SERVICE_ALIAS;
        $redirectUrl = route('rfq.report');
        $requesters = ErpRfqHeader::withDefaultGroupCompanyOrg()->bookViewAccess($pathUrl)->orderByDesc('id')->get()
        ->map(function ($item) {
            return [
                'id' => $item->requester()->first()->id ?? null,
                'name' => $item->requester()->first()->name ?? '',
            ];
        });
        if ($request->ajax()) {
            try {
                // Fetch Material Issues with Related Items and Attributes
                $docs = ErpRfqHeader::with('requester')->where('issue_type', 'Consumption')
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
                $issue_data = ErpRfqItem::with(['header'])->whereIn('rfq_header_id', $docs->pluck('id'))->orderByDesc('id')->get();
                $issue_item_ids = $issue_data -> pluck('id');
                // Fetch corresponding return data
                $return_data = ErpMrItem::whereIn('rfq_item_id', $issue_item_ids)
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
                            $used = $return_data->where('rfq_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'RETURN OLD';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();

                            $returned = $return_data->where('rfq_item_id', $row->id)
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
        return view('rfq.report',['requesters'=>$requesters]);
    }
    public function pullItems($doc)
    {
         // Now manually load paginated items (25 per page)
         $items = ErpRfqItem::with([
            'item.specifications',
            'item.alternateUoms.uom',
            'item.uom',
            'item.hsn',
        ])
        ->where('rfq_header_id', $doc->id)
        ->paginate(25); // Laravel paginator

        $items->getCollection()->transform(function ($item) {
            $item->attributes_array = $item->get_attributes_array();
            return $item;
        });
    // Pass $items separately to the view or return response accordingly
            return $items;
    }

    public function getPiItemForPulling(Request $request)
    {
        try { 
            $selectedIds = $request->input('selected_ids', []);
            $piItemIds = $request->input('pi_item_ids', []);
            $docType = $request->input('doc_type');
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
            $requesterType = $request->input('requester_type', 'Department');

            $baseQuery = null;
            if (in_array($docType, [ConstantHelper::PI_SERVICE_ALIAS, 'pi'])) {
                $referedHeaderId = PurchaseIndent::whereIn('id', $selectedIds)->first()?->header?->id;

                $baseQuery = PiItem::with(['header', 'attributes', 'uom'])
                    ->whereHas('header', function ($query) use ($request, $requesterType, $referedHeaderId, $applicableBookIds) {
                        $query->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id))
                            ->whereIn('document_status', [
                                ConstantHelper::APPROVED,
                                ConstantHelper::APPROVAL_NOT_REQUIRED
                            ])
                            ->whereIn('book_id', $applicableBookIds);
                    })
                    ->whereColumn('indent_qty', '>', 'rfq_qty')
                    ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds));
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
                ->addColumn('store_location_code', fn($item) => $item->header?->store_location?->store_name ?? '')
                ->addColumn('sub_store_code', fn($item) => $item->header?->sub_store?->name ?? '')
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
                    ),2);
                })
                ->editColumn('qty', function ($item) use ($request) {
                    if ($request->mi_type === ConstantHelper::TYPE_JOB_ORDER) {
                        return number_format($item->order_qty, 2);
                    } else {
                        return (number_format($item->qty,2));
                    }
                })
                ->editColumn('balance_qty', function ($item) use ($request) {
                    return (number_format($item->indent_qty - $item->rfq_qty,2));
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

    public function processItems(Request $request)
    {
        $piItemIds = $request->input('items_id', []);
        if (empty($piItemIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PI item IDs provided.',
            ], 422);
        }

        $piItems = PiItem::with(['item.hsn', 'attributes', 'uom'])
            ->whereIn('id', $piItemIds)
            ->get();

        // Group by item_id + uom_id + sorted attributes
        $grouped = $piItems->groupBy(function ($item) {
            $attributes = $item->attributes->map(function ($attr) {
                return [
                    'name' => $attr->attr_name,
                    'value' => $attr->attr_value,
                ];
            })->sortBy('name')->values();

            return implode('|', [
                $item->item_id,
                $item->uom_id,
                $attributes->toJson(),
            ]);
        });
        $groupedData = $grouped->map(function ($items) {
            $firstItem = $items->first();
            $header_ids = [];
            $items_id = $items->pluck('id')->values();
            foreach($items as $item){
                $header_ids[] = $item->header->id;
            }
            return [
                'id'                  => null, // optional if no common ID; frontend ignores it
                'item_id'             => $firstItem->item_id,
                'item' => [
                    'item_code'        => $firstItem->item?->item_code,
                    'item_name'        => $firstItem->item?->item_name,
                    'hsn'              => ['code' => $firstItem->item?->hsn?->code],
                    'specifications'   => $firstItem->item?->specifications,
                    'uom'              => ['id' => $firstItem->uom_id, 'alias' => $firstItem->uom_code],
                ],
                'uom_id'              => $firstItem->uom_id,
                'item_attributes_array' => $firstItem->item_attributes_array(),
                'rfq_balance_qty'     => $items->sum('rfq_balance_qty'),
                'remarks'             => null, // optional — your frontend defaults it
                'header_ids'          => $header_ids,
                'item_ids'           => $items_id,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => [['items' => $groupedData]],
        ]);
    }

    public function mail(Request $request)
    {
        $rfq = ErpRfqHeader::find($request->id);
        $suppliers = $rfq->vendors() ?? collect(); // Ensure it's a collection
        $supp_ids = isset($request->email_to_id) ? explode('.', $request->email_to_id) : [];
        $sendTo =  isset($supp_ids) ? Vendor::whereIn('id', $supp_ids)->pluck('company_name','email')->toArray() : [];
        $user = Helper::getAuthenticatedUser();
        $title = "Request for Quotation Generated";
        $pattern = "Request for Quotation";
        $remarks = $request->remarks ?? null;

        $mail_from = '';
        $mail_from_name = '';
        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
        $bcc = $request->bcc_to ? implode(',', $request->bcc_to) : null;

        $attachments = [];

        // Generate PDF
        try {
            $pdfContent = $this->generatePdf($request, $request->id, $pattern, false, true);

            $pdfFileName = "Request_for_quotation_{$rfq->document_number}.pdf";
            $attachments[] = [
                'file' => $pdfContent,
                'options' => [
                    'as' => $pdfFileName,
                    'mime' => 'application/pdf',
                ]
            ];
        } catch (\Exception $e) {
            \Log::error("Failed to generate RFQ PDF: " . $e->getMessage());
        }

        // Handle any uploaded files
        if ($request->hasFile('attachments')) {
            foreach ((array) $request->file('attachments') as $uploadedFile) {
                $attachments[] = [
                    'file' => file_get_contents($uploadedFile->getRealPath()),
                    'options' => [
                        'as' => $uploadedFile->getClientOriginalName(),
                        'mime' => $uploadedFile->getMimeType(),
                    ]
                ];
            }
        }


        $description = <<<HTML
        <table width="100%" style="max-width: 600px; background: #fff; padding: 24px; border-radius: 8px; font-family: Arial;">
            <tr>
                <td>
                    <h2 style="color: #2c3e50;">Request for Quotation</h2>
                    <p style="font-size: 16px; color: #555;">Dear Supplier,</p>
                    <p style="font-size: 15px;">{$remarks}</p>
                    <p style="font-size: 15px;">
                        Please find the attached quotation PDF. Let us know if you need any changes or have queries.
                    </p>
                </td>
            </tr>
        </table>
        HTML;

        // Use object with email for compatibility
        $receiver = (object)[
            'email' => key($sendTo) ?? '',
            'name' => current($sendTo) ?? 'Supplier',
            'description' => $description,
        ];

        return $this->sendMail($receiver, $title, $description, $cc, $attachments, $mail_from, $mail_from_name, $bcc);
    }



    public function sendMail($receiver, $title, $description, $cc = null, $attachments = [], $mail_from = null, $mail_from_name = null,$bcc=null)
    {
        try {
            if (!$receiver || !isset($receiver->email)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Receiver details are missing or invalid.',
                ], 400);
            }

            // Prepare attachment paths to pass to the job (avoid binary content in queue)
            $storedAttachments = [];

            foreach ($attachments as $attachment) {
                $filename = $attachment['options']['as'] ?? uniqid() . '.pdf';
                $mime = $attachment['options']['mime'] ?? 'application/octet-stream';
                $tempPath = storage_path("app/temp_mails/{$filename}");

                // Ensure directory exists
                if (!file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0777, true);
                }

                file_put_contents($tempPath, $attachment['file']);

                $storedAttachments[] = [
                    'path' => $tempPath,
                    'as' => $filename,
                    'mime' => $mime
                ];
            }
            dispatch(new SendEmailJob(
            $receiver,
            $mail_from,
            $mail_from_name,
            $title,
            $description,
            $cc,
            $bcc,
            $storedAttachments
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Email request sent successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending email: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send email. ' . $e->getMessage(),
            ], 500);
        }
    }
}
