<?php
namespace App\Http\Controllers;

use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Http\Requests\PRRequest;
use App\Http\Requests\EditPRRequest;

use App\Models\PRTed;
use App\Models\PRHeader;
use App\Models\PRDetail;
use App\Models\PRItemAttribute;

use App\Models\PRTedHistory;
use App\Models\PRHeaderHistory;
use App\Models\PRDetailHistory;
use App\Models\PRItemLocation;
use App\Models\PRItemAttributeHistory;

use App\Models\Hsn;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Book;
use App\Models\Item;
use App\Models\City;
use App\Models\State;
use App\Models\PoItem;
use App\Models\Vendor;
use App\Models\Address;
use App\Models\Country;
use App\Models\ErpStore;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\MrnDetail;
use App\Models\MrnHeader;
use App\Models\CostCenter;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\AlternateUOM;
use App\Models\Organization;
use App\Models\NumberPattern;
use App\Models\AttributeGroup;

use App\Models\ErpEinvoice;
use App\Models\ErpEinvoiceLog;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\BookHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\GstInvoiceHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\ServiceParametersHelper;

use App\Http\Controllers\EInvoiceServiceController;

use App\Services\PRService;
use Illuminate\Http\Exceptions\HttpResponseException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;



class PurchaseReturnController extends Controller
{
    protected $pbService;

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
        // dd($parentUrl);
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = PRHeader::with(
                [
                    'items',
                    'vendor',
                ]   
            )
            ->withDefaultGroupCompanyOrg()
            ->withDraftListingLogic()
            ->bookViewAccess($parentUrl)
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $route = route('purchase-return.edit', $row->id);
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
                    return number_format($row->total_item_amount, 2);
                })
                ->addColumn('total_discount', function ($row) {
                    return number_format($row->total_discount, 2);
                })
                ->addColumn('taxable_amount', function ($row) {
                    return number_format(($row->total_item_amount - $row->total_discount), 2);
                })
                ->addColumn('total_taxes', function ($row) {
                    return number_format($row->total_taxes, 2);
                })
                ->addColumn('expense_amount', function ($row) {
                    return number_format($row->expense_amount, 2);
                })
                ->addColumn('total_amount', function ($row) {
                    return number_format($row->total_amount, 2);
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }
        return view('procurement.purchase-return.index', [
            'servicesBooks'=>$servicesBooks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $serviceAlias = $servicesBooks['services'][0]->alias ?? ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)
            ->where('organization_id', $user->organization_id)
            ->get();
        $materialReceipts = MrnHeader::with('vendor')
            ->where('status', ConstantHelper::ACTIVE)
            ->where('organization_id', $user->organization_id)
            ->get();
        // $erpStores = ErpStore::where('organization_id', $user->organization_id)
        //     ->orderBy('id', 'ASC')
        //     ->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        return view('procurement.purchase-return.create', [
            'books' => $books,
            'vendors' => $vendors,
            'servicesBooks'=>$servicesBooks,
            'materialReceipts' => $materialReceipts,
            'locations' =>$locations
        ]);
    }

    # Purchase Bill store
    public function store(PRRequest $request)
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
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            $mrnHeaderId = null;
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

            # PR Header save
            $totalItemValue = 0.00;
            $totalTaxValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalAmount = 0.00;

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request->currency_id, $request->document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }

            $pb = new PRHeader();
            $pb->fill($request->all());
            $pb->store_id = $request->header_store_id;
            $pb->organization_id = $organization->id;
            $pb->group_id = $organization->group_id;
            $pb->company_id = $organization->company_id;
            $pb->book_code = $request->book_code;
            $pb->series_id = $request->book_id;
            $pb->book_id = $request->book_id;
            $pb->book_code = $request->book_code;
            $pb->vendor_id = $request->vendor_id;
            $pb->vendor_code = $request->vendor_code;
            $pb->qty_return_type = $request->return_type;
            $pb->supplier_invoice_no = $request->supplier_invoice_no;
            $pb->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $pb->billing_to = $request->billing_id;
            $pb->ship_to = $request->shipping_id;
            $pb->billing_address = $request->billing_address;
            $pb->shipping_address = $request->shipping_address;
            $pb->revision_number = 0;
            $document_number = $request->document_number ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_number;
            $regeneratedDocExist = PRHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                ->where('document_number', $document_number)->first();
            //Again check regenerated doc no
            if (isset($regeneratedDocExist)) {
                return response()->json([
                    'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                    'error' => "",
                ], 422);
            }

            $pb->doc_number_type = $numberPatternData['type'];
            $pb->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $pb->doc_prefix = $numberPatternData['prefix'];
            $pb->doc_suffix = $numberPatternData['suffix'];
            $pb->doc_no = $numberPatternData['doc_no'];

            $pb->document_number = $document_number;
            $pb->document_date = $request->document_date;
            $pb->final_remark = $request->remarks ?? null;

            $pb->total_item_amount = 0.00;
            $pb->total_discount = 0.00;
            $pb->taxable_amount = 0.00;
            $pb->total_taxes = 0.00;
            $pb->total_after_tax_amount = 0.00;
            $pb->expense_amount = 0.00;
            $pb->total_amount = 0.00;
            $pb->save();

            $vendorBillingAddress = $pb->billingAddress ?? null;
            $vendorShippingAddress = $pb->shippingAddress ?? null;
            // dd($vendorBillingAddress, $vendorShippingAddress);
            if ($vendorBillingAddress) {
                $billingAddress = $pb->bill_address_details()->firstOrNew([
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
                $shippingAddress = $pb->ship_address_details()->firstOrNew([
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
            if($pb?->erpStore)
            {
                $storeAddress  = $pb?->erpStore->address;
                $storeLocation = $pb->store_address()->firstOrNew();
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
            $pb -> gst_invoice_type = EInvoiceHelper::getGstInvoiceType($request -> vendor_id, $shippingAddress -> country_id, $storeLocation -> country_id, 'vendor');

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
                $pbItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach ($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $mrn_detail_id = null;
                    if (isset($component['mrn_detail_id']) && $component['mrn_detail_id']) {
                        $pbDetail = MrnDetail::find($component['mrn_detail_id']);
                        $mrn_detail_id = $pbDetail->id ?? null;
                        $mrnHeaderId = $component['mrn_header_id'];
                        if ($pbDetail) {
                            if($pb->qty_return_type == 'rejected'){
                                $pbDetail->pr_rejected_qty += floatval($component['accepted_qty']);
                            } else{
                                $pbDetail->pr_qty += floatval($component['accepted_qty']);
                            }
                            $pbDetail->save();
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
                        if ($alUom) {
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
                    $pbItemArr[] = [
                        'header_id' => $pb->id,
                        'mrn_detail_id' => $mrn_detail_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'store_id' => $component['store_id'] ?? null,
                        'store_code' => @$component['erp_store_code'] ?? null,
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
                if (isset($parameters['tax_required']) && !empty($parameters['tax_required'])) {
                    if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                        $isTax = true;
                    }
                }

                foreach ($pbItemArr as &$pbItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($pbItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $pbItem['taxable_amount'] - $headerDiscount; // after both discount
                    $pbItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;

                    //Tax
                    if ($isTax) {
                        $itemTax = 0;
                        $itemPrice = ($pbItem['basic_value'] - $headerDiscount - $pbItem['discount_amount']);
                        $shippingAddress = $pb->shippingAddress;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress->country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress->state_id : null;

                        $taxDetails = TaxHelper::calculateTax($pbItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'collection');

                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double) $taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $pbItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($pbItem);

                foreach ($pbItemArr as $_key => $pbItem) {
                    $itemPriceAterBothDis = $pbItem['basic_value'] - $pbItem['discount_amount'] - $pbItem['header_discount_amount'];
                    $totalAfterTax = $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                    $itemHeaderExp = $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    $pbDetail = new PRDetail;

                    $pbDetail->header_id = $pbItem['header_id'];
                    $pbDetail->mrn_detail_id = $pbItem['mrn_detail_id'];
                    $pbDetail->item_id = $pbItem['item_id'];
                    $pbDetail->item_code = $pbItem['item_code'];
                    $pbDetail->hsn_id = $pbItem['hsn_id'];
                    $pbDetail->hsn_code = $pbItem['hsn_code'];
                    $pbDetail->uom_id = $pbItem['uom_id'];
                    $pbDetail->uom_code = $pbItem['uom_code'];
                    $pbDetail->store_id = $pbItem['store_id'];
                    $pbDetail->store_code = $pbItem['store_code'];
                    $pbDetail->accepted_qty = $pbItem['accepted_qty'];
                    $pbDetail->inventory_uom_id = $pbItem['inventory_uom_id'];
                    $pbDetail->inventory_uom_code = $pbItem['inventory_uom_code'];
                    $pbDetail->inventory_uom_qty = $pbItem['inventory_uom_qty'];
                    $pbDetail->rate = $pbItem['rate'];
                    $pbDetail->basic_value = $pbItem['basic_value'];
                    $pbDetail->discount_amount = $pbItem['discount_amount'];
                    $pbDetail->header_discount_amount = $pbItem['header_discount_amount'];
                    $pbDetail->header_exp_amount = $itemHeaderExp;
                    $pbDetail->tax_value = $pbItem['tax_value'];
                    // $pbDetail->company_currency = $pbItem['company_currency_id'];
                    // $pbDetail->group_currency = $pbItem['group_currency_id'];
                    // $pbDetail->exchange_rate_to_group_currency = $pbItem['group_currency_exchange_rate'];
                    $pbDetail->remark = $pbItem['remark'];
                    $pbDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach ($pbDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $pbAttr = new PRItemAttribute;
                            $pbAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $pbAttr->header_id = $pb->id;
                            $pbAttr->detail_id = $pbDetail->id;
                            $pbAttr->item_attribute_id = $itemAttribute->id;
                            $pbAttr->item_code = $component['item_code'] ?? null;
                            $pbAttr->attr_name = $itemAttribute->attribute_group_id;
                            $pbAttr->attr_value = $pbAttrName ?? null;
                            $pbAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if (isset($component['discounts'])) {
                        foreach ($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = new PRTed;
                                $ted->header_id = $pb->id;
                                $ted->detail_id = $pbDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->ted_code = $dis['dis_name'];
                                $ted->assesment_amount = $pbItem['basic_value'];
                                $ted->ted_percentage = $dis['dis_perc'] ?? 0.00;
                                $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                                $ted->applicability_type = 'Deduction';
                                $ted->save();
                                $totalItemLevelDiscValue = $totalItemLevelDiscValue + $dis['dis_amount'];
                            }
                        }
                    }

                    #Save Componet item Tax
                    if (isset($component['taxes'])) {
                        foreach ($component['taxes'] as $tax) {
                            if (isset($tax['t_value']) && $tax['t_value']) {
                                $ted = new PRTed;
                                $ted->header_id = $pb->id;
                                $ted->detail_id = $pbDetail->id;
                                $ted->ted_type = 'Tax';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $tax['t_d_id'] ?? null;
                                $ted->ted_name = $tax['t_type'] ?? null;
                                $ted->ted_code = $tax['t_type'] ?? null;
                                $ted->assesment_amount = $pbItem['basic_value'] - $pbItem['discount_amount'] - $pbItem['header_discount_amount'];
                                $ted->ted_percentage = $tax['t_perc'] ?? 0.00;
                                $ted->ted_amount = $tax['t_value'] ?? 0.00;
                                $ted->applicability_type = $tax['applicability_type'] ?? 'Collection';
                                $ted->save();
                            }
                        }
                    }
                }

                /*Header level save discount*/
                if (isset($request->all()['disc_summary'])) {
                    foreach ($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $ted = new PRTed;
                            $ted->header_id = $pb->id;
                            $ted->detail_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
                            $ted->assesment_amount = $itemTotalValue - $itemTotalDiscount;
                            $ted->ted_percentage = $dis['d_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Deduction';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if (isset($request->all()['exp_summary'])) {
                    foreach ($request->all()['exp_summary'] as $dis) {
                        if (isset($dis['e_amnt']) && $dis['e_amnt']) {
                            $totalAfterTax = $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                            $ted = new PRTed;
                            $ted->header_id = $pb->id;
                            $ted->detail_id = null;
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

                /*Update total in main header PR*/
                $pb->total_item_amount = $itemTotalValue ?? 0.00;
                $totalDiscValue = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($itemTotalValue < $totalDiscValue){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Negative value not allowed'
                    ], 422);
                }
                $pb->total_discount = $totalDiscValue ?? 0.00;
                $pb->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $pb->total_taxes = $totalTax ?? 0.00;
                $pb->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $totalTax) ?? 0.00;
                $pb->expense_amount = $totalHeaderExpense ?? 0.00;
                $totalAmount = (($itemTotalValue - $totalDiscValue) + ($totalTax + $totalHeaderExpense)) ?? 0.00;
                $pb->total_amount = $totalAmount ?? 0.00;
                $pb->save();

                /*Update mrn header id in main header PR*/
                $pb->mrn_header_id = $mrnHeaderId;
                $pb->save();

            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please add atleast one row in component table.',
                    'error' => "",
                ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($pb->vendor->currency_id, $pb->document_date);

            $pb->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $pb->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $pb->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $pb->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $pb->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $pb->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $pb->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $pb->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $pb->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $pb->save();

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $pb->book_id;
                $docId = $pb->id;
                $remarks = $pb->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $pb->approval_level;
                $revisionNumber = $pb->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($pb);
                $totalValue = $pb->total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                if ($approveDocument['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $approveDocument['message'],
                    ],422);
                }
            }

            $pb = PRHeader::find($pb->id);
            if ($request->document_status == 'submitted') {
                // $totalValue = $po->grand_total_amount ?? 0;
                // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                $pb->document_status = $approveDocument['approvalStatus'] ?? $pb->document_status;
            } else {
                $pb->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            /*PR Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $pb->uploadDocuments($request->file('attachment'), 'pb', false);
            }
            $pb->save();
            if($pb->qty_return_type == 'accepted'){
                $invoiceLedger = self::maintainStockLedger($pb);
            }
            
            $redirectUrl = '';
            if(($pb->document_status == ConstantHelper::POSTED)) {
                $gstInvoiceType = EInvoiceHelper::getGstInvoiceType($request -> vendor_id, $shippingAddress -> country_id, $storeLocation -> country_id, 'vendor');
                if ($pb -> document_status === ConstantHelper::POSTED){
                    if ($gstInvoiceType === EInvoiceHelper::B2B_INVOICE_TYPE) {
                        $data = EInvoiceHelper::saveGstIn($pb);
                        if (isset($data) && $data['status'] == 'error') {
                            DB::rollBack();
                            return response()->json([
                                'error' => 'error',
                                'message' => $data['message'],
                            ], 500);
                        }
                    }
                }
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $pb->id . '/pdf');
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $pb,
                'redirect_url' => $redirectUrl
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

        $pb = PRHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        $totalItemValue = $pb->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($pb->series_id, $pb->document_status, $pb->id, $pb->total_amount, $pb->approval_level, $pb->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory($pb->series_id, $pb->id, $pb->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pb->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        return view('procurement.purchase-return.view', [
            'pb' => $pb,
            'buttons' => $buttons,
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
        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $serviceAlias = ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $user = Helper::getAuthenticatedUser();
        $pb = PRHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        //dd($pb->toArray());
        $totalItemValue = $pb->items()->sum('basic_value');
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $revision_number = $pb->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($pb->book_id, $pb->document_status, $pb->id, $pb->total_amount, $pb->approval_level, $pb->created_by ?? 0, $userType['type'], $revision_number);
        $revNo = $pb->revision_number;
        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $pb->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($pb->book_id, $pb->id, $revNo, $pb->total_amount);
        $view = 'procurement.purchase-return.edit';
        if ($request->has('revisionNumber') && $request->revisionNumber != $pb->revision_number) {
            $pb = $pb->source;
            $pb = PRHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('header_id', $pb->header_id)
                ->first();
            $view = 'procurement.purchase-return.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pb->document_status] ?? '';
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $store = $pb->erpStore;
        $deliveryAddress = $store?->address?->display_address;
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;
        $eInvoice = $pb->irnDetail()->first();

        return view($view, [
            'deliveryAddress'=> $deliveryAddress,
            'orgAddress'=> $orgAddress,
            'mrn' => $pb,
            'user' => $user,
            'books' => $books,
            'buttons' => $buttons,
            'vendors' => $vendors,
            'locations' => $locations,
            'docStatusClass' => $docStatusClass,
            'totalItemValue' => $totalItemValue,
            'revision_number' => $revision_number,
            'approvalHistory' => $approvalHistory,
            'eInvoice' => $eInvoice
        ]);
    }

    # PR Update
    public function update(EditPRRequest $request, $id)
    {
        $pb = PRHeader::find($id);
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;
        $groupId = $organization?->group_id ?? null;
        $companyId = $organization?->company_id ?? null;
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
            // dd($request->all());
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            $currentStatus = $pb->document_status;
            $actionType = $request->action_type;

            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'PRHeader', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'PRDetail', 'relation_column' => 'header_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PRItemAttribute', 'relation_column' => 'detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PRTed', 'relation_column' => 'detail_id']
                ];
                $a = Helper::documentAmendment($revisionData, $id);
            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedPRItemIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            if (count($deletedData['deletedHeaderExpTedIds'])) {
                PRTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'])->delete();
            }

            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                PRTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemDiscTedIds'])) {
                PRTed::whereIn('id', $deletedData['deletedItemDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedPRItemIds'])) {
                $pbItems = PRDetail::whereIn('id', $deletedData['deletedPRItemIds'])->get();
                # all ted remove item level
                foreach ($pbItems as $pbItem) {
                    $pbItem->teds()->delete();
                    # all attr remove
                    $pbItem->attributes()->delete();
                    $pbItem->delete();
                }
            }

            # PB Header save
            $totalTaxValue = 0.00;
            $pb->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $pb->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $pb->final_remark = $request->remarks ?? '';
            $pb->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $pb->save();

            $vendorBillingAddress = $pb->billingAddress ?? null;
            $vendorShippingAddress = $pb->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $pb->bill_address_details()->firstOrNew([
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
                $shippingAddress = $pb->ship_address_details()->firstOrNew([
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
            if($pb?->erpStore)
            {
                $storeAddress  = $pb?->erpStore->address;
                $storeLocation = $pb->store_address()->firstOrNew();
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
                $pbItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach ($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $mrn_detail_id = null;
                    if (isset($component['mrn_detail_id']) && $component['mrn_detail_id']) {
                        $mrnDetail = MrnDetail::find($component['mrn_detail_id']);
                        $mrn_detail_id = $mrnDetail->id ?? null;
                        if ($mrnDetail) {
                            $mrnDetail->pr_qty += floatval($component['accepted_qty']);
                            $mrnDetail->save();
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
                        if ($alUom) {
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
                    $pbItemArr[] = [
                        'header_id' => $pb->id,
                        'mrn_detail_id' => $mrn_detail_id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'store_id' => $component['store_id'] ?? null,
                        'store_code' => @$component['erp_store_code'] ?? null,
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
                if (isset($parameters['tax_required']) && !empty($parameters['tax_required'])) {
                    if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                        $isTax = true;
                    }
                }

                foreach ($pbItemArr as &$pbItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($pbItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $pbItem['taxable_amount'] - $headerDiscount; // after both discount
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if ($isTax) {
                        //Tax
                        $itemTax = 0;
                        $itemPrice = ($pbItem['basic_value'] - $headerDiscount - $pbItem['discount_amount']);
                        $shippingAddress = $pb->shippingAddress;

                        $partyCountryId = isset($shippingAddress) ? $shippingAddress->country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress->state_id : null;
                        $taxDetails = TaxHelper::calculateTax($pbItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'collection');

                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                $itemTax += ((double) $taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                            }
                        }
                        $pbItem['tax_value'] = $itemTax;
                        $totalTax += $itemTax;
                    }
                }
                unset($pbItem);

                foreach ($pbItemArr as $_key => $pbItem) {
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];
                    $itemPriceAterBothDis = $pbItem['basic_value'] - $pbItem['discount_amount'] - $pbItem['header_discount_amount'];
                    $totalAfterTax = $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                    $itemHeaderExp = $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;

                    # PR Detail Save
                    $pbDetail = PRDetail::find($component['detail_id'] ?? null) ?? new PRDetail;

                    $pbDetail->header_id = $pbItem['header_id'];
                    $pbDetail->mrn_detail_id = $pbItem['mrn_detail_id'];
                    $pbDetail->item_id = $pbItem['item_id'];
                    $pbDetail->item_code = $pbItem['item_code'];

                    $pbDetail->hsn_id = $pbItem['hsn_id'];
                    $pbDetail->hsn_code = $pbItem['hsn_code'];
                    $pbDetail->uom_id = $pbItem['uom_id'];
                    $pbDetail->uom_code = $pbItem['uom_code'];
                    $pbDetail->store_id = $pbItem['store_id'];
                    $pbDetail->store_code = $pbItem['store_code'];
                    $pbDetail->accepted_qty = $pbItem['accepted_qty'];
                    $pbDetail->inventory_uom_id = $pbItem['inventory_uom_id'];
                    $pbDetail->inventory_uom_code = $pbItem['inventory_uom_code'];
                    $pbDetail->inventory_uom_qty = $pbItem['inventory_uom_qty'];
                    $pbDetail->rate = $pbItem['rate'];
                    $pbDetail->basic_value = $pbItem['basic_value'];
                    $pbDetail->discount_amount = $pbItem['discount_amount'];
                    $pbDetail->header_discount_amount = $pbItem['header_discount_amount'];
                    $pbDetail->tax_value = $pbItem['tax_value'];
                    $pbDetail->header_exp_amount = $itemHeaderExp;
                    // $pbDetail->company_currency = $pbItem['company_currency_id'];
                    // $pbDetail->group_currency = $pbItem['group_currency_id'];
                    // $pbDetail->exchange_rate_to_group_currency = $pbItem['group_currency_exchange_rate'];
                    $pbDetail->remark = $pbItem['remark'];
                    $pbDetail->save();

                    #Save component Attr
                    foreach ($pbDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $pbAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $pbAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $pbAttr = PRItemAttribute::find($pbAttrId) ?? new PRItemAttribute;
                            $pbAttr->header_id = $pb->id;
                            $pbAttr->detail_id = $pbDetail->id;
                            $pbAttr->item_attribute_id = $itemAttribute->id;
                            $pbAttr->item_code = $component['item_code'] ?? null;
                            $pbAttr->attr_name = $itemAttribute->attribute_group_id;
                            $pbAttr->attr_value = $pbAttrName ?? null;
                            $pbAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if (isset($component['discounts'])) {
                        foreach ($component['discounts'] as $dis) {
                            if (isset($dis['dis_amount']) && $dis['dis_amount']) {
                                $ted = PRTed::find(@$dis['id']) ?? new PRTed;
                                $ted->header_id = $pb->id;
                                $ted->detail_id = $pbDetail->id;
                                $ted->ted_type = 'Discount';
                                $ted->ted_level = 'D';
                                $ted->ted_id = $dis['ted_id'] ?? null;
                                $ted->ted_name = $dis['dis_name'];
                                $ted->ted_code = $dis['dis_name'];
                                $ted->assesment_amount = $pbItem['basic_value'];
                                $ted->ted_percentage = $dis['dis_perc'] ?? 0.00;
                                $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                                $ted->applicability_type = 'Deduction';
                                $ted->save();
                                $totalItemLevelDiscValue = $totalItemLevelDiscValue + $dis['dis_amount'];
                            }
                        }
                    }

                    #Save Component item Tax
                    if (isset($component['taxes'])) {
                        foreach ($component['taxes'] as $key => $tax) {
                            $pbAmountId = null;
                            $ted = PRTed::find(@$tax['id']) ?? new PRTed;
                            $ted->header_id = $pb->id;
                            $ted->detail_id = $pbDetail->id;
                            $ted->ted_type = 'Tax';
                            $ted->ted_level = 'D';
                            $ted->ted_id = $tax['t_d_id'] ?? null;
                            $ted->ted_name = $tax['t_type'] ?? null;
                            $ted->ted_code = $tax['t_type'] ?? null;
                            $ted->assesment_amount = $pbItem['basic_value'] - $pbItem['discount_amount'] - $pbItem['header_discount_amount'];
                            $ted->ted_percentage = $tax['t_perc'] ?? 0.00;
                            $ted->ted_amount = $tax['t_value'] ?? 0.00;
                            $ted->applicability_type = $tax['applicability_type'] ?? 'Collection';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if (isset($request->all()['disc_summary'])) {
                    foreach ($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $pbAmountId = @$dis['d_id'];
                            $ted = PRTed::find($pbAmountId) ?? new PRTed;
                            $ted->header_id = $pb->id;
                            $ted->detail_id = null;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'H';
                            $ted->ted_id = $dis['ted_d_id'] ?? null;
                            $ted->ted_name = $dis['d_name'];
                            $ted->ted_code = @$dis['d_name'];
                            $ted->assesment_amount = $itemTotalValue - $itemTotalDiscount;
                            $ted->ted_percentage = $dis['d_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                            $ted->applicability_type = 'Deduction';
                            $ted->save();
                        }
                    }
                }

                /*Header level save discount*/
                if (isset($request->all()['exp_summary'])) {
                    foreach ($request->all()['exp_summary'] as $dis) {
                        if (isset($dis['e_amnt']) && $dis['e_amnt']) {
                            $totalAfterTax = $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                            $pbAmountId = @$dis['e_id'];
                            $ted = PRTed::find($pbAmountId) ?? new PRTed;
                            $ted->header_id = $pb->id;
                            $ted->detail_id = null;
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

                /*Update total in main header PR*/
                $pb->total_item_amount = $itemTotalValue ?? 0.00;
                $totalDiscValue = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($itemTotalValue < $totalDiscValue){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Negative value not allowed'
                    ], 422);
                }
                $pb->total_discount = $totalDiscValue ?? 0.00;
                $pb->taxable_amount = ($itemTotalValue - $totalDiscValue) ?? 0.00;
                $pb->total_taxes = $totalTax ?? 0.00;
                $pb->total_after_tax_amount = (($itemTotalValue - $totalDiscValue) + $totalTax) ?? 0.00;
                $pb->expense_amount = $totalHeaderExpense ?? 0.00;
                $totalAmount = (($itemTotalValue - $totalDiscValue) + ($totalTax + $totalHeaderExpense)) ?? 0.00;
                $pb->total_amount = $totalAmount ?? 0.00;
                $pb->save();
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please add atleast one row in component table.',
                    'error' => "",
                ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($pb->vendor->currency_id, $pb->document_date);

            $pb->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $pb->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $pb->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $pb->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $pb->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $pb->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $pb->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $pb->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $pb->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $pb->save();

            /*Create document submit log*/
            $bookId = $pb->book_id;
            $docId = $pb->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $pb->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $pb->approval_level;
            $modelName = get_class($pb);
            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                //*amendmemnt document log*/
                $revisionNumber = $pb->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, $pb->total_amount, $modelName);
                $pb->revision_number = $revisionNumber;
                $pb->approval_level = 1;
                $pb->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ?? $pb->document_status;
                // $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                // if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                //     $totalValue = $pb->grand_total_amount ?? 0;
                //     $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                // }
                // if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                //     $actionType = 'submit';
                //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                // }
                $pb->document_status = $amendAfterStatus;
                $pb->save();

            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $pb->revision_number ?? 0;
                    $actionType = 'submit';
                    $totalValue = $pb->total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);

                    // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    $document_status = $approveDocument['approvalStatus'] ?? $pb->document_status;
                    $pb->document_status = $document_status;
                } else {
                    $pb->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }

            /*PR Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $pb->uploadDocuments($request->file('attachment'), 'pb', false);
            }

            $pb->save();
            if($pb->qty_return_type == 'accepted'){
                $invoiceLedger = self::maintainStockLedger($pb);
            }

            $redirectUrl = '';
            if(($pb->document_status == ConstantHelper::POSTED)) {
                $gstInvoiceType = EInvoiceHelper::getGstInvoiceType($request -> vendor_id, $shippingAddress -> country_id, $storeLocation -> country_id, 'vendor');
                if ($pb -> document_status === ConstantHelper::POSTED){
                    if ($gstInvoiceType === EInvoiceHelper::B2B_INVOICE_TYPE) {
                        $data = EInvoiceHelper::saveGstIn($pb);
                        if (isset($data) && $data['status'] == 'error') {
                            DB::rollBack();
                            return response()->json([
                                'error' => 'error',
                                'message' => $data['message'],
                            ], 500);
                        }
                    }
                }
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $pb->id . '/pdf');
            }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $pb,
                'redirect_url' => $redirectUrl
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
        $item = json_decode($request->item, true) ?? [];
        // dd($item);
        $componentItem = json_decode($request->component_item, true) ?? [];
        // $erpStores = ErpStore::where('organization_id', $user->organization_id)
        //     ->orderBy('id', 'ASC')
        //     ->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        /*Check last tr in table mandatory*/
        if (isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                // return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.purchase-return.partials.item-row', compact(['rowCount', 'locations']))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    // PO Item Rows
    public function mrnItemRows(Request $request)
    {
        //dd('hii');
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $item_ids = explode(',', $request->item_ids);
        $items = MrnDetail::whereIn('id', $item_ids)
            ->get();
        //dd($items);
        $costCenters = CostCenter::where('organization_id', $user->organization_id)->get();
        $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])->find($request->vendor_id);
        $currency = $vendor->currency;
        $paymentTerm = $vendor->paymentTerms;
        $shipping = $vendor->addresses()->where(function ($query) {
            $query->where('type', 'shipping')->orWhere('type', 'both');
        })->latest()->first();
        $billing = $vendor->addresses()->where(function ($query) {
            $query->where('type', 'billing')->orWhere('type', 'both');
        })->latest()->first();
        $html = view(
            'procurement.purchase-return.partials.mrn-item-row',
            compact(
                'items',
                'costCenters'
            )
        )
            ->render();
        return response()->json(
            [
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

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $attributeGroups = AttributeGroup::with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];
        $pbDetailId = $request->detail_id ?? null;
        $itemAttIds = [];
        if ($pbDetailId) {
            $pbDetail = PRDetail::find($pbDetailId);
            if ($pbDetail) {
                $itemAttIds = $pbDetail->attributes()->pluck('item_attribute_id')->toArray();
            }
        }
        $itemAttributes = collect();
        if (count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id', $itemAttIds)->get();
        } else {
            $itemAttributes = $item?->itemAttributes;
        }
        $html = view('procurement.purchase-return.partials.comp-attribute', compact('item', 'attributeGroups', 'rowCount', 'selectedAttr'))->render();
        $hiddenHtml = '';
        foreach ($item->itemAttributes as $attribute) {
            $selected = '';
            foreach ($attribute->attributes() as $value) {
                if (in_array($value->id, $selectedAttr)) {
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
        $disPerc = $request->dis_perc;
        $disAmount = $request->dis_amount;
        $html = view('procurement.purchase-return.partials.add-disc-row', compact('tblRowCount', 'rowCount', 'disName', 'disAmount', 'disPerc'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # get tax calcualte
    public function taxCalculation(Request $request)
    {
        // dd($request->all());
        $user = Helper::getAuthenticatedUser();
        $location = ErpStore::find($request->location_id ?? null);
        $organization = $user->organization;
        $firstAddress = $location?->address ?? null;
        if(!$firstAddress) {
            $firstAddress = $organization?->addresses->first();
        }
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
            $taxDetails = TaxHelper::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType,$document_date);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = floatval($request->price) ?? 0;
            // dd($hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType);
            $html = view('procurement.purchase-return.partials.item-tax', compact('taxDetails', 'rowCount', 'itemPrice'))->render();
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
        $storeId = $request->store_id ?? null;
        $store = ErpStore::find($storeId);
        $deliveryAddress = $store?->address?->display_address;

        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;
        return response()->json(['data' => ['org_address' => $orgAddress,'delivery_address' => $deliveryAddress, 'vendor' =>$vendor, 'shipping' => $shipping,'billing' => $billing, 'paymentTerm' => $paymentTerm, 'currency' => $currency, 'currency_exchange' => $currencyData], 'status' => 200, 'message' => 'fetched']);
    }

    # Get edit address modal
    public function editAddress(Request $request)
    {
        $type = $request->type;
        $addressId = $request->address_id;
        $vendor = Vendor::find($request->vendor_id ?? null);
        if (!$vendor) {
            return response()->json([
                'message' => 'Please First select vendor.',
                'error' => null,
            ], 500);
        }
        if ($request->type == 'shipping') {
            $addresses = $vendor->addresses()->where(function ($query) {
                $query->where('type', 'shipping')->orWhere('type', 'both');
            })->latest()->get();

            $selectedAddress = $vendor->addresses()->where('id', $addressId)->where(function ($query) {
                $query->where('type', 'shipping')->orWhere('type', 'both');
            })->latest()->first();
        } else {
            $addresses = $vendor->addresses()->where(function ($query) {
                $query->where('type', 'billing')->orWhere('type', 'both');
            })->latest()->get();
            $selectedAddress = $vendor->addresses()->where('id', $addressId)->where(function ($query) {
                $query->where('type', 'billing')->orWhere('type', 'both');
            })->latest()->first();
        }
        $html = '';
        if (!intval($request->onChange)) {
            $html = view('procurement.purchase-return.partials.edit-address-modal', compact('addresses', 'selectedAddress'))->render();
        }
        return response()->json(['data' => ['html' => $html, 'selectedAddress' => $selectedAddress], 'status' => 200, 'message' => 'fetched!']);
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

        $addressType = $request->address_type;
        $vendorId = $request->hidden_vendor_id;
        $countryId = $request->country_id;
        $stateId = $request->state_id;
        $cityId = $request->city_id;
        $pincode = $request->pincode;
        $address = $request->address;

        $vendor = Vendor::find($vendorId ?? null);
        $selectedAddress = $vendor->addresses()
            ->where('id', $addressId)
            ->where(function ($query) use ($addressType) {
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
        $selectedAttr = json_decode($request->selectedAttr, 200) ?? [];
        $itemId = $request->item_id;
        $item = Item::find($request->item_id ?? null);
        $mrnDetail = MrnDetail::find($request->mrn_detail_id ?? null);
        $poItem = PoItem::with('po')->find($mrnDetail->purchase_order_item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = intval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';
        $storeId = $request->store_id ?? null;
        if ($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
        }
        $remark = $request->remark ?? null;
        $mrn = MrnHeader::find($request->mrn_header_id);
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $totalStockData = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttr,  $uomId, $storeId);
        $detailedStocks = InventoryHelper::fetchStockSummary($itemId, $selectedAttr,  $uomId, $qty, $storeId);
        $html = view('procurement.purchase-return.partials.comp-item-detail', compact('item', 'mrn', 'selectedAttr', 'remark', 'uomName', 'qty', 'specifications', 'poItem', 'totalStockData'))->render();
        $storeHtml = view('procurement.purchase-return.partials.item-location-modal', compact('detailedStocks'))->render();
        return response()->json(['data' => ['html' => $html, 'detailedStocks' => $detailedStocks], 'status' => 200, 'message' => 'fetched.']);
    }

    public function getMrnItemsByVendorId(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])
                // ->where('organization_id', $organization->id)
                ->find($request->vendor_id);
            //dd($vendor);
            $items = MrnDetail::with([
                'header',
                'item',
                'attributes'
            ])
                ->whereHas('mrnHeader', function ($q) use ($request, $organization) {
                    $q->where('vendor_id', $request->vendor_id)
                        ->where('document_status', '=', 'approved');
                })
                ->whereHas('item', function ($q) {
                    $q->where('type', 'Goods');
                })
                ->get();

            $currency = $vendor->currency;
            $paymentTerm = $vendor->paymentTerms;
            $shipping = $vendor->addresses()->where('type', 'shipping')->Orwhere('type', 'both')->latest()->first();
            $billing = $vendor->addresses()->where('type', 'billing')->Orwhere('type', 'both')->latest()->first();
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

    public function getMrnItemsByMrnId(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $items = MrnDetail::with([
                'header',
                'item',
                'attributes'
            ])
                ->whereHas('header', function ($q) use ($request, $organization) {
                    $q->where('organization_id', $organization->id)
                        ->where('document_status', '=', 'approved');
                })
                ->whereHas('item', function ($q) {
                    $q->where('type', 'Goods');
                })
                ->where('mrn_header_id', $request->mrn_header_id)
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

    # Component Delete
    public function componentDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $pbHeader = null;
            $totalItemValue = 0.00;
            $totalDiscValue = 0.00;
            $totalTaxableValue = 0.00;
            $totalTaxes = 0.00;
            $totalAfterTax = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalAmount = 0.00;
            $componentIds = json_decode($request->ids, true) ?? [];
            $pbItems = PRDetail::with(['attributes'])->whereIn('id', $componentIds)->get();
            foreach ($pbItems as $pbItem) {
                $pbHeader = $pbItem->header;
                $totalItemValue = $pbHeader->total_item_amount - ($pbItem->accepted_qty * $pbItem->rate);
                $totalDiscValue = $pbHeader->total_discount - ($pbItem->discount_amount + $pbItem->header_discount_amount);
                $totalTaxableValue = ($totalItemValue - $totalDiscValue);
                $totalTaxes = $pbHeader->total_taxes - $pbItem->tax_value;
                $totalAfterTax = ($totalTaxableValue + $totalTaxes);
                $totalExpValue = $pbHeader->expense_amount - $pbItem->header_exp_amount;
                $totalAmount = ($totalAfterTax + $totalExpValue);
                // dd($totalItemValue, $totalDiscValue, $totalTaxableValue, $totalTaxes, $totalAfterTax, $totalExpValue, $totalAmount);

                $pbHeader->total_item_amount = $totalItemValue;
                $pbHeader->total_discount = $totalDiscValue;
                $pbHeader->taxable_amount = $totalTaxableValue;
                $pbHeader->total_taxes = $totalTaxes;
                $pbHeader->total_after_tax_amount = $totalAfterTax;
                $pbHeader->total_amount = $totalAmount;
                $pbHeader->save();

                $headerDic = PRTed::where('header_id', $pbHeader->id)
                    ->where('ted_level', 'H')
                    ->where('ted_type', 'Discount')
                    ->first();
                if ($headerDic) {
                    $headerDic->ted_amount -= $pbItem->header_discount_amount;
                    $headerDic->save();
                }

                $headerExp = PRTed::where('header_id', $pbHeader->id)
                    ->where('ted_level', 'H')
                    ->where('ted_type', 'Expense')
                    ->first();
                if ($headerExp) {
                    $headerExp->ted_amount -= $pbItem->header_exp_amount;
                    $headerExp->save();
                }

                $pbItem->attributes()->delete();
                $pbItem->itemDiscount()->delete();
                $pbItem->taxes()->delete();
                if ($pbItem->mrn_detail_id) {
                    $mrnDetail = MrnDetail::find($pbItem->mrn_detail_id);
                    if ($mrnDetail) {
                        $mrnDetail->purchase_bill_qty = $mrnDetail->purchase_bill_qty - $pbItem->accepted_qty;
                        $mrnDetail->save();
                    }
                }
                $pbItem->delete();
            }
            if ($pbHeader) {
                /*Update Po header*/
                $to_h_dis = $pbHeader->headerDiscount()->sum('ted_amount'); // total head dis
                $to_h_exp = $pbHeader->expenses()->sum('ted_amount'); // total head dis
                $totalTax = $pbHeader->total_taxes;
                $afterTaxAmntTotal = $pbHeader->total_expAssessment_amount;
                foreach ($pbHeader->items as $item) {
                    $taxAmnt = $pbHeader->total_item_amount - $item->discount_amount; // total taxable amount
                    $h_dis = ($item->accepted_qty * $item->rate - $item->discount_amount) / $taxAmnt * $to_h_dis;
                    $item->header_discount_amount = $h_dis;
                    $h_exp = ($item->accepted_qty * $item->rate - ($item->header_discount_amount + $item->discount_amount)) + $item->tax_value;
                    $final_h_exp = $h_exp / $h_exp * $to_h_exp;
                    $item->header_exp_amount = $final_h_exp;
                    $item->save();
                }

                foreach ($pbHeader->pb_ted()->where('ted_type', 'Expense')->where('ted_level', 'H')->get() as $pbHeader_ted) {
                    $pbHeader_ted->assesment_amount = $pbHeader->total_expAssessment_amount;
                    $pbHeader_ted->save();
                }

                $itemLevelTotalDis = $pbHeader->items()->sum('discount_amount');
                foreach ($pbHeader->pb_ted()->where('ted_type', 'Discount')->where('ted_level', 'H')->get() as $pbHeader_ted) {
                    $pbHeader_ted->assesment_amount = $pbHeader->total_item_value - $itemLevelTotalDis;
                    $pbHeader_ted->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Component deleted successfully.']);

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
        $purchaseReturn = PRHeader::with(['vendor', 'currency', 'items', 'book', 'expenses'])
            ->findOrFail($id);
        $shippingAddress = $purchaseReturn->shippingAddress;
        $billingAddress = $purchaseReturn->billingAddress;
        $buyerAddress = $purchaseReturn?->erpStore?->address;

        $totalItemValue = $purchaseReturn->total_item_amount ?? 0.00;
        $totalDiscount = $purchaseReturn->total_discount ?? 0.00;
        $totalTaxes = $purchaseReturn->total_taxes ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $purchaseReturn->expense_amount ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($purchaseReturn->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$purchaseReturn->document_status] ?? '';
        $taxes = PRTed::where('header_id', $purchaseReturn->id)
            ->where('ted_type', 'Tax')
            ->select('ted_type','ted_id','ted_name', 'ted_percentage', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assesment_amount) as total_assesment_amount'))
            ->groupBy('ted_name', 'ted_percentage')
            ->get();
        $sellerShippingAddress = $purchaseReturn->latestShippingAddress();
        $sellerBillingAddress = $purchaseReturn->latestBillingAddress();
        $eInvoice = $purchaseReturn->irnDetail()->first();

        // QrCode::format('png')->size(300)->generate($eInvoice->signed_qr_code, $qrCodePath);
        $qrCodeBase64 = EInvoiceHelper::generateQRCodeBase64($eInvoice->signed_qr_code);
        

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        
        $html = view('pdf.purchase-return', 
        [
            'pb' => $purchaseReturn,
            'user' => $user,
            'shippingAddress' => $shippingAddress,
            'buyerAddress' => $buyerAddress,
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
            'eInvoice' => $eInvoice,
            'qrCodeBase64' => $qrCodeBase64
        ]
        )->render();

    
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfPath = 'invoices/pdfs/invoice_' . $eInvoice->ack_no . '.pdf';
        Storage::disk('local')->put($pdfPath, $dompdf->output());

        $fileName = 'IRN-' . date('Y-m-d') . '.pdf';
        // return $dompdf->stream($fileName);

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Einvoice_' . $eInvoice->ack_no . '.pdf"');
    }

    # Handle calculation update
    public function updateCalculation($pbId)
    {
        $pb = PRHeader::find($pbId);
        if (!$pb) {
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
        $vendorShippingCountryId = @$pb->shippingAddress->country_id;
        $vendorShippingStateId = @$pb->shippingAddress->state_id;

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $firstAddress = $organization->addresses->first();
        $companyCountryId = $firstAddress->country_id;
        $companyStateId = $firstAddress->state_id;

        # Save Item level discount
        foreach ($pb->items as $pb_item) {
            $itemPrice = $pb_item->rate * $pb_item->accepted_qty;
            $totalItemAmnt = $totalItemAmnt + $itemPrice;
            $itemDis = $pb_item->itemDiscount()->sum('ted_amount');
            $pb_item->discount_amount = $itemDis;
            $pb_item->save();
        }
        # Save header level discount
        $totalItemValue = $pb->total_item_amount;
        $totalItemValueAfterTotalItemDisc = $pb->total_item_amount - $pb->items()->sum('discount_amount');
        $totalHeaderDiscount = $pb->total_header_disc_amount;

        foreach ($pb->items as $pb_item) {
            $itemPrice = $pb_item->rate * $pb_item->accepted_qty;
            $itemPriceAfterItemDis = $itemPrice - $pb_item->discount_amount;
            # Calculate header discount
            // Calculate and save header discount
            if ($totalItemValueAfterTotalItemDisc > 0 && $totalHeaderDiscount > 0) {
                $headerDis = ($itemPriceAfterItemDis / $totalItemValueAfterTotalItemDisc) * $totalHeaderDiscount;
            } else {
                $headerDis = 0;
            }
            $pb_item->header_discount_amount = $headerDis;

            # Calculate header expenses
            $priceAfterBothDis = $itemPriceAfterItemDis - $headerDis;
            $taxDetails = TaxHelper::calculateTax($pb_item->hsn_id, $priceAfterBothDis, $companyCountryId, $companyStateId, $vendorShippingCountryId, $vendorShippingStateId, 'sale');
            if (isset($taxDetails) && count($taxDetails) > 0) {
                $itemTax = 0;
                $cTaxDeIds = array_column($taxDetails, 'id');
                $existTaxIds = PRTed::where('detail_id', $pb_item->id)
                    ->where('ted_type', 'Tax')
                    ->pluck('ted_id')
                    ->toArray();

                $array1 = array_map('strval', $existTaxIds);
                $array2 = array_map('strval', $cTaxDeIds);
                sort($array1);
                sort($array2);

                if ($array1 != $array2) {
                    # Changes
                    PRTed::where("detail_id", $pb_item->id)
                        ->where('ted_type', 'Tax')
                        ->delete();
                }

                foreach ($taxDetails as $taxDetail) {
                    $itemTax += ((double) $taxDetail['tax_percentage'] / 100 * $priceAfterBothDis);

                    $ted = PRTed::firstOrNew([
                        'detail_id' => $pb_item->id,
                        'ted_id' => $taxDetail['id'],
                        'ted_type' => 'Tax',
                    ]);

                    $ted->header_id = $pb->id;
                    $ted->detail_id = $pb_item->id;
                    $ted->ted_type = 'Tax';
                    $ted->ted_level = 'D';
                    $ted->ted_id = $taxDetail['id'] ?? null;
                    $ted->ted_name = $taxDetail['tax_type'] ?? null;
                    $ted->assesment_amount = $pb_item->assessment_amount_total;
                    $ted->ted_percentage = $taxDetail['tax_percentage'] ?? 0.00;
                    $ted->ted_amount = ((double) $taxDetail['tax_percentage'] / 100 * $priceAfterBothDis) ?? 0.00;
                    $ted->applicability_type = $taxDetail['applicability_type'] ?? 'Collection';
                    $ted->save();
                }
                if ($itemTax) {
                    $pb_item->tax_value = $itemTax;
                    $pb_item->save();
                    $totalTaxAmnt = $totalTaxAmnt + $itemTax;
                }
            }
            $pb_item->save();
        }

        # Save expenses
        $totalValueAfterBothDis = $totalItemValueAfterTotalItemDisc - $totalHeaderDiscount;
        $headerExpensesTotal = $pb->expenses()->sum('ted_amount');

        if ($headerExpensesTotal) {
            foreach ($pb->items as $pb_item) {
                $itemPriceAterBothDis = ($pb_item->rate * $pb_item->accepted_qty) - $pb_item->header_discount_amount - $pb_item->discount_amount;
                $exp = $itemPriceAterBothDis / $totalValueAfterBothDis * $headerExpensesTotal;
                $pb_item->header_exp_amount = $exp;
                $pb_item->save();
            }
        } else {
            foreach ($pb->items as $pb_item) {
                $pb_item->header_exp_amount = 0.00;
                $pb_item->save();
            }
        }

        /*Update Calculation*/
        // dd($totalItemValue, $totalDiscValue, ($totalItemValue - $totalDiscValue), $totalTaxValue, (($totalItemValue - $totalDiscValue) + $totalTaxValue), $totalExpValue, (($totalItemValue - $totalDiscValue) + ($totalTaxValue + $totalExpValue)));
        $totalDiscValue = $pb->items()->sum('header_discount_amount') + $pb->items()->sum('discount_amount');
        $totalExpValue = $pb->items()->sum('header_exp_amount');
        $pb->total_item_amount = $totalItemAmnt;
        $pb->total_discount = $totalDiscValue;
        $pb->taxable_amount = ($totalItemAmnt - $totalDiscValue);
        $pb->total_taxes = $totalTaxAmnt;
        $pb->total_after_tax_amount = (($totalItemAmnt - $totalDiscValue) + $totalTaxAmnt);
        $pb->expense_amount = $totalExpValue;
        $totalAmount = (($totalItemAmnt - $totalDiscValue) + ($totalTaxAmnt + $totalExpValue));
        $pb->total_amount = $totalAmount;
        $pb->save();
    }

    # Remove discount item level
    public function removeDisItemLevel(Request $request)
    {
        DB::beginTransaction();
        try {
            $pTedId = $request->id;
            $ted = PRTed::find($pTedId);
            if ($ted) {
                $tedPoId = $ted->header_id;
                $ted->delete();
                $this->updateCalculation($tedPoId);
            }
            DB::commit();
            return response()->json(['status' => 200, 'message' => 'data deleted successfully.']);
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
            $ted = PRTed::find($pTedId);
            if ($ted) {
                $tedPoId = $ted->header_id;
                $ted->delete();
                $this->updateCalculation($tedPoId);
            }
            DB::commit();
            return response()->json(['status' => 200, 'message' => 'data deleted successfully.']);
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
            $ted = PRTed::find($pTedId);
            if ($ted) {
                $tedPoId = $ted->header_id;
                $ted->delete();
                $this->updateCalculation($tedPoId);
            }
            DB::commit();
            return response()->json(['status' => 200, 'message' => 'data deleted successfully.']);
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
            // dd($id);
            $pbHeader = PRHeader::find($id);
            if (!$pbHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $pbHeaderData = $pbHeader->toArray();
            unset($pbHeaderData['id']); // You might want to remove the primary key, 'id'
            $pbHeaderData['header_id'] = $pbHeader->id;
            $headerHistory = PRHeaderHistory::create($pbHeaderData);
            $headerHistoryId = $headerHistory->id;

            // Detail History
            $pbDetails = PRDetail::where('header_id', $pbHeader->id)->get();
            if (!empty($pbDetails)) {
                foreach ($pbDetails as $key => $detail) {
                    $pbDetailData = $detail->toArray();
                    unset($pbDetailData['id']); // You might want to remove the primary key, 'id'
                    $pbDetailData['detail_id'] = $detail->id;
                    $pbDetailData['header_history_id'] = $headerHistoryId;
                    $detailHistory = PRDetailHistory::create($pbDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $pbAttributes = PRItemAttribute::where('header_id', $pbHeader->id)
                        ->where('detail_id', $detail->id)
                        ->get();
                    if (!empty($pbAttributes)) {
                        foreach ($pbAttributes as $key1 => $attribute) {
                            $pbAttributeData = $attribute->toArray();
                            unset($pbAttributeData['id']); // You might want to remove the primary key, 'id'
                            $pbAttributeData['attribute_id'] = $attribute->id;
                            $pbAttributeData['header_history_id'] = $headerHistoryId;
                            $pbAttributeData['detail_history_id'] = $detailHistoryId;
                            $attributeHistory = PRItemAttributeHistory::create($pbAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Ted History
                    $pbTed = PRTed::where('header_id', $pbHeader->id)
                        ->where('detail_id', $detail->id)
                        ->where('ted_level', '=', 'D')
                        ->get();

                    if (!empty($pbTed)) {
                        foreach ($pbTed as $key4 => $extraAmount) {
                            $extraAmountData = $extraAmount->toArray();
                            unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                            $extraAmountData['pb_ted_id'] = $extraAmount->id;
                            $extraAmountData['header_history_id'] = $headerHistoryId;
                            $extraAmountData['detail_history_id'] = $detailHistoryId;
                            $extraAmountDataHistory = PRTedHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // PRTed Header History
            $pbTed = PRTed::where('header_id', $pbHeader->id)
                ->where('ted_level', '=', 'H')
                ->get();

            if (!empty($pbTed)) {
                foreach ($pbTed as $key4 => $extraAmount) {
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $extraAmountData['pb_ted_id'] = $extraAmount->id;
                    $extraAmountData['header_history_id'] = $headerHistoryId;
                    $extraAmountDataHistory = PRTedHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000, 99999);

            $revisionNumber = "PB" . $randNo;
            $pbHeader->revision_number += 1;
            $pbHeader->document_status = "draft";
            $pbHeader->save();

            /*Create document submit log*/
            if ($pbHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $pbHeader->series_id;
                $docId = $pbHeader->id;
                $remarks = $pbHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $pbHeader->approval_level;
                $revisionNumber = $pbHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType);
            }

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $pbHeader,
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

    // Validate Quantity
    public function validateQuantity(Request $request)
    {
        $mrnQty = 0;
        $prQty = 0;
        $inputQty = $request->qty;
        $availableQty = 0.00;

        $mrnDetail = MrnDetail::find($request->mrnDetailId);
        if($mrnDetail){
            $mrnQty = ($request->returnType == 'rejected') ? $mrnDetail->rejected_qty : $mrnDetail->accepted_qty;
            $prQty = ($request->returnType == 'rejected') ? $mrnDetail->pr_rejected_qty : $mrnDetail->pr_qty;
            if($mrnQty < $inputQty){
                return response() -> json([
                    'data' => array(
                        'error_message' => "Qty can not be greater than mrn quantity."
                    )
                ]);
            }
            $actualQtyDifference = ($mrnQty - $prQty);
            if($actualQtyDifference < $inputQty){
                $availableQty = $actualQtyDifference;
                return response() -> json([
                    'data' => array(
                        'error_message' => "You can add ".number_format($availableQty,2)." quantity as ".number_format($prQty,2)." quantity already used in mrn. and mrn quantity is ".number_format($mrnQty,2)."."
                    )
                ]);
            }
        }
        return response()->json(['data' => ['quantity' => $inputQty], 'status' => 200, 'message' => 'fetched']);
    }

    // Get MRN
    public function getMrn(Request $request)
    {
        $applicableBookIds = array();
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $qtyTypeRequired = $request->pr_qty_type ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $headerStoreId = $request->header_store_id ?? null;
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        // dd($applicableBookIds);
        $mrnItems = MrnDetail::where(function ($query) use ($seriesId, $applicableBookIds, $docNumber, $itemId, $vendorId, $qtyTypeRequired) {
            $query->whereHas('item');
            $query->whereHas('mrnHeader', function ($mrn) use ($seriesId, $applicableBookIds, $docNumber, $vendorId) {
                $mrn->withDefaultGroupCompanyOrg();
                $mrn->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                if ($seriesId) {
                    $mrn->where('book_id', $seriesId);
                } else {
                    if (count($applicableBookIds)) {
                        $mrn->whereIn('book_id', $applicableBookIds);
                    }
                }
                if ($docNumber) {
                    $mrn->where('document_number', $docNumber);
                }
                if ($vendorId) {
                    $mrn->where('vendor_id', $vendorId);
                }
            });

            if ($itemId) {
                $query->where('item_id', $itemId);
            }
            if($qtyTypeRequired && ($qtyTypeRequired == 'rejected')){
                $query->whereRaw('rejected_qty > pr_qty');
            } else{
                $query->whereRaw('accepted_qty > pr_qty');
            }
        })
        ->get();

        $html = view('procurement.purchase-return.partials.mrn-item-list', ['mrnItems' => $mrnItems, 'qtyTypeRequired' => $qtyTypeRequired])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit PI Item list
    public function processMrnItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $ids = json_decode($request->ids, true) ?? [];
        $qtyTypeRequired = $request->pr_qty_type ?? null;
        $vendor = null;
        $supplierInvoiceNumber = null;
        $supplierInvoiceDate = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $mrnItems = MrnDetail::whereIn('id', $ids)->get();
        $uniqueMrnIds = MrnDetail::whereIn('id', $ids)
            ->distinct()
            ->pluck('mrn_header_id')
            ->toArray();
        if(count($uniqueMrnIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time purchase return create from one MRN."]);
        }
        $mrnData = MrnHeader::whereIn('id', $uniqueMrnIds)->first();
        $mrnHeaders = MrnHeader::whereIn('id', $uniqueMrnIds)->get();
        $discounts = collect();
        $expenses = collect();

        foreach ($mrnHeaders as $mrn) {
            foreach ($mrn->headerDiscount as $headerDiscount) {
                if (!intval($headerDiscount->ted_percentage)) {
                    $tedPerc = (floatval($headerDiscount->ted_amount) / floatval($headerDiscount->assesment_amount)) * 100;
                    $headerDiscount['ted_percentage'] = $tedPerc;
                }
                $discounts->push($headerDiscount);
            }

            foreach ($mrn->expenses as $headerExpense) {
                if (!intval($headerExpense->ted_percentage)) {
                    $tedPerc = (floatval($headerExpense->ted_amount) / floatval($headerExpense->assesment_amount)) * 100;
                    $headerExpense['ted_percentage'] = $tedPerc;
                }
                $expenses->push($headerExpense);
            }
        }
        $groupedDiscounts = $discounts
            ->groupBy('ted_id')
            ->map(function ($group) {
                return $group->sortByDesc('ted_percentage')->first(); // Select the record with max `ted_perc`
            });
        $groupedExpenses = $expenses
            ->groupBy('ted_id')
            ->map(function ($group) {
                return $group->sortByDesc('ted_percentage')->first(); // Select the record with max `ted_perc`
            });
        $finalDiscounts = $groupedDiscounts->values()->toArray();
        $finalExpenses = $groupedExpenses->values()->toArray();
        $mrnIds = $mrnItems->pluck('mrn_header_id')->all();
        $vendorId = MrnHeader::whereIn('id', $mrnIds)->pluck('vendor_id')->toArray();
        $vendorId = array_unique($vendorId);
        // $erpStores = ErpStore::where('organization_id', $user->organization_id)
        //     ->orderBy('id', 'ASC')
        //     ->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        if (count($vendorId) && count($vendorId) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "You can not selected multiple vendor of MRN item at time."]);
        } else {
            $mrnHeader = MrnHeader::whereIn('id', $uniqueMrnIds)->first();
            $vendorId = $vendorId[0];
            $vendor = Vendor::find($vendorId);
            $vendor->billing = $vendor->latestBillingAddress();
            $vendor->shipping = $vendor->latestShippingAddress();
            $vendor->currency = $vendor->currency;
            $vendor->paymentTerm = $vendor->paymentTerm;
            $supplierInvoiceNumber = $mrnHeader->supplier_invoice_no ?? '';
            $supplierInvoiceDate = $mrnHeader->supplier_invoice_date ?? '';
        }
        $html = view('procurement.purchase-return.partials.mrn-item-row',
        [
            'mrnItems' => $mrnItems,
            'locations' => $locations,
            'qtyTypeRequired' => $qtyTypeRequired,
            ]
        )
        ->render();

        return response()->json(
            [
                'data' => [
                    'pos' => $html,
                    'vendor' => $vendor,
                    'finalDiscounts' => $finalDiscounts,
                    'finalExpenses' => $finalExpenses,
                    'supplierInvoiceNumber' => $supplierInvoiceNumber,
                    'supplierInvoiceDate' => $supplierInvoiceDate,
                    'mrnData' => $mrnData
                ],
                'status' => 200,
                'message' => "fetched!"
            ]
        );
    }

    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, $request->type ?? 'get');
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ]);
        }
    }

    public function postPR(Request $request)
    {
        $purchaseReturn = PRHeader::find($request->document_id);
        $eInvoice = $purchaseReturn?->irnDetail()->first();
        if (!$eInvoice) {
            $data = [
                'message' => 'Please generate IRN First.',
            ];
            return response()->json([
                'status' => 'error',
                'data' => $data
            ]);
        }
        try {
            // dd($request->all());
            DB::beginTransaction();
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, 'post');
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

    // Revoke Document
    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $mrn = PRHeader::find($request->id);
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

    // Maintain Stock Ledger
    private static function maintainStockLedger($pr)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $pr->items->pluck('id')->toArray();
        $data = InventoryHelper::settlementOfInventoryAndStock($pr->id, $detailIds, ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS, $pr->document_status);
        if(!empty($data['records'])){
            $itemLocations = PRItemLocation::where('header_id', $pr->id)
                ->whereIn('detail_id', $detailIds)
                ->delete();

            foreach($data['records'] as $key => $val){
                $itemLocation = new PRItemLocation;
                $itemLocation->header_id = @$val->issuedBy->document_header_id;
                $itemLocation->detail_id = @$val->issuedBy->document_detail_id;
                $itemLocation->store_id = @$val->store_id;
                $itemLocation->rack_id = @$val->rack_id;
                $itemLocation->shelf_id = @$val->shelf_id;
                $itemLocation->bin_id = @$val->bin_id;
                $itemLocation->quantity = @$val->total_receipt_qty;
                $itemLocation->inventory_uom_qty = @$val->total_receipt_qty;
                $itemLocation->save();
            }
        }
    }

    public function generateEInvoice(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $documentHeader = PRHeader::find($request->id);
            $documentDetails = PRDetail::where('header_id', $request->id)->get();
            $organization = Organization::where('id', $user->organization_id)->first();
            $organizationAddress = Address::with(['city', 'state', 'country'])
                ->where('addressable_id', $user->organization_id)
                ->where('addressable_type', Organization::class)
                ->first();
            $shippingAddress = $documentHeader->shippingAddress;
            $storeAddress = $documentHeader->store_address;
            $buyerAddress = $documentHeader?->erpStore?->address;
            $sellerShippingAddress = $documentHeader->latestShippingAddress();
            $sellerBillingAddress = $documentHeader->latestBillingAddress();
            // $checkSellerGstIn = EInvoiceHelper::validateGstNumber($organization?->gst_number);
            // if(!(is_string($checkSellerGstIn))){
            //     if(!$checkSellerGstIn['Status']){
            //         $errorMsg = "Seller : "; 
            //         if($checkSellerGstIn['errorMsg'] == "Requested data is not available"){
            //             $errorMsg = "Seller : "."Error: ". @$checkSellerGstIn['ErrorDetails'][0]['ErrorCode'].' - Invalid GST Number';    
            //         } else{
            //             $errorMsg = "Seller : "."Error: ". @$checkSellerGstIn['ErrorDetails'][0]['ErrorCode'].' -'.$checkSellerGstIn['ErrorDetails'][0]['ErrorMessage']; 
            //         }
            //         return response()->json([
            //             'status' => 'error',
            //             'message' => $errorMsg,
            //             'checkSellerGstIn' => $checkSellerGstIn,
            //         ], 422);
            //     }
            // }
            // $checkBuyerGstIn = EInvoiceHelper::validateGstNumber($documentHeader?->vendor->compliances->gstin_no);
            // if(!(is_string($checkBuyerGstIn))){
            //     if(!$checkBuyerGstIn['Status']){
            //         $errorMsg = "Buyer : "; 
            //         if($checkBuyerGstIn['ErrorDetails'][0]['ErrorMessage'] == "Requested data is not available"){
            //             $errorMsg = "Seller : "."Error: ". @$checkBuyerGstIn['ErrorDetails'][0]['ErrorCode'].' - Invalid GST Number';    
            //         } else{
            //             $errorMsg = "Seller : "."Error: ". @$checkBuyerGstIn['ErrorDetails'][0]['ErrorCode'].' -'.$checkBuyerGstIn['ErrorDetails'][0]['ErrorMessage']; 
            //         }
            //         return response()->json([
            //             'status' => 'error',
            //             'message' => $errorMsg,
            //             'checkBuyerGstIn' => $checkBuyerGstIn,
            //         ], 422);
            //     }
            // }

            // if(!(is_string($checkSellerGstIn))){
            //     if(!$checkSellerGstIn['Status']){
            //         $errorMsg = "Seller : "; 
            //         if($checkSellerGstIn['ErrorDetails'][0]['ErrorMessage'] == "Requested data is not available"){
            //             $errorMsg = "Seller : "."Error: ". @$checkSellerGstIn['ErrorDetails'][0]['ErrorCode'].' - Invalid GST Number';    
            //         } else{
            //             $errorMsg = "Seller : "."Error: ". @$checkSellerGstIn['ErrorDetails'][0]['ErrorCode'].' -'.$checkSellerGstIn['ErrorDetails'][0]['ErrorMessage']; 
            //         }
            //         return [
            //             'checkSellerGstIn' => $checkSellerGstIn,
            //             'errorMsg' => $errorMsg,
            //             'Status' => 0
            //         ];
            //     }
            // }

            $gstInvoiceType = EInvoiceHelper::getGstInvoiceType($documentHeader -> vendor_id, $shippingAddress -> country_id, $storeAddress -> country_id, 'vendor');
            if ($gstInvoiceType === EInvoiceHelper::B2B_INVOICE_TYPE) {
                $data = EInvoiceHelper::saveGstIn($documentHeader);
                if (isset($data) && $data['status'] == 'error') {
                    return response()->json([
                        'error' => 'error',
                        'message' => $data['message'],
                    ], 500);
                }
            }

            
            $generateInvoice = EInvoiceHelper::generateInvoice($documentHeader, $documentDetails);
            if(!$generateInvoice['Status']){
                return response()->json([
                    'status' => 'error',
                    'message' => "Error: ". @$generateInvoice['ErrorDetails'][0]['ErrorCode'].' -'.$generateInvoice['ErrorDetails'][0]['ErrorMessage'],
                ], 422);
            }
            // dd($generateInvoice['ErrorDetails'][0]['ErrorMessage']);
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
            return response() -> json([
                'status' => 'success',
                'results' => $generateInvoice,
                'message' => 'E-Invoice generated succesfully',
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response() -> json([
                'status' => 'error',
                'message' => $ex -> getMessage(),
            ]);
        }
    }

}
