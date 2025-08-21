<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\TransactionReportHelper;
use App\Helpers\UserHelper;
use App\Models\Address;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\ErpMiItem;
use App\Models\ErpPickupDynamicField;
use App\Models\ErpPickupItem;
use App\Models\ErpPickupItemAttribute;
use App\Models\ErpPickupSchedule;
use App\Models\ErpPickupScheduleHistory;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\Organization;
use App\Models\Vendor;
use Carbon\Carbon;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Storage;
use Yajra\DataTables\DataTables;

class ErpPdsController extends Controller
{
    //
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = ConstantHelper::PDS_SERVICE_ALIAS;
        $redirectUrl = route('pds.index');
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $createRoute = route('pds.create');
        $typeName = ConstantHelper::SERVICE_LABEL[ConstantHelper::PDS_SERVICE_ALIAS];
        $autoCompleteFilters = self::getBasicFilters();
        
        if ($request -> ajax()) {
            try {
            $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
            $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
            //Date Filters
            $dateRange = $request -> date_range ??  null;
            $docs = ErpPickupSchedule::withDefaultGroupCompanyOrg()
                ->bookViewAccess($pathUrl)
                ->withDraftListingLogic()
                ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']])
                ->whereIn('store_id', $accessible_locations)
                ->when($request->customer_id, function ($custQuery) use ($request) {
                    $custQuery->where('customer_id', $request->customer_id);
                })
                ->when($request->book_id, function ($bookQuery) use ($request) {
                    $bookQuery->where('book_id', $request->book_id);
                })
                ->when($request->document_number, function ($docQuery) use ($request) {
                    $docQuery->where('document_number', 'LIKE', '%' . $request->document_number . '%');
                })
                ->when($request->from_location_id, function ($docQuery) use ($request) {
                    $docQuery->where('store_id', $request->from_location_id);
                })
                ->when($request->to_location_id, function ($docQuery) use ($request) {
                    $docQuery->where('to_store_id', $request->to_location_id);
                })
                ->when($request->company_id, function ($docQuery) use ($request) {
                    $docQuery->where('company_id', $request->company_id);
                })
                ->when($request->organization_id, function ($docQuery) use ($request) {
                    $docQuery->where('organization_id', $request->organization_id);
                })
                ->when($request->status, function ($docStatusQuery) use ($request) {
                    $searchDocStatus = [];
                    if ($request->status === ConstantHelper::DRAFT) {
                        $searchDocStatus = [ConstantHelper::DRAFT];
                    } else if ($request->status === ConstantHelper::SUBMITTED) {
                        $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
                    } else {
                        $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
                    }
                    $docStatusQuery->whereIn('document_status', $searchDocStatus);
                })
                ->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
                    $dateRanges = explode('to', $dateRange);
                    if (count($dateRanges) == 2) {
                        $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                        $toDate = Carbon::parse(trim($dateRanges[1]))->format('Y-m-d');
                        $dateRangeQuery->whereDate('document_date', ">=", $fromDate)->where('document_date', '<=', $toDate);
                    } else {
                        $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                        $dateRangeQuery->whereDate('document_date', $fromDate);
                    }
                })
                ->when($request->item_id, function ($itemQuery) use ($request) {
                    $itemQuery->withWhereHas('items', function ($itemSubQuery) use ($request) {
                        $itemSubQuery->where('item_id', $request->item_id)
                            //Compare Item Category
                            ->when($request->item_category_id, function ($itemCatQuery) use ($request) {
                                $itemCatQuery->whereHas('item', function ($itemRelationQuery) use ($request) {
                                    $itemRelationQuery->where('category_id', $request->category_id)
                                        //Compare Item Sub Category
                                        ->when($request->item_sub_category_id, function ($itemSubCatQuery) use ($request) {
                                            $itemSubCatQuery->where('subcategory_id', $request->item_sub_category_id);
                                        });
                                });
                            });
                    });
                })
                ->orderByDesc('id');

