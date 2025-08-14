<?php

namespace App\Http\Controllers\Supplier;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\Helper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PoRequest;
use App\Models\Address;
use App\Models\AttributeGroup;
use App\Models\Currency;
use App\Models\ErpAddress;
use App\Models\Hsn;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\PaymentTerm;
use App\Models\PiItem;
use App\Models\PoItem;
use App\Models\PoItemAttribute;
use App\Models\PoItemDelivery;
use App\Models\PoTerm;
use App\Models\PurchaseIndent;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderMedia;
use App\Models\PurchaseOrderTed;
use App\Models\TermsAndCondition;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\Book;
use Auth;
use DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;

class SiController extends Controller
{
    protected $type;

    public function __construct(Request $request)
    {
        // view()->share('type', request()->route('tyspe'));
        if($request->route('type') == 'purchase-order')
        {
            $this->type = 'po';
        } else {
            $this->type = 'supplier-invoice';
        }
    }

    # Po List
    public function index(Request $request)
    {
        $type = $this->type;
        if (request()->ajax()) {
            $vendor_id = $request->cookie('vendor_id');
            $pos = PurchaseOrder::ofType($type)
                    ->withDefaultGroupCompanyOrg()
                    ->withDraftListingLogic()
                    ->where('vendor_id',$vendor_id)
                    ->latest()
                    ->with('vendor')
                    ->get();

            return DataTables::of($pos)
            ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                $route = route('supplier.invoice.edit', ['id' => $row->id]);
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
                return $row->book ? $row->book?->book_code : 'N/A';
            })
            ->addColumn('department', function ($row) {
                return $row->department ? $row->department?->name : 'N/A';
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
            ->addColumn('vendor_name', function ($row) {
                return $row->vendor?->company_name ?? 'NA';
            })
            ->addColumn('components', function ($row) {
                return $row->po_items->count();
            })
            ->addColumn('company_name', function ($row) {
                return $row?->organization?->name ?? 'NA';
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
                return number_format($row->grand_total_amount,2);
            })
            ->rawColumns(['document_status'])
            ->make(true);
        }
        return view('supplier.si.index');
    }

