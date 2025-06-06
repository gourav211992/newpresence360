<?php
namespace App\Http\Controllers;

use App\Exports\ExpenseAdviceExport;
use Auth;
use PDF;
use DB;
use View;
use Session;
use Yajra\DataTables\DataTables;


use Illuminate\Http\Request;
use App\Http\Requests\EditExpenseRequest;
use App\Http\Requests\ExpenseAdviseRequest;

use App\Models\ExpenseTed;
use App\Models\ExpenseHeader;
use App\Models\ExpenseDetail;
use App\Models\ExpenseItemLocation;
use App\Models\ExpenseItemAttribute;

use App\Models\ExpenseTedHistory;
use App\Models\ExpenseDetailHistory;
use App\Models\ExpenseHeaderHistory;
use App\Models\ExpenseItemAttributeHistory;

use App\Models\Tax;
use App\Models\Hsn;
use App\Models\Unit;
use App\Models\Book;
use App\Models\Item;
use App\Models\City;
use App\Models\State;
use App\Models\ErpBin;
use App\Models\PoItem;
use App\Models\Vendor;
use App\Models\Country;
use App\Models\Address;
use App\Models\ErpRack;
use App\Models\ErpShelf;
use App\Models\Currency;
use App\Models\ErpStore;
use App\Models\Customer;
use App\Models\ErpSoItem;
use App\Models\ErpAddress;
use App\Models\CostCenter;
use App\Models\PaymentTerm;
use App\Models\Organization;
use App\Models\ErpSaleOrder;
use App\Models\PurchaseOrder;
use App\Models\NumberPattern;
use App\Models\AttributeGroup;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\BookHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\ServiceParametersHelper;
use App\Jobs\SendEmailJob;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Employee;
use App\Models\ErpExpDynamicField;
use App\Models\ErpItem;
use App\Models\ErpVendor;
use App\Services\ExpenseService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB as FacadesDB;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class ExpenseAdviseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }
    public function get_book_no($book_id)
    {
        $data = Helper::generateVoucherNumber($book_id);
        return response()->json($data);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parentUrl = request() -> segments()[0];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $orderType = ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS;
        request() -> merge(['type' => $orderType]);
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = ExpenseHeader::with(
                [
                    'items',
                    'vendor',
                    'erpStore',
                    'costCenters',
                    'currency'
                ]
            )
            ->withDefaultGroupCompanyOrg()
            ->where('company_id', $organization->company_id)
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $route = route('expense-adv.edit', $row->id);
                    $displayStatus = $row->display_status;
                    return "<div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <div class='dropdown' style='display:inline;'>
                            <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                <i data-feather='more-vertical'></i>
                            </button>
                            <div class='dropdown-menu dropdown-menu-end'>
                                <a class='dropdown-item' href='" . $route . "'>
                                    <i data-feather='edit-3' class='me-50'></i>
                                    <span>View/ Edit Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>";
                })
                ->addColumn('book_code', function ($row) {
                    return $row->book ? $row->book?->book_code : 'N/A';
                })
                ->editColumn('document_date', function ($row) {
                    return date('d/m/Y', strtotime($row->document_date)) ?? 'N/A';
                })
                ->addColumn('location', function ($row) {
                    return strval($row->erpStore?->store_name) ?? 'N/A';
                })
                ->addColumn('cost_center', function ($row) {
                    return strval($row->costCenters?->name) ?? 'N/A';
                })
                ->addColumn('currency', function ($row) {
                    return strval($row->currency?->short_name) ?? 'N/A';
                })
                ->addColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('vendor_name', function ($row) {
                    return $row->vendor ? $row->vendor?->company_name : 'N/A';
                })
                ->addColumn('total_items', function ($row) {
                    return $row->items ? count($row->items) : 0;
                })
                ->editColumn('total_item_amount', function ($row) {
                    return number_format($row->total_item_amount,2);
                })
                ->addColumn('total_discount', function ($row) {
                    return number_format($row->total_discount,2);
                })
                ->addColumn('taxable_amount', function ($row) {
                    return number_format(($row->total_item_amount - $row->total_discount),2);
                })
                ->addColumn('total_taxes', function ($row) {
                    return number_format($row->total_taxes,2);
                })
                ->addColumn('expense_amount', function ($row) {
                    return number_format($row->expense_amount,2);
                })
                ->addColumn('total_amount', function ($row) {
                    return number_format($row->total_amount,2);
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }
        return view('procurement.expense-advise.index', [
            'servicesBooks'=>$servicesBooks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $serviceAlias = ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();

        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $customers = Customer::where('status',ConstantHelper::ACTIVE)->get();
        $purchaseOrders = PurchaseOrder::with('vendor')->get();
        $saleOrders = ErpSaleOrder::with('customer')->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        return view('procurement.expense-advise.create', [
            'books'=>$books,
            'vendors' => $vendors,
            'customers'=>$customers,
            'saleOrders'=>$saleOrders,
            'servicesBooks'=>$servicesBooks,
            'purchaseOrders' => $purchaseOrders,
            'locations' =>$locations
        ]);
    }

    # Purchase Order store
    public function store(ExpenseAdviseRequest $request)
    {
        $user = Helper::getAuthenticatedUser();

        DB::beginTransaction();
        try {
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $purchaseOrderId = null;
            $saleOrderId = null;
            //Tax Country and State
            $firstAddress = $organization->addresses->first();
            $companyCountryId = null;
            $companyStateId = null;
            $applicabilityType = '';
            if ($firstAddress) {
                $companyCountryId = $firstAddress->country_id;
                $companyStateId = $firstAddress->state_id;
            } else {
                return response()->json([
                    'message' => 'Please create an organization first'
                ], 422);
            }

            # Expense Header save
            $totalItemValue = 0.00;
            $totalTaxValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalAmount = 0.00;

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request -> currency_id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }

            $expense = new ExpenseHeader();
            $expense->fill($request->all());
            $expense->organization_id = $organization->id;
            $expense->group_id = $organization->group_id;
            $expense->company_id = $organization->company_id;
            $expense->book_code = $request->book_code;
            $expense->series_id = $request->book_id;
            $expense->book_id = $request->book_id;
            $expense->vendor_id = $request->vendor_id;
            $expense->vendor_code = $request->vendor_code;
            $expense->supplier_invoice_no = $request->supplier_invoice_no;
            $expense->supplier_invoice_date = date('Y-m-d', strtotime($request->supplier_invoice_date));
            $expense->billing_to = $request->billing_id;
            $expense->ship_to = $request->shipping_id;
            $expense->billing_address = $request->billing_address;
            $expense->shipping_address = $request->shipping_address;
            $expense->revision_number = 0;
            $document_number = $request->document_number ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
            $regeneratedDocExist = ExpenseHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
            //Again check regenerated doc no
            if (isset($regeneratedDocExist)) {
                return response()->json([
                    'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                    'error' => "",
                ], 422);
            }

            $expense->doc_number_type = $numberPatternData['type'];
            $expense->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $expense->doc_prefix = $numberPatternData['prefix'];
            $expense->doc_suffix = $numberPatternData['suffix'];
            $expense->doc_no = $numberPatternData['doc_no'];

            $expense->document_number = $document_number;
            $expense->document_date = $request->document_date;
            $expense->final_remark = $request->remarks ?? null;

            $expense->total_item_amount = 0.00;
            $expense->total_discount = 0.00;
            $expense->taxable_amount = 0.00;
            $expense->total_taxes = 0.00;
            $expense->total_after_tax_amount = 0.00;
            $expense->expense_amount = 0.00;
            $expense->total_amount = 0.00;
            $expense->store_id = $request->header_store_id ?? '';
            $expense->cost_center_id = $request->cost_center_id ?? '';
            $expense->save();

            $vendorBillingAddress = $expense->billingAddress ?? null;
            $vendorShippingAddress = $expense->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $expense->bill_address_details()->firstOrNew([
                    'type' => 'billing',
                ]);
                $billingAddress->fill([
                    'address' => $vendorBillingAddress->address,
                    'country_id' => $vendorBillingAddress->country_id,
                    'state_id' => $vendorBillingAddress->state_id,
                    'city_id' => $vendorBillingAddress->city_id,
                    'pincode' => $vendorBillingAddress->pincode,
                    'phone' => $vendorBillingAddress->phone,
                    'fax_number' => $vendorBillingAddress->fax_number,
                ]);
                $billingAddress->save();
            }

            if ($vendorShippingAddress) {
                $shippingAddress = $expense->ship_address_details()->firstOrNew([
                    'type' => 'shipping',
                ]);
                $shippingAddress->fill([
                    'address' => $vendorShippingAddress->address,
                    'country_id' => $vendorShippingAddress->country_id,
                    'state_id' => $vendorShippingAddress->state_id,
                    'city_id' => $vendorShippingAddress->city_id,
                    'pincode' => $vendorShippingAddress->pincode,
                    'phone' => $vendorShippingAddress->phone,
                    'fax_number' => $vendorShippingAddress->fax_number,
                ]);
                $shippingAddress->save();
            }
            # Store location address
            if($expense?->erpStore)
            {
                $storeAddress  = $expense?->erpStore->address;
                $storeLocation = $expense->store_address()->firstOrNew();
                $storeLocation->fill([
                    'type' => 'location',
                    'address' => $storeAddress->address,
                    'country_id' => $storeAddress->country_id,
                    'state_id' => $storeAddress->state_id,
                    'city_id' => $storeAddress->city_id,
                    'pincode' => $storeAddress->pincode,
                    'phone' => $storeAddress->phone,
                    'fax_number' => $storeAddress->fax_number,
                ]);
                $storeLocation->save();
            }
            $totalItemValue = 0.00;
            $totalTaxValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalTax = 0;
            $finaltotalTax = 0;

            $totalHeaderDiscount = 0;
            if (isset($request->all()['disc_summary']) && count($request->all()['disc_summary']) > 0)
            foreach ($request->all()['disc_summary'] as $DiscountValue) {
                $totalHeaderDiscount += floatval($DiscountValue['d_amnt']) ?? 0.00;
            }

            $totalHeaderExpense = 0;
            if (isset($request->all()['exp_summary']) && count($request->all()['exp_summary']) > 0)
            foreach ($request->all()['exp_summary'] as $expValue) {
                $totalHeaderExpense += floatval($expValue['e_amnt']) ?? 0.00;
            }

            if (isset($request->all()['components'])) {
                $expenseItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $so_detail_id = null;
                    $po_detail_id = null;
                    $so_id = null;
                    if(isset($component['po_detail_id']) && $component['po_detail_id']){
                        $poDetail =  PoItem::find($component['po_detail_id']);
                        $po_detail_id = $poDetail->id ?? null;
                        $purchaseOrderId = $poDetail->purchase_order_id;
                        if($poDetail){
                            $poDetail->grn_qty += floatval($component['accepted_qty']);
                            $poDetail->save();
                            $so_id = $poDetail->so_id;
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    $inventoryUom = Unit::find($item->uom_id ?? null);
                    $inventory_uom_id = $inventoryUom->id;
                    $inventory_uom_code = $inventoryUom->name;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_qty = floatval($component['accepted_qty']) ?? 0.00 ;
                    } else {
                        $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $inventory_uom_qty = floatval($component['accepted_qty']) * $alUom->conversion_to_inventory;
                        }
                    }

                    $itemValue = floatval($component['accepted_qty']) * floatval($component['rate']);
                    $itemDiscount = floatval($component['discount_amount']) ?? 0.00;

                    $itemTotalValue += $itemValue;
                    $itemTotalDiscount += $itemDiscount;
                    $itemValueAfterDiscount = $itemValue - $itemDiscount;
                    $totalValueAfterDiscount += $itemValueAfterDiscount;
                    $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                    $uom = Unit::find($component['uom_id'] ?? null);
                    $expenseItemArr[] = [
                        'expense_header_id' => $expense->id,
                        'purchase_order_item_id' => (isset($component['po_detail_id']) && $component['po_detail_id']) ?? null,
                        'so_id' => $so_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id ?? null,
                        'inventory_uom_code' => $inventory_uom_code ?? null,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'header_exp_amount' => 0.00,
                        'tax_value' => 0.00,
                        'company_currency_id' => @$component['company_currency_id'] ?? 0.00,
                        'company_currency_exchange_rate' => @$component['company_currency_exchange_rate'] ?? 0.00,
                        'group_currency_id' => @$component['group_currency_id'] ?? 0.00,
                        'group_currency_exchange_rate' => @$component['group_currency_exchange_rate'] ?? 0.00,
                        'remark' => $component['remark'] ?? null,
                        'taxable_amount' => $itemValueAfterDiscount,
                        'basic_value' => $itemValue
                    ];
                }

                $isTax = false;
                if(isset($parameters['tax_required']) && !empty($parameters['tax_required']))
                {
                    if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                        $isTax = true;
                    }
                }

                foreach($expenseItemArr as &$expenseItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($expenseItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $expenseItem['taxable_amount'] - $headerDiscount; // after both discount
                    $expenseItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;

                    //Tax
                    if($isTax) {
                        $itemTax = 0;
                        $itemPrice = ($expenseItem['basic_value'] - $headerDiscount - $expenseItem['discount_amount']);
                        $shippingAddress = $expense->shippingAddress;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;

                        $taxDetails = TaxHelper::calculateTax($expenseItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> shipping_country_id, $partyStateId ?? $request -> shipping_state_id, 'purchase');

                        $applicabilityType = $taxDetails[0]['applicability_type'];
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $expenseItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                        if ($applicabilityType === ConstantHelper::COLLECTION) {
                            $finaltotalTax += $itemTax;
                        } else {
                            $finaltotalTax -= $itemTax;
                        }
                    }
                }
                unset($expenseItem);

                foreach($expenseItemArr as $_key => $expenseItem) {
                    $itemPriceAterBothDis =  $expenseItem['basic_value'] - $expenseItem['discount_amount'] - $expenseItem['header_discount_amount'];
                    if($applicabilityType === ConstantHelper::COLLECTION) {
                        $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $finaltotalTax;
                    } else {
                        $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount - $finaltotalTax;
                    }
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    $expenseDetail = new ExpenseDetail;
                    $expenseDetail->expense_header_id = $expenseItem['expense_header_id'];
                    $expenseDetail->purchase_order_item_id = $expenseItem['purchase_order_item_id'];
                    // $expenseDetail->sale_order_item_id = $expenseItem['sale_order_item_id'];
                    $expenseDetail->so_id = $expenseItem['so_id'];
                    $expenseDetail->item_id = $expenseItem['item_id'];
                    $expenseDetail->item_code = $expenseItem['item_code'];
                    $expenseDetail->hsn_id = $expenseItem['hsn_id'];
                    $expenseDetail->hsn_code = $expenseItem['hsn_code'];
                    $expenseDetail->uom_id = $expenseItem['uom_id'];
                    $expenseDetail->uom_code = $expenseItem['uom_code'];
                    $expenseDetail->accepted_qty = $expenseItem['accepted_qty'];
                    $expenseDetail->inventory_uom_id = $expenseItem['inventory_uom_id'];
                    $expenseDetail->inventory_uom_code = $expenseItem['inventory_uom_code'];
                    $expenseDetail->inventory_uom_qty = $expenseItem['inventory_uom_qty'];
                    $expenseDetail->rate = $expenseItem['rate'];
                    $expenseDetail->basic_value = $expenseItem['basic_value'];
                    $expenseDetail->discount_amount = $expenseItem['discount_amount'];
                    $expenseDetail->header_discount_amount = $expenseItem['header_discount_amount'];
                    $expenseDetail->header_exp_amount = $itemHeaderExp;
                    $expenseDetail->tax_value = $expenseItem['tax_value'];
                    $expenseDetail->company_currency = $expenseItem['company_currency_id'];
                    $expenseDetail->group_currency = $expenseItem['group_currency_id'];
                    $expenseDetail->exchange_rate_to_group_currency = $expenseItem['group_currency_exchange_rate'];
                    $expenseDetail->remark = $expenseItem['remark'];
                    $expenseDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach($expenseDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $expenseAttr = new ExpenseItemAttribute;
                            $expenseAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $expenseAttr->expense_header_id = $expense->id;
                            $expenseAttr->expense_detail_id = $expenseDetail->id;
                            $expenseAttr->item_attribute_id = $itemAttribute->id;
                            $expenseAttr->item_code = $component['item_code'] ?? null;
                            $expenseAttr->attr_name = $itemAttribute->attribute_group_id;
                            $expenseAttr->attr_value = $expenseAttrName ?? null;
                            $expenseAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = new ExpenseTed;
                                $ted->expense_header_id = $expense->id;
                                $ted->expense_detail_id = $expenseDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->ted_code = $dis['dis_name'];
                                $ted->assesment_amount = $expenseItem['basic_value'];
                                $ted->ted_percentage = $dis['dis_perc'] ?? 0.00;
                                $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                                $ted->applicability_type = 'Deduction';
                                $ted->save();
                                $totalItemLevelDiscValue = $totalItemLevelDiscValue+$dis['dis_amount'];
                            }
                        }
                    }

                    #Save Componet item Tax
                    if(isset($component['taxes'])) {
                        foreach($component['taxes'] as $tax) {
                            if(isset($tax['t_value']) && $tax['t_value']) {
                                $ted = new ExpenseTed;
                                $ted->expense_header_id = $expense->id;
                                $ted->expense_detail_id = $expenseDetail->id;
                                $ted->ted_type = 'Tax';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $tax['t_d_id'] ?? null;
                                $ted->ted_name = $tax['t_type'] ?? null;
                                $ted->ted_code = $tax['t_type'] ?? null;
                                $ted->assesment_amount = $expenseDetail->assesment_amount;
                                $ted->assesment_amount = $expenseItem['basic_value'] - $expenseItem['discount_amount'] - $expenseItem['header_discount_amount'];
                                $ted->ted_percentage = $tax['t_perc'] ?? 0.00;
                                $ted->ted_amount = $tax['t_value'] ?? 0.00;
                                $ted->applicability_type = $tax['applicability_type'] ?? 'Collection';
                                $ted->save();
                            }
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $ted = new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
                            $ted->ted_code = $dis['d_name'];
                            $ted->assesment_amount = $itemTotalValue-$itemTotalDiscount;
                            $ted->ted_percentage = $dis['d_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Deduction';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['exp_summary'])) {
                    foreach($request->all()['exp_summary'] as $dis) {
                        if(isset($dis['e_amnt']) && $dis['e_amnt']) {
                            if ($applicabilityType === ConstantHelper::COLLECTION) {
                                $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $finaltotalTax;
                            } else {
                                $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount - $finaltotalTax;
                            }
                            $ted = new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_e_id'] ?? null;
                            $ted->ted_name = $dis['e_name'];
                            $ted->ted_code = $dis['e_name'];
                            $ted->assesment_amount = $totalAfterTax;
                            $ted->ted_percentage = $dis['e_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['e_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Update total in main header Pb*/
                $expense->total_item_amount = $itemTotalValue ?? 0.00;
                $totalDiscValue = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($itemTotalValue < $totalDiscValue){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Negative value not allowed'
                    ], 422);
                }
                $expense->total_discount = $totalDiscValue ?? 0.00;
                $expense->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $expense->total_taxes = $totalTax ?? 0.00;
                if ($applicabilityType === ConstantHelper::COLLECTION) {
                    $expense->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $finaltotalTax) ?? 0.00;
                } else {
                    $expense->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) - $finaltotalTax) ?? 0.00;
                }
                $expense->expense_amount = $totalHeaderExpense ?? 0.00;
                if ($applicabilityType === ConstantHelper::COLLECTION) {
                    $totalAmount = (($itemTotalValue - $totalDiscValue) + ($finaltotalTax + $totalHeaderExpense)) ?? 0.00;
                } else {
                    $totalAmount = (($itemTotalValue - $totalDiscValue) - $finaltotalTax + $totalHeaderExpense) ?? 0.00;
                }
                $expense->total_amount = $totalAmount ?? 0.00;
                $expense->save();

                /*Update po and so in main header Expense Advise*/
                $expense->purchase_order_id = $purchaseOrderId;
                // $expense->sale_order_id = $saleOrderId;
                $expense->save();

            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please add atleast one row in component table.',
                    'error' => "",
                ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($expense->vendor->currency_id, $expense->document_date);

            $expense->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $expense->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $expense->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $expense->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $expense->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $expense->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $expense->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $expense->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $expense->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $expense->save();

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $expense->book_id;
                $docId = $expense->id;
                $remarks = $expense->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $expense->approval_level ?? 1;
                $revisionNumber = $expense->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }

            $expense = ExpenseHeader::find($expense->id);
            if ($request->document_status == 'submitted') {
                $totalValue = $expense->total_amount ?? 0;
                $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                $expense->document_status = $document_status;
            } else {
                $expense->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            /*Expense Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $expense->uploadDocuments($request->file('attachment'), 'pb', false);
            }
            $expense->save();

            $redirectUrl = '';
            if(($expense->document_status == ConstantHelper::APPROVED) || ($expense->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $expense->id . '/pdf');
            }
            $status = DynamicFieldHelper::saveDynamicFields(ErpExpDynamicField::class, $expense -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $expense,
                'redirectUrl'=>$redirectUrl
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $user = Helper::getAuthenticatedUser();

        $expense = ExpenseHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        $totalItemValue = $expense->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($expense->series_id,$expense->document_status , $expense->id, $expense->total_amount, $expense->approval_level, $expense->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory($expense->series_id, $expense->id, $expense->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$expense->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        return view('procurement.expense-advise.view', [
            'mrn' => $expense,
            'buttons' => $buttons,
            'erpStores'=>$erpStores,
            'totalItemValue' => $totalItemValue,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revisionNumbers' => $revisionNumbers,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $serviceAlias = ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();

        $user = Helper::getAuthenticatedUser();
        $expense = ExpenseHeader::with(['vendor', 'currency', 'items', 'items.costCenter', 'book'])
            ->findOrFail($id);
        $totalItemValue = $expense->items()->sum('basic_value');
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $revision_number = $expense->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($expense->book_id,$expense->document_status , $expense->id, $expense->total_amount, $expense->approval_level, $expense->created_by ?? 0, $userType['type'], $revision_number);
        $revNo = $expense->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $expense->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($expense->book_id, $expense->id, $revNo, $expense->total_amount);
        $view = 'procurement.expense-advise.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $expense->revision_number) {
            $expense = $expense->source;
            $expense = ExpenseHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('header_id', $expense->header_id)
                ->first();
            $view = 'procurement.expense-advise.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$expense->document_status] ?? '';
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $dynamicFieldsUI = $expense -> dynamicfieldsUi();
        return view($view, [
            'mrn' => $expense,
            'user' => $user,
            'books'=>$books,
            'buttons' => $buttons,
            'vendors' => $vendors,
            'costCenters'=>$costCenters,
            'docStatusClass' => $docStatusClass,
            'totalItemValue' => $totalItemValue,
            'revision_number' => $revision_number,
            'approvalHistory' => $approvalHistory,
            'locations'=>$locations,
            'erpStores' => $erpStores,
            'dynamicFieldsUI' => $dynamicFieldsUI
        ]);
    }

    # Expense Update
    public function update(EditExpenseRequest $request, $id)
    {
        $expense = ExpenseHeader::find($id);
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization ?-> id ?? null;
        $groupId = $organization ?-> group_id ?? null;
        $companyId = $organization ?-> company_id ?? null;
        //Tax Country and State
        $firstAddress = $organization->addresses->first();
        $companyCountryId = null;
        $companyStateId = null;
        $applicabilityType = '';
        if ($firstAddress) {
            $companyCountryId = $firstAddress->country_id;
            $companyStateId = $firstAddress->state_id;
        } else {
            return response()->json([
                'message' => 'Please create an organization first'
            ], 422);
        }
        DB::beginTransaction();
        try {

            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            $currentStatus = $expense->document_status;
            $actionType = $request->action_type;

            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'ExpenseHeader', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'ExpenseDetail', 'relation_column' => 'expense_header_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'ExpenseItemAttribute', 'relation_column' => 'expense_detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'ExpenseTed', 'relation_column' => 'expense_detail_id']
                ];
                // $a = Helper::documentAmendment($revisionData, $id);
                $this->amendmentSubmit($request, $id);
            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedExpenseItemIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            if (count($deletedData['deletedHeaderExpTedIds'])) {
                ExpenseTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
            }

            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                ExpenseTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemDiscTedIds'])) {
                ExpenseTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedExpenseItemIds'])) {
                $expenseItems = ExpenseDetail::whereIn('id',$deletedData['deletedExpenseItemIds'])->get();
                # all ted remove item level
                foreach($expenseItems as $expenseItem) {
                    $expenseItem->teds()->delete();
                    # all attr remove
                    $expenseItem->attributes()->delete();
                    $expenseItem->delete();
                }
            }

            # Expense Header save
            $totalTaxValue = 0.00;
            $expense->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $expense->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $expense->final_remark = $request->remarks ?? '';
            $expense->store_id = $request->header_store_id ?? '';
            $expense->cost_center_id = $request->cost_center_id ?? '';
            $expense->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $expense->save();

            $vendorBillingAddress = $expense->billingAddress ?? null;
            $vendorShippingAddress = $expense->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $expense->bill_address_details()->firstOrNew([
                    'type' => 'billing',
                ]);
                $billingAddress->fill([
                    'address' => $vendorBillingAddress->address,
                    'country_id' => $vendorBillingAddress->country_id,
                    'state_id' => $vendorBillingAddress->state_id,
                    'city_id' => $vendorBillingAddress->city_id,
                    'pincode' => $vendorBillingAddress->pincode,
                    'phone' => $vendorBillingAddress->phone,
                    'fax_number' => $vendorBillingAddress->fax_number,
                ]);
                $billingAddress->save();
            }

            if ($vendorShippingAddress) {
                $shippingAddress = $expense->ship_address_details()->firstOrNew([
                    'type' => 'shipping',
                ]);
                $shippingAddress->fill([
                    'address' => $vendorShippingAddress->address,
                    'country_id' => $vendorShippingAddress->country_id,
                    'state_id' => $vendorShippingAddress->state_id,
                    'city_id' => $vendorShippingAddress->city_id,
                    'pincode' => $vendorShippingAddress->pincode,
                    'phone' => $vendorShippingAddress->phone,
                    'fax_number' => $vendorShippingAddress->fax_number,
                ]);
                $shippingAddress->save();
            }

            # Store location address
            if($expense?->erpStore)
            {
                $storeAddress  = $expense?->erpStore->address;
                $storeLocation = $expense->store_address()->firstOrNew();
                $storeLocation->fill([
                    'type' => 'location',
                    'address' => $storeAddress->address,
                    'country_id' => $storeAddress->country_id,
                    'state_id' => $storeAddress->state_id,
                    'city_id' => $storeAddress->city_id,
                    'pincode' => $storeAddress->pincode,
                    'phone' => $storeAddress->phone,
                    'fax_number' => $storeAddress->fax_number,
                ]);
                $storeLocation->save();
            }

            $totalItemValue = 0.00;
            $totalTaxValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalTax = 0;
            $finaltotalTax = 0;

            $totalHeaderDiscount = 0;
            if (isset($request->all()['disc_summary']) && count($request->all()['disc_summary']) > 0)
            foreach ($request->all()['disc_summary'] as $DiscountValue) {
                $totalHeaderDiscount += floatval($DiscountValue['d_amnt']) ?? 0.00;
            }

            $totalHeaderExpense = 0;
            if (isset($request->all()['exp_summary']) && count($request->all()['exp_summary']) > 0)
            foreach ($request->all()['exp_summary'] as $expValue) {
                $totalHeaderExpense += floatval($expValue['e_amnt']) ?? 0.00;
            }

            if (isset($request->all()['components'])) {
                $expenseItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $so_detail_id = null;
                    $po_detail_id = null;
                    if(isset($component['po_detail_id']) && $component['po_detail_id']){
                        $poDetail =  PoItem::find($component['po_detail_id']);
                        $po_detail_id = $poDetail->id ?? null;
                        if($poDetail){
                            $poDetail->grn_qty += floatval($component['accepted_qty']);
                            $poDetail->save();
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    $inventoryUom = Unit::find($item->uom_id ?? null);
                    $inventory_uom_id = $inventoryUom->id;
                    $inventory_uom_code = $inventoryUom->name;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_qty = floatval($component['accepted_qty']) ?? 0.00 ;
                    } else {
                        $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $inventory_uom_qty = floatval($component['accepted_qty']) * $alUom->conversion_to_inventory;
                        }
                    }
                    $itemValue = floatval($component['accepted_qty']) * floatval($component['rate']);
                    $itemDiscount = floatval($component['discount_amount']) ?? 0.00;

                    $itemTotalValue += $itemValue;
                    $itemTotalDiscount += $itemDiscount;
                    $itemValueAfterDiscount = $itemValue - $itemDiscount;
                    $totalValueAfterDiscount += $itemValueAfterDiscount;
                    $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                    $uom = Unit::find($component['uom_id'] ?? null);
                    $expenseItemArr[] = [
                        'expense_header_id' => $expense->id,
                        'purchase_order_item_id' => $po_detail_id,
                        // 'sale_order_item_id' => $so_detail_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id ?? null,
                        'inventory_uom_code' => $inventory_uom_code ?? null,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'header_exp_amount' => 0.00,
                        'tax_value' => 0.00,
                        'company_currency_id' => @$component['company_currency_id'] ?? 0.00,
                        'company_currency_exchange_rate' => @$component['company_currency_exchange_rate'] ?? 0.00,
                        'group_currency_id' => @$component['group_currency_id'] ?? 0.00,
                        'group_currency_exchange_rate' => @$component['group_currency_exchange_rate'] ?? 0.00,
                        'remark' => $component['remark'] ?? null,
                        'taxable_amount' => $itemValueAfterDiscount,
                        'basic_value' => $itemValue
                    ];
                }

                $isTax = false;
                if(isset($parameters['tax_required']) && !empty($parameters['tax_required']))
                {
                    if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                        $isTax = true;
                    }
                }

                foreach($expenseItemArr as &$expenseItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($expenseItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $expenseItem['taxable_amount'] - $headerDiscount; // after both discount
                    $expenseItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if($isTax) {
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($expenseItem['basic_value'] - $headerDiscount - $expenseItem['discount_amount']);
                        $shippingAddress = $expense->shippingAddress;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($expenseItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'purchase');
                        $applicabilityType = $taxDetails[0]['applicability_type'];
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $expenseItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                        if ($applicabilityType === ConstantHelper::COLLECTION) {
                            $finaltotalTax += $itemTax;
                        } else {
                            $finaltotalTax -= $itemTax;
                        }
                    }
                }
                unset($expenseItem);

                foreach($expenseItemArr as $_key => $expenseItem) {
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];
                    $itemPriceAterBothDis =  $expenseItem['basic_value'] - $expenseItem['discount_amount'] - $expenseItem['header_discount_amount'];
                    if ($applicabilityType === ConstantHelper::COLLECTION) {
                        $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $finaltotalTax;
                    } else {
                        $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount - $finaltotalTax;
                    }
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    # Expense Detail Save
                    $expenseDetail = ExpenseDetail::find($component['expense_detail_id'] ?? null) ?? new ExpenseDetail;

                    // if((isset($component['po_detail_id']) && $component['po_detail_id']) || (isset($expenseDetail->purchase_order_item_id) && $expenseDetail->purchase_order_item_id)) {
                    //     $poItem = PoItem::find($component['po_detail_id'] ?? $expenseDetail->purchase_order_item_id);
                    //     if(isset($poItem) && $poItem) {
                    //         if(isset($poItem->id) && $poItem->id) {
                    //             $orderQty = floatval($expenseDetail->accepted_qty);
                    //             $componentQty = floatval($component['accepted_qty']);
                    //             $qtyDifference = $poItem->order_qty - $orderQty + $componentQty;
                    //             if($qtyDifference) {
                    //                 $poItem->grn_qty = $qtyDifference;
                    //             }
                    //         } else {
                    //             $poItem->order_qty += $component['qty'];
                    //         }
                    //         $poItem->save();
                    //     }
                    // }

                    $expenseDetail->expense_header_id = $expenseItem['expense_header_id'];
                    $expenseDetail->purchase_order_item_id = $expenseItem['purchase_order_item_id'];
                    // $expenseDetail->sale_order_item_id = $expenseItem['sale_order_item_id'];
                    $expenseDetail->item_id = $expenseItem['item_id'];
                    $expenseDetail->item_code = $expenseItem['item_code'];
                    $expenseDetail->hsn_id = $expenseItem['hsn_id'];
                    $expenseDetail->hsn_code = $expenseItem['hsn_code'];
                    $expenseDetail->uom_id = $expenseItem['uom_id'];
                    $expenseDetail->uom_code = $expenseItem['uom_code'];
                    $expenseDetail->accepted_qty = $expenseItem['accepted_qty'];
                    $expenseDetail->inventory_uom_id = $expenseItem['inventory_uom_id'];
                    $expenseDetail->inventory_uom_code = $expenseItem['inventory_uom_code'];
                    $expenseDetail->inventory_uom_qty = $expenseItem['inventory_uom_qty'];
                    $expenseDetail->rate = $expenseItem['rate'];
                    $expenseDetail->basic_value = $expenseItem['basic_value'];
                    $expenseDetail->discount_amount = $expenseItem['discount_amount'];
                    $expenseDetail->header_discount_amount = $expenseItem['header_discount_amount'];
                    $expenseDetail->tax_value = $expenseItem['tax_value'];
                    $expenseDetail->header_exp_amount = $itemHeaderExp;
                    $expenseDetail->company_currency = $expenseItem['company_currency_id'];
                    $expenseDetail->group_currency = $expenseItem['group_currency_id'];
                    $expenseDetail->exchange_rate_to_group_currency = $expenseItem['group_currency_exchange_rate'];
                    $expenseDetail->remark = $expenseItem['remark'];
                    $expenseDetail->save();

                    #Save component Attr
                    foreach($expenseDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $expenseAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $expenseAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $expenseAttr = ExpenseItemAttribute::find($expenseAttrId) ?? new ExpenseItemAttribute;
                            $expenseAttr->expense_header_id = $expense->id;
                            $expenseAttr->expense_detail_id = $expenseDetail->id;
                            $expenseAttr->item_attribute_id = $itemAttribute->id;
                            $expenseAttr->item_code = $component['item_code'] ?? null;
                            $expenseAttr->attr_name = $itemAttribute->attribute_group_id;
                            $expenseAttr->attr_value = $expenseAttrName ?? null;
                            $expenseAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = ExpenseTed::find(@$dis['id']) ?? new ExpenseTed;
                                $ted->expense_header_id = $expense->id;
                                $ted->expense_detail_id = $expenseDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->ted_code = $dis['dis_name'];
                                $ted->assesment_amount = $expenseItem['basic_value'];
                                $ted->ted_percentage = $dis['dis_perc'] ?? 0.00;
                                $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                                $ted->applicability_type = 'Deduction';
                                $ted->save();
                                $totalItemLevelDiscValue = $totalItemLevelDiscValue+$dis['dis_amount'];
                            }
                        }
                    }

                    #Save Component item Tax
                    if(isset($component['taxes'])) {
                        foreach($component['taxes'] as $key => $tax) {
                            $expenseAmountId = null;
                            $ted = ExpenseTed::find(@$tax['id']) ?? new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = $expenseDetail->id;
                            $ted->ted_type = 'Tax';
                            $ted->ted_level = 'D';
                            $ted->ted_id = $tax['t_d_id'] ?? null;
                            $ted->ted_name = $tax['t_type'] ?? null;
                            $ted->ted_code = $tax['t_type'] ?? null;
                            $ted->assesment_amount = $expenseItem['basic_value'] - $expenseItem['discount_amount'] - $expenseItem['header_discount_amount'];
                            $ted->ted_percentage = $tax['t_perc'] ?? 0.00;
                            $ted->ted_amount = $tax['t_value'] ?? 0.00;
                            $ted->applicability_type = $tax['applicability_type'] ?? 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $expenseAmountId = @$dis['d_id'] ?? null;
                            $ted = ExpenseTed::find($expenseAmountId) ?? new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
                            $ted->ted_code = @$dis['d_name'];
                            $ted->assesment_amount = $itemTotalValue-$itemTotalDiscount;
                            $ted->ted_percentage = $dis['d_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Deduction';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['exp_summary'])) {
                    foreach($request->all()['exp_summary'] as $dis) {
                        if(isset($dis['e_amnt']) && $dis['e_amnt']) {
                            if($applicabilityType === ConstantHelper::COLLECTION) {
                                $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $finaltotalTax;
                            } else {
                                $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount - $finaltotalTax;
                            }
                            $expenseAmountId = @$dis['e_id'] ?? null;
                            $ted = ExpenseTed::find($expenseAmountId) ?? new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_e_id'] ?? null;
                            $ted->ted_name = $dis['e_name'];
                            $ted->ted_code = @$dis['d_name'];
                            $ted->assesment_amount = $totalAfterTax;
                            $ted->ted_percentage = $dis['e_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['e_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Update total in main header Expense*/
                $expense->total_item_amount = $itemTotalValue ?? 0.00;
                $totalDiscValue = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($itemTotalValue < $totalDiscValue){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Negative value not allowed'
                    ], 422);
                }
                $expense->total_discount = $totalDiscValue ?? 0.00;
                $expense->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $expense->total_taxes = $totalTax ?? 0.00;
                if($applicabilityType === ConstantHelper::COLLECTION) {
                    $expense->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $finaltotalTax) ?? 0.00;
                } else {
                    $expense->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) - $finaltotalTax) ?? 0.00;
                }
                $expense->expense_amount = $totalHeaderExpense ?? 0.00;
                if ($applicabilityType === ConstantHelper::COLLECTION) {
                    $totalAmount = (($itemTotalValue - $totalDiscValue) + ($finaltotalTax + $totalHeaderExpense)) ?? 0.00;
                } else {
                    $totalAmount = (($itemTotalValue - $totalDiscValue) - $finaltotalTax + $totalHeaderExpense) ?? 0.00;
                }
                $expense->total_amount = $totalAmount ?? 0.00;
                $expense->save();
            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($expense->vendor->currency_id, $expense->document_date);

            $expense->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $expense->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $expense->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $expense->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $expense->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $expense->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $expense->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $expense->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $expense->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $expense->save();

            /*Create document submit log*/
            $bookId = $expense->book_id;
            $docId = $expense->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $expense->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $expense->approval_level ?? 1;
            $modelName = get_class($expense);
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                //*amendmemnt document log*/
                $revisionNumber = $expense->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                $expense->revision_number = $revisionNumber;
                $expense->approval_level = 1;
                $expense->revision_date = now();
                $amendAfterStatus = $expense->document_status;
                $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                    $totalValue = $expense->grand_total_amount ?? 0;
                    $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                }
                if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                    $actionType = 'submit';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                }
                $expense->document_status = $amendAfterStatus;
                $expense->save();

            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $expense->revision_number ?? 0;
                    $actionType = 'submit';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                    $totalValue = $expense->grand_total_amount ?? 0;
                    $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    $expense->document_status = $document_status;
                } else {
                    $expense->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }

            /*MRN Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $expense->uploadDocuments($request->file('attachment'), 'expense', false);
            }

            $expense->save();

            $redirectUrl = '';
            if(($expense->document_status == ConstantHelper::APPROVED) || ($expense->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $expense->id . '/pdf');
            }

            $status = DynamicFieldHelper::saveDynamicFields(ErpExpDynamicField::class, $expense -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $expense,
                'redirectUrl'=>$redirectUrl
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addItemRow(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $item = json_decode($request->item,true) ?? [];
        $componentItem = json_decode($request->component_item,true) ?? [];
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                // return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.expense-advise.partials.item-row',compact(['rowCount', 'costCenters']))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    // PO Item Rows
    public function poItemRows(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $item_ids = explode(',', $request->item_ids);
        $items = PoItem::whereIn('id', $item_ids)
            ->get();
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])->find($request->vendor_id);
        $currency = $vendor->currency;
        $paymentTerm = $vendor->paymentTerms;
        $shipping = $vendor->addresses()->where(function($query) {
                        $query->where('type', 'shipping')->orWhere('type', 'both');
                    })->latest()->first();
        $billing = $vendor->addresses()->where(function($query) {
                    $query->where('type', 'billing')->orWhere('type', 'both');
                })->latest()->first();
        $html = view(
            'procurement.expense-advise.partials.po-item-row',
            compact(
                'items',
                'costCenters'
                )
            )
            ->render();
        return response()->json([
            'data' =>
                [
                    'html' => $html,
                    'vendor' => $vendor,
                    'currency' => $currency,
                    'paymentTerm' => $paymentTerm,
                    'shipping' => $shipping,
                    'billing' => $billing,
                ],
                'status' => 200,
                'message' => 'fetched.'
            ]
        );
    }

    // PO Item Rows
    public function soItemRows(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $item_ids = explode(',', $request->item_ids);
        $items = ErpSoItem::whereIn('id', $item_ids)
            ->get();
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        $html = view(
            'procurement.expense-advise.partials.so-item-row',
            compact(
                'items',
                'costCenters'
                )
            )
            ->render();
        return response()->json([
            'data' =>
                [
                    'html' => $html,
                ],
                'status' => 200,
                'message' => 'fetched.'
            ]
        );
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $attributeGroups = AttributeGroup::with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];
        $expenseDetailId = $request->expense_detail_id ?? null;
        $itemAttIds = [];
        $itemAttributeArray = [];
        if ($expenseDetailId) {
            $detail = ExpenseDetail::find($expenseDetailId);
            if ($detail) {
                $itemAttIds = collect($detail->attributes)->pluck('item_attribute_id')->toArray();
                $itemAttributeArray = $detail->item_attributes_array();
            }
        }
        $itemAttributes = collect();
        if (count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id', $itemAttIds)->get();
            if (count($itemAttributes) < 1) {
                $itemAttributes = $item?->itemAttributes;
                $itemAttributeArray = $item->item_attributes_array();
            }
        } else {
            $itemAttributes = $item?->itemAttributes;
            $itemAttributeArray = $item->item_attributes_array();
        }
        $html = view('procurement.expense-advise.partials.comp-attribute', compact('item', 'attributeGroups', 'rowCount', 'selectedAttr','itemAttributes'))->render();
        $hiddenHtml = '';
        foreach ($itemAttributes as $attribute) {
            $selected = '';
            foreach ($attribute->attributes() as $value) {
                if (in_array($value->id, $selectedAttr)) {
                    $selected = $value->id;
                }
            }
            $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
        }

        if (count($selectedAttr)) {
            foreach ($itemAttributeArray as &$group) {
                foreach ($group['values_data'] as $attribute) {
                    if (in_array($attribute->id, $selectedAttr)) {
                        $attribute->selected = true;
                    }
                }
            }
        }
        return response()->json(['data' => ['attr' => $item->itemAttributes->count(), 'html' => $html, 'hiddenHtml' => $hiddenHtml, 'itemAttributeArray' => $itemAttributeArray], 'status' => 200, 'message' => 'fetched.']);
    }

    # Add discount row
    public function addDiscountRow(Request $request)
    {
        $tblRowCount = intval($request->tbl_row_count) ? intval($request->tbl_row_count) + 1 : 1;
        $rowCount = intval($request->row_count);
        $disName = $request->dis_name;
        $disPerc = $request->dis_perc;
        $disAmount = $request->dis_amount;
        $html = view('procurement.expense-advise.partials.add-disc-row',compact('tblRowCount','rowCount','disName','disAmount','disPerc'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # get tax calcualte
    public function taxCalculation(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $firstAddress = $organization->addresses->first();
        if ($firstAddress) {
            $companyCountryId = $firstAddress->country_id;
            $companyStateId = $firstAddress->state_id;
        } else {
            return response()->json(['error' => 'No address found for the organization.'], 404);
        }
        $price = $request->input('price', 6000);
        $document_date =$request->document_date ?? date('Y-m-d');
        $hsnId = null;
        $item = Item::find($request -> item_id);
        if (isset($item)) {
            $hsnId = $item -> hsn_id;
        } else {
            return response()->json(['error' => 'Invalid Item'], 500);
        }
        $transactionType = $request->input('transaction_type', 'sale');
        if ($transactionType === "sale") {
            $fromCountry = $companyCountryId;
            $fromState = $companyStateId;
            $upToCountry = $request->input('party_country_id', $companyCountryId) ?? 0;
            $upToState = $request->input('party_state_id', $companyStateId) ?? 0;
        } else {
            $fromCountry = $request->input('party_country_id', $companyCountryId) ?? 0;
            $fromState = $request->input('party_state_id', $companyStateId) ?? 0;
            $upToCountry = $companyCountryId;
            $upToState = $companyStateId;
        }
        try {
            $taxDetails = TaxHelper::calculateTax( $hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,'purchase',$document_date);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = floatval($request->price) ?? 0;
            $html = view('procurement.expense-advise.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get Address
    public function getAddress(Request $request)
    {
        $vendor = Vendor::withDefaultGroupCompanyOrg()
        ->with(['currency:id,name', 'paymentTerms:id,name'])->find($request->id);
        $currency = $vendor->currency;
        $paymentTerm = $vendor->paymentTerms;
        $shipping = $vendor->addresses()->where(function($query) {
                        $query->where('type', 'shipping')->orWhere('type', 'both');
                    })->latest()->first();
        $billing = $vendor->addresses()->where(function($query) {
                    $query->where('type', 'billing')->orWhere('type', 'both');
                })->latest()->first();

        $vendorId = $vendor->id;
        $documentDate = $request->document_date;
        $billingAddresses = ErpAddress::where('addressable_id', $vendorId) -> where('addressable_type', Vendor::class) -> whereIn('type', ['billing', 'both'])-> get();
        $shippingAddresses = ErpAddress::where('addressable_id', $vendorId) -> where('addressable_type', Vendor::class) -> whereIn('type', ['shipping','both'])-> get();

        $user = Helper::getAuthenticatedUser();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;

        foreach ($billingAddresses as $billingAddress) {
            $billingAddress -> value = $billingAddress -> id;
            $billingAddress -> label = $billingAddress -> display_address;
        }
        foreach ($shippingAddresses as $shippingAddress) {
            $shippingAddress -> value = $shippingAddress -> id;
            $shippingAddress -> label = $shippingAddress -> display_address;
        }
        if (count($shippingAddresses) == 0) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Shipping Address not found for '. $vendor ?-> company_name
                )
            ]);
        }
        if (count($billingAddresses) == 0) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Billing Address not found for '. $vendor ?-> company_name
                )
            ]);
        }
        if (!isset($vendor->currency_id)) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Currency not found for '. $vendor ?-> company_name
                )
            ]);
        }
        if (!isset($vendor->payment_terms_id)) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Payment Terms not found for '. $vendor ?-> company_name
                )
            ]);
        }
        $currencyData = CurrencyHelper::getCurrencyExchangeRates($vendor->currency_id ?? 0, $documentDate ?? '');

        return response()->json(['data' => ['shipping' => $shipping,'billing' => $billing, 'paymentTerm' => $paymentTerm, 'currency' => $currency, 'currency_exchange' => $currencyData, 'org_address' => $orgAddress], 'status' => 200, 'message' => 'fetched']);
    }

    # Get edit address modal
    public function editAddress(Request $request)
    {
        $type = $request->type;
        $addressId = $request->address_id;
        $vendor = Vendor::find($request->vendor_id ?? null);
        if(!$vendor) {
            return response()->json([
                'message' => 'Please First select vendor.',
                'error' => null,
            ], 500);
        }
        if($request->type == 'shipping') {
            $addresses = $vendor->addresses()->where(function($query) {
                $query->where('type', 'shipping')->orWhere('type', 'both');
            })->latest()->get();

            $selectedAddress = $vendor->addresses()->where('id', $addressId)->where(function($query) {
                $query->where('type', 'shipping')->orWhere('type', 'both');
            })->latest()->first();
        } else {
            $addresses = $vendor->addresses()->where(function($query) {
                    $query->where('type', 'billing')->orWhere('type', 'both');
                })->latest()->get();
            $selectedAddress = $vendor->addresses()->where('id', $addressId)->where(function($query) {
                    $query->where('type', 'billing')->orWhere('type', 'both');
                })->latest()->first();
        }
        $html = '';
        if(!intval($request->onChange)) {
            $html = view('procurement.expense-advise.partials.edit-address-modal',compact('addresses','selectedAddress'))->render();
        }
        return response()->json(['data' => ['html' => $html,'selectedAddress' => $selectedAddress], 'status' => 200, 'message' => 'fetched!']);
    }

    # Save Address
    public function addressSave(Request $request)
    {
        $addressId = $request->address_id;
        $request->validate([
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'pincode' => 'required',
            'address' => 'required'
        ]);

        $addressType =  $request->address_type;
        $vendorId = $request->hidden_vendor_id;
        $countryId = $request->country_id;
        $stateId = $request->state_id;
        $cityId = $request->city_id;
        $pincode = $request->pincode;
        $address = $request->address;

        $vendor = Vendor::find($vendorId ?? null);
        $selectedAddress = $vendor->addresses()
        ->where('id', $addressId)
        ->where(function($query) use ($addressType) {
            if ($addressType == 'shipping') {
                $query->where('type', 'shipping')
                      ->orWhere('type', 'both');
            } else {
                $query->where('type', 'billing')
                      ->orWhere('type', 'both');
            }
        })
        ->first();

        $newAddress = null;

        if ($selectedAddress) {
            $newAddress = $vendor->addresses()->firstOrNew([
                'type' => $addressType ?? 'both',
            ]);
            $newAddress->fill([
                'country_id' => $countryId,
                'state_id' => $stateId,
                'city_id' => $cityId,
                'pincode' => $pincode,
                'address' => $address,
                'addressable_id' => $vendorId,
                'addressable_type' => Vendor::class,
            ]);
            $newAddress->save();
        } else {
            $newAddress = $vendor->addresses()->create([
                'type' => $addressType ?? 'both',
                'country_id' => $countryId,
                'state_id' => $stateId,
                'city_id' => $cityId,
                'pincode' => $pincode,
                'address' => $address,
                'addressable_id' => $vendorId,
                'addressable_type' => Vendor::class
            ]);
        }
        return response()->json(['data' => ['new_address' => $newAddress], 'status' => 200, 'message' => 'fetched!']);
    }

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $itemId = $request->item_id;
        $item = Item::find($request->item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = intval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';
        if($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
        }
        $remark = $request->remark ?? null;
        $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);
        $poDetail = PoItem::find($request->po_detail_id);
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $html = view(
            'procurement.expense-advise.partials.comp-item-detail',
            compact(
                'qty',
                'item',
                'remark',
                'uomName',
                'poDetail',
                'selectedAttr',
                'purchaseOrder',
                'specifications',
            )
        )
        ->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    // genrate pdf
    public function generatePdf(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();

        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $expense = ExpenseHeader::with(['vendor', 'currency', 'items', 'book', 'expenses'])
            ->findOrFail($id);

        $shippingAddress = $expense->shippingAddress;

        $totalItemValue = $expense->total_item_amount ?? 0.00;
        $totalDiscount = $expense->total_discount ?? 0.00;
        $totalTaxes = $expense->total_taxes ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $expense->expense_amount ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($expense->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$expense->document_status] ?? '';
        $taxes = ExpenseTed::where('expense_header_id', $expense->id)
            ->where('ted_type', 'Tax')
            ->select('ted_type','ted_id','ted_name', 'ted_percentage', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assesment_amount) as total_assesment_amount'))
            ->groupBy('ted_name', 'ted_percentage')
            ->get();
        $sellerShippingAddress = $expense->latestShippingAddress();
        $sellerBillingAddress = $expense->latestBillingAddress();
        $buyerAddress = $expense?->erpStore?->address;

        $pdf = PDF::loadView(
            'pdf.expense',
            [
                'exp' => $expense,
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
                'docStatusClass' => $docStatusClass,
                'taxes' => $taxes,
                'sellerShippingAddress' => $sellerShippingAddress,
                'sellerBillingAddress' => $sellerBillingAddress,
                'buyerAddress' => $buyerAddress
            ]
        );

        $fileName = 'Expense-Advice-' . date('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    # Handle calculation update
    public function updateCalculation($expenseId)
    {
        $expense = ExpenseHeader::find($expenseId);
        if (!$expense) {
            return;
        }

        $totalItemAmnt = 0;
        $totalTaxAmnt = 0;
        $totalItemValue = 0.00;
        $totalTaxValue = 0.00;
        $totalDiscValue = 0.00;
        $totalExpValue = 0.00;
        $totalItemLevelDiscValue = 0.00;
        $totalAmount = 0.00;
        $vendorShippingCountryId = $expense->shippingAddress->country_id;
        $vendorShippingStateId = $expense->shippingAddress->state_id;

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $firstAddress = $organization->addresses->first();
        $companyCountryId = $firstAddress->country_id;
        $companyStateId = $firstAddress->state_id;

        # Save Item level discount
        foreach($expense->items as $expense_item) {
            $itemPrice = $expense_item->rate*$expense_item->accepted_qty;
            $totalItemAmnt = $totalItemAmnt + $itemPrice;
            $itemDis = $expense_item->itemDiscount()->sum('ted_amount');
            $expense_item->discount_amount = $itemDis;
            $expense_item->save();
        }
        # Save header level discount
        $totalItemValue = $expense->total_item_amount;
        $totalItemValueAfterTotalItemDisc = $expense->total_item_amount - $expense->items()->sum('discount_amount');
        $totalHeaderDiscount = $expense->total_header_disc_amount;

        foreach($expense->items as $expense_item) {
            $itemPrice = $expense_item->rate*$expense_item->accepted_qty;
            $itemPriceAfterItemDis = $itemPrice - $expense_item->discount_amount;
            # Calculate header discount
            // Calculate and save header discount
            if ($totalItemValueAfterTotalItemDisc > 0 && $totalHeaderDiscount > 0) {
                $headerDis = ($itemPriceAfterItemDis / $totalItemValueAfterTotalItemDisc) * $totalHeaderDiscount;
            } else {
                $headerDis = 0;
            }
            $expense_item->header_discount_amount = $headerDis;

            # Calculate header expenses
            $priceAfterBothDis = $itemPriceAfterItemDis - $headerDis;
            $taxDetails = TaxHelper::calculateTax($expense_item->hsn_id, $priceAfterBothDis, $companyCountryId, $companyStateId, $vendorShippingCountryId, $vendorShippingStateId, 'purchase');
            if (isset($taxDetails) && count($taxDetails) > 0) {
                $itemTax = 0;
                $cTaxDeIds = array_column($taxDetails, 'id');
                $existTaxIds = ExpenseTed::where('expense_detail_id', $expense_item->id)
                                ->where('ted_type','Tax')
                                ->pluck('ted_id')
                                ->toArray();

                $array1 = array_map('strval', $existTaxIds);
                $array2 = array_map('strval', $cTaxDeIds);
                sort($array1);
                sort($array2);

                if($array1 != $array2) {
                    # Changes
                    ExpenseTed::where("expense_detail_id",$expense_item->id)
                        ->where('ted_type','Tax')
                        ->delete();
                }

                foreach ($taxDetails as $taxDetail) {
                    $itemTax += ((double)$taxDetail['tax_percentage']/100*$priceAfterBothDis);

                    $ted = ExpenseTed::firstOrNew([
                        'expense_detail_id' => $expense_item->id,
                        'ted_id' => $taxDetail['id'],
                        'ted_type' => 'Tax',
                    ]);

                    $ted->expense_header_id = $expense->id;
                    $ted->expense_detail_id = $expense_item->id;
                    $ted->ted_type = 'Tax';
                    $ted->ted_level = 'D';
                    $ted->ted_id = $taxDetail['id'] ?? null;
                    $ted->ted_name = $taxDetail['tax_type'] ?? null;
                    $ted->assesment_amount = $expense_item->assessment_amount_total;
                    $ted->ted_percentage = $taxDetail['tax_percentage'] ?? 0.00;
                    $ted->ted_amount = ((double)$taxDetail['tax_percentage']/100*$priceAfterBothDis) ?? 0.00;
                    $ted->applicability_type = $taxDetail['applicability_type'] ?? 'Collection';
                    $ted->save();
                }
                if($itemTax) {
                    $expense_item->tax_value = $itemTax;
                    $expense_item->save();
                    $totalTaxAmnt = $totalTaxAmnt + $itemTax;
                }
            }
            $expense_item->save();
        }

        # Save expenses
        $totalValueAfterBothDis = $totalItemValueAfterTotalItemDisc -$totalHeaderDiscount;
        $headerExpensesTotal = $expense->expenses()->sum('ted_amount');

        if ($headerExpensesTotal) {
            foreach($expense->items as $expense_item) {
                $itemPriceAterBothDis = ($expense_item->rate*$expense_item->accepted_qty) - $expense_item->header_discount_amount - $expense_item->discount_amount;
                $exp = $itemPriceAterBothDis / $totalValueAfterBothDis * $headerExpensesTotal;
                $expense_item->header_exp_amount = $exp;
                $expense_item->save();
            }
        } else {
            foreach($expense->items as $expense_item) {
                $expense_item->header_exp_amount = 0.00;
                $expense_item->save();
            }
        }

        /*Update Calculation*/
        $totalDiscValue = $expense->items()->sum('header_discount_amount') + $expense->items()->sum('discount_amount');
        $totalExpValue = $expense->items()->sum('header_exp_amount');
        $expense->total_item_amount = $totalItemAmnt;
        $expense->total_discount = $totalDiscValue;
        $expense->taxable_amount = ($totalItemAmnt - $totalDiscValue);
        $expense->total_taxes = $totalTaxAmnt;
        $expense->total_after_tax_amount = (($totalItemAmnt - $totalDiscValue) + $totalTaxAmnt);
        $expense->expense_amount = $totalExpValue;
        $totalAmount = (($totalItemAmnt - $totalDiscValue) + ($totalTaxAmnt + $totalExpValue));
        $expense->total_amount = $totalAmount;
        $expense->save();
    }

    # Remove discount item level
    public function removeDisItemLevel(Request $request)
    {
        DB::beginTransaction();
        try {
            $pTedId = $request->id;
            $ted = ExpenseTed::find($pTedId);
            if($ted) {
                $tedPoId = $ted->expense_header_id;
                $ted->delete();
                $this->updateCalculation($tedPoId);
            }
            DB::commit();
            return response()->json(['status' => 200,'message' => 'data deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting component: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete the item level disc.'], 500);
        }
    }

    # Remove discount header level
    public function removeDisHeaderLevel(Request $request)
    {
        DB::beginTransaction();
        try {
            $pTedId = $request->id;
            $ted = ExpenseTed::find($pTedId);
            if($ted) {
                $tedPoId = $ted->expense_header_id;
                $ted->delete();
                $this->updateCalculation($tedPoId);
            }
            DB::commit();
            return response()->json(['status' => 200,'message' => 'data deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting component: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete the item level disc.'], 500);
        }
    }

    # Remove exp header level
    public function removeExpHeaderLevel(Request $request)
    {
        DB::beginTransaction();
        try {
            $pTedId = $request->id;
            $ted = ExpenseTed::find($pTedId);
            if($ted) {
                $tedPoId = $ted->expense_header_id;
                $ted->delete();
                $this->updateCalculation($tedPoId);
            }
            DB::commit();
            return response()->json(['status' => 200,'message' => 'data deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting component: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete the item level disc.'], 500);
        }
    }

    # Submit Amendment
    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Header History
            $expenseHeader = ExpenseHeader::find($request->id);
            if(!$expenseHeader) {
                return response()->json(['error' => 'Expense Header not found'], 404);
            }
            $expenseHeaderData = $expenseHeader->toArray();
            unset($expenseHeaderData['id']); // You might want to remove the primary key, 'id'
            $expenseHeaderData['header_id'] = $expenseHeader->id;
            $headerHistory = ExpenseHeaderHistory::create($expenseHeaderData);
            $headerHistoryId = $headerHistory->id;

            $vendorBillingAddress = $expenseHeader->billingAddress ?? null;
            $vendorShippingAddress = $GateEntryHeader->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $headerHistory->bill_address_details()->firstOrNew([
                    'type' => 'billing',
                ]);
                $billingAddress->fill([
                    'address' => $vendorBillingAddress->address,
                    'country_id' => $vendorBillingAddress->country_id,
                    'state_id' => $vendorBillingAddress->state_id,
                    'city_id' => $vendorBillingAddress->city_id,
                    'pincode' => $vendorBillingAddress->pincode,
                    'phone' => $vendorBillingAddress->phone,
                    'fax_number' => $vendorBillingAddress->fax_number,
                ]);
                $billingAddress->save();
            }

            if ($vendorShippingAddress) {
                $shippingAddress = $headerHistory->ship_address_details()->firstOrNew([
                    'type' => 'shipping',
                ]);
                $shippingAddress->fill([
                    'address' => $vendorShippingAddress->address,
                    'country_id' => $vendorShippingAddress->country_id,
                    'state_id' => $vendorShippingAddress->state_id,
                    'city_id' => $vendorShippingAddress->city_id,
                    'pincode' => $vendorShippingAddress->pincode,
                    'phone' => $vendorShippingAddress->phone,
                    'fax_number' => $vendorShippingAddress->fax_number,
                ]);
                $shippingAddress->save();
            }

            if ($request->hasFile('amend_attachment')) {
                $mediaFiles = $headerHistory->uploadDocuments($request->file('amend_attachment'), 'exp-adv', false);
            }
            $headerHistory->save();

            // Detail History
            $expenseDetails = ExpenseDetail::where('expense_header_id', $expenseHeader->id)->get();
            if(!empty($expenseDetails)){
                foreach($expenseDetails as $key => $detail){
                    $expenseDetailData = $detail->toArray();
                    unset($expenseDetailData['id']); // You might want to remove the primary key, 'id'
                    $expenseDetailData['header_id'] = $detail->expense_header_id;
                    $expenseDetailData['detail_id'] = $detail->id;
                    $expenseDetailData['header_history_id'] = $headerHistoryId;
                    $detailHistory = ExpenseDetailHistory::create($expenseDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $expenseAttributes = ExpenseItemAttribute::where('expense_header_id', $expenseHeader->id)
                        ->where('expense_detail_id', $detail->id)
                        ->get();
                    if(!empty($expenseAttributes)){
                        foreach($expenseAttributes as $key1 => $attribute){
                            $expenseAttributeData = $attribute->toArray();
                            unset($expenseAttributeData['id']); // You might want to remove the primary key, 'id'
                            $expenseAttributeData['header_id'] = $detail->expense_header_id;
                            $expenseAttributeData['detail_id'] = $detail->id;
                            $expenseAttributeData['attribute_id'] = $attribute->id;
                            $expenseAttributeData['header_history_id'] = $headerHistoryId;
                            $expenseAttributeData['detail_history_id'] = $detailHistoryId;
                            $attributeHistory = ExpenseItemAttributeHistory::create($expenseAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Expense Item TED History
                    $itemExtraAmounts = ExpenseTed::where('expense_header_id', $expenseHeader->id)
                        ->where('expense_detail_id', $detail->id)
                        ->where('ted_level', '=', 'D')
                        ->get();

                    if(!empty($itemExtraAmounts)){
                        foreach($itemExtraAmounts as $key4 => $extraAmount){
                            $extraAmountData = $extraAmount->toArray();
                            unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                            $extraAmountData['header_id'] = $detail->expense_header_id;
                            $extraAmountData['detail_id'] = $detail->id;
                            $extraAmountData['header_history_id'] = $headerHistoryId;
                            $extraAmountData['detail_history_id'] = $detailHistoryId;
                            $extraAmountData['expense_ted_id'] = $extraAmount->id;
                            $extraAmountDataHistory = ExpenseTedHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // Expense Header TED History
            $expenseExtraAmounts = ExpenseTed::where('expense_header_id', $expenseHeader->id)
                ->where('ted_level', '=', 'H')
                ->get();

            if(!empty($expenseExtraAmounts)){
                foreach($expenseExtraAmounts as $key4 => $extraAmount){
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $extraAmountData['header_id'] = $detail->expense_header_id;
                    $extraAmountData['header_history_id'] = $headerHistoryId;
                    $extraAmountData['expense_ted_id'] = $extraAmount->id;
                    $extraAmountDataHistory = ExpenseTedHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000,99999);

            $revisionNumber = "Expense".$randNo;
            $expenseHeader->revision_number += 1;
            // $expenseHeader->status = "draft";
            // $expenseHeader->document_status = "draft";
            // $expenseHeader->save();

            /*Create document submit log*/
            if ($expenseHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $expenseHeader->series_id;
                $docId = $expenseHeader->id;
                $remarks = $expenseHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $expenseHeader->approval_level ?? 1;
                $revisionNumber = $expenseHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                $expenseHeader->document_status = $approveDocument['approvalStatus'];
            }
            $expenseHeader->save();

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $expenseHeader,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while amendement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    # Get PO Item List
    public function getPo(Request $request){
        // Initialize variables
        $applicableBookIds = [];
        $headerBookId = $request->header_book_id ?? null;
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $vendorId = $request->vendor_id ?? null;

        // Fetch applicable book IDs from the headerBookId
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);

        $poItemIds = [];
        $poItems = PoItem::select(
            'erp_po_items.*',
            'erp_purchase_orders.id as po_id',
            'erp_purchase_orders.vendor_id as vendor_id',
            'erp_purchase_orders.book_id as book_id'
            )
            ->leftJoin('erp_purchase_orders', 'erp_purchase_orders.id', 'erp_po_items.purchase_order_id')
            ->whereIn('erp_purchase_orders.book_id', $applicableBookIds)
            ->whereRaw('order_qty > expense_advise_qty')
            ->whereHas('item', function($item){
                $item->where('type', 'Service');
            })
            ->get();

        foreach ($poItems as $poItem) {
            $checkInvoiceRequired = Vendor::whereHas('supplier_books')->find($poItem->vendor_id);
            if($checkInvoiceRequired) {
                $siItem = PoItem::where('po_item_id', $poItem->id)
                    ->whereHas('po', function($po){
                        $po->where('type', 'supplier-invoice');
                    })
                    ->whereRaw('order_qty > expense_advise_qty')
                    ->first();
                if($siItem){
                    $poItemIds[] = $siItem->id;
                    // $siItemIds[] = $siItem->id;
                }
            } else {
                $poItemIds[] = $poItem->id;
            }
        }

        // Query to get PO items with the required conditions
        $poItems = PoItem::with('attributes')->whereIn('id',$poItemIds)
            ->where(function ($query) use ($seriesId, $docNumber, $itemId, $vendorId) {
            // Ensure item exists
            $query->whereHas('item', function($item){
                $item->where('type', 'Service');
            });

            // Check POs
            $query->whereHas('po', function ($po) use ($seriesId, $docNumber, $vendorId) {
                // Filter by book_id (headerBookId)
                $po->withDefaultGroupCompanyOrg();
                $po->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                // Filter by series ID
                if($seriesId) {
                    $po->where('erp_purchase_orders.book_id',$seriesId);
                }

                // Filter by document number
                if ($docNumber) {
                    $po->where('erp_purchase_orders.document_number', $docNumber);
                }

                // Filter by vendor ID
                if ($vendorId) {
                    $po->where('erp_purchase_orders.vendor_id', $vendorId);
                }
            });

            // Filter by item ID if provided
            if ($itemId) {
                $query->where('item_id', $itemId);
            }

            // Ensure remaining quantity condition
            $query->whereRaw('order_qty > expense_advise_qty');
        })->get();

        $html = view('procurement.expense-advise.partials.po-item-list', ['poItems' => $poItems])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);

    }

    # Submit PI Item list
    public function processPoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $ids = json_decode($request->ids,true) ?? [];
        $vendor = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $poItems = PoItem::whereIn('id', $ids)->get();
        $uniquePoIds = PoItem::whereIn('id', $ids)
                    ->distinct()
                    ->pluck('purchase_order_id')
                    ->toArray();
        if(count($uniquePoIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time expense advise create from one PO."]);
        }
        $pos = PurchaseOrder::whereIn('id', $uniquePoIds)->get();
        $discounts = collect();
        $expenses = collect();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;
        foreach ($pos as $po) {
            foreach ($po->headerDiscount as $headerDiscount) {
                if (!intval($headerDiscount->ted_perc)) {
                    $tedPerc = (floatval($headerDiscount->ted_amount) / floatval($headerDiscount->assessment_amount)) * 100;
                    $headerDiscount['ted_perc'] = $tedPerc;
                }
                $discounts->push($headerDiscount);
            }

            foreach ($po->headerExpenses as $headerExpense) {
                if (!intval($headerExpense->ted_perc)) {
                    $tedPerc = (floatval($headerExpense->ted_amount) / floatval($headerExpense->assessment_amount)) * 100;
                    $headerExpense['ted_perc'] = $tedPerc;
                }
                $expenses->push($headerExpense);
            }
        }
        $groupedDiscounts = $discounts
            ->groupBy('ted_id')
            ->map(function ($group) {
                return $group->sortByDesc('ted_perc')->first(); // Select the record with max `ted_perc`
            });
        $groupedExpenses = $expenses
            ->groupBy('ted_id')
            ->map(function ($group) {
                return $group->sortByDesc('ted_perc')->first(); // Select the record with max `ted_perc`
            });
        $finalDiscounts = $groupedDiscounts->values()->toArray();
        $finalExpenses = $groupedExpenses->values()->toArray();

        $poIds = $poItems->pluck('purchase_order_id')->all();
        $vendorId = PurchaseOrder::whereIn('id',$poIds)->pluck('vendor_id')->toArray();
        $vendorId = array_unique($vendorId);
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        if(count($vendorId) && count($vendorId) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "You can not selected multiple vendor of PO item at time."]);
        } else {
            $vendorId = $vendorId[0];
            $vendor = Vendor::find($vendorId);
            if ($vendor) {
                $vendor->billing = $vendor->addresses()
                    ->whereIn('type', ['billing', 'both'])
                    ->latest()
                    ->first();
                $vendor->shipping = $vendor->addresses()
                    ->whereIn('type', ['shipping', 'both'])
                    ->latest()
                    ->first();

                $vendor->currency = $vendor->currency;
                $vendor->paymentTerm = $vendor->paymentTerm;
            }
        }
        $html = view('procurement.expense-advise.partials.po-item-row', ['poItems' => $poItems, 'costCenters' => $costCenters])->render();
        return response()->json(['data' => ['pos' => $html, 'vendor' => $vendor,'finalDiscounts' => $finalDiscounts,'finalExpenses' => $finalExpenses, 'org_address' => $orgAddress], 'status' => 200, 'message' => "fetched!"]);
    }

    # Get SO Item List
    public function getSo(Request $request)
    {
        $applicableBookIds = array();
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $customerId = $request->customer_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        // Fetch applicable book IDs from the headerBookId
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $poItems = ErpSoItem::select(
            'erp_so_items.*',
            'erp_sale_orders.id as so_id',
            'erp_sale_orders.vendor_id as vendor_id',
            'erp_sale_orders.book_id as book_id'
            )
            ->leftJoin('erp_sale_orders', 'erp_sale_orders.id', 'erp_so_items.sale_order_id')
            ->whereIn('erp_sale_orders.book_id', $applicableBookIds)
            ->whereRaw('order_qty > expense_advise_qty')
            ->whereHas('item', function($item){
                $item->where('type', 'Service');
            })
            ->get();

        $soItems = PoItem::with('attributes')
        ->where(function ($query) use ($seriesId, $docNumber, $itemId, $customerId) {
            // Ensure item exists
            $query->whereHas('item', function($item){
                $item->where('type', 'Service');
            });

            // Check POs
            $query->whereHas('header', function ($header) use ($seriesId, $docNumber, $customerId) {
                // Filter by book_id (headerBookId)
                // Filter by series ID
                if($seriesId) {
                    $header->where('book_id',$seriesId);
                }

                // Filter by document number
                if ($docNumber) {
                    $header->where('document_number', $docNumber);
                }

                // Filter by customer ID
                if ($customerId) {
                    $header->where('customer_id', $customerId);
                }
            });

            // Filter by item ID if provided
            if ($itemId) {
                $query->where('item_id', $itemId);
            }

            // Ensure remaining quantity condition
            $query->whereRaw('order_qty > expense_advise_qty');
        })->get();

        $html = view('procurement.expense-advise.partials.so-item-list', ['soItems' => $soItems])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit SO Item list
    public function processSoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $ids = json_decode($request->ids,true) ?? [];
        $customer = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $soItems = ErpSoItem::whereIn('id', $ids)->get();
        $uniqueSoIds = ErpSoItem::whereIn('id', $ids)
                    ->distinct()
                    ->pluck('sale_order_id')
                    ->toArray();
        if(count($uniqueSoIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time expense advise create from one SO."]);
        }
        $soHeader = PurchaseOrder::whereIn('id', $uniqueSoIds)->get();
        $discounts = collect();
        $expenses = collect();

        foreach ($soHeader as $so) {
            foreach ($so->headerDiscount as $headerDiscount) {
                if (!intval($headerDiscount->ted_perc)) {
                    $tedPerc = (floatval($headerDiscount->ted_amount) / floatval($headerDiscount->assessment_amount)) * 100;
                    $headerDiscount['ted_perc'] = $tedPerc;
                }
                $discounts->push($headerDiscount);
            }

            foreach ($so->headerExpenses as $headerExpense) {
                if (!intval($headerExpense->ted_perc)) {
                    $tedPerc = (floatval($headerExpense->ted_amount) / floatval($headerExpense->assessment_amount)) * 100;
                    $headerExpense['ted_perc'] = $tedPerc;
                }
                $expenses->push($headerExpense);
            }
        }
        $groupedDiscounts = $discounts
            ->groupBy('ted_id')
            ->map(function ($group) {
                return $group->sortByDesc('ted_perc')->first(); // Select the record with max `ted_perc`
            });
        $groupedExpenses = $expenses
            ->groupBy('ted_id')
            ->map(function ($group) {
                return $group->sortByDesc('ted_perc')->first(); // Select the record with max `ted_perc`
            });
        $finalDiscounts = $groupedDiscounts->values()->toArray();
        $finalExpenses = $groupedExpenses->values()->toArray();

        $soIds = $soItems->pluck('sale_order_id')->all();
        $customerId = PurchaseOrder::whereIn('id',$soIds)->pluck('customer_id')->toArray();
        $customerId = array_unique($customerId);
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        if(count($customerId) && count($customerId) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "You can not selected multiple customer of SO item at time."]);
        }
        $html = view('procurement.expense-advise.partials.so-item-row', ['soItems' => $soItems, 'costCenters' => $costCenters])->render();
        return response()->json(['data' => ['pos' => $html, 'customer' => $customer,'finalDiscounts' => $finalDiscounts,'finalExpenses' => $finalExpenses], 'status' => 200, 'message' => "fetched!"]);
    }

    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::financeVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, "get");
            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex -> getMessage()
            ]);
        }
    }

    public function postExpenseAdvise(Request $request)
    {
        try {
            DB::beginTransaction();
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
            ]);
        }
    }

    // Revoke Document
    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $mrn = ExpenseHeader::find($request->id);
            if (isset($mrn)) {
                $revoke = Helper::approveDocument($mrn->book_id, $mrn->id, $mrn->revision_number, '', [], 0, ConstantHelper::REVOKE, $mrn->total_amount, get_class($mrn));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $mrn->document_status = $revoke['approvalStatus'];
                    $mrn->save();
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

    // Expense Advise Report
    public function Report()
    {
        $user = Helper::getAuthenticatedUser();
        $categories = Category::withDefaultGroupCompanyOrg()->where('parent_id', null)->get();
        $sub_categories = Category::withDefaultGroupCompanyOrg()->where('parent_id', '!=',null)->get();
        $items = Item::withDefaultGroupCompanyOrg()->get();
        $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
        $employees = Employee::where('organization_id', $user->organization_id)->get();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $attribute_groups = AttributeGroup::withDefaultGroupCompanyOrg()->get();
        $purchaseOrderIds = ExpenseHeader::withDefaultGroupCompanyOrg()
                            ->distinct()
                            ->pluck('purchase_order_id');
        $purchaseOrders = PurchaseOrder::whereIn('id', $purchaseOrderIds)->get();
        $soIds = ExpenseDetail::whereHas('expenseHeader', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->distinct()
                ->pluck('so_id');

        $so = ErpSaleOrder::whereIn('id', $soIds)->get();
        $gateEntry = ExpenseHeader::withDefaultGroupCompanyOrg()->get();
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;
        // $attributes = Attribute::get();
        return view('procurement.expense-advise.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'purchaseOrders', 'gateEntry', 'statusCss'));
    }

    public function getReportFilter(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $period = $request->query('period');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $poId = $request->query('poNo');
        $gateEntryId = $request->query('gateEntryNo');
        $soId = $request->query('soNo');
        $vendorId = $request->query('vendor');
        $itemId = $request->query('item');
        $status = $request->query('status');
        $mCategoryId = $request->query('m_category');
        $mSubCategoryId = $request->query('m_subCategory');
        $mAttribute = $request->query('m_attribute');
        $mAttributeValue = $request->query('m_attributeValue');

        $query = ExpenseHeader::query()
        ->withDefaultGroupCompanyOrg();

        if ($poId) {
            $query->where('purchase_order_id', $poId);
        }
        if ($gateEntryId) {
            $query->where('id', $gateEntryId);
        }

        $query->with([
            'items' => function($query) use ($itemId, $soId, $mCategoryId, $mSubCategoryId) {
            $query->whereHas('item', function($q) use ($itemId, $soId, $mCategoryId, $mSubCategoryId) {
                if ($itemId) {
                    $q->where('id', $itemId);
                }
                if ($soId) {
                    $q->where('so_id', $soId);
                }
                if ($mCategoryId) {
                    $q->where('category_id', $mCategoryId);
                }
                if ($mSubCategoryId) {
                    $q->where('subcategory_id', $mSubCategoryId);
                }
            });
        },
        'items.item', 'items.item.category', 'items.item.subCategory', 'vendor',
        'items.so', 'po']);

        // Date Filtering
        if (($startDate && $endDate) || $period) {
            if ($startDate && $endDate) {
                $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
                $endDate = Carbon::createFromFormat('d-m-Y', $endDate);
            }
            if (!$startDate || !$endDate) {
                switch ($period) {
                    case 'this-month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'last-month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'this-year':
                        $startDate = Carbon::now()->startOfYear();
                        $endDate = Carbon::now()->endOfYear();
                        break;
                }
            }
            $query->whereBetween('document_date', [$startDate, $endDate]);
        }

        // Vendor Filter
        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        // Status Filter
        if ($status) {
            $query->where('document_status', $status);
        }

        // Fetch Results
        $po_reports = $query->get();

        DB::enableQueryLog();

        return response()->json($po_reports);
    }

    public function addScheduler(Request $request)
    {
        try{
            $headers = $request->input('displayedHeaders');
            $data = $request->input('displayedData');
            $itemName = '';
            $poNo = '';
            $gateEntryNo = '';
            $soNo = '';
            $lotNo = '';
            $status = '';
            $vendorName = '';
            $categoryName = '';
            $subCategoriesName = '';
            $formattedstartDate = '';
            $formattedendDate = '';
            $startDate = '';
            $endDate = '';
            if ($request->filled('startDate')) {
                $startDate = new DateTime($request->input('startDate'));
            }

            if ($request->filled('endDate')) {
                $endDate = new DateTime($request->input('endDate'));
            }
            $period = $request->input('period');

            if (($startDate && $endDate) || $period) {
                if (!$startDate || !$endDate) {
                    switch ($period) {
                        case 'this-month':
                            $startDate = Carbon::now()->startOfMonth();
                            $endDate = Carbon::now()->endOfMonth();
                            break;
                        case 'last-month':
                            $startDate = Carbon::now()->subMonth()->startOfMonth();
                            $endDate = Carbon::now()->subMonth()->endOfMonth();
                            break;
                        case 'this-year':
                            $startDate = Carbon::now()->startOfYear();
                            $endDate = Carbon::now()->endOfYear();
                            break;
                    }
                }
                $formattedstartDate = $startDate->format('d-m-y');
                $formattedendDate = $endDate->format('d-m-y');
            }

            if ($request->filled('po_no'))
            {
                $poData = PurchaseOrder::find($request->input('po_no'));
                $poNo = optional($poData)->document_number;
            }

            if ($request->filled('so_no'))
            {
                $soData = ErpSaleOrder::find($request->input('so_no'));
                $soNo = optional($soData)->document_number;
            }

            if ($request->filled('gate_entry_no'))
            {
                $gateEntryNo = $request->input('gate_entry_no');
            }

            if ($request->filled('lot_no'))
            {
                $lotNo = $request->input('lot_no');
            }

            if ($request->filled('status'))
            {
                $status = $request->input('status');
            }

            if ($request->filled('m_category'))
            {
                $categories = Category::find($request->input('m_category'));
                $categoryName = optional($categories)->name;
            }

            if ($request->filled('m_subCategory'))
            {
                $subCategories = Category::find($request->input('m_subCategory'));
                $subCategoriesName = optional($subCategories)->name;
            }

            if ($request->filled('item'))
            {
                $itemData = ErpItem::find($request->input('item'));
                $itemName = optional($itemData)->item_name;
            }

            if ($request->filled('vendor'))
            {
                $vendorData = ErpVendor::find($request->input('vendor'));
                $vendorName = optional($vendorData)->company_name;
            }

            $blankSpaces = count($headers) - 1;
            $centerPosition = (int)floor($blankSpaces / 2);
            $filters = [
                'Filters',
                'Item: ' . $itemName,
                'Vendor: ' . $vendorName,
                'PO No: ' . $poNo,
                'SO No: ' . $soNo,
                'Status:' . $status,
                'Category:' . $categoryName,
                'Sub Category' . $subCategoriesName,
            ];

            $fileName = 'expense-advise.xlsx';
            $filePath = storage_path('app/public/expense-advise/' . $fileName);
            $directoryPath = storage_path('app/public/expense-advise');
            if($formattedstartDate && $formattedendDate)
            {
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Expense Advise Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else{
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Expense Advise Report' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }

            $remainingSpaces = $blankSpaces - count($filters) + 1;
            $filterHeader = array_merge($filters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new ExpenseAdviceExport($customHeader, $filterHeader, $headers, $data), \Maatwebsite\Excel\Excel::XLSX);

            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }
            file_put_contents($filePath, $excelData);
            if (!file_exists($filePath)) {
                throw new \Exception('File does not exist at path: ' . $filePath);
            }

            $email_to = $request->email_to ?? [];
            $email_cc = $request->email_cc ?? [];

            foreach($email_to as $email)
            {
                $user = AuthUser::where('email', $email)
                ->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                ->where('status', ConstantHelper::ACTIVE)
                ->get();

                if ($user->isEmpty()) {
                    $user = new AuthUser();
                    $user->email = $email;
                }
                $title = "Expense Advise Report Generated";
                $heading = "Expense Advise Report";

                $remarks = $request->remarks ?? null;
                $mail_from = '';
                $mail_from_name = '';
                $cc = implode(', ', $email_cc);
                $bcc = null;
                $attachment = $filePath ?? null;
                // $name = $user->name;
                $description = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif; line-height: 1.6;">
                    <tr>
                        <td>
                            <h2 style="color: #2c3e50; font-size: 24px; margin-bottom: 20px;">{$heading}</h2>
                            <p style="font-size: 16px; color: #555; margin-bottom: 20px;">
                                Dear <strong style="color: #2c3e50;">user</strong>,
                            </p>

                            <p style="font-size: 15px; color: #333; margin-bottom: 20px;">
                                We hope this email finds you well. Please find your expense advise report attached below.
                            </p>
                            <p style="font-size: 15px; color: #333; margin-bottom: 30px;">
                                <strong>Remark:</strong> {$remarks}
                            </p>
                            <p style="font-size: 14px; color: #777;">
                                If you have any questions or need further assistance, feel free to reach out to us.
                            </p>
                        </td>
                    </tr>
                </table>
                HTML;
                self::sendMail($user,$title,$description,$cc,$bcc, $attachment,$mail_from,$mail_from_name);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'emails sent successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }


    }
    public function sendMail($receiver, $title, $description, $cc= null, $bcc= null, $attachment, $mail_from=null, $mail_from_name=null)
    {
        if (!$receiver || !isset($receiver->email)) {
            return "Error: Receiver details are missing or invalid.";
        }

        dispatch(new SendEmailJob($receiver, $mail_from, $mail_from_name,$title,$description,$cc,$bcc, $attachment));
        return response() -> json([
            'status' => 'success',
            'message' => 'Email request sent succesfully',
        ]);

    }

    public function expenseAdviseReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $pathUrl = route('expense-adv.index');
        $orderType = ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS;
        $expenseAdvises = ExpenseHeader::withDefaultGroupCompanyOrg()
            // ->where('document_type', $orderType)
            // ->bookViewAccess($pathUrl)
            ->withDraftListingLogic()
            ->orderByDesc('id');

        // Vendor Filter
        $expenseAdvises = $expenseAdvises->when($request->vendor, function ($vendorQuery) use ($request) {
            $vendorQuery->where('vendor_id', $request->vendor);
        });

        // PO No Filter
        $expenseAdvises = $expenseAdvises->when($request->po_no, function ($poQuery) use ($request) {
            $poQuery->where('purchase_order_id', $request->po_no);
        });

        // Document Status Filter
        $expenseAdvises = $expenseAdvises->when($request->status, function ($docStatusQuery) use ($request) {
            $searchDocStatus = [];
            if ($request->status === ConstantHelper::DRAFT) {
                $searchDocStatus = [ConstantHelper::DRAFT];
            } else if ($request->status === ConstantHelper::SUBMITTED) {
                $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
            } else {
                $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
            }
            $docStatusQuery->whereIn('document_status', $searchDocStatus);
        });

        // Date Filters
        $dateRange = $request->date_range ?? Carbon::now()->startOfMonth()->format('Y-m-d') . " to " . Carbon::now()->endOfMonth()->format('Y-m-d');
        $expenseAdvises = $expenseAdvises->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
            $dateRanges = explode('to', $dateRange);
            if (count($dateRanges) == 2) {
                $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                $toDate = Carbon::parse(trim($dateRanges[1]))->format('Y-m-d');
                $dateRangeQuery->whereDate('document_date', ">=", $fromDate)->where('document_date', '<=', $toDate);
            }
        });

        // Item Id Filter
        // $materialReceipts = $materialReceipts->when($request->item_id, function ($itemQuery) use ($request) {
        //     $itemQuery->withWhereHas('items', function ($itemSubQuery) use ($request) {
        //         $itemSubQuery->where('item_id', $request->item_id)
        //             // Compare Item Category
        //             ->when($request->item_category_id, function ($itemCatQuery) use ($request) {
        //                 $itemCatQuery->whereHas('item', function ($itemRelationQuery) use ($request) {
        //                     $itemRelationQuery->where('category_id', $request->item_category_id)
        //                         // Compare Item Sub Category
        //                         ->when($request->item_sub_category_id, function ($itemSubCatQuery) use ($request) {
        //                             $itemSubCatQuery->where('subcategory_id', $request->item_sub_category_id);
        //                         });
        //                 });
        //             });
        //     });
        // });

        $expenseAdvises->with([
            'items' => function ($query) use ($request) {
                $query
                    ->when($request->item_id, function ($subQuery) use ($request) {
                        $subQuery->where('item_id', $request->item_id);
                    })
                    ->when($request->so_no, function ($subQuery) use ($request) {
                        $subQuery->where('so_id', $request->so_no);
                    })
                    ->whereHas('item', function ($q) use ($request) {
                        $q->when($request->m_category_id, function ($subQ) use ($request) {
                            $subQ->where('category_id', $request->m_category_id);
                        });

                        $q->when($request->m_subcategory_id, function ($subQ) use ($request) {
                            $subQ->where('category_id', $request->m_subcategory_id);
                        });
                    });
            },
            'items.item',
            'items.item.category',
            'items.item.subCategory',
            'vendor',
            'items.so',
            'po'
        ]);


        $expenseAdvises = $expenseAdvises->get();
        $processedExpenseAdvises = collect([]);

        foreach ($expenseAdvises as $expenseAdvise) {
            foreach ($expenseAdvise->items as $expenseAdviseItem) {
                $reportRow = new stdClass();

                // Header Details
                $header = $expenseAdviseItem->expenseHeader;
                $total_item_value = (($expenseAdviseItem?->rate ?? 0.00) * ($expenseAdviseItem?->accepted_qty ?? 0.00)) - ($expenseAdviseItem?->discount_amount ?? 0.00);
                $reportRow->id = $expenseAdviseItem->id;
                $reportRow->book_code = $header->book_code;
                $reportRow->document_number = $header->document_number;
                $reportRow->document_date = $header->document_date;
                $reportRow->po_no = !empty($header->po?->book_code) && !empty($header->po?->document_number)
                                    ? $header->po?->book_code . ' - ' . $header->po?->document_number
                                    : '';
                $reportRow->so_no = !empty($header->so?->book_code) && !empty($header->so?->document_number)
                                    ? $header->so?->book_code . ' - ' . $header->so?->document_number
                                    : '';
                $reportRow->vendor_name = $header->vendor ?-> company_name;
                $reportRow->vendor_rating = null;
                $reportRow->category_name = $expenseAdviseItem->item ?->category ?-> name;
                $reportRow->sub_category_name = $expenseAdviseItem->item ?->category ?-> name;
                $reportRow->item_type = $expenseAdviseItem->item ?->type;
                $reportRow->sub_type = null;
                $reportRow->item_name = $expenseAdviseItem->item ?->item_name;
                $reportRow->item_code = $expenseAdviseItem->item ?->item_code;

                // Amount Details
                $reportRow->receipt_qty = number_format($expenseAdviseItem->accepted_qty, 2);
                $reportRow->store_name = $expenseAdviseItem?->erpStore?->store_name;
                $reportRow->rate = number_format($expenseAdviseItem->rate);
                $reportRow->basic_value = number_format($expenseAdviseItem->basic_value, 2);
                $reportRow->item_discount = number_format($expenseAdviseItem->discount_amount, 2);
                $reportRow->header_discount = number_format($expenseAdviseItem->header_discount_amount, 2);
                $reportRow->item_amount = number_format($total_item_value, 2);

                // Attributes UI
                // $attributesUi = '';
                // if (count($mrnItem->item_attributes) > 0) {
                //     foreach ($mrnItem->item_attributes as $mrnAttribute) {
                //         $attrName = $mrnAttribute->attribute_name;
                //         $attrValue = $mrnAttribute->attribute_value;
                //         $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName : $attrValue </span>";
                //     }
                // } else {
                //     $attributesUi = 'N/A';
                // }
                // $reportRow->item_attributes = $attributesUi;

                // Document Status
                $reportRow->status = $header->document_status;
                $processedExpenseAdvises->push($reportRow);
            }
        }

        return DataTables::of($processedExpenseAdvises)
            ->addIndexColumn()
            ->editColumn('status', function ($row) use ($orderType) {
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->status ?? ConstantHelper::DRAFT];
                $displayStatus = ucfirst($row->status);
                return "
                    <div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClass'>$displayStatus</span>
                    </div>
                ";
            })
            ->rawColumns(['status'])
            ->make(true);
    }

}
