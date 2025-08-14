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
use App\Models\ErpPqcHeader;
use App\Models\ErpPqcHeaderHistory;
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


class ErpPqcController extends Controller
{
    //
    public function index(Request $request)
    {
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());

        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PQC_SERVICE_ALIAS;
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $redirectUrl = route('pqc.index');
        $createRoute = route('pqc.create');
        $typeName = "Purchase Quotation Comparison";
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
                $docs = ErpPqcHeader::withDefaultGroupCompanyOrg() -> whereIn('store_id',$accessible_locations) -> bookViewAccess($pathUrl) ->  withDraftListingLogic()->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) 
                -> when($request -> book_id, function ($bookQuery) use($request) {
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
                }) -> orderByDesc('id');
                return DataTables::of($docs) ->addIndexColumn()
                ->editColumn('document_status', function ($row) use($orderType) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                    $displayStatus = $row -> display_status;
                    $editRoute = route('pqc.edit', ['id' => $row -> id]); 
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
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
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
        return view('pqc.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl,'create_route' => $createRoute, 'create_button' => $create_button, 'filterArray' => pqReportHelper::PQ_FILTERS,
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
        $redirectUrl = route('pqc.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::PQC_SERVICE_ALIAS;
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
        return view('pqc.create_edit', $data);
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
                ->whereNull('selected_vendor')
                ->whereNull('selected_pq')
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

        $rfqItems = ErpRfqHeader::with([
            'items.item.hsn',
            'items.attributes',
            'items.uom',
            'pqs.items', // PQs and their items
            'pqs.suppliers', // PQ vendor details
            'items.header'
        ])
        ->whereIn('id', $rfqItemIds)
        ->get()
        ->flatMap(function ($header) {
            return $header->items->map(function ($item) use ($header) {
            $pqVendorRates = [];
            foreach ($header->pqs as $pq) {
                $pqItem = $pq->items->where('rfq_item_id',$item->id)->first();
                if ($pqItem) {
                $vendor = $pq->suppliers;
                $pqVendorRates[] = [
                    'vendor' => $vendor ? [
                    'id' => $vendor->id,
                    'pq_id' => $pq->id,
                    'company_name' => $vendor->company_name,
                    'email' => $vendor->email,
                    // Add other vendor fields as needed
                    'rate' => $pqItem->rate ?? null,
                    ] : null,
                ];
                }
            }
            $item->pq_vendor_rates = $pqVendorRates;
            return $item;
        });
    });
        $itemsData = $rfqItems->map(function ($item) {
            return [
                'id'                  => $item->id,
                'item_id'             => $item->rfq_header_id,
                'item' => [
                    'id'               => $item->item->id,
                    'item_code'        => $item->item?->item_code,
                    'item_name'        => $item->item?->item_name,
                    'hsn'              => ['code' => $item->item?->hsn?->code],
                    'specifications'   => $item->item?->specifications,
                    'uom'              => ['id' => $item->uom_id, 'alias' => $item->uom_code],
                    'item_attributes_array' => $item->item_attributes_array(),
                ],
                'pq_balance_qty'       => $item->request_qty,
                'vendor_data'          => $item->pq_vendor_rates,
            ];
        })->values();
        return response()->json([
            'status' => 'success',
            'data'   => [['items' => $itemsData]],
        ]);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $currentfyYear = Helper::getCurrentFinancialYear();
            $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('pqc.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpPqcHeaderHistory::with(['book','media_files','suppliers'])
                ->bookViewAccess($parentUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()
                -> where('source_id', $id)->where('revision_number', $request->revisionNumber)->firstOrFail();
        
            $ogDoc = ErpPqcHeader::find($id) -> bookViewAccess($parentUrl) -> withDefaultGroupCompanyOrg() 
                -> withDraftListingLogic() -> firstOrFail();
            } else {
                $doc = ErpPqcHeader::with(['book','media_files','suppliers'])-> where('id', $id) ->bookViewAccess($parentUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic()->firstOrFail();
                $ogDoc = $doc;
            }
            $stores = InventoryHelper::getAccessibleLocations([ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias);
            }            
            $totalValue = 0;
            $suppliers = Vendor::select('id', 'company_name','email') -> withDefaultGroupCompanyOrg() 
            -> where('status', ConstantHelper::ACTIVE) -> get();
            $items = self::pullItems($doc);
            $revision_number = $doc->revision_number;
            $selectedfyYear = Helper::getFinancialYear($doc->document_date ?? Carbon::now()->format('Y-m-d'));
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
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $ogDoc->id, $revNo, 0, $doc -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
            $typeName = "Purchase Quotation Comparison";
            $stations = Station::withDefaultGroupCompanyOrg()
            ->where('status', ConstantHelper::ACTIVE)
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
            return view('pqc.create_edit', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage(),$ex -> getLine());
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

            $isUpdate = $request->pqc_header_id ? true : false;
            
            if (!$isUpdate) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ?? $request->document_no;
                $regeneratedDocExist = ErpPqcHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                if ($regeneratedDocExist) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }

            $store = ErpStore::find($request->store_id);
            if ($store && isset($store->address)) {
                $companyCountryId = $store->address?->country_id ?? null;
                $companyStateId = $store->address?->state_id ?? null;
            }
            if($request->filled('vendor_radio'))
            {
                $vendor =  Vendor::find($request->vendor_radio);
            }
            else
            {
                return response()->json(['message' => 'Please Select a Vendor.',
                    'error' => "Error",
                ], 500);
            }
            $pq = request()->filled('pq_id') ? ErpPqHeader::with(['rfq'])->where('vendor_id',$request->vendor_radio)->where('rfq_id',$request->rfq_ids)->first() : null; 
            $store = ErpStore::find($request->store_id ?? null);
            if ($isUpdate) {
                $pqc = ErpPqcHeader::find($request->pqc_header_id);
                $store = ErpStore::find($request->store_id);
                $actionType = $request->action_type ?? '';

                if (($pqc->document_status == ConstantHelper::APPROVED || $pqc->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpPqcHeader', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpPqcDynamicField', 'relation_column' => 'pqc_header_id'],
                    ];
                    Helper::documentAmendment($revisionData, $pqc->id);
                }

                $pqc->fill([
                    'document_date' => $request->document_date,
                    'instructions' => $request->instructions,
                    'store_id' => $request->store_id,
                    'store_code' => $store->store_code ?? null,
                    'selected_vendor' => $request->vendor_radio ?? ($pqc->selected_vendor ?? null),
                    'selected_pq' => $request->pq_id ?? ($pqc->selected_pq ?? null),
                    'vendor_email' => $request->vendor_email ?? ($pqc->vendor_email ?? null),
                    'vendor_phone' => $request->vendor_phone_no ?? ($pqc->vendor_phone ?? null),
                    'remark' => $request->final_remarks,
                    'updated_by' => $user->auth_user_id,
                ])->save();

            } else {
                $pqc = new ErpPqcHeader();
                $pqc->fill([
                    'rfq_id' => $pq?->rfq?->id ?? null,
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'store_id' => $request->store_id,
                    'store_code' => $store->store_code ?? ($request->store_code ?? null),
                    'sub_store_id' => $request->sub_store_id ?? null,
                    'sub_store_code' => $request->sub_store_code ?? null,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_number' => $document_number,
                    'document_date' => $request->document_date,
                    'due_date' => $request->due_date ?? null,
                    'document_status' => $request->document_status ?? ConstantHelper::DRAFT,
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
                    'instructions' => $request->instructions,
                    'remark' => $request->final_remarks ?? null,
                    'selected_vendor' => $request->vendor_radio,
                    'selected_pq' => $pq?->id, // Assigned after save
                    'vendor_email' => $request->vendor_email ?? null,
                    'vendor_phone' => $request->vendor_phone_no ?? null,
                    'created_by' => $user->auth_user_id,
                    'updated_by' => $user->auth_user_id,
                ]);
                $pqc->save();
            }

            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approvalLogic = self::handleApprovalLogic($request, $pqc);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $pqc->uploadDocuments($file, 'pqc_header', false);
                }
            }

            $status = DynamicFieldHelper::saveDynamicFields(ErpPqcDynamicField::class, $pqc->id, $request->dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response()->json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            $rfq = ErpRfqHeader::find($pq?->rfq?->id);
            $rfq->selected_pq = $pq?->id;
            $rfq->selected_vendor = $pq->vendor_id;
            $rfq->save();
            DB::commit();
            return response()->json([
                'message' => "Purchase Quotation Compared successfully",
                'redirect_url' => route('pqc.index')
            ]);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex->getLine() . ' in ' . $ex->getFile(),
            ], 500);
        }
    }

    public function pullItems($doc)
    {
        $items = ErpRfqItem::with([
            'item',
            'item.specifications',
            'item.alternateUoms.uom',
            'item.uom',
            'item.hsn',
            'piItem',
        ])
        ->where('rfq_header_id', $doc->rfq_id) // ✅ Corrected from 'pq_header_id' to 'id'
        ->get();

        $items->transform(function ($item) {
            $item->attributes_array = $item->get_attributes_array(); // assumes this method exists on the model
            return $item;
        });

        return $items;
    }

    
    private function handleApprovalLogic(Request $request, ErpPqcHeader $pq)
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

    public function revokePqc(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpPqcHeader::find($request -> id);
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
    
}
