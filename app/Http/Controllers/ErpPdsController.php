<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\TransactionReportHelper;
use App\Helpers\UserHelper;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Country;
use App\Models\ErpMiItem;
use App\Models\ErpPickupSchedule;
use App\Models\ErpPickupScheduleHistory;
use App\Models\Item;
use App\Models\Vendor;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
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
                    return $row->items->count(); // Items
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
                $doc = ErpPickupScheduleHistory::with(['book']) -> with('items', function ($query) {
                    $query -> with(['item_locations','department','user']) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> where('source_id', $id)->first();
                $ogDoc = ErpPickupSchedule::find($id);
            } else {
                $doc = ErpPickupSchedule::with(['book']) -> with('items', function ($query) {
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
            foreach ($doc -> items as $docItem) {
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
            dd($ex -> getMessage());
        }
    }

    public function store(Request $request)
    {
        return true;
    }
    
}
