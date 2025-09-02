<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\DynamicFieldHelper;
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
use App\Helpers\TransactionReportHelper;
use App\Http\Requests\ErpSaleReturnRequest;
use App\Jobs\SendEmailJob;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Country;
use App\Models\Address;
use App\Models\ErpAddress;
use App\Models\Department;
use App\Models\ErpAttribute;
use App\Models\ErpInvoiceItem;
use App\Models\ErpInvoiceItemAttribute;
use App\Models\ErpInvoiceItemLocation;
use App\Models\ErpItemAttribute;
use App\Models\ErpRack;
use App\Models\ErpSaleReturn;
use App\Models\ErpSrDynamicField;
use App\Models\ErpSaleReturnItem;
use App\Models\ErpSaleReturnItemLocation;
use App\Models\ErpSaleReturnItemAttribute;
use App\Models\ErpSaleReturnHistory;
use App\Models\ErpSaleReturnTed;
use App\Models\ErpSaleOrder;
use App\Models\EwayBillMaster;
use App\Models\ErpSoMedia;
use App\Models\ErpSrItemLotDetail;
use App\Models\ErpSrMedia;
use App\Models\Item;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\LandLease;
use App\Models\LandLeaseScheduler;
use App\Models\LandParcel;
use App\Models\NumberPattern;
use App\Models\Organization;
use Carbon\Carbon;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PDF;
use Exception;
use Illuminate\Http\Request;
use Storage;
use Validator;
use Yajra\DataTables\DataTables;

use stdClass;

class ErpSaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = SaleModuleHelper::SALES_RETURN_DEFAULT_TYPE;
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
        $redirectUrl = route('sale.return.index');
        $createRoute = route('sale.return.create');
        request()->merge(['type' => $orderType]);
        $typeName = SaleModuleHelper::getAndReturnReturnTypeName($orderType);
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        $create_button = (isset($servicesBooks)  && count($servicesBooks['services']) > 0 && $selectedfyYear['authorized'] && !$selectedfyYear['lock_fy']) ? true : false;
        $autoCompleteFilters = self::getBasicFilters();
        request() -> merge(['type' => $orderType]);
        if ($request -> ajax()) {
            $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
            $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
            //Date Filters
            $dateRange = $request -> date_range ??  null;
            
            $returns = ErpSaleReturn::withDefaultGroupCompanyOrg()
                -> whereIn('store_id',$accessible_locations)
                ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']])
                ->withDraftListingLogic()
                -> when($request -> customer_id, function ($custQuery) use($request) {
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
                }) -> orderByDesc('id');

            return DataTables::of($returns)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) use ($orderType) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $displayStatus = '';
                    $row->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED ? $displayStatus = 'Approved' : $displayStatus = $row->display_status;
                    if ($orderType == SaleModuleHelper::SALES_RETURN_DEFAULT_TYPE) {
                        $editRoute = route('sale.return.edit', ['id' => $row->id]);
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
                    return 'Sales Return';
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
                ->editColumn('total_return_value', function ($row) {
                    return number_format($row->total_return_value, 2);
                })
                ->editColumn('total_discount_value', function ($row) {
                    return number_format($row->total_discount_value, 2);
                })
                ->editColumn('total_tax_value', function ($row) {
                    return number_format($row->total_tax_value, 2);
                })
                ->editColumn('total_expense_value', function ($row) {
                    return number_format($row->total_expense_value, 2);
                })
                ->editColumn('grand_total_amount', function ($row) {
                    return number_format($row->total_amount, 2);
                })
                ->editColumn('e_invoice_status', function ($row) {
                    return ucfirst($row->e_invoice_status);
                })
                ->editColumn('delivery_status', function ($row) {
                    return ucfirst($row->delivery_status ? 'Delivered' : 'Not Delivered');
                })
                ->editColumn('is_ewb_generated', function ($row) {
                    return ucfirst($row->total_amount > EInvoiceHelper::EWAY_BILL_MIN_AMOUNT_LIMIT && $row -> irnDetail ? ($row -> is_ewb_generated ? 'Generated' : 'Pending') : '');
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }

        return view('salesReturn.index', [
            'typeName' => $typeName,
            'redirect_url' => $redirectUrl,
            'create_route' => $createRoute,
            'filterArray' => TransactionReportHelper::FILTERS_MAPPING[ConstantHelper::SR_SERVICE_ALIAS],
            'autoCompleteFilters' => $autoCompleteFilters,
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
        $parentURL = request()->segments()[0];
        $redirectUrl = route('sale.return.index');
        $user = Helper::getAuthenticatedUser();
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
        $users = AuthUser::where('organization_id', $user -> organization_id) -> where('status', ConstantHelper::ACTIVE) -> get();

        $type = SaleModuleHelper::getAndReturnReturnType($request->type ?? ConstantHelper::SR_SERVICE_ALIAS);
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL,'',$user);
        $firstService = $servicesBooks['services'][0];
        $bookType = $type;
        $typeName = "Sales Return";
        if ($typeName == ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
            $typeName = "Delivery Note";
        } else {
            $typeName = "Sales Return";
        }
        // $stores = ErpStore::withDefaultGroupCompanyOrg()->where('store_location_type', ConstantHelper::STOCKK)->get();
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $organization = Organization::where('id', $user->organization_id)->first();
        // $departments = Department::where('organization_id', $organization->id)
        //     ->where('status', ConstantHelper::ACTIVE)
        //     ->get();
        $books = Helper::getBookSeries($bookType)->get();
        $countries = Country::select('id AS value', 'name AS label')->where('status', ConstantHelper::ACTIVE)->get();
        $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
        $data = [
            'user' => $user,
            'users' => $users,
            'stores' => $stores,
            // 'departments' => $departments,
            'services' => $servicesBooks['services'],
            'selectedService' => $firstService?->id ?? null,
            'series' => $books,
            'countries' => $countries,
            'type' => $type,
            'typeName' => $typeName,
            'redirect_url' => $redirectUrl,
            'current_financial_year' => $selectedfyYear,
            'transportationModes' => $transportationModes,
            'einvoice' => null

        ];
        return view('salesReturn.create_edit', $data);
    }
    public function edit(Request $request, string $id)
    {
        try {

            $user = Helper::getAuthenticatedUser();
            $users = AuthUser::where('organization_id', $user -> organization_id) -> where('status', ConstantHelper::ACTIVE) -> get();
            if (isset($request->revisionNumber)) {
                $order = ErpSaleReturnHistory::with(['discount_ted', 'media_files' ,'expense_ted', 'billing_address_details', 'shipping_address_details','location_address_details'])->with('items', function ($query) {
                    $query->with('discount_ted', 'tax_ted', 'item_locations','item_attributes')->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                })
                    ->where('revision_number',$request->revisionNumber)
                    ->where('source_id', $id)->first();
                $ogReturn = ErpSaleReturn::find($id);
            } else {
                $order = ErpSaleReturn::with(['discount_ted', 'media_files' ,'expense_ted', 'billing_address_details', 'shipping_address_details'])->with('items', function ($query) {
                    $query->with('discount_ted', 'tax_ted', 'item_locations','attributes')->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                })->find($id);
                $ogReturn = $order;
            }

            $parentURL = request()->segments()[0];
            $redirectUrl = route('sale.return.index');
            if (isset($order)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL, $order?->book?->service?->alias);
                $firstService = $servicesBooks['services'][0];
                foreach ($order->items as &$siItem) {
                    if ($siItem->si_item_id !== null) {
                        $pulled = ErpInvoiceItem::find($siItem->si_item_id);
                        if (isset($pulled)) {
                            $siItem->max_attribute = $siItem->order_qty + $pulled->return_balance_qty;
                            $siItem->is_editable = false;
                        } else {
                            $siItem->max_attribute = 999999;
                            $siItem->is_editable = true;
                        }
                    } else {
                        $siItem->max_attribute = 999999;
                        $siItem->is_editable = true;
                    }
                    if($order->document_status != ConstantHelper::DRAFT){
                        $siItem->is_editable = false;
                    }
                }
            }
            $revision_number = $order->revision_number??null;
            $totalValue = ($order->total_return_value - $order->total_discount_value) + $order->total_tax_value + $order->total_expense_value;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($order->book_id, $order->document_status, $order->id, $totalValue, $order->approval_level, $order->created_by ?? 0, $userType['type'], $revision_number);
            $type = SaleModuleHelper::getAndReturnReturnType($request->type ?? ConstantHelper::SR_SERVICE_ALIAS);
            $books = Helper::getBookSeries($type)->get();
            $selectedfyYear = Helper::getFinancialYear($order->document_date ?? Carbon::now()->format('Y-m-d'));
            $countries = Country::select('id AS value', 'name AS label')->where('status', ConstantHelper::ACTIVE)->get();
            $revNo = $order->revision_number;
            if ($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $order->revision_number;
            }
            $docValue = $order->total_amount ?? 0;
            $approvalHistory = Helper::getApprovalHistory($order->book_id, $ogReturn->id, $revNo, $docValue);
            $order->document_status == 'approval_not_required' ? $display_status = 'Apporved' : $display_status = $order->display_status;
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$order->document_status] ?? '';
            $typeName = "Sales Return";
            if ($typeName == "dnote") {
                $typeName = "Delivery Note";
            } else if ($typeName == 'srdn') {
                $typeName = "Delivery Note CUM Return";
            } else {
                $typeName = "Sales Return";
            }
            $bookType = $type;
            $stores = InventoryHelper::getAccessibleLocations();
            $organization = Organization::where('id', $user->organization_id)->first();
            // $departments = Department::where('organization_id', $organization->id)
            //     ->where('status', ConstantHelper::ACTIVE)
            //     ->get();
            $enableEinvoice = $order -> gst_invoice_type === EInvoiceHelper::B2B_INVOICE_TYPE ? true : false;
            $einvoice = $order -> irnDetail() -> first();
            $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
            $editTransporterFields = false;
            if (!isset($einvoice -> ewb_no) && $order -> total_amount > EInvoiceHelper::EWAY_BILL_MIN_AMOUNT_LIMIT) {
                $editTransporterFields = true;
            }
                $dynamicFieldsUI = $order -> dynamicfieldsUi();

            $data = [
                'user' => $user,
                'users' => $users,
                'services' => $servicesBooks['services'],
                'stores' => $stores,
                // 'departments' => $departments,
                'selectedService' => $firstService?->id ?? null,
                'series' => $books,
                'order' => $order,
                'countries' => $countries,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'type' => $type,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'display_status' => $display_status,
                'redirect_url' => $redirectUrl,
                'einvoice' => $einvoice,
                'transportationModes' => $transportationModes,
                'enableEinvoice' => $enableEinvoice,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'current_financial_year' => $selectedfyYear,
                'editTransporterFields' => $editTransporterFields
            ];
            return view('salesReturn.create_edit', $data);

        } catch (Exception $ex) {
            dd($ex);
        }
    }

    public function store(ErpSaleReturnRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            $type = SaleModuleHelper::getAndReturnReturnType($request->type ?? ConstantHelper::SR_SERVICE_ALIAS);
            $request->merge(['type' => $type]);
            //Auth credentials
            $store = ErpStore::find($request -> store_id);
            $organization = Organization::find($user->organization_id);
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            //Tax Country and State
            $firstAddress = $organization->addresses->first();
            $companyCountryId = null;
            $companyStateId = null;
            if ($firstAddress) {
                $companyCountryId = $store?->address?->country->id??null;
                $companyStateId = $store?->address?->state->id??null;
            } else {
                return response()->json([
                    'message' => 'Please create an organization first'
                ], 422);
            }
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request->currency_id, $request->document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }
            $documentNo = $request->document_number ?? null;
            $itemTaxIds = [];
            $itemAttributeIds = [];
            if (!$request->sale_invoice_id) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_no;
                $regeneratedDocExist = ErpSaleReturn::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
            $saleInvoice = null;
            $transportationMode = EwayBillMaster::find($request->transporter_mode);
            if ($request->sale_return_id) { //Update
                $saleInvoice = ErpSaleReturn::find($request->sale_return_id);
                $saleInvoice->document_date = $request->document_date;
                $saleInvoice->reference_number = $request->reference_no;
                $saleInvoice->consignee_name = $request->consignee_name;
                $saleInvoice->consignment_no = $request->consignment_no;
                $saleInvoice->vehicle_no = $request->vehicle_no;
                $saleInvoice->transporter_name = $request->transporter_name;
                $saleInvoice -> transportation_mode = $transportationMode ?-> description;
                $saleInvoice -> eway_bill_master_id = $transportationMode ?-> id;
                // $saleInvoice->eway_bill_no = $request->eway_bill_no;
                $saleInvoice->remarks = $request->final_remarks;
                $actionType = $request->action_type ?? '';
                //Amend backup
                if (($saleInvoice->document_status == ConstantHelper::APPROVED || $saleInvoice->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpSaleReturn', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpSaleReturnItem', 'relation_column' => 'sale_return_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleReturnItemAttribute', 'relation_column' => 'sale_return_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleReturnItemLocation', 'relation_column' => 'sale_return_item_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleReturnTed', 'relation_column' => 'sale_return_item_id'],
                    ];
                    $a = Helper::documentAmendment($revisionData, $saleInvoice->id);

                }
                $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedSiItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }
                if (count($deletedData['deletedHeaderExpTedIds'])) {
                    ErpSaleReturnTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'])->delete();
                }

                if (count($deletedData['deletedHeaderDiscTedIds'])) {
                    ErpSaleReturnTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'])->delete();
                }

                if (count($deletedData['deletedItemDiscTedIds'])) {
                    ErpSaleReturnTed::whereIn('id', $deletedData['deletedItemDiscTedIds'])->delete();
                }

                if (count($deletedData['deletedSiItemIds'])) {
                    $srItems = ErpSaleReturnItem::whereIn('id', $deletedData['deletedSiItemIds'])->get();
                    # all ted remove item level
                    foreach ($srItems as $srItem) {

                        // if ($siItem -> dnote_item_id) {
                        //     $refSiItem = ErpInvoiceItem::find($siItem -> dnote_item_id);
                        //     if (isset($refSiItem)) {
                        //         $refSiItem -> srn_qty -= $siItem -> invoice_qty;
                        //         $refSiItem -> save(); 
                        //     }
                        // }
                        $srItem->teds()->delete();
                        #delivery remove
                        // $siItem->item_deliveries()->delete();
                        # all attr remove
                        $srItem->attributes()->delete();

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

                        $srItem->delete();
                        if ($srItem->si_item_id) {
                            $siItem = ErpInvoiceItem::find($srItem->si_item_id);

                            if (isset($siItem)) {
                                $siItem->srn_qty -= $srItem->order_qty;
                                if ($siItem->header->document_type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS ||
                                        $siItem->header->document_type === ConstantHelper::SI_SERVICE_ALIAS) {
                                    $siItem->srn_qty -= $srItem->order_qty;
                                }
                                $siItem->save();

                                if ($siItem->so_item_id) {
                                    $soItem = ErpInvoiceItem::find($siItem->si_item_id);
                                    if (isset($soItem)) {
                                        $soItem->srn_qty -= $srItem->order_qty;
                                        if ($siItem->header->document_type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS ||
                                                $siItem->header->document_type === ConstantHelper::SI_SERVICE_ALIAS) {
                                            $soItem->srn_qty -= $srItem->order_qty;
                                        }
                                        $soItem->save();
                                    }

                                }
                            }
                        }
                    }
                }

                if (count($deletedData['deletedAttachmentIds'])) {
                    $files = ErpSrMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
                    foreach ($files as $singleMedia) {
                        $filePath = $singleMedia -> file_name;
                        if (Storage::exists($filePath)) {
                            Storage::delete($filePath);
                        }
                        $singleMedia -> delete();
                    }
                }
            }



            //Delete all Item references
            // foreach ($saleInvoice -> items as $item) {
            //     InventoryHelper::deleteIssueStock($saleInvoice->id, $item->id, $item->item_id, 'invoice', 'issue');
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
            if (!$request->sale_return_id) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_no;
                $regeneratedDocExist = ErpSaleReturn::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
            $saleInvoice = null;
            if ($request->sale_return_id) { //Update
                $saleInvoice = ErpSaleReturn::find($request->sale_return_id);
                $saleInvoice->document_date = $request->document_date;
                $saleInvoice->reference_number = $request->reference_no;
                $saleInvoice->consignee_name = $request->consignee_name;
                $saleInvoice->consignment_no = $request->consignment_no;
                $saleInvoice->vehicle_no = $request->vehicle_no;
                $saleInvoice->transporter_name = $request->transporter_name;
                $saleInvoice -> transportation_mode = $request -> transporter_mode;
                $saleInvoice -> eway_bill_master_id = $transportationMode ?-> id;
                // $saleInvoice->eway_bill_no = $request->eway_bill_no;
                $saleInvoice->remarks = $request->final_remarks;
                //Update all Item references
                // foreach ($saleInvoice->items as $item) {
                //     InventoryHelper::addReturnedStock($saleInvoice->id, $item->id, $item->item_id, 'return', 'receive');
                    // if (($request -> type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $request -> type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS)) {
                    // }
                    // $item -> item_attributes() -> forceDelete();
                    // $item -> discount_ted() -> forceDelete();
                    // $item -> tax_ted() -> forceDelete();
                    // $item -> item_locations() -> forceDelete();
                    // $item -> forceDelete();
                // }
                //Delete all header TEDs
                // foreach ($saleInvoice -> discount_ted as $saleInvoiceDiscount) {
                //     $saleInvoiceDiscount -> forceDelete(); 
                // }
                // foreach ($saleInvoice -> expense_ted as $saleInvoiceExpense) {
                //     $saleInvoiceExpense -> forceDelete(); 
                // }
            } else { //Create
                // $department = Department::find($request -> department_id);
                
                $saleInvoice = ErpSaleReturn::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'document_type' => $type,
                    'document_number' => $request->document_no,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request->document_date,
                    'reference_number' => $request->reference_no,
                    'store_id' => $request->store_id ?? null,
                    'store_code' => $store?->store_code ?? null,
                    // 'department_id' => $request->department_id,
                    // 'department_code' => $department?->name ?? null,
                    'customer_id' => $request->customer_id,
                    'customer_code' => $request->customer_code,
                    'consignee_name' => $request->consignee_name,
                    'consignment_no' => $request->consignment_no,
                    'vehicle_no' => $request->vehicle_no,
                    'transporter_name' => $request->transporter_name,
                    'transportation_mode' => $transportationMode ?-> description,
                    'eway_bill_master_id' => $transportationMode ?-> id,
                    // 'eway_bill_no' => $request->eway_bill_no,
                    'billing_address' => null,
                    'shipping_address' => null,
                    'currency_id' => $request->currency_id,
                    'currency_code' => $request->currency_code,
                    'payment_term_id' => $request->payment_terms_id,
                    'payment_term_code' => $request->payment_terms_code,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => $request->final_remarks,
                    'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                    'total_return_value' => 0,
                    'total_discount_value' => 0,
                    'total_tax_value' => 0,
                    'total_expense_value' => 0,
                ]);
                //Billing Address
                $customerBillingAddress = ErpAddress::find($request->billing_address);
                if (isset($customerBillingAddress)) {
                    $billingAddress = $saleInvoice->billing_address_details()->create([
                        'address' => $customerBillingAddress->address,
                        'country_id' => $customerBillingAddress->country_id,
                        'state_id' => $customerBillingAddress->state_id,
                        'city_id' => $customerBillingAddress->city_id,
                        'type' => 'billing',
                        'pincode' => $customerBillingAddress->pincode,
                        'phone' => $customerBillingAddress->phone,
                        'fax_number' => $customerBillingAddress->fax_number
                    ]);
                }
                // Shipping Address
                $customerShippingAddress = ErpAddress::find($request->shipping_address);
                if (isset($customerShippingAddress)) {
                    $shippingAddress = $saleInvoice->shipping_address_details()->create([
                        'address' => $customerShippingAddress->address,
                        'country_id' => $customerShippingAddress->country_id,
                        'state_id' => $customerShippingAddress->state_id,
                        'city_id' => $customerShippingAddress->city_id,
                        'type' => 'shipping',
                        'pincode' => $customerShippingAddress->pincode,
                        'phone' => $customerShippingAddress->phone,
                        'fax_number' => $customerShippingAddress->fax_number
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
            }
            $saleInvoice -> gst_invoice_type = EInvoiceHelper::getGstInvoiceType($request -> customer_id, $saleInvoice -> shipping_address_details -> country_id, $saleInvoice ?->  location_address_details ?-> country_id);
            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpSrDynamicField::class, $saleInvoice -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            //Get Header Discount
            $totalHeaderDiscount = 0;
            if (isset($request->order_discount_value) && count($request->order_discount_value) > 0)
                foreach ($request->order_discount_value as $orderDiscountValue) {
                    $totalHeaderDiscount += $orderDiscountValue;
                }
            //Initialize item discount to 0
            $itemTotalDiscount = 0;
            $itemTotalValue = 0;
            $totalTax = 0;
            $totalItemValueAfterDiscount = 0;

            $saleInvoice->billing_address = $request -> billing_address ??  null;
            $saleInvoice->shipping_address = $request -> shipping_address ?? null;
            $saleInvoice->save();
            //Seperate array to store each item calculation
            $itemsData = array();
            if ($request->item_id && count($request->item_id) > 0) {
                //Items
                $totalValueAfterDiscount = 0;
                foreach ($request->item_id as $itemKey => $itemId) {
                    $item = Item::find($itemId);
                    if (isset($item)) {
                        $itemValue = (isset($request->item_qty[$itemKey]) ? $request->item_qty[$itemKey] : 0) * (isset($request->item_rate[$itemKey]) ? $request->item_rate[$itemKey] : 0);
                        $itemDiscount = 0;
                        //Item Level Discount
                        if (isset($request->item_discount_value[$itemKey]) && count($request->item_discount_value[$itemKey]) > 0) {
                            foreach ($request->item_discount_value[$itemKey] as $itemDiscountValue) {
                                $itemDiscount += $itemDiscountValue;
                            }
                        }
                        $itemTotalValue += $itemValue;
                        $itemTotalDiscount += $itemDiscount;
                        $itemValueAfterDiscount = $itemValue - $itemDiscount;
                        $totalValueAfterDiscount += $itemValueAfterDiscount;
                        $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                        $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0);
                        array_push($itemsData, [
                            'sale_return_id' => $saleInvoice->id,
                            'item_id' => $item->id,
                            'item_code' => $item->item_code,
                            'item_name' => $item->item_name,
                            'hsn_id' => $item->hsn_id,
                            'hsn_code' => $item->hsn?->code,
                            'uom_id' => isset($request->uom_id[$itemKey]) ? $request->uom_id[$itemKey] : null, //Need to change
                            'uom_code' => isset($request->item_uom_code[$itemKey]) ? $request->item_uom_code[$itemKey] : null,
                            'order_qty' => isset($request->item_qty[$itemKey]) ? $request->item_qty[$itemKey] : 0,
                            'store_id' => isset($request->item_store[$itemKey])?$request->item_store[$itemKey]:null,
                            'invoice_qty' => 0,
                            'inventory_uom_id' => $item->uom?->id,
                            'inventory_uom_code' => $item->uom?->name,
                            'inventory_uom_qty' => $inventoryUomQty,
                            'rate' => isset($request->item_rate[$itemKey]) ? $request->item_rate[$itemKey] : 0,
                            'item_discount_amount' => $itemDiscount,
                            'header_discount_amount' => 0,
                            'item_expense_amount' => 0, //Need to change
                            'header_expense_amount' => 0, //Need to change
                            'tax_amount' => 0,
                            'company_currency_id' => null,
                            'company_currency_exchange_rate' => null,
                            'group_currency_id' => null,
                            'group_currency_exchange_rate' => null,
                            'remarks' => isset($request->item_remarks[$itemKey]) ? $request->item_remarks[$itemKey] : null,
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
                    $partyCountryId = isset($billingAddress) ? $billingAddress->country_id : null;
                    $partyStateId = isset($billingAddress) ? $billingAddress->state_id : null;
                    $taxDetails = TaxHelper::calculateTax($itemDataValue['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'sale');
                    if (isset($taxDetails) && count($taxDetails) > 0) {
                        foreach ($taxDetails as $taxDetail) {
                            $itemTax += ((double) $taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
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
                    // dd($itemDataValue);
                    $itemRowData = [
                        'sale_return_id' => $saleInvoice->id,
                        'item_id' => $itemDataValue['item_id'],
                        'item_code' => $itemDataValue['item_code'],
                        'item_name' => $itemDataValue['item_name'],
                        'hsn_id' => $itemDataValue['hsn_id'],
                        'hsn_code' => $itemDataValue['hsn_code'],
                        'uom_id' => $itemDataValue['uom_id'], //Need to change
                        'uom_code' => $itemDataValue['inventory_uom_code'],
                        'store_id' => $itemDataValue['store_id'],
                        'order_qty' => $itemDataValue['order_qty'],
                        'rate' => $itemDataValue['rate'],
                        'item_discount_amount' => $itemDataValue['item_discount_amount'],
                        'header_discount_amount' => $headerDiscount,
                        'item_expense_amount' => $itemExpenseAmount,
                        'header_expense_amount' => $itemHeaderExpenseAmount,
                        'total_item_amount' => ($itemDataValue['order_qty'] * $itemDataValue['rate']) - ($itemDataValue['item_discount_amount'] + $headerDiscount) + ($itemExpenseAmount + $itemHeaderExpenseAmount) + $itemTax,
                        'tax_amount' => $itemTax,
                        'remarks' => $itemDataValue['remarks'],
                    ];
                    // dd($request->si_item_id[$itemDataKey]);
                    if (isset($request->si_item_id[$itemDataKey])) {
                        $oldSoItem = ErpSaleReturnItem::find($request->si_item_id[$itemDataKey]);
                        $soItem = ErpSaleReturnItem::updateOrCreate(['id' => $request->si_item_id[$itemDataKey]], $itemRowData);
                    } else {
                        $soItem = ErpSaleReturnItem::create($itemRowData);
                    }

                    //Order Pulling condition 
                    if (($request->quotation_item_ids && isset($request->quotation_item_ids[$itemDataKey]) && isset($request->quotation_item_type[$itemDataKey])) || $soItem->si_item_id) {
                        $pullType = $request->quotation_item_type[$itemDataKey];
                        if ($pullType === ConstantHelper::SI_SERVICE_ALIAS) {
                            $qtItem = ErpInvoiceItem::find($request->quotation_item_ids[$itemDataKey]);
                            if (isset($qtItem)) {
                                $extraQty = isset($oldSoItem->order_qty) ? $oldSoItem->order_qty : 0;
                                $qtItem->srn_qty = $qtItem->srn_qty + $itemDataValue['order_qty'] - $extraQty;
                                $qtItem->save();
                                $soItem->si_item_id = $qtItem?->id;
                                $soItem->save();
                                if (isset($qtItem->so_item_id)) {
                                    $orderItem = ErpSOItem::find($qtItem->so_item_id);
                                    $extraQty = isset($oldSoItem->order_qty) ? $oldSoItem->order_qty : 0;
                                    $orderItem->srn_qty = $orderItem->srn_qty + $itemDataValue['order_qty'] - $extraQty;
                                    $orderItem->save();
                                }
                            }
                        } else if ($pullType === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                            $qtItem = ErpInvoiceItem::find($request->quotation_item_ids[$itemDataKey]);
                            if (isset($qtItem)) {
                                $qtItem->srn_qty = $qtItem->srn_qty + $itemDataValue['order_qty'] - isset($oldSoItem->order_qty) ?? 0;
                                $qtItem->save();
                                $soItem->si_item_id = $qtItem?->id;
                                $soItem->save();
                                if (isset($qtItem->so_item_id)) {
                                    $orderItem = ErpSOItem::find($qtItem->so_item_id);
                                    // dd($qtItem->so_item_id);
                                    $orderItem->srn_qty = $orderItem->srn_qty + $itemDataValue['order_qty'] - isset($oldSoItem->order_qty) ?? 0;
                                    // dd($qtItem->srn_qty);
                                    $orderItem->save();
                                }
                            }
                        }

                    }

                    //TED Data (DISCOUNT)
                    if (isset($request->item_discount_value[$itemDataKey])) {
                        foreach ($request->item_discount_value[$itemDataKey] as $itemDiscountKey => $itemDiscountTed) {
                            $itemDiscountRowData = [
                                'sale_return_id' => $saleInvoice->id,
                                'sale_return_item_id' => $soItem->id,
                                'ted_type' => 'Discount',
                                'ted_level' => 'D',
                                'ted_id' => isset($request->item_discount_master_id[$itemDataKey][$itemDiscountKey]) ? $request->item_discount_master_id[$itemDataKey][$itemDiscountKey] : null,
                                'ted_name' => isset($request->item_discount_name[$itemDataKey][$itemDiscountKey]) ? $request->item_discount_name[$itemDataKey][$itemDiscountKey] : null,
                                'assessment_amount' => $itemDataValue['rate'] * $itemDataValue['order_qty'],
                                'ted_percentage' => isset($request->item_discount_percentage[$itemDataKey][$itemDiscountKey]) ? $request->item_discount_percentage[$itemDataKey][$itemDiscountKey] : null,
                                'ted_amount' => $itemDiscountTed,
                                'applicable_type' => 'Deduction',
                            ];
                            if (isset($request->item_discount_id[$itemDataKey][$itemDiscountKey])) {
                                $soItemTedForDiscount = ErpSaleReturnTed::updateOrCreate(['id' => $request->item_discount_id[$itemDataKey][$itemDiscountKey]], $itemDiscountRowData);
                            } else {
                                $soItemTedForDiscount = ErpSaleReturnTed::create($itemDiscountRowData);
                            }

                        }
                    }
                    //TED Data (TAX)
                    if (isset($taxDetails) && count($taxDetails) > 0) {
                        foreach ($taxDetails as $taxDetail) {

                            $soItemTedForDiscount = ErpSaleReturnTed::updateOrCreate(
                                [
                                    'sale_return_id' => $saleInvoice->id,
                                    'sale_return_item_id' => $soItem->id,
                                    'ted_type' => 'Tax',
                                    'ted_level' => 'D',
                                    'ted_id' => $taxDetail['id'],
                                ],
                                [
                                    'ted_group_code' => $taxDetail['tax_group'],
                                    'ted_name' => $taxDetail['tax_type'],
                                    'assessment_amount' => $valueAfterHeaderDiscount,
                                    'ted_percentage' => (double) $taxDetail['tax_percentage'],
                                    'ted_amount' => ((double) $taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount),
                                    'applicable_type' => $taxDetail['applicability_type'],
                                ]
                            );
                            array_push($itemTaxIds, $soItemTedForDiscount->id);

                        }
                    }

                    //Item Attributes
                    if (isset($request->item_attributes[$itemDataKey])) {
                        $attributesArray = json_decode($request->item_attributes[$itemDataKey], true);
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
                                $itemAttribute = ErpSaleReturnItemAttribute::updateOrCreate(
                                    [
                                        'sale_return_id' => $saleInvoice->id,
                                        'sale_return_item_id' => $soItem->id,
                                        'item_attribute_id' => $attribute['id'],
                                    ],
                                    [
                                        'item_code' => $soItem->item_code,
                                        'attribute_name' => $attribute['group_name'],
                                        'attr_name' => $attribute['attribute_group_id'],
                                        'attribute_value' => $attributeVal,
                                        'attr_value' => $attributeValId,
                                    ]
                                );
                                array_push($itemAttributeIds, $itemAttribute->id);
                                // ErpInvoiceItemAttribute::create([
                                //     'sale_invoice_id' => $saleInvoice -> id,
                                //     'invoice_item_id' => $soItem -> id,
                                //     'item_attribute_id' => $attribute['id'],
                                //     'item_code' => $soItem -> item_code,
                                //     'attribute_name' => $attribute['group_name'],
                                //     'attr_name' => $attribute['attribute_group_id'],
                                //     'attribute_value' => $attributeVal,
                                //     'attr_value' => $attributeValId,
                                // ]);
                            }
                        } else {
                            return response()->json([
                                'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid attributes',
                                'error' => ''
                            ], 422);
                        }
                    }

                    // Item Locations (only in case of DN and Inv CUM DN)
                    // if (isset($request -> item_locations[$itemDataKey]) && ($request -> type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $request -> type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS)) {
                    if (isset($request->item_store[$itemDataKey])) {
                        $itemLocations = json_decode($request->item_locations[$itemDataKey], true);
                            
                        // if (json_last_error() === JSON_ERROR_NONE && is_array($itemLocations)) {
                            $item_store = ErpStore::find($request->item_store[$itemDataKey]);
                            ErpSaleReturnItemLocation::where('sale_return_id', $saleInvoice->id)
                            ->where('sale_return_item_id', $soItem->id)
                            ->delete();
                            $total_item_qty = 0;
                            // foreach ($itemLocations as $itemLocationKey => $itemLocationData) {
                                $total_item_qty += $itemLocationData['store_qty'] ?? $request->item_qty[$itemDataKey];
                                if($total_item_qty <= $soItem->order_qty){
//  $itemLocationData && $itemLocationData['store_id']>0 ? $itemLocationData['store_id'] : for reference
                                    ErpSaleReturnItemLocation::create([
                                        'sale_return_id' => $saleInvoice->id,
                                        'sale_return_item_id' => $soItem->id,
                                        'item_id' => $soItem->item_id,
                                        'item_code' => $soItem->item_code,
                                        'store_id' => $item_store->id,
                                        'store_code' => $item_store->store_name ,
                                        'rack_id' =>  null,
                                        'rack_code' => null,
                                        'shelf_id' => null,
                                        'shelf_code' => null,
                                        'bin_id' => null,
                                        'bin_code' => null,
                                        'returned_qty' => $request->item_qty[$itemDataKey],
                                        'inventory_uom_qty' => ItemHelper::convertToBaseUom($itemDataValue['item_id'], $itemDataValue['uom_id'],$request->item_qty[$itemDataKey]) ?? 0
                                    ]);
                                }
                                else{
                                    return response()->json([
                                        'message' => '',
                                        'errors' => ['item_qty.'.$itemDataKey => 'Item Store has invalid item quantity'],
                                    ], 422);
                                }
                            // }
                        // } else {
                        //     return response()->json([
                        //         'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid store locations',
                        //         'error' => ''
                        //     ], 422);
                        // }
                    }

                    // //Media
                    // if ($request->hasFile('attachments')) {
                    //     foreach ($request->file('attachments') as $singleFile) {
                    //         $mediaFiles = $saleInvoice->uploadDocuments($singleFile, 'sale_order', false);
                    //     }
                    // }
                    if (($request->type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $request->type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS)) {
                        //Update Inventory Stock Settlement
                    }
                    // InventoryHelper::settlementOfInventoryAndStock($saleInvoice->id, $soItem->id, 'invoice', $request->document_status ?? ConstantHelper::DRAFT);


                    // Handle Lot Data
                    if (isset($request->item_lots[$itemKey])) {
                        $lotArray = json_decode($request->item_lots[$itemKey], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($lotArray)) {
                            foreach ($lotArray as $lot) {
                                ErpSrItemLotDetail::updateOrCreate(
                                    [
                                        'sr_item_id' => $soItem->id,
                                        'lot_number' => $lot['lot_number'],
                                    ],
                                    [
                                        'lot_qty' => $lot['lot_qty'],
                                        'total_lot_qty' => $lot['total_lot_qty'],
                                        'inventory_uom_qty' => ItemHelper::convertToBaseUom($soItem -> item_id, $soItem -> uom_id, (float)$lot['lot_qty']),
                                        'original_receipt_date' => $lot['original_receipt_date'],
                                    ]
                                );
                            }
                        } else {
                            return response()->json([
                                'message' => 'Item No. ' . ($itemKey + 1) . ' has invalid lot data',
                                'error' => ''
                            ], 422);
                        }
                    }
                    else 
                    {
                        $lot_number = date('Y/M/d', strtotime($saleInvoice->document_date)) . '/' . $saleInvoice->book_code . '/' . $saleInvoice->document_number;
                        ErpSrItemLotDetail::updateOrCreate(
                            [
                                'sr_item_id' => $soItem->id,
                                'lot_number' => strtoupper($lot_number),
                            ],
                            [
                                'lot_qty' => $soItem->order_qty,
                                'total_lot_qty' => $soItem->order_qty,
                                'inventory_uom_qty' => ItemHelper::convertToBaseUom($soItem -> item_id, $soItem -> uom_id, $soItem->order_qty),
                                'original_receipt_date' => $soItem->header->document_date,
                            ]
                        );
                    }
                }

            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please select Items',
                    'error' => "",
                ], 422);
            }

            //Header TED (Discount)
            if (isset($request->order_discount_value) && count($request->order_discount_value) > 0) {
                foreach ($request->order_discount_value as $orderDiscountKey => $orderDiscountVal) {
                    $headerDiscountRowData = [
                        'sale_return_id' => $saleInvoice->id,
                        'invoice_item_id' => null,
                        'ted_type' => 'Discount',
                        'ted_level' => 'H',
                        'ted_id' => isset($request->order_discount_master_id[$orderDiscountKey]) ? $request->order_discount_master_id[$orderDiscountKey] : null,
                        'ted_name' => isset($request->order_discount_name[$orderDiscountKey]) ? $request->order_discount_name[$orderDiscountKey] : null,
                        'assessment_amount' => $totalItemValueAfterDiscount,
                        'ted_percentage' => isset($request->order_discount_percentage[$orderDiscountKey]) ? ($request->order_discount_percentage[$orderDiscountKey]) : null,
                        'ted_amount' => $orderDiscountVal,
                        'applicable_type' => 'Deduction',
                    ];
                    if (isset($request->order_discount_id[$orderDiscountKey])) {
                        ErpSaleReturnTed::updateOrCreate(['id' => $request->order_discount_id[$orderDiscountKey]], $headerDiscountRowData);
                    } else {
                        ErpSaleReturnTed::create($headerDiscountRowData);
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
            if (isset($request->order_expense_value) && count($request->order_expense_value) > 0) {
                foreach ($request->order_expense_value as $orderExpenseKey => $orderExpenseVal) {
                    $headerExpenseRowData = [
                        'sale_return_id' => $saleInvoice->id,
                        'invoice_item_id' => null,
                        'ted_type' => 'Expense',
                        'ted_level' => 'H',
                        'ted_id' => isset($request->order_expense_master_id[$orderExpenseKey]) ? $request->order_expense_master_id[$orderExpenseKey] : null,
                        'ted_name' => isset($request->order_expense_name[$orderExpenseKey]) ? $request->order_expense_name[$orderExpenseKey] : null,
                        'assessment_amount' => $totalItemValueAfterDiscount,
                        'ted_percentage' => isset($request->order_expense_percentage[$orderExpenseKey]) ? $request->order_expense_percentage[$orderExpenseKey] : null, // Need to change
                        'ted_amount' => $orderExpenseVal,
                        'applicable_type' => 'Collection',
                    ];
                    if (isset($request->order_expense_id[$orderExpenseKey])) {
                        ErpSaleReturnTed::updateOrCreate(['id' => $request->order_expense_id[$orderExpenseKey]], $headerExpenseRowData);
                    } else {
                        ErpSaleReturnTed::create($headerExpenseRowData);
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
            if ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount) + $totalExpenseAmount < 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document Value cannot be less than 0'
                ], 422);
            }
            $saleInvoice->total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
            $saleInvoice->total_return_value = $itemTotalValue;
            $saleInvoice->total_tax_value = $totalTax;
            $saleInvoice->total_expense_value = $totalExpenseAmount;
            $saleInvoice->total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;
            //Approval check
            //Approval check

            if ($request->sale_return_id) { //Update condition
                $bookId = $saleInvoice->book_id;
                $docId = $saleInvoice->id;
                $amendRemarks = $request->amend_remarks ?? null;
                $remarks = $saleInvoice->remarks;
                $amendAttachments = $request->file('amend_attachments');
                $attachments = $request->file('attachment');
                $currentLevel = $saleInvoice->approval_level;
                $modelName = get_class($saleInvoice);
                $actionType = $request->action_type ?? "";
                if (($saleInvoice->document_status == ConstantHelper::APPROVED || $saleInvoice->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    //*amendmemnt document log*/
                    $revisionNumber = $saleInvoice->revision_number + 1;
                    $actionType = 'amendment';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
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
                    $mediaFiles = $saleInvoice->uploadDocuments($singleFile, 'sale_return', false);
                }
            }

            $saleInvoice->document_type = isset($request->type) && in_array($request->type, ConstantHelper::SALE_RETURN_DOC_TYPES) ? $request->type : ConstantHelper::SR_SERVICE_ALIAS;
            $saleInvoice->save();
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
            if($saleInvoice){
                $invoiceLedger = self::maintainStockLedger($saleInvoice);
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
                            $saleInvoice->e_invoice_status=ConstantHelper::GENERATED;
                            $saleInvoice->save();
                        }
                    }
                }
            $saleInvoice -> e_invoice_status = EInvoiceHelper::getEInvoicePendingDocumentStatus($saleInvoice, $saleInvoice -> gst_invoice_type);
            $saleInvoice -> save();
            DB::commit();
            $module = "Sales Return";
            if ($request->type == ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                $module = "Delivery Return";
            } else if ($request->type == ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
                $module = "DN Cum Return";
            }

            return response()->json([
                'message' => $module . " created successfully",
                'redirect_url' => route('sale.return.index', ['type' => $request->type ?? ConstantHelper::SR_SERVICE_ALIAS])
            ]);


        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . $ex->getFile() . $ex->getLine(),
            ], 500);
        }
    }

    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $saleInvoice = ErpSaleReturn::where('id', $id)->first();
            if (!$saleInvoice) {
                return response()->json(['data' => [], 'message' => "Sale Return not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'ErpSaleReturn', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ErpSaleReturnItem', 'relation_column' => 'sale_return_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleReturnItemAttribute', 'relation_column' => 'sale_return_item_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleReturnItemLocation', 'relation_column' => 'return_item_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'ErpSaleReturnTed', 'relation_column' => 'return_item_id'],
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
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType);


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
            return response()->json(['data' => [], 'message' => "An unexpected error occurred. Please try again.", 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    //Function to get all items of sales module depending upon the doc type - order , invoice, delivery note
    public function getInvoiceItemsForPulling(Request $request)
    {
        try {
            $selectedIds = $request->selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
            if ($request->doc_type === ConstantHelper::SI_SERVICE_ALIAS) {
                $referedHeaderId=ErpInvoiceItem::whereIn('id',$selectedIds)?->first()?->header?->id;
                $order = ErpInvoiceItem::whereHas('header', function ($subQuery) use ($request, $applicableBookIds,$referedHeaderId) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    })->where('document_type', ConstantHelper::SI_SERVICE_ALIAS)->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])->whereIn('book_id', $applicableBookIds)->when($request->customer_id, function ($custQuery) use ($request) {
                        $custQuery->where('customer_id', $request->customer_id);
                    })->when($request->book_id, function ($bookQuery) use ($request) {
                        $bookQuery->where('book_id', $request->book_id);
                    })->when($request->document_id, function ($docQuery) use ($request) {
                        $docQuery->where('id', $request->document_id);
                    });
                })-> with('attributes') -> with('uom') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                })->with('header', function ($headerQuery) {
                    $headerQuery->with(['customer', 'shipping_address_details']);
                })->whereColumn('srn_qty', "<", "invoice_qty");
            } else if ($request->doc_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                $referedHeaderId=ErpInvoiceItem::whereIn('id',$selectedIds)?->first()?->header?->id;
                $order = ErpInvoiceItem::whereHas('header', function ($subQuery) use ($request, $applicableBookIds,$referedHeaderId) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    })->whereIn('document_type', [ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS])->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])->whereIn('book_id', $applicableBookIds)->when($request->customer_id, function ($custQuery) use ($request) {
                        $custQuery->where('customer_id', $request->customer_id);
                    })->when($request->book_id, function ($bookQuery) use ($request) {
                        $bookQuery->where('book_id', $request->book_id);
                    })->when($request->document_id, function ($docQuery) use ($request) {
                        $docQuery->where('id', $request->document_id);
                    });
                })-> with('attributes') -> with('uom') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                })->with('header', function ($headerQuery) {
                    $headerQuery->with(['customer', 'shipping_address_details']);
                })->whereColumn('dnote_qty', ">", "srn_qty")->whereColumn('dnote_qty', ">", "srn_qty");
            } else {
                $order = null;
            }
            if ($request->item_id && isset($order) && $request->doc_type !== ConstantHelper::LAND_LEASE) {
                $order = $order->where('item_id', $request->item_id);
            }
            $order = isset($order) ? $order->get() : new Collection();
            $order = $order->values();
            return response()->json([
                'data' => $order
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ]);
        }
    }

    //Function to get all items of sales module depending upon the doc type - order , invoice, delivery note
    public function processPulledItems(Request $request)
    {
        try {
            $modelName = null;
            $headers = [];
            if ($request->doc_type === ConstantHelper::SR_SERVICE_ALIAS) {
                $modelName = resolve("App\\Models\\ErpSaleReturn");
            } else if ($request->doc_type === ConstantHelper::SI_SERVICE_ALIAS || $request->doc_type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                $modelName = resolve("App\\Models\\ErpSaleInvoice");
            } else {
                $modelName = null;
            }
            if (isset($modelName)) {
                $headers = $modelName::with(['discount_ted', 'expense_ted', 'billing_address_details', 'shipping_address_details','location_address_details'])->with('customer', function ($sQuery) {
                    $sQuery->with(['currency', 'payment_terms']);
                })->whereHas('items', function ($subQuery) use ($request) {
                    $subQuery->whereIn('id', $request->items_id);
                })->with('items', function ($itemQuery) use ($request) {
                    $itemQuery->whereIn('id', $request->items_id)->with(['discount_ted', 'tax_ted'])->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                        }
                    ]);
                })->whereIn('id', $request->order_id)->get();
                foreach ($headers as $header) {
                    if ($modelName::class == "App\\Models\\ErpSaleInvoice") {
                        $saleOrderItems = $header->sale_order_items();
                        // dd($saleOrderItems);
                        foreach ($saleOrderItems as &$saleOrderItem) {
                            $saleOrderItem->actual_qty = $saleOrderItem->order_qty;
                        }
                    }
                    foreach ($header->items as $orderItemKey => &$orderItem) {
                       $orderItem->stock_qty = $orderItem->getStockBalanceQty();
                        $lotdata = InventoryHelper::getIssueTransactionLotNumbers($header->document_type, $header->id, $orderItem->id,$orderItem->uom_id);
                        $orderItem->lotdata =$lotdata;
                        $orderItem->item_attributes_array = $orderItem->item_attributes_array();
                        // if (isset($saleOrderItems[$orderItemKey])) {
                        //     $header->items[$orderItemKey] = $saleOrderItems[$orderItemKey];
                        //     $header->items[$orderItemKey]->item_attributes_array = $orderItem->item_attributes_array();
                        // /
                    }
                }
            }
            return response()->json([
                'message' => 'Data found',
                'data' => $headers
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex
            ]);
        }
    }

    // genrate pdf
    public function generatePdf(Request $request, $id, $pattern)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $order = ErpSaleReturn::with(
            [
                'customer',
                'currency',
                'discount_ted',
                'expense_ted',
                'billing_address_details',
                'shipping_address_details'
            ]
        )
            ->with('items', function ($query) {
                $query->with('discount_ted', 'tax_ted', 'item_locations')->with([
                    'item' => function ($itemQuery) {
                        $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                    }
                ]);
            })
            ->find($id);
        // $creator = AuthUser::with(['authUser'])->find($order->created_by);
        // dd($creator,$order->created_by);
        $shippingAddress = $order->shipping_address_details;
        $billingAddress = $order->billing_address_details;

        $approvedBy = Helper::getDocStatusUser(get_class($order), $order -> id, $order -> document_status);

        // dd($user);
        // $type = ConstantHelper::SERVICE_LABEL[$order->document_type];
        $totalItemValue = $order->total_return_value ?? 0.00;
        $totalDiscount = $order->total_discount_value ?? 0.00;
        $totalTaxes = $order->total_tax_value ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $order->total_expense_value ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // $storeAddress = ErpStore::with('address')->where('id',$order->store_id)->get();
        // dd($order->location->address);
        // Path to your image (ensure the file exists and is accessible)
        $approvedBy = Helper::getDocStatusUser(get_class($order), $order -> id, $order -> document_status);
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory

        $eInvoice = $order->irnDetail()->first();
        $qrCodeBase64 = null;
        if (isset($eInvoice)) {
            $qrCodeBase64 = EInvoiceHelper::generateQRCodeBase64($eInvoice->signed_qr_code);
        }

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        

        $data_array = [
            'type' => $pattern,
            'order' => $order,
            'user' => $user,
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
            'approvedBy' => $approvedBy,
            'eInvoice' => $eInvoice,
            'qrCodeBase64' => $qrCodeBase64,
        ];
        $pdfViewFile = 'pdf.sales-document';
        if ($pattern === 'Credit Note') {
            $pdfViewFile = 'pdf.sales-return-pdf';
        }
        $html = view($pdfViewFile,
            $data_array
        )->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $fileName = ($order->book_code . '-' . $order -> document_number);

        $pdfPath = 'sale-returns/pdfs/return_' . $fileName . '.pdf';
        Storage::disk('local')->put($pdfPath, $dompdf->output());

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Einvoice_' .$fileName . '.pdf"');
    }

    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "get");
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()." ".$ex->getLine()." ".$ex->getFile(),
            ]);
        }
    }

    public function postReturn(Request $request)
    {
        try {
            DB::beginTransaction();
            $saleReturn = ErpSaleReturn::find($request->document_id);
            $enableEinvoice = $saleReturn -> gst_invoice_type === EInvoiceHelper::B2B_INVOICE_TYPE ? true : false;
            $eInvoice = $saleReturn?->irnDetail()->first();
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
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post");
            if ($data['status']) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ]);
        }
    }

    public function revokeSalesReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $saleDocument = ErpSaleReturn::find($request->id);
            if (isset($saleDocument)) {
                $revoke = Helper::approveDocument($saleDocument->book_id, $saleDocument->id, $saleDocument->revision_number, '', [], 0, ConstantHelper::REVOKE, $saleDocument->total_amount, get_class($saleDocument));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $saleDocument->document_status = $revoke['approvalStatus'];
                    $saleDocument->save();

                }
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Revoked succesfully',
                ]);

            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex->getMessage());
        }
    }

    private static function maintainStockLedger($saleReturn)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $saleReturn->items->pluck('id')->toArray();
        InventoryHelper::settlementOfInventoryAndStock($saleReturn->id, $detailIds, ConstantHelper::SR_SERVICE_ALIAS, $saleReturn->document_status);
        return true;
    }
    public function getRacksAndBins(Request $request)
    {
        try {
            $storeData = ErpStore::with(['racks', 'bins'])->find($request->store_id);
    
            if (!$storeData) {
                return response()->json([
                    'message' => 'Store not found',
                    'stores' => [
                        'code' => 404,
                        'message' => 'No store data available',
                        'status' => 'error',
                    ]
                ], 404);
            }
    
            $storeResponse = [
                'id' => $storeData->id,
                'store_code' => $storeData->store_code,
                'store_name' => $storeData->store_name,
                'store_location_type' => $storeData->store_location_type,
                'racks' => $storeData->racks->map(function ($rack) {
                    return [
                        'id' => $rack->id,
                        'rack_code' => $rack->rack_code,
                    ];
                }),
                'bins' => $storeData->bins->map(function ($bin) {
                    return [
                        'id' => $bin->id,
                        'bin_code' => $bin->bin_code,
                    ];
                }),
            ];
    
            return response()->json([
                'message' => 'Store details found',
                'stores' => [
                    'code' => 200,
                    'message' => '',
                    'status' => 'success',
                    'store' => $storeResponse,
                ]
            ]);
    
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
    public function getShelfs(Request $request)
    {
        try {
            $rack = ErpRack::with('shelfs')->find($request->rack_id);
            if (!$rack) {
                return response()->json([
                    'message' => 'Rack not found',
                    'data' => [
                        'code' => 404,
                        'message' => 'No rack data available',
                        'status' => 'error',
                    ]
                ], 404);
            }
    
            $rackResponse =$rack->shelfs->map(function ($shelf) {
                return [
                    'id' => $shelf->id,
                    'shelf_code' => $shelf->shelf_code,
                ];
            });
    
            return response()->json([
                'message' => 'Rack details found',
                'shelfs' => $rackResponse,
            ]);
    
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ], 500);
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
            $documentHeader = ErpSaleReturn::find($id);
            $documentHeader = SaleModuleHelper::updateEInvoiceDataFromHelper($documentHeader);
            $documentDetails = ErpSaleReturnItem::where('sale_return_id', $id)->get();
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

            $documentHeader->e_invoice_status=ConstantHelper::GENERATED;
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
     
    public function CreditNoteMail(Request $request)
    {
        $request->validate([
            'email_to' => 'required|email',
        ], [
            'email_to.required' => 'Recipient email is required.',
            'email_to.email' => 'Please enter a valid email address.',
        ]);

        $invoice = ErpSaleReturn::with(['customer'])->find($request->id);
        $customer = $invoice->customer;

        $sendTo = $request->email_to ?? $customer->email;
        $customer->email = $sendTo;

        $title = "Credit Note Generated";
        $pattern = "Credit Note";
        $remarks = $request->remarks ?? null;

        $mail_from = '';
        $mail_from_name = '';
        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
        $name = $customer->company_name;

        $viewLink = route('sale.return.generate-pdf', ['id' => $request->id, 'pattern' => $pattern]);

        $description = <<<HTML
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif;">
            <tr>
                <td>
                    <h2 style="color: #2c3e50;">Your Credit Note</h2>
                    <p style="font-size: 16px; color: #555;">Dear {$name},</p>
                    <p style="font-size: 15px; color: #333;">{$remarks}</p>
                    <p style="font-size: 15px; color: #333;">
                        Please find the attached Credit Note PDF for your reference. You can download and review it at your convenience.
                    </p>
                    <p style="font-size: 15px; color: #333;">
                        If you have any questions or need further assistance, feel free to reach out.
                    </p>
                </td>
            </tr>
        </table>
        HTML;


        $attachments = [];

        // Attach generated credit note PDF
        try {
            $pdfContent = $this->generatePdf(
                $request,
                $request->id,
                $pattern,
            );

            $pdfFileName = "CreditNote_{$invoice->document_number}.pdf";
            $attachments[] = [
                'file' => $pdfContent,
                'options' => [
                    'as' => $pdfFileName,
                    'mime' => 'application/pdf',
                ]
            ];
        } catch (\Exception $e) {
            \Log::error("Failed to generate credit note PDF for email: " . $e->getMessage());
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
    // public function returnPod(Request $request)
    // {
    //     $request->validate([
    //         'remarks' => 'nullable|string|max:255',
    //         'attachment' => 'nullable'
    //     ]);
    //     DB::beginTransaction();
    //     try {
    //         $saleReturn = ErpSaleReturn::find($request->id);
    //         $bookId = $saleReturn->book_id;
    //         $docId = $saleReturn->id;
    //         $docValue = $saleReturn->total_amount;
    //         $remarks = $request->remarks;
    //         $attachments = $request->file('attachments');
    //         $currentLevel = $saleReturn->approval_level;
    //         $revisionNumber = $saleReturn->revision_number ?? 0;
    //         $actionType = "Delivered"; // Approve or reject
    //         $modelName = get_class($saleReturn);
            // $saleReturn->delivery_status = 1;
    //         $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
    //         $saleReturn->save();

    //         DB::commit();
    //         return response()->json([
    //             'message' => "POD Updated successfully!",
    //             'data' => $saleReturn,
    //         ]);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => "Error occurred while Updating POD of the document.",
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
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
            $documentHeader = ErpSaleReturn::find($request->id);
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

    public function salesreturnReport(Request $request)
    {
        $pathUrl = route('sale.return.index');
        $orderType = [ConstantHelper::SR_SERVICE_ALIAS];
        $salesOrders = ErpSaleReturn::with('items')->whereIn('document_type', $orderType)-> withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> orderByDesc('id');
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
            } else if ($request -> doc_status === ConstantHelper::SUBMITTED) {
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
        //Invoice No Filter
        $salesOrders = $salesOrders -> when($request -> si_no, function ($orderNoQuery) use($request) {
            $orderNoQuery -> whereHas('items', function ($soItemQuery) use($request) {
                $soItemQuery -> whereHas('invoice_item', function ($invoiceQuery) use($request) {
                    $invoiceQuery -> whereHas('header', function ($headerQuery) use($request) {
                        $headerQuery -> where('document_number', 'LIKE', '%' . $request -> si_no . '%')
                        ->orWhere('book_code',"LIKE",'%'. $request->si_no . '%');
                    });
                });
            });
        });
        //SI Date Range Filter
        $salesOrders = $salesOrders -> when($request -> si_dt, function ($orderDtQuery) use($request) {
            if (count($request -> si_dt) == 2) {
                $fromDate = Carbon::parse(trim($request -> si_dt[0])) -> format('Y-m-d');
                $toDate = Carbon::parse(trim($request -> si_dt[1])) -> format('Y-m-d');
                $orderDtQuery -> whereDate('document_date', ">=" , $fromDate) -> where('document_date', '<=', $toDate);
           }
           else{
                $fromDate = Carbon::parse(trim($request -> si_dt[0])) -> format('Y-m-d');
                $orderDtQuery -> whereDate('document_date', $fromDate);
            }
        });
        //Order No Filter
        $salesOrders = $salesOrders -> when($request -> so_no, function ($orderNoQuery) use($request) {
            $orderNoQuery -> whereHas('items', function ($soItemQuery) use($request) {
                $soItemQuery -> whereHas('invoice_item', function ($invoiceQuery) use($request) {
                    $invoiceQuery -> whereHas('sale_order', function ($sale_orderQuery) use($request) {
                        $sale_orderQuery -> where('document_number', 'LIKE', '%' . $request -> so_no . '%')
                        -> orWhere('book_code', 'LIKE', '%' . $request -> so_no . '%')
                        ;
                    });
                });
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
                $reportRow -> id = $header -> id;
                $reportRow -> book_name = $header -> book_code;
                $reportRow -> document_number = $header -> document_number;
                $reportRow -> document_date = $header -> document_date;
                $reportRow -> store_name = $header -> erpStore ?-> store_name;
                $reportRow -> customer_name = $header -> customer ?-> company_name;
                $reportRow -> customer_currency = $header -> currency_code;
                $reportRow -> payment_terms_name = $header -> payment_term_code;
                //Item Details
                $reportRow -> item_name = $soItem -> item_name;
                $reportRow -> item_code = $soItem -> item_code;
                $reportRow -> hsn_code = $soItem -> hsn ?-> code;
                $reportRow -> uom_name = $soItem -> uom ?-> name;
                //Amount Details
                $reportRow -> sr_qty = number_format($soItem -> order_qty, 2);
                $reportRow -> si_qty = number_format($soItem -> invoice_item ?-> order_qty ?? 0.00, 2);
                $reportRow -> si_date = $soItem ?-> invoice_item ?-> header ?-> document_date ?? " ";
                $reportRow -> si_no = $soItem->invoice_item ?-> header ? $soItem ?-> invoice_item ?-> header ?-> book_code."-".$soItem ?-> invoice_item ?-> header ?-> document_number : " ";
                $reportRow -> so_qty = number_format($soItem -> invoice_item ?-> sale_order_item() -> order_qty ?? 0.00, 2);
                $reportRow -> so_date = $soItem ?-> invoice_item ?-> sale_order ?-> document_date ?? " ";
                $reportRow -> so_no = $soItem->invoice_item ?-> sale_order ? $soItem ?-> invoice_item ?-> header ?-> book_code."-".$soItem ?-> invoice_item ?-> header ?-> document_number : " ";
                $reportRow -> rate = number_format($soItem -> rate, 2);
                $reportRow -> total_discount_amount = number_format($soItem -> header_discount_amount + $soItem -> item_discount_amount, 2);
                $reportRow -> tax_amount = number_format($soItem -> tax_amount, 2);
                $reportRow -> taxable_amount = number_format($soItem -> total_item_amount - $soItem -> tax_amount, 2);
                $reportRow -> total_item_amount = number_format($soItem -> total_item_amount, 2);
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

