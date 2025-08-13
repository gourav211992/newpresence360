<?php

namespace App\Http\Controllers\JobOrder;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\Helper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\ErpSaleOrder;
use App\Http\Requests\JoRequest;
use App\Models\Address;
use App\Models\Bom;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ErpAddress;
use App\Models\Item;
use App\Models\Organization;
use App\Models\JobOrder\JobOrder;
use App\Models\JobOrder\JobOrderMedia;
use App\Models\JobOrder\JobOrderTed;
use App\Models\TermsAndCondition;
use App\Models\Unit;
use App\Models\Vendor;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\ErpStore;
use App\Models\JobOrder\JoBomMapping;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoItemAttribute;
use App\Models\JobOrder\JoProduct;
use App\Models\JobOrder\JoProductDelivery;
use App\Models\PwoSoMapping;
use App\Models\State;
use App\Services\JobOrderService;

class JoController extends Controller
{
    # Po List
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $pos = JobOrder::withDefaultGroupCompanyOrg()
                    ->withDraftListingLogic()
                    ->with('vendor')
                    ->latest();
            return DataTables::of($pos)
            ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                return view('partials.action-dropdown', [
                    'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                    'displayStatus' => $row->display_status,
                    'row' => $row,
                    'actions' => [
                        [
                            'url' => fn($r) => route('jo.edit', ['type' => request()->route('type'), 'id' => $r->id]),
                            'icon' => 'edit-3',
                            'label' => 'View/ Edit Detail',
                        ]
                    ]
                ])->render();
            })
            ->addColumn('book_name', function ($row) {
                return $row->book ? $row->book?->book_code : 'N/A';
            })
            ->addColumn('sales_order', function ($row) {
                $saleReferences = ErpSaleOrder::whereIn('id', $row->so_id ?? [])
                ->get()
                ->map(function ($item) {
                    return strtoupper($item->book_code) . ' - ' . $item->document_number;
                })
                ->unique()
                ->implode(', ');
                return $saleReferences;
            })
            ->addColumn('store_location', function ($row) {
                return $row->store_location ? $row->store_location?->store_name : 'N/A';
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
                return $row?->joProducts?->count();
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
            ->addColumn('grand_total_amount', function ($row) {
                return number_format($row->grand_total_amount,2);
            })
            ->rawColumns(['document_status'])
            ->make(true);
        }
        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        return view('procurement.jo.index',['servicesBooks' => $servicesBooks]);
    }

    # Po create
    public function create()
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $termsAndConditions = TermsAndCondition::withDefaultGroupCompanyOrg()
                            ->where('status',ConstantHelper::ACTIVE)->get();
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Add New';
        $short_title = '';
        $reference_from_title = '';
        $serviceAlias = ConstantHelper::JO_SERVICE_ALIAS;
        $title = 'Job Order';
        $short_title = 'JO';
        $reference_from_title = 'PWO';
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $currencyName = $organization?->currency?->short_name ?? '';
        $jobOrderTypes = ConstantHelper::JOB_ORDER_TYPES;
        return view('procurement.jo.create', [
            'books'=> $books,
            'termsAndConditions' => $termsAndConditions,
            'title' => $title,
            'menu' => $menu,
            'menu_url' => $menu_url,
            'sub_menu' => $sub_menu,
            'short_title' => $short_title,
            'reference_from_title' => $reference_from_title,
            'locations' => $locations,
            'serviceAlias' => $serviceAlias,
            'currencyName' => $currencyName,
            'jobOrderTypes' => $jobOrderTypes
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
        $html = view('procurement.jo.partials.item-row',compact('rowCount'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }
    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $isPi = intval($request->isPi) ?? 0;
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $poItemId = $request->jo_item_id ?? null;
        $itemAttIds = [];
        $itemAttributeArray = [];
        if($poItemId) {
            $poItem = JoProduct::where('id',$poItemId)->where('item_id',$item->id??null)->first();
            if($poItem) {
                $itemAttIds = $poItem->attributes()->pluck('item_attribute_id')->toArray();
                $itemAttributeArray = $poItem->item_attributes_array();
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
            $itemAttributeArray = $item?->item_attributes_array();
        }
        $html = view('procurement.jo.partials.comp-attribute',compact('item','rowCount','selectedAttr','isPi','itemAttributes'))->render();
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
        if(count($selectedAttr)) {
            foreach ($itemAttributeArray as &$group) {
                foreach ($group['values_data'] as $attribute) {
                    if (in_array($attribute->id, $selectedAttr)) {
                        $attribute->selected = true;
                    }
                }
            }
        }
        return response()->json(['data' => ['attr' => $item?->itemAttributes->count() ?? 0,'html' => $html, 'hiddenHtml' => $hiddenHtml, 'itemAttributeArray' => $itemAttributeArray], 'status' => 200, 'message' => 'fetched.']);
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
        $document_date = $request->document_date ?? date('Y-m-d');
        $hsnId = null;
        $item = Item::find($request -> item_id);
        if (isset($item)) {
            $hsnId = $item -> hsn_id;
        } else {
            return response()->json(['error' => 'Invalid Item'], 500);
        }
        $transactionType = $request->input('transaction_type', 'purchase');
        if ($transactionType === "purchase") {
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
            $html = view('procurement.jo.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    # Get address on vendor change and set
    public function getAddress(Request $request)
    {
        $vendorId = $request?->id ?? null;
        $vendor = Vendor::withDefaultGroupCompanyOrg()
                ->with(['currency:id,name', 'paymentTerms:id,name'])
                ->find($vendorId);
        $currency = $vendor?->currency;
        $paymentTerm = $vendor?->paymentTerms;
        $vendorId = $vendor?->id;
        $documentDate = $request?->document_date;
        $vendorAddress = ErpAddress::where('addressable_id', $vendorId)
                    ->where('addressable_type', Vendor::class)
                    ->latest()
                    ->first();

        if (!$vendorAddress) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Address not found for '. $vendor?->company_name
                )
            ]);
        }
        if (!isset($vendor->currency_id)) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Currency not found for '. $vendor?->company_name
                )
            ]);
        }
        if (!isset($vendor->payment_terms_id)) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Payment Terms not found for '. $vendor?->company_name
                )
            ]);
        }
        $currencyData = CurrencyHelper::getCurrencyExchangeRates($vendor?->currency_id ?? 0, $documentDate ?? '');
        $storeId = $request?->store_id ?? null;
        $store = ErpStore::find($storeId);
        $locationAddress = $store?->address;

        return response()->json([
            'data' =>
                [
                    'vendor_address' => $vendorAddress, 
                    'location_address' => $locationAddress, 
                    'vendor' => $vendor, 
                    'paymentTerm' => $paymentTerm, 
                    'currency' => $currency, 
                    'currency_exchange' => $currencyData
                ], 
            'status' => 200, 
            'message' => 'fetched'
        ]);
    }

    # Purchase Order store
    public function store(JoRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->has('tnc') && strlen(strip_tags($request->tnc)) > 250) {
                return response()->json([
                    'message' => 'The terms and conditions cannnot be greater than 250 characters.',
                    'error' => 'tnc exceeds maximum length',
                ], 422);
            }
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
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
            # PO Header Save
            $po = new JobOrder;
            $po->organization_id = $organization->id;
            $po->group_id = $organization->group_id;
            $po->company_id = $organization->company_id;
            $po->store_id = $request->store_id;
            $po->job_order_type = $request->job_order_type;
            $po->book_id = $request->book_id;
            $po->book_code = $request->book_code;
            $document_number = $request->document_number ?? null;
            $po -> tnc = $request->tnc ?? null;
            $po->gate_entry_required = $parameters['gate_entry_required'][0] ?? 'no';
            $po->partial_delivery = $parameters['partial_delivery_allowed'][0] ?? 'no';
            /**/
            $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
            $regeneratedDocExist = JobOrder::withDefaultGroupCompanyOrg()
                                ->where('book_id',$request->book_id)
                                ->where('document_number',$document_number)
                                ->first();
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
            $po->document_number = $document_number;
            $po->document_date = $request->document_date;
            $po->reference_number = $request->reference_number;
            $po->vendor_id = $request->vendor_id;
            $po->vendor_code = $request->vendor_code;
            $po->billing_address = $request->billing_address_id;
            $po->shipping_address = $request->vendor_address_id;
            $po->currency_id = $request->currency_id;
            $currency = Currency::find($request->currency_id ?? null);
            $po->currency_code = $currency?->short_name;
            $po->document_status = $request->document_status;
            $po->remarks = $request->remarks ?? null;
            $po->payment_term_id = $request->payment_term_id;
            // $po->payment_term_code = $request->payment_term_code;
            $po->save();
            if($po?->vendor?->supplier_books?->count()) {
                $po->supp_invoice_required = 'yes';
                $po->save();
            }
            # Store Billing address
            JobOrderService::saveAddressDetails($po, 'billing', $po->bill_address ?? null);
            # Store Vendor address
            JobOrderService::saveAddressDetails($po, 'shipping', $po->ship_address ?? null);
            $storeAddress = null;
            if (!empty($request->delivery_address_id)) {
                $storeAddress = ErpAddress::find($request->delivery_address_id);
            } else {
                $storeAddress = (object) [
                    'address' => $request->delivery_address,
                    'country_id' => $request->delivery_country_id,
                    'state_id' => $request->delivery_state_id,
                    'city_id' => $request->delivery_city_id,
                    'pincode' => $request->delivery_pincode,
                    'phone' => null,
                    'fax_number' => null,
                ];
            }
            if(!$storeAddress) {
                $storeAddress = $po->store_location->address;
            }
            # Store Delivery address
            JobOrderService::saveAddressDetails($po, 'location', $storeAddress);

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
            if (isset($request->all()['components']) && count($request->all()['components'])) {
                $poItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $serviceItem = Item::find($component['sow_id'] ?? null);
                    if(!$serviceItem)
                    {
                        return response()->json([
                            'message' => 'Illegal Service Choice',
                            'error' => '',
                        ],422);
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
                        'key' => $c_key,
                        'so_id' => $component['so_id'] ?? null,
                        'jo_id' => $po->id,
                        'item_id' => $component['item_id'] ?? null,
                        'service_item_id' => $component['sow_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $item?->hsn?->id ?? null,
                        'hsn_code' => $item?->hsn?->code ?? null,
                        'uom_id' => $component['uom_id'] ?? null,
                        'uom_code' => $unit?->name ?? null,
                        'order_qty' => floatval($component['qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id,
                        'inventory_uom_code' => $inventory_uom_code,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'item_discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'expense_amount' => floatval($component['exp_amount_header']) ?? 0.00,
                        'tax_amount' => 0.00,
                        'remarks' => $component['remark'] ?? null,
                        'value_after_discount' => $itemValueAfterDiscount,
                        'item_value' => $itemValue,
                        'delivery_date' => $component['delivery_date'] ?? date('Y-m-d'),
                        'pwo_so_mapping_id' => $component['pwo_so_mapping_id'] ?? null,
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
                    $headerDiscount = 0;
                    $headerDiscount = ($poItem['value_after_discount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $poItem['value_after_discount'] - $headerDiscount;
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if($isTax) {
                        $itemTax = 0;
                        $itemPrice = ($poItem['item_value'] - $headerDiscount - $poItem['item_discount_amount']);
                        $shippingAddress = $po->ship_address;
                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($poItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'purchase');
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                if($taxDetail['applicability_type'] == 'collection') {
                                    $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                                } else {
                                    $itemTax -= ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                                }
                            }
                        }
                        $poItem['tax_amount'] = abs($itemTax);
                        $totalTax += $itemTax;
                    }
                }
                unset($poItem);
                foreach($poItemArr as $_key => $poItem) {
                    $_key = $poItem['key'] ?? $_key;
                    $component = $request->all()['components'][$_key] ?? [];
                    # Save Jo Item with Attribute
                    $joProduct = JobOrderService::saveJoProductWithAttributes($poItem, $component, $po->id);
                    #Save Componet Delivery
                    JobOrderService::saveJoProductDeliveries($joProduct, $component, $po->id);
                    #Save Componet Discount
                    JobOrderService::saveJoProductDiscounts($joProduct, $component, $poItem, $po->id);
                    #Save Componet item Tax
                    JobOrderService::saveJoProductTaxes($joProduct, $component, $poItem, $po->id);
                    #Pwo so mapping to JO
                    JobOrderService::syncPwoJoMapping($poItem,$joProduct);
                    # Job Order Bom Mapping
                    JobOrderService::mapJobOrderBom($po, $joProduct);
                }
                # Save Jo Item 
                JobOrderService::saveJoItems($po);
                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    JobOrderService::saveHeaderLevelDiscounts($request->all()['disc_summary'], $itemTotalValue, $itemTotalDiscount, $po->id);
                }
                /*Header level save Exp*/
                if(isset($request->all()['exp_summary'])) {
                    JobOrderService::saveHeaderLevelExpenses(
                        $request->input('exp_summary'),
                        $itemTotalValue,
                        $itemTotalDiscount,
                        $itemTotalHeaderDiscount,
                        $totalTax,
                        $po->id
                    );
                }
                /*Update total in main header PO*/
                if($itemTotalValue < ($itemTotalHeaderDiscount + $itemTotalDiscount)) {
                    DB::rollBack();
                    return response()->json([
                            'message' => "Item total can't be negative.",
                            'error' => "",
                        ], 422);
                }
                $po->total_item_value = $itemTotalValue ?? 0.00;
                $po->total_discount_value = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($po?->teds?->count()) {
                    $po->total_tax_value = abs($totalTax) ?? 0.00;
                }
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
            $po->org_currency_exg_rate = $request?->exchange_rate ?? 1;
            // $po->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $po->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $po->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $po->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $po->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $po->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $po->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $po->save();
            #Save Term & Condition
            JobOrderService::saveJobOrderTerms($po, $request);
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
                $totalValue = $po->grand_total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $po->document_status = $approveDocument['approvalStatus'] ?? $po->document_status;
            } else {
                $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            $po->save();
            /*Po Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'jo', false);
            }
            $redirectUrl = '';
            if($po->document_status == ConstantHelper::APPROVED) {
                $redirectUrl = route('jo.generate-pdf', $po->id);
            }
            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $po,
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

    # Purchase Order store
    public function update(JoRequest $request, $id)
    {
        if ($request->has('tnc') && strlen(strip_tags($request->tnc)) > 250) {
            return response()->json([
                'message' => 'The terms and conditions cannnot be greater than 250 characters.',
                'error' => 'terms_data exceeds maximum length',
            ], 422);
        }
        $po = JobOrder::find($id);
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
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
                // $revisionData = [
                //     ['model_type' => 'header', 'model_name' => 'JobOrder', 'relation_column' => ''],
                //     ['model_type' => 'detail', 'model_name' => 'PoItem', 'relation_column' => 'jo_id'],
                //     ['model_type' => 'detail', 'model_name' => 'PoTerm', 'relation_column' => 'jo_id'],
                //     ['model_type' => 'sub_detail', 'model_name' => 'PoItemAttribute', 'relation_column' => 'jo_item_id'],
                //     ['model_type' => 'sub_detail', 'model_name' => 'PoItemDelivery', 'relation_column' => 'jo_item_id'],
                //     ['model_type' => 'sub_detail', 'model_name' => 'JobOrderTed', 'relation_column' => 'jo_item_id']
                // ];
                // $a = Helper::documentAmendment($revisionData, $id);
            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedPiItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
            $deletedData = [];
            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }
            if (count($deletedData['deletedHeaderExpTedIds'])) {
                JobOrderTed::whereIn('id',$deletedData['deletedHeaderExpTedIds'])->delete();
            }
            if (count($deletedData['deletedHeaderDiscTedIds'])) {
                JobOrderTed::whereIn('id',$deletedData['deletedHeaderDiscTedIds'])->delete();
            }
            if (count($deletedData['deletedItemDiscTedIds'])) {
                JobOrderTed::whereIn('id',$deletedData['deletedItemDiscTedIds'])->delete();
            }
            if (count($deletedData['deletedDelivery'])) {
                JoProductDelivery::whereIn('id',$deletedData['deletedDelivery'])->delete();
            }
            if (count($deletedData['deletedAttachmentIds'])) {
                $medias = JobOrderMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
                foreach ($medias as $media) {
                    if ($request->document_status == ConstantHelper::DRAFT) {
                        Storage::delete($media->file_name);
                    }
                    $media->delete();
                }
            }
            $ctr = 0;
            if (count($deletedData['deletedPiItemIds'])) {
                JoItemAttribute::where('jo_id', $po->id)->delete();
                JoItem::where('jo_id', $po->id)->delete();
                JoBomMapping::where('jo_id', $po->id)->delete();

                $poItems = JoProduct::whereIn('id',$deletedData['deletedPiItemIds'])->get();
                foreach($poItems as $poItem) {
                    $poItem->teds()->delete();
                    $poItem->productDelivery()->delete();
                    $poItem->attributes()->delete();
                    if($poItem?->pwoSoMapping) {
                        $poItem->pwoSoMapping->jo_qty -= $poItem?->order_qty;
                        $poItem->pwoSoMapping->save(); 
                    }
                    $poItem->delete();
                }
                $ctr++;
            }
            # Bom Header save
            $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $po->remarks = $request->remarks ?? null;
            $po->tnc = $request->tnc ?? null;
            $po->payment_term_id = $request->payment_term_id;
            $po->payment_term_code = $request->payment_term_code;
            $po->store_id = $request->store_id;
            $po->save();

            # Store Billing address
            JobOrderService::saveAddressDetails($po, 'billing', $po->bill_address ?? null);
            # Store Vendor address
            JobOrderService::saveAddressDetails($po, 'shipping', $po->ship_address ?? null);
            $storeAddress = null;
            if (!empty($request->delivery_address_id)) {
                $storeAddress = ErpAddress::find($request->delivery_address_id);
            } else {
                $storeAddress = (object) [
                    'address' => $request->delivery_address,
                    'country_id' => $request->delivery_country_id,
                    'state_id' => $request->delivery_state_id,
                    'city_id' => $request->delivery_city_id,
                    'pincode' => $request->delivery_pincode,
                    'phone' => null,
                    'fax_number' => null,
                ];
            }
            if(!$storeAddress) {
                $storeAddress = $po->store_location->address;
            }
            # Store Delivery address
            JobOrderService::saveAddressDetails($po, 'location', $storeAddress);
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
            if (isset($request->all()['components']) && count($request->all()['components'])) {
                $poItemArr = [];
                $totalValueAfterDiscount = 0;
                $itemTotalValue = 0;
                $itemTotalDiscount = 0;
                $itemTotalHeaderDiscount = 0;
                $itemValueAfterDiscount = 0;
                $totalItemValueAfterDiscount = 0;
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
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
                        'key' => $c_key,
                        'so_id' => $component['so_id'] ?? null,
                        'jo_id' => $po->id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $item?->hsn?->id ?? null,
                        'hsn_code' => $item?->hsn?->code ?? null,
                        'uom_id' => $component['uom_id'] ?? null,
                        'uom_code' => $unit?->name ?? null,
                        'order_qty' => floatval($component['qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id,
                        'inventory_uom_code' => $inventory_uom_code,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'item_discount_amount' => floatval($component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'expense_amount' => floatval($component['exp_amount_header']) ?? 0.00,
                        'tax_amount' => 0.00,
                        'remarks' => $component['remark'] ?? null,
                        'value_after_discount' => $itemValueAfterDiscount,
                        'item_value' => $itemValue,
                        'delivery_date' => $component['delivery_date'] ?? date('Y-m-d'),
                        'pwo_so_mapping_id' => $component['pwo_so_mapping_id'] ?? null,
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
                    $headerDiscount = 0;
                    $headerDiscount = ($poItem['value_after_discount'] / $totalValueAfterDiscount) * $totalHeaderDiscount;
                    $valueAfterHeaderDiscount = $poItem['value_after_discount'] - $headerDiscount;
                    $poItem['header_discount_amount'] = $headerDiscount;
                    $itemTotalHeaderDiscount += $headerDiscount;
                    if($isTax) {
                        $itemTax = 0;
                        $itemPrice = ($poItem['item_value'] - $headerDiscount - $poItem['item_discount_amount']);
                        $shippingAddress = $po->ship_address;
                        $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                        $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                        $taxDetails = TaxHelper::calculateTax($poItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request->shipping_country_id, $partyStateId ?? $request->shipping_state_id, 'purchase');
                        if (isset($taxDetails) && count($taxDetails) > 0) {
                            foreach ($taxDetails as $taxDetail) {
                                if($taxDetail['applicability_type'] == 'collection') {
                                    $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                                } else {
                                    $itemTax -= ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount);
                                }
                            }
                        }
                        $poItem['tax_amount'] = abs($itemTax);
                        $totalTax += $itemTax;
                    }
                }
                unset($poItem);
                foreach($poItemArr as $_key => $poItem) {
                    # MoProductDetail
                    $_key = $poItem['key'] ?? $_key;
                    $component = $request->all()['components'][$_key] ?? [];
                    if(isset($component['jo_product_id']) && $component['jo_product_id']) {
                        $joProduct = JoProduct::where('id', $component['jo_product_id'])->first();
                        if(intval($joProduct->inventory_uom_id) == intval($component['uom_id']) && intval($component['item_id']) == intval($joProduct->item_id)) {
                            continue;
                        }
                    }
                    $ctr++;
                    # Save Jo Item with Attribute
                    $joProduct = JobOrderService::saveJoProductWithAttributes($poItem, $component, $po->id);
                    #Save Componet Delivery
                    JobOrderService::saveJoProductDeliveries($joProduct, $component, $po->id);
                    #Save Componet Discount
                    JobOrderService::saveJoProductDiscounts($joProduct, $component, $poItem, $po->id);
                    #Save Componet item Tax
                    JobOrderService::saveJoProductTaxes($joProduct, $component, $poItem, $po->id);
                    #Pwo so mapping to JO
                    JobOrderService::syncPwoJoMapping($poItem,$joProduct);
                }
                # Save Jo Item 
                if($ctr) {
                    JoItemAttribute::where('jo_id', $po->id)->delete();
                    JoItem::where('jo_id', $po->id)->delete();      
                    JoBomMapping::where('jo_id', $po->id)->delete();
                    foreach($po->joProducts as $joProduct) {
                        JobOrderService::mapJobOrderBom($po, $joProduct);
                    }
                    JobOrderService::saveJoItems($po);
                }
                /*Header level save discount*/
                if(isset($request->all()['disc_summary'])) {
                    JobOrderService::saveHeaderLevelDiscounts($request->all()['disc_summary'], $itemTotalValue, $itemTotalDiscount, $po->id);
                }
                /*Header level save Exp*/
                if(isset($request->all()['exp_summary'])) {
                    JobOrderService::saveHeaderLevelExpenses(
                        $request->input('exp_summary'),
                        $itemTotalValue,
                        $itemTotalDiscount,
                        $itemTotalHeaderDiscount,
                        $totalTax,
                        $po->id
                    );
                }
                /*Update total in main header PO*/
                if($itemTotalValue < ($itemTotalHeaderDiscount + $itemTotalDiscount)) {
                    DB::rollBack();
                    return response()->json([
                            'message' => "Item total can't be negative.",
                            'error' => "",
                        ], 422);
                }
                $po->total_item_value = $itemTotalValue ?? 0.00;
                $po->total_discount_value = ($itemTotalHeaderDiscount + $itemTotalDiscount) ?? 0.00;
                if($po?->teds?->count()) {
                    $po->total_tax_value = abs($totalTax) ?? 0.00;
                }
                $po->total_expense_value =  $totalHeaderExpense ?? 0.00;
                $po->save();
            } else {
                if($request->document_status == ConstantHelper::SUBMITTED) {
                    DB::rollBack();
                    return response()->json([
                            'message' => 'Please add atleast one row in component table.',
                            'error' => "",
                        ], 422);
                }
                if($po->fresh()->joProducts->isEmpty()) {
                    $po->total_expense_value = 0;
                    $po->total_tax_value = 0;
                    $po->total_discount_value = 0;
                    $po->total_item_value = 0;
                }
            }

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($po->vendor->currency_id, $po->document_date);
            $po->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $po->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            // $po->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $po->org_currency_exg_rate = $request?->exchange_rate ?? 1;
            $po->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $po->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $po->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $po->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $po->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $po->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $po->save();
            #Save Term
            JobOrderService::saveJobOrderTerms($po, $request);
            /*Create document submit log*/
            $bookId = $po->book_id;
            $docId = $po->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $po->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $po->approval_level ?? 1;
            $modelName = get_class($po);
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                $revisionNumber = $po->revision_number + 1;
                $actionType = 'amendment';
                $totalValue = $po->grand_total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                $po->revision_number = $revisionNumber;
                $po->approval_level = 1;
                $po->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ?? $po->document_status;
                $po->document_status = $amendAfterStatus;
                $po->save();
            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $po->revision_number ?? 0;
                    $actionType = 'submit';
                    $totalValue = $po->grand_total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                    $document_status = $approveDocument['approvalStatus'] ?? $po->document_status;
                    $po->document_status = $document_status;
                } else {
                    $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }
            /*Po Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'jo', false);
            }
            $po->save();
            $redirectUrl = '';
            if($po->document_status == ConstantHelper::APPROVED) {
                $redirectUrl = route('jo.generate-pdf', $po->id);
            }

            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $po,
                'redirect_url' => $redirectUrl
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while updating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    # Get edit address modal
    public function editAddress(Request $request)
    {
        $type = $request?->type ?? '';
        $addressId = $request?->address_id ;
        $selectedAddress = ErpAddress::where('id', $addressId)->first();
        $addresses = collect();
        if($type == 'vendor_address') {
            $vendorId = $request?->vendor_id ?? null;
            $addresses = ErpAddress::where('addressable_id', $vendorId)
                    ->where('addressable_type', Vendor::class)
                    ->latest()
                    ->get();
        }
        if($type == 'delivery_address') {
            $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
            $storeIds = $locations->pluck('id')->toArray() ?? [];
            $addresses = ErpAddress::whereIn('addressable_id', $storeIds)
                    ->where('addressable_type', ErpStore::class)
                    ->latest()
                    ->get();
        }
        $html = '';
        if($selectedAddress) {
            $html = view('procurement.jo.partials.edit-address-modal', compact('type', 'addresses', 'selectedAddress'))->render();
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
        $selectedAddress = null;
        if($addressType == 'vendor_address' && $addressId) {
            $vendor = Vendor::find($vendorId ?? null);
            $selectedAddress = $vendor->addresses()->where('id', $addressId)->first();
        }
        if($addressType == 'delivery_address' && $addressId) {
            $selectedAddress = ErpAddress::where('id', $addressId)->first();
        }
        $newAddres = '';
        if(!$selectedAddress) {
            $country = Country::find($countryId);
            $state = State::find($stateId);
            $city = City::find($cityId);
            $addressParts = [
                $address,
                $city?->name,
                $state?->name,
                $country?->name,
                $pincode ? 'Pincode - ' . $pincode : null,
            ];
            $newAddres = implode(', ', array_filter($addressParts));
        }
        return response()->json(['data' => ['new_address' => $selectedAddress, 'add_new_address' => $newAddres], 'status' => 200, 'message' => 'fetched!']);
    }
    # On select row get item detail 1
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $delivery = json_decode($request->delivery,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $serviceItem = Item::find($request->sow_id);
        $poItem = JoProduct::find($request->jo_product_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        $uomName = $item?->uom?->name ?? 'NA';
        if($item?->uom_id == $uomId) {
        } else {
            $alUom = $item?->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
        }
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $remark = $request->remark ?? null;
        $delivery = isset($delivery) ? $delivery  : null;
        $piItems = [];
        $totalPoQnt = 0;
        if($poItem) {
            $piItems = $poItem->pi_item_mappings;
            $poItem->short_bal_qty = $poItem->short_bal_qty;
        }
        $html = view('procurement.jo.partials.comp-item-detail',compact('serviceItem','item','selectedAttr','remark','uomName','qty','delivery','specifications','piItems','totalPoQnt','poItem'))->render();
        return response()->json(['data' => ['html' => $html,'po_item' => $poItem], 'status' => 200, 'message' => 'fetched.']);
    }
    # On select row get item detail 2
    public function getItemDetail2(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $poItem = JoProduct::find($request->jo_item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        $uomName = $item?->uom?->name ?? 'NA';
        if($item?->uom_id == $uomId) {
        } else {
            $alUom = $item?->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
        }
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $html = view('procurement.jo.partials.comp-item-detail2',compact('item','selectedAttr','uomName','qty','specifications'))->render();
        return response()->json(['data' => ['html' => $html,'po_item' => $poItem], 'status' => 200, 'message' => 'fetched.']);
    }
    # Edit Po
    public function edit(Request $request, $id)
    {
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Edit';
        $short_title = '';
        $reference_from_title = '';
        $user = Helper::getAuthenticatedUser();
        $serviceAlias = ConstantHelper::JO_SERVICE_ALIAS;
        $title = 'Job Order';
        $short_title = 'JO';
        $reference_from_title = 'PWO';
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $po = JobOrder::where('id',$id)->first();
        if (!$po) {
            abort(404);
        }
        $createdBy = $po?->created_by;
        $revision_number = $po->revision_number;
        $totalValue = $po->grand_total_amount ?? 0;
        $creatorType = Helper::userCheck()['type'];
        $approval_level = $po->approval_level ?? 1;
        $buttons = Helper::actionButtonDisplay($po->book_id,$po->document_status , $po->id, $totalValue, $approval_level, $po->created_by ?? 0, $creatorType, $revision_number);
        $revNo = $po->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $po->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($po->book_id, $po->id, $revNo, $totalValue,$createdBy);
        $termsAndConditions = TermsAndCondition::withDefaultGroupCompanyOrg()
                            ->where('status',ConstantHelper::ACTIVE)->get();
        $view = 'procurement.jo.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $po->revision_number) {
            $po = $po->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'procurement.jo.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $locations = $locations = InventoryHelper::getAccessibleLocations('stock', $po->store_id);
        $shortClose = 0;
        if(intval($po->revision_number) > 0) {
            $shortClose = 1;
        } else {
            if($po->document_status == ConstantHelper::APPROVED || $po->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) {
                $shortClose = 1;
            }
        }
        $pendingOrder = JoProduct::where('jo_id', $po->id)
            ->whereRaw('order_qty > (grn_qty + short_close_qty)')
            ->count();
        if($pendingOrder) {
            $shortClose = 1;
        } else {
            $shortClose = 0;
        }
        $isEdit = $buttons['submit'];
        if(!$isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true: false;
        }
        $saleOrders = ErpSaleOrder::whereIn('id', $po->so_id ?? [])
        ->get();
        $currencyName = $organization?->currency?->short_name ?? '';
        $isDifferentCurrency = intval($po?->vendor?->currency_id) !== intval($organization?->currency_id);
        $jobOrderTypes = ConstantHelper::JOB_ORDER_TYPES;
        $isRawMaterial = false;
        if (strtolower($po->job_order_type) === strtolower(ConstantHelper::TYPE_SUBCONTRACTING)){
            $isRawMaterial = true;
        }
        return view($view, [
            'isRawMaterial' => $isRawMaterial,
            'users' => $users,
            'isEdit'=> $isEdit,
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
            'reference_from_title' => $reference_from_title,
            'locations' => $locations,
            'shortClose' => $shortClose,
            'saleOrders' => $saleOrders,
            'serviceAlias' => $serviceAlias,
            'currencyName' => $currencyName,
            'isDifferentCurrency' => $isDifferentCurrency,
            'jobOrderTypes' => $jobOrderTypes

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
        $po = JobOrder::with(['vendor', 'currency', 'joProducts', 'book', 'headerExpenses', 'TermsCondition'])
            ->findOrFail($id);
        $totalItemValue = $po->joProducts()
                ->selectRaw('SUM(order_qty * rate) as total')
                ->value('total') ?? 0.00;
        $totalItemDiscount = $po->joProducts()->sum('item_discount_amount') ?? 0.00;
        $totalHeaderDiscount = $po->joProducts()->sum('header_discount_amount') ?? 0.00;
        $totalTaxes = $po->joProducts()->sum('tax_amount') ?? 0.00;
        $totalTaxableValue = ($totalItemValue - ($totalItemDiscount + $totalHeaderDiscount));
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalAmount = ($totalAfterTax + $po->total_expense_value ?? 0.00);
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        $imagePath = public_path('assets/css/midc-logo.jpg');
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';
        $fileName = '';
        $path = 'pdf.jo';
        $fileName = 'Job-Order-' . date('Y-m-d') . '.pdf';
        $taxes = JobOrderTed::where('jo_id', $po->id)
        ->where('ted_type', 'Tax')
        ->select('ted_type','ted_id','ted_name', 'ted_perc', DB::raw('SUM(ted_amount) as total_amount'), DB::raw('SUM(assessment_amount) as total_assessment_amount'))
        ->groupBy('ted_name', 'ted_perc')
        ->get();
        $sellerShippingAddress = $po->latestShippingAddress();
        $sellerBillingAddress = $po->latestBillingAddress();
        $buyerAddress = $po->latestDeliveryAddress();
        
        $findSo = $po->joProducts()
                    ->whereNotNull('so_id')
                    ->count();
        if($findSo) {
            $soTracking = true; 
        } else {
            $soTracking = false; 
        }
        $isDifferentCurrency = intval($po?->currency_id) !== intval($po?->org_currency_id);
        $pdf = PDF::loadView(
            $path,
            [
                'user' => $user,
                'po'=> $po,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress,
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
                'taxes' => $taxes,
                'sellerShippingAddress' => $sellerShippingAddress,
                'sellerBillingAddress' => $sellerBillingAddress,
                'buyerAddress' => $buyerAddress,
                'soTracking' => $soTracking,
                'isDifferentCurrency' => $isDifferentCurrency
            ]
        );
        return $pdf->stream($fileName);
    }
    # Get PI Item List
    public function getPi(Request $request)
    {
        $documentDate = $request->document_date ?? null;
        $pwoBookId = $request->series_id ?? null;
        $pwoDocNumber = $request->document_number ?? null;
        $soDocNumber = $request->so_doc_number ?? null;
        $soBookId = $request->so_book_id ?? null;
        $storeId = $request->store_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $selectedPwoSoMappingIds = json_decode($request->selected_pi_ids,true) ?? [];

        $pwoItems = PwoSoMapping::whereHas('pwo', function ($subQuery) use ($applicableBookIds,$pwoBookId,$pwoDocNumber) {
            $subQuery->withDefaultGroupCompanyOrg()
            ->whereIn('book_id', $applicableBookIds)
            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
            ->when($pwoBookId, function ($bookQuery) use ($pwoBookId) {
               $bookQuery->where('book_id', $pwoBookId);
            })
            ->when($pwoDocNumber, function ($bookQuery) use ($pwoDocNumber) {
                $normalized = preg_replace('/[^a-zA-Z0-9]+/', ' ', $pwoDocNumber);
                $keywords = preg_split('/\s+/', trim($normalized));
                $bookQuery->where(function ($query) use ($keywords) {
                    foreach ($keywords as $word) {
                        $query->where(function ($subQuery) use ($word) {
                            $subQuery->where('book_code', 'LIKE', "%{$word}%")->orWhere('document_number', 'LIKE', "%{$word}%");
                        });
                    }
                });
            });
        })
        ->whereHas('bom', function ($query) {
            $query->whereIn('production_type', ['Job Work'])
                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
        })
        ->when($storeId, function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })
        ->when($soBookId, function ($q) use ($soBookId) {
            $q->whereHas('so',function($soQ) use($soBookId) {
                $soQ->where('book_id', $soBookId);
            });
        })
        ->when($soDocNumber, function ($q) use ($soDocNumber) {
            $q->whereHas('so',function($soQ) use($soDocNumber) {
                $normalized = preg_replace('/[^a-zA-Z0-9]+/', ' ', $soDocNumber);
                $keywords = preg_split('/\s+/', trim($normalized));
                $soQ->where(function ($query) use ($keywords) {
                    foreach ($keywords as $word) {
                        $query->where(function ($subQuery) use ($word) {
                            $subQuery->where('book_code', 'LIKE', "%{$word}%")->orWhere('document_number', 'LIKE', "%{$word}%");
                        });
                    }
                });
            });
        })
        ->when(count($selectedPwoSoMappingIds), function ($q) use ($selectedPwoSoMappingIds) {
            $q->whereNotIn('id',$selectedPwoSoMappingIds);
        })
        ->when($itemSearch, function ($q) use ($itemSearch) {
            $q->whereHas('item', function ($q2) use ($itemSearch) {
                $q2->where('item_name', 'like', "%$itemSearch%")
                    ->orWhere('item_code', 'like', "%$itemSearch%");
            });
        })
        ->whereColumn('qty', '>', 'jo_qty');

        
        $pwoItems = $pwoItems->with(['pwo', 'item'])->get();
        $html = view('procurement.jo.partials.pwo-item-list', ['pwoItems' => $pwoItems, 'documentDate' => $documentDate])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit PWO Item list
    public function processPiItem(Request $request)
    {
        $ids = json_decode($request->ids,true) ?? [];
        $current_row_count = intval($request->current_row_count);
        $pwoItems = PwoSoMapping::whereIn('id',$ids)->get();
        $html = view('procurement.jo.partials.item-row-pwo', [
            'pwoItems' => $pwoItems,
            'current_row_count' => $current_row_count
            ])->render();
        return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $po = JobOrder::find($request->id);
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
                return response() -> json([
                    'status' => 'error',
                    'message' => 'No Document found',
                ]);
            }
        } catch(Exception $ex) {
            DB::rollBack();
            return response() -> json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function shortCloseSubmit(Request $request)
    {
        DB::beginTransaction();
        try {
            if($request->short_close_ids) {
                $shortCloseIds = explode(',',$request->short_close_ids) ?? [];
                $shortCloseItems =  JoProduct::where('id',$shortCloseIds)->get();
                $po = null;
                foreach($shortCloseItems as $shortCloseItem) {
                    $shortCloseItem->short_close_qty = $shortCloseItem->short_bal_qty;
                    $shortCloseItem->save();
                    $po = $shortCloseItem?->po;
                }
                if($po) {
                    $bookId = $po->book_id;
                    $docId = $po->id; 
                    $revisionNumber = $po->revision_number;
                    $amendRemarks = $request->amend_remark ?? '';
                    $currentLevel = $po->approval_level ?? 1;
                    $actionType = 'short close';
                    $totalValue = $po->grand_total_amount;
                    $modelName = get_class($po);
                    $amendAttachments = $request->file('amend_attachment');
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                }
            }
            DB::commit();
            return response() -> json([
                'status' => 'success',
                'message' => 'Short Close Succesfully!',
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response() -> json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function poMail(Request $request)
    {
        $request->validate([
            'email_to'  => 'required|email',
        ], [
            'email_to.required' => 'Recipient email is required.',
            'email_to.email'    => 'Please enter a valid email address.',
        ]);
        $po = JobOrder::with(['vendor'])->find($request->id);
        $vendor = $po->vendor;
        $sendTo = $request->email_to ?? $vendor->email;
        $vendor->email = $sendTo;
        $title = "Purchase Order Generated";
        $pattern = "Purchase Order";
        $remarks = $request->remarks ?? null;
        $mail_from = '';
        $mail_from_name = '';
        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
        $attachment = $request->file('attachments') ?? null;
        $name = $vendor->company_name; // Assuming vendor is already defined
        $viewLink = route('jo.generate-pdf', ['id'=>$request->id,'type'=>'purchase-order']);
        $description = <<<HTML
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif;">
            <tr>
                <td>
                    <h2 style="color: #2c3e50;">Your Purchase Order </h2>
                    <p style="font-size: 16px; color: #555;">Dear {$name},</p>
                        <p style='font-size: 15px; color: #333;'>
                            {$remarks}
                        </p>
                    <p style="font-size: 15px; color: #333;">
                        Please click the button below to view or download your Purchase Order:
                    </p>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="{$viewLink}" target="_blank" style="background-color: #7415ae; color: #ffffff; padding: 12px 24px; border-radius: 5px; font-size: 16px; text-decoration: none; font-weight: bold;">
                            Purchase Order
                        </a>
                    </p>
                </td>
            </tr>
        </table>
        HTML;
        self::sendMail($vendor,$title,$description,$cc,$attachment,$mail_from,$mail_from_name);
    }
    public function sendMail($receiver, $title, $description, $cc = null, $attachment, $mail_from=null, $mail_from_name=null)
    {
        if (!$receiver || !isset($receiver->email)) {
            return "Error: Receiver details are missing or invalid.";
        }
        dispatch(new SendEmailJob($receiver, $mail_from, $mail_from_name,$title,$description,$cc,$attachment));
        return response() -> json([
            'status' => 'success',
            'message' => 'Email request sent succesfully',
        ]);
            
    }
    public function poReport(Request $request)
    {
        $pathUrl = route('jo.index', ['type' => request()->route('type')]);
        $orderType = ConstantHelper::JO_SERVICE_ALIAS;
        $poItems = JoProduct::whereHas('po', function ($headerQuery) use($orderType, $pathUrl, $request) {
            $headerQuery -> where('type', $orderType)-> withDefaultGroupCompanyOrg() -> withDraftListingLogic();
            //Vendor Filter
            $headerQuery = $headerQuery -> when($request -> vendor_id, function ($custQuery) use($request) {
                $custQuery -> where('vendor_id', $request -> vendor_id);
            });
            //Book Filter
            $headerQuery = $headerQuery -> when($request -> book_id, function ($bookQuery) use($request) {
                $bookQuery -> where('book_id', $request -> book_id);
            });
            //Document Id Filter
            $headerQuery = $headerQuery -> when($request -> document_number, function ($docQuery) use($request) {
                $docQuery -> where('document_number', 'LIKE', '%' . $request -> document_number . '%');
            });
            //Location Filter
            $headerQuery = $headerQuery -> when($request -> location_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> location_id);
            });
            //Company Filter
            $headerQuery = $headerQuery -> when($request -> company_id, function ($docQuery) use($request) {
                $docQuery -> where('store_id', $request -> company_id);
            });
            //Organization Filter
            $headerQuery = $headerQuery -> when($request -> organization_id, function ($docQuery) use($request) {
                $docQuery -> where('organization_id', $request -> organization_id);
            });
            //Document Status Filter
            $headerQuery = $headerQuery -> when($request -> doc_status, function ($docStatusQuery) use($request) {
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
            $headerQuery = $headerQuery -> when($dateRange, function ($dateRangeQuery) use($request, $dateRange) {
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
            $headerQuery = $headerQuery -> when($request -> item_id, function ($itemQuery) use($request) {
                $itemQuery -> withWhereHas('jo_items', function ($itemSubQuery) use($request) {
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
        }) -> orderByDesc('id');
            $dynamicFields = DynamicFieldHelper::getServiceDynamicFields(ConstantHelper::JO_SERVICE_ALIAS);
            $datatables = DataTables::of($poItems) ->addIndexColumn()
            ->editColumn('status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->po->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = ucfirst($row -> po -> document_status);   
                $editRoute = null;
                $editRoute = route('jo.edit', ['id' => $row->po->id,'type' => request()->route('type')]);
                return "
                <div style='text-align:right;'>
                    <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <a href='" . $editRoute . "'>
                            <i class='cursor-pointer' data-feather='eye'></i>
                        </a>
                </div>
            ";
            })
            ->addColumn('book_name', function ($row) {
                return $row -> po -> book -> book_code;
            })
            ->addColumn('indent', function ($row) {
                return $row ?-> pi_item ?-> pi ?-> book_code."-".$row ?-> pi_item ?-> pi ?-> document_number;
            })
            ->addColumn('order', function ($row) {
                return $row ?-> so ?-> book_code."-".$row ?-> so ?-> document_number;
            })
            ->addColumn('item_name', function ($row) {
                return $row -> item -> item_name;
            })
            ->addColumn('item_code', function ($row) {
                return $row -> item -> item_code;
            })
            ->editColumn('location', function ($row) {
                return $row ?-> po ?-> store_location ?-> store_name;
            })
            ->addColumn('vendor_currency',function($row){
                return $row -> po ?-> currency ?-> name;
            })
            ->addColumn('document_number', function ($row) {
                return $row -> po -> document_number;
            })
            ->addColumn('document_date', function ($row) {
                return $row -> po -> document_date;
            })
            ->addColumn('store_name', function ($row) {
                return $row -> po ?-> store ?-> store_name;
            })
            ->addColumn('store_name', function ($row) {
                return $row -> po ?-> store ?-> store_name;
            })
            ->addColumn('vendor_name', function ($row) {
                return $row -> po ?-> vendor ?-> company_name;
            })
            ->addColumn('vendor_currency', function ($row) {
                return $row -> po -> currency_code;
            })
            ->addColumn('payment_terms_name', function ($row) {
                return $row -> po -> payment_term_code;
            })
            ->addColumn('hsn_code', function ($row) {
                return $row -> hsn ?-> code;
            })
            ->addColumn('uom_name', function ($row) {
                return $row -> uom ?-> name;
            })
            ->addColumn('po_qty', function ($row) {
                return number_format($row -> order_qty, 2);
            })
            ->editColumn('ge_qty', function ($row) {
                return number_format($row -> ge_qty, 2);
            })
            ->editColumn('grn_qty', function ($row) {
                return number_format($row -> grn_qty, 2);
            })
            ->editColumn('short_close_qty', function ($row) {
                return number_format($row -> short_close_qty, 2);
            })
            ->editColumn('rate', function ($row) {
                return number_format($row -> rate, 2);
            })
            ->addColumn('total_discount_amount', function ($row) {
                return number_format($row -> header_discount_amount + $row -> item_discount_amount, 2);
            })
            ->editColumn('tax_amount', function ($row) {
                return number_format($row -> tax_amount, 2);
            })
            ->addColumn('taxable_amount', function ($row) {
                return number_format(($row -> rate * $row->order_qty) - ($row -> header_discount_amount + $row -> item_discount_amount), 2);
            })
            ->editColumn('total_item_amount', function ($row) {
                return number_format(($row -> rate * $row->order_qty) - ($row -> header_discount_amount + $row -> item_discount_amount) + $row -> tax_amount, 2);
            })
            // ->editColumn('pending_qty', function ($row) {
            //     return number_format($row -> pending_qty, 2);
            // })
            ->addColumn('item_attributes', function ($row) {
                $attributesUi = '';
                if (count($row -> attributes) > 0) {
                    foreach ($row -> attributes as $soAttribute) {
                        $attrName = AttributeGroup::find($soAttribute->attribute_name);
                        $attrValue = Attribute::find($soAttribute -> attribute_value);
                        $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName?->name : $attrValue?->value </span>";
                    }
                } else {
                    $attributesUi = 'N/A';
                }
                return $attributesUi;
            });
            foreach ($dynamicFields as $field) {
                $datatables = $datatables->addColumn($field -> name, function ($row) use ($field) {
                    $value = "";
                    $actualDynamicFields = $row -> po ?-> dynamic_fields;
                    foreach ($actualDynamicFields as $actualDynamicField) {
                        if ($field -> name == $actualDynamicField -> name) {
                            $value = $actualDynamicField -> value;
                        }
                    }
                    return $value;
                });
            }
            $datatables = $datatables
            ->rawColumns(['item_attributes','status'])
            ->make(true);
            return $datatables;
    }
    // Check bom job
    public function checkBomJob(Request $request)
    {
        $itemId = $request->item_id ?? null;
        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'data' => ['is_bom' => false],
                'status' => 404,
                'message' => 'Item not found'
            ], 404);
        }

        $bomExists = Bom::withDefaultGroupCompanyOrg()
            ->where('item_id', $item->id)
            ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->exists();

        if(!$bomExists) {
            return response()->json([
                'data' => ['is_bom' => false],
                'status' => 422,
                'message' => 'Bom not exist!'
            ]);
        }

        $bomExists = Bom::withDefaultGroupCompanyOrg()
        ->where('item_id', $item->id)
        ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
        ->whereIn('production_type', ['Job Work'])
        ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
        ->exists();

        return response()->json([
            'data' => ['is_bom' => $bomExists],
            'status' => 200,
            'message' => $bomExists ? 'Fetched!' : "Only products with production type Job Work are allowed."
        ]);
    }
}
