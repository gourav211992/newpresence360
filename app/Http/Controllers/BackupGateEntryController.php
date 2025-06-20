<?php
namespace App\Http\Controllers;

use App\Exports\GateEntryExport;
use DB;
use PDF;
use Yajra\DataTables\DataTables;

use Illuminate\Http\Request;
use App\Http\Requests\GateEntryRequest;
use App\Http\Requests\EditGateEntryRequest;

use App\Models\GateEntryHeader;
use App\Models\GateEntryDetail;
use App\Models\GateEntryAttribute;
use App\Models\GateEntryItemLocation;
use App\Models\GateEntryTed;
use App\Models\AlternateUOM;

use App\Models\VendorAsn;
use App\Models\VendorAsnItem;


use App\Models\GateEntryHeaderHistory;
use App\Models\GateEntryDetailHistory;
use App\Models\GateEntryAttributeHistory;
use App\Models\GateEntryItemLocationHistory;
use App\Models\GateEntryTedHistory;

use App\Models\Unit;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\ErpBin;
use App\Models\PoItem;
use App\Models\Address;
use App\Models\ErpRack;
use App\Models\ErpStore;
use App\Models\ErpShelf;
use App\Models\ErpAddress;
use App\Models\ErpSaleOrder;
use App\Models\Organization;
use App\Models\PurchaseOrder;
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
use App\Models\ErpGeDynamicField;
use App\Models\ErpItem;
use App\Models\ErpVendor;
use App\Models\JobOrder\JobOrder;
use App\Models\JobOrder\JoProduct;
use App\Services\MrnService;
use Carbon\Carbon;
use DateTime;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class BackupGateEntryController extends Controller
{
    protected $gatentryService;

    // public function __construct(MrnService $mrnService)
    // {
    //     $this->mrnService = $mrnService;
    // }
    // public function get_mrn_no($book_id)
    // {
    //     $data = Helper::generateVoucherNumber($book_id);
    //     return response()->json($data);
    // }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $orderType = ConstantHelper::GATE_ENTRY_SERVICE_ALIAS;
        request() -> merge(['type' => $orderType]);
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = GateEntryHeader::with(
                [
                    'items',
                    'vendor',
                    'erpStore',
                    'currency',
                    'purchaseOrder',
                    'jobOrder'
                ]
            )
            ->withDefaultGroupCompanyOrg()
            // ->withDraftListingLogic()
            ->bookViewAccess($parentUrl)
            ->where('company_id', $organization->company_id)
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $route = route('gate-entry.edit', $row->id);
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
                ->addColumn('reference_number', function ($row) {
                    if ($row->reference_type == 'po') {
                        return $row->po ? $row->po->book_code . ' - ' . $row->po->document_number : 'N/A';
                    } elseif ($row->reference_type == 'jo') {
                        return $row->jobOrder ? $row->jobOrder->book_code . ' - ' . $row->jobOrder->document_number : 'N/A';
                    } else {
                        return '';
                    }
                })
                ->editColumn('document_date', function ($row) {
                    return date('d/m/Y', strtotime($row->document_date)) ?? 'N/A';
                })
                ->addColumn('location', function ($row) {
                    return strval($row->erpStore?->store_name) ?? 'N/A';
                })
                ->addColumn('lot_no', function ($row) {
                    return strval($row->lot_no) ?? 'N/A';
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
        return view('procurement.gate-entry.index', [
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
        $locations = InventoryHelper::getAccessibleLocations('stock');
        return view('procurement.gate-entry.create', [
            'books'=>$books,
            'vendors' => $vendors,
            'locations'=>$locations,
            'servicesBooks'=>$servicesBooks,
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    # MRN store
    public function store(GateEntryRequest $request)
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
            $purchaseOrderId = null;
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

            $mrn = new GateEntryHeader();
            $mrn->fill($request->all());
            $mrn->store_id = $request->header_store_id;
            $mrn->organization_id = $organization->id;
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
            $mrn->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $mrn->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $mrn->eway_bill_no = $request->eway_bill_no ?? '';
            $mrn->consignment_no = $request->consignment_no ?? '';
            $mrn->transporter_name = $request->transporter_name ?? '';
            $mrn->vehicle_no = $request->vehicle_no ?? '';
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
            $regeneratedDocExist = GateEntryHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
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
            $mrn->final_remark = $request->remarks ?? null;

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
            # Store location address
            if($mrn?->erpStore)
            {
                $storeAddress  = $mrn?->erpStore->address;
                $storeLocation = $mrn->store_address()->firstOrNew();
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
                    $so_id = null;
                    if ($request->all()['reference_type'] == ConstantHelper::JO_SERVICE_ALIAS) {
                        if (isset($component['supplier_inv_detail_id']) && $component['supplier_inv_detail_id']) {
                            $supplierInvDetail =  JoProduct::find($component['supplier_inv_detail_id']);
                            $po_detail_id = $supplierInvDetail->id ?? null;
                            $purchaseOrderId = $supplierInvDetail->jo_id;
                            if ($supplierInvDetail) {
                                $supplierInvDetail->ge_qty += floatval($component['accepted_qty']);
                                $supplierInvDetail->save();
                            }
                            if ($supplierInvDetail->po_item_id) {
                                $poDetail =  JoProduct::find($supplierInvDetail->po_item_id);
                                $po_detail_id = $poDetail->id ?? null;
                                if ($poDetail) {
                                    $so_id = $poDetail->so_id;
                                    $poDetail->ge_qty += floatval($component['accepted_qty']);
                                    $poDetail->save();
                                }
                            }
                        } else {
                            if (isset($component['po_detail_id']) && $component['po_detail_id']) {
                                $poDetail =  JoProduct::find($component['po_detail_id']);
                                $po_detail_id = $poDetail->id ?? null;
                                $purchaseOrderId = $poDetail->jo_id;
                                if ($poDetail) {
                                    $poQtyDifference = ($poDetail->order_qty - $poDetail->ge_qty);
                                    if ($poQtyDifference < $component['accepted_qty']) {
                                        DB::rollBack();
                                        return response()->json([
                                            'message' => 'GE Qty can not be greater than jo qty.',
                                            'error' => "",
                                        ], 422);
                                    }
                                    $so_id = $poDetail->so_id;
                                    $poDetail->ge_qty += floatval($component['accepted_qty']);
                                    $poDetail->save();
                                }
                            }
                        }
                    } else {
                        if (isset($component['supplier_inv_detail_id']) && $component['supplier_inv_detail_id']) {
                            $supplierInvDetail =  PoItem::find($component['supplier_inv_detail_id']);
                            $po_detail_id = $supplierInvDetail->id ?? null;
                            $purchaseOrderId = $supplierInvDetail->purchase_order_id;
                            if ($supplierInvDetail) {
                                $supplierInvDetail->ge_qty += floatval($component['accepted_qty']);
                                $supplierInvDetail->save();
                            }
                            if ($supplierInvDetail->po_item_id) {
                                $poDetail =  PoItem::find($supplierInvDetail->po_item_id);
                                $po_detail_id = $poDetail->id ?? null;
                                if ($poDetail) {
                                    $so_id = $poDetail->so_id;
                                    $poDetail->ge_qty += floatval($component['accepted_qty']);
                                    $poDetail->save();
                                }
                            }
                        } else {
                            if (isset($component['po_detail_id']) && $component['po_detail_id']) {
                                $inputQty = 0.00;
                                $balanceQty = 0.00;
                                $availableQty = 0.00;
                                $poDetail =  PoItem::find($component['po_detail_id']);
                                $po_detail_id = $poDetail->id ?? null;
                                $purchaseOrderId = $poDetail->purchase_order_id;
                                if ($poDetail) {
                                    $poQtyDifference = ($poDetail->order_qty - $poDetail->ge_qty);
                                    if ($poQtyDifference < $component['accepted_qty']) {
                                        DB::rollBack();
                                        return response()->json([
                                            'message' => 'GE Qty can not be greater than po qty.',
                                            'error' => "",
                                        ], 422);
                                    }
                                    $so_id = $poDetail->so_id;
                                    $poDetail->ge_qty += floatval($component['accepted_qty']);
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
                        'header_id' => $mrn->id,
                        'purchase_order_item_id' => ($request->all()['reference_type'] == ConstantHelper::PO_SERVICE_ALIAS) ? $po_detail_id : null,
                        'job_order_item_id' => ($request->all()['reference_type'] == ConstantHelper::JO_SERVICE_ALIAS) ? $po_detail_id : null,
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
                        'basic_value' => $itemValue,
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

                    $GateEntryDetail = new GateEntryDetail;
                    $GateEntryDetail->header_id = $mrnItem['header_id'];
                    $GateEntryDetail->purchase_order_item_id = $mrnItem['purchase_order_item_id'];
                    $GateEntryDetail->job_order_item_id = $mrnItem['job_order_item_id'];
                    $GateEntryDetail->so_id = $mrnItem['so_id'];
                    $GateEntryDetail->item_id = $mrnItem['item_id'];
                    $GateEntryDetail->item_code = $mrnItem['item_code'];
                    $GateEntryDetail->hsn_id = $mrnItem['hsn_id'];
                    $GateEntryDetail->hsn_code = $mrnItem['hsn_code'];
                    $GateEntryDetail->uom_id = $mrnItem['uom_id'];
                    $GateEntryDetail->uom_code = $mrnItem['uom_code'];
                    $GateEntryDetail->accepted_qty = $mrnItem['accepted_qty'];
                    $GateEntryDetail->inventory_uom_id = $mrnItem['inventory_uom_id'];
                    $GateEntryDetail->inventory_uom_code = $mrnItem['inventory_uom_code'];
                    $GateEntryDetail->inventory_uom_qty = $mrnItem['inventory_uom_qty'];
                    $GateEntryDetail->store_id = $mrnItem['store_id'];
                    $GateEntryDetail->store_code = $mrnItem['store_code'];
                    $GateEntryDetail->rate = $mrnItem['rate'];
                    $GateEntryDetail->basic_value = $mrnItem['basic_value'];
                    $GateEntryDetail->discount_amount = $mrnItem['discount_amount'];
                    $GateEntryDetail->header_discount_amount = $mrnItem['header_discount_amount'];
                    $GateEntryDetail->header_exp_amount = $itemHeaderExp;
                    $GateEntryDetail->tax_value = $mrnItem['tax_value'];
                    // $GateEntryDetail->company_currency = $mrnItem['company_currency_id'];
                    // $GateEntryDetail->group_currency = $mrnItem['group_currency_id'];
                    // $GateEntryDetail->exchange_rate_to_group_currency = $mrnItem['group_currency_exchange_rate'];
                    $GateEntryDetail->remark = $mrnItem['remark'];
                    $GateEntryDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach($GateEntryDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $mrnAttr = new GateEntryAttribute;
                            $mrnAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $mrnAttr->header_id = $mrn->id;
                            $mrnAttr->detail_id = $GateEntryDetail->id;
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
                                $ted = new GateEntryTed;
                                $ted->header_id = $mrn->id;
                                $ted->detail_id = $GateEntryDetail->id;
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
                                $ted = new GateEntryTed;
                                $ted->header_id = $mrn->id;
                                $ted->detail_id = $GateEntryDetail->id;
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
                }

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $ted = new GateEntryTed;
                            $ted->header_id = $mrn->id;
                            $ted->detail_id = null;
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
                            $ted = new GateEntryTed;
                            $ted->header_id = $mrn->id;
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

                /*Update po header id in main header MRN*/
                if($request->all()['reference_type'] == ConstantHelper::JO_SERVICE_ALIAS)
                {
                    $mrn->job_order_id = $purchaseOrderId ?? null;
                    $mrn->reference_type = $request->all()['reference_type'] ?? null;
                    $mrn->save();
                }
                else
                {
                    $mrn->purchase_order_id = $purchaseOrderId ?? null;
                    $mrn->reference_type = $request->all()['reference_type'] ?? null;
                    $mrn->save();
                }

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
                $currentLevel = $mrn->approval_level ?? 1;
                $revisionNumber = $mrn->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($mrn);
                $totalValue = $mrn->total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $mrn->document_status = $approveDocument['approvalStatus'] ?? $mrn->document_status;
            } else {
                $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }

            // $mrn = GateEntryHeader::find($mrn->id);
            // if ($request->document_status == 'submitted') {
            //     // $totalValue = $po->grand_total_amount ?? 0;
            //     // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
            //     $mrn->document_status = $approveDocument['approvalStatus'] ?? $mrn->document_status;
            // } else {
            //     $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            // }
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

            $redirectUrl = '';
            if(($mrn->document_status == ConstantHelper::APPROVED) || ($mrn->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $mrn->id . '/pdf');
            }

            $status = DynamicFieldHelper::saveDynamicFields(ErpGeDynamicField::class, $mrn -> id, $request -> dynamic_field ?? []);
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
                'data' => $mrn,
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

        $mrn = GateEntryHeader::with([
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

        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();

        return view('procurement.gate-entry.view',
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
        $mrn = GateEntryHeader::with([
            'vendor',
            'currency',
            'items',
            'book',
            'purchaseOrder',
            'jobOrder',
        ])
        ->findOrFail($id);

        $totalItemValue = $mrn->items()->sum('basic_value');
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $revision_number = $mrn->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($mrn->book_id,$mrn->document_status , $mrn->id, $mrn->total_amount, $mrn->approval_level, $mrn->created_by ?? 0, $userType['type'], $revision_number);
        $revNo = $mrn->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $mrn->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($mrn->book_id, $mrn->id, $revNo, $mrn->total_amount);
        $view = 'procurement.gate-entry.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $mrn->revision_number) {
            $mrn = $mrn->source;
            $mrn = GateEntryHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('source_id', $mrn->source_id)
                ->first();
            $view = 'procurement.gate-entry.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[@$mrn->document_status] ?? '';
        $locations = InventoryHelper::getAccessibleLocations('stock');
        $store = $mrn->erpStore;
        $deliveryAddress = $store?->address?->display_address;
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;

        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $dynamicFieldsUI = $mrn -> dynamicfieldsUi();
        return view($view, [
            'deliveryAddress'=> $deliveryAddress,
            'orgAddress'=> $orgAddress,
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
            'erpStores' => $erpStores,
            'dynamicFieldsUI' => $dynamicFieldsUI
        ]);
    }

    # Bom Update
    public function update(EditGateEntryRequest $request, $id)
    {
        $mrn = GateEntryHeader::find($id);
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
                    ['model_type' => 'header', 'model_name' => 'GateEntryHeader', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'GateEntryDetail', 'relation_column' => 'header_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'GateEntryAttribute', 'relation_column' => 'detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'GateEntryItemLocation', 'relation_column' => 'detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'GateEntryTed', 'relation_column' => 'detail_id']
                ];
                // $a = Helper::documentAmendment($revisionData, $id);
                $this->amendmentSubmit($request, $id);
            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedMrnItemIds', 'deletedItemLocationIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            if (count($deletedData['deletedHeaderExpTedIds'])) {
                GateEntryTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
            }

            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                GateEntryTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemDiscTedIds'])) {
                GateEntryTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
            }

            if (count($deletedData['deletedItemLocationIds'])) {
                GateEntryItemLocation::whereIn('id',$deletedData['deletedItemLocationIds'])->delete();
            }

            if (count($deletedData['deletedMrnItemIds'])) {
                $mrnItems = GateEntryDetail::whereIn('id',$deletedData['deletedMrnItemIds'])->get();
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
            $mrn->store_id = $request->header_store_id;
            $mrn->gate_entry_date = $request->gate_entry_date ? date('Y-m-d', strtotime($request->gate_entry_date)) : '';
            $mrn->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $mrn->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $mrn->eway_bill_no = $request->eway_bill_no ?? '';
            $mrn->consignment_no = $request->consignment_no ?? '';
            $mrn->transporter_name = $request->transporter_name ?? '';
            $mrn->vehicle_no = $request->vehicle_no ?? '';
            $mrn->final_remark = $request->remarks ?? '';
            $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $mrn->reference_type = $request->reference_type;
            if ($mrn->reference_type == ConstantHelper::PO_SERVICE_ALIAS) {
                $mrn->purchase_order_id = $request->purchase_order_id;
                $mrn->job_order_id = null;
            } elseif ($mrn->reference_type == ConstantHelper::JO_SERVICE_ALIAS) {
                $mrn->job_order_id = $request->purchase_order_id;
                $mrn->purchase_order_id = null;
            }
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
            # Store location address
            if($mrn?->erpStore)
            {
                $storeAddress  = $mrn?->erpStore->address;
                $storeLocation = $mrn->store_address()->firstOrNew();
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
                        if($poDetail){
                            $qtyDifference = ($poDetail->ge_qty - floatval($component['accepted_qty']));
                            $poDetail->ge_qty += floatval($qtyDifference);
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
                        'header_id' => $mrn->id,
                        'purchase_order_item_id' => $po_detail_id,
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

                foreach($mrnItemArr as &$mrnItem) {
                    /*Header Level Item discount*/
                    $headerDiscount = 0;
                    $headerDiscount = ($mrnItem['taxable_amount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $mrnItem['taxable_amount'] - $headerDiscount; // after both discount
                    $mrnItem['header_discount_amount'] = $headerDiscount;
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

                    # Gate Entry Detail Save
                    $GateEntryDetail = GateEntryDetail::find($component['detail_id'] ?? null) ?? new GateEntryDetail;

                    if((isset($component['po_detail_id']) && $component['po_detail_id']) || (isset($GateEntryDetail->purchase_order_item_id) && $GateEntryDetail->purchase_order_item_id)) {
                        $poItem = PoItem::find($component['po_detail_id'] ?? $GateEntryDetail->purchase_order_item_id);
                        if(isset($poItem) && $poItem) {
                            if(isset($poItem->id) && $poItem->id) {
                                $orderQty = floatval($GateEntryDetail->accepted_qty);
                                $componentQty = floatval($component['accepted_qty']);
                                $qtyDifference = $poItem->order_qty - $orderQty + $componentQty;
                                if($qtyDifference) {
                                    $poItem->ge_qty = $qtyDifference;
                                }
                            } else {
                                $poItem->ge_qty += $component['qty'];
                            }
                            $poItem->save();
                        }
                    }

                    $GateEntryDetail->header_id = $mrnItem['header_id'];
                    $GateEntryDetail->purchase_order_item_id = $mrnItem['purchase_order_item_id'];
                    $GateEntryDetail->item_id = $mrnItem['item_id'];
                    $GateEntryDetail->item_code = $mrnItem['item_code'];
                    $GateEntryDetail->hsn_id = $mrnItem['hsn_id'];
                    $GateEntryDetail->hsn_code = $mrnItem['hsn_code'];
                    $GateEntryDetail->uom_id = $mrnItem['uom_id'];
                    $GateEntryDetail->uom_code = $mrnItem['uom_code'];
                    $GateEntryDetail->accepted_qty = $mrnItem['accepted_qty'];
                    $GateEntryDetail->inventory_uom_id = $mrnItem['inventory_uom_id'];
                    $GateEntryDetail->inventory_uom_code = $mrnItem['inventory_uom_code'];
                    $GateEntryDetail->inventory_uom_qty = $mrnItem['inventory_uom_qty'];
                    $GateEntryDetail->rate = $mrnItem['rate'];
                    $GateEntryDetail->basic_value = $mrnItem['basic_value'];
                    $GateEntryDetail->discount_amount = $mrnItem['discount_amount'];
                    $GateEntryDetail->header_discount_amount = $mrnItem['header_discount_amount'];
                    $GateEntryDetail->tax_value = $mrnItem['tax_value'];
                    $GateEntryDetail->header_exp_amount = $itemHeaderExp;
                    // $GateEntryDetail->company_currency = $mrnItem['company_currency_id'];
                    // $GateEntryDetail->group_currency = $mrnItem['group_currency_id'];
                    // $GateEntryDetail->exchange_rate_to_group_currency = $mrnItem['group_currency_exchange_rate'];
                    $GateEntryDetail->remark = $mrnItem['remark'];
                    $GateEntryDetail->save();

                    #Save component Attr
                    foreach($GateEntryDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $mrnAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                        $mrnAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $mrnAttr = GateEntryAttribute::find($mrnAttrId) ?? new GateEntryAttribute;
                        $mrnAttr->header_id = $mrn->id;
                        $mrnAttr->detail_id = $GateEntryDetail->id;
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
                                $ted = GateEntryTed::find($dis['id'] ?? null) ?? new GateEntryTed;
                                $ted->header_id = $mrn->id;
                                $ted->detail_id = $GateEntryDetail->id;
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
                            $ted = GateEntryTed::find(@$tax['id']) ?? new GateEntryTed;
                            $ted->header_id = $mrn->id;
                            $ted->detail_id = $GateEntryDetail->id;
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

                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    foreach($request->all()['disc_summary'] as $dis) {
                        if (isset($dis['d_amnt']) && $dis['d_amnt']) {
                            $mrnAmountId = @$dis['d_id'] ?? null;
                            $ted = GateEntryTed::find($mrnAmountId) ?? new GateEntryTed;
                            $ted->header_id = $mrn->id;
                            $ted->detail_id = null;
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
                            $ted = GateEntryTed::find($mrnAmountId) ?? new GateEntryTed;
                            $ted->header_id = $mrn->id;
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
            $currentLevel = $mrn->approval_level ?? 1;
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

            // if($mrn){
            //     $invoiceLedger = self::maintainStockLedger($mrn);
            // }

            $redirectUrl = '';
            if(($mrn->document_status == ConstantHelper::APPROVED) || ($mrn->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $mrn->id . '/pdf');
            }

            $status = DynamicFieldHelper::saveDynamicFields(ErpGeDynamicField::class, $mrn -> id, $request -> dynamic_field ?? []);
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
                'data' => $mrn,
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

    // Add Item Row
    public function addItemRow(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $item = json_decode($request->item,true) ?? [];
        $componentItem = json_decode($request->component_item,true) ?? [];
        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                // return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        // $erpStores = ErpStore::withDefaultGroupCompanyOrg()
        //     ->orderBy('id', 'ASC')
        //     ->get();
        $locations = InventoryHelper::getAccessibleLocations('stock');
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.gate-entry.partials.item-row',compact('rowCount', 'locations'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $detailItemId = $request->detail_id ?? null;
        $itemAttIds = [];
        $itemAttributeArray = [];
        if($detailItemId) {
            $detail = GateEntryDetail::find($detailItemId);
            if($detail) {
            $itemAttIds = collect($detail->attributes)->pluck('item_attribute_id')->toArray();
            $itemAttributeArray = $detail->item_attributes_array();
            }
        }
        $itemAttributes = collect();
        if(count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
            if(count($itemAttributes) < 1) {
                $itemAttributes = $item?->itemAttributes;
                $itemAttributeArray = $item->item_attributes_array();
            }
        } else {
            $itemAttributes = $item?->itemAttributes;
            $itemAttributeArray = $item->item_attributes_array();
        }

        $html = view('procurement.gate-entry.partials.comp-attribute',compact('item','rowCount','selectedAttr','itemAttributes'))->render();
        $hiddenHtml = '';
        foreach ($itemAttributes as $attribute) {
                $selected = '';
                foreach ($attribute->attributes() as $value){
                    if (in_array($value->id, $selectedAttr)){
                        $selected = $value->id;
                    }
                }
            $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
        }

    if(count($selectedAttr)) {
        foreach ($itemAttributeArray as &$group) {
            foreach ($group['values_data'] as $attribute) {
                if (in_array($attribute->id, $selectedAttr)) {
                    $attribute->selected = true;
                }
            }
        }
    }
        return response()->json(['data' => ['attr' => $item->itemAttributes->count(),'html' => $html, 'hiddenHtml' => $hiddenHtml, 'itemAttributeArray' => $itemAttributeArray], 'status' => 200, 'message' => 'fetched.']);
    }


    # Add discount row
    public function addDiscountRow(Request $request)
    {
        $tblRowCount = intval($request->tbl_row_count) ? intval($request->tbl_row_count) + 1 : 1;
        $rowCount = intval($request->row_count);
        $disName = $request->dis_name;
        $disPerc = $request->dis_percentage;
        $disAmount = $request->dis_amount;
        $html = view('procurement.gate-entry.partials.add-disc-row',compact('tblRowCount','rowCount','disName','disAmount','disPerc'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # get tax calcualte
    public function taxCalculation(Request $request)
    {
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
            $taxDetails = TaxHelper::calculateTax( $hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType,$document_date);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = floatval($request->price) ?? 0;
            $html = view('procurement.gate-entry.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
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

    /**
     * Store a newly created resource in storage.
     */
    public function getStoreRacks(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
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
            $html = view('procurement.gate-entry.partials.edit-address-modal',compact('addresses','selectedAddress'))->render();
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
        $purchaseOrder = JobOrder::find($request->purchase_order_id);
        $poDetail = JoProduct::find($request->po_detail_id ?? $request->supplier_inv_detail_id);
        $html = view(
            'procurement.gate-entry.partials.comp-item-detail',
            compact(
                'item',
                'purchaseOrder',
                'selectedAttr',
                'remark',
                'uomName',
                'qty',
                'headerId',
                'detailId',
                'specifications',
                'poDetail',
            )
        )->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    public function logs(Request $request, string $id)
    {
        $user = Helper::getAuthenticatedUser();

        $revisionNo = $request->revision_number ?? 0;
        $GateEntryHeader = GateEntryHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        $mrn = GateEntryHeaderHistory::with(['mrn'])
            ->where('revision_number', $revisionNo)
            ->where('header_id', $id)
            ->first();
        $totalItemValue = $mrn->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($mrn->series_id,$mrn->document_status , $mrn->id, $mrn->total_amount, $mrn->approval_level, $mrn->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory(@$mrn->mrn->series_id, @$mrn->mrn->id, @$mrn->mrn->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[@$mrn->mrn->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $mrnRevisionNumbers = GateEntryHeaderHistory::where('header_id', $id)->get();
        return view('procurement.gate-entry.logs', [
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
        InventoryHelper::isExistInventoryAndStock($itemId, $selectedAttr,  $itemStoreData);
        $item = Item::find($request->item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = intval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';
        if($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
            // $uomName = $alUom->uom->name ?? 'NA';
        }
        $remark = $request->remark ?? null;
        $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);
        $html = view('procurement.gate-entry.partials.comp-item-detail',compact('item','purchaseOrder', 'selectedAttr','remark','uomName','qty'))->render();
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
        $mrn = GateEntryHeader::with(['vendor', 'currency', 'items', 'book', 'expenses'])
            ->findOrFail($id);


        $shippingAddress = $mrn->shippingAddress;

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
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mrn->document_status] ?? '';
        $taxes = GateEntryTed::where('header_id', $mrn->id)
            ->where('ted_type', 'Tax')
            ->select('ted_type','ted_id','ted_name', 'ted_percentage', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assesment_amount) as total_assesment_amount'))
            ->groupBy('ted_name', 'ted_percentage')
            ->get();
        $sellerShippingAddress = $mrn->latestShippingAddress();
        $sellerBillingAddress = $mrn->latestBillingAddress();
        $buyerAddress = $mrn?->erpStore?->address;

        $pdf = PDF::loadView(
            'pdf.gate-entry',
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
                'imagePath' => $imagePath,
                'docStatusClass' => $docStatusClass,
                'taxes' => $taxes,
                'sellerShippingAddress' => $sellerShippingAddress,
                'sellerBillingAddress' => $sellerBillingAddress,
                'buyerAddress' => $buyerAddress
            ]
        );

        $fileName = 'Meterial-Receipt-' . date('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    # Submit Amendment
    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Header History
            $GateEntryHeader = GateEntryHeader::find($id);
            if(!$GateEntryHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $GateEntryHeaderData = $GateEntryHeader->toArray();
            unset($GateEntryHeaderData['id']); // You might want to remove the primary key, 'id'
            $GateEntryHeaderData['header_id'] = $GateEntryHeader->id;
            $GateEntryHeaderData['source_id'] = $GateEntryHeader->id;
            $headerHistory = GateEntryHeaderHistory::create($GateEntryHeaderData);
            $headerHistoryId = $headerHistory->id;

            $vendorBillingAddress = $GateEntryHeader->billingAddress ?? null;
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
                $mediaFiles = $headerHistory->uploadDocuments($request->file('amend_attachment'), 'ge', false);
            }
            $headerHistory->save();

            // Detail History
            $GateEntryDetails = GateEntryDetail::where('header_id', $GateEntryHeader->id)->get();
            if(!empty($GateEntryDetails)){
                foreach($GateEntryDetails as $key => $detail){
                    $GateEntryDetailData = $detail->toArray();
                    unset($GateEntryDetailData['id']); // You might want to remove the primary key, 'id'
                    $GateEntryDetailData['source_id'] = $detail->id;
                    $GateEntryDetailData['header_id'] = $headerHistoryId;
                    $detailHistory = GateEntryDetailHistory::create($GateEntryDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $GateEntryAttributes = GateEntryAttribute::where('header_id', $GateEntryHeader->id)
                        ->where('detail_id', $detail->id)
                        ->get();
                    if(!empty($GateEntryAttributes)){
                        foreach($GateEntryAttributes as $key1 => $attribute){
                            $GateEntryAttributeData = $attribute->toArray();
                            unset($GateEntryAttributeData['id']); // You might want to remove the primary key, 'id'
                            $GateEntryAttributeData['source_id'] = $attribute->id;
                            $GateEntryAttributeData['header_id'] = $headerHistoryId;
                            $GateEntryAttributeData['detail_id'] = $detailHistoryId;
                            $attributeHistory = GateEntryAttributeHistory::create($GateEntryAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Extra Amount Item History
                    $itemExtraAmounts = GateEntryTed::where('header_id', $GateEntryHeader->id)
                        ->where('detail_id', $detail->id)
                        ->where('ted_level', '=', 'D')
                        ->get();

                    if(!empty($itemExtraAmounts)){
                        foreach($itemExtraAmounts as $key4 => $extraAmount){
                            $extraAmountData = $extraAmount->toArray();
                            unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                            $extraAmountData['source_id'] = $extraAmount->id;
                            $extraAmountData['header_id'] = $headerHistoryId;
                            $extraAmountData['detail_id'] = $detailHistoryId;
                            $extraAmountDataHistory = GateEntryTedHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // Extra Amount Header History
            $GateEntryTeds = GateEntryTed::where('header_id', $GateEntryHeader->id)
                ->where('ted_level', '=', 'H')
                ->get();

            if(!empty($GateEntryTeds)){
                foreach($GateEntryTeds as $key4 => $extraAmount){
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $extraAmountData['source_id'] = $extraAmount->id;
                    $extraAmountData['header_id'] = $headerHistoryId;
                    $extraAmountDataHistory = GateEntryTedHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000,99999);

            $revisionNumber = "GE".$randNo;
            $GateEntryHeader->revision_number += 1;
            // $GateEntryHeader->status = "draft";
            // $GateEntryHeader->document_status = "draft";
            // $GateEntryHeader->save();

            /*Create document submit log*/
            if ($GateEntryHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $GateEntryHeader->series_id;
                $docId = $GateEntryHeader->id;
                $remarks = $GateEntryHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $GateEntryHeader->approval_level ?? 1;
                $revisionNumber = $GateEntryHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                $GateEntryHeader->document_status = $approveDocument['approvalStatus'];
            }
            $GateEntryHeader->save();

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $GateEntryHeader,
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
        $poQty = 0;
        $geQty = 0;
        $inputQty = $request->qty;
        $availableQty = 0.00;

        $poDetail = PoItem::find($request->poDetailId);
        if($poDetail){
            $poQty = $poDetail->order_qty;
            $geQty = $poDetail->ge_qty;

            $diffQty = $inputQty - $geQty;

            if($poQty  < $diffQty){
                return response() -> json([
                    'data' => array(
                        'error_message' => "Qty can not be greater than po quantity."
                    )
                ]);
            }

            $actualQtyDifference = ($poQty - $geQty);
            if($actualQtyDifference < $inputQty){
                $availableQty = $actualQtyDifference;
                return response() -> json([
                    'data' => array(
                        'error_message' => "You can add ".number_format($availableQty,2)." quantity as ".number_format($geQty,2)." quantity already used in po. and po quantity is ".number_format($poQty,2)."."
                    )
                ]);
            }
        }
        return response()->json(['data' => ['quantity' => $request->qty], 'status' => 200, 'message' => 'fetched']);
    }

    # Get PO Item List
    public function getPo(Request $request){
        // Initialize variables
        $documentDate = $request->document_date ?? null;
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $storeId = $request->store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $selected_po_ids = json_decode($request->selected_po_ids) ?? [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);

        $poData = '';
        $poItemIds = [];
        $poItems = PoItem::select(
                'erp_po_items.*',
                'erp_purchase_orders.id as po_id',
                'erp_purchase_orders.vendor_id as vendor_id',
                'erp_purchase_orders.book_id as book_id',
                'erp_purchase_orders.gate_entry_required as gate_entry_required',
                'erp_purchase_orders.supp_invoice_required as supp_invoice_required'
            )
            ->leftJoin('erp_purchase_orders', 'erp_purchase_orders.id', 'erp_po_items.purchase_order_id')
            ->whereIn('erp_purchase_orders.book_id', $applicableBookIds)
            ->where('erp_purchase_orders.gate_entry_required', 'yes')
            ->whereRaw('((order_qty - short_close_qty) > ge_qty)')
            ->whereHas('item', function($item){
                $item->where('type', 'Goods');
            });
        if ($request->type == 'create') {
            if (count($selected_po_ids)) {
                $poData = PoItem::with('po')->whereIn('id', $selected_po_ids)->first();
                $poItems->whereNotIn('erp_po_items.id', $selected_po_ids);
            }
        } else if ($request->type == 'edit') {
            if (count($selected_po_ids)) {
                $poData = PoItem::with('po')->whereIn('purchase_order_id', $selected_po_ids)->first();
                $poItems->whereIn('erp_po_items.purchase_order_id', $selected_po_ids);
            }
        }

        $poItems = $poItems->get();

        foreach ($poItems as $poItem) {
            if($poItem->supp_invoice_required == 'yes') {
                $siItem = PoItem::where('id', $poItem->id)
                    ->whereHas('po', function($po){
                        $po->where('type', 'supplier-invoice');
                    })
                    ->whereRaw('((order_qty - short_close_qty) > ge_qty)')
                    ->first();
                if($siItem){
                    $poItemIds[] = $siItem->id;
                }
            } else {
                $poItemIds[] = $poItem->id;
            }
        }

        // Query to get PO items with the required conditions
        $poItems = PoItem::with('attributes')->whereIn('id',$poItemIds)
            ->where(function ($query) use ($seriesId, $docNumber, $itemId, $vendorId, $storeId) {
            // Ensure item exists
            $query->whereHas('item', function($item){
                $item->where('type', 'Goods');
            });

            // Check POs
            $query->whereHas('po', function ($po) use ($seriesId, $docNumber, $vendorId, $storeId) {
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

                // Filter by store ID
                if ($storeId) {
                    $po->where('erp_purchase_orders.store_id', $storeId);
                }
            });

            // Filter by item ID if provided
            if ($itemId) {
                $query->where('item_id', $itemId);
            }

            // Ensure remaining quantity condition
            $query->whereRaw('((order_qty - short_close_qty) > ge_qty)');
        })->get();
        $html = view('procurement.gate-entry.partials.po-item-list', [
            'poItems' => $poItems,
            'poData' => $poData
        ])
        ->render();

        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);

    }


    # Get Job Order Item List
    public function getJo(Request $request){
        // Initialize variables
        $documentDate = $request->document_date ?? null;
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $storeId = $request->store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $selected_jo_ids = json_decode($request->selected_jo_ids) ?? [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);

        $joData = '';
        $joItemIds = [];
        $joItems = JoProduct::select(
                'erp_jo_products.*',
                'erp_job_orders.id as jo_id',
                'erp_job_orders.vendor_id as vendor_id',
                'erp_job_orders.book_id as book_id',
                'erp_job_orders.gate_entry_required as gate_entry_required',
                'erp_job_orders.supp_invoice_required as supp_invoice_required'
            )
            ->leftJoin('erp_job_orders', 'erp_job_orders.id', 'erp_jo_products.jo_id')
            ->whereIn('erp_job_orders.book_id', $applicableBookIds)
            // ->where('erp_job_orders.gate_entry_required', 'yes')
            ->whereRaw('((order_qty - short_close_qty) > ge_qty)')
            ->whereHas('item', function($item){
                $item->where('type', 'Goods');
            });

        if ($request->type == 'create') {
            if (count($selected_jo_ids)) {
                $joData = JoProduct::with('jo')->whereIn('id', $selected_jo_ids)->first();
                $joItems->whereNotIn('erp_jo_products.id',$selected_jo_ids);
            }
        } else if ($request->type == 'edit') {
            if (count($selected_jo_ids)) {
                $joData = JoProduct::with('jo')->whereIn('id', $selected_jo_ids)->first();
                $joItems->whereIn('erp_jo_products.jo_id', $selected_jo_ids);
            }
        }
        $joItems = $joItems->get();

        foreach ($joItems as $joItem) {
            if($joItem->supp_invoice_required == 'yes') {
                $siItem = JoProduct::where('id', $joItem->id)
                    ->whereHas('jo', function($jo){
                        $jo->where('type', 'supplier-invoice');
                    })
                    ->whereRaw('((order_qty - short_close_qty) > ge_qty)')
                    ->first();
                if($siItem){
                    $joItemIds[] = $siItem->id;
                }
            } else {
                $joItemIds[] = $joItem->id;
            }
        }

        // Query to get PO items with the required conditions
        $joItems = JoProduct::with('attributes')->whereIn('id',$joItemIds)
            ->where(function ($query) use ($seriesId, $docNumber, $itemId, $vendorId, $storeId) {
            // Ensure item exists
            $query->whereHas('item', function($item){
                $item->where('type', 'Goods');
            });

            // Check POs
            $query->whereHas('jo', function ($jo) use ($seriesId, $docNumber, $vendorId, $storeId) {
                // Filter by book_id (headerBookId)
                // Filter by series ID
                $jo->withDefaultGroupCompanyOrg();
                $jo->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                if($seriesId) {
                    $jo->where('erp_job_orders.book_id',$seriesId);
                }

                // Filter by document number
                if ($docNumber) {
                    $jo->where('erp_job_orders.document_number', $docNumber);
                }

                // Filter by vendor ID
                if ($vendorId) {
                    $jo->where('erp_job_orders.vendor_id', $vendorId);
                }

                // Filter by store ID
                if ($storeId) {
                    $jo->where('erp_job_orders.store_id', $storeId);
                }
            });

            // Filter by item ID if provided
            if ($itemId) {
                $query->where('item_id', $itemId);
            }

            // Ensure remaining quantity condition
            $query->whereRaw('((order_qty - short_close_qty) > ge_qty)');
        })->get();
        $html = view('procurement.gate-entry.partials.jo-item-list', [
            'joItems' => $joItems,
            'joData' => $joData
        ])
        ->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);

    }

    # Submit PI Item list
    public function processPoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $type = 'po';
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
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time gate entry can be created from one PO."]);
        }

        // $erpStores = ErpStore::withDefaultGroupCompanyOrg()
        //     ->orderBy('id', 'ASC')
        //     ->get();
        $locations = InventoryHelper::getAccessibleLocations('stock');
        $pos = PurchaseOrder::whereIn('id', $uniquePoIds)->get();
        $purchaseOrder = PurchaseOrder::whereIn('id', $uniquePoIds)->first();
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
            $vendor->billing = $vendor->addresses()->where(function($query) {
                $query->where('type', 'billing')->orWhere('type', 'both');
            })->latest()->first();
            $vendor->shipping = $vendor->addresses()->where(function($query) {
                $query->where('type', 'shipping')->orWhere('type', 'both');
            })->latest()->first();
            $vendor->currency = $vendor->currency;
            $vendor->paymentTerm = $vendor->paymentTerm;
        }
        $html = view('procurement.gate-entry.partials.po-item-row',
        [
            'poItems' => $poItems,
            'locations'=>$locations,
            'type' => $type
        ])
        ->render();

        return response()->json([
            'data' => [
                'pos' => $html,
                'vendor' => $vendor,
                'finalDiscounts' => $finalDiscounts,
                'finalExpenses' => $finalExpenses,
                'purchaseOrder' => $purchaseOrder
            ],
            'status' => 200,
            'message' => "fetched!"
        ]);
    }

    public function processJoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $type = 'jo';
        $ids = json_decode($request->ids,true) ?? [];
        $vendor = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $poItems = JoProduct::whereIn('id', $ids)->get();
        $uniquePoIds = JoProduct::whereIn('id', $ids)
                    ->distinct()
                    ->pluck('jo_id')
                    ->toArray();
        if(count($uniquePoIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time gate entry can be created from one JO."]);
        }

        // $erpStores = ErpStore::withDefaultGroupCompanyOrg()
        //     ->orderBy('id', 'ASC')
        //     ->get();
        $locations = InventoryHelper::getAccessibleLocations('stock');
        $pos = JobOrder::whereIn('id', $uniquePoIds)->get();
        $purchaseOrder = JobOrder::whereIn('id', $uniquePoIds)->first();
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

        $poIds = $poItems->pluck('jo_id')->all();
        $vendorId = JobOrder::whereIn('id',$poIds)->pluck('vendor_id')->toArray();
        $vendorId = array_unique($vendorId);
        if(count($vendorId) && count($vendorId) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "You can not selected multiple vendor of JO item at same time."]);
        } else {
            $vendorId = $vendorId[0];
            $vendor = Vendor::find($vendorId);
            $vendor->billing = $vendor->addresses()->where(function($query) {
                $query->where('type', 'billing')->orWhere('type', 'both');
            })->latest()->first();
            $vendor->shipping = $vendor->addresses()->where(function($query) {
                $query->where('type', 'shipping')->orWhere('type', 'both');
            })->latest()->first();
            $vendor->currency = $vendor->currency;
            $vendor->paymentTerm = $vendor->paymentTerm;
        }
        $html = view('procurement.gate-entry.partials.po-item-row',
        [
            'poItems' => $poItems,
            'locations'=>$locations,
            'type' => $type
        ])
        ->render();

        return response()->json([
            'data' => [
                'pos' => $html,
                'vendor' => $vendor,
                'finalDiscounts' => $finalDiscounts,
                'finalExpenses' => $finalExpenses,
                'purchaseOrder' => $purchaseOrder
            ],
            'status' => 200,
            'message' => "fetched!"
        ]);
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
            $data = FinancialPostingHelper::financeVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, $request->type);
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
            DB::beginTransaction();
            $data = FinancialPostingHelper::financeVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, $request->type);
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
            $mrn = GateEntryHeader::find($request->id);
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

    // Gate Entry Report
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
        $purchaseOrderIds = GateEntryHeader::withDefaultGroupCompanyOrg()
                            ->distinct()
                            ->pluck('purchase_order_id');
        $purchaseOrders = PurchaseOrder::whereIn('id', $purchaseOrderIds)->get();
        $soIds = GateEntryDetail::whereHas('gateEntryHeader', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->distinct()
                ->pluck('so_id');

        $so = ErpSaleOrder::whereIn('id', $soIds)->get();
        $gateEntry = GateEntryHeader::withDefaultGroupCompanyOrg()->get();
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;
        // $attributes = Attribute::get();
        return view('procurement.gate-entry.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'purchaseOrders', 'gateEntry', 'statusCss'));
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

        $query = GateEntryHeader::query()
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
        'items.so', 'po', 'items.erpStore']);

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
                'Gate Entry No: ' . $gateEntryNo,
                'SO No: ' . $soNo,
                'LOT No: ' . $lotNo,
                'Status:' . $status,
                'Category:' . $categoryName,
                'Sub Category' . $subCategoriesName,
            ];

            $fileName = 'gate-entry.xlsx';
            $filePath = storage_path('app/public/gate-entry/' . $fileName);
            $directoryPath = storage_path('app/public/gate-entry');
            if($formattedstartDate && $formattedendDate)
            {
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Gate Entry Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else{
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Gate Entry Report' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }

            $remainingSpaces = $blankSpaces - count($filters) + 1;
            $filterHeader = array_merge($filters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new GateEntryExport($customHeader, $filterHeader, $headers, $data), \Maatwebsite\Excel\Excel::XLSX);

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
                $title = "Gate Entry Report Generated";
                $heading = "Gate Entry Report";

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
                                We hope this email finds you well. Please find your gate entry report attached below.
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

    public function gateEntryReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $pathUrl = route('gate-entry.index');
        $orderType = ConstantHelper::GATE_ENTRY_SERVICE_ALIAS;
        $gateEntries = GateEntryHeader::withDefaultGroupCompanyOrg()
            // ->where('document_type', $orderType)
            // ->bookViewAccess($pathUrl)
            ->withDraftListingLogic()
            ->orderByDesc('id');

        // Vendor Filter
        $gateEntries = $gateEntries->when($request->vendor, function ($vendorQuery) use ($request) {
            $vendorQuery->where('vendor_id', $request->vendor);
        });

        // PO No Filter
        $gateEntries = $gateEntries->when($request->po_no, function ($poQuery) use ($request) {
            $poQuery->where('purchase_order_id', $request->po_no);
        });

        // Document Status Filter
        $gateEntries = $gateEntries->when($request->status, function ($docStatusQuery) use ($request) {
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
        $gateEntries = $gateEntries->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
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

        $gateEntries->with([
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
            'po',
            'items.erpStore'
        ]);

        $gateEntries = $gateEntries->get();
        $processedGateEntries = collect([]);

        foreach ($gateEntries as $gateEntry) {
            foreach ($gateEntry->items as $gateEntryItem) {
                $reportRow = new stdClass();

                // Header Details
                $header = $gateEntryItem->gateEntryHeader;
                $total_item_value = (($gateEntryItem?->rate ?? 0.00) * ($gateEntryItem?->accepted_qty ?? 0.00)) - ($gateEntryItem?->discount_amount ?? 0.00);
                $reportRow->id = $gateEntryItem->id;
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
                $reportRow->category_name = $gateEntryItem->item ?->category ?-> name;
                $reportRow->sub_category_name = $gateEntryItem->item ?->category ?-> name;
                $reportRow->item_type = $gateEntryItem->item ?->type;
                $reportRow->sub_type = null;
                $reportRow->item_name = $gateEntryItem->item ?->item_name;
                $reportRow->item_code = $gateEntryItem->item ?->item_code;

                // Amount Details
                $reportRow->receipt_qty = number_format($gateEntryItem->order_qty, 2);
                $reportRow->store_name = $gateEntryItem?->erpStore?->store_name;
                $reportRow->rate = number_format($gateEntryItem->rate);
                $reportRow->basic_value = number_format($gateEntryItem->basic_value, 2);
                $reportRow->item_discount = number_format($gateEntryItem->discount_amount, 2);
                $reportRow->header_discount = number_format($gateEntryItem->header_discount_amount, 2);
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
                $processedGateEntries->push($reportRow);
            }
        }

        return DataTables::of($processedGateEntries)
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
