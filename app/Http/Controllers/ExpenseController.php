<?php
namespace App\Http\Controllers;

use Auth;
use PDF;
use DB;
use View;
use Session;
use Yajra\DataTables\DataTables;


use Illuminate\Http\Request;
use App\Http\Requests\ExpenseRequest;
use App\Http\Requests\EditExpenseRequest;

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
use App\Helpers\InventoryHelper;

use App\Services\ExpenseService;
use Illuminate\Http\Exceptions\HttpResponseException;


class ExpenseController extends Controller
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
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first(); 
            $records = ExpenseHeader::with(
                [
                    'items',
                    'vendor',
                ]
            )
            ->where('organization_id', $user->organization_id)
            ->where('company_id', $organization->company_id)
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];    
                    return "<span class='badge rounded-pill $statusClasss  badgeborder-radius'>$row->display_status</span>";
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book ? $row->book?->book_name : 'N/A';
                })
                ->editColumn('document_date', function ($row) {
                    return date('d/m/Y', strtotime($row->document_date)) ?? 'N/A';
                })
                ->editColumn('revision_number', function ($row) {
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
                ->addColumn('action', function ($row) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="' . route('expense.edit', $row->id) . '">
                                    <i data-feather="edit-3" class="me-50"></i>
                                    <span>Edit</span>
                                </a>
                            </div>
                        </div>';
                })
                ->rawColumns(['document_status', 'action'])
                ->make(true);
        }
        return view('procurement.expense.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $bookTypeAlias = ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS;
        $books = Helper::getSeriesCode($bookTypeAlias)->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $customers = Customer::where('status',ConstantHelper::ACTIVE)->get();
        $purchaseOrders = PurchaseOrder::with('vendor')->get();
        $saleOrders = ErpSaleOrder::with('customer')->get();
        return view('procurement.expense.create', [
            'books'=>$books,
            'vendors' => $vendors,
            'purchaseOrders' => $purchaseOrders,
            'saleOrders'=>$saleOrders,
            'customers'=>$customers
        ]);
    }

    # Purchase Order store
    public function store(ExpenseRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        
        DB::beginTransaction();
        try {
            // dd($request->all());
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
            //Tax Country and State
            $firstAddress = $organization->addresses->first();
            $companyCountryId = null;
            $companyStateId = null;
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
            $expense->series_id = $request->series_id;
            $expense->book_id = $request->series_id;
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
            $expense->final_remarks = $request->remarks ?? null;
        
            $expense->total_item_amount = 0.00;
            $expense->total_discount = 0.00;
            $expense->taxable_amount = 0.00;
            $expense->total_taxes = 0.00;
            $expense->total_after_tax_amount = 0.00;
            $expense->expense_amount = 0.00;
            $expense->total_amount = 0.00;
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
        
            $totalItemValue = 0.00;
            $totalTaxValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalTax = 0;
        
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
                    if(isset($component['so_item_id']) && $component['so_item_id']){
                        $soDetail =  ErpSoItem::find($component['so_item_id']);
                        $so_detail_id = $soDetail->id ?? null;
                        if($soDetail){
                            $soDetail->invoice_qty += $component['accepted_qty'];
                            $soDetail->save();
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
                        $inventory_uom_qty = floatval($component['accepted_qty']) ?? 0.00 ;
                    } else {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
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
            
                    $expenseItemArr[] = [
                        'expense_header_id' => $expense->id,
                        'purchase_order_item_id' => (isset($component['po_detail_id']) && $component['po_detail_id']) ?? null,
                        'sale_order_item_id' => (isset($component['so_item_id']) && $component['so_item_id']) ?? null,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' => $item->uom_id ?? null,
                        'uom_code' => $item->uom->name ?? null,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id,
                        'inventory_uom_code' => $inventory_uom_code,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'cost_center_id' => $component['cost_center_id'] ?? null,
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
            
                        $taxDetails = TaxHelper::calculateTax($expenseItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> shipping_country_id, $partyStateId ?? $request -> shipping_state_id, 'collection');
            
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $expenseItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($expenseItem);
            
                foreach($expenseItemArr as $_key => $expenseItem) {
                    $itemPriceAterBothDis =  $expenseItem['basic_value'] - $expenseItem['discount_amount'] - $expenseItem['header_discount_amount'];
                    $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;
            
                    $expenseDetail = new ExpenseDetail;
                    $expenseDetail->expense_header_id = $expenseItem['expense_header_id']; 
                    $expenseDetail->purchase_order_item_id = $expenseItem['purchase_order_item_id']; 
                    $expenseDetail->sale_order_item_id = $expenseItem['sale_order_item_id']; 
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
                    $expenseDetail->cost_center_id = $expenseItem['cost_center_id']; 
                    $expenseDetail->rate = $expenseItem['rate']; 
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
                                $ted->ted_id = null;
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
                            $ted->detail_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = null;
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
                            $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                            $ted = new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = null;
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
                $expense->total_discount = $totalDiscValue ?? 0.00;
                $expense->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $expense->total_taxes = $totalTax ?? 0.00;
                $expense->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $totalTax) ?? 0.00;
                $expense->expense_amount = $totalHeaderExpense ?? 0.00;
                $totalAmount = (($itemTotalValue - $totalDiscValue) + ($totalTax + $totalHeaderExpense)) ?? 0.00;
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
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $expense->book_id;
                $docId = $expense->id;
                $remarks = $expense->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $expense->approval_level;
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
            
            DB::commit();
        
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $expense,
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
        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'DESC')
            ->get();
        return view('procurement.expense.view', [
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
        $user = Helper::getAuthenticatedUser();
        // dd($user);
        $expense = ExpenseHeader::with(['vendor', 'currency', 'items', 'items.costCenter', 'book'])
            ->findOrFail($id);
        $bookTypeAlias = ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS;
        $books = Helper::getBookSeries($bookTypeAlias)->get();
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
        // dd($approvalHistory);
        $view = 'procurement.expense.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $expense->revision_number) {
            $expense = $expense->source;
            $expense = ExpenseHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('header_id', $expense->header_id)
                ->first();
            $view = 'procurement.expense.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$expense->document_status] ?? '';
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        $costCenters = CostCenter::where('organization_id', $user->organization_id)->get();
        
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
                $a = Helper::documentAmendment($revisionData, $id);
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
            $expense->final_remarks = $request->remarks ?? '';
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

            $totalItemValue = 0.00;
            $totalTaxValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalTax = 0;

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
                    if(isset($component['so_item_id']) && $component['so_item_id']){
                        $soDetail =  ErpSoItem::find($component['so_item_id']);
                        $so_detail_id = $soDetail->id ?? null;
                        if($soDetail){
                            $soDetail->invoice_qty += $component['accepted_qty'];
                            $soDetail->save();
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
                        $inventory_uom_qty = floatval($component['accepted_qty']) ?? 0.00 ;
                    } else {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
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
            
                    $expenseItemArr[] = [
                        'expense_header_id' => $expense->id,
                        'purchase_order_item_id' => $po_detail_id,
                        'sale_order_item_id' => $so_detail_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' => $item->uom_id ?? null,
                        'uom_code' => $item->uom->name ?? null,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id,
                        'inventory_uom_code' => $inventory_uom_code,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'cost_center_id' => $component['cost_center_id'] ?? null,
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
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if($isTax) {
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($expenseItem['basic_value'] - $headerDiscount - $expenseItem['discount_amount']);
                        $shippingAddress = $expense->shippingAddress;
            
                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($expenseItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'collection');
            
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $expenseItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($expenseItem);
            
                foreach($expenseItemArr as $_key => $expenseItem) {
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];
                    $itemPriceAterBothDis =  $expenseItem['basic_value'] - $expenseItem['discount_amount'] - $expenseItem['header_discount_amount'];
                    $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
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
                    $expenseDetail->sale_order_item_id = $expenseItem['sale_order_item_id']; 
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
                    $expenseDetail->cost_center_id = $expenseItem['cost_center_id']; 
                    $expenseDetail->rate = $expenseItem['rate']; 
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
                                $ted->ted_id = null;
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
                            $ted->assesment_amount = $expenseDetail->assessment_amount_total;
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
                            $ted->ted_id = null;
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
                            $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                            $expenseAmountId = @$dis['e_id'] ?? null;
                            $ted = ExpenseTed::find($expenseAmountId) ?? new ExpenseTed;
                            $ted->expense_header_id = $expense->id;
                            $ted->expense_detail_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = null;
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
                $expense->total_discount = $totalDiscValue ?? 0.00;
                $expense->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $expense->total_taxes = $totalTax ?? 0.00;
                $expense->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $totalTax) ?? 0.00;
                $expense->expense_amount = $totalHeaderExpense ?? 0.00;
                $totalAmount = (($itemTotalValue - $totalDiscValue) + ($totalTax + $totalHeaderExpense)) ?? 0.00;
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
            $currentLevel = $expense->approval_level;
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
            DB::commit();
            
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $expense,
            ]);
        
        } catch (Exception $e) {
            // dd($e);
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
        // dd($item);
        $componentItem = json_decode($request->component_item,true) ?? [];
        $costCenters = CostCenter::where('organization_id', $user->organization_id)->get();
        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                // return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.expense.partials.item-row',compact(['rowCount', 'costCenters']))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    // PO Item Rows
    public function poItemRows(Request $request)
    {   
        //dd('hii');
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $item_ids = explode(',', $request->item_ids);
        $items = PoItem::whereIn('id', $item_ids)
            ->get();
        //dd($items);
        $costCenters = CostCenter::where('organization_id', $user->organization_id)->get();
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
            'procurement.expense.partials.po-item-row',
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
        // dd('hii');
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $item_ids = explode(',', $request->item_ids);
        $items = ErpSoItem::whereIn('id', $item_ids)
            ->get();
        // dd($items);
        $costCenters = CostCenter::where('organization_id', $user->organization_id)->get();
        $html = view(
            'procurement.expense.partials.so-item-row',
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
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $html = view('procurement.expense.partials.comp-attribute',compact('item','attributeGroups','rowCount','selectedAttr'))->render();
        $hiddenHtml = '';
        foreach ($item->itemAttributes as $attribute) {
                $selected = '';
                foreach ($attribute->attributes() as $value){
                    if (in_array($value->id, $selectedAttr)){
                        $selected = $value->id;
                    }
                }
            $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
        }
        return response()->json(['data' => ['html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
    }

    # Add discount row
    public function addDiscountRow(Request $request)
    {
        $tblRowCount = intval($request->tbl_row_count) ? intval($request->tbl_row_count) + 1 : 1;
        $rowCount = intval($request->row_count);
        $disName = $request->dis_name;
        $disPerc = $request->dis_perc;
        $disAmount = $request->dis_amount;
        $html = view('procurement.expense.partials.add-disc-row',compact('tblRowCount','rowCount','disName','disAmount','disPerc'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # get tax calcualte
    public function taxCalculation(Request $request)
    {
        // dd($request->all());
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
            $upToCountry = $request->input('party_country_id', $companyCountryId);
            $upToState = $request->input('party_state_id', $companyStateId);
        } else {
            $fromCountry = $request->input('party_country_id', $companyCountryId);
            $fromState = $request->input('party_state_id', $companyStateId);
            $upToCountry = $companyCountryId;
            $upToState = $companyStateId;
        }
        try {
            $taxDetails = TaxHelper::calculateTax( $hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = intval($request->price) ?? 0;
            $html = view('procurement.expense.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAddress(Request $request)
    {
        $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])->find($request->id);
        $currency = $vendor->currency;
        $paymentTerm = $vendor->paymentTerms;
        $shipping = $vendor->addresses()->where(function($query) {
                        $query->where('type', 'shipping')
                        ->orWhere('type', 'both')
                        ->orWhere('is_shipping', 1);
                    })->latest()->first();
        $billing = $vendor->addresses()->where(function($query) {
                    $query->where('type', 'billing')
                    ->orWhere('type', 'both')
                    ->orWhere('is_billing', 1);
                })->latest()->first();

        $vendorId = $vendor->id;
        $documentDate = $request->document_date;
        $billingAddresses = ErpAddress::where('addressable_id', $vendorId)
            ->where('addressable_type', Vendor::class)
            ->where(function($query) {
                $query->whereIn('type', ['billing', 'both'])
                ->orWhere('is_billing', 1);
            })
            ->get();
        $shippingAddresses = ErpAddress::where('addressable_id', $vendorId)
            ->where('addressable_type', Vendor::class)
            ->where(function($query) {
                $query->whereIn('type', ['shipping', 'both'])
                ->orWhere('is_billing', 1);
            })
            ->get();
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

        return response()->json(['data' => ['shipping' => $shipping,'billing' => $billing, 'paymentTerm' => $paymentTerm, 'currency' => $currency, 'currency_exchange' => $currencyData], 'status' => 200, 'message' => 'fetched']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getStoreRacks(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        // dd($user);
        $storeBins = array();
        $storeRacks = array();
        $storeCode = ErpStore::find($request->store_code_id);
        if($storeCode){
            // Fetch storeRacks
            $storeRacks = ErpRack::where('erp_store_id', $storeCode->id)
                ->where('organization_id', $user->organization_id)
                ->pluck('rack_code', 'id');

            $storeBins = ErpBin::where('erp_store_id', $storeCode->id)
                ->where('organization_id', $user->organization_id)
                ->pluck('bin_code', 'id');

        }
        // Return data as JSON
        return response()->json([
            'storeBins' => $storeBins,
            'storeRacks' => $storeRacks,
        ]);
    }

    public function getStoreShelfs(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $storeShelfs = array();
        $rackCode = ErpRack::find($request->rack_code_id);
        if($rackCode){
            // Fetch storeShelfs
            // dd($rackCode);
            $storeShelfs = ErpShelf::where('erp_rack_id', $rackCode->id)
                ->where('organization_id', $user->organization_id)
                ->pluck('shelf_code', 'id');

        }
        // Return data as JSON
        return response()->json([
            'storeShelfs' => $storeShelfs
        ]);
    }

    public function getStoreBins(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $storeBins = array();
        $shelfCode = ErpShelf::find($request->shelf_code_id);
        if($shelfCode){
            // Fetch storeBins
            $storeBins = ErpBin::where('erp_shelf_id', $shelfCode->id)
                ->where('organization_id', $user->organization_id)
                ->pluck('bin_code', 'id');

        }
        // Return data as JSON
        return response()->json([
            'storeBins' => $storeBins
        ]);
    }

    # Get edit address modal
    public function editAddress(Request $request)
    {
        $type = $request->type;
        $addressId = $request->address_id;
        $vendor = Vendor::find($request->vendor_id ?? null);
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
            $html = view('procurement.po.partials.edit-address-modal',compact('addresses','selectedAddress'))->render();
        }
        return response()->json(['data' => ['html' => $html,'selectedAddress' => $selectedAddress], 'status' => 200, 'message' => 'fetched!']);
    }

    # Save Address
    public function addressSave(Request $request)
    {

        $addressId = $request->address_id;
        if(!$addressId) {
            $request->validate([
                'country_id' => 'required',
                'state_id' => 'required',
                'city_id' => 'required',
                'pincode' => 'required',
                'address' => 'required'
            ]);
        }

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

        if($selectedAddress->country_id == $countryId && $selectedAddress->state_id == $stateId && $selectedAddress->city_id == $cityId && $selectedAddress->pincode == $pincode && $selectedAddress->address == $address) {
            $newAddress = $selectedAddress;
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
        //dd('hii');
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $itemId = $request->item_id;
        $item = Item::find($request->item_id ?? null);
        // dd($item);
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
        $saleOrder = ErpSaleOrder::find($request->sale_order_id);
        $html = view('procurement.expense.partials.comp-item-detail',compact('item','purchaseOrder', 'saleOrder', 'selectedAttr','remark','uomName','qty'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    public function getPoItemsByVendorId(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])
                ->where('organization_id', $organization->id)
                ->find($request->vendor_id);
            //dd($vendor);
            $items = PoItem::with([
                'po',
                'item',
                'attributes'
            ])
            ->whereHas('po', function ($q) use ($request, $organization) {
                $q->where('vendor_id', $request->vendor_id)
                ->where('document_status', '=', 'approved');
            })
            ->whereHas('item', function ($q) {
                $q->where('type', 'like', '%Service%');
            })
            ->get();

            $currency = $vendor->currency;
            $paymentTerm = $vendor->paymentTerms;
            $shipping = $vendor->addresses()->where('type','shipping')->Orwhere('type','both')->latest()->first();
            $billing = $vendor->addresses()->where('type','billing')->Orwhere('type','both')->latest()->first();
            $response = [
                'success' => true,
                'error' => '',
                'response' => [
                    'data' => $items
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function getPoItemsByPoId(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $items = PoItem::with([
                'po',
                'item',
                'attributes',
            ])
            ->whereHas('po', function ($q) use ($request, $organization) {
                $q->where('organization_id', $organization->id)
                ->where('document_status', '=', 'approved');
            })
            ->whereHas('item', function ($q) {
                $q->where('type', 'like', '%Service%');
            })
            ->where('purchase_order_id', $request->purchase_order_id)
            ->get();
 
            $response = [
                'success' => true,
                'error' => '',
                'response' => [
                    'data' => $items
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            dd($e);
        }
    }
    
    public function getSoItemsBySoId(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $items = ErpSoItem::with([
                'header',
                'item',
                'item_attributes',
            ])
            ->whereHas('header', function ($q) use ($request, $organization) {
                $q->where('organization_id', $organization->id)
                ->where('document_status', '=', 'approved');
            })
            ->whereHas('item', function ($q) {
                $q->where('type', 'like', '%Service%');
            })
            ->where('sale_order_id', $request->sale_order_id)
            ->get();
            
            $response = [
                'success' => true,
                'error' => '',
                'response' => [
                    'data' => $items
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function getSoItemsByCustomerId(Request $request)
    {   
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $customer = Customer::with(['currency:id,name', 'paymentTerms:id,name'])
                    ->find($request->customer_id);
            $items = ErpSoItem::with([
                'header',
                'item',
                'item_attributes',
            ])
            ->whereHas('header', function ($q) use ($request, $organization) {
                $q->where('customer_id', $request->customer_id)
                ->where('document_status', '=', 'approved');
            })
            ->whereHas('item', function ($q) {
                $q->where('type', 'like', '%Service%');
            })
            ->get();
            //dd($items->toArray());
            $response = [
                'success' => true,
                'error' => '',
                'response' => [
                    'data' => $items
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            dd($e);
        }
    }
    
    # Component Delete
    public function componentDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $expenseHeader = null;
            $totalItemValue = 0.00;
            $totalDiscValue = 0.00;
            $totalTaxableValue = 0.00;
            $totalTaxes = 0.00;
            $totalAfterTax = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalAmount = 0.00;
            $componentIds = json_decode($request->ids,true) ?? [];
            $expenseItems = ExpenseDetail::with(['attributes'])->whereIn('id', $componentIds)->get();
            // dd($expenseItems);
            foreach($expenseItems as $expenseItem) {
                $expenseHeader = $expenseItem->expenseHeader;
                $totalItemValue = $expenseHeader->total_item_amount - ($expenseItem->accepted_qty*$expenseItem->rate);
                $totalDiscValue = $expenseHeader->total_discount - ($expenseItem->discount_amount + $expenseItem->header_discount_amount);
                $totalTaxableValue = ($totalItemValue - $totalDiscValue);
                $totalTaxes = $expenseHeader->total_taxes - $expenseItem->tax_value;
                $totalAfterTax = ($totalTaxableValue + $totalTaxes);
                $totalExpValue = $expenseHeader->expense_amount - $expenseItem->header_exp_amount;
                $totalAmount = ($totalAfterTax + $totalExpValue);
                // dd($totalItemValue, $totalDiscValue, $totalTaxableValue, $totalTaxes, $totalAfterTax, $totalExpValue, $totalAmount);    

                $expenseHeader->total_item_amount = $totalItemValue;
                $expenseHeader->total_discount = $totalDiscValue;
                $expenseHeader->taxable_amount = $totalTaxableValue;
                $expenseHeader->total_taxes = $totalTaxes;
                $expenseHeader->total_after_tax_amount = $totalAfterTax;
                $expenseHeader->total_amount = $totalAmount;
                $expenseHeader->save();

                $headerDic = ExpenseTed::where('expense_header_id', $expenseHeader->id)
                    ->where('ted_level', 'H')
                    ->where('ted_type','Discount')
                    ->first();
                if($headerDic){
                    $headerDic->ted_amount -= $expenseItem->header_discount_amount;
                    $headerDic->save();
                }

                $headerExp = ExpenseTed::where('expense_header_id', $expenseHeader->id)
                    ->where('ted_level', 'H')
                    ->where('ted_type','Expense')
                    ->first();
                if($headerExp){
                    $headerExp->ted_amount -= $expenseItem->header_exp_amount;
                    $headerExp->save();
                }

                $expenseItem->attributes()->delete();
                $expenseItem->itemDiscount()->delete();
                $expenseItem->taxHistories()->delete();
                $expenseItem->taxes()->delete();
                if($expenseItem->sale_order_item_id){
                    $soItem = ErpSoItem::find($expenseItem->sale_order_item_id);
                    if($soItem) {
                        $soItem->invoice_qty = $soItem->invoice_qty - $expenseItem->accepted_qty;
                        $soItem->save();
                    }
                }
                if($expenseItem->purchase_order_item_id){
                    $poItem = PoItem::find($expenseItem->purchase_order_item_id);
                    if($poItem) {
                        $poItem->grn_qty = $poItem->grn_qty - $expenseItem->accepted_qty;
                        $poItem->save();
                    }
                }
                $expenseItem->delete();
            }
            if($expenseHeader) {
                /*Update Po header*/
                $to_h_dis = $expenseHeader->headerDiscount()->sum('ted_amount'); // total head dis
                $to_h_exp = $expenseHeader->expenses()->sum('ted_amount'); // total head dis
                $totalTax = $expenseHeader->total_taxes;
                $afterTaxAmntTotal = $expenseHeader->total_expAssessment_amount; 
                foreach($expenseHeader->items as $item) {
                    $taxAmnt = $expenseHeader->total_item_amount - $item->discount_amount; // total taxable amount 
                    $h_dis = ($item->accepted_qty*$item->rate - $item->discount_amount) / $taxAmnt * $to_h_dis;
                    $item->header_discount_amount = $h_dis;
                    $h_exp = ($item->accepted_qty*$item->rate - ($item->header_discount_amount + $item->discount_amount)) + $item->tax_value;
                    $final_h_exp = $h_exp/$h_exp*$to_h_exp;
                    $item->header_exp_amount = $final_h_exp; 
                    $item->save();
                }

                foreach($expenseHeader->expense_ted()->where('ted_type','Expense')->where('ted_level','H')->get() as $expenseHeader_ted) {
                    $expenseHeader_ted->assesment_amount = $expenseHeader->total_expAssessment_amount;
                    $expenseHeader_ted->save();
                }
                $itemLevelTotalDis = $expenseHeader->items()->sum('discount_amount'); 
                foreach($expenseHeader->expense_ted()->where('ted_type','Discount')->where('ted_level','H')->get() as $expenseHeader_ted) {
                    $expenseHeader_ted->assesment_amount = $expenseHeader->total_item_value - $itemLevelTotalDis;
                    $expenseHeader_ted->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 200,'message' => 'Component deleted successfully.']);

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            \Log::error('Error deleting component: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete the component.'], 500);
        }
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
        
        $amountInWords = NumberHelper::convertAmountToWords($expense->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory

        $pdf = PDF::loadView(
            // return view(
            'pdf.mrn',
            [
                'mrn' => $expense,
                'shippingAddress' => $shippingAddress,
                'organization' => $organization,
                'amountInWords' => $amountInWords,
                'organizationAddress' => $organizationAddress,
                'imagePath' => $imagePath
            ]
        );

        return $pdf->stream('Expense.pdf');
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
            $taxDetails = TaxHelper::calculateTax($expense_item->hsn_id, $priceAfterBothDis, $companyCountryId, $companyStateId, $vendorShippingCountryId, $vendorShippingStateId, 'sale');
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
        // dd($totalItemValue, $totalDiscValue, ($totalItemValue - $totalDiscValue), $totalTaxValue, (($totalItemValue - $totalDiscValue) + $totalTaxValue), $totalExpValue, (($totalItemValue - $totalDiscValue) + ($totalTaxValue + $totalExpValue)));
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
            $expenseHeader->status = "draft";
            $expenseHeader->document_status = "draft";
            $expenseHeader->save();

            /*Create document submit log*/
            if ($expenseHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $expenseHeader->series_id; 
                $docId = $expenseHeader->id;
                $remarks = $expenseHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $expenseHeader->approval_level;
                $revisionNumber = $expenseHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }

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

}