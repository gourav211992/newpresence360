<?php
namespace App\Http\Controllers;

use Auth;
use PDF;
use DB;
use View;
use Session;
use Yajra\DataTables\DataTables;

use Illuminate\Http\Request;
use App\Http\Requests\MaterialReceiptRequest;
use App\Http\Requests\EditMaterialReceiptRequest;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\MrnItemLocation;
use App\Models\MrnExtraAmount;
use App\Models\AlternateUOM;

use App\Models\MrnHeaderHistory;
use App\Models\MrnDetailHistory;
use App\Models\MrnAttributeHistory;
use App\Models\MrnItemLocationHistory;
use App\Models\MrnExtraAmountHistory;

use App\Models\Hsn;
use App\Models\Tax;
use App\Models\Book;
use App\Models\Unit;
use App\Models\Item;
use App\Models\City;
use App\Models\State;
use App\Models\Vendor;
use App\Models\ErpBin;
use App\Models\PoItem;
use App\Models\Country;
use App\Models\Address;
use App\Models\ErpRack;
use App\Models\Currency;
use App\Models\ErpStore;
use App\Models\ErpShelf;
use App\Models\VendorBook;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\NumberPattern;
use App\Models\AttributeGroup;

use App\Models\StockLedger;
use App\Models\StockLedgerItemAttribute;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\BookHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\ServiceParametersHelper;

use App\Services\MrnService;
use Illuminate\Http\Exceptions\HttpResponseException;


class MaterialReceiptController extends Controller
{
    protected $mrnService;

