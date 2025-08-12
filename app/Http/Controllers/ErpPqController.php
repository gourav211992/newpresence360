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
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Helpers\TransactionReport\pqReportHelper;
use App\Helpers\UserHelper;
use App\Jobs\SendEmailJob;
use App\Models\Address;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Country;
use App\Models\ErpAddress;
use App\Models\ErpPqDynamicField;
use App\Models\ErpPqHeader;
use App\Models\ErpPqHeaderHistory;
use App\Models\ErpPqItem;
use App\Models\ErpPqItemAttribute;
use App\Models\ErpPqItemTed;
use App\Models\ErpPqItemTedHistory;
use App\Models\ErpPqPiMapping;
use App\Models\ErpPqPiMappingDetail;
use App\Models\ErpRfqHeader;
use App\Models\ErpRfqItem;
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

class ErpPqController extends Controller
{
    //
    public function index(Request $request)
    {
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());

        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PQ_SERVICE_ALIAS;
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $redirectUrl = route('pq.index');
        $createRoute = route('pq.create');
        $typeName = "Purchase Quotation";
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
                $docs = ErpPqHeader::withDefaultGroupCompanyOrg() -> whereIn('store_id',$accessible_locations) -> bookViewAccess($pathUrl) ->  withDraftListingLogic()->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) 
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
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                    $displayStatus = $row -> display_status;
                    $editRoute = route('pq.edit', ['id' => $row -> id]); 
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
                ->addColumn('rfq',function($row){
                    return $row?->rfq?->document_number ? $row?->rfq?->book_code.'-'.$row?->rfq?->document_number : " ";
                })
                ->addColumn('name',function($row){
                    return $row?->rfq?->contact_name??" ";
                })
                ->addColumn('name',function($row){
                    return $row?->rfq?->contact_name??" ";
                })
                ->addColumn('email',function($row){
                    return $row?->rfq?->contact_email??" ";
                })
                ->addColumn('vendor',function($row){
                    return $row?->selected_vendor??" ";
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
        return view('pq.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl,'create_route' => $createRoute, 'create_button' => $create_button, 'filterArray' => pqReportHelper::PQ_FILTERS,
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
        $redirectUrl = route('pq.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::PQ_SERVICE_ALIAS;
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
        return view('pq.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $currentfyYear = Helper::getCurrentFinancialYear();
            $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('pq.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpPqHeaderHistory::with(['book','media_files', 'discount_ted', 'expense_ted', 
                'billing_address_details', 'location_address_details'])
                ->with('items', function ($query) {
                    $query->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                })->bookViewAccess($parentUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()
                -> where('source_id', $id)->where('revision_number', $request->revisionNumber)->firstOrFail();
        
            $ogDoc = ErpPqHeader::find($id) -> bookViewAccess($parentUrl) -> withDefaultGroupCompanyOrg() 
                -> withDraftListingLogic() -> firstOrFail();
            } else {
                $doc = ErpPqHeader::with(['book','media_files', 'discount_ted', 'expense_ted', 
                'billing_address_details', 'location_address_details'])
                ->with('items', function ($query) {
                    $query->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                }) -> where('id', $id) ->bookViewAccess($parentUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()->firstOrFail();
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
            $books = Helper::getBookSeriesNew(ConstantHelper::PQ_SERVICE_ALIAS, ) -> get();
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
            return view('pq.create_edit', $data);  
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
            $isUpdate = $request->pq_header_id ? true : false;
    
            if (!$isUpdate) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ?? $request->document_no;
                $regeneratedDocExist = ErpPqHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                if ($regeneratedDocExist) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
            $itemTaxIds = [];
            $companyCountryId = null;
            $companyStateId = null;
            $store = ErpStore::find($request -> store_id);
            if ($store && isset($store -> address)) {
                $companyCountryId = $store->address?->country_id??null;
                $companyStateId = $store->address?->state_id??null;
            } else {
                return response()->json([
                    'message' => 'Please create an organization first'
                ], 422);
            }
            $vendors = Vendor::whereIn('id',$request->supplier_ids ?? [])->get()->toArray();
            
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
            
            //Seperate array to store each item calculation
            $itemsData = array();
            $pqExists = ErpPqHeader::where('rfq_id', $request->rfq_id)
                    ->where('vendor_id', $request->vendor_id)
                    ->exists();

                if ($pqExists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Quote already present for this vendor for the selected request.",
                        'status'  => 'error'
                    ], 500);
                }

            if ($isUpdate) {
                // If it's an update, we need to find the existing PQ header
                $pq = ErpPqHeader::find($request->pq_header_id);
                $pq->document_date = $request->document_date;
                $pq->instructions = $request->instructions;
                $store = ErpStore::find($request->store_id);
                $pq->vendor_id = $request->vendor_id ?? ($pq->vendor_id ?? null);
                $pq->vendor_name = $request->vendor_code ?? ($pq->vendor_code ?? null);
                $pq->vendor_email = $request->vendor_email ?? ($pq->vendor_email ?? null);
                $pq->vendor_phone = $request->vendor_phone_no ?? ($pq->vendor_phone ?? null);
                $pq->vendor_gstin = $request->vendor_gstin ?? ($pq->vendor_gstin ?? null);
                $pq->store_id = $request->store_id ?? ($pq->store_id ?? null);
                $pq->store_code = $store->store_code ?? ($pq->store_code ?? null);
                $pq->remarks = $request->final_remarks;
                $actionType = $request->action_type ?? '';

                if (($pq->document_status == ConstantHelper::APPROVED || $pq->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpPqHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpPqDynamicField', 'relation_column' => 'pq_header_id'],
                        ['model_type' => 'detail', 'model_name' => 'ErpPqItem', 'relation_column' => 'pq_header_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpPqItemAttribute', 'relation_column' => 'pq_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpPqItemTed', 'relation_column' => 'pq_item_id'],
                        // Add more if you have sub-details like deliveries, etc.
                    ];
                    Helper::documentAmendment($revisionData, $pq->id);
                }

                $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedSiItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }

                if (count($deletedData['deletedHeaderExpTedIds'])) {
                    ErpPqItemTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
                }

                if (count($deletedData['deletedHeaderDiscTedIds'])) {
                    ErpPqItemTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
                }

                if (count($deletedData['deletedItemDiscTedIds'])) {
                    ErpPqItemTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
                }
                // Handle deleted attachments
                if (count($deletedData['deletedAttachmentIds'])) {
                    $files = $pq->mediaFiles()->whereIn('id', $deletedData['deletedAttachmentIds'])->get();
                    foreach ($files as $singleMedia) {
                        $filePath = $singleMedia->file_name;
                        if (Storage::exists($filePath)) {
                            Storage::delete($filePath);
                        }
                        $singleMedia->delete();
                    }
                }
                // Handle deleted PQ Items
                if (count($deletedData['deletedSiItemIds'])) {
                    $pqItems = ErpPqItem::whereIn('id', $deletedData['deletedSiItemIds'])->get();
                    foreach ($pqItems as $pqItem) {
                        $pqItem->attributes()->delete();
                        $pqItem->teds()->delete();
                        // If you have other sub-details, delete them here
                        $pqItem->delete();
                    }
                }

                $pq->fill([
                    'document_date' => $request->document_date,
                    'store_id' => $request->store_id,
                    'remarks' => $request->final_remarks,
                ])->save();
            } else {
                $pq = ErpPqHeader::create([
                    'rfq_id' => $request->rfq_id ?? null,
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
                    'document_date' => $request?->document_date,
                    'due_date' => $request?->due_date ?? null,
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
                    'vendor_id' => $request->vendor_id ? $request->vendor_id : 0,
                    // 'consignee_name' => $request->consignee_name ? $request->consignee_name : 0,
                    'vendor_name' => $request->vendor_code ? $request->vendor_code : 0,
                    'vendor_email' => $request->vendor_email ? $request->vendor_email : 0,
                    'vendor_phone' => $request->vendor_phone_no ? $request->vendor_phone_no : 0,
                    'vendor_gstin' => $request->vendor_gstin ? $request->vendor_gstin : 0,
                    'payment_terms_id' => $request->payment_terms_id ? $request->payment_terms_id : 0,
                    'payment_terms_code' => $request->payment_terms_code ? $request->payment_terms_code : 0,
                    'remarks' => $request->final_remarks,
                ]);
                //Billing Address
                $vendorBillingAddress = ErpAddress::find($request -> billing_address);
                if (isset($vendorBillingAddress)) {
                    $billingAddress = $pq -> billing_address_details() -> create([
                        'address' => $vendorBillingAddress -> address,
                        'country_id' => $vendorBillingAddress -> country_id,
                        'state_id' => $vendorBillingAddress -> state_id,
                        'city_id' => $vendorBillingAddress -> city_id,
                        'type' => 'billing',
                        'pincode' => $vendorBillingAddress -> pincode,
                        'phone' => $vendorBillingAddress -> phone,
                        'fax_number' => $vendorBillingAddress -> fax_number
                    ]);
                } else {
                    $billingAddress = $pq -> billing_address_details() -> create([
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
                $vendorShippingAddress = ErpAddress::find($request -> shipping_address);
                if (isset($vendorShippingAddress)) {
                    $shippingAddress = $pq -> shipping_address_details() -> create([
                        'address' => $vendorShippingAddress -> address,
                        'country_id' => $vendorShippingAddress -> country_id,
                        'state_id' => $vendorShippingAddress -> state_id,
                        'city_id' => $vendorShippingAddress -> city_id,
                        'type' => 'shipping',
                        'pincode' => $vendorShippingAddress -> pincode,
                        'phone' => $vendorShippingAddress -> phone,
                        'fax_number' => $vendorShippingAddress -> fax_number
                    ]);
                } else {
                    $shippingAddress = $pq -> shipping_address_details() -> create([
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
                $orgLocationAddress = ErpStore::with('address') -> find($request -> store_id);
                if (!isset($orgLocationAddress) || !isset($orgLocationAddress -> address)) {
                    DB::rollBack();
                    return response() -> json([
                        'message' => 'Location Address not assigned',
                        'error' => ''
                    ], 422);
                }
                $locationAddress = $pq -> location_address_details() -> create([
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
            $pq -> billing_address = isset($billingAddress) ? $billingAddress -> id : null;
            $pq -> shipping_address = isset($shippingAddress) ? $shippingAddress -> id : null;
            $pq -> save();
            
            if ($request -> item_id && count($request -> item_id) > 0) {
                //Items
                $totalValueAfterDiscount = 0;
                foreach ($request -> item_id as $itemKey => $itemId) {
                    $item = Item::find($itemId);
                    if (isset($item))
                    {
                        $itemValue = (isset($request -> item_req_qty[$itemKey]) ? $request -> item_req_qty[$itemKey] : 0) * (isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0);
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
                        $uom = $item -> uom ?? null;
                        $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_req_qty[$itemKey]) ? $request -> item_req_qty[$itemKey] : 0);
                        array_push($itemsData, [
                            'pq_header_id' => $pq -> id,
                            'rfq_id' => isset($request -> rfq_id) ? $request -> rfq_id : null,
                            'rfq_item_id' => isset($request -> rfq_item_ids[$itemKey]) ? json_decode($request -> rfq_item_ids[$itemKey])[0] : null,
                            'item_id' => $item -> id,
                            'item_code' => $item -> item_code,
                            'item_name' => $item -> item_name,
                            'hsn_id' => $item -> hsn_id,
                            'hsn_code' => $item -> hsn ?-> code,
                            'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null, //Need to change
                            'uom_code' => isset($request -> uom_code[$itemKey]) ? $request -> uom_code[$itemKey] : ($uom-> name ?? null),
                            'request_qty' => isset($request -> item_req_qty[$itemKey]) ? $request -> item_req_qty[$itemKey] : 0,
                            'invoice_qty' => 0,
                            'inventory_uom_id' => $item -> uom ?-> id,
                            'inventory_uom_code' => $item -> uom ?-> name,
                            'inventory_uom_qty' => $inventoryUomQty,
                            'rate' => isset($request -> item_rate[$itemKey]) ? (float)$request -> item_rate[$itemKey] : 0,
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
                    $headerDiscount = ($itemDataValue['value_after_discount'] ?? 1 / $totalValueAfterDiscount) * $totalHeaderDiscount;
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
                    $itemPrice = ($itemDataValue['item_value'] + $headerDiscount + $itemDataValue['item_discount_amount']) / $itemDataValue['request_qty'];
                    $partyCountryId = isset($billingAddress) ? $billingAddress -> country_id : null;
                    $partyStateId = isset($billingAddress) ? $billingAddress -> state_id : null;
                    $taxDetails = TaxHelper::calculateTax($itemDataValue['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> country_id, $partyStateId ?? $request -> state_id, 'purchase');
                    if (isset($taxDetails) && count($taxDetails) > 0) {
                        foreach ($taxDetails as $taxDetail) {
                            $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            if($taxDetail['applicability_type']=="collection")
                            {
                                $totalTax -= $itemTax;
                            }
                            else
                            {
                                $totalTax += $itemTax;
                            }
                        }
                    }
                    //Check if update or create
                    $itemRowData = [
                        'pq_header_id' => $pq -> id,
                        'rfq_id' => $itemDataValue['rfq_id'],
                        'rfq_item_id' => $itemDataValue['rfq_item_id'],
                        'item_id' => $itemDataValue['item_id'],
                        'item_code' => $itemDataValue['item_code'],
                        'item_name' => $itemDataValue['item_name'],
                        'hsn_id' => $itemDataValue['hsn_id'],
                        'hsn_code' => $itemDataValue['hsn_code'],
                        'uom_id' => $itemDataValue['uom_id'], //Need to change
                        'uom_code' => $itemDataValue['uom_code'] ?? ($uom->code ?? null),
                        'request_qty' => $itemDataValue['request_qty'],
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
                        'total_item_amount' => ($itemDataValue['request_qty'] * $itemDataValue['rate']) - ($itemDataValue['item_discount_amount'] + $headerDiscount) + ($itemExpenseAmount + $itemHeaderExpenseAmount) + $itemTax,
                        'company_currency_id' => null,
                        'company_currency_exchange_rate' => null,
                        'group_currency_id' => null,
                        'group_currency_exchange_rate' => null,
                        'remarks' => $itemDataValue['remarks'],
                    ];
                    if (isset($request -> pq_item_id[$itemDataKey])) {
                        $oldSoItem = ErpPqItem::find($request -> pq_item_id[$itemDataKey]);
                        $soItem = ErpPqItem::updateOrCreate(['id' => $request -> pq_item_id[$itemDataKey]], $itemRowData);
                    } else {
                        $soItem = ErpPqItem::create($itemRowData);
                    }
                    //TED Data (DISCOUNT)
                    if (isset($request -> item_discount_value[$itemDataKey]))
                    {
                        foreach ($request -> item_discount_value[$itemDataKey] as $itemDiscountKey => $itemDiscountTed){
                            $itemDiscountRowData = [
                                'pq_id' => $pq -> id,
                                'rfq_item_id' => $soItem -> id,
                                'ted_type' => 'Discount',
                                'ted_level' => 'D',
                                'ted_id' => isset($request -> item_discount_master_id[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_master_id[$itemDataKey][$itemDiscountKey] : null,
                                'ted_name' => isset($request -> item_discount_name[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_name[$itemDataKey][$itemDiscountKey] : null,
                                'assessment_amount' => $itemDataValue['rate'] * $itemDataValue['request_qty'],
                                'ted_percentage' => isset($request -> item_discount_percentage[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_percentage[$itemDataKey][$itemDiscountKey] : null,
                                'ted_amount' => $itemDiscountTed,
                                'applicable_type' => 'Deduction',
                            ];
                            if (isset($request -> item_discount_id[$itemDataKey][$itemDiscountKey])) {
                                $soItemTedForDiscount = ErpPqItemTed::updateOrCreate(['id' => $request -> item_discount_id[$itemDataKey][$itemDiscountKey]], $itemDiscountRowData);
                            } else {
                                $soItemTedForDiscount = ErpPqItemTed::create($itemDiscountRowData);
                            }
                        }
                    }
                    //TED Data (TAX)
                    if (isset($taxDetails) && count($taxDetails) > 0) {
                        foreach ($taxDetails as $taxDetail) {
                            $soItemTedForDiscount = ErpPqItemTed::updateOrCreate(
                                [
                                    'pq_id' => $pq -> id,
                                    'pq_item_id' => $soItem -> id,
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
                                $itemAttribute = ErpPqItemAttribute::updateOrCreate(
                                    [
                                        'pq_id' => $pq -> id,
                                        'pq_item_id' => $soItem -> id,
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
                    ErpPqItemTed::where([
                        'pq_id' => $pq -> id,
                        'pq_item_id' => $soItem -> id,
                        'ted_type' => 'Tax',
                        'ted_level' => 'D',
                    ]) -> whereNotIn('id', $itemTaxIds) -> delete();
                    ErpPqItemAttribute::where([
                        'pq_id' => $pq -> id,
                        'pq_item_id' => $soItem -> id,
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
                        'pq_id' => $pq -> id,
                        'pq_item_id' => null,
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
                        ErpPqItemTed::updateOrCreate(['id' => $request -> order_discount_id[$orderDiscountKey]], $headerDiscountRowData);
                    } else {
                        ErpPqItemTed::create($headerDiscountRowData);
                    }
                }
            }
            //Header TED (Expense)
            $totalValueAfterTax = $totalItemValueAfterDiscount + $totalTax;
            $totalExpenseAmount = 0;
            if (isset($request -> order_expense_value) && count($request -> order_expense_value) > 0) {
                foreach ($request -> order_expense_value as $orderExpenseKey => $orderExpenseVal) {
                    $headerExpenseRowData = [
                        'pq_id' => $pq -> id,
                        'pq_item_id' => null,
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
                        ErpPqItemTed::updateOrCreate(['id' => $request -> order_expense_id[$orderExpenseKey]], $headerExpenseRowData);
                    } else {
                        ErpPqItemTed::create($headerExpenseRowData);
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
            
            $pq -> total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
            $pq -> total_quotation_value = $itemTotalValue;
            $pq -> total_tax_value = $totalTax;
            $pq -> total_expense_value = $totalExpenseAmount;
            $pq -> total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;
             
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approvalLogic = self::handleApprovalLogic($request, $pq);
            }
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $pq->uploadDocuments($file, 'pq_header', false);
                }
            }

            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpPqDynamicField::class, $pq -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            DB::commit();
            return response()->json([
                'message' => "Purchase Quotation created successfully",
                'redirect_url' => route('pq.index')
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex->getLine() . ' in ' . $ex->getFile(),
            ], 500);
        }
    }
    
    private function handleApprovalLogic(Request $request, ErpPqHeader $pq)
    {
        $bookId = $pq->book_id;
        $docId = $pq->id;
        $currentLevel = $pq->approval_level;
        $revisionNumber = $pq->revision_number ?? 0;
        $modelName = get_class($pq);
        $attachments = $request->file('attachments');
        $actionType = $request->action_type ?? '';
        $remarks = $pq->remark;

        if (($pq->document_status === ConstantHelper::APPROVED ||
            $pq->document_status === ConstantHelper::APPROVAL_NOT_REQUIRED) && 
            $actionType === 'amendment') {

            $revisionNumber++;
            $pq->revision_number = $revisionNumber;
            $pq->approval_level = 1;
            $pq->revision_date = now();

            $amendRemarks = $request->amend_remarks ?? $remarks;
            $amendAttachments = $request->file('amend_attachments') ?? $attachments;

            Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, 'amendment', 0, $modelName);

            $checkAmendment = Helper::checkAfterAmendApprovalRequired($bookId);
            if (isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                $totalValue = $pq->grand_total_amount ?? 0;
                $pq->document_status = Helper::checkApprovalRequired($bookId, $totalValue);
            } else {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'approve', 0, $modelName);
                $pq->document_status = ConstantHelper::APPROVED;
            }

            if ($pq->document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'submit', 0, $modelName);
            }
        } else {
            if ($request->document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, 'submit', 0, $modelName);
                $totalValue = $pq->grand_total_amount ?? $pq->total_amount ?? 0;
                $pq->document_status = Helper::checkApprovalRequired($bookId, $totalValue);
            } else {
                $pq->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
        }

        $pq->save();
    }

    public function revokePQ(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpPqHeader::find($request -> id);
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
        $pq = ErpPqHeader::with([
            'store',
            'book',
            'items.item.specifications',
            'items.item.alternateUoms.uom',
            'items.item.uom',
        ])
        ->find($id);

        // Add item_attributes to each item
        if ($pq && $pq->items) {
            foreach ($pq->items as $item) {
                $item->item_attributes = $item->get_attributes_array();
            }
        }

        // $creator = AuthUser::with(['authUser'])->find($pq->created_by);
        // dd($creator,$pq->created_by);
        $shippingAddress = $pq?->from_store?->address;
        $billingAddress = $pq?->to_store?->address;
        $approvedBy = Helper::getDocStatusUser(get_class($pq), $pq -> id, $pq -> document_status);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pq->document_status] ?? '';

        // dd($user);
        // $type = ConstantHelper::SERVICE_LABEL[$pq->document_type];
        // $totalItemValue = $pq->total_item_value ?? 0.00;
        // $totalTaxes = $pq->total_tax_value ?? 0.00;
        // $totalAmount = ($totalItemValue + $totalTaxes);
        // $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // // $storeAddress = ErpStore::with('address')->where('id',$pq->store_id)->get();
        // dd($pq->location->address);
        // Path to your image (ensure the file exists and is accessible)
        $approvedBy = Helper::getDocStatusUser(get_class($pq), $pq -> id, $pq -> document_status);
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dynamicFields = $pq -> dynamic_fields;

        $html = view('pq.pq', [
            'pq' => $pq,
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

        $fileName = $pq->book_code . '-' . $pq->document_number;
        $pdfPath = 'pq/pdfs/pq_' . $fileName . '.pdf';

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
        $orderType = ConstantHelper::PQ_SERVICE_ALIAS;
        $redirectUrl = route('pq.report');
        $requesters = ErpPqHeader::with(['requester'])->withDefaultGroupCompanyOrg()->bookViewAccess($pathUrl)->orderByDesc('id')->where('issue_type','Consumption')->where('requester_type',"User")->get()->unique('user_id')
        ->map(function ($item) {
            return [
                'id' => $item->requester()->first()->id ?? null,
                'name' => $item->requester()->first()->name ?? 'N/A',
            ];
        });
        if ($request->ajax()) {
            try {
                // Fetch Material Issues with Related Items and Attributes
                $docs = ErpPqHeader::with('requester')->where('issue_type', 'Consumption')
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
                $issue_data = ErpPqItem::with(['header'])->whereIn('pq_header_id', $docs->pluck('id'))->orderByDesc('id')->get();
                $issue_item_ids = $issue_data -> pluck('id');
                // Fetch corresponding return data
                $return_data = ErpMrItem::whereIn('pq_item_id', $issue_item_ids)
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
                            $used = $return_data->where('pq_item_id', $row->id)
                                ->filter(function ($return) {
                                    return $return->attributes->contains(function ($attr) {
                                        return $attr->attribute_name == 'TYPE' && $attr->attribute_value == 'RETURN OLD';
                                    });
                                })
                                ->pluck('qty')
                                ->sum();

                            $returned = $return_data->where('pq_item_id', $row->id)
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
        return view('pq.report',['requesters'=>$requesters]);
    }
    public function pullItems($doc)
    {
         // Now manually load paginated items (25 per page)
         $items = ErpPqItem::with([
            'item.specifications',
            'item.alternateUoms.uom',
            'item.uom',
            'item.hsn',
        ])
        ->where('pq_header_id', $doc->id)
        ->paginate(25); // Laravel paginator

        $items->getCollection()->transform(function ($item) {
            $item->attributes_array = $item->get_attributes_array();
            return $item;
        });
    // Pass $items separately to the view or return response accordingly
            return $items;
    }

    public function getRfqItemForPulling(Request $request)
    {
        try {
            $selectedIds = $request->input('selected_ids', []);
            $docType = $request->input('doc_type');
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);

            // Only handle RFQ type for now
            if ($docType !== ConstantHelper::RFQ_SERVICE_ALIAS) {
                return response()->json([
                    'message' => 'Invalid document type for this operation.',
                ], 400);
            }

            // Build the base query for RFQ headers
            $query = ErpRfqHeader::with(['book', 'store', 'sub_store'])
                ->whereIn('document_status', [
                    ConstantHelper::APPROVED,
                    ConstantHelper::APPROVAL_NOT_REQUIRED
                ])
                ->whereIn('book_id', $applicableBookIds);

            if (!empty($selectedIds)) {
                $query->whereIn('id', $selectedIds);
            }
            if ($request->book_id) {
                $query->where('book_id', $request->book_id);
            }
            if ($request->document_id) {
                $query->where('id', $request->document_id);
            }
            if ($request->item_id) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('item_id', $request->item_id);
                });
            }

            return DataTables::of($query)
                ->addColumn('book_code', fn($header) => $header->book?->book_code ?? '')
                ->addColumn('document_number', fn($header) => $header->document_number)
                ->addColumn('document_date', fn($header) => $header->getFormattedDate("document_date"))
                ->addColumn('rfq_no', fn($header) => $header->book?->book_code . '-' . $header->document_number)
                ->addColumn('store_location_code', fn($header) => $header->store_location?->store_name ?? '')
                ->addColumn('sub_store_code', fn($header) => $header->sub_store?->name ?? '')
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
        $rfqItemIds = $request->input('items_id', []);
        if (empty($rfqItemIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No RFQ item IDs provided.',
            ], 422);
        }

        $rfqItems = ErpRfqHeader::with(['items.item.hsn' ,'items.attributes', 'items.uom', 'items.header'])
            ->whereIn('id', $rfqItemIds)
            ->get()
            ->flatMap(function ($header) {
            return $header->items;
            });
            $itemsData = $rfqItems->map(function ($item) {
                return [
                    'id'                  => $item->id,
                    'item_id'             => $item->item_id,
                    'item' => [
                        'item_code'        => $item->item?->item_code,
                        'item_name'        => $item->item?->item_name,
                        'hsn'              => ['code' => $item->item?->hsn?->code],
                        'specifications'   => $item->item?->specifications,
                        'uom'              => ['id' => $item->uom_id, 'alias' => $item->uom_code],
                    ],
                    'vendors'              => $item->header->vendors()->toArray(),
                    'uom_id'               => $item->uom_id,
                    'item_attributes_array'=> $item->item_attributes_array(),
                    'pq_balance_qty'       => $item->pq_balance_qty,
                    'remarks'              => null,
                    'header_ids'           => [$item->header->id],
                    'item_ids'             => [$item->id],
                ];
            });

            // STEP: collect and flatten all vendors from headers
            $allVendors = $rfqItems
                ->pluck('header')                   // extract headers
                ->flatMap(fn($header) => $header->vendors()) // collect all vendors from each header
                ->unique('id')                      // ensure uniqueness by vendor ID
                ->values();                         // reset keys

            return response()->json([
                'status' => 'success',
                'data'   => [
                    [
                        'items'   => $itemsData,
                        'vendors' => $allVendors,
                    ]
                ],
            ]);
    }

    public function mail(Request $request)
    {
        $pq = ErpPqHeader::find($request->id);
        $suppliers = $pq->suppliers ?? null; // Ensure it's a collection
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

            $pdfFileName = "Request_for_quotation_{$pq->document_number}.pdf";
            $attachments[] = [
                'file' => $pdfContent,
                'options' => [
                    'as' => $pdfFileName,
                    'mime' => 'application/pdf',
                ]
            ];
        } catch (\Exception $e) {
            \Log::error("Failed to generate PQ PDF: " . $e->getMessage());
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
    public function getVendorAddresses(Request $request, string $vendorId)
    {
        try {
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                return response()->json([
                    'data' => array(
                        'error_message' => 'Vendor not found'
                    )
                ]);
            }
        // if ($vendor -> vendor_type === ConstantHelper::CASH) {
        //     $phoneNo = $request -> phone_no ?? null;
        //     $cashCustomerDetail = CashCustomerDetail::where('phone_no', $phoneNo) -> first();
        //     $billingAddresses = ErpAddress::where('addressable_id', $cashCustomerDetail ?-> id)->where('addressable_type', CashCustomerDetail::class)->whereIn('type', ['billing', 'both'])->get();
        //     $shippingAddresses = ErpAddress::where('addressable_id', $cashCustomerDetail ?-> id)->where('addressable_type', CashCustomerDetail::class)->whereIn('type', ['shipping', 'both'])->get();
        // } else {
            $billingAddresses = ErpAddress::where('addressable_id', $vendorId)->where('addressable_type', Vendor::class)
            ->where(function ($subQuery) {
                $subQuery -> whereIn('type', ['billing', 'both']) -> orWhere('is_billing', 1);
            }) -> latest() -> get();
            $shippingAddresses = ErpAddress::where('addressable_id', $vendorId)->where('addressable_type', Vendor::class)
            ->where(function ($subQuery) {
                $subQuery -> whereIn('type', ['shipping', 'both']) -> orWhere('is_shipping', 1);
            }) -> latest() -> get();
            // }
            foreach ($billingAddresses as $billingAddress) {
                $billingAddress->value = $billingAddress->id;
                $billingAddress->label = $billingAddress->display_address;
            }
            if ($vendor -> vendor_sub_type === ConstantHelper::REGULAR) {
                if (count($billingAddresses) == 0) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Vendor Address not found for ' . $vendor?->company_name
                        )
                    ]);
                }
                if (!isset($vendor?->currency_id)) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Currency not found for ' . $vendor?->company_name
                        )
                    ]);
                }
                if (!isset($vendor?->payment_terms_id)) {
                    return response()->json([
                        'data' => array(
                            'error_message' => 'Payment Terms not found for ' . $vendor?->company_name
                        )
                    ]);
                }
            }
            //Currency Helper
            $currencyData = CurrencyHelper::getCurrencyExchangeRates($vendor?->currency_id ?? 0, $request->document_date ?? '');
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
    public function addAddress(Request $request)
    {
        try {
            $vendor = Vendor::find($request -> vendor_id);
            if (!isset($vendor)) {
                return response()->json([
                    'message' => 'Vendor Not found',
                    'error' => 'Vendor not found'
                ], 500);
            }
            $address = ErpAddress::where([
                ['addressable_id', $request->vendor_id],
                ['addressable_type', Vendor::class],
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
                'addressable_id' => $request->vendor_id,
                'addressable_type' => Vendor::class,
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

}