            return DataTables::of($docs)
                ->addIndexColumn() // S.No
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? 'N/A'; // Date
                })
                ->addColumn('series', function ($row) {
                    return $row->book_code ?? 'N/A'; // Series
                })
                ->addColumn('doc_no', function ($row) {
                    return $row->doc_no ?? $row->document_number ?? 'N/A'; // Doc No.
                })
                ->addColumn('trip_id', function ($row) {
                    return $row->trip_no ?? 'N/A'; // Trip Id
                })
                ->addColumn('champ', function ($row) {
                    return $row->champ ?? 'N/A'; // Champ
                })
                ->addColumn('vehicle_no', function ($row) {
                    return $row->vehicle_no ?? 'N/A'; // Vehicle No.
                })
                ->addColumn('location', function ($row) {
                    return $row->store_code ?? 'N/A'; // Location
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number); // Rev No
                })
                ->addColumn('items', function ($row) {
                    return $row->pickupItems->count(); // Items
                })
                ->editColumn('document_status', function ($row) use ($orderType) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];
                    $displayStatus = $row->display_status;
                    $editRoute = route('pds.edit', ['id' => $row->id]);
                    return "
                        <div style='text-align:center;'>
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
                ->rawColumns(['document_status'])
                ->make(true);
            }catch (Exception $ex) {
                return response() -> json([
                    'message' => $ex -> getMessage()
                ]);
            }
        }
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        $create_button = (isset($servicesBooks['services'])  && count($servicesBooks['services']) > 0 && isset($selectedfyYear['authorized']) && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        return view('pds.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::SO_SERVICE_ALIAS],
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
        $redirectUrl = route('pds.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = ConstantHelper::PDS_SERVICE_ALIAS;
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
            'suppliers' => $vendors,
            'redirect_url' => $redirectUrl,
            'stockTypes' => $stockTypes,
        ];
        return view('pds.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('pds.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpPickupScheduleHistory::with(['book']) -> with('pickupItems', function ($query) {
                    $query ->  with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> where('source_id', $id)->first();
                $ogDoc = ErpPickupSchedule::find($id);
            } else {
                $doc = ErpPickupSchedule::with(['book']) -> with('pickupItems', function ($query) {
                    $query -> with(['item' => function ($itemQuery) {
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
            $books = Helper::getBookSeriesNew(ConstantHelper::PDS_SERVICE_ALIAS, ) -> get();
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
            $typeName = ConstantHelper::PDS_SERVICE_ALIAS;
            $vendors = Vendor::withDefaultGroupCompanyOrg()->where('id', $doc -> vendor_id) -> get();
            foreach ($doc -> pickupItems as $docItem) {
                $docItem -> max_qty_attribute = 9999999;
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
                'items' => $doc->pickupItems,
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
            return view('pds.create_edit', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage(),$ex->getLine(),$ex->getFile());
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
            $isUpdate = $request->pickup_header_id ? true : false;
    
            if (!$isUpdate) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ?? $request->document_no;
                $regeneratedDocExist = ErpPickupSchedule::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                if ($regeneratedDocExist) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
            $store = ErpStore::find($request -> store_id);
            if ($store && isset($store -> address)) {
                $companyCountryId = $store->address?->country_id??null;
                $companyStateId = $store->address?->state_id??null;
            } else {
                return response()->json([
                    'message' => 'Please create an organization first'
                ], 422);
            }
            $customer = Customer::whereIn('id',$request->item_customer_id ?? [])->get()->toArray();
            
            //Seperate array to store each item calculation
            $itemsData = array();
            $pqExists = ErpPickupSchedule::where('id', $request->pickup_id)
                    ->where('trip_no', $request->trip_no)
                    ->exists();

                if ($pqExists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Trip already present.",
                        'status'  => 'error'
                    ], 500);
                }

            if ($isUpdate) {
                // If it's an update, we need to find the existing PQ header
                $pq = ErpPickupSchedule::find($request->pickup_header_id);
                $pq->document_date = $request->document_date;
                $pq->instructions = $request->instructions;
                $store = ErpStore::find($request->store_id);
                $pq->store_id = $request->store_id ?? ($pq->store_id ?? null);
                $pq->store_code = $store->store_code ?? ($pq->store_code ?? null);
                $pq->remark = $request->final_remarks;
                $actionType = $request->action_type ?? '';

                if (($pq->document_status == ConstantHelper::APPROVED || $pq->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpPickupSchedule', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpPickupDynamicField', 'relation_column' => 'pickup_header_id'],
                        ['model_type' => 'detail', 'model_name' => 'ErpPickupItem', 'relation_column' => 'pickup_header_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpPickupItemAttribute', 'relation_column' => 'pickup_item_id'],
                        // Add more if you have sub-details like deliveries, etc.
                    ];
                    Helper::documentAmendment($revisionData, $pq->id);
                }

                $keys = ['deletedSiItemIds', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
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
                    $pqItems = ErpPickupItem::whereIn('id', $deletedData['deletedSiItemIds'])->get();
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
                $pq = ErpPickupSchedule::create([
                    'organization_id'   => $organizationId,
                    'group_id'          => $groupId,
                    'company_id'        => $companyId,
                    'book_id'           => $request->book_id,
                    'book_code'         => $request->book_code,
                    'store_id'          => $request->store_id,
                    'store_code'        => $store?->store_name,
                    'doc_number_type'   => $numberPatternData['type'] ?? null,
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'] ?? null,
                    'doc_prefix'        => $numberPatternData['prefix'] ?? null,
                    'doc_suffix'        => $numberPatternData['suffix'] ?? null,
                    'doc_no'            => $numberPatternData['doc_no'] ?? null,
                    'document_number'   => $document_number,
                    'document_date'     => $request->document_date,
                    'due_date'          => $request->due_date ?? null,
                    'document_status'   => ConstantHelper::DRAFT,
                    'revision_number'   => 0,
                    'approval_level'    => 1,
                    'reference_number'  => $request->reference_number ?? null,
                    'trip_no'           => $request->trip_no ?? null,
                    'vehicle_no'        => $request->vehicle ?? null,
                    'champ'             => $request->champ ?? null,
                    'total_item_count'  => $request->item_count ?? ($request->item_id ? count($request->item_id) : 0),
                    'instructions'      => $request->instructions,
                    'remark'            => $request->final_remarks,
                    'created_by'        => $user->auth_user_id ?? null,
                ]);
            }
            $pq -> save();
            
            if ($request->item_id && count($request->item_id) > 0) {
                foreach ($request->item_id as $itemKey => $itemId) {
                    $item = Item::find($itemId);
                    if ($item) {
                        $pickupItemData = [
                            'pickup_schedule_id' => $pq->id,
                            'item_id' => $item->id,
                            'item_code' => $request->item_code[$itemKey] ?? $item->item_code,
                            'item_name' => $request->item_name[$itemKey] ?? $item->item_name,
                            'uom_id' => $request->uom_id[$itemKey] ?? $item->uom_id,
                            'uom_code' => $item->uom ? $item->uom->name : null,
                            'customer_id' => $request->item_customer_id[$itemKey] ?? null,
                            'customer_name' => $request->item_customer_name[$itemKey] ?? null,
                            'customer_email' => $request->item_email[$itemKey] ?? null,
                            'customer_phone' => $request->item_mobile[$itemKey] ?? null,
                            'uid' => $request->item_uid[$itemKey] ?? null,
                            'delivery_cancelled' => (isset($request->item_delivery_cancelled[$itemKey]) && $request->item_delivery_cancelled[$itemKey]) ? 'Yes' : 'No',
                            'type' => $request->item_type[$itemKey] ?? null,
                            'qty' => $request->item_qty[$itemKey] ?? 0,
                            'remarks' => $request->item_remarks[$itemKey] ?? null,
                            'created_by' => $user->auth_user_id ?? null,
                        ];

                        // If updating, use updateOrCreate, else create
                        if (isset($request->pickup_item_id[$itemKey])) {
                            $pickupItem = ErpPickupItem::updateOrCreate(
                                ['id' => $request->pickup_item_id[$itemKey]],
                                $pickupItemData
                            );
                        } else {
                            $pickupItem = ErpPickupItem::create($pickupItemData);
                        }

                        // Handle item attributes
                        if (isset($request->item_attributes[$itemKey])) {
                            $attributesArray = json_decode($request->item_attributes[$itemKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($attributesArray)) {
                                foreach ($attributesArray as $attribute) {
                                    $attributeVal = "";
                                    $attributeValId = null;
                                    foreach ($attribute['values_data'] as $valData) {
                                        if ($valData['selected']) {
                                            $attributeVal = $valData['value'];
                                            $attributeValId = $valData['id'];
                                            break;
                                        }
                                    }
                                    ErpPickupItemAttribute::updateOrCreate(
                                        [
                                            'pickup_id' => $pq->id,
                                            'pickup_item_id' => $pickupItem->id,
                                            'item_attribute_id' => $attribute['id'],
                                        ],
                                        [
                                            'item_code' => $pickupItem->item_code,
                                            'attribute_name' => $attribute['group_name'],
                                            'attr_name' => $attribute['attribute_group_id'],
                                            'attribute_value' => $attributeVal,
                                            'attr_value' => $attributeValId,
                                            'created_by' => $user->auth_user_id ?? null,
                                        ]
                                    );
                                }
                            } else {
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Item No. ' . ($itemKey + 1) . ' has invalid attributes',
                                    'error' => ''
                                ], 422);
                            }
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
             
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approvalLogic = self::handleApprovalLogic($request, $pq);
            }
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $pq->uploadDocuments($file, 'pickup_schedule', false);
                }
            }

            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpPickupDynamicField::class, $pq -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            DB::commit();
            return response()->json([
                'message' => "Pickup Schedule created successfully",
                'redirect_url' => route('pds.index')
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex->getLine() . ' in ' . $ex->getFile(),
            ], 500);
        }
    }
    
    private function handleApprovalLogic(Request $request, $pq)
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

    public function revoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpPickupSchedule::find($request -> id);
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
        $rfq = ErpPickupSchedule::with([
            'store',
            'book',
            'pickupItems.item.specifications',
            'pickupItems.item.alternateUoms.uom',
            'pickupItems.item.uom',
        ])
        ->find($id);

        // Add item_attributes to each item
        if ($rfq && $rfq->pickupItems) {
            foreach ($rfq->pickupItems as $item) {
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

        $html = view('pds.pds', [
            'pds' => $rfq,
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
        $pdfPath = 'pds/pdfs/pds_' . $fileName . '.pdf';

        Storage::disk('local')->put($pdfPath, $dompdf->output());

        if ($download) {
            return $dompdf->stream($fileName . '.pdf', ['Attachment' => true]);
        }

        if ($returnRaw) {
            return $dompdf->output();
        }

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Pickup_Schedule_' . $fileName . '.pdf"');
    }
    
}