    public function __construct(MrnService $mrnService)
    {
        $this->mrnService = $mrnService;
    }
    public function get_mrn_no($book_id)
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
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = MrnHeader::with(
                [
                    'items',
                    'vendor',
                ]
            )
            ->where('organization_id', $user->organization_id)
            ->bookViewAccess($parentUrl)
            ->where('company_id', $organization->company_id)
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $route = route('material-receipt.edit', $row->id);
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
                ->rawColumns(['document_status'])
                ->make(true);
        }
        return view('procurement.material-receipt.index', [
            'servicesBooks'=>$servicesBooks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        //Get the menu
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $serviceAlias = $servicesBooks['services'][0]->alias ?? ConstantHelper::MRN_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $purchaseOrders = PurchaseOrder::with('vendor')->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        // dd($servicesBooks['services']);
        return view('procurement.material-receipt.create', [
            'books'=>$books,
            'vendors' => $vendors,
            'locations'=>$locations,
            'servicesBooks'=>$servicesBooks,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    # MRN store
    public function store(MaterialReceiptRequest $request)
    {
        // dd($request->all());
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

            # Mrn Header save
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

            $mrn = new MrnHeader();
            $mrn->fill($request->all());
            $mrn->store_id = $request->header_store_id;
            $mrn->organization_id = $organization->id;
            $mrn->bill_to_follow = $request->bill_to_follow;
            $mrn->group_id = $organization->group_id;
            $mrn->book_code = $request->book_code;
            $mrn->series_id = $request->book_id;
            $mrn->book_id = $request->book_id;
            $mrn->book_code = $request->book_code ?? null;
            $mrn->vendor_code = $request->vendor_code;
            $mrn->company_id = $organization->company_id;
            $mrn->gate_entry_date = $request->gate_entry_date ? date('Y-m-d', strtotime($request->gate_entry_date)) : '';
            $mrn->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $mrn->billing_to = $request->billing_id;
            $mrn->ship_to = $request->shipping_id;
            $mrn->billing_address = $request->billing_address;
            $mrn->shipping_address = $request->shipping_address;
            $mrn->revision_number = 0;
            $document_number = $request->document_number ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
            $regeneratedDocExist = MrnHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }

            $mrn->doc_number_type = $numberPatternData['type'];
            $mrn->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $mrn->doc_prefix = $numberPatternData['prefix'];
            $mrn->doc_suffix = $numberPatternData['suffix'];
            $mrn->doc_no = $numberPatternData['doc_no'];

            $mrn->document_number = $document_number;
            $mrn->document_date = $request->document_date;
            // $mrn->mrn_no = $document_number;
            // $mrn->mrn_date = $request->document_date;
            // $mrn->revision_date = $request->revision_date;
            $mrn->final_remarks = $request->remarks ?? null;

            $mrn->total_item_amount = 0.00;
            $mrn->total_discount = 0.00;
            $mrn->taxable_amount = 0.00;
            $mrn->total_taxes = 0.00;
            $mrn->total_after_tax_amount = 0.00;
            $mrn->expense_amount = 0.00;
            $mrn->total_amount = 0.00;
            $mrn->save();

            $vendorBillingAddress = $mrn->billingAddress ?? null;
            $vendorShippingAddress = $mrn->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $mrn->bill_address_details()->firstOrNew([
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
                $shippingAddress = $mrn->ship_address_details()->firstOrNew([
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
                $mrnItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $po_detail_id = null;
                    if(isset($component['supplier_inv_detail_id']) && $component['supplier_inv_detail_id']){
                        $supplierInvDetail =  PoItem::find($component['supplier_inv_detail_id']);
                        $po_detail_id = $supplierInvDetail->id ?? null;
                        if($supplierInvDetail){
                            $supplierInvDetail->grn_qty += floatval($component['accepted_qty']);
                            $supplierInvDetail->save();
                        }
                        if($supplierInvDetail->po_item_id){
                            $poDetail =  PoItem::find($supplierInvDetail->po_item_id);
                            $po_detail_id = $poDetail->id ?? null;
                            if($poDetail){
                                $poDetail->grn_qty += floatval($component['accepted_qty']);
                                $poDetail->save();
                            }
                        }
                    } else{
                        if(isset($component['po_detail_id']) && $component['po_detail_id']){
                            $inputQty = 0.00;
                            $balanceQty = 0.00;
                            $availableQty = 0.00;
                            $poDetail =  PoItem::find($component['po_detail_id']);
                            $po_detail_id = $poDetail->id ?? null;
                            if($poDetail){
                                $inputQty = ($request->accepted_qty ?? 0) + ($poDetail->grn_qty ?? 0.00);
                                $balanceQty = ($poDetail->order_qty - ($inputQty ?? 0.00));
                                if(($item->po_positive_tolerance > 0) || ($item->po_negative_tolerance > 0)){
                                    $positiveTolerenceAmt = $item->po_positive_tolerance ? (($item->po_positive_tolerance/$poDetail->order_qty)*100) : 0;
                                    $negativeTolerenceAmt = $item->po_negative_tolerance ? (($item->po_negative_tolerance/$poDetail->order_qty)*100) : 0;
                                    if(($balanceQty <= $negativeTolerenceAmt) && ($balanceQty >= 0)){
                                        $poDetail->grn_qty += floatval($component['accepted_qty']);
                                        $poDetail->short_close_qty += floatval($balanceQty);
                                        $poDetail->save();
                                    }
                                    if(($balanceQty < 0) && (-($positiveTolerenceAmt) >= $balanceQty)){
                                        $poDetail->grn_qty += floatval($component['accepted_qty']);
                                        $poDetail->save();
                                    }
                                } else{
                                    $poDetail->grn_qty += floatval($component['accepted_qty']);
                                    $poDetail->save();
                                }
                            }
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
                        $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
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
                    $mrnItemArr[] = [
                        'mrn_header_id' => $mrn->id,
                        'purchase_order_item_id' => $po_detail_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'order_qty' => floatval($component['order_qty']) ?? 0.00,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'rejected_qty' => floatval($component['rejected_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id ?? null,
                        'inventory_uom_code' => $inventory_uom_code ?? null,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'store_id' => $component['store_id'] ?? null,
                        'store_code' => $component['erp_store_code'] ?? null,
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

                foreach($mrnItemArr as &$mrnItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($mrnItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $mrnItem['taxable_amount'] - $headerDiscount; // after both discount
                    $mrnItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;

                    //Tax
                    if($isTax) {
                        $itemTax = 0;
                        $itemPrice = ($mrnItem['basic_value'] - $headerDiscount - $mrnItem['discount_amount']);
                        $shippingAddress = $mrn->shippingAddress;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;

                        $taxDetails = TaxHelper::calculateTax($mrnItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> shipping_country_id, $partyStateId ?? $request -> shipping_state_id, 'collection');

                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $mrnItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($mrnItem);

                foreach($mrnItemArr as $_key => $mrnItem) {
                    $itemPriceAterBothDis =  $mrnItem['basic_value'] - $mrnItem['discount_amount'] - $mrnItem['header_discount_amount'];
                    $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    $mrnDetail = new MrnDetail;
                    $mrnDetail->mrn_header_id = $mrnItem['mrn_header_id'];
                    $mrnDetail->purchase_order_item_id = $mrnItem['purchase_order_item_id'];
                    $mrnDetail->item_id = $mrnItem['item_id'];
                    $mrnDetail->item_code = $mrnItem['item_code'];
                    $mrnDetail->hsn_id = $mrnItem['hsn_id'];
                    $mrnDetail->hsn_code = $mrnItem['hsn_code'];
                    $mrnDetail->uom_id = $mrnItem['uom_id'];
                    $mrnDetail->uom_code = $mrnItem['uom_code'];
                    $mrnDetail->order_qty = $mrnItem['order_qty'];
                    $mrnDetail->accepted_qty = $mrnItem['accepted_qty'];
                    $mrnDetail->rejected_qty = $mrnItem['rejected_qty'];
                    $mrnDetail->inventory_uom_id = $mrnItem['inventory_uom_id'];
                    $mrnDetail->inventory_uom_code = $mrnItem['inventory_uom_code'];
                    $mrnDetail->inventory_uom_qty = $mrnItem['inventory_uom_qty'];
                    $mrnDetail->store_id = $mrnItem['store_id'];
                    $mrnDetail->store_code = $mrnItem['store_code'];
                    $mrnDetail->rate = $mrnItem['rate'];
                    $mrnDetail->basic_value = $mrnItem['basic_value'];
                    $mrnDetail->discount_amount = $mrnItem['discount_amount'];
                    $mrnDetail->header_discount_amount = $mrnItem['header_discount_amount'];
                    $mrnDetail->header_exp_amount = $itemHeaderExp;
                    $mrnDetail->tax_value = $mrnItem['tax_value'];
                    $mrnDetail->company_currency = $mrnItem['company_currency_id'];
                    $mrnDetail->group_currency = $mrnItem['group_currency_id'];
                    $mrnDetail->exchange_rate_to_group_currency = $mrnItem['group_currency_exchange_rate'];
                    $mrnDetail->remark = $mrnItem['remark'];
                    $mrnDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach($mrnDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $mrnAttr = new MrnAttribute;
                            $mrnAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $mrnAttr->mrn_header_id = $mrn->id;
                            $mrnAttr->mrn_detail_id = $mrnDetail->id;
                            $mrnAttr->item_attribute_id = $itemAttribute->id;
                            $mrnAttr->item_code = $component['item_code'] ?? null;
                            $mrnAttr->attr_name = $itemAttribute->attribute_group_id;
                            $mrnAttr->attr_value = $mrnAttrName ?? null;
                            $mrnAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = new MrnExtraAmount;
                                $ted->mrn_header_id = $mrn->id;
                                $ted->mrn_detail_id = $mrnDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->assesment_amount = $mrnItem['basic_value'];
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
                                $ted = new MrnExtraAmount;
                                $ted->mrn_header_id = $mrn->id;
                                $ted->mrn_detail_id = $mrnDetail->id;
                                $ted->ted_type = 'Tax';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $tax['t_d_id'] ?? null;
                                $ted->ted_name = $tax['t_type'] ?? null;
                                $ted->ted_code = $tax['t_type'] ?? null;
                                $ted->assesment_amount = $mrnItem['basic_value'] - $mrnItem['discount_amount'] - $mrnItem['header_discount_amount'];
                                $ted->ted_percentage = $tax['t_perc'] ?? 0.00;
                                $ted->ted_amount = $tax['t_value'] ?? 0.00;
                                $ted->applicability_type = $tax['applicability_type'] ?? 'Collection';
                                $ted->save();
                            }
                        }
                    }

                    #Save item store locations
                    if(isset($component['erp_store']) && $component['erp_store']) {
                        foreach($component['erp_store'] as $i => $val) {
                            $storeLocation = new MrnItemLocation();
                            $storeLocation->mrn_header_id = $mrn->id;
                            $storeLocation->mrn_detail_id = $mrnDetail->id;
                            $storeLocation->item_id = $mrnDetail->item_id;
                            $storeLocation->store_id = $val['erp_store_id'] ?? null;
                            $storeLocation->rack_id = $val['erp_rack_id'] ?? null;
                            $storeLocation->shelf_id = $val['erp_shelf_id'] ?? null;
                            $storeLocation->bin_id = $val['erp_bin_id'] ?? null;
                            $storeLocation->quantity = $val['store_qty'] ?? 0.00;
                            if(@$component['uom_id'] == @$item->uom_id) {
                                $storeLocation->inventory_uom_qty = $val['store_qty'] ?? 0.00;
                            } else {
                                $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                                if($alUom) {
                                    $storeLocation->inventory_uom_qty = intval($val['store_qty']) * $alUom->conversion_to_inventory;
                                }
                            }
                            $storeLocation->save();
                        }
                    } else{
                        $storeLocation = new MrnItemLocation();
                        $storeLocation->mrn_header_id = $mrn->id;
                        $storeLocation->mrn_detail_id = $mrnDetail->id;
                        $storeLocation->item_id = $mrnDetail->item_id;
                        $storeLocation->store_id = $mrnDetail->store_id;
                        $storeLocation->quantity = $mrnDetail->accepted_qty ?? 0.00;
                        if(@$component['uom_id'] == @$item->uom_id) {
                            $storeLocation->inventory_uom_qty = $mrnDetail->accepted_qty ?? 0.00;
                        } else {
                            $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                            if($alUom) {
                                $storeLocation->inventory_uom_qty = intval($mrnDetail->accepted_qty) * $alUom->conversion_to_inventory;
                            }
                        }
                        $storeLocation->save();
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $ted = new MrnExtraAmount;
                            $ted->mrn_header_id = $mrn->id;
                            $ted->mrn_detail_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
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
                            $ted = new MrnExtraAmount;
                            $ted->mrn_header_id = $mrn->id;
                            $ted->mrn_detail_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_e_id'] ?? null;
                            $ted->ted_name = $dis['e_name'];
                            $ted->assesment_amount = $totalAfterTax;
                            $ted->ted_percentage = $dis['e_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['e_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Update total in main header MRN*/
                $mrn->total_item_amount = $itemTotalValue ?? 0.00;
                $totalDiscValue = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($itemTotalValue < $totalDiscValue){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Negative value not allowed'
                    ], 422);
                }
                $mrn->total_discount = $totalDiscValue ?? 0.00;
                $mrn->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $mrn->total_taxes = $totalTax ?? 0.00;
                $mrn->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $totalTax) ?? 0.00;
                $mrn->expense_amount = $totalHeaderExpense ?? 0.00;
                $totalAmount = (($itemTotalValue - $totalDiscValue) + ($totalTax + $totalHeaderExpense)) ?? 0.00;
                $mrn->total_amount = $totalAmount ?? 0.00;
                $mrn->save();

            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($mrn->vendor->currency_id, $mrn->document_date);

            $mrn->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $mrn->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $mrn->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $mrn->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $mrn->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $mrn->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $mrn->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $mrn->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $mrn->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $mrn->save();

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $mrn->book_id;
                $docId = $mrn->id;
                $remarks = $mrn->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $mrn->approval_level;
                $revisionNumber = $mrn->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($mrn);
                $totalValue = $mrn->total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);

            }

            $mrn = MrnHeader::find($mrn->id);
            if ($request->document_status == 'submitted') {
                // $totalValue = $po->grand_total_amount ?? 0;
                // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                $mrn->document_status = $approveDocument['approvalStatus'] ?? $mrn->document_status;
            } else {
                $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            // if ($request->document_status == 'submitted') {
            //     $totalValue = $mrn->total_amount ?? 0;
            //     $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
            //     $mrn->document_status = $document_status;
            // } else {
            //     $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            // }
            /*MRN Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $mrn->uploadDocuments($request->file('attachment'), 'mrn', false);
            }
            $mrn->save();
            if($mrn){
                $invoiceLedger = self::maintainStockLedger($mrn);
            }

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $mrn,
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

        $mrn = MrnHeader::with([
            'vendor',
            'currency',
            'items',
            'book'
        ])
        ->findOrFail($id);

        $totalItemValue = $mrn->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($mrn->series_id,$mrn->document_status , $mrn->id, $mrn->total_amount, $mrn->approval_level, $mrn->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory($mrn->series_id, $mrn->id, $mrn->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mrn->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();

        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('procurement.material-receipt.view',
        [
            'mrn' => $mrn,
            'buttons' => $buttons,
            'erpStores' => $erpStores,
            'totalItemValue' => $totalItemValue,
            'docStatusClass' => $docStatusClass,
            'approvalHistory' => $approvalHistory,
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
        $serviceAlias = ConstantHelper::MRN_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $user = Helper::getAuthenticatedUser();
        // dd($user->toArray());
        $mrn = MrnHeader::with([
            'vendor',
            'currency',
            'items',
            'book'
        ])
        ->findOrFail($id);

        $totalItemValue = $mrn->items()->sum('basic_value');
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $revision_number = $mrn->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($mrn->book_id,$mrn->document_status , $mrn->id, $mrn->total_amount, $mrn->approval_level, $mrn->created_by ?? 0, $userType['type'], $revision_number);
        // dd($buttons);
        $revNo = $mrn->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $mrn->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($mrn->book_id, $mrn->id, $revNo, $mrn->total_amount);
        // dd($approvalHistory);
        $view = 'procurement.material-receipt.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $mrn->revision_number) {
            $mrn = $mrn->source;
            $mrn = MrnHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('mrn_header_id', $mrn->mrn_header_id)
                ->first();
            $view = 'procurement.material-receipt.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mrn->document_status] ?? '';
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        // $erpStores = ErpStore::where('organization_id', $user->organization_id)
        //     ->orderBy('id', 'ASC')
        //     ->get();
        // dd($revision_number);
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        return view($view, [
            'mrn' => $mrn,
            'books'=>$books,
            'buttons' => $buttons,
            'vendors' => $vendors,
            'locations'=>$locations,
            'docStatusClass' => $docStatusClass,
            'totalItemValue' => $totalItemValue,
            'revision_number' => $revision_number,
            'approvalHistory' => $approvalHistory,
            'services' => $servicesBooks['services'],
            'servicesBooks' => $servicesBooks,
        ]);
    }

    # Bom Update
    public function update(EditMaterialReceiptRequest $request, $id)
    {
        $mrn = MrnHeader::find($id);
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

            $currentStatus = $mrn->document_status;
            $actionType = $request->action_type;

            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'MrnHeader', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'MrnDetail', 'relation_column' => 'mrn_header_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'MrnAttribute', 'relation_column' => 'mrn_detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'MrnItemLocation', 'relation_column' => 'mrn_detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'MrnExtraAmount', 'relation_column' => 'mrn_detail_id']
                ];
                $a = Helper::documentAmendment($revisionData, $id);
            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedMrnItemIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            if (count($deletedData['deletedHeaderExpTedIds'])) {
                MrnExtraAmount::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
            }

            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                MrnExtraAmount::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemDiscTedIds'])) {
                MrnExtraAmount::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedMrnItemIds'])) {
                $mrnItems = MrnDetail::whereIn('id',$deletedData['deletedMrnItemIds'])->get();
                # all ted remove item level
                foreach($mrnItems as $mrnItem) {
                    $mrnItem->teds()->delete();
                    # all attr remove
                    $mrnItem->attributes()->delete();
                    $mrnItem->delete();
                }
            }

            # MRN Header save
            $totalTaxValue = 0.00;
            $mrn->gate_entry_no = $request->gate_entry_no ?? '';
            $mrn->gate_entry_date = $request->gate_entry_date ? date('Y-m-d', strtotime($request->gate_entry_date)) : '';
            $mrn->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $mrn->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $mrn->eway_bill_no = $request->eway_bill_no ?? '';
            $mrn->consignment_no = $request->consignment_no ?? '';
            $mrn->transporter_name = $request->transporter_name ?? '';
            $mrn->vehicle_no = $request->vehicle_no ?? '';
            $mrn->final_remarks = $request->remarks ?? '';
            $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $mrn->save();

            $vendorBillingAddress = $mrn->billingAddress ?? null;
            $vendorShippingAddress = $mrn->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $mrn->bill_address_details()->firstOrNew([
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
                $shippingAddress = $mrn->ship_address_details()->firstOrNew([
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
                $poItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $po_detail_id = null;
                    if(isset($component['po_detail_id']) && $component['po_detail_id']){
                        $poDetail =  PoItem::find($component['po_detail_id']);
                        $po_detail_id = $poDetail->id ?? null;
                        // if($poDetail){
                        //     $poDetail->grn_qty += floatval($component['accepted_qty']);
                        //     $poDetail->save();
                        // }
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
                        $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
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
                    $mrnItemArr[] = [
                        'mrn_header_id' => $mrn->id,
                        'purchase_order_item_id' => $po_detail_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'order_qty' => floatval($component['order_qty']) ?? 0.00,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'rejected_qty' => floatval($component['rejected_qty']) ?? 0.00,
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

                foreach($mrnItemArr as &$mrnItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($mrnItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $mrnItem['taxable_amount'] - $headerDiscount; // after both discount
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if($isTax) {
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($mrnItem['basic_value'] - $headerDiscount - $mrnItem['discount_amount']);
                        $shippingAddress = $mrn->shippingAddress;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($mrnItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'collection');

                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $mrnItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($mrnItem);

                foreach($mrnItemArr as $_key => $mrnItem) {
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];
                    $itemPriceAterBothDis =  $mrnItem['basic_value'] - $mrnItem['discount_amount'] - $mrnItem['header_discount_amount'];
                    $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    # Mrn Detail Save
                    $mrnDetail = MrnDetail::find($component['mrn_detail_id'] ?? null) ?? new MrnDetail;

                    if((isset($component['po_detail_id']) && $component['po_detail_id']) || (isset($mrnDetail->purchase_order_item_id) && $mrnDetail->purchase_order_item_id)) {
                        $poItem = PoItem::find($component['po_detail_id'] ?? $mrnDetail->purchase_order_item_id);
                        if(isset($poItem) && $poItem) {
                            if(isset($poItem->id) && $poItem->id) {
                                $orderQty = floatval($mrnDetail->accepted_qty);
                                $componentQty = floatval($component['accepted_qty']);
                                $qtyDifference = $poItem->order_qty - $orderQty + $componentQty;
                                if($qtyDifference) {
                                    $poItem->grn_qty = $qtyDifference;
                                }
                            } else {
                                $poItem->order_qty += $component['qty'];
                            }
                            $poItem->save();
                        }
                    }

                    $mrnDetail->mrn_header_id = $mrnItem['mrn_header_id'];
                    $mrnDetail->purchase_order_item_id = $mrnItem['purchase_order_item_id'];
                    $mrnDetail->item_id = $mrnItem['item_id'];
                    $mrnDetail->item_code = $mrnItem['item_code'];
                    $mrnDetail->hsn_id = $mrnItem['hsn_id'];
                    $mrnDetail->hsn_code = $mrnItem['hsn_code'];
                    $mrnDetail->uom_id = $mrnItem['uom_id'];
                    $mrnDetail->uom_code = $mrnItem['uom_code'];
                    $mrnDetail->accepted_qty = $mrnItem['accepted_qty'];
                    $mrnDetail->inventory_uom_id = $mrnItem['inventory_uom_id'];
                    $mrnDetail->inventory_uom_code = $mrnItem['inventory_uom_code'];
                    $mrnDetail->inventory_uom_qty = $mrnItem['inventory_uom_qty'];
                    $mrnDetail->rate = $mrnItem['rate'];
                    $mrnDetail->basic_value = $mrnItem['basic_value'];
                    $mrnDetail->discount_amount = $mrnItem['discount_amount'];
                    $mrnDetail->header_discount_amount = $mrnItem['header_discount_amount'];
                    $mrnDetail->tax_value = $mrnItem['tax_value'];
                    $mrnDetail->header_exp_amount = $itemHeaderExp;
                    $mrnDetail->company_currency = $mrnItem['company_currency_id'];
                    $mrnDetail->group_currency = $mrnItem['group_currency_id'];
                    $mrnDetail->exchange_rate_to_group_currency = $mrnItem['group_currency_exchange_rate'];
                    $mrnDetail->remark = $mrnItem['remark'];
                    $mrnDetail->save();

                    #Save component Attr
                    foreach($mrnDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $mrnAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                        $mrnAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $mrnAttr = MrnAttribute::find($mrnAttrId) ?? new MrnAttribute;
                        $mrnAttr->mrn_header_id = $mrn->id;
                        $mrnAttr->mrn_detail_id = $mrnDetail->id;
                        $mrnAttr->item_attribute_id = $itemAttribute->id;
                        $mrnAttr->item_code = $component['item_code'] ?? null;
                        $mrnAttr->attr_name = $itemAttribute->attribute_group_id;
                        $mrnAttr->attr_value = $mrnAttrName ?? null;
                        $mrnAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = MrnExtraAmount::find($dis['id'] ?? null) ?? new MrnExtraAmount;
                                $ted->mrn_header_id = $mrn->id;
                                $ted->mrn_detail_id = $mrnDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->ted_code = $dis['dis_name'];
                                $ted->assesment_amount = $mrnItem['basic_value'];
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
                            $mrnAmountId = null;
                            $ted = MrnExtraAmount::find(@$tax['id']) ?? new MrnExtraAmount;
                            $ted->mrn_header_id = $mrn->id;
                            $ted->mrn_detail_id = $mrnDetail->id;
                            $ted->ted_type = 'Tax';
                            $ted->ted_level = 'D';
                            $ted->ted_id = $tax['t_d_id'] ?? null;
                            $ted->ted_name = $tax['t_type'] ?? null;
                            $ted->ted_code = $tax['t_type'] ?? null;
                            $ted->assesment_amount = $mrnItem['basic_value'] - $mrnItem['discount_amount'] - $mrnItem['header_discount_amount'];
                            $ted->ted_percentage = $tax['t_perc'] ?? 0.00;
                            $ted->ted_amount = $tax['t_value'] ?? 0.00;
                            $ted->applicability_type = $tax['applicability_type'] ?? 'Collection';
                            $ted->save();
                        }
                    }

                    #Save item store locations
                    if (isset($component['erp_store']) && $component['erp_store']) {
                        foreach($component['erp_store'] as $val) {
                            $storeLocation = MrnItemLocation::find(@$val['id']) ?? new MrnItemLocation;
                            $storeLocation->mrn_header_id = $mrn->id;
                            $storeLocation->mrn_detail_id = $mrnDetail->id;
                            $storeLocation->item_id = $mrnDetail->item_id;
                            $storeLocation->store_id = $val['erp_store_id'] ?? null;
                            $storeLocation->rack_id = $val['erp_rack_id'] ?? null;
                            $storeLocation->shelf_id = $val['erp_shelf_id'] ?? null;
                            $storeLocation->bin_id = $val['erp_bin_id'] ?? null;
                            $storeLocation->quantity = $val['store_qty'] ?? 0.00;
                            if(@$component['uom_id'] == @$item->uom_id) {
                                $storeLocation->inventory_uom_qty = $val['store_qty'] ?? 0.00;
                            } else {
                                $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                                if($alUom) {
                                    $storeLocation->inventory_uom_qty = intval($val['store_qty']) * $alUom->conversion_to_inventory;
                                }
                            }
                            $storeLocation->save();
                        }
                    } else{
                        $storeLocation = MrnItemLocation::where('mrn_header_id', $mrn->id)
                            ->where('mrn_detail_id', $mrnDetail->id)
                            ->where('store_id', $mrnDetail->store_id)
                            ->first();
                        if(!$storeLocation){
                            $storeLocation = new MrnItemLocation;
                        }
                        $storeLocation->mrn_header_id = $mrn->id;
                        $storeLocation->mrn_detail_id = $mrnDetail->id;
                        $storeLocation->item_id = $mrnDetail->item_id;
                        $storeLocation->store_id = $mrnDetail->store_id;
                        $storeLocation->quantity = $mrnDetail->accepted_qty ?? 0.00;
                        if(@$component['uom_id'] == @$item->uom_id) {
                            $storeLocation->inventory_uom_qty = $mrnDetail->accepted_qty ?? 0.00;
                        } else {
                            $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                            if($alUom) {
                                $storeLocation->inventory_uom_qty = intval($mrnDetail->accepted_qty) * $alUom->conversion_to_inventory;
                            }
                        }
                        $storeLocation->save();
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $mrnAmountId = @$dis['d_id'] ?? null;
                            $ted = MrnExtraAmount::find($mrnAmountId) ?? new MrnExtraAmount;
                            $ted->mrn_header_id = $mrn->id;
                            $ted->mrn_detail_id = null;
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
                            $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                            $mrnAmountId = @$dis['e_id'] ?? null;
                            $ted = MrnExtraAmount::find($mrnAmountId) ?? new MrnExtraAmount;
                            $ted->mrn_header_id = $mrn->id;
                            $ted->mrn_detail_id = null;
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

                /*Update total in main header MRN*/
                $mrn->total_item_amount = $itemTotalValue ?? 0.00;
                $totalDiscValue = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($itemTotalValue < $totalDiscValue){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Negative value not allowed'
                    ], 422);
                }
                $mrn->total_discount = $totalDiscValue ?? 0.00;
                $mrn->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $mrn->total_taxes = $totalTax ?? 0.00;
                $mrn->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $totalTax) ?? 0.00;
                $mrn->expense_amount = $totalHeaderExpense ?? 0.00;
                $totalAmount = (($itemTotalValue - $totalDiscValue) + ($totalTax + $totalHeaderExpense)) ?? 0.00;
                $mrn->total_amount = $totalAmount ?? 0.00;
                $mrn->save();
            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($mrn->vendor->currency_id, $mrn->document_date);

            $mrn->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $mrn->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $mrn->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $mrn->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $mrn->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $mrn->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $mrn->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $mrn->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $mrn->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $mrn->save();

            /*Create document submit log*/
            $bookId = $mrn->book_id;
            $docId = $mrn->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $mrn->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $mrn->approval_level;
            $modelName = get_class($mrn);
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                //*amendmemnt document log*/
                $revisionNumber = $mrn->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $mrn->total_amount, $modelName);
                $mrn->revision_number = $revisionNumber;
                $mrn->approval_level = 1;
                $mrn->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ?? $mrn->document_status;
                // $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                // if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                //     $totalValue = $mrn->grand_total_amount ?? 0;
                //     $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                // }
                // if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                //     $actionType = 'submit';
                //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                // }
                $mrn->document_status = $amendAfterStatus;
                $mrn->save();

            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $mrn->revision_number ?? 0;
                    $actionType = 'submit';
                    $totalValue = $mrn->total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);

                    // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    $document_status = $approveDocument['approvalStatus'] ?? $mrn->document_status;
                    $mrn->document_status = $document_status;
                } else {
                    $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }

            /*MRN Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $mrn->uploadDocuments($request->file('attachment'), 'mrn', false);
            }

            $mrn->save();
            if($mrn){
                $invoiceLedger = self::maintainStockLedger($mrn);
            }
            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $mrn,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    // Add Item Row
    public function addItemRow(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $item = json_decode($request->item,true) ?? [];
        // dd($item);
        $componentItem = json_decode($request->component_item,true) ?? [];
        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                // return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'ASC')
            ->get();
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.material-receipt.partials.item-row',compact('rowCount', 'erpStores'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $attributeGroups = AttributeGroup::with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $mrnDetailId = $request->mrn_detail_id ?? null;
        $itemAttIds = [];
        if($mrnDetailId) {
            $mrnDetail = MrnDetail::find($mrnDetailId);
            if($mrnDetail) {
                $itemAttIds = $mrnDetail->attributes()->pluck('item_attribute_id')->toArray();
            }
        }
        $itemAttributes = collect();
        if(count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
        } else {
            $itemAttributes = $item?->itemAttributes;
        }
        $html = view('procurement.material-receipt.partials.comp-attribute',compact('item','attributeGroups','rowCount','selectedAttr'))->render();
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
        return response()->json(['data' => ['attr' => $item?->itemAttributes->count() ?? 0, 'html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
    }

    # Add discount row
    public function addDiscountRow(Request $request)
    {
        $tblRowCount = intval($request->tbl_row_count) ? intval($request->tbl_row_count) + 1 : 1;
        $rowCount = intval($request->row_count);
        $disName = $request->dis_name;
        $disPerc = $request->dis_percentage;
        $disAmount = $request->dis_amount;
        $html = view('procurement.material-receipt.partials.add-disc-row',compact('tblRowCount','rowCount','disName','disAmount','disPerc'))->render();
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
            $upToCountry = $request->input('party_country_id', $companyCountryId) ?? 0;
            $upToState = $request->input('party_state_id', $companyStateId) ?? 0;
        } else {
            $fromCountry = $request->input('party_country_id', $companyCountryId) ?? 0;
            $fromState = $request->input('party_state_id', $companyStateId) ?? 0;
            $upToCountry = $companyCountryId;
            $upToState = $companyStateId;
        }
        try {
            $taxDetails = TaxHelper::calculateTax( $hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = floatval($request->price) ?? 0;
            // dd($hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType);
            $html = view('procurement.material-receipt.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
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
            $html = view('procurement.material-receipt.partials.edit-address-modal',compact('addresses','selectedAddress'))->render();
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
        $itemStoreData = json_decode($request->itemStoreData,200) ?? [];
        $itemId = $request->item_id;
        $storeId = null;
        $rackId = null;
        $shelfId = null;
        $binId = null;
        $quantity = $request->qty;
        $headerId = $request->headerId;
        $detailId = $request->detailId;
        $totalStockData = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttr,  $storeId, $rackId, $shelfId, $binId);
        // $checkStockStatus = InventoryHelper::checkItemStockStatus($headerId, $detailId, $itemId, $selectedAttr, $quantity, $storeId, $rackId, $shelfId, $binId, 'mrn', 'receipt');
        // $checkApprovedQuantity = InventoryHelper::checkItemStockQuantity($headerId, $detailId, $itemId, $selectedAttr, $quantity, $storeId, $rackId, $shelfId, $binId, 'mrn', 'receipt');
        // dd($checkStockStatus);
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
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);
        $html = view('procurement.material-receipt.partials.comp-item-detail',compact('item','purchaseOrder', 'selectedAttr','remark','uomName','qty', 'totalStockData', 'headerId', 'detailId','specifications'))->render();
        return response()->json(['data' => ['html' => $html, 'totalStockData' => $totalStockData], 'status' => 200, 'message' => 'fetched.']);
    }

    public function logs(Request $request, string $id)
    {
        $user = Helper::getAuthenticatedUser();

        $revisionNo = $request->revision_number ?? 0;
        $mrnHeader = MrnHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        $mrn = MrnHeaderHistory::with(['mrn'])
            ->where('revision_number', $revisionNo)
            ->where('mrn_header_id', $id)
            ->first();
        $totalItemValue = $mrn->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($mrn->series_id,$mrn->document_status , $mrn->id, $mrn->total_amount, $mrn->approval_level, $mrn->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory(@$mrn->mrn->series_id, @$mrn->mrn->id, @$mrn->mrn->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[@$mrn->mrn->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'DESC')
            ->get();
        $mrnRevisionNumbers = MrnHeaderHistory::where('mrn_header_id', $id)->get();
        return view('procurement.material-receipt.logs', [
            'mrn' => $mrn,
            'buttons' => $buttons,
            'erpStores'=>$erpStores,
            'currentRevisionNumber'=>$revisionNo,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revisionNumbers' => $revisionNumbers,
            'mrnRevisionNumbers' => $mrnRevisionNumbers,
        ]);
    }

    public function getStockDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $itemStoreData = json_decode($request->itemStoreData,200) ?? [];
        $itemId = $request->item_id;
        dd([$itemId, $selectedAttr, $itemStoreData]);
        InventoryHelper::isExistInventoryAndStock($itemId, $selectedAttr,  $itemStoreData);
        // dd($request->item_id);
        $item = Item::find($request->item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = intval($request->qty) ?? 0;
        // $uomName = '';
        // dd($item);
        $uomName = $item->uom->name ?? 'NA';
        if($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
            // $uomName = $alUom->uom->name ?? 'NA';
        }
        $remark = $request->remark ?? null;
        $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);
        // dd($purchaseOrder);
        $html = view('procurement.material-receipt.partials.comp-item-detail',compact('item','purchaseOrder', 'selectedAttr','remark','uomName','qty'))->render();
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
        $mrn = MrnHeader::with(['vendor', 'currency', 'items', 'book', 'expenses'])
            ->findOrFail($id);


        $shippingAddress = $mrn->shippingAddress;
        $buyerAddress = $mrn?->store_location?->address;

        $totalItemValue = $mrn->total_item_amount ?? 0.00;
        $totalDiscount = $mrn->total_discount ?? 0.00;
        $totalTaxes = $mrn->total_taxes ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $mrn->expense_amount ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($mrn->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory

        $pdf = PDF::loadView(

            // return view(
            'pdf.mrn',
            [
                'mrn' => $mrn,
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
                'imagePath' => $imagePath
            ]
        );

        return $pdf->stream('Meterial-Receipt.pdf');
    }

    # Submit Amendment
    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Header History
            // dd($id);
            $mrnHeader = MrnHeader::find($id);
            if(!$mrnHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $mrnHeaderData = $mrnHeader->toArray();
            unset($mrnHeaderData['id']); // You might want to remove the primary key, 'id'
            $mrnHeaderData['mrn_header_id'] = $mrnHeader->id;
            $headerHistory = MrnHeaderHistory::create($mrnHeaderData);
            $headerHistoryId = $headerHistory->id;

            // Detail History
            $mrnDetails = MrnDetail::where('mrn_header_id', $mrnHeader->id)->get();
            if(!empty($mrnDetails)){
                foreach($mrnDetails as $key => $detail){
                    $mrnDetailData = $detail->toArray();
                    unset($mrnDetailData['id']); // You might want to remove the primary key, 'id'
                    $mrnDetailData['mrn_detail_id'] = $detail->id;
                    $mrnDetailData['mrn_header_history_id'] = $headerHistoryId;
                    $detailHistory = MrnDetailHistory::create($mrnDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $mrnAttributes = MrnAttribute::where('mrn_header_id', $mrnHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->get();
                    if(!empty($mrnAttributes)){
                        foreach($mrnAttributes as $key1 => $attribute){
                            $mrnAttributeData = $attribute->toArray();
                            unset($mrnAttributeData['id']); // You might want to remove the primary key, 'id'
                            $mrnAttributeData['mrn_attribute_id'] = $attribute->id;
                            $mrnAttributeData['mrn_header_history_id'] = $headerHistoryId;
                            $mrnAttributeData['mrn_detail_history_id'] = $detailHistoryId;
                            $attributeHistory = MrnAttributeHistory::create($mrnAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Item Locations History
                    $itemLocations = MrnItemLocation::where('mrn_header_id', $mrnHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->get();
                    if(!empty($itemLocations)){
                        foreach($itemLocations as $key2 => $location){
                            $itemLocationData = $location->toArray();
                            unset($itemLocationData['id']); // You might want to remove the primary key, 'id'
                            $itemLocationData['mrn_item_location_id'] = $location->id;
                            $itemLocationData['mrn_header_history_id'] = $headerHistoryId;
                            $itemLocationData['mrn_detail_history_id'] = $detailHistoryId;
                            $itemLocationHistory = MrnItemLocationHistory::create($itemLocationData);
                            $itemLocationHistoryId = $itemLocationHistory->id;
                        }
                    }

                    // Extra Amount Item History
                    $itemExtraAmounts = MrnExtraAmount::where('mrn_header_id', $mrnHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->where('ted_level', '=', 'D')
                        ->get();

                    if(!empty($itemExtraAmounts)){
                        foreach($itemExtraAmounts as $key4 => $extraAmount){
                            $extraAmountData = $extraAmount->toArray();
                            unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                            $extraAmountData['mrn_extra_amount_id'] = $extraAmount->id;
                            $extraAmountData['mrn_header_history_id'] = $headerHistoryId;
                            $extraAmountData['mrn_detail_history_id'] = $detailHistoryId;
                            $extraAmountDataHistory = MrnExtraAmountHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // Extra Amount Header History
            $mrnExtraAmounts = MrnExtraAmount::where('mrn_header_id', $mrnHeader->id)
                ->where('ted_level', '=', 'H')
                ->get();

            if(!empty($mrnExtraAmounts)){
                foreach($mrnExtraAmounts as $key4 => $extraAmount){
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $extraAmountData['mrn_extra_amount_id'] = $extraAmount->id;
                    $extraAmountData['mrn_header_history_id'] = $headerHistoryId;
                    $extraAmountDataHistory = MrnExtraAmountHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000,99999);

            $revisionNumber = "MRN".$randNo;
            $mrnHeader->revision_number += 1;
            $mrnHeader->status = "draft";
            $mrnHeader->document_status = "draft";
            $mrnHeader->save();

            /*Create document submit log*/
            if ($mrnHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $mrnHeader->series_id;
                $docId = $mrnHeader->id;
                $remarks = $mrnHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $mrnHeader->approval_level;
                $revisionNumber = $mrnHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $mrnHeader,
                'status' => 200
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Amendment Submit Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while amendement.',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function validateQuantity(Request $request)
    {
        $errorMessage = '';
        $item = Item::find($request->item_id);
        if(!$item){
            return response() -> json([
                'data' => array(
                    'error_message' => 'Item not found.'
                )
            ]);
        }
        if(isset($request->mrnDetailId) && $request->mrnDetailId){
            $mrnDetail = MrnDetail::find($request->mrnDetailId);
            if(!$mrnDetail){
                return response() -> json([
                    'data' => array(
                        'error_message' => 'Mrn detail not found.'
                    )
                ]);
            }
            if(($mrnDetail->purchase_bill_qty) && ($mrnDetail->purchase_bill_qty > $request->qty)){
                return response() -> json([
                    'data' => array(
                        'error_message' => "Accepted qty can not be less than purchase bill quantity(which is : ".$mrnDetail->purchase_bill_qty.") as it has been already used there for this this item."
                    )
                ]);
            }
            $poDetail = PoItem::find($request->poDetailId);
            if($poDetail){
                $availableQty = 0.00;
                $actualQtyDifference = ($poDetail->order_qty - $poDetail->grn_qty);
                $upcomingQtyDifference = ($poDetail->order_qty - $request->qty);
                if($actualQtyDifference < $request->qty){
                    $availableQty = $actualQtyDifference;
                    return response() -> json([
                        'data' => array(
                            'error_message' => "You can add ".$availableQty." quantity as ".$poDetail->grn_qty." quantity already used in po. and po quantity is ".$poDetail->order_qty."."
                        )
                    ]);
                }
            }
        } else{
            $poDetail = PoItem::find($request->poDetailId);
            if($poDetail){
                $availableQty = 0.00;
                $inputQty = ($request->qty ?? 0) + ($poDetail->grn_qty ?? 0.00);
                $balanceQty = ($poDetail->order_qty - ($inputQty ?? 0.00));
                if($item->po_positive_tolerance > 0){
                    $positiveTolerenceAmt = $item->po_positive_tolerance ? (($item->po_positive_tolerance/$poDetail->order_qty)*100) : 0;
                    if($request->qty > ($poDetail->order_qty + $positiveTolerenceAmt)){
                        $errorMessage = "Accepted quantity can not be greater than po tolerence.";
                    }
                } else{
                    $errorMessage = "You can add ".$balanceQty." quantity as ".$poDetail->grn_qty." quantity already used in po. and po quantity is ".$poDetail->order_qty.".";
                }

                return response() -> json([
                    'data' => array(
                        'error_message' => $errorMessage
                    )
                ]);

                // $actualQtyDifference = ($poDetail->order_qty - $poDetail->grn_qty);
                // if($actualQtyDifference < $request->qty){
                //     $availableQty = $actualQtyDifference;
                //     return response() -> json([
                //         'data' => array(
                //             'error_message' => "You can add ".$availableQty." quantity as ".$poDetail->grn_qty." quantity already used in po. and po quantity is ".$poDetail->order_qty."."
                //         )
                //     ]);
                // }
            }
        }
        return response()->json(['data' => ['quantity' => $request->qty], 'status' => 200, 'message' => 'fetched']);
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
        // dd($headerBookId);
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        // dd($applicableBookIds);

        $poItemIds = [];
        $poItems = PoItem::select(
                'erp_po_items.*',
                'erp_purchase_orders.id as po_id',
                'erp_purchase_orders.vendor_id as vendor_id',
                'erp_purchase_orders.book_id as book_id'
            )
            ->leftJoin('erp_purchase_orders', 'erp_purchase_orders.id', 'erp_po_items.purchase_order_id')
            ->whereIn('erp_purchase_orders.book_id', $applicableBookIds)
            ->whereRaw('(order_qty > (grn_qty + short_close_qty))')
            ->whereHas('item', function($item){
                $item->where('type', 'Goods');
            })
            ->get();

        foreach ($poItems as $poItem) {
            $checkInvoiceRequired = Vendor::whereHas('supplier_books')->find($poItem->vendor_id);
            if($checkInvoiceRequired) {
                $siItem = PoItem::where('po_item_id', $poItem->id)
                    ->whereHas('po', function($po){
                        $po->where('type', 'supplier-invoice');
                    })
                    ->whereRaw('(order_qty > (grn_qty + short_close_qty))')
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
        // dd($applicableBookIds);
        $poItems = PoItem::with('attributes')->whereIn('id',$poItemIds)
            ->where(function ($query) use ($seriesId, $docNumber, $itemId, $vendorId) {
            // Ensure item exists
            $query->whereHas('item', function($item){
                $item->where('type', 'Goods');
            });

            // Check POs
            $query->whereHas('po', function ($po) use ($seriesId, $docNumber, $vendorId) {
                // Filter by book_id (headerBookId)
                // Filter by series ID
                $po->withDefaultGroupCompanyOrg();
                $po->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
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
            $query->whereRaw('(order_qty > (grn_qty + short_close_qty))');
        })->get();

        // dd($poItems);
        $html = view('procurement.material-receipt.partials.po-item-list', ['poItems' => $poItems])->render();
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
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time mrn create from one PO."]);
        }

        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'ASC')
            ->get();

        $pos = PurchaseOrder::whereIn('id', $uniquePoIds)->get();
        $discounts = collect();
        $expenses = collect();

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
        if(count($vendorId) && count($vendorId) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "You can not selected multiple vendor of PO item at time."]);
        } else {
            $vendorId = $vendorId[0];
            $vendor = Vendor::find($vendorId);
            $vendor->billing = $vendor->latestBillingAddress();
            $vendor->shipping = $vendor->latestShippingAddress();
            $vendor->currency = $vendor->currency;
            $vendor->paymentTerm = $vendor->paymentTerm;
        }
        $html = view('procurement.material-receipt.partials.po-item-row',
        [
            'poItems' => $poItems,
            'erpStores' => $erpStores,
        ])
        ->render();

        return response()->json(['data' => ['pos' => $html, 'vendor' => $vendor,'finalDiscounts' => $finalDiscounts,'finalExpenses' => $finalExpenses], 'status' => 200, 'message' => "fetched!"]);
    }

    // Maintain Stock Ledger
    private static function maintainStockLedger($mrn)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $mrn->items->pluck('id')->toArray();
        InventoryHelper::settlementOfInventoryAndStock($mrn->id, $detailIds, ConstantHelper::MRN_SERVICE_ALIAS, $mrn->document_status);

        return true;
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

    public function postMrn(Request $request)
    {
        try {
            // dd($request->all());
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
            $mrn = MrnHeader::find($request->id);
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

}
