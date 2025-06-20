<?php

namespace App\Http\Controllers;
use App\Helpers\GstInvoiceHelper;
use App\Helpers\TransactionReportHelper;
use App\Jobs\SendEmailJob;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ErpInvoiceItemPacket;
use App\Models\EwayBillMaster;
use App\Models\PackingListDetail;
use App\Models\PackingListItem;
use Dompdf\Dompdf;
use App\Helpers\PackingList\Constants as PackingListConstants;
use App\Helpers\DynamicFieldHelper;
use App\Models\ErpSiDynamicField;
use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Http\Requests\ErpSaleInvoiceRequest;
use App\Models\Country;
use App\Models\Address;
use App\Models\DiscountMaster;
use App\Models\EmployeeBookMapping;
use App\Models\ErpAddress;
use App\Models\ErpAttribute;
use App\Models\ErpInvoiceItem;
use App\Models\ErpInvoiceItemAttribute;
use App\Models\ErpInvoiceItemLocation;
use App\Models\ErpItemAttribute;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleInvoiceHistory;
use App\Models\ErpSaleInvoiceTed;
use App\Models\ErpSaleOrder;
// use App\Models\ErpSoDnMapping;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\LandLease;
use App\Models\LandLeaseScheduler;
use App\Models\LandParcel;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\OrganizationBookParameter;
use App\Models\OrganizationMenu;
use App\Models\Unit;
use Carbon\Carbon;
use DB;
use Dompdf\Options;
use App\Models\CashCustomerDetail;
use Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PDF;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use stdClass;

class ErpSaleInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        if ($pathUrl === 'sale-invoices') {
            $orderType = SaleModuleHelper::SALES_INVOICE_DEFAULT_TYPE;
            $redirectUrl = route('sale.invoice.index');
            $createRoute = route('sale.invoice.create');
        }
        if ($pathUrl === 'lease-invoices') {
            $orderType = SaleModuleHelper::SALES_INVOICE_LEASE_TYPE;
            $redirectUrl = route('sale.leaseInvoice.index');
            $createRoute = route('sale.leaseInvoice.create');
        }
        $typeName = SaleModuleHelper::getAndReturnInvoiceTypeName($orderType);
        request() -> merge(['type' => $orderType]);
        // $type = SaleModuleHelper::getAndReturnInvoiceType($request -> type ?? '');
        $autoCompleteFilters = self::getBasicFilters();
        if ($request -> ajax()) {
            try {
            $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
            $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
            //Date Filters
            $dateRange = $request -> date_range ??  null;
            $invoices = ErpSaleInvoice::withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> bookViewAccess($pathUrl) ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]) -> whereIn('store_id',$accessible_locations) ->  when($request -> customer_id, function ($custQuery) use($request) {
                $custQuery -> where('customer_id', $request -> customer_id);
            }) -> when($request -> book_id, function ($bookQuery) use($request) {
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
            })  -> orderByDesc('id');
            return DataTables::of($invoices) ->addIndexColumn()
            ->editColumn('document_status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = $row -> display_status;
                if ($orderType == SaleModuleHelper::SALES_INVOICE_DEFAULT_TYPE) {
                    $editRoute = route('sale.invoice.edit', ['id' => $row->id]);
                }
                if ($orderType == SaleModuleHelper::SALES_INVOICE_LEASE_TYPE) {
                    $editRoute = route('sale.leaseInvoice.edit', ['id' => $row->id]);
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
            ->addColumn('document_type', function ($row) {
                return SaleModuleHelper::getServiceName($row -> book_id);
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
                return number_format($row->total_tax_value,2);
            })
            ->editColumn('total_expense_value', function ($row) {
                return number_format($row->total_expense_value,2);
            })
            ->editColumn('grand_total_amount', function ($row) {
                return number_format($row->total_amount,2);
            })
            ->editColumn('e_invoice_status', function ($row) {
                return ucfirst($row->e_invoice_status ?? " ");
            })
            ->editColumn('delivery_status', function ($row) {
                return ucfirst($row->delivery_status ? 'Delivered' : 'Pending');
            })
            ->editColumn('is_ewb_generated', function ($row) {
                return ucfirst($row->total_amount > EInvoiceHelper::EWAY_BILL_MIN_AMOUNT_LIMIT && $row -> irnDetail ? ($row -> is_ewb_generated ? 'Generated' : 'Pending') : '');
            })
            // ->addColumn('action', function ($row) use($type) {
            //     return '
            //         <div class="dropdown">
            //             <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
            //                 <i data-feather="more-vertical"></i>
            //             </button>
            //             <div class="dropdown-menu dropdown-menu-end">
            //                 <a class="dropdown-item" href="' . route('sale.invoice.edit', ['id' => $row->id, 'type' => $type]) . '">
            //                     <i data-feather="edit-3" class="me-50"></i>
            //                     <span>View/ Edit Detail</span>
            //                 </a>
            //             </div>
            //         </div>';
            // })
            ->rawColumns(['document_status'])
            ->make(true);
            }
            catch (Exception $ex) {
                return response() -> json([
                    'message' => $ex -> getMessage() . " in " . $ex -> getFile() . " at Line No " . $ex -> getLine()
                ]);
            }
        }
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        $create_button = (isset($servicesBooks['services'])  && count($servicesBooks['services']) > 0 && isset($selectedfyYear['authorized']) && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        return view('salesInvoice.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'create_button' => $create_button,'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::SI_SERVICE_ALIAS],
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
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $redirectUrl = route('sale.invoice.index');
        $locationVisiblity = true;
        if ($parentURL === 'sale-invoices') {
            $orderType = SaleModuleHelper::SALES_INVOICE_DEFAULT_TYPE;
            $redirectUrl = route('sale.invoice.index');
            $locationVisiblity = true;
        }
        if ($parentURL === 'lease-invoices') {
            $orderType = SaleModuleHelper::SALES_INVOICE_LEASE_TYPE;
            $redirectUrl = route('sale.leaseInvoice.index');
            $locationVisiblity = false;
        }
        request() -> merge(['type' => $orderType]);
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $type = SaleModuleHelper::getAndReturnInvoiceType($request -> type ?? '');
        $users = AuthUser::where('organization_id', $user -> organization_id) -> where('status', ConstantHelper::ACTIVE) -> get();
        $request -> merge(['type' => $type]);
        $typeName = 'Sales Invoice';
        if ($type == ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
            $typeName = "Delivery Note";
        } else if ($type == ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
            $typeName = "Delivery Note CUM Invoice";
        } else if ($type == ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS) {
            $typeName = "Lease Invoice";
        }
        $books = [];
        $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $organization = Organization::where('id', $user->organization_id)->first();
        $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
        $data = [
            'user' => $user,
            'users' => $users,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => $books,
            'countries' => $countries,
            'type' => $type,
            'typeName' => $typeName,
            'stores' => $stores,
            'redirect_url' => $redirectUrl,
            'location_visibility' => $locationVisiblity,
            'current_financial_year' => $selectedfyYear,
            'transportationModes' => $transportationModes,
            'einvoice' => null
        ];
        return view('salesInvoice.create_edit', $data);
    }
    public function edit(Request $request, String $id)
    {
        try {
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('sale.invoice.index');
            $locationVisiblity = true;
            if ($parentUrl === 'sale-invoices') {
                $locationVisiblity = true;
                $orderType = SaleModuleHelper::SALES_INVOICE_DEFAULT_TYPE;
            }
            if ($parentUrl === 'lease-invoices') {
                $locationVisiblity = false;
                $orderType = SaleModuleHelper::SALES_INVOICE_LEASE_TYPE;
            }
            request() -> merge(['type' => $orderType]);
            $user = Helper::getAuthenticatedUser();
            $users = AuthUser::where('organization_id', $user -> organization_id) -> where('status', ConstantHelper::ACTIVE) -> get();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $order = ErpSaleInvoiceHistory::with(['customer','media_files','discount_ted', 'expense_ted', 'billing_address_details', 'shipping_address_details', 'location_address_details']) -> with('items', function ($query) {
                    $query -> with('discount_ted', 'tax_ted', 'item_locations', 'bundles') -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> where('source_id', $id)->first();
            } else {
                $order = ErpSaleInvoice::with(['customer','media_files','discount_ted', 'expense_ted', 'billing_address_details', 'shipping_address_details',  'location_address_details']) -> with('items', function ($query) {
                    $query -> with('discount_ted', 'tax_ted', 'item_locations', 'bundles') -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> find($id);
            }
            $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
            $organization = Organization::where('id', $user->organization_id)->first();
            if (isset($order)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$order -> book ?-> service ?-> alias);
                foreach ($order -> items as &$siItem) {
                    if (count($siItem -> bundles) > 0) {
                        $siItem -> disable_qty = true;
                    } else {
                        $siItem -> disable_qty = false;
                    }
                    if ($siItem -> so_item_id !== null) {
                        $pulled = ErpSoItem::find($siItem -> so_item_id);
                        if (isset($pulled)) {
                            $siItem -> max_attribute = $pulled -> order_qty;
                            $siItem -> is_editable = false;
                        } else {
                            $siItem -> max_attribute = 999999;
                            $siItem -> is_editable = true;
                        }
                    } else if ($siItem -> land_lease_id || $siItem -> land_schedule_id) {
                        $pulled = LandLease::find($siItem -> land_lease_id);
                        if (isset($pulled)) {
                            $siItem -> max_attribute = 999999;
                            $siItem -> is_editable = false;
                        } else {
                            $siItem -> max_attribute = 999999;
                            $siItem -> is_editable = true;
                        }
                    }
                    else {
                        // if (count($siItem -> mapped_so_item_ids()) > 0) {
                        //     $referenceItems = ErpSoItem::whereIn('id', $siItem -> mapped_so_item_ids()) -> get();
                        //     $maxAttribute = 0;
                        //     foreach ($referenceItems as $refItem) {
                        //         $maxAttribute += $refItem -> balance_qty;
                        //     }
                        //     $maxAttribute += $siItem -> order_qty;
                        //     $siItem -> max_attribute = $maxAttribute;
                        //     $siItem -> is_editable = false;
                        // } else {
                            $siItem -> max_attribute = 999999;
                            $siItem -> is_editable = true;
                        // }
                    }
                    $packingListDetail = $siItem -> packets;
                    if (isset($packingListDetail) && count($packingListDetail) > 0) {
                        $siItem -> package = $packingListDetail -> first() -> package_number;
                    }
                }
            }
           
            $revision_number = $order->revision_number;
            $totalValue = ($order -> total_item_value - $order -> total_discount_value) + $order -> total_tax_value + $order -> total_expense_value;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($order->book_id,$order->document_status , $order->id, $totalValue, $order->approval_level, $order -> created_by ?? 0, $userType['type'], $revision_number);
            $type = SaleModuleHelper::getAndReturnInvoiceType($request -> type);
            $request -> merge(['type' => $type]);
            $books = Helper::getBookSeriesNew($type) -> get();
            $countries = Country::select('id AS value', 'name AS label') -> where('status', ConstantHelper::ACTIVE) -> get();
            $revNo = $order->revision_number;
            if($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $order->revision_number;
            }
            $docValue = $order->total_amount ?? 0;
            $approvalHistory = Helper::getApprovalHistory($order->book_id, $order->id, $revNo, $docValue, $order -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$order->document_status] ?? '';
            $typeName = "Sales Invoice";
            if ($type == ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                $typeName = "Delivery Note";
            } else if ($type == ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
                $typeName = "Delivery Note CUM Invoice";
            } else if ($type == ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS) {
                $typeName = "Lease Invoice";
            }
            $editBundle = !in_array($order -> document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
            $einvoice = $order -> irnDetail() -> first();
            $enableEinvoice = ($order -> document_type === ConstantHelper::SI_SERVICE_ALIAS) ||
                $order -> document_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && !$order -> invoice_required;
            if ($order -> gst_invoice_type !== EInvoiceHelper::B2B_INVOICE_TYPE) {
                $enableEinvoice = false;
            }
            $subStores = InventoryHelper::getAccesibleSubLocations($order -> store_id);
            $transportationModes = EwayBillMaster::where('status', 'active')
                ->where('type', '=', 'transportation-mode')
                ->orderBy('id', 'ASC')
                ->get();
            $editTransporterFields = false;
            if (!isset($einvoice -> ewb_no) && $order -> total_amount > EInvoiceHelper::EWAY_BILL_MIN_AMOUNT_LIMIT) {
                $editTransporterFields = true;
            }
            $dynamicFieldsUI = $order -> dynamicfieldsUi();
            $selectedfyYear = Helper::getFinancialYear($order->document_date ?? Carbon::now()->format('Y-m-d'));

            $data = [
                'user' => $user,
                'users' => $users,
                'series' => $books,
                'order' => $order,
                'countries' => $countries,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'type' => $type,
                'editBundle' => $editBundle,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'stores' => $stores,
                'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($order -> media_files)) : 10,
                'services' => $servicesBooks['services'],
                'redirect_url' => $redirect_url,
                'location_visibility' => $locationVisiblity,
                'einvoice' => $einvoice,
                'enableEinvoice' => $enableEinvoice,
                'subStores' => $subStores,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'transportationModes' => $transportationModes,
                'current_financial_year' => $selectedfyYear,
                'editTransporterFields' => $editTransporterFields
            ];
            return view('salesInvoice.create_edit', $data);
           
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }

    public function store(ErpSaleInvoiceRequest $request)
    {
        try {
            //Reindex
            $request -> item_qty =  array_values($request -> item_qty);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id);
            $request -> item_discount_value =  array_values($request -> item_discount_value ?? []);
            $request -> item_rate =  array_values($request -> item_rate);

            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            $type = SaleModuleHelper::getAndReturnInvoiceType($request -> type ?? '');
            $request -> merge(['type' => $type]);
            $invoiceRequired = false;
            $store = ErpStore::find($request -> store_id);
            $invoiceRequiredParam = OrganizationBookParameter::where('book_id', $request -> book_id) -> where('parameter_name', ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM) -> first();
            if (isset($invoiceRequiredParam) && $invoiceRequiredParam -> parameter_value[0] == 'yes') {
                $invoiceRequired = true;
            }
            //Auth credentials
            $organization = Organization::find($user -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            //Tax Country and State
            $firstAddress = $organization->addresses->first();
            $companyCountryId = null;
            $companyStateId = null;
            if ($firstAddress) {
                $companyCountryId = $store->address->country->id;
                $companyStateId = $store->address->state->id;
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
            if (!$request -> sale_invoice_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpSaleInvoice::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $saleInvoice = null;
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
            $transportationMode = EwayBillMaster::find($request->transporter_mode);

            if ($request -> sale_invoice_id) { //Update
                $saleInvoice = ErpSaleInvoice::find($request -> sale_invoice_id);
                $saleInvoice -> document_date = $request -> document_date;
                $saleInvoice -> reference_number = $request -> reference_no;
                //Store and department keys
                $saleInvoice -> store_id = $request -> store_id ?? null;
                $saleInvoice -> store_code = $store ?-> store_code ?? null;
                $saleInvoice -> consignee_name = $request -> consignee_name;
                $saleInvoice -> consignment_no = $request -> consignment_no;
                $saleInvoice -> vehicle_no = $request -> vehicle_no;
                $saleInvoice -> transporter_name = $request -> transporter_name;
                $saleInvoice -> transportation_mode = $transportationMode ?-> description;
                $saleInvoice -> eway_bill_master_id = $transportationMode ?-> id;
                // $saleInvoice -> eway_bill_no = $request -> eway_bill_no;
                $saleInvoice -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup
                if(($saleInvoice -> document_status == ConstantHelper::APPROVED || $saleInvoice -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpSaleInvoice', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpInvoiceItem', 'relation_column' => 'sale_invoice_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpInvoiceItemAttribute', 'relation_column' => 'invoice_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpInvoiceItemLocation', 'relation_column' => 'invoice_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleInvoiceTed', 'relation_column' => 'invoice_item_id'],
                    ];
                    $a = Helper::documentAmendment($revisionData, $saleInvoice->id);

                }
                $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedSiItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }

                if (count($deletedData['deletedHeaderExpTedIds'])) {
                    ErpSaleInvoiceTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
                }

                if (count($deletedData['deletedHeaderDiscTedIds'])) {
                    ErpSaleInvoiceTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
                }

                if (count($deletedData['deletedItemDiscTedIds'])) {
                    ErpSaleInvoiceTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
                }

                // if (count($deletedData['deletedAttachmentIds'])) {
                //     $files = ErpSoMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
                //     foreach ($files as $singleMedia) {
                //         $filePath = $singleMedia -> file_name;
                //         if (Storage::exists($filePath)) {
                //             Storage::delete($filePath);
                //         }
                //         $singleMedia -> delete();
                //     }
                // }

                if (count($deletedData['deletedSiItemIds'])) {
                    $siItems = ErpInvoiceItem::whereIn('id',$deletedData['deletedSiItemIds'])->get();
                    # all ted remove item level
                    foreach($siItems as $siItem) {
                        if ($saleInvoice -> dcoument_type != ConstantHelper::SI_SERVICE_ALIAS) {
                            InventoryHelper::deleteIssueStock($saleInvoice->id, $siItem->id, ConstantHelper::SI_SERVICE_ALIAS);
                        }

                        if ($siItem -> so_item_id) {
                            $soItem = ErpSoItem::find($siItem -> so_item_id);
                            if (isset($soItem)) {
                                $soItem -> dnote_qty -= $siItem -> order_qty;
                                if ($siItem -> document_type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
                                    $soItem -> invoice_qty -= $siItem -> order_qty;
                                }
                                $soItem -> save();
                            }
                        }
                        if ($siItem -> dnote_item_id) {
                            $refSiItem = ErpInvoiceItem::find($siItem -> dnote_item_id);
                            if (isset($refSiItem)) {
                                $refSiItem -> invoice_qty -= $siItem -> order_qty;
                                $refSiItem -> save();
                            }
                        }
                        //Free up all the bundles
                        ErpPslipItemDetail::where('dn_item_id', $siItem -> id) -> update([
                            'dn_item_id' => null
                        ]);
                        $siItem->teds()->delete();
                        #delivery remove
                        // $siItem->item_deliveries()->delete();
                        # all attr remove
                        $siItem->attributes()->delete();

                        // $refereceItemIds = $siItem -> mapped_so_item_ids();
                        // if (count($refereceItemIds) > 0) {
                        //     foreach ($refereceItemIds as $referenceFromId) {
                        //         $referenceItem = ErpSoItem::where('id', $referenceFromId) -> first();
                        //         $existingMapping = ErpSoDnMapping::where([
                        //             ['sale_order_id', $referenceItem -> sale_order_id],
                        //             ['so_item_id', $referenceItem -> id],
                        //             ['delivery_note_id', $saleInvoice -> id],
                        //             ['dn_item_id', $siItem -> id],
                        //         ]) -> first();
                        //         if (isset($existingMapping)) {
                        //             $referenceItem -> dnote_qty = $referenceItem -> dnote_qty - $siItem -> order_qty;
                        //             if (!$invoiceRequiredParam) {
                        //                 $referenceItem -> invoice_qty = $referenceItem -> invoice_qty - $siItem -> order_qty;
                        //             }
                        //             $referenceItem -> save();
                        //             $existingMapping -> delete();
                        //         }
                        //     }
                        // }
                       
                        $siItem->delete();
                    }
                }

                //Delete all Item references
                // foreach ($saleInvoice -> items as $item) {
                //     if (($request -> type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $request -> type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS)) {
                //     }
                //     $item -> item_attributes() -> forceDelete();
                //     $item -> discount_ted() -> forceDelete();
                //     $item -> tax_ted() -> forceDelete();
                //     $item -> item_locations() -> forceDelete();
                //     $item -> forceDelete();
                // }
                //Delete all header TEDs
                // foreach ($saleInvoice -> discount_ted as $saleInvoiceDiscount) {
                //     $saleInvoiceDiscount -> forceDelete();
                // }
                // foreach ($saleInvoice -> expense_ted as $saleInvoiceExpense) {
                //     $saleInvoiceExpense -> forceDelete();
                // }
            } else { //Create
                $saleInvoice = ErpSaleInvoice::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request -> book_id,
                    'invoice_required' => $invoiceRequired,
                    'book_code' => $request -> book_code,
                    'document_type' => $type,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request -> document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'reference_number' => $request -> reference_no,
                    'store_id' => $request -> store_id ?? null,
                    'store_code' => $store ?-> store_code ?? null,
                    'customer_id' => $request -> customer_id,
                    'customer_code' => $request -> customer_code,
                    'customer_email' => $customerEmail,
                    'customer_phone_no' => $customerPhoneNo,
                    'customer_gstin' => $customerGSTIN,
                    'consignee_name' => $request -> consignee_name,
                    'consignment_no' => $request -> consignment_no,
                    'vehicle_no' => $request -> vehicle_no,
                    'transporter_name' => $request -> transporter_name,
                    'transportation_mode' => $transportationMode ?-> description,
                    'eway_bill_master_id' => $transportationMode ?-> id,
                    // 'eway_bill_no' => $request -> eway_bill_no,
                    'billing_address' => null,
                    'shipping_address' => null,
                    'currency_id' => $request -> currency_id,
                    'currency_code' => $request -> currency_code,
                    'payment_term_id' => $request -> payment_terms_id,
                    'payment_term_code' => $request -> payment_terms_code,
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
                    $billingAddress = $saleInvoice -> billing_address_details() -> create([
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
                    $billingAddress = $saleInvoice -> billing_address_details() -> create([
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
                    $shippingAddress = $saleInvoice -> shipping_address_details() -> create([
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
                    $shippingAddress = $saleInvoice -> shipping_address_details() -> create([
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
                $locationAddress = $saleInvoice -> location_address_details() -> create([
                    'address' => $orgLocationAddress -> address -> address,
                    'country_id' => $orgLocationAddress -> address -> country_id,
                    'state_id' => $orgLocationAddress -> address -> state_id,
                    'city_id' => $orgLocationAddress -> address -> city_id,
                    'type' => 'location',
                    'pincode' => $orgLocationAddress -> address -> pincode,
                    'phone' => $orgLocationAddress -> address -> phone,
                    'fax_number' => $orgLocationAddress -> address -> fax_number
                ]);
                $saleInvoice -> gst_invoice_type = EInvoiceHelper::getGstInvoiceType($request -> customer_id, $saleInvoice -> shipping_address_details -> country_id, $saleInvoice ?->  location_address_details ?-> country_id);
            }
            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpSiDynamicField::class, $saleInvoice -> id, $request -> dynamic_field ?? []);
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

                $saleInvoice -> billing_address = $request -> billing_address ?? null;
                $saleInvoice -> shipping_address = $request -> shipping_address ??  null;
                $saleInvoice -> save();
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
                                return response() -> json([
                                    'message' => '',
                                    'errors' => array(
                                        'item_name.' . $itemKey => "Discount more than value"
                                    )
                                ], 422);
                            }
                            // $inventoryUomQty = isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0;
                            // $requestUomId = isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null;
                            // if($requestUomId != $item->uom_id) {
                            //     $alUom = $item->alternateUOMs()->where('uom_id',$requestUomId)->first();
                            //     if($alUom) {
                            //         $inventoryUomQty= intval(isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0) * $alUom->conversion_to_inventory;
                            //     }
                            // }
                            $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0);
                            $uom = Unit::find($request -> uom_id[$itemKey] ?? null);
                            $dnoteQty = 0;
                            $invoiceQty = 0;
                            if ($saleInvoice -> document_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                                $dnoteQty = isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0;
                                if (!$saleInvoice -> invoice_required) {
                                    $invoiceQty = isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0;
                                }
                            } else {
                                $invoiceQty = isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0;
                            }
                            $customersItemDetails = ItemHelper::getCustomerItemDetails($item -> id, $saleInvoice -> customer_id);
                            array_push($itemsData, [
                                'sale_invoice_id' => $saleInvoice -> id,
                                'item_id' => $item -> id,
                                'item_code' => $item -> item_code,
                                'store_id' => isset($request -> item_store[$itemKey]) ? $request -> item_store[$itemKey] : null,
                                'sub_store_id' => isset($request -> item_sub_store[$itemKey]) ? $request -> item_sub_store[$itemKey] : null,
                                'item_name' => $item -> item_name,
                                'customer_item_id' => $customersItemDetails['customer_item_id'],
                                'customer_item_code' => $customersItemDetails['customer_item_code'],
                                'customer_item_name' => $customersItemDetails['customer_item_name'],
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null, //Need to change
                                'uom_code' => isset($uom) ? $uom -> name : null,
                                'order_qty' => isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0,
                                'invoice_qty' => $invoiceQty,
                                'dnote_qty' => $dnoteQty,
                                'inventory_uom_id' => $item -> uom ?-> id,
                                'inventory_uom_code' => $item -> uom ?-> name,
                                'inventory_uom_qty' => $inventoryUomQty,
                                'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
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
                        if ($totalValueAfterDiscount > 0) {
                            $headerDiscount = ($itemDataValue['value_after_discount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                        }
                        $valueAfterHeaderDiscount = $itemDataValue['value_after_discount'] - $headerDiscount;
                        //Expense
                        $itemExpenseAmount = 0;
                        $itemHeaderExpenseAmount = 0;
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($itemDataValue['item_value'] + $headerDiscount + $itemDataValue['item_discount_amount']) / $itemDataValue['order_qty'];
                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($itemDataValue['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> shipping_country_id, $partyStateId ?? $request -> shipping_state_id, 'sale');
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
                        //Update or create
                        $itemRowData = [
                            'sale_invoice_id' => $saleInvoice -> id,
                            'item_id' => $itemDataValue['item_id'],
                            'item_code' => $itemDataValue['item_code'],
                            'store_id' => $itemDataValue['store_id'],
                            'sub_store_id' => $itemDataValue['sub_store_id'],
                            'item_name' => $itemDataValue['item_name'],
                            'customer_item_id' => $itemDataValue['customer_item_id'],
                            'customer_item_code' => $itemDataValue['customer_item_code'],
                            'customer_item_name' => $itemDataValue['customer_item_name'],
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
                            'item_discount_amount' => $itemDataValue['item_discount_amount'],
                            'header_discount_amount' => $headerDiscount,
                            'item_expense_amount' => $itemExpenseAmount, //Need to change
                            'header_expense_amount' => $itemHeaderExpenseAmount, //Need to change
                            'total_item_amount' => ($itemDataValue['order_qty'] * $itemDataValue['rate']) - ($itemDataValue['item_discount_amount'] + $headerDiscount) + ($itemExpenseAmount + $itemHeaderExpenseAmount) + $itemTax,
                            'tax_amount' => $itemTax,
                            'company_currency_id' => null,
                            'company_currency_exchange_rate' => null,
                            'group_currency_id' => null,
                            'group_currency_exchange_rate' => null,
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        if (isset($request -> so_item_id[$itemDataKey])) {
                            $oldSoItem = ErpInvoiceItem::find($request -> so_item_id[$itemDataKey]);
                            $soItem = ErpInvoiceItem::updateOrCreate(['id' => $request -> so_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $soItem = ErpInvoiceItem::create($itemRowData);
                        }
                        //Bundle Conditions
                        if (isset($request -> bundle_ids[$itemDataKey])) {
                            $bundleIds = json_decode($request -> bundle_ids[$itemDataKey], true);
                            if (isset($bundleIds) && $bundleIds) {
                                foreach ($bundleIds as $bundleIdData) {
                                    if ($bundleIdData['checked']) {
                                        ErpPslipItemDetail::where('id', $bundleIdData['bundle_id']) -> update([
                                            'dn_item_id' => $soItem -> id
                                        ]);
                                    } else {
                                        ErpPslipItemDetail::where('id', $bundleIdData['bundle_id']) -> update([
                                            'dn_item_id' => null
                                        ]);
                                    }
                                }
                            }
                        }
                        //Order Pulling condition
                        if (isset($request -> quotation_item_type[$itemDataKey])) {
                            $pullType = $request -> quotation_item_type[$itemDataKey];
                            if ($pullType === ConstantHelper::SO_SERVICE_ALIAS || $pullType === PackingListConstants::SERVICE_ALIAS) {
                                $qtItem = ErpSoItem::find($request -> quotation_item_ids[$itemDataKey]);
                                if (isset($qtItem)) {
                                    //If Order is pulled inside DN
                                    if ($invoiceRequired) {
                                        $qtItem -> dnote_qty = ($qtItem -> dnote_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                        $soItem -> dnote_qty = ($soItem -> dnote_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                    } else {
                                        $qtItem -> dnote_qty = ($qtItem -> dnote_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                        $soItem -> dnote_qty = ($soItem -> dnote_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                        $qtItem -> invoice_qty = ($qtItem -> invoice_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                        $soItem -> invoice_qty = ($soItem -> invoice_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                    }
                                    $qtItem -> save();
                                    $soItem -> sale_order_id = $qtItem -> header ?-> id;
                                    $soItem -> so_item_id = $qtItem ?-> id;
                                    $soItem -> save();
                                }
                                if (isset($request -> plist_detail_ids[$itemDataKey])) {
                                    $plistIds = json_decode($request -> plist_detail_ids[$itemDataKey]);
                                    $plistDetails = PackingListDetail::whereIn('id', $plistIds) -> get();
                                    foreach ($plistDetails as $plistDetail) {
                                        ErpInvoiceItemPacket::create([
                                            'invoice_item_id' => $soItem -> id,
                                            'plist_detail_id' => $plistDetail -> id,
                                            'package_number' => $plistDetail -> packing_number
                                        ]);
                                        $plistDetail -> dn_item_id = $soItem -> id;
                                        $plistDetail -> save();
                                    }
                                }
                                
                                // $itemQty = isset($oldSoItem) ? $soItem -> order_qty - $oldSoItem -> order_qty : $soItem -> order_qty;
                                // $referenceFromIds = json_decode($request -> reference_from[$itemDataKey]);
                                // if ($itemQty > 0) {
                                //     sort($referenceFromIds);
                                // } else {
                                //     rsort($referenceFromIds);
                                // }
                                // foreach ($referenceFromIds as $referenceFromId) {
                                //    $referenceItem = ErpSoItem::where('id', $referenceFromId) -> first();
                                //    $existingMapping = ErpSoDnMapping::where([
                                //     ['sale_order_id', $referenceItem -> sale_order_id],
                                //     ['so_item_id', $referenceItem -> id],
                                //     ['delivery_note_id', $saleInvoice -> id],
                                //     ['dn_item_id', $soItem -> id],
                                //    ]) -> first();
                                //    if ($itemQty >= 0) {
                                //     $utilizedQty = min($itemQty, $referenceItem -> balance_qty);
                                //    } else {
                                //     $utilizedQty = min(abs($itemQty), $existingMapping ?-> dn_qty) * -1;
                                //    }
                                //    if (!isset($existingMapping)){
                                //     $dnMapping = ErpSoDnMapping::create([
                                //         'sale_order_id' => $referenceItem -> sale_order_id,
                                //         'so_item_id' => $referenceItem -> id,
                                //         'delivery_note_id' => $saleInvoice -> id,
                                //         'dn_item_id' => $soItem -> id,
                                //         'dn_qty' => $utilizedQty
                                //     ]);
                                //    } else {
                                //     if ($existingMapping -> dn_qty + $utilizedQty <= 0) {
                                //         $existingMapping -> delete();
                                //     } else {
                                //         $existingMapping -> dn_qty = $existingMapping -> dn_qty + $utilizedQty;
                                //         $existingMapping -> save();
                                //     }
                                //    }
                                //    $referenceItem -> dnote_qty = $referenceItem -> dnote_qty + $utilizedQty;
                                //    if (!$invoiceRequiredParam) {
                                //     $referenceItem -> invoice_qty = $referenceItem -> invoice_qty + $utilizedQty;
                                //    }
                                //    if ($referenceItem -> order_qty < $referenceItem -> dnote_qty) {
                                //     DB::rollBack();
                                //     return response() -> json([
                                //         'message' => 'Item No. ' . ($itemDataKey + 1) . ' quantity cannot exceed ' . $referenceItem -> order_qty,
                                //         'error' => ''
                                //     ], 422);
                                //    }
                                //    $referenceItem -> save();
                                //    $itemQty -= $utilizedQty;
                                //    if ($itemQty == 0) {
                                //       break;
                                //    }
                                // }
                                // if (($itemQty) != 0) {
                                //     DB::rollBack();
                                //     return response()->json([
                                //         'message' => '',
                                //         'errors' => array(
                                //             'item_qty.' . $itemDataKey => 'Quantity more than balance'
                                //         )
                                //     ], 422);
                                // }
                            } else if ($pullType === ConstantHelper::SI_SERVICE_ALIAS || $pullType === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                                $qtItem = ErpInvoiceItem::find($request -> quotation_item_ids[$itemDataKey]);
                                if (isset($qtItem)) {
                                    $qtItem -> invoice_qty = ($qtItem -> invoice_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                    // $soItem -> invoice_qty = ($soItem -> invoice_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                    //Check if sales order exists
                                    if ($qtItem -> so_item_id) {
                                        $saleOrderItem = ErpSoItem::find($qtItem -> so_item_id);
                                        if ($saleOrderItem) {
                                            $saleOrderItem -> invoice_qty = ($saleOrderItem -> invoice_qty - (isset($oldSoItem) ? $oldSoItem -> order_qty : 0)) + $itemDataValue['order_qty'];
                                            $saleOrderItem -> save();
                                        }
                                    }
                                    //If invoice is required then update both quantites
                                    if (!$saleInvoice -> invoice_required) {
                                        $qtItem -> invoice_qty = $itemDataValue['order_qty'];
                                    }
                                    $soItem -> dnote_id = $qtItem -> header ?-> id;
                                    $soItem -> dnote_item_id = $qtItem ?-> id;
                                    if(isset($qtItem->so_item_id)){
                                        $soItem -> so_item_id = $qtItem ?-> so_item_id;
                                        $soItem -> sale_order_id = $qtItem -> sale_order_id;
                                           
                                    }
                                    $qtItem -> save();
                                    $soItem -> save();
                                }
                            } else if ($pullType === ConstantHelper::LAND_LEASE) {
                                $leaseSchedule = LandLeaseScheduler::find($request -> quotation_item_ids[$itemDataKey]);
                                if (isset($leaseSchedule)) {
                                    $leaseSchedule -> invoice_amount = ($leaseSchedule -> invoice_amount - (isset($oldSoItem) ? $oldSoItem -> rate : 0)) + $itemDataValue['rate'];
                                    $leaseSchedule -> save();
                                    $soItem -> lease_schedule_id = $leaseSchedule -> id;
                                    $soItem -> land_lease_id = $leaseSchedule -> header ?-> id;
                                    $soItem -> lease_item_type = ConstantHelper::LEASE_SERVICE_TYPE_NAME;
                                    $soItem -> save();
                                }
                                if (!($request -> quotation_item_ids[$itemDataKey]) && isset($request -> quotation_item_ids_header[$itemDataKey] )) {
                                    $landLease = LandLease::find($request -> quotation_item_ids_header[$itemDataKey]);
                                    if (isset($landLease)) {
                                        $landLease -> invoice_security_deposit = ($landLease -> invoice_security_deposit - (isset($oldSoItem) ? $oldSoItem -> rate : 0)) + $itemDataValue['rate'];
                                        $landLease -> save();
                                        $soItem -> land_lease_id = $landLease ?-> id;
                                        $soItem -> lease_item_type = "security";
                                        $soItem -> save();
                                    }
                                }
                            }
                           
                        }
                        //TED Data (DISCOUNT)
                        if (isset($request -> item_discount_value[$itemDataKey]))
                        {
                            foreach ($request -> item_discount_value[$itemDataKey] as $itemDiscountKey => $itemDiscountTed){
                                $itemDiscountRowData = [
                                    'sale_invoice_id' => $saleInvoice -> id,
                                    'invoice_item_id' => $soItem -> id,
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
                                    $soItemTedForDiscount = ErpSaleInvoiceTed::updateOrCreate(['id' => $request -> item_discount_id[$itemDataKey][$itemDiscountKey]], $itemDiscountRowData);
                                } else {
                                    $soItemTedForDiscount = ErpSaleInvoiceTed::create($itemDiscountRowData);
                                }
                                // $soItemTedForDiscount = ErpSaleInvoiceTed::create([
                                //     'sale_invoice_id' => $saleInvoice -> id,
                                //     'invoice_item_id' => $soItem -> id,
                                //     'ted_type' => 'Discount',
                                //     'ted_level' => 'D',
                                //     'ted_id' => null,
                                //     'ted_name' => isset($request -> item_discount_name[$itemDataKey][$itemDiscountKey]) ? $request -> item_discount_name[$itemDataKey][$itemDiscountKey] : null,
                                //     'assessment_amount' => $itemDataValue['rate'] * $itemDataValue['order_qty'],
                                //     'ted_percentage' => $itemDiscountTed / ($itemDataValue['rate'] * $itemDataValue['order_qty']) * 100,
                                //     'ted_amount' => $itemDiscountTed,
                                //     'applicable_type' => 'Deduction',
                                // ]);
                            }
                        }
                        //TED Data (TAX)
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                // $soItemTedForDiscount = ErpSaleInvoiceTed::create([
                                //     'sale_invoice_id' => $saleInvoice -> id,
                                //     'invoice_item_id' => $soItem -> id,
                                //     'ted_type' => 'Tax',
                                //     'ted_level' => 'D',
                                //     'ted_id' => $taxDetail['tax_id'],
                                //     'ted_group_code' => $taxDetail['tax_group'],
                                //     'ted_name' => $taxDetail['tax_type'],
                                //     'assessment_amount' => $valueAfterHeaderDiscount,
                                //     'ted_percentage' => (double)$taxDetail['tax_percentage'],
                                //     'ted_amount' => ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount),
                                //     'applicable_type' => 'Collection',
                                // ]);
                                $soItemTedForDiscount = ErpSaleInvoiceTed::updateOrCreate(
                                    [
                                        'sale_invoice_id' => $saleInvoice -> id,
                                        'invoice_item_id' => $soItem -> id,
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
                                    $itemAttribute = ErpInvoiceItemAttribute::updateOrCreate(
                                        [
                                            'sale_invoice_id' => $saleInvoice -> id,
                                            'invoice_item_id' => $soItem -> id,
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
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);
                }
                ErpSaleInvoiceTed::where([
                    'sale_invoice_id' => $saleInvoice -> id,
                    'invoice_item_id' => $soItem -> id,
                    'ted_type' => 'Tax',
                    'ted_level' => 'D',
                ]) -> whereNotIn('id', $itemTaxIds) -> delete();
                ErpInvoiceItemAttribute::where([
                    'sale_invoice_id' => $saleInvoice -> id,
                    'invoice_item_id' => $soItem -> id,
                ]) -> whereNotIn('id', $itemAttributeIds) -> delete();
                //Header TED (Discount)
                if (isset($request -> order_discount_value) && count($request -> order_discount_value) > 0) {
                    foreach ($request -> order_discount_value as $orderDiscountKey => $orderDiscountVal) {
                        $headerDiscountRowData = [
                            'sale_invoice_id' => $saleInvoice -> id,
                            'invoice_item_id' => null,
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
                            ErpSaleInvoiceTed::updateOrCreate(['id' => $request -> order_discount_id[$orderDiscountKey]], $headerDiscountRowData);
                        } else {
                            ErpSaleInvoiceTed::create($headerDiscountRowData);
                        }
                        // ErpSaleInvoiceTed::create([
                        //     'sale_invoice_id' => $saleInvoice -> id,
                        //     'invoice_item_id' => null,
                        //     'ted_type' => 'Discount',
                        //     'ted_level' => 'H',
                        //     'ted_id' => null,
                        //     'ted_name' => isset($request -> order_discount_name[$orderDiscountKey]) ? $request -> order_discount_name[$orderDiscountKey] : null,
                        //     'assessment_amount' => $totalItemValueAfterDiscount,
                        //     'ted_percentage' => $orderDiscountVal / $totalItemValueAfterDiscount * 100 ,
                        //     'ted_amount' => $orderDiscountVal,
                        //     'applicable_type' => 'Deduction',
                        // ]);
                    }
                }
                //Header TED (Expense)
                $totalValueAfterTax = $totalItemValueAfterDiscount + $totalTax;
                $totalExpenseAmount = 0;
                if (isset($request -> order_expense_value) && count($request -> order_expense_value) > 0) {
                    foreach ($request -> order_expense_value as $orderExpenseKey => $orderExpenseVal) {
                        $headerExpenseRowData = [
                            'sale_invoice_id' => $saleInvoice -> id,
                            'invoice_item_id' => null,
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
                            ErpSaleInvoiceTed::updateOrCreate(['id' => $request -> order_expense_id[$orderExpenseKey]], $headerExpenseRowData);
                        } else {
                            ErpSaleInvoiceTed::create($headerExpenseRowData);
                        }

                        // ErpSaleInvoiceTed::create([
                        //     'sale_invoice_id' => $saleInvoice -> id,
                        //     'invoice_item_id' => null,
                        //     'ted_type' => 'Expense',
                        //     'ted_level' => 'H',
                        //     'ted_id' => null,
                        //     'ted_name' => isset($request -> order_expense_name[$orderExpenseKey]) ? $request -> order_expense_name[$orderExpenseKey] : null,
                        //     'assessment_amount' => $totalItemValueAfterDiscount,
                        //     'ted_percentage' => $orderExpenseVal / $totalValueAfterTax * 100 , // Need to change
                        //     'ted_amount' => $orderExpenseVal,
                        //     'applicable_type' => 'Collection',
                        // ]);
                        $totalExpenseAmount += $orderExpenseVal;
                    }
                }
                //Check all total values
                if ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount) + $totalExpenseAmount < 0)
                {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => 'Document Value cannot be less than 0'
                    ], 422);
                }
                $saleInvoice -> total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
                $saleInvoice -> total_item_value = $itemTotalValue;
                $saleInvoice -> total_tax_value = $totalTax;
                $saleInvoice -> total_expense_value = $totalExpenseAmount;
                $saleInvoice -> total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;
                //Approval check
                if ($request -> sale_invoice_id) { //Update condition
                    $bookId = $saleInvoice->book_id;
                    $docId = $saleInvoice->id;
                    $amendRemarks = $request->amend_remarks ?? null;
                    $remarks = $saleInvoice->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachment');
                    $currentLevel = $saleInvoice->approval_level;
                    $modelName = get_class($saleInvoice);
                    $actionType = $request -> action_type ?? "";
                    if(($saleInvoice -> document_status == ConstantHelper::APPROVED || $saleInvoice -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $saleInvoice->revision_number + 1;
                        $actionType = 'amendment';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                        $saleInvoice->revision_number = $revisionNumber;
                        $saleInvoice->approval_level = 1;
                        $saleInvoice->revision_date = now();
                        $amendAfterStatus = $approveDocument['approvalStatus'] ?? $saleInvoice -> document_status;
                        $saleInvoice->document_status = $amendAfterStatus;
                        $saleInvoice->save();
                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $saleInvoice->revision_number ?? 0;
                            $actionType = 'submit';
                            $totalValue = $saleInvoice -> total_amount ?? 0;
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                            if ($approveDocument['message']) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => $approveDocument['message'],
                                    'error' => "",
                                ], 422);
                            }
                            $document_status = $approveDocument['approvalStatus'] ?? $saleInvoice -> document_status;
                            $saleInvoice->document_status = $document_status;
                        } else {
                            $saleInvoice->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { //Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $saleInvoice->book_id;
                        $docId = $saleInvoice->id;
                        $remarks = $saleInvoice->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $saleInvoice->approval_level;
                        $revisionNumber = $saleInvoice->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($saleInvoice);
                        $totalValue = $saleInvoice->total_amount ?? 0;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                        $saleInvoice->document_status = $approveDocument['approvalStatus'] ?? $saleInvoice->document_status;
                    }
                    $saleInvoice -> save();
                }
                $saleInvoice -> document_type = isset($request -> type) && in_array($request -> type, ConstantHelper::SALE_INVOICE_DOC_TYPES) ? $request -> type : ConstantHelper::SI_SERVICE_ALIAS;
                $saleInvoice -> save();
                //Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $saleInvoice->uploadDocuments($singleFile, 'sale_order', false);
                    }
                }
                //Logs
                // if ($request->document_status == ConstantHelper::SUBMITTED) {
                //     $bookId = $saleInvoice->book_id;
                //     $docId = $saleInvoice->id;
                //     $remarks = $saleInvoice->remarks;
                //     $attachments = null;
                //     $currentLevel = $saleInvoice->approval_level;
                //     $revisionNumber = $saleInvoice->revision_number ?? 0;
                //     $actionType = 'submit'; // Approve // reject // submit
                //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                // }
                if ($saleInvoice -> document_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                    $status = self::maintainStockLedger($saleInvoice);
                    if (!$status) {     
                        DB::rollBack();
                        return response() -> json([
                            'message' => 'Stock not available'
                        ], 422);
                    }
                }
                $gstInvoiceType = EInvoiceHelper::getGstInvoiceType($saleInvoice -> customer_id, $saleInvoice ?->shipping_address_details  ?-> country_id, $saleInvoice -> location_address_details ?-> country_id);
                if ($saleInvoice -> document_status === ConstantHelper::POSTED){
                    if ($gstInvoiceType === EInvoiceHelper::B2B_INVOICE_TYPE) {
                        SaleModuleHelper::updateEInvoiceDataFromHelper($saleInvoice);
                        $data = EInvoiceHelper::saveGstIn($saleInvoice);
                        if (isset($data) && $data['status'] == 'error') {
                            DB::rollBack();
                            return response()->json([
                                'message' => $data['message'],
                                'error' => $data['message'],
                            ], 500);
                        } else {
                            $saleInvoice->save();
                        }
                    }
                }
                $saleInvoice -> e_invoice_status = EInvoiceHelper::getEInvoicePendingDocumentStatus($saleInvoice, $saleInvoice -> gst_invoice_type);
                $saleInvoice -> save();
                SaleModuleHelper::cashCustomerMasterData($saleInvoice);
                DB::commit();
                $module = "Invoice";
                $redirect_url = route('sale.invoice.index');
                if ($saleInvoice -> type == ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS) {
                    $redirect_url = route('sale.leaseInvoice.index');
                }
                return response() -> json([
                    'message' => $module .  " created successfully",
                    'redirect_url' => $redirect_url
                ]);

        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex -> getLine() . ' in ' . $ex -> getFile(),
            ], 500);
        }
    }

    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $saleInvoice = ErpSaleInvoice::where('id',$id)->first();
            if (!$saleInvoice) {
                return response()->json(['data' => [], 'message' => "Sale Invoice not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'ErpSaleInvoice', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ErpInvoiceItem', 'relation_column' => 'sale_invoice_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpInvoiceItemAttribute', 'relation_column' => 'invoice_item_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpInvoiceItemLocation', 'relation_column' => 'invoice_item_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleInvoiceTed', 'relation_column' => 'invoice_item_id'],
            ];

            $a = Helper::documentAmendment($revisionData, $id);
            if ($a) {
                //*amendmemnt document log*/
                $bookId = $saleInvoice->book_id;
                $docId = $saleInvoice->id;
                $remarks = 'Amendment';
                $attachments = $request->file('attachment');
                $currentLevel = $saleInvoice->approval_level;
                $revisionNumber = $saleInvoice->revision_number;
                $actionType = 'amendment'; // Approve // reject // submit // amend
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);

               
                $saleInvoice->document_status = ConstantHelper::DRAFT;
                $saleInvoice->revision_number = $saleInvoice->revision_number + 1;
                $saleInvoice->approval_level = 1;
                $saleInvoice->revision_date = now();
                $saleInvoice->save();
            }

            DB::commit();
            return response()->json(['data' => [], 'message' => "Amendment done!", 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Amendment Submit Error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => "An unexpected error occurred. Please try again.", 'error' => $e -> getMessage(), 'status' => 500]);
        }
    }

    //Function to get all items of sales module depending upon the doc type - order , invoice, delivery note
    public function getSalesItemsForPulling(Request $request)
    {
        try {
            $selectedIds = $request -> selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
            $query = null;

            if ($request->doc_type === ConstantHelper::SO_SERVICE_ALIAS) {
                $referedHeaderId = ErpSoItem::whereIn('id', $selectedIds)->first()?->header?->id;

                $query = ErpSoItem::with(['attributes', 'uom', 'header.customer', 'header.shipping_address_details'])
                    ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $referedHeaderId) {
                        $subQuery->withDefaultGroupCompanyOrg()
                            ->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                            ->where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                            ->where('store_id', $request->store_id)
                            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                            ->whereIn('book_id', $applicableBookIds)
                            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id));
                    })
                    ->whereColumn('dnote_qty', '<', 'order_qty')
                    ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds));

            } elseif (in_array($request->doc_type, [ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS])) {
                $query = ErpInvoiceItem::with(['attributes', 'uom', 'header.customer', 'header.shipping_address_details'])
                    ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds) {
                        $subQuery->withDefaultGroupCompanyOrg()
                            ->whereIn('document_type', [ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS])
                            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                            ->whereIn('book_id', $applicableBookIds)
                            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id)->where('store_id', $request->store_id))
                            ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                            ->when($request->document_id, fn($q) => $q->where('id', $request->document_id));
                    })
                    ->whereColumn('invoice_qty', '<', 'order_qty');

            } elseif ($request->doc_type === PackingListConstants::SERVICE_ALIAS) {
                $query = PackingListDetail::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds) {
                    $subQuery -> withDefaultGroupCompanyOrg() -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
                    -> when($request -> book_id, function ($bookQuery) use($request) {
                        $bookQuery -> where('book_id', $request -> book_id);
                    }) -> when($request -> document_id, function ($docQuery) use($request) {
                        $docQuery -> where('id', $request -> document_id);
                    });
                }) -> when($request -> customer_id, function ($docQuery) use($request) {
                    $docQuery -> whereHas('sale_order', function ($nestedQuery) use($request) {
                        $nestedQuery -> where('customer_id', $request -> customer_id);
                    });
                }) -> when($request -> item_id, function ($custQuery) use($request) {
                    $custQuery -> whereHas('items', function ($nestedQuery) use($request) {
                        $nestedQuery -> where('item_id', $request -> item_id);
                    });
                }) ->with('sale_order')-> whereNull('dn_item_id');
            }

            // LAND_LEASE cannot be paginated without get()
            // $order = $order -> values();
            // if ($request -> doc_type == ConstantHelper::LAND_LEASE) {
            //     $order = SaleModuleHelper::sortByDueDateLogic($order);
            //     $order = $order->groupBy('lease_id')
            //         ->flatMap(function ($group) {
            //             // Optionally, sort each group further if needed
            //             return $group;
            //         });
            // }
            // You should either: cache & paginate separately or continue to use get()

            if (!$query) {
                return DataTables::of(collect([]))->make(true);
            }

            return DataTables::of($query)
                ->addColumn('book_code', fn($item) => $item?->header?->book_code ?? ($item->header->book?->book_code ?? ''))
                ->addColumn('document_number', fn($item) => $item?->header?->document_number)
                ->addColumn('document_date', fn($item) =>  method_exists($item?->header, 'getFormattedDate') ? $item->header->getFormattedDate("document_date") : '')
                ->addColumn('so_no', fn($item) => ($item?->so?->book_code ?? '') . '-' . ($item?->so?->document_number ?? ''))
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
                ->addColumn('requester_name', fn($item) => isset($item?->header) && method_exists($item?->header, 'requester_name') ? $item->header->requester_name() : '')
                ->addColumn('station_name', fn($item) => $item->header?->station?->name ?? '')
                ->editColumn('avl_stock', fn($item) => isset($item) && method_exists($item, 'getAvailableStocks') 
                    ? number_format($item->getAvailableStocks(
                        request('store_id'),
                        request('sub_store_id') ?? null
                    ), 2) : '0.00')
                ->editColumn('qty', fn($item) => number_format($item->qty ?? 0, 2))
                ->editColumn('balance_qty', fn($item) => number_format($item->balance_qty ?? 0, 2))
                ->editColumn('rate', fn($item) => number_format($item->rate ?? 0, 2))
                ->addColumn('attributes_array', function ($item) use ($request) {
                    if (in_array($request->doc_type, [ConstantHelper::JO_SERVICE_ALIAS])) {
                        return $item->attributes->map(fn($attr) => [
                            'attribute_name' => $attr->headerAttribute?->name,
                            'attribute_value' => $attr->headerAttributeValue?->value,
                        ])->values();
                    }
                    if ($request->doc_type === PackingListConstants::SERVICE_ALIAS) {
                        return $item->items->flatMap(function ($detailItem) {
                            return $detailItem->attributes->map(fn($attr) => [
                                'attribute_name' => $attr->attr_name,
                                'attribute_value' => $attr->attr_value,
                            ]);
                        })->values();
                    }
                    return $item->attributes->map(fn($attr) => [
                        'attribute_name' => $attr->attr_name,
                        'attribute_value' => $attr->attribute_value,
                    ])->values();
                })
                ->addColumn('stock_qty', function ($item) use ($request) {
                    return method_exists($item, 'getStockBalanceQty') 
                        ? $item->getStockBalanceQty($request->store_id ?? 0, $request->sub_store_id ?? 0) ?? 0 
                        : 0;
                })
                ->addColumn('sale_order', function ($item) {
                    return [
                        'book_code' => $item?->sale_order?->book_code,
                        'document_number' => $item?->sale_order?->document_number,
                        'document_date'   => isset($item->sale_order) && method_exists($item?->sale_order, 'getFormattedDate') 
                            ? $item->sale_order->getFormattedDate("document_date") 
                            : '',
                        'customer_code'   => $item?->sale_order?->customer?->customer_code,
                        'so_item_ids' => $item?->items && $item->items->isNotEmpty()
                            ? $item->items->pluck('so_item_id')->toArray()
                            : ($item?->so_item_id ? [$item->so_item_id] : [$item->id]),
                    ];
                })
                ->addColumn('items_ui', function ($item) {
                    $itemsHTML = '';
                    $totalChar = 0;
                    $maxChar = 70;
                    $extraItemsCount = 0;
                    $totalQty = 0;

                    if ($item->items && $item->items->count()) {
                        foreach ($item->items as $index => $detailItem) {
                            $totalQty += $detailItem->qty;
                            if ($index == 0) {
                                $itemName = $detailItem->item_name ?? '';
                                $totalChar += strlen($itemName);
                                $attributesHTML = '';

                                if ($detailItem->attributes && $detailItem->attributes->count()) {
                                    foreach ($detailItem->attributes as $attr) {
                                        $attributeName = $attr->attribute_name ?? '';
                                        $attributeValue = $attr->attribute_value ?? '';
                                        $totalChar += strlen($attributeName) + strlen($attributeValue);

                                        if ($totalChar <= $maxChar) {
                                            $attributesHTML .= "<span class='badge rounded-pill badge-light-primary'>$attributeName : $attributeValue</span> ";
                                        } else {
                                            $attributesHTML .= '..';
                                            break;
                                        }
                                    }
                                }

                                $itemsHTML .= "<span class='badge rounded-pill badge-light-primary'>$itemName</span> $attributesHTML";
                            } else {
                                $extraItemsCount++;
                            }
                        }

                        if ($extraItemsCount > 0) {
                            $itemsHTML .= "<span class='badge rounded-pill badge-light-primary'> + $extraItemsCount</span>";
                        }
                    }

                    return $itemsHTML;
                })

                ->addColumn('total_item_qty', function ($item) {
                    if ($item->items && $item->items->count()) {
                        return $item->items->sum('qty');
                    }
                    return 0;
                })
                ->rawColumns(['items_ui'])
                ->make( true);


        } catch(Exception $ex) {
            return response() -> json([
                'message' => 'Some internal error occurred',
                'error' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
            ]);
        }
    }

    public function getSalesLandForPulling(Request $request)
    {
        try {
            $selectedIds = $request->selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);

            // Build base query
            $query = LandLeaseItem::with(['uom', 'header.customer', 'header.shipping_address_details'])
                ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds) {
                    $subQuery->withDefaultGroupCompanyOrg()
                        ->where('document_type', ConstantHelper::LAND_LEASE)
                        ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                        ->whereIn('book_id', $applicableBookIds)
                        ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
                        ->when($request->book_id, fn($q) => $q->where('book_id', $request->book_id))
                        ->when($request->document_id, fn($q) => $q->where('id', $request->document_id));
                })
                ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds));

            $items = $query->get(); // must fetch as collection

            // Optional: Sort or group here if needed
            // $items = SaleModuleHelper::sortByDueDateLogic($items);

            return DataTables::of(collect($items))
                ->addColumn('book_code', fn($item) => $item?->header?->book_code ?? ($item->header->book?->book_code ?? ''))
                ->addColumn('document_number', fn($item) => $item?->header?->document_number)
                ->addColumn('document_date', fn($item) => $item->header->getFormattedDate("document_date"))
                ->addColumn('item_name', fn($item) => $item->item->item_name ?? '')
                ->addColumn('store_location_code', fn($item) => $item->header?->store_location?->store_name ?? '')
                ->addColumn('sub_store_code', fn($item) => $item->header?->sub_store?->name ?? '')
                ->addColumn('department_code', fn($item) => $item->header?->department?->name ?? '')
                ->addColumn('requester_name', fn($item) => $item?->header && method_exists($item->header, 'requester_name') ? $item->header->requester_name() : '')
                ->addColumn('station_name', fn($item) => $item->header?->station?->name ?? '')
                ->editColumn('avl_stock', fn($item) => number_format($item->getAvailableStocks(
                    request('store_id'),
                    request('sub_store_id') ?? null
                ), 2))
                ->editColumn('qty', fn($item) => number_format($item->qty, 2))
                ->editColumn('balance_qty', fn($item) => number_format($item->balance_qty, 2))
                ->editColumn('rate', fn($item) => number_format($item->rate, 2))
                ->addColumn('attributes_array', fn($item) => $item->attributes->map(fn($attr) => [
                    'attribute_name' => $attr->attr_name,
                    'attribute_value' => $attr->attr_value,
                ])->values())
                ->make(true);

        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine()
            ]);
        }
    }

    //Function to get all items of sales module depending upon the doc type - order , invoice, delivery note
    public function processPulledItems(Request $request)
    {
        try {
            $itemIds = $request -> items_id;
            $modelName = null;
            $headers = [];
            if ($request -> doc_type === ConstantHelper::SO_SERVICE_ALIAS) {
                $modelName = resolve("App\\Models\\ErpSaleOrder");
            } else if ($request -> doc_type === ConstantHelper::SI_SERVICE_ALIAS || $request -> doc_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                $modelName = resolve("App\\Models\\ErpSaleInvoice");
            } else {
                $modelName = null;
            }
            if (isset($modelName)) {
                $decoded = is_array($request->items_id[0]) ? $request->items_id[0] : json_decode($request->items_id[0], true); // decode as associative array
                $currentIds = is_array($decoded) ? $decoded : [$decoded]; 
                $headers = $modelName::with(['discount_ted', 'expense_ted', 'billing_address_details', 'shipping_address_details']) -> with('customer', function ($sQuery) {
                    $sQuery -> with(['currency', 'payment_terms']);
                }) -> with('items', function ($itemQuery) use($currentIds) {
                    $itemQuery -> whereIn('id', $currentIds) -> with(['discount_ted', 'tax_ted']) -> with(['item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                    }]);
                }) -> whereIn('id', $request -> order_id) -> get();
                foreach ($headers as $header) {
                    if ($modelName::class == "App\\Models\\ErpSaleInvoice") {
                        $saleOrderItems = $header -> sale_order_items();
                        foreach ($saleOrderItems as &$saleOrderItem) {
                            $saleOrderItem -> actual_qty = $saleOrderItem -> order_qty;
                        }
                    }
                    foreach ($header -> items as $orderItemKey => &$orderItem) {
                        $orderItem -> stock_qty = $orderItem -> getStockBalanceQty($request -> store_id ?? 0);
                        $orderItem -> item_attributes_array = $orderItem -> item_attributes_array();
                        if (isset($saleOrderItems[$orderItemKey])) {
                            $header -> items[$orderItemKey] = $saleOrderItems[$orderItemKey];
                            $header -> items[$orderItemKey] -> id = $orderItem -> id;
                            $header -> items[$orderItemKey] -> item_attributes_array = $orderItem -> item_attributes_array();
                        }
                    }
                }
            } else {
                if ($request -> doc_type === ConstantHelper::LAND_LEASE) {
                    $headers = LandLease::with(['customer.currency', 'customer.payment_terms'])
                    // ->whereHas('schedule', function ($subQuery) use ($request) {
                    //     $subQuery->whereIn('id', $request->items_id);
                    // })
                    ->with(['schedule' => function ($itemQuery) use ($request) {
                        $itemQuery->whereIn('id', $request->items_id);
                        // Uncomment below if needed, ensure relationships are correctly defined
                        // ->with(['discount_ted', 'tax_ted'])
                        // ->with(['item' => function ($itemQuery) {
                        //     $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        // }]);
                    }]) -> with('plots')
                    ->whereIn('id', $request->order_id)
                    ->get();

                    if ($headers && count($headers) > 0) {
                        foreach ($headers as &$header) {
                            //Customer Details
                            $header -> customer_code = $header -> customer ?-> customer_code;
                            $header -> consignee_name = '';
                            $header -> currency_code = $header -> currency ?-> short_name;
                            $header -> payment_term_code = $header -> payment_terms ?-> name;
                            //Address details
                            $header -> shipping_address_details = $header -> address;
                            $header -> billing_address_details = $header -> address;
                            //Other
                            $header -> document_type = '';
                            $header -> book_code = $header -> series ?-> book_code;
                            $header -> document_number = $header -> document_no;
                            $header -> discount_ted = [];
                            $header -> expense_ted = [];
                            $header -> document_type = ConstantHelper::LAND_LEASE;
                            //Item or Detail details
                            $items = [];
                            $landParcelId = $header -> plots ?-> first() -> land_parcel_id;
                            $landParcel = LandParcel::find($landParcelId);
                            $itemDetails = json_decode($landParcel -> service_item, true);
                            foreach ($header -> schedule as $headerItem) {
                                $itemDetail = null;
                                if (isset($landParcel)) {
                                    $itemDetail = new stdClass();
                                    $itemDetail -> id = $headerItem -> id;
                                    $itemDetail -> balance_qty = 1;
                                    $itemDetail -> actual_qty = 1;
                                    $itemDetail -> stock_qty = 1;
                                    $itemDetail -> remarks = null;
                                    $itemDetail -> discount_ted = [];
                                    $itemDetail -> tax_ted = [];
                                    $itemDetail -> header_discount_amount = 0;
                                    $itemDetail -> item_discount_amount = 0;
                                    $itemDetail -> item_expense_amount = 0;
                                    $itemDetail -> tax_amount = 0;
                                    $itemDetail -> header_expense_amount = 0;
                                    $itemDetail -> rate = round($headerItem -> installment_cost - $headerItem -> invoice_amount, 2);
                                   
                                    if (isset($itemDetails) && count($itemDetails) > 0) {
                                        $serviceItem = array_filter($itemDetails, function ($leaseItem) {
                                            return $leaseItem["'servicetype'"] == "Lease" || $leaseItem["'servicetype'"] == "Land-Lease";
                                        });
                                        if ($serviceItem && count($serviceItem) > 0) {
                                            $serviceItem = array_values($serviceItem);
                                            $item = Item::where('item_code', $serviceItem[0]["'servicecode'"]) -> where('type', ConstantHelper::SERVICE) -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']) -> first();
                                            if (isset($item)) {
                                                $itemDetail -> item = $item;
                                                $itemDetail -> due_date = $headerItem -> due_date;
                                                $itemDetail -> item_id = $item -> id;
                                                $itemDetail -> item_lease_type = ConstantHelper::LAND_LEASE;
                                                $itemDetail -> item_attributes_array = SaleModuleHelper::item_attributes_array($item -> id, $serviceItem[0]["'attributes'"] ?? []);
                                                $itemDetail -> land_parcel_display = $landParcel -> name;
                                                $plots = '';
                                                foreach ($header -> plots as $headerPlotIndex => $headerPlot) {
                                                    $plots .= (($headerPlotIndex !== 0 ? ',' : '') . ($headerPlot ?-> plot ?-> plot_name));
                                                }
                                                $itemDetail -> land_plots_display = $plots;
                                                //Attributes
                                                // $itemAttributes = ErpItemAttribute::where('item_id', $item -> id) -> get();
                                                // foreach ($itemAttributes as &$attribute) {
                                                //     $attributesArray = array();
                                                //     $attribute_ids = json_decode($attribute -> attribute_id);
                                                //     $attribute -> group_name = $attribute -> group ?-> name;
                                                //     foreach ($attribute_ids as $attributeValue) {
                                                //         $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                                                //         if (isset($attributeValueData))
                                                //         {
                                                //             $attributeValueData -> selected = false;
                                                //             array_push($attributesArray, $attributeValueData);
                                                //         }
                                                //     }
                                                //    $attribute -> values_data = $attributesArray;
                                                //    $attribute -> only(['id','group_name', 'values_data']);
                                                // }
                                                // $itemDetail -> item_attributes_array = $itemAttributes;
                                            }
                                        }
                                    }
                                }
                                array_push($items, $itemDetail);
                            }
                            $itemIds = isset($request -> items_id) ? $request -> items_id : [];
                            if (isset($landParcel) && in_array(0, $itemIds)) {
                                $securityItem = array_filter($itemDetails, function ($leaseItem) {
                                    return $leaseItem["'servicetype'"] === 'security';
                                });
                                if ($securityItem && count($securityItem) > 0) {
                                    $securityItem = array_values($securityItem);
                                    $item = Item::where('item_code', $securityItem[0]["'servicecode'"]) -> where('type', ConstantHelper::SERVICE) -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']) -> first();
                                    if (isset($item)) {
                                        $itemDetail = new stdClass();
                                        $itemDetail -> id = 0;
                                        $itemDetail -> balance_qty = 1;
                                        $itemDetail -> actual_qty = 1;
                                        $itemDetail -> stock_qty = 1;
                                        $itemDetail -> remarks = null;
                                        $itemDetail -> discount_ted = [];
                                        $itemDetail -> tax_ted = [];
                                        $itemDetail -> item_lease_type = "security";
                                        $itemDetail -> header_discount_amount = 0;
                                        $itemDetail -> item_discount_amount = 0;
                                        $itemDetail -> item_expense_amount = 0;
                                        $itemDetail -> tax_amount = 0;
                                        $itemDetail -> header_expense_amount = 0;
                                        $itemDetail -> rate = $header -> security_deposit - $header -> invoice_security_deposit;
                                        $itemDetail -> item = $item;
                                        $itemDetail -> due_date = $header -> document_date;
                                        $itemDetail -> item_id = $item -> id;
                                        $itemDetail -> item_attributes_array = SaleModuleHelper::item_attributes_array($item -> id, $securityItem[0]["'attributes'"] ?? []);;
                                        $itemDetail -> land_parcel_display = $landParcel -> name;
                                        $plots = '';
                                        foreach ($header -> plots as $headerPlotIndex => $headerPlot) {
                                            $plots .= (($headerPlotIndex !== 0 ? ',' : '') . ($headerPlot ?-> plot ?-> plot_name));
                                        }
                                        $itemDetail -> land_plots_display = $plots;
                                        //Attributes
                                        // $itemAttributes = ErpItemAttribute::where('item_id', $item -> id) -> get();
                                        // foreach ($itemAttributes as &$attribute) {
                                        //     $attributesArray = array();
                                        //     $attribute_ids = json_decode($attribute -> attribute_id);
                                        //     $attribute -> group_name = $attribute -> group ?-> name;
                                        //     foreach ($attribute_ids as $attributeValue) {
                                        //         $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                                        //         if (isset($attributeValueData))
                                        //         {
                                        //             $attributeValueData -> selected = false;
                                        //             array_push($attributesArray, $attributeValueData);
                                        //         }
                                        //     }
                                        //    $attribute -> values_data = $attributesArray;
                                        //    $attribute -> only(['id','group_name', 'values_data']);
                                        // }
                                        // $itemDetail -> item_attributes_array = $itemAttributes;
                                        array_push($items, $itemDetail);
                                    }
                                }
                            }
                            $header -> items = $items;
                           
                        }
                    }
                } else if ($request -> doc_type === PackingListConstants::SERVICE_ALIAS) {
                    $soItems = $request -> items_id;
                    $actualSoItemIds = [];
                    foreach ($soItems as $key => $value) {
                        $decoded = json_decode($value, true); // decode as associative array
                        $currentIds = is_array($decoded) ? $decoded : [$decoded];
                        foreach ($currentIds as $soItemId) {
                            array_push($actualSoItemIds, $soItemId);
                        }
                    }
                    $saleOrderIds = $request -> order_id;
                    $packingListDetailIds = $request -> plist_detail_ids;
                    // $packingListDetail = PackingListDetail::whereIn('id', $packingListDetailIds) -> with('items') -> get();
                    $headers = ErpSaleOrder::with(['discount_ted', 'expense_ted', 'billing_address_details', 'shipping_address_details']) -> with('customer', function ($sQuery) {
                        $sQuery -> with(['currency', 'payment_terms']);
                    }) -> with('items', function ($itemQuery) use($request, $actualSoItemIds) {
                        $itemQuery -> whereIn('id', $actualSoItemIds) -> with(['discount_ted', 'tax_ted']) -> with(['item' => function ($itemQuery) {
                            $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }]);
                    }) -> whereIn('id', $saleOrderIds) -> get();
                    foreach ($headers as $header) {
                        foreach ($header -> items as $orderItemKey => &$orderItem) {
                            $orderItem -> stock_qty = $orderItem -> getStockBalanceQty($request -> store_id ?? 0);
                            $orderItem -> item_attributes_array = $orderItem -> item_attributes_array();
                            $plistItem = PackingListItem::find($orderItem -> plist_item_id);
                            if (isset($plistItem)) {
                                $orderItem -> order_qty = $plistItem -> qty;
                                $orderItem -> balance_qty = $plistItem -> qty;   
                                $orderItem -> package = $plistItem -> detail ?-> packing_number;
                                $orderItem -> package_id = $plistItem -> detail ?-> id;
                            }

                        }
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
                'error' => $ex -> getMessage().''.$ex ->getLine() . ''.$ex ->getFile().')'
            ]);
        }
    }

    // genrate pdf
    public function generatePdf(Request $request, $id,$pattern,$download = false,$returnRaw = false)
    {
        $user = Helper::getAuthenticatedUser();

        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
       
        $order = ErpSaleInvoice::with(
            [
                'customer',
                'currency',
                'discount_ted',
                'expense_ted',
                'billing_address_details',
                'shipping_address_details',
            ]
        )
        ->with('items', function ($query) {
            $query -> with('discount_ted', 'tax_ted', 'bundles','item_locations') -> with(['item' => function ($itemQuery) {
                $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
            }]);
        })
        -> find($id);

        $shippingAddress = $order->shipping_address_details;
        $billingAddress = $order->billing_address_details;

        $type = ConstantHelper::SERVICE_LABEL[$order->document_type];
        $maxAttributeCount = 0;
        $allAttributeValues = [];
       
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
        $orderItems = $order->items;
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
        if ($order -> document_type === ConstantHelper::SI_SERVICE_ALIAS || 
        ($order -> document_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && !$order -> invoice_required))
        {
            $pdfFile = 'pdf.sales-invoice-pdf';
        } else {
            $pdfFile = "pdf.sales-document";
        }
        if($pattern && $pattern=="Delivery Note"){
            $pdfFile = "pdf.delivery-note";
        }
        $eInvoice = $order->irnDetail()->first();
        $qrCodeBase64 = null;
        if (isset($eInvoice)) {
            $qrCodeBase64 = EInvoiceHelper::generateQRCodeBase64($eInvoice->signed_qr_code);
        }

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        
        $html = view($pdfFile,
            [
                'pattern' => $pattern,
                'type' => $pattern,
                'order' => $order,
                'user' => $user,
                'shippingAddress' => $shippingAddress,
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
                'approvedBy' => $approvedBy,
                'items' => $orderItems,
                'maxAttributeCount' => $maxAttributeCount,
                'attributeName' => null,
                'qrCodeBase64' => $qrCodeBase64,
                'allAttributeValues' => $allAttributeValues,
                'billingAddress' => $billingAddress,
                'eInvoice' => $eInvoice
            ]
        )->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = ($order->book_code . '-' . $order -> document_number);

        $pdfPath = 'invoices/pdfs/invoice_' . $fileName  . '.pdf';
        Storage::disk('local')->put($pdfPath, $dompdf->output());
        if ($download) {
            return $dompdf->stream($fileName . '.pdf', ['Attachment' => true]);
        }
        if ($returnRaw) {
            return $dompdf->output(); // raw PDF content (string)
        }
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Einvoice_' . $fileName . '.pdf"');
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

    public function postInvoice(Request $request)
    {
        try {
            DB::beginTransaction();
            $saleInvoice = ErpSaleInvoice::find($request->document_id);
            $enableEinvoice = $saleInvoice -> gst_invoice_type === EInvoiceHelper::B2B_INVOICE_TYPE ? true : false;
            $eInvoice = $saleInvoice?->irnDetail()->first();
            if (!$eInvoice && $enableEinvoice) {
                $data = [
                    'message' => 'Please generate IRN First.',
                ];
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'data' => $data
                ], 422);
            }
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

    public function revokeSalesInvoice(Request $request)
    {
        DB::beginTransaction();
        try {
            $saleDocument = ErpSaleInvoice::find($request -> id);
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

    private static function maintainStockLedger($saleInvoice)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $saleInvoice->items->pluck('id')->toArray();
        $issueRecords = InventoryHelper::settlementOfInventoryAndStock($saleInvoice->id, $detailIds, $saleInvoice -> document_type, $saleInvoice->document_status, 'issue');
        if(!empty($issueRecords['data']) && count($issueRecords['data']) > 0){
            ErpInvoiceItemLocation::where('sale_invoice_id', $saleInvoice->id)
                ->whereIn('invoice_item_id', $detailIds)
                ->delete();

            foreach($issueRecords['data'] as $key => $val){

                $invoiceItem = ErpInvoiceItem::where('id', @$val->issuedBy->document_detail_id) -> first();

                ErpInvoiceItemLocation::create([
                    'sale_invoice_id' => $saleInvoice -> id,
                    'invoice_item_id' => @$val->issuedBy->document_detail_id,
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
                    'quantity' => ItemHelper::convertToAltUom($val -> issuedBy -> item_id, $invoiceItem ?-> uom_id ?? $val->issuedBy?->inventory_uom_id, $val -> issuedBy -> issue_qty),
                    'inventory_uom_qty' => $val -> issuedBy -> issue_qty
                ]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function getBundlesForPulledSo(Request $request)
    {
        $soItemId = $request -> so_item_id ?? null;
        $bundleIds = ($request -> bundle_ids ?? []);
        $bundles = [];
        $selectedIds = $request -> selected_bundles ?? [];
        if ($bundleIds && count($bundleIds) > 0) {
            //Edit Approved only previous selected values
            $bundles = ErpPslipItemDetail::whereIn('id', $bundleIds) -> get();
            foreach ($bundles as $bundle) {
                $bundle -> checked = true;
            }
        } else {
            //Create or in edit draft mode (Show all possible values)
            $pslipItemIds = ErpPslipItem::where('so_item_id', $soItemId) -> get() -> pluck('id') -> toArray();
            $bundles = ErpPslipItemDetail::whereIn('pslip_item_id', $pslipItemIds) -> when($request -> dn_item_id, function ($subQuery) use($request) {
                $subQuery -> whereNull('dn_item_id') -> orWhere('dn_item_id', $request -> dn_item_id);
            }) -> when(!$request -> dn_item_id, function ($subQuery) {
                $subQuery -> whereNull('dn_item_id');
            }) -> get();
            foreach ($bundles as &$bundle) {
                $checkedStatus = ((count($selectedIds) > 0 && in_array($bundle -> id, $selectedIds)) || $request -> initial_open == "true");
                $bundle -> checked = $checkedStatus;
            }
        }
        return response() -> json([
            'status' => 'success',
            'message' => '',
            'data' => [
                'bundles' => $bundles
            ]
        ]);
    }

    public function getFreePslipsForDirectDeliveryNote(Request $request)
    {
        try {
            $freePslipItems = ErpPslipItem::whereHas('header', function ($headerQuery) {
                $headerQuery -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                -> withDefaultGroupCompanyOrg();
            }) -> whereNull('so_item_id') -> get();
            return array(
                'message' => 'Production Slips found',
                'data' => $freePslipItems
            );
        } catch (Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }

    public function generateEInvoice(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vehicle_no' => [
                    'required',
                    'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{0,3}[0-9]{4}$/'
                ],
                'transporter_mode' => 'required|integer',
                "transporter_name" => [
                   "required",
                   'string'
                ],
            ],
            [
                'vehicle_no.required' => 'Vehicle number is required.',
                'vehicle_no.regex' => 'Vehicle number format is invalid. Example: MH12AB1234.',
                'transporter_mode.required' => 'Transporter mode is required.',
                'transporter_mode.integer' => 'Transporter mode must be an integer.',
                'transporter_name.required' => 'Transporter name is required.',
                'transporter_name.string' => 'Transporter name must be a string.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages()->first(),
            ], 422);
        }
        $id = $request -> id;
        try{
            $documentHeader = ErpSaleInvoice::find($id);
            $documentHeader = SaleModuleHelper::updateEInvoiceDataFromHelper($documentHeader);
            $documentDetails = ErpInvoiceItem::where('sale_invoice_id', $id)->get();
            $generateInvoice = EInvoiceHelper::generateInvoice($documentHeader, $documentDetails);

            if(!$generateInvoice['Status']){
                return response()->json([
                    'status' => 'error',
                    'message' => "Error: ". @$generateInvoice['ErrorDetails'][0]['ErrorCode'].' -'.$generateInvoice['ErrorDetails'][0]['ErrorMessage'],
                ], 422);
            }

            $documentHeader->irnDetail()->create([
                'ack_no' => $generateInvoice['AckNo'],
                'ack_date' => $generateInvoice['AckDt'],
                'irn_number' => $generateInvoice['Irn'],
                'signed_invoice' => $generateInvoice['SignedInvoice'],
                'signed_qr_code' => $generateInvoice['SignedQRCode'],
                'ewb_no' => $generateInvoice['EwbNo'],
                'ewb_date' => $generateInvoice['EwbDt'],
                'ewb_valid_till' => $generateInvoice['EwbValidTill'],
                'status' => $generateInvoice['Status'],
                'remarks' => $generateInvoice['Remarks']
            ]);
            $transportationMode = EwayBillMaster::find($request->transporter_mode);

            $documentHeader->transporter_name=$request->transporter_name;
            $documentHeader->transportation_mode=$transportationMode?->description ?? null;
            $documentHeader->eway_bill_master_id=$transportationMode?->id ?? null;
            $documentHeader->vehicle_no=$request->vehicle_no;

            $documentHeader->e_invoice_status = ConstantHelper::GENERATED;
            $documentHeader->save();
            //Generate Eway Bill
            if ($documentHeader -> total_amount > EInvoiceHelper::EWAY_BILL_MIN_AMOUNT_LIMIT)
            {
                $data = EInvoiceHelper::generateEwayBill($documentHeader);
                if (isset($data) && (isset($data['status']) && ($data['status'] == 'error'))) {
                    return response()->json([
                        'status' => 'error',
                        'error' => 'error',
                        'message' => 'E-Invoice generated successfully and ' . $data['message'],
                        'redirect' => true
                    ], 500);
                } else{
                    $eInvoice = $documentHeader->irnDetail()->first();
                    $eInvoice->ewb_no = $data['EwbNo'];
                    $eInvoice->ewb_date = date('Y-m-d H:i:s', strtotime($data['EwbDt']));
                    $eInvoice->ewb_valid_till = date('Y-m-d H:i:s', strtotime($data['EwbValidTill']));
                    $eInvoice->save();

                    $documentHeader -> is_ewb_generated = 1;
                    $documentHeader -> save();

                    return response() -> json([
                        'status' => 'success',
                        'results' => $data,
                        'message' => 'E-Invoice and E-Way Bill generated succesfully',
                    ]);
                }
            }
            return response() -> json([
                'status' => 'success',
                'results' => $generateInvoice,
                'message' => 'E-Invoice generated succesfully',
            ]);
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
    public function EInvoiceMail(Request $request)
    {
        $invoice = ErpSaleInvoice::with(['customer'])->find($request->id);
        $customer = $invoice->customer;

        $sendTo = $request->email_to ?? $customer->email;
        $customer->email = $sendTo;

        $title = "Invoice Generated";
        $pattern = "Tax Invoice";
        $remarks = $request->remarks ?? null;

        $mail_from = '';
        $mail_from_name = '';
        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;

        $name = $customer->company_name;
        $viewLink = route('sale.invoice.generate-pdf', ['id' => $request->id, 'pattern' => $pattern]);

        // HTML description
        $description = <<<HTML
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif;">
            <tr>
                <td>
                    <h2 style="color: #2c3e50;">Your Invoice</h2>
                    <p style="font-size: 16px; color: #555;">Dear {$name},</p>
                    <p style="font-size: 15px; color: #333;">{$remarks}</p>
                    <p style="font-size: 15px; color: #333;">
                        Please find the attached invoice PDF for your reference. You may download and review it at your convenience.
                    </p>
                    <p style="font-size: 15px; color: #333;">
                        If you have any questions or need further assistance, feel free to reach out.
                    </p>
                </td>
            </tr>
        </table>
        HTML;
        $attachments = [];

        // Attach generated invoice PDF
        try {
            $pdfContent = $this->generatePdf(
                 $request,
                 $request->id,
                 $pattern,
                 false,
                 true,
            );

            $pdfFileName = "Invoice_{$invoice->document_number}.pdf";
            $attachments[] = [
                'file' => $pdfContent,
                'options' => [
                    'as' => $pdfFileName,
                    'mime' => 'application/pdf',
                ]
            ];
        } catch (\Exception $e) {
            // Handle PDF generation failure (optional log or notify)
            \Log::error("Failed to generate invoice PDF for email: " . $e->getMessage());
        }

        // Attach any uploaded files
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

        // Send email with attachments
        return self::sendMail(
            $customer,
            $title,
            $description,
            $cc,
            $attachments,
            $mail_from,
            $mail_from_name
        );
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

    public function invoicePod(Request $request)
    {
        //ALLOWED_EXTENSIONS = ['doc', 'docx', 'odt', 'rtf', 'txt', 'xls', 'xlsx', 'ods', 'csv','ppt', 'pptx', 'odp', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'svg', 'ico', 'webp'
        // $request->validate([
        //     'remarks' => 'nullable|string|max:255',
        //     'attachment' => 'required|mime:pdf,jpg,jpeg,png,gif,bmp,tiff,tif,svg,ico,webp,doc,docx,odt,rtf,txt,xls,xlsx,ods,csv,ppt,pptx,odp|max:2048',
        // ]);
        DB::beginTransaction();
        try {
            $saleInvoice = ErpSaleInvoice::find($request->id);
            $bookId = $saleInvoice->book_id;
            $docId = $saleInvoice->id;
            $docValue = $saleInvoice->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $saleInvoice->approval_level;
            $revisionNumber = $saleInvoice->revision_number ?? 0;
            $actionType = "Delivered"; // Approve or reject
            $modelName = get_class($saleInvoice);
            $saleInvoice->delivery_status = 1;
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $saleInvoice->save();

            DB::commit();
            return response()->json([
                'message' => "POD Updated successfully!",
                'data' => $saleInvoice,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while Updating POD of the document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateEwayBill(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vehicle_no' => [
                    'required',
                    'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{0,3}[0-9]{4}$/'
                ],
                'transporter_mode' => 'required|integer',
                "transporter_name" => [
                   "required",
                   'string'
                ],
            ],
            [
                'vehicle_no.required' => 'Vehicle number is required.',
                'vehicle_no.regex' => 'Vehicle number format is invalid. Example: MH12AB1234.',
                'transporter_mode.required' => 'Transporter mode is required.',
                'transporter_mode.integer' => 'Transporter mode must be an integer.',
                'transporter_name.required' => 'Transporter name is required.',
                'transporter_name.string' => 'Transporter name must be a string.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages()->first(),
            ], 422);
        }
        $user = Helper::getAuthenticatedUser();

        try{
            $documentHeader = ErpSaleInvoice::find($request->id);
            $transportationMode = EwayBillMaster::find($request->transporter_mode);
            $documentHeader->transporter_name=$request->transporter_name;
            $documentHeader->transportation_mode=$transportationMode?->description ?? null;
            $documentHeader->eway_bill_master_id=$transportationMode?->id ?? null;
            $documentHeader->vehicle_no=$request->vehicle_no;
            $data = EInvoiceHelper::generateEwayBill($documentHeader);
            if (isset($data) && (isset($data['status']) && ($data['status'] == 'error'))) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'error',
                    'message' => $data['message'],
                ], 500);
            } else{
                $eInvoice = $documentHeader->irnDetail()->first();
                $eInvoice->ewb_no = $data['EwbNo'];
                $eInvoice->ewb_date = date('Y-m-d H:i:s', strtotime($data['EwbDt']));
                $eInvoice->ewb_valid_till = date('Y-m-d H:i:s', strtotime($data['EwbValidTill']));
                $eInvoice->save();

                $documentHeader -> is_ewb_generated = 1;
                $documentHeader -> save();

                return response() -> json([
                    'status' => 'success',
                    'results' => $data,
                    'message' => 'E-Way bill generated succesfully',
                ]);
            }
            
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'error',
                'message' => $ex -> getMessage(),
            ]);
        }
    }
    
    public function salesInvoiceReport(Request $request)
    {
        $pathUrl = route('sale.invoice.index');
        $orderType = [ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS];
        $salesOrders = ErpSaleInvoice::with('items')->whereIn('document_type', $orderType) ->where('invoice_required', 0)-> withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> orderByDesc('id');
        //Customer Filter
        $salesOrders = $salesOrders -> when($request -> customer_id, function ($custQuery) use($request) {
            $custQuery -> where('customer_id', $request -> customer_id);
        });
        //Book Filter
        $salesOrders = $salesOrders -> when($request -> book_id, function ($bookQuery) use($request) {
            $bookQuery -> where('book_id', $request -> book_id);
        });
        //Document Id Filter
        $salesOrders = $salesOrders -> when($request -> document_number, function ($docQuery) use($request) {
            $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
        });
        //Location Filter
        $salesOrders = $salesOrders -> when($request -> location_id, function ($docQuery) use($request) {
            $docQuery -> where('store_id', $request -> location_id);
        });
        //Company Filter
        $salesOrders = $salesOrders -> when($request -> company_id, function ($docQuery) use($request) {
            $docQuery -> where('store_id', $request -> company_id);
        });
        //Organization Filter
        $salesOrders = $salesOrders -> when($request -> organization_id, function ($docQuery) use($request) {
            $docQuery -> where('organization_id', $request -> organization_id);
        });
        //Document Status Filter
        $salesOrders = $salesOrders -> when($request -> doc_status, function ($docStatusQuery) use($request) {
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
        $salesOrders = $salesOrders -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
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
        $salesOrders = $salesOrders -> when($request -> item_id, function ($itemQuery) use($request) {
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
        //Order No Filter
        $salesOrders = $salesOrders -> when($request -> so_no, function ($orderNoQuery) use($request) {
            $orderNoQuery -> whereHas('items', function ($soItemQuery) use($request) {
                $soItemQuery -> where('sale_order_id', $request -> so_no);
            });
        });
        //SO Date Range Filter
        $salesOrders = $salesOrders -> when($request -> so_dt, function ($orderDtQuery) use($request) {
            $orderDtQuery -> whereDate('document_date', '>=', $request -> so_dt[0])
                           -> whereDate('document_date', '<=', $request -> so_dt[1]);
        });
        $salesOrders = $salesOrders -> get();
        $processedSalesOrder = collect([]);
        foreach ($salesOrders as $saleOrder) {
            foreach ($saleOrder -> items as $soItem) {
                $reportRow = new stdClass();
                //Header Details
                $header = $soItem -> header;
                $reportRow -> id = $soItem -> id;
                $reportRow -> book_name = $header -> book_code;
                $reportRow -> document_number = $header -> document_number;
                $reportRow -> document_date = $header -> document_date;
                $reportRow -> store_name = $header -> erpstore ?-> store_name;
                $reportRow -> customer_name = $header -> customer ?-> company_name;
                $reportRow -> customer_currency = $header -> currency_code;
                $reportRow -> payment_terms_name = $header -> payment_term_code;
                //Item Details
                $reportRow -> item_name = $soItem -> item_name;
                $reportRow -> item_code = $soItem -> item_code;
                $reportRow -> hsn_code = $soItem -> hsn ?-> code;
                $reportRow -> uom_name = $soItem -> uom ?-> name;
                //Amount Details
                $reportRow -> si_qty = number_format($soItem -> order_qty, 2);
                $reportRow -> dnote_qty = number_format($soItem -> dnote_qty, 2);
                $reportRow -> srn_qty = number_format($soItem -> srn_qty, 2);
                $reportRow -> so_qty = number_format($soItem -> sale_order_item() ?-> order_qty ?? 0.00, 2);
                $reportRow -> so_date = $soItem ?-> sale_order ?-> document_date ?? " ";
                $reportRow -> so_no = $soItem->sale_order ? $soItem ?-> sale_order ?-> document_number."-".$soItem ?-> sale_order ?-> document_number : " ";
                $reportRow -> rate = number_format($soItem -> rate, 2);
                $reportRow -> total_discount_amount = number_format($soItem -> header_discount_amount + $soItem -> item_discount_amount, 2);
                $reportRow -> tax_amount = number_format($soItem -> tax_amount, 2);
                $reportRow -> taxable_amount = number_format($soItem -> total_item_amount - $soItem -> tax_amount, 2);
                $reportRow -> total_item_amount = number_format($soItem -> total_item_amount, 2);
                $reportRow -> pending_qty = number_format($soItem -> order_qty - $soItem -> srn_qty, 2);
                //Delivery Schedule UI
                // $deliveryHtml = '';
                // if (count($soItem -> item_deliveries) > 0) {
                //     foreach ($soItem -> item_deliveries as $itemDelivery) {
                //         $deliveryDate = Carbon::parse($itemDelivery -> delivery_date) -> format('d-m-Y');
                //         $deliveryQty = number_format($itemDelivery -> qty, 2);
                //         $deliveryHtml .= "<span class='badge rounded-pill badge-light-primary'><strong>$deliveryDate</strong> : $deliveryQty</span>";
                //     }
                // } else {
                //     $parsedDeliveryDate = Carbon::parse($soItem -> delivery_date) -> format('d-m-Y');
                //     $deliveryHtml .= "$parsedDeliveryDate";
                // }
                // $reportRow -> delivery_schedule = $deliveryHtml;
                //Attributes UI
                $attributesUi = '';
                if (count($soItem -> item_attributes) > 0) {
                    foreach ($soItem -> item_attributes as $soAttribute) {
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
                $editRoute = route('sale.invoice.edit', ['id' => $row->id]);
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