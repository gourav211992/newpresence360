<?php
namespace App\Http\Controllers;

use App\Exports\PurchaseBillExport;
use DB;
use PDF;
use Auth;
use View;
use Session;
use Yajra\DataTables\DataTables;

use Illuminate\Http\Request;
use App\Http\Requests\PbRequest;
use App\Http\Requests\EditPbRequest;

use App\Models\PbTed;
use App\Models\PbHeader;
use App\Models\PbDetail;
use App\Models\PbItemLocation;
use App\Models\PbItemAttribute;

use App\Models\PbTedHistory;
use App\Models\PbHeaderHistory;
use App\Models\PbDetailHistory;
use App\Models\PbItemLocationHistory;
use App\Models\PbItemAttributeHistory;

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
use App\Models\ErpSaleOrder;
use App\Models\Organization;
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
use App\Models\ErpItem;
use App\Models\ErpPbDynamicField;
use App\Models\ErpVendor;
use App\Models\PRHeader;
use App\Services\PbService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Exceptions\HttpResponseException;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class PurchaseBillController extends Controller
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
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $orderType = ConstantHelper::PB_SERVICE_ALIAS;
        request() -> merge(['type' => $orderType]);
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = PbHeader::with(
                [
                    'items',
                    'vendor',
                    'erpStore',
                    'costCenters',
                    'currency'
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
                    $route = route('purchase-bill.edit', $row->id);
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
                ->addColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('vendor_name', function ($row) {
                    return $row->vendor ? $row->vendor?->company_name : 'N/A';
                })
                ->addColumn('currency', function ($row) {
                    return strval($row->currency?->short_name) ?? 'N/A';
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
        return view('procurement.purchase-bill.index', [
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
        $serviceAlias = ConstantHelper::PB_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)
            ->withDefaultGroupCompanyOrg()
            ->get();
        $materialReceipts = MrnHeader::with('vendor')
            ->where('status', ConstantHelper::ACTIVE)
            ->withDefaultGroupCompanyOrg()
            ->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        return view('procurement.purchase-bill.create', [
            'books' => $books,
            'vendors' => $vendors,
            'servicesBooks'=>$servicesBooks,
            'materialReceipts' => $materialReceipts,
            'locations'=>$locations
        ]);
    }

    # Purchase Bill store
    public function store(PbRequest $request)
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

            # Pb Header save
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

            $pb = new PbHeader();
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
            $pb->supplier_invoice_no = $request->supplier_invoice_no;
            $pb->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $pb->billing_to = $request->billing_id;
            $pb->ship_to = $request->shipping_id;
            $pb->billing_address = $request->billing_address;
            $pb->shipping_address = $request->shipping_address;
            $pb->cost_center_id = $request->cost_center_id;
            $pb->revision_number = 0;
            $document_number = $request->document_number ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_no;
            $regeneratedDocExist = PbHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
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
                    $so_id = null;
                    if (isset($component['mrn_detail_id']) && $component['mrn_detail_id']) {
                        $pbDetail = MrnDetail::find($component['mrn_detail_id']);
                        $mrn_detail_id = $pbDetail->id ?? null;
                        $mrnHeaderId = $component['mrn_header_id'];
                        if ($pbDetail) {
                            $pbDetail->purchase_bill_qty += floatval($component['accepted_qty']);
                            $pbDetail->save();
                            $so_id = $pbDetail->so_id;
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
                        'so_id' => $so_id,
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
                        'po_rate' => floatval($component['po_val']) ?? 0.00,
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
                        'basic_value' => $itemValue,
                        'item_variance' => $component['item_variance']
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

                    $pbDetail = new PbDetail;

                    $pbDetail->header_id = $pbItem['header_id'];
                    $pbDetail->mrn_detail_id = $pbItem['mrn_detail_id'];
                    $pbDetail->so_id = $pbItem['so_id'];
                    $pbDetail->item_id = $pbItem['item_id'];
                    $pbDetail->item_code = $pbItem['item_code'];
                    $pbDetail->hsn_id = $pbItem['hsn_id'];
                    $pbDetail->hsn_code = $pbItem['hsn_code'];
                    $pbDetail->uom_id = $pbItem['uom_id'];
                    $pbDetail->uom_code = $pbItem['uom_code'];
                    $pbDetail->order_qty = $pbItem['order_qty'];
                    $pbDetail->accepted_qty = $pbItem['accepted_qty'];
                    $pbDetail->rejected_qty = $pbItem['rejected_qty'];
                    $pbDetail->inventory_uom_id = $pbItem['inventory_uom_id'];
                    $pbDetail->inventory_uom_code = $pbItem['inventory_uom_code'];
                    $pbDetail->inventory_uom_qty = $pbItem['inventory_uom_qty'];
                    $pbDetail->rate = $pbItem['rate'];
                    $pbDetail->po_rate = $pbItem['po_rate'];
                    $pbDetail->basic_value = $pbItem['basic_value'];
                    $pbDetail->item_variance = $pbItem['item_variance'];
                    $pbDetail->discount_amount = $pbItem['discount_amount'];
                    $pbDetail->header_discount_amount = $pbItem['header_discount_amount'];
                    $pbDetail->header_exp_amount = $itemHeaderExp;
                    $pbDetail->tax_value = $pbItem['tax_value'];
                    $pbDetail->company_currency = $pbItem['company_currency_id'];
                    $pbDetail->group_currency = $pbItem['group_currency_id'];
                    $pbDetail->exchange_rate_to_group_currency = $pbItem['group_currency_exchange_rate'];
                    $pbDetail->remark = $pbItem['remark'];
                    $pbDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach ($pbDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $pbAttr = new PbItemAttribute;
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
                                $ted = new PbTed;
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
                                $ted = new PbTed;
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
                            $ted = new PbTed;
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
                            $ted = new PbTed;
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

                /*Update total in main header Pb*/
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
                $currentLevel = $pb->approval_level ?? 1;
                $revisionNumber = $pb->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($pb);
                $totalValue = $pb->total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);

            }

            $pb = PbHeader::find($pb->id);
            if ($request->document_status == 'submitted') {
                // $totalValue = $po->grand_total_amount ?? 0;
                // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                $pb->document_status = $approveDocument['approvalStatus'] ?? $pb->document_status;
            } else {
                $pb->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            /*Pb Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $pb->uploadDocuments($request->file('attachment'), 'pb', false);
            }
            $pb->save();

            $redirectUrl = '';
            if(($pb->document_status == ConstantHelper::APPROVED) || ($pb->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $pb->id . '/pdf');
            }
            $status = DynamicFieldHelper::saveDynamicFields(ErpPbDynamicField::class, $pb -> id, $request -> dynamic_field ?? []);
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

        $pb = PbHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        $totalItemValue = $pb->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($pb->series_id, $pb->document_status, $pb->id, $pb->total_amount, $pb->approval_level, $pb->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory($pb->series_id, $pb->id, $pb->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pb->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        return view('procurement.purchase-bill.view', [
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
        $serviceAlias = ConstantHelper::PB_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $user = Helper::getAuthenticatedUser();
        $pb = PbHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
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
        $view = 'procurement.purchase-bill.edit';
        if ($request->has('revisionNumber') && $request->revisionNumber != $pb->revision_number) {
            $pb = $pb->source;
            $pb = PbHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('header_id', $pb->header_id)
                ->first();
            $view = 'procurement.purchase-bill.view';
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
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $dynamicFieldsUI = $pb -> dynamicfieldsUi();
        return view($view, [
            'deliveryAddress'=> $deliveryAddress,
            'orgAddress'=> $orgAddress,
            'mrn' => $pb,
            'user' => $user,
            'books' => $books,
            'buttons' => $buttons,
            'vendors' => $vendors,
            'costCenters' => $costCenters,
            'docStatusClass' => $docStatusClass,
            'totalItemValue' => $totalItemValue,
            'revision_number' => $revision_number,
            'approvalHistory' => $approvalHistory,
            'locations'=> $locations,
            'erpStores' => $erpStores,
            'dynamicFieldsUI' => $dynamicFieldsUI
        ]);
    }

    # Pb Update
    public function update(EditPbRequest $request, $id)
    {
        $pb = PbHeader::find($id);
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
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            $currentStatus = $pb->document_status;
            $actionType = $request->action_type;

            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'PbHeader', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'PbDetail', 'relation_column' => 'header_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PbItemAttribute', 'relation_column' => 'detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PbTed', 'relation_column' => 'detail_id']
                ];
                // $a = Helper::documentAmendment($revisionData, $id);
                $this->amendmentSubmit($request, $id);
            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedPbItemIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            if (count($deletedData['deletedHeaderExpTedIds'])) {
                PbTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'])->delete();
            }

            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                PbTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemDiscTedIds'])) {
                PbTed::whereIn('id', $deletedData['deletedItemDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedPbItemIds'])) {
                $pbItems = PbDetail::whereIn('id', $deletedData['deletedPbItemIds'])->get();
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
            $pb->store_id = $request->header_store_id ?? '';
            $pb->cost_center_id = $request->cost_center_id ?? '';
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
                            $mrnDetail->purchase_bill_qty += floatval($component['accepted_qty']);
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

                    # Pb Detail Save
                    $pbDetail = PbDetail::find($component['detail_id'] ?? null) ?? new PbDetail;

                    $pbDetail->header_id = $pbItem['header_id'];
                    $pbDetail->mrn_detail_id = $pbItem['mrn_detail_id'];
                    $pbDetail->item_id = $pbItem['item_id'];
                    $pbDetail->item_code = $pbItem['item_code'];

                    $pbDetail->hsn_id = $pbItem['hsn_id'];
                    $pbDetail->hsn_code = $pbItem['hsn_code'];
                    $pbDetail->uom_id = $pbItem['uom_id'];
                    $pbDetail->uom_code = $pbItem['uom_code'];
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
                    $pbDetail->company_currency = $pbItem['company_currency_id'];
                    $pbDetail->group_currency = $pbItem['group_currency_id'];
                    $pbDetail->exchange_rate_to_group_currency = $pbItem['group_currency_exchange_rate'];
                    $pbDetail->remark = $pbItem['remark'];
                    $pbDetail->save();

                    #Save component Attr
                    foreach ($pbDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $pbAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $pbAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $pbAttr = PbItemAttribute::find($pbAttrId) ?? new PbItemAttribute;
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
                                $ted = PbTed::find(@$dis['id']) ?? new PbTed;
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
                            $ted = PbTed::find(@$tax['id']) ?? new PbTed;
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
                            $ted = PbTed::find($pbAmountId) ?? new PbTed;
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
                            $ted = PbTed::find($pbAmountId) ?? new PbTed;
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

                /*Update total in main header Pb*/
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
            $currentLevel = $pb->approval_level ?? 1;
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

            /*Pb Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $pb->uploadDocuments($request->file('attachment'), 'pb', false);
            }

            $pb->save();

            $redirectUrl = '';
            if(($pb->document_status == ConstantHelper::APPROVED) || ($pb->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $pb->id . '/pdf');
            }
            $status = DynamicFieldHelper::saveDynamicFields(ErpPbDynamicField::class, $pb -> id, $request -> dynamic_field ?? []);
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
                'data' => $pb,
                'redirectUrl' =>$redirectUrl
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

        $componentItem = json_decode($request->component_item, true) ?? [];
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();

        /*Check last tr in table mandatory*/
        if (isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                // return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.purchase-bill.partials.item-row', compact(['rowCount', 'costCenters']))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    // PO Item Rows
    public function mrnItemRows(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $item_ids = explode(',', $request->item_ids);
        $items = MrnDetail::whereIn('id', $item_ids)
            ->get();
        $costCenters = CostCenter::withDefaultGroupCompanyOrg()->get();
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
            'procurement.purchase-bill.partials.mrn-item-row',
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
        $pbDetailId = $request->pb_detail_id ?? null;
        $itemAttIds = [];
        $itemAttributeArray = [];
        if ($pbDetailId) {
            $detail = PbDetail::find($pbDetailId);
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
        $html = view('procurement.purchase-bill.partials.comp-attribute', compact('item', 'attributeGroups', 'rowCount', 'selectedAttr','itemAttributes'))->render();
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
        $html = view('procurement.purchase-bill.partials.add-disc-row', compact('tblRowCount', 'rowCount', 'disName', 'disAmount', 'disPerc'))->render();
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
        $item = Item::find($request->item_id);
        if (isset($item)) {
            $hsnId = $item->hsn_id;
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
            $html = view('procurement.purchase-bill.partials.item-tax', compact('taxDetails', 'rowCount', 'itemPrice'))->render();
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
            $html = view('procurement.purchase-bill.partials.edit-address-modal', compact('addresses', 'selectedAddress'))->render();
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
        if ($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
        }
        $remark = $request->remark ?? null;
        $mrn = MrnHeader::find($request->mrn_header_id);
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $html = view(
            'procurement.purchase-bill.partials.comp-item-detail',
            compact(
                'item',
                'mrn',
                'selectedAttr',
                'remark',
                'uomName',
                'qty',
                'specifications',
                'poItem',
                'mrnDetail'
            )
        )
        ->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    public function getMrnItemsByVendorId(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])
                // ->where('organization_id', $organization->id)
                ->find($request->vendor_id);
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
            $pbItems = PbDetail::with(['attributes'])->whereIn('id', $componentIds)->get();
            foreach ($pbItems as $pbItem) {
                $pbHeader = $pbItem->header;
                $totalItemValue = $pbHeader->total_item_amount - ($pbItem->accepted_qty * $pbItem->rate);
                $totalDiscValue = $pbHeader->total_discount - ($pbItem->discount_amount + $pbItem->header_discount_amount);
                $totalTaxableValue = ($totalItemValue - $totalDiscValue);
                $totalTaxes = $pbHeader->total_taxes - $pbItem->tax_value;
                $totalAfterTax = ($totalTaxableValue + $totalTaxes);
                $totalExpValue = $pbHeader->expense_amount - $pbItem->header_exp_amount;
                $totalAmount = ($totalAfterTax + $totalExpValue);

                $pbHeader->total_item_amount = $totalItemValue;
                $pbHeader->total_discount = $totalDiscValue;
                $pbHeader->taxable_amount = $totalTaxableValue;
                $pbHeader->total_taxes = $totalTaxes;
                $pbHeader->total_after_tax_amount = $totalAfterTax;
                $pbHeader->total_amount = $totalAmount;
                $pbHeader->save();

                $headerDic = PbTed::where('header_id', $pbHeader->id)
                    ->where('ted_level', 'H')
                    ->where('ted_type', 'Discount')
                    ->first();
                if ($headerDic) {
                    $headerDic->ted_amount -= $pbItem->header_discount_amount;
                    $headerDic->save();
                }

                $headerExp = PbTed::where('header_id', $pbHeader->id)
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
        $purchaseBill = PbHeader::with(['vendor', 'currency', 'items', 'book', 'expenses'])
            ->findOrFail($id);

        $shippingAddress = $purchaseBill->shippingAddress;

        $totalItemValue = $purchaseBill->total_item_amount ?? 0.00;
        $totalDiscount = $purchaseBill->total_discount ?? 0.00;
        $totalTaxes = $purchaseBill->total_taxes ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $purchaseBill->expense_amount ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($purchaseBill->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$purchaseBill->document_status] ?? '';
        $taxes = PbTed::where('header_id', $purchaseBill->id)
            ->where('ted_type', 'Tax')
            ->select('ted_type','ted_id','ted_name', 'ted_percentage', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assesment_amount) as total_assesment_amount'))
            ->groupBy('ted_name', 'ted_percentage')
            ->get();
        $sellerShippingAddress = $purchaseBill->latestShippingAddress();
        $sellerBillingAddress = $purchaseBill->latestBillingAddress();
        $buyerAddress = $purchaseBill?->erpStore?->address;

        $pdf = PDF::loadView(
            'pdf.purchase-bill',
            [
                'pb' => $purchaseBill,
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

        $fileName = 'Purchase-Bill-' . date('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    # Handle calculation update
    public function updateCalculation($pbId)
    {
        $pb = PbHeader::find($pbId);
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
                $existTaxIds = PbTed::where('detail_id', $pb_item->id)
                    ->where('ted_type', 'Tax')
                    ->pluck('ted_id')
                    ->toArray();

                $array1 = array_map('strval', $existTaxIds);
                $array2 = array_map('strval', $cTaxDeIds);
                sort($array1);
                sort($array2);

                if ($array1 != $array2) {
                    # Changes
                    PbTed::where("detail_id", $pb_item->id)
                        ->where('ted_type', 'Tax')
                        ->delete();
                }

                foreach ($taxDetails as $taxDetail) {
                    $itemTax += ((double) $taxDetail['tax_percentage'] / 100 * $priceAfterBothDis);

                    $ted = PbTed::firstOrNew([
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
            $ted = PbTed::find($pTedId);
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
            $ted = PbTed::find($pTedId);
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
            $ted = PbTed::find($pTedId);
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
            $pbHeader = PbHeader::find($id);
            if (!$pbHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $pbHeaderData = $pbHeader->toArray();
            unset($pbHeaderData['id']); // You might want to remove the primary key, 'id'
            $pbHeaderData['header_id'] = $pbHeader->id;
            $headerHistory = PbHeaderHistory::create($pbHeaderData);
            $headerHistoryId = $headerHistory->id;

            $vendorBillingAddress = $pbHeader->billingAddress ?? null;
            $vendorShippingAddress = $pbHeader->shippingAddress ?? null;

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
                $mediaFiles = $headerHistory->uploadDocuments($request->file('amend_attachment'), 'pb', false);
            }
            $headerHistory->save();

            // Detail History
            $pbDetails = PbDetail::where('header_id', $pbHeader->id)->get();
            if (!empty($pbDetails)) {
                foreach ($pbDetails as $key => $detail) {
                    $pbDetailData = $detail->toArray();
                    unset($pbDetailData['id']); // You might want to remove the primary key, 'id'
                    $pbDetailData['detail_id'] = $detail->id;
                    $pbDetailData['header_history_id'] = $headerHistoryId;
                    $detailHistory = PbDetailHistory::create($pbDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $pbAttributes = PbItemAttribute::where('header_id', $pbHeader->id)
                        ->where('detail_id', $detail->id)
                        ->get();
                    if (!empty($pbAttributes)) {
                        foreach ($pbAttributes as $key1 => $attribute) {
                            $pbAttributeData = $attribute->toArray();
                            unset($pbAttributeData['id']); // You might want to remove the primary key, 'id'
                            $pbAttributeData['attribute_id'] = $attribute->id;
                            $pbAttributeData['header_history_id'] = $headerHistoryId;
                            $pbAttributeData['detail_history_id'] = $detailHistoryId;
                            $attributeHistory = PbItemAttributeHistory::create($pbAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Ted History
                    $pbTed = PbTed::where('header_id', $pbHeader->id)
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
                            $extraAmountDataHistory = PbTedHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // PbTed Header History
            $pbTed = PbTed::where('header_id', $pbHeader->id)
                ->where('ted_level', '=', 'H')
                ->get();

            if (!empty($pbTed)) {
                foreach ($pbTed as $key4 => $extraAmount) {
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $extraAmountData['pb_ted_id'] = $extraAmount->id;
                    $extraAmountData['header_history_id'] = $headerHistoryId;
                    $extraAmountDataHistory = PbTedHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000, 99999);

            $revisionNumber = "PB" . $randNo;
            $pbHeader->revision_number += 1;
            // $pbHeader->document_status = "draft";
            // $pbHeader->save();

            /*Create document submit log*/
            if ($pbHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $pbHeader->series_id;
                $docId = $pbHeader->id;
                $remarks = $pbHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $pbHeader->approval_level ?? 1;
                $revisionNumber = $pbHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType);
                $pbHeader->document_status = $approveDocument['approvalStatus'];
            }
            $pbHeader->save();

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

    public function getMrn(Request $request)
    {
        $mrnData = '';
        $applicableBookIds = array();
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $storeId = $request->store_id ?? null;
        $itemId = $request->item_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $selected_mrn_ids = json_decode($request->selected_mrn_ids) ?? [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $mrnItems = MrnDetail::where(function ($query) use ($seriesId, $applicableBookIds, $docNumber, $itemId, $vendorId, $storeId, $selected_mrn_ids) {
            $query->whereHas('item');
            $query->whereHas('mrnHeader', function ($mrn) use ($seriesId, $applicableBookIds, $docNumber, $vendorId, $storeId) {
                $mrn->where('bill_to_follow', 'yes')
                    ->where('store_id', $storeId)
                    ->withDefaultGroupCompanyOrg();
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

            $query->whereRaw('(accepted_qty-pr_qty) > purchase_bill_qty');
        });

        if(count($selected_mrn_ids)) {
            $mrnData = MrnDetail::with('mrnHeader')->whereIn('id', $selected_mrn_ids)->first();
            $mrnItems->whereNotIn('id',$selected_mrn_ids);
        }
        $mrnItems = $mrnItems->get();

        $html = view('procurement.purchase-bill.partials.mrn-item-list', [
            'mrnItems' => $mrnItems,
            'mrnData' => $mrnData
        ])
        ->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit PI Item list
    public function processMrnItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $ids = json_decode($request->ids, true) ?? [];
        $vendor = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $mrnItems = MrnDetail::whereIn('id', $ids)->get();
        $uniqueMrnIds = MrnDetail::whereIn('id', $ids)
            ->distinct()
            ->pluck('mrn_header_id')
            ->toArray();
        if(count($uniqueMrnIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time purchase bill create from one MRN."]);
        }
        $mrnData = MrnHeader::whereIn('id', $uniqueMrnIds)->with('costCenters')->first();
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
        if (count($vendorId) && count($vendorId) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "You can not selected multiple vendor of MRN item at time."]);
        } else {
            $vendorId = $vendorId[0];
            $vendor = Vendor::find($vendorId);
            $vendor->billing = $vendor->latestBillingAddress();
            $vendor->shipping = $vendor->latestShippingAddress();
            $vendor->currency = $vendor->currency;
            $vendor->paymentTerm = $vendor->paymentTerm;
        }
        $html = view('procurement.purchase-bill.partials.mrn-item-row', ['mrnItems' => $mrnItems])->render();
        return response()->json(['data' => ['pos' => $html, 'vendor' => $vendor, 'finalDiscounts' => $finalDiscounts, 'finalExpenses' => $finalExpenses, 'mrnData' => $mrnData], 'status' => 200, 'message' => "fetched!"]);
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
                'error' => $ex->getMessage()
            ]);
        }
    }

    public function postPb(Request $request)
    {
        try {
            DB::beginTransaction();
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

    // Revoke Document
    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $mrn = PbHeader::find($request->id);
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


    // Purchase Bill Report
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
        $MRNHeaderIds = PbHeader::withDefaultGroupCompanyOrg()
                            ->distinct()
                            ->pluck('mrn_header_id');
        $MRNHeaders = MrnHeader::whereIn('id', $MRNHeaderIds)->get();
        $soIds = PbDetail::whereHas('header', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->distinct()
                ->pluck('so_id');

        $so = ErpSaleOrder::whereIn('id', $soIds)->get();
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;
        return view('procurement.purchase-bill.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'MRNHeaders', 'statusCss'));
    }

    public function getReportFilter(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $period = $request->query('period');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $mrnId = $request->query('mrnNo');
        $soId = $request->query('soNo');
        $vendorId = $request->query('vendor');
        $itemId = $request->query('item');
        $status = $request->query('status');
        $mCategoryId = $request->query('m_category');
        $mSubCategoryId = $request->query('m_subCategory');
        $mAttribute = $request->query('m_attribute');
        $mAttributeValue = $request->query('m_attributeValue');

        $query = PbHeader::query()
        ->withDefaultGroupCompanyOrg();

        if ($mrnId) {
            $query->where('mrn_header_id', $mrnId);
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
        'items.so', 'mrn'])
        ->where('organization_id', $user->organization_id);

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
            $mrnNo = '';
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

            if ($request->filled('mrn_no'))
            {
                $mrnData = MrnHeader::find($request->input('mrn_no'));
                $mrnNo = optional($mrnData)->document_number;
            }

            if ($request->filled('so_no'))
            {
                $soData = ErpSaleOrder::find($request->input('so_no'));
                $soNo = optional($soData)->document_number;
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
                'MRN No: ' . $mrnNo,
                'SO No: ' . $soNo,
                'Status:' . $status,
                'Category:' . $categoryName,
                'Sub Category' . $subCategoriesName,
            ];

            $fileName = 'purchase-bill.xlsx';
            $filePath = storage_path('app/public/purchase-bill/' . $fileName);
            $directoryPath = storage_path('app/public/purchase-bill');
            if($formattedstartDate && $formattedendDate)
            {
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Purchase Bill Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else{
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Purchase Bill Report' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }

            $remainingSpaces = $blankSpaces - count($filters) + 1;
            $filterHeader = array_merge($filters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new PurchaseBillExport($customHeader, $filterHeader, $headers, $data), \Maatwebsite\Excel\Excel::XLSX);

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
                $title = "Purchase Bill Report Generated";
                $heading = "Purchase Bill Report";

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
                                We hope this email finds you well. Please find your purchase bill report attached below.
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

    public function purchaseBillReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $pathUrl = route('purchase-bill.index');
        $orderType = ConstantHelper::PB_SERVICE_ALIAS;
        $purchaseBills = PbHeader::withDefaultGroupCompanyOrg()
            // ->where('document_type', $orderType)
            // ->bookViewAccess($pathUrl)
            ->withDraftListingLogic()
            ->orderByDesc('id');

        // Vendor Filter
        $purchaseBills = $purchaseBills->when($request->vendor, function ($vendorQuery) use ($request) {
            $vendorQuery->where('vendor_id', $request->vendor);
        });

        // PO No Filter
        $purchaseBills = $purchaseBills->when($request->mrn_no, function ($mrnQuery) use ($request) {
            $mrnQuery->where('mrn_header_id', $request->mrn_no);
        });

        // Document Status Filter
        $purchaseBills = $purchaseBills->when($request->status, function ($docStatusQuery) use ($request) {
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
        $purchaseBills = $purchaseBills->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
            $dateRanges = explode('to', $dateRange);
            if (count($dateRanges) == 2) {
                $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                $toDate = Carbon::parse(trim($dateRanges[1]))->format('Y-m-d');
                $dateRangeQuery->whereDate('document_date', ">=", $fromDate)->where('document_date', '<=', $toDate);
            }
        });

        // Item Id Filter
        // $purchaseBills = $purchaseBills->when($request->item_id, function ($itemQuery) use ($request) {
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

        $purchaseBills->with([
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
            'mrn'
        ]);


        $purchaseBills = $purchaseBills->get();
        $processedPurchaseBills = collect([]);

        foreach ($purchaseBills as $pb) {
            foreach ($pb->items as $pbItem) {
                $reportRow = new stdClass();

                // Header Details
                $header = $pbItem->header;
                $total_item_value = (($pbItem?->rate ?? 0.00) * ($pbItem?->accepted_qty ?? 0.00)) - ($pbItem?->discount_amount ?? 0.00);
                $reportRow->id = $pbItem->id;
                $reportRow->book_code = $header->book_code;
                $reportRow->document_number = $header->document_number;
                $reportRow->document_date = $header->document_date;
                $reportRow->mrn_no = !empty($header->mrn?->book_code) && !empty($header->mrn?->document_number)
                                    ? $header->mrn?->book_code . ' - ' . $header->mrn?->document_number
                                    : '';
                $reportRow->ge_no = $header->gate_entry_no;
                $reportRow->so_no = !empty($header->so?->book_code) && !empty($header->so?->document_number)
                                    ? $header->so?->book_code . ' - ' . $header->so?->document_number
                                    : '';
                $reportRow->lot_no = $header->lot_no;
                $reportRow->vendor_name = $header->vendor ?-> company_name;
                $reportRow->vendor_rating = null;
                $reportRow->category_name = $pbItem->item ?->category ?-> name;
                $reportRow->sub_category_name = $pbItem->item ?->category ?-> name;
                $reportRow->item_type = $pbItem->item ?->type;
                $reportRow->sub_type = null;
                $reportRow->item_name = $pbItem->item ?->item_name;
                $reportRow->item_code = $pbItem->item ?->item_code;

                // Amount Details
                $reportRow->receipt_qty = number_format($pbItem->order_qty, 2);
                $reportRow->accepted_qty = number_format($pbItem->accepted_qty, 2);
                $reportRow->rejected_qty = number_format($pbItem->rejected_qty, 2);
                $reportRow->pr_qty = number_format($pbItem->pr_qty, 2);
                $reportRow->pr_rejected_qty = number_format($pbItem->pr_rejected_qty, 2);
                $reportRow->purchase_bill_qty = number_format($pbItem->purchase_bill_qty, 2);
                $reportRow->store_name = $pbItem?->erpStore?->store_name;
                $reportRow->sub_store_name = $pbItem?->subStore?->name;
                $reportRow->rate = number_format($pbItem->rate);
                $reportRow->basic_value = number_format($pbItem->basic_value, 2);
                $reportRow->item_discount = number_format($pbItem->discount_amount, 2);
                $reportRow->header_discount = number_format($pbItem->header_discount_amount, 2);
                $reportRow->item_amount = number_format($total_item_value, 2);

                // Attributes UI
                // $attributesUi = '';
                // if (count($pbItem->item_attributes) > 0) {
                //     foreach ($pbItem->item_attributes as $pbAttribute) {
                //         $attrName = $pbAttribute->attribute_name;
                //         $attrValue = $pbAttribute->attribute_value;
                //         $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName : $attrValue </span>";
                //     }
                // } else {
                //     $attributesUi = 'N/A';
                // }
                // $reportRow->item_attributes = $attributesUi;

                // Document Status
                $reportRow->status = $header->document_status;
                $processedPurchaseBills->push($reportRow);
            }
        }

        return DataTables::of($processedPurchaseBills)
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
            ->rawColumns(['item_attributes', 'status'])
            ->make(true);
    }

}