    # Po create
    public function create(Request $request)
    {
        $termsAndConditions = TermsAndCondition::withDefaultGroupCompanyOrg()
                            ->where('status',ConstantHelper::ACTIVE)->get();
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Add New';
        $short_title = '';
        $reference_from_title = '';
        $serviceAlias = ConstantHelper::SUPPLIER_INVOICE_SERVICE_ALIAS;
        $title = 'Supplier Invoice';
        $short_title = 'SI';
        $reference_from_title = 'Purchase Order';

        $user = Helper::getAuthenticatedUser();
        $vendor_id = $request->cookie('vendor_id');
        $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])->find($vendor_id);
        $bookIds = [];
        if($vendor) {
            $bookIds = $vendor?->supplier_books()?->pluck('book_id')?->toArray();
        }
        // Vendor Book Code
        $books = Book::whereIn('id', $bookIds)->get();
        // $books = Helper::getBookSeries($serviceAlias)->get();
        
        $shipping = $vendor->addresses()->where(function($query) {
                        $query->where('type', 'shipping')->orWhere('type', 'both');
                    })->latest()->first();
        $billing = $vendor->addresses()->where(function($query) {
                    $query->where('type', 'billing')->orWhere('type', 'both');
                })->latest()->first();
        // $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        return view('supplier.si.create', [
            'books'=> $books,
            'termsAndConditions' => $termsAndConditions,
            'title' => $title, 
            'menu' => $menu,
            'menu_url' => $menu_url, 
            'sub_menu' => $sub_menu,
            'short_title' => $short_title,
            'reference_from_title' => $reference_from_title,
            'vendor' => $vendor,
            'shipping' => $shipping,
            'billing' => $billing
            // 'locations' => $locations
        ]);
    }

    # Add item row
    public function addItemRow(Request $request)
    {
        $item = json_decode($request->item,true) ?? [];
        $componentItem = json_decode($request->component_item,true) ?? [];
        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('supplier.si.partials.item-row',compact('rowCount'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $isPi = intval($request->isPi) ?? 0;
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $poItemId = $request->po_item_id ?? null;
        $itemAttIds = [];
        if($poItemId) {
            $poItem = PoItem::find($poItemId);
            if($poItem) {
                $itemAttIds = $poItem->attributes()->pluck('item_attribute_id')->toArray();
            }
        }
        $itemAttributes = collect();
        if(count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
            if(count($itemAttributes) < 1) {
                $itemAttributes = $item?->itemAttributes;
            }
        } else {
            $itemAttributes = $item?->itemAttributes;
        }
        $html = view('supplier.si.partials.comp-attribute',compact('item','rowCount','selectedAttr','isPi','itemAttributes'))->render();
        $hiddenHtml = '';
        if($item) {
            foreach ($itemAttributes as $attribute) {
                    $selected = '';
                    foreach ($attribute->attributes() as $value) {
                        if (in_array($value->id, $selectedAttr)) {
                            $selected = $value->id;
                        }
                    }
                $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
            }
        }
        return response()->json(['data' => ['attr' => $item?->itemAttributes->count() ?? 0,'html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
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
            $html = view('supplier.si.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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

    # Purchase Order store
    public function store(PoRequest $request)
    {
        DB::beginTransaction();
        try {
            $type = $this->type;
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

            # Bom Header save
            
            $po = new PurchaseOrder;
            $po->type = $type;
            $po->organization_id = $organization->id;
            $po->group_id = $organization->group_id;
            $po->company_id = $organization->company_id;
            $po->book_id = $request->book_id;
            $po->book_code = $request->book_code;

            $document_number = $request->document_number ?? null;
            
            /**/
            $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
            $regeneratedDocExist = PurchaseOrder::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }

            $po->doc_number_type = $numberPatternData['type'];
            $po->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $po->doc_prefix = $numberPatternData['prefix'];
            $po->doc_suffix = $numberPatternData['suffix'];
            $po->doc_no = $numberPatternData['doc_no'];
            /**/

            $po->document_number = $document_number;
            $po->document_date = $request->document_date;
            // $po->revision_number = $request->revision_number;
            // $po->revision_date = $request->revision_date;
            $po->reference_number = $request->reference_number;
            $po->vendor_id = $request->vendor_id;
            $po->vendor_code = $request->vendor_code;
            $po->billing_address = $request->billing_id;
            $po->shipping_address = $request->shipping_id;
            $po->currency_id = $request->currency_id;
            // $po->currency_code = $request->currency_code;
            $po->document_status = $request->document_status;
            // $po->approval_level = $request->approval_level;
            $po->remarks = $request->remarks ?? null;
            $po->payment_term_id = $request->payment_term_id;
            // $po->payment_term_code = $request->payment_term_code;
            $po->total_item_value = 0.00;
            $po->total_discount_value = 0.00;
            $po->total_tax_value = 0.00;
            $po->total_expense_value = 0.00;
            $po->save();
            
            $vendorBillingAddress = $po->bill_address ?? null;
            $vendorShippingAddress = $po->ship_address ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $po->bill_address_details()->firstOrNew([
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
                $shippingAddress = $po->ship_address_details()->firstOrNew([
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
                    $po_item_id = null;
                    $si_po_item_id = null;
                    if(isset($component['pi_item_id']) && $component['pi_item_id']) {
                        $piItem = PiItem::find($component['pi_item_id']);
                        $po_item_id = $piItem->id ?? null; 
                        if($piItem) {
                            $piItem->order_qty = $piItem->order_qty + floatval($component['qty']);
                            $piItem->save();
                        }
                    }
                    if(isset($component['si_po_item_id']) && $component['si_po_item_id']) {
                        $si_po_item_id = $component['si_po_item_id'];
                        $si_po_item = PoItem::find($si_po_item_id);
                        if($si_po_item) {
                            $si_po_item->invoice_quantity = $si_po_item->invoice_quantity + floatval($component['qty']);
                            $si_po_item->save();
                        }

                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
                        $inventory_uom_qty = floatval($component['qty']) ?? 0.00 ;
                    } else {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
                        $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $inventory_uom_qty = floatval($component['qty']) * $alUom->conversion_to_inventory;
                        }
                    }

                    $itemValue = floatval($component['qty']) * floatval($component['rate']);
                    $itemDiscount = floatval($component['discount_amount']) ?? 0.00;
                    
                    $itemTotalValue += $itemValue;
                    $itemTotalDiscount += $itemDiscount;
                    $itemValueAfterDiscount = $itemValue - $itemDiscount;
                    $totalValueAfterDiscount += $itemValueAfterDiscount;
                    $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                    $unit = Unit::find($component['uom_id']);
                    $poItemArr[] = [
                        'purchase_order_id' => $po->id,
                        'pi_item_id' => $po_item_id,
                        'po_item_id' => $si_po_item_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' => $component['uom_id'] ?? null,
                        'uom_code' => $unit?->name ?? null,
                        'order_qty' => floatval($component['qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id,
                        'inventory_uom_code' => $inventory_uom_code,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'item_discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'expense_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'company_currency_id' => @$component['company_currency_id'] ?? 0.00,
                        'company_currency_exchange_rate' => @$component['company_currency_exchange_rate'] ?? 0.00,
                        'group_currency_id' => @$component['group_currency_id'] ?? 0.00,
                        'group_currency_exchange_rate' => @$component['group_currency_exchange_rate'] ?? 0.00,
                        'remarks' => $component['remark'] ?? null,
                        'value_after_discount' => $itemValueAfterDiscount,
                        'item_value' => $itemValue
                    ];
                }

                $isTax = false;
                if(isset($parameters['tax_required']) && !empty($parameters['tax_required']))
                {
                    if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                        $isTax = true;
                    }
                }

                foreach($poItemArr as &$poItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($poItem['value_after_discount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $poItem['value_after_discount'] - $headerDiscount; // after both discount
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    //Tax

                    if($isTax) {
                        $itemTax = 0;
                        $itemPrice = ($poItem['item_value'] - $headerDiscount - $poItem['item_discount_amount']);
                        $shippingAddress = $po->ship_address;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;

                        $taxDetails = TaxHelper::calculateTax($poItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> shipping_country_id, $partyStateId ?? $request -> shipping_state_id, 'collection');

                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $poItem['tax_amount'] = $itemTax;
                        $totalTax += $itemTax;
                    }

                }
                unset($poItem);

                foreach($poItemArr as $_key => $poItem) {
                    $itemPriceAterBothDis =  $poItem['item_value'] - $poItem['item_discount_amount'] - $poItem['header_discount_amount'];
                    $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    $poDetail = new PoItem;
                    $poDetail->purchase_order_id = $poItem['purchase_order_id']; 
                    $poDetail->pi_item_id = $poItem['pi_item_id']; 
                    $poDetail->po_item_id = $poItem['po_item_id']; 
                    $poDetail->item_id = $poItem['item_id']; 
                    $poDetail->item_code = $poItem['item_code']; 
                    $poDetail->hsn_id = $poItem['hsn_id']; 
                    $poDetail->hsn_code = $poItem['hsn_code']; 
                    $poDetail->uom_id = $poItem['uom_id']; 
                    $poDetail->uom_code = $poItem['uom_code']; 
                    $poDetail->order_qty = $poItem['order_qty']; 
                    $poDetail->inventory_uom_id = $poItem['inventory_uom_id']; 
                    $poDetail->inventory_uom_code = $poItem['inventory_uom_code']; 
                    $poDetail->inventory_uom_qty = $poItem['inventory_uom_qty']; 
                    $poDetail->rate = $poItem['rate']; 
                    $poDetail->item_discount_amount = $poItem['item_discount_amount']; 
                    $poDetail->header_discount_amount = $poItem['header_discount_amount']; 
                    $poDetail->expense_amount = $itemHeaderExp; 
                    $poDetail->tax_amount = $poItem['tax_amount']; 
                    $poDetail->company_currency_id = $poItem['company_currency_id']; 
                    $poDetail->group_currency_id = $poItem['group_currency_id']; 
                    $poDetail->group_currency_exchange_rate = $poItem['group_currency_exchange_rate']; 
                    $poDetail->remarks = $poItem['remarks'];
                    $poDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach($poDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $poAttr = new PoItemAttribute;
                        $poAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $poAttr->purchase_order_id = $po->id;
                        $poAttr->po_item_id = $poDetail->id;
                        $poAttr->item_attribute_id = $itemAttribute->id;
                        $poAttr->item_code = $component['item_code'] ?? null;
                        $poAttr->attribute_name = $itemAttribute->attribute_group_id;
                        $poAttr->attribute_value = $poAttrName ?? null;
                        $poAttr->save();
                        }
                    }

                    #Save Componet Delivery
                    if(isset($component['delivery'])) {
                        foreach($component['delivery'] as $delivery) {
                            if(isset($delivery['d_qty']) && $delivery['d_qty']) {
                                $poItemDelivery = new PoItemDelivery;
                                $poItemDelivery->purchase_order_id = $po->id;
                                $poItemDelivery->po_item_id = $poDetail->id;
                                $poItemDelivery->qty = $delivery['d_qty'] ?? 0.00;
                                $poItemDelivery->delivery_date = $delivery['d_date'] ?? now();
                                $poItemDelivery->save();
                            }
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = new PurchaseOrderTed;
                                $ted->purchase_order_id = $po->id;
                                $ted->po_item_id = $poDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->assessment_amount = $poItem['item_value'];
                                $ted->ted_perc = $dis['dis_perc'] ?? 0.00;
                                $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                                $ted->applicable_type = 'Deduction';
                                $ted->save();
                                $totalItemLevelDiscValue = $totalItemLevelDiscValue+$dis['dis_amount'];
                            }
                        }
                    }

                    #Save Componet item Tax
                    if(isset($component['taxes'])) {
                        foreach($component['taxes'] as $tax) {
                            if(isset($tax['t_value']) && $tax['t_value']) {
                                $ted = new PurchaseOrderTed;
                                $ted->purchase_order_id = $po->id;
                                $ted->po_item_id = $poDetail->id;
                                $ted->ted_type = 'Tax';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $tax['t_d_id'] ?? null;
                                $ted->ted_name = $tax['t_type'] ?? null;
                                $ted->assessment_amount = $poItem['item_value'] - $poItem['item_discount_amount'] - $poItem['header_discount_amount'];
                                $ted->ted_perc = $tax['t_perc'] ?? 0.00;
                                $ted->ted_amount = $tax['t_value'] ?? 0.00;
                                $ted->applicable_type = $tax['applicability_type'] ?? 'Collection';
                                $ted->save();
                            }
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $ted = new PurchaseOrderTed;
                            $ted->purchase_order_id = $po->id;
                            $ted->po_item_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
                            $ted->assessment_amount = $itemTotalValue-$itemTotalDiscount;
                            $ted->ted_perc = $dis['d_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                            $ted->applicable_type = 'Deduction';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['exp_summary'])) {
                    foreach($request->all()['exp_summary'] as $dis) {
                        if(isset($dis['e_amnt']) && $dis['e_amnt']) {
                            $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                            $ted = new PurchaseOrderTed;
                            $ted->purchase_order_id = $po->id;
                            $ted->po_item_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_e_id'] ?? null;
                            $ted->ted_name = $dis['e_name'];
                            $ted->assessment_amount = $totalAfterTax;
                            $ted->ted_perc = $dis['e_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['e_amnt'] ?? 0.00;
                            $ted->applicable_type = 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Update total in main header PO*/
                $po->total_item_value = $itemTotalValue ?? 0.00;
                $po->total_discount_value = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                $po->total_tax_value = $totalTax ?? 0.00;
                $po->total_expense_value =  $totalHeaderExpense ?? 0.00;
                $po->save();

            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($po->vendor->currency_id, $po->document_date);

            $po->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $po->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $po->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $po->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $po->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $po->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $po->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $po->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $po->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $po->save();

            #Save Term
            if (isset($request->term_id) && $request->term_id) {
                foreach($request->term_id as $index => $term_id) {
                    $poTerm = new PoTerm;
                    $poTerm->purchase_order_id =  $po->id;
                    $poTerm->term_id =  $term_id;
                    $poTerm->term_code =  $request->term_code[$index] ?? null;
                    $poTerm->remarks =  $request->description[$index] ?? null;
                    $poTerm->save();
                }
            }

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $po->book_id;
                $docId = $po->id;
                $remarks = $po->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $po->approval_level ?? 1;
                $revisionNumber = $po->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($po);
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
            }

            $po = PurchaseOrder::find($po->id);

            if ($request->document_status == 'submitted') {
                $totalValue = $po->grand_total_amount ?? 0;
                $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                $po->document_status = $document_status;
            } else {
                $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }

            /*Po Attachment*/
            if ($request->hasFile('attachment')) {
                if ($this->type == 'supplier-invoice') {
                    $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'supplier-invoice', false);
                } else {
                    $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'po', false);
                }
            }

            $po->save();
            
            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $po,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    # Purchase Order store
    public function update(PoRequest $request, $id)
    {
        $po = PurchaseOrder::find($id);
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

            $currentStatus = $po->document_status;
            $actionType = $request->action_type;

            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'PurchaseOrder', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'PoItem', 'relation_column' => 'purchase_order_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PoItemAttribute', 'relation_column' => 'po_item_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PoItemDelivery', 'relation_column' => 'po_item_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PoTerm', 'relation_column' => 'purchase_order_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PurchaseOrderTed', 'relation_column' => 'po_item_id']
                ];

                $a = Helper::documentAmendment($revisionData, $id);

            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedPiItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            if (count($deletedData['deletedHeaderExpTedIds'])) {
                PurchaseOrderTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
            }

            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                PurchaseOrderTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemDiscTedIds'])) {
                PurchaseOrderTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedDelivery'])) {
                PoItemDelivery::whereIn('id',$deletedData['deletedDelivery'])->delete();
            }

            if (count($deletedData['deletedAttachmentIds'])) {
                $medias = PurchaseOrderMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
                foreach ($medias as $media) {
                    if ($request->document_status == ConstantHelper::DRAFT) {
                        Storage::delete($media->file_name);
                    }
                    $media->delete();
                }
            }
            if (count($deletedData['deletedPiItemIds'])) {
                $poItems = PoItem::whereIn('id',$deletedData['deletedPiItemIds'])->get();
                # all ted remove item level
                foreach($poItems as $poItem) {
                    $poItem->teds()->delete();
                    #delivery remove
                    $poItem->itemDelivery()->delete();
                    # all attr remove
                    $poItem->attributes()->delete();
                    $poItem->delete();
                }
            }

            # Bom Header save
            $totalTaxValue = 0.00;
           
            $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $po->remarks = $request->remarks ?? null;
            $po->payment_term_id = $request->payment_term_id;
            $po->payment_term_code = $request->payment_term_code;
            $po->save();
            $vendorBillingAddress = $po->bill_address ?? null;
            $vendorShippingAddress = $po->ship_address ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $po->bill_address_details()->firstOrNew([
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
                $shippingAddress = $po->ship_address_details()->firstOrNew([
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
                    $po_item_id = null;
                    $si_po_item_id = null;
                    if(isset($component['pi_item_id']) && $component['pi_item_id']) {
                        $piItem = PiItem::find($component['pi_item_id']);
                        $po_item_id = $piItem->id ?? null; 
                    }
                    if(isset($component['si_po_item_id']) && $component['si_po_item_id']) {
                        $si_po_item_id = $component['si_po_item_id'];
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
                        $inventory_uom_qty = floatval($component['qty']) ?? 0.00 ;
                    } else {
                        $inventory_uom_id = $component['uom_id'] ?? null;
                        $inventory_uom_code = $component['uom_code'] ?? null;
                        $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $inventory_uom_qty = floatval($component['qty']) * $alUom->conversion_to_inventory;
                        }
                    }
                    $itemValue = floatval($component['qty']) * floatval($component['rate']);
                    $itemDiscount = floatval($component['discount_amount']) ?? 0.00;
                    
                    $itemTotalValue += $itemValue;
                    $itemTotalDiscount += $itemDiscount;
                    $itemValueAfterDiscount = $itemValue - $itemDiscount;
                    $totalValueAfterDiscount += $itemValueAfterDiscount;
                    $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                    $unit = Unit::find($component['uom_id']);
                    $poItemArr[] = [
                        'purchase_order_id' => $po->id,
                        'pi_item_id' => $po_item_id,
                        'po_item_id' => $si_po_item_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' => $component['uom_id'] ?? null,
                        'uom_code' => $unit?->name ?? null,
                        'order_qty' => floatval($component['qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id,
                        'inventory_uom_code' => $inventory_uom_code,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'item_discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'expense_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'company_currency_id' => @$component['company_currency_id'] ?? 0.00,
                        'company_currency_exchange_rate' => @$component['company_currency_exchange_rate'] ?? 0.00,
                        'group_currency_id' => @$component['group_currency_id'] ?? 0.00,
                        'group_currency_exchange_rate' => @$component['group_currency_exchange_rate'] ?? 0.00,
                        'remarks' => $component['remark'] ?? null,
                        'value_after_discount' => $itemValueAfterDiscount,
                        'item_value' => $itemValue
                    ];
                }

                $isTax = false;
                if(isset($parameters['tax_required']) && !empty($parameters['tax_required']))
                {
                    if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                        $isTax = true;
                    }
                }

                foreach($poItemArr as &$poItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($poItem['value_after_discount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $poItem['value_after_discount'] - $headerDiscount; // after both discount
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if($isTax) {
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($poItem['item_value'] - $headerDiscount - $poItem['item_discount_amount']);
                        $shippingAddress = $po->ship_address;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($poItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'collection');

                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $poItem['tax_amount'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($poItem);

                foreach($poItemArr as $_key => $poItem) {

                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];
                    $itemPriceAterBothDis =  $poItem['item_value'] - $poItem['item_discount_amount'] - $poItem['header_discount_amount'];
                    $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                    $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    # Purchase Order Detail Save
                    $poDetail = PoItem::find($component['po_item_id'] ?? null) ?? new PoItem;

                    if((isset($component['pi_item_id']) && $component['pi_item_id']) || (isset($poDetail->pi_item_id) && $poDetail->pi_item_id)) {
                        $piItem = PiItem::find($component['pi_item_id'] ?? $poDetail->pi_item_id);
                        if(isset($piItem) && $piItem) {

                            if(isset($poDetail->id) && $poDetail->id) {
                                $orderQty = floatval($poDetail->order_qty);
                                $componentQty = floatval($component['qty']);
                                $qtyDifference = $piItem->order_qty - $orderQty + $componentQty;
                                if($qtyDifference) {
                                    $piItem->order_qty = $qtyDifference;
                                }
                            } else {
                                $piItem->order_qty += $component['qty'];
                            }
                            $piItem->save();
                        }
                    }

                    $poDetail->purchase_order_id = $poItem['purchase_order_id']; 
                    $poDetail->pi_item_id = $poItem['pi_item_id']; 
                    $poDetail->po_item_id = $poItem['po_item_id'];
                    $poDetail->item_id = $poItem['item_id']; 
                    $poDetail->item_code = $poItem['item_code']; 
                    $poDetail->hsn_id = $poItem['hsn_id']; 
                    $poDetail->hsn_code = $poItem['hsn_code']; 
                    $poDetail->uom_id = $poItem['uom_id']; 
                    $poDetail->uom_code = $poItem['uom_code']; 
                    $poDetail->order_qty = $poItem['order_qty']; 
                    $poDetail->inventory_uom_id = $poItem['inventory_uom_id']; 
                    $poDetail->inventory_uom_code = $poItem['inventory_uom_code']; 
                    $poDetail->inventory_uom_qty = $poItem['inventory_uom_qty']; 
                    $poDetail->rate = $poItem['rate']; 
                    $poDetail->item_discount_amount = $poItem['item_discount_amount']; 
                    $poDetail->header_discount_amount = $poItem['header_discount_amount']; 
                    $poDetail->expense_amount = $itemHeaderExp; 
                    $poDetail->tax_amount = $poItem['tax_amount']; 
                    $poDetail->company_currency_id = $poItem['company_currency_id']; 
                    $poDetail->group_currency_id = $poItem['group_currency_id']; 
                    $poDetail->group_currency_exchange_rate = $poItem['group_currency_exchange_rate']; 
                    $poDetail->remarks = $poItem['remarks'];
                    $poDetail->save();

                    #Save component Attr
                    foreach($poDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $poAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                        $poAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $poAttr = PoItemAttribute::find($poAttrId) ?? new PoItemAttribute;
                        $poAttr->purchase_order_id = $po->id;
                        $poAttr->po_item_id = $poDetail->id;
                        $poAttr->item_attribute_id = $itemAttribute->id;
                        $poAttr->item_code = $component['item_code'] ?? null;
                        $poAttr->attribute_name = $itemAttribute->attribute_group_id;
                        $poAttr->attribute_value = $poAttrName ?? null;
                        $poAttr->save();
                        }
                    }

                    #Save Componet Delivery
                    if(isset($component['delivery'])) {
                        foreach($component['delivery'] as $delivery) {
                            if(isset($delivery['d_qty']) && $delivery['d_qty']) {
                                $poItemDelivery = PoItemDelivery::find($delivery['id'] ?? null) ?? new PoItemDelivery;
                                $poItemDelivery->purchase_order_id = $po->id;
                                $poItemDelivery->po_item_id = $poDetail->id;
                                $poItemDelivery->qty = $delivery['d_qty'] ?? 0.00;
                                $poItemDelivery->delivery_date = $delivery['d_date'] ?? now();
                                $poItemDelivery->save();
                            }
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = PurchaseOrderTed::find($dis['id'] ?? null) ?? new PurchaseOrderTed;
                                $ted->purchase_order_id = $po->id;
                                $ted->po_item_id = $poDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->assessment_amount = $poItem['item_value'];
                                $ted->ted_perc = $dis['dis_perc'] ?? 0.00;
                                $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                                $ted->applicable_type = 'Deduction';
                                $ted->save();
                                $totalItemLevelDiscValue = $totalItemLevelDiscValue+$dis['dis_amount'];
                            }
                        }
                    }

                    #Save Componet item Tax
                    if(isset($component['taxes'])) {
                        foreach($component['taxes'] as $tax) {
                            if(isset($tax['t_value']) && $tax['t_value']) {
                                $ted = PurchaseOrderTed::find($tax['id'] ?? null) ?? new PurchaseOrderTed;
                                $ted->purchase_order_id = $po->id;
                                $ted->po_item_id = $poDetail->id;
                                $ted->ted_type = 'Tax';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $tax['t_d_id'] ?? null;
                                $ted->ted_name = $tax['t_type'] ?? null;
                                $ted->assessment_amount = $poItem['item_value'] - $poItem['item_discount_amount'] - $poItem['header_discount_amount'];
                                $ted->ted_perc = $tax['t_perc'] ?? 0.00;
                                $ted->ted_amount = $tax['t_value'] ?? 0.00;
                                $ted->applicable_type = $tax['applicability_type'] ?? 'Collection';
                                $ted->save();
                            }
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $ted = PurchaseOrderTed::find($dis['d_id'] ?? null) ?? new PurchaseOrderTed;
                            $ted->purchase_order_id = $po->id;
                            $ted->po_item_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
                            $ted->assessment_amount = $itemTotalValue-$itemTotalDiscount;
                            $ted->ted_perc = $dis['d_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                            $ted->applicable_type = 'Deduction';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if(isset($request->all()['exp_summary'])) {
                    foreach($request->all()['exp_summary'] as $dis) {
                        if(isset($dis['e_amnt']) && $dis['e_amnt']) {
                            $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax; 
                            $ted = PurchaseOrderTed::find($dis['e_id'] ?? null) ?? new PurchaseOrderTed;
                            $ted->purchase_order_id = $po->id;
                            $ted->po_item_id = null;
                            $ted->ted_type = 'Expense';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_e_id'] ?? null;
                            $ted->ted_name = $dis['e_name'];
                            $ted->assessment_amount = $totalAfterTax;
                            $ted->ted_perc = $dis['e_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['e_amnt'] ?? 0.00;
                            $ted->applicable_type = 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Update total in main header PO*/
                $po->total_item_value = $itemTotalValue ?? 0.00;
                $po->total_discount_value = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                $po->total_tax_value = $totalTax ?? 0.00;
                $po->total_expense_value =  $totalHeaderExpense ?? 0.00;
                $po->save();
            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($po->vendor->currency_id, $po->document_date);

            $po->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $po->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $po->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $po->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $po->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $po->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $po->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $po->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $po->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $po->save();

            #Save Term
            if (isset($request->term_id) && $request->term_id) {

                foreach($request->term_id as $index => $term_id) {
                    $existingTerm = $po->termsConditions()
                    ->where('term_id', $term_id)
                    ->where('purchase_order_id', $po->id)
                    ->first();

                    if ($existingTerm) {
                        $existingTerm->term_code = $request->term_code[$index] ?? null; 
                        $existingTerm->remarks = $request->description[$index] ?? null;
                        $existingTerm->save(); 
                    } else {
                        $poTerm = new PoTerm;
                        $poTerm->purchase_order_id =  $po->id;
                        $poTerm->term_id =  $term_id;
                        $poTerm->term_code =  $request->term_code[$index] ?? null;
                        $poTerm->remarks =  $request->description[$index] ?? null;
                        $poTerm->save();                        
                    }
                }
            }

            /*Create document submit log*/
            $bookId = $po->book_id; 
            $docId = $po->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $po->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $po->approval_level;
            $modelName = get_class($po);
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                //*amendmemnt document log*/
                $revisionNumber = $po->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                $po->revision_number = $revisionNumber;
                $po->approval_level = 1;
                $po->revision_date = now();
                $amendAfterStatus = $po->document_status;
                $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                    $totalValue = $po->grand_total_amount ?? 0;
                    $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                }
                if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                    $actionType = 'submit';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                }
                $po->document_status = $amendAfterStatus;
                $po->save();

            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $po->revision_number ?? 0;
                    $actionType = 'submit';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                    $totalValue = $po->grand_total_amount ?? 0;
                    $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    $po->document_status = $document_status;
                } else {
                    $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }

            /*Po Attachment*/
            if ($request->hasFile('attachment')) {
                if($this->type == 'supplier-invoice')
                {
                    $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'supplier-invoice', false);
                } else {
                    $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'po', false);
                }
            }

            $po->save();
            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $po,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
            $html = view('supplier.si.partials.edit-address-modal',compact('addresses','selectedAddress'))->render();
        }
        return response()->json(['data' => ['html' => $html,'selectedAddress' => $selectedAddress], 'status' => 200, 'message' => 'fetched!']);
    }

    # Save Address
    public function addressSave(Request $request)
    {

        $addressId = $request->address_id;
        // if(!$addressId) {
            $request->validate([
                'country_id' => 'required',
                'state_id' => 'required',
                'city_id' => 'required',
                'pincode' => 'required',
                'address' => 'required'
            ]);
        // }

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
        $delivery = json_decode($request->delivery,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        // $uomName = '';
        $uomName = $item?->uom?->name ?? 'NA';
        if($item?->uom_id == $uomId) {
        } else {
            $alUom = $item?->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
            // $uomName = $alUom->uom->name ?? 'NA';
        }

        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $remark = $request->remark ?? null;
        $delivery = isset($delivery) ? $delivery  : null;
        $html = view('supplier.si.partials.comp-item-detail',compact('item','selectedAttr','remark','uomName','qty','delivery','specifications'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # Edit Po
    public function edit(Request $request, $id)
    {
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Edit';
        $short_title = ''; 
        $reference_from_title = '';
        $user = Helper::getAuthenticatedUser();

        $serviceAlias = ConstantHelper::SUPPLIER_INVOICE_SERVICE_ALIAS;
        $title = 'Supplier Invoice';
        $short_title = 'SI'; 
        $reference_from_title = 'Purchase Order'; 
        
        $vendor = $user?->vendor_portal?->vendor;
        $bookIds = $vendor->supplier_books()->pluck('book_id')->toArray();
        $books = Book::whereIn('id', $bookIds)->get();

        // $books = Helper::getBookSeries($serviceAlias)->get();
        $po = PurchaseOrder::ofType($this->type)->where('id',$id)->first();
        if (!$po) {
            abort(404);
        }
        $createdBy = $po?->created_by;
        $revision_number = $po->revision_number;
        $totalValue = $po->grand_total_amount ?? 0;
        $creatorType = Helper::userCheck()['type'];
        $buttons = Helper::actionButtonDisplay($po->book_id,$po->document_status , $po->id, $totalValue, $po->approval_level, $po->created_by ?? 0, $creatorType, $revision_number);

        $revNo = $po->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $po->revision_number;
        }

        $approvalHistory = Helper::getApprovalHistory($po->book_id, $po->id, $revNo, $totalValue,$createdBy);
        $termsAndConditions = TermsAndCondition::withDefaultGroupCompanyOrg()
                            ->where('status',ConstantHelper::ACTIVE)->get();
        $view = 'supplier.si.edit';

        if($request->has('revisionNumber') && $request->revisionNumber != $po->revision_number) {
            $po = $po->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'supplier.si.view';
        } 
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';
        // $locations = InventoryHelper::getAccessibleLocations('stock',$po->store_id);
        return view($view, [
            'books'=> $books,
            'po' => $po,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'termsAndConditions' => $termsAndConditions,
            'revision_number' => $revision_number,
            'title' => $title,
            'menu' => $menu,
            'menu_url' => $menu_url,
            'sub_menu' => $sub_menu,
            'short_title' => $short_title,
            'reference_from_title' => $reference_from_title
        ]);
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
        $po = PurchaseOrder::with(['vendor', 'currency', 'po_items', 'book', 'headerExpenses', 'TermsCondition'])
            ->findOrFail($id);
        $shippingAddress = $po->latestShippingAddress() ?? $po->shippingAddress;

        $totalItemValue = $po->po_items()
                ->selectRaw('SUM(order_qty * rate) as total')
                ->value('total') ?? 0.00;
        $totalItemDiscount = $po->po_items()->sum('item_discount_amount') ?? 0.00;
        $totalHeaderDiscount = $po->po_items()->sum('header_discount_amount') ?? 0.00;
        $totalTaxes = $po->po_items()->sum('tax_amount') ?? 0.00;
        $totalTaxableValue = ($totalItemValue - ($totalItemDiscount + $totalHeaderDiscount));
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalAmount = ($totalAfterTax + $po->total_expense_value ?? 0.00);
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';

        $taxes = PurchaseOrderTed::where('purchase_order_id', $po->id)
        ->where('ted_type', 'Tax')
        ->select('ted_name', 'ted_perc', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assessment_amount) as total_assessment_amount'))
        ->groupBy('ted_name', 'ted_perc')
        ->get();

        // $path = 'pdf.supplier-invoice';
        $path = 'pdf.supplier-invoice2';
        $pdf = PDF::loadView(

            // return view(
            $path,
            [
                'po'=> $po,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress,
                'shippingAddress' =>     $shippingAddress,
                'totalItemValue' => $totalItemValue,
                'totalItemDiscount' =>$totalItemDiscount,
                'totalHeaderDiscount' => $totalHeaderDiscount,
                'totalTaxes' =>$totalTaxes,
                'totalTaxableValue' =>$totalTaxableValue,
                'totalAfterTax' =>$totalAfterTax,
                'totalAmount'=>$totalAmount,
                'amountInWords'=>$amountInWords,
                'imagePath' => $imagePath,
                'docStatusClass' => $docStatusClass,
                'taxes' => $taxes
            ]
        );

        return $pdf->stream('Supplier-Invoice-' . date('Y-m-d') . '.pdf');
    }
    
    # Get PI Item List
    public function getPi(Request $request)
    {
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $org_id = $request->vendor_id ?? null;
        $vendorId = auth()->user()?->vendor_portal->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $departmentId = $request->department_id ?? null;
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $piItems = PoItem::where(function($query) use ($seriesId,$applicableBookIds,$docNumber,$itemId,$vendorId,$org_id, $departmentId) {
                    $query->whereHas('item');
                    $query->whereHas('po', function($pi) use ($seriesId,$applicableBookIds,$docNumber,$vendorId,$org_id, $departmentId) {
                        if($org_id) {
                            $pi->where('organization_id', $org_id);
                        }
                        $pi->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
                        if($seriesId) {
                            $pi->where('book_id',$seriesId);
                        } else {
                            if(count($applicableBookIds)) {
                                $pi->whereIn('book_id',$applicableBookIds);
                            }
                        }
                        if($docNumber) {
                            $pi->where('document_number',$docNumber);
                        }
                        if($departmentId) {
                            $pi->where('department_id', $departmentId);
                        }
                        $pi->where('vendor_id', $vendorId);
                    });

                    if ($itemId) {
                        $query->where('item_id', $itemId);
                    }
                    
                    $query->whereRaw('order_qty > invoice_quantity');
                })
                ->get();
        $html = view('supplier.si.partials.pi-item-list', ['piItems' => $piItems])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit PI Item list
    public function processPiItem(Request $request)
    {
        $ids = json_decode($request->ids,true) ?? [];
        $vendor = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $piItems = PoItem::whereIn('id', $ids)->get();

        $uniquePoIds = PoItem::whereIn('id', $ids)
                        ->distinct()
                        ->pluck('purchase_order_id')
                        ->toArray();
        if(count($uniquePoIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time supplier invoice create from one PO."]);
        }
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

        $poIds = $piItems->pluck('purchase_order_id')->all();
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
        $html = view('supplier.si.partials.invoice-po-item', ['poItems' => $piItems])->render();
        }
        return response()->json(['data' => ['pos' => $html, 'vendor' => $vendor, 'finalDiscounts' => $finalDiscounts,'finalExpenses' => $finalExpenses], 'status' => 200, 'message' => "fetched!"]);
    }

    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::find($request->id);
            if (isset($po)) {
                $revoke = Helper::approveDocument($po->book_id, $po->id, $po->revision_number, '', [], 0, ConstantHelper::REVOKE, $po->grand_total_amount, get_class($po));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $po->document_status = $revoke['approvalStatus'];
                    $po->save();
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
