<?php

namespace App\Http\Controllers\PurchaseOrder;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\ErpSaleOrder;
use App\Http\Requests\PoBulkRequest;
use App\Http\Requests\PoRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ErpAddress;
use App\Models\Item;
use App\Models\Organization;
use App\Models\PiItem;
use App\Models\PoItem;
use App\Models\PoItemAttribute;
use App\Models\PoItemDelivery;
use App\Models\PoTerm;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderMedia;
use App\Models\PurchaseOrderTed;
use App\Models\TermsAndCondition;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\PiPoMapping;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Models\ErpPaymentTerm;
use App\Models\ErpPoPaymentTerm;
use App\Models\ErpStore;
use App\Models\PaymentTermDetail;
use App\Models\PurchaseIndent;
use App\Models\State;

class PoController extends Controller
{
    protected $type;

    public function __construct(Request $request)
    {
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
            $selectColumns = ['id','vendor_id','type','document_status','book_id','document_number','store_id','currency_id','document_date','revision_number',
        'total_item_value','total_discount_value','total_tax_value','total_expense_value'];
            $pos = PurchaseOrder::select($selectColumns)->ofType($type)
                    ->withDraftListingLogic()
                    ->with('vendor:id,vendor_code,company_name')
                    ->with('currency:id,short_name,name')
                    ->latest();

            // Apply drawer filters
            if ($request->filled('date_range')) {
                $dates = explode(' to ', $request->date_range);

                if (count($dates) === 2) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate   = Carbon::parse($dates[1])->endOfDay();

                    $pos->whereBetween('document_date', [$startDate, $endDate]);
                }
            }
            if ($request->filled('book_id')) {
                $pos->whereIn('book_id', $request->book_id);
            }
            if ($request->filled('location_id')) {
                $pos->whereIn('store_id', $request->location_id);
            }
            if ($request->filled('vendor_id')) {
                $pos->whereIn('vendor_id', $request->vendor_id);
            }
            if ($request->filled('organization_id')) {
                $pos->whereIn('organization_id', $request->organization_id);
            }
            return DataTables::of($pos)
            ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                return view('partials.action-dropdown', [
                    'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                    'displayStatus' => $row->display_status,
                    'row' => $row,
                    'actions' => [
                        [
                            'url' => fn($r) => route('po.edit', ['type' => request()->route('type'), 'id' => $r->id]),
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
            ->filterColumn('store_location', function($query, $keyword) {
            $query->whereHas('store_location', function($q) use ($keyword) {
                $q->where('store_name', 'like', "%{$keyword}%");
            });
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
            ->filterColumn('vendor_name', function($query, $keyword) {
            $query->whereHas('vendor', function($q) use ($keyword) {
                $q->where('company_name', 'like', "%{$keyword}%")
                ->orWhere('vendor_code', 'like', "%{$keyword}%");
            });
            })
            ->addColumn('components', function ($row) {
                return $row->po_items->count();
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
        $user = Helper::getAuthenticatedUser();
        $applicableOrgIds = $user->organizations->pluck('id')->toArray();
        $serviceAlias = ConstantHelper::PO_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)->get();
        $vendors = Vendor::where('organization_id', $user->organization_id)->get();
        $locations = InventoryHelper::getAccessibleLocations();
        $applicableOrganizations = Organization::whereIn('id', $applicableOrgIds)
        ->where('status', ConstantHelper::ACTIVE)
        ->get(['id', 'name']);
        return view('procurement.po.index',[
            'servicesBooks' => $servicesBooks,
            'books' => $books,
            'vendors' => $vendors,
            'locations' => $locations,
            'applicableOrganizations' => $applicableOrganizations,
        ]);
    }

    # Po create
    public function create()
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $termsAndConditions = TermsAndCondition::where('status',ConstantHelper::ACTIVE)->get();
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Add New';
        $short_title = '';
        $reference_from_title = '';
        $serviceAlias = ConstantHelper::PO_SERVICE_ALIAS;
        $title = 'Purchase Order';
        $short_title = 'PO';
        $reference_from_title = 'Purchase Indent';
        $user = Helper::getAuthenticatedUser();
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)->get();

        $organization = Organization::where('id', $user->organization_id)->first();
        $departments = Department::where('organization_id', $organization->id)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->get();

        $selectedDepartmentId = null;
        $userCheck = $user;
        if($userCheck) {
            $selectedDepartmentId = $user?->department_id;
        }

        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $currencyName = $organization?->currency?->short_name ?? '';
        $companyCountryId = null;
        $companyStateId = null;
        $firstAddress = $organization->addresses->first();
        if ($firstAddress) {
            $companyCountryId = $firstAddress->country_id;
            $companyStateId = $firstAddress->state_id;
        }
        $itemImportFile = asset('templates/PurchaseOrderImport.xlsx');
        
        return view('procurement.po.create', [
            'fromState'=> $companyStateId,
            'fromCountry'=> $companyCountryId,
            'books'=> $books,
            'termsAndConditions' => $termsAndConditions,
            'title' => $title,
            'menu' => $menu,
            'menu_url' => $menu_url,
            'sub_menu' => $sub_menu,
            'short_title' => $short_title,
            'reference_from_title' => $reference_from_title,
            'selectedDepartmentId' => $selectedDepartmentId,
            'departments' => $departments,
            'locations' => $locations,
            'serviceAlias' => $serviceAlias,
            'currencyName' => $currencyName
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
        $isEdit = isset($request->is_edit) ? intval($request->is_edit) : 0;
        $html = view('procurement.po.partials.item-row',compact('rowCount','isEdit'))->render();
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
        $itemAttributeArray = [];
        if($poItemId) {
            $poItem = PoItem::where('id',$poItemId)->where('item_id',$item->id??null)->first();
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
            $itemAttributeArray = $item->item_attributes_array();
        }
        $html = view('procurement.po.partials.comp-attribute',compact('item','rowCount','selectedAttr','isPi','itemAttributes'))->render();
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
            $html = view('procurement.po.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    # Get address on vendor change and set
    public function getAddress(Request $request)
    {
        $vendorId = $request?->id ?? null;
        $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])
                ->find($vendorId);
        $currency = $vendor?->currency;
        $paymentTerm = $vendor?->paymentTerms;
        $vendorId = $vendor?->id;
        $documentDate = $request?->document_date;
        $vendorAddress = ErpAddress::where('addressable_id', $vendorId)
                    ->where('addressable_type', Vendor::class)
                    ->latest()
                    ->first();

        $compliances = $vendor->compliances ?? null;

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
                    'currency_exchange' => $currencyData,
                    'compliances' => $compliances
                ],
            'status' => 200,
            'message' => 'fetched'
        ]);
    }

    # Purchase Order store
    public function store(PoRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->has('tnc') && strlen(strip_tags($request->tnc)) > 250) {
                return response()->json([
                    'message' => 'The terms and conditions cannnot be greater than 250 characters.',
                    'error' => 'tnc exceeds maximum length',
                ], 422);
            }
            $type = $this->type;
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            //Tax Country and State
            $firstAddress = $organization?->addresses->first();
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
            $po = new PurchaseOrder;
            $po->type = $type;
            $po->organization_id = $organization?->id;
            $po->group_id = $organization?->group_id;
            $po->company_id = $organization?->company_id;
            $po->department_id = $request->department_id;
            $po->store_id = $request->store_id;
            $po->book_id = $request->book_id;
            $po->book_code = $request->book_code;
            $po->procurement_type = $request->procurement_type;
            $document_number = $request->document_number ?? null;
            $po -> tnc = $request->tnc ?? null;
            $poTypeParam = $parameters['goods_or_services'][0] ?? 'Goods';
            $po->po_type = $poTypeParam;

            if (in_array(ucfirst(strtolower($poTypeParam)), ['Goods'])) {
                $po->gate_entry_required = $parameters['gate_entry_required'][0] ?? 'no';
            } else {
                $po->gate_entry_required = 'no';
            }
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
            $regeneratedDocExist = PurchaseOrder::where('book_id',$request->book_id)
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
            $po->total_item_value = 0.00;
            $po->total_discount_value = 0.00;
            $po->total_tax_value = 0.00;
            $po->total_expense_value = 0.00;
            $po->credit_days = $request->credit_days;
            $po->consignee_name = $request->consignee_name;
            $po->save();

            if (in_array(ucfirst(strtolower($poTypeParam)), ['Goods'])) {
                if($po?->vendor?->supplier_books?->count()) {
                    $po->supp_invoice_required = 'yes';
                    $po->save();
                }
            } else {
                $po->supp_invoice_required = 'no';
            }

            $vendorBillingAddress = $po->bill_address ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $po->bill_address_details()->firstOrNew([
                    'type' => 'billing',
                ]);
                $billingAddress->fill([
                    'address' => $vendorBillingAddress?->address,
                    'country_id' => $vendorBillingAddress?->country_id,
                    'state_id' => $vendorBillingAddress?->state_id,
                    'city_id' => $vendorBillingAddress?->city_id,
                    'pincode' => $vendorBillingAddress->pincode ?? $vendorBillingAddress->postal_code,
                    'phone' => $vendorBillingAddress->phone ?? $vendorBillingAddress->mobile,
                    'fax_number' => $vendorBillingAddress?->fax_number ?? null,
                ]);
                $billingAddress->save();
            }

            $vendorShippingAddress = $po?->ship_address ?? null;
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

            # Store location address
            $vendorDeliveryAddress = ErpAddress::find($request->delivery_address_id ?? null);
            if($vendorDeliveryAddress) {
                $storeLocation = $po->store_address()->firstOrNew();
                $storeLocation->fill([
                    'type' => 'location',
                    'address' => $vendorDeliveryAddress->address,
                    'country_id' => $vendorDeliveryAddress->country_id,
                    'state_id' => $vendorDeliveryAddress->state_id,
                    'city_id' => $vendorDeliveryAddress->city_id,
                    'pincode' => $vendorDeliveryAddress->pincode,
                    'phone' => $vendorDeliveryAddress->phone,
                    'fax_number' => $vendorDeliveryAddress->fax_number,
                ]);
                $storeLocation->save();
            } else {
                $d_country_id = $request->delivery_country_id ?? null;
                $d_state_id = $request->delivery_state_id ?? null;
                $d_city_id = $request->delivery_city_id ?? null;
                $d_pincode = $request->delivery_pincode ?? null;
                $d_address = $request->delivery_address ?? null;
                $storeLocation = $po->store_address()->firstOrNew();
                $storeLocation->fill([
                    'type' => 'location',
                    'address' => $d_address,
                    'country_id' => $d_country_id,
                    'state_id' => $d_state_id,
                    'city_id' => $d_city_id,
                    'pincode' => $d_pincode,
                    'phone' => null,
                    'fax_number' => null,
                ]);
                $storeLocation->save();
            }

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
                $totalHeaderExpense += floatval($expValue['total'] ?? $expValue['e_amnt']) ?? 0.00;
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
                    $pi_item_id = null;
                    if(isset($component['pi_item_id']) && $component['pi_item_id']) {
                        $piItem = PiItem::find($component['pi_item_id']);
                        $pi_item_id = $piItem->id ?? null;
                        if($piItem) {
                            $piItem->order_qty = $piItem->order_qty + floatval($component['qty']);
                            $piItem->save();
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
                        'key' => $c_key,
                        'so_id' => $component['so_id'] ?? null,
                        'purchase_order_id' => $po->id,
                        'pi_item_id' => $pi_item_id,
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
                        'expense_amount' => floatval($component['exp_amount_header']) ?? 0.00,
                        'tax_amount' => 0.00,
                        'company_currency_id' => @$component['company_currency_id'] ?? 0.00,
                        'company_currency_exchange_rate' => @$component['company_currency_exchange_rate'] ?? 0.00,
                        'group_currency_id' => @$component['group_currency_id'] ?? 0.00,
                        'group_currency_exchange_rate' => @$component['group_currency_exchange_rate'] ?? 0.00,
                        'remarks' => $component['remark'] ?? null,
                        'value_after_discount' => $itemValueAfterDiscount,
                        'item_value' => $itemValue,
                        'delivery_date' => @$component['delivery_date']
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
                    $itemHeaderExp = floatval($poItem['expense_amount']);
                    $poDetail = new PoItem;
                    $poDetail->so_id = $poItem['so_id'];
                    $poDetail->purchase_order_id = $poItem['purchase_order_id'];
                    $poDetail->pi_item_id = $poItem['pi_item_id'];
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
                    $poDetail->delivery_date = $poItem['delivery_date'];
                    $poDetail->save();
                    $_key = $poItem['key'] ?? $_key;
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

                    # Store PI Po mapping
                    $pi_item_ids = $request->pi_item_ids ? explode(',',$request->pi_item_ids) : [];
                    $piItems = PiItem::whereIn('id',$pi_item_ids)
                                ->where('item_id',$poDetail->item_id)
                                ->where('uom_id',$poDetail->uom_id)
                                ->when($poDetail->so_id, function($query) use($poDetail) {
                                    $query->where('so_id', $poDetail->so_id);
                                })
                                ->when(count($poDetail->attributes), function ($query) use ($poDetail) {
                                    $query->whereHas('attributes', function ($piAttributeQuery) use ($poDetail) {
                                        $piAttributeQuery->where(function ($subQuery) use ($poDetail) {
                                            foreach ($poDetail->attributes as $poAttribute) {
                                                $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                    $q->where('item_attribute_id', $poAttribute->item_attribute_id)
                                                      ->where('attribute_value', $poAttribute->attribute_value);
                                                });
                                            }
                                        });
                                    }, '=', count($poDetail->attributes));
                                })
                                ->get();
                    $poQty = $poDetail->order_qty;
                    foreach($piItems as $piItem) {
                        $piPoMapping = new PiPoMapping;
                        $piPoMapping->pi_id = $piItem->pi_id;
                        $piPoMapping->pi_item_id = $piItem->id;
                        $piPoMapping->po_id = $poDetail->purchase_order_id;
                        $piPoMapping->po_item_id = $poDetail->id;
                        $piPoMapping->so_id = $piItem->so_id;
                        $indentQty = min($piItem->indent_qty,$poQty);
                        $piPoMapping->po_qty = $indentQty;

                        // if($piItem->indent_qty < ($piItem->order_qty + $indentQty)) {
                        //     $itemName = $piItem?->item?->item_name;
                        //     DB::rollBack();
                        //     return response()->json([
                        //             'message' => "Po is more than indent qty for item $itemName",
                        //             'error' => "",
                        //         ], 422);
                        // }

                        $piPoMapping->save();
                        $piItem->order_qty += $indentQty;
                        $piItem->save();
                        $poQty -= $indentQty;
                        if($poQty <= 0) {
                            break;
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
                    if($poDetail?->itemDelivery?->count() < 1) {
                        $poItemDelivery = new PoItemDelivery;
                        $poItemDelivery->purchase_order_id = $po->id;
                        $poItemDelivery->po_item_id = $poDetail->id;
                        $poItemDelivery->qty = $poDetail->order_qty ?? 0.00;
                        $poItemDelivery->delivery_date = $poDetail->delivery_date ?? now();
                        $poItemDelivery->save();
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

                if(isset($request->all()['exp_summary'])) {
                    foreach($request->all()['exp_summary'] as $dis) {
                        if(isset($dis['e_amnt']) && $dis['e_amnt']) {
                            $ted = new PurchaseOrderTed;
                            $ted->purchase_order_id  =      $po->id;
                            $ted->po_item_id         =      null;
                            $ted->hsn_id             =      $dis['hsn_id'] ?? null;
                            $ted->ted_type           =      'Expense';
                            $ted->ted_level          =      'H';
                            $ted->ted_id             =      $dis['ted_e_id'] ?? null;
                            $ted->ted_name           =      $dis['e_name'] ?? null;
                            $ted->assessment_amount  =      $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                            $ted->ted_amount         =      $dis['e_amnt'] ?? 0.00;
                            $ted->ted_perc           =      0.00;
                            $ted->tax_amount         =      $dis['tax_amount'] ?? 0.00;
                            // $ted->total_amount       =      $dis['total'] ?? ($ted->ted_amount + $ted->tax_amount);
                            $ted->tax_breakup        =      $dis['tax_breakup'] ?? null;
                            $ted->applicable_type    =      'Collection';
                            $ted->save();
                        }
                    }
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
                $po->total_tax_value = abs($totalTax) ?? 0.00;
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
                $totalValue = $po->grand_total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $po->document_status = $approveDocument['approvalStatus'] ?? $po->document_status;
            } else {
                $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            $po->save();
            /*Po Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'po', false);
            }
            $redirectUrl = '';
            if($po->document_status == ConstantHelper::APPROVED) {
                $redirectUrl = url(request()->route('type') . '/' . $po->id . '/pdf');
            }
            // Save Po Payment Terms
            self::savePoPaymentTerm($request->payment_term_id, $po->id, $request->credit_days);

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
    public function update(PoRequest $request, $type, $id)
    {
        if ($request->has('tnc') && strlen(strip_tags($request->tnc)) > 250) {
            return response()->json([
                'message' => 'The terms and conditions cannnot be greater than 250 characters.',
                'error' => 'terms_data exceeds maximum length',
            ], 422);
        }
        $po = PurchaseOrder::find($id);
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        //Tax Country and State
        $firstAddress = $organization?->addresses->first();
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
                    ['model_type' => 'detail', 'model_name' => 'PoTerm', 'relation_column' => 'purchase_order_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PoItemAttribute', 'relation_column' => 'po_item_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PoItemDelivery', 'relation_column' => 'po_item_id'],
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
                foreach($poItems as $poItem) {
                    if(floatval($poItem->grn_qty) > 0) {
                        return response()->json([
                            'message' => 'Can not delete: Item used in GRN.',
                            'error' => "",
                            'refresh_page' => true
                        ], 422);
                    }
                    if(floatval($poItem->ge_qty) > 0) {
                        return response()->json([
                            'message' => 'Can not delete: Item used in Gate Entry.',
                            'error' => "",
                            'refresh_page' => true
                        ], 422);
                    }
                    if(floatval($poItem->asn_qty) > 0) {
                        return response()->json([
                            'message' => 'Can not delete: Item used in ASN.',
                            'error' => "",
                            'refresh_page' => true
                        ], 422);
                    }
                    $poItem->teds()->delete();
                    $poItem->itemDelivery()->delete();
                    $poItem->attributes()->delete();
                    $updatedQty = $poItem?->order_qty;
                    $piPoMappings = PiPoMapping::where('po_item_id',$poItem->id)->orderBy('id', 'desc')->get();
                    foreach($piPoMappings as $piPoMapping) {
                        $pi_item = $piPoMapping->pi_item;
                        $balQty = $pi_item->order_qty;
                        $utlQty =  min($updatedQty, $balQty);
                        $pi_item->order_qty -= $utlQty;
                        $pi_item->save();
                        if($piPoMapping->po_qty == $utlQty) {
                            $piPoMapping->delete();
                        } else {
                            $piPoMapping->po_qty -= $utlQty;
                            $piPoMapping->save();
                        }
                        $updatedQty -= $utlQty;
                        if($updatedQty <= 0) {
                            break;
                        }
                    }
                    $poItem->delete();
                }
            }
            # Bom Header save
            $po->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $po->remarks = $request->remarks ?? null;
            $po->payment_term_id = $request->payment_term_id;
            $po->payment_term_code = $request->payment_term_code;
            $po->department_id = $request->department_id;
            $po->procurement_type = $request->procurement_type;
            $po->store_id = $request->store_id;
            $po->tnc = $request->tnc ?? null;
            $po->document_date = $request->document_date ?? $po->document_date;
            $po->credit_days = $request->credit_days;
            $po->consignee_name = $request->consignee_name;
            $poTypeParam = $parameters['goods_or_services'][0] ?? 'Goods';
            $po->po_type = $poTypeParam;

            if (in_array(ucfirst(strtolower($poTypeParam)), ['Goods'])) {
                $po->gate_entry_required = $parameters['gate_entry_required'][0] ?? 'no';
            } else {
                $po->gate_entry_required = 'no';
            }
            $po->partial_delivery = $parameters['partial_delivery_allowed'][0] ?? 'no';

            if (in_array(ucfirst(strtolower($poTypeParam)), ['Goods'])) {
                if($po?->vendor?->supplier_books?->count()) {
                    $po->supp_invoice_required = 'yes';
                    $po->save();
                }
            } else {
                $po->supp_invoice_required = 'no';
            }

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

            # Store location address
            if($po?->store_location)
            {
                $storeAddress  = $po?->store_location->address;
                $storeLocation = $po->store_address()->firstOrNew();
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
                $totalHeaderExpense += floatval($expValue['total'] ?? $expValue['e_amnt']) ?? 0.00;
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
                    $pi_item_id = null;
                    if(isset($component['pi_item_id']) && $component['pi_item_id']) {
                        $piItem = PiItem::find($component['pi_item_id']);
                        $pi_item_id = $piItem->id ?? null;
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
                        'purchase_order_id' => $po->id,
                        'pi_item_id' => $pi_item_id,
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
                        'expense_amount' => floatval($component['exp_amount_header']) ?? 0.00,
                        'tax_amount' => 0.00,
                        'company_currency_id' => @$component['company_currency_id'] ?? 0.00,
                        'company_currency_exchange_rate' => @$component['company_currency_exchange_rate'] ?? 0.00,
                        'group_currency_id' => @$component['group_currency_id'] ?? 0.00,
                        'group_currency_exchange_rate' => @$component['group_currency_exchange_rate'] ?? 0.00,
                        'remarks' => $component['remark'] ?? null,
                        'value_after_discount' => $itemValueAfterDiscount,
                        'item_value' => $itemValue,
                        'pi_item_hidden_ids' => $component['pi_item_hidden_ids'] ?? [],
                        'delivery_date' => $component['delivery_date']
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
                    $itemHeaderExp = floatval($poItem['expense_amount']);
                    $poDetail = PoItem::find($component['po_item_id'] ?? null) ?? new PoItem;

                    $isNewItem = false;
                    if(isset($poDetail->item_id) && $poDetail->item_id) {
                        $isNewItem = $poDetail->item_id != ($poItem['item_id'] ?? null);
                    }

                    $updatedQty =  floatval($poItem['order_qty']) - ($poDetail?->order_qty ?? 0);
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

                    $poDetail->so_id = $poItem['so_id'];
                    $poDetail->purchase_order_id = $poItem['purchase_order_id'];
                    $poDetail->pi_item_id = $poItem['pi_item_id'];
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
                    $poDetail->delivery_date = $poItem['delivery_date'];
                    $poDetail->save();

                    #Save component Attr
                    if ($isNewItem && $poDetail->id) {
                        PoItemAttribute::where('po_item_id', $poDetail->id)
                            ->delete();
                    }
                    foreach($poDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            // $poAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $poAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $poAttr = PoItemAttribute::firstOrNew([
                                'purchase_order_id' => $po->id,
                                'po_item_id' => $poDetail->id,
                                'item_attribute_id' => $itemAttribute->id
                            ]);
                            // $poAttr = PoItemAttribute::find($poAttrId) ?? new PoItemAttribute;
                            $poAttr->item_code = $component['item_code'] ?? null;
                            $poAttr->attribute_name = $itemAttribute->attribute_group_id;
                            $poAttr->attribute_value = $poAttrName ?? null;
                            $poAttr->save();
                        }
                    }

                    # Store PI Po mapping
                    $pi_item_ids = $poItem['pi_item_hidden_ids'] ? explode(',',$poItem['pi_item_hidden_ids']) : [];
                    $piItems = PiItem::whereIn('id',$pi_item_ids)
                                ->where('item_id',$poDetail->item_id)
                                ->where('uom_id',$poDetail->uom_id)
                                ->when($poDetail->so_id, function($query) use($poDetail) {
                                    $query->where('so_id', $poDetail->so_id);
                                })
                                ->when(count($poDetail->attributes), function ($query) use ($poDetail) {
                                    $query->whereHas('attributes', function ($piAttributeQuery) use ($poDetail) {
                                        $piAttributeQuery->where(function ($subQuery) use ($poDetail) {
                                            foreach ($poDetail->attributes as $poAttribute) {
                                                $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                    $q->where('item_attribute_id', $poAttribute->item_attribute_id)
                                                      ->where('attribute_value', $poAttribute->attribute_value);
                                                });
                                            }
                                        });
                                    }, '=', count($poDetail->attributes));
                                })
                                ->get();
                    $poQty = $poDetail->order_qty;
                    foreach($piItems as $piItem) {
                        $piPoMapping = PiPoMapping::where('po_item_id', $poDetail->id)
                                        ->where('pi_item_id', $piItem->id)
                                        ->first() ?? new PiPoMapping;
                        if(!isset($piPoMapping->id)) {
                            $piPoMapping->so_id = $piItem->so_id;
                            $piPoMapping->pi_id = $piItem->pi_id;
                            $piPoMapping->pi_item_id = $piItem->id;
                            $piPoMapping->po_id = $poDetail->purchase_order_id;
                            $piPoMapping->po_item_id = $poDetail->id;
                            $indentQty = min($piItem->indent_qty,$poQty);
                            $piPoMapping->po_qty = $indentQty;
                            if($piItem->indent_qty < ($piItem->order_qty + $indentQty)) {
                                DB::rollBack();
                                $itemName = $piItem?->item?->item_name;
                                return response()->json([
                                        'message' => "Po is more than indent qty for item $itemName",
                                        'error' => "",
                                    ], 422);
                            }
                            $piPoMapping->save();
                            $piItem->order_qty += $indentQty;
                            $piItem->save();
                            $poQty -= $indentQty;
                            if($poQty <= 0) {
                                break;
                            }
                        }
                    }
                    if($updatedQty >= 0) {
                        $piPoMappings = PiPoMapping::where('po_item_id',$poDetail->id)
                                    ->orderBy('id', 'asc')
                                    ->get();
                    } else {
                        $piPoMappings = PiPoMapping::where('po_item_id',$poDetail->id)
                                    ->orderBy('id', 'desc')
                                    ->get();
                    }
                    foreach($piPoMappings as $key => $piPoMapping) {
                        $pi_item = $piPoMapping->pi_item;
                        if($updatedQty < 0) {
                            $balQty = $pi_item->order_qty;
                            $utlQty =  min(abs($updatedQty), $balQty) * -1;
                        } else {
                            $balQty = $pi_item->indent_qty - $pi_item->order_qty;
                            $utlQty =  min($updatedQty, $balQty);

                        }
                        $pi_item->order_qty += $utlQty;
                        $pi_item->save();
                        if(($piPoMapping->po_qty + $utlQty) <= 0) {
                            $piPoMapping->delete();
                        } else {
                            $piPoMapping->po_qty += $utlQty;
                            $piPoMapping->save();
                        }
                        $updatedQty -= $utlQty;
                        if($updatedQty == 0) {
                            break;
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

                    if($poDetail?->itemDelivery?->count() < 1) {
                        $poItemDelivery = new PoItemDelivery;
                        $poItemDelivery->purchase_order_id = $po->id;
                        $poItemDelivery->po_item_id = $poDetail->id;
                        $poItemDelivery->qty = $poDetail->order_qty ?? 0.00;
                        $poItemDelivery->delivery_date = $poDetail->delivery_date ?? now();
                        $poItemDelivery->save();
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
                            $ted = PurchaseOrderTed::find($dis['e_id'] ?? null) ?? new PurchaseOrderTed;
                            $ted->purchase_order_id  =      $po->id;
                            $ted->hsn_id             =      $dis['hsn_id'] ?? null;
                            $ted->po_item_id         =      null;
                            $ted->ted_type           =      'Expense';
                            $ted->ted_level          =      'H';
                            $ted->ted_id             =      $dis['ted_e_id'] ?? null;
                            $ted->ted_name           =      $dis['e_name'] ?? null;
                            $ted->assessment_amount  =      $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                            $ted->ted_amount         =      $dis['e_amnt'] ?? 0.00;
                            $ted->ted_perc           =      0.00;
                            $ted->tax_amount         =      $dis['tax_amount'] ?? 0.00;
                            // $ted->total_amount       =      $dis['total'] ?? ($ted->ted_amount + $ted->tax_amount);
                            $ted->tax_breakup        =      $dis['tax_breakup'] ?? null;
                            $ted->applicable_type    =      'Collection';
                            $ted->save();
                        }
                    }
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
                $po->total_tax_value = abs($totalTax) ?? 0.00;
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
                if($po->fresh()->po_items->isEmpty()) {
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
                $mediaFiles = $po->uploadDocuments($request->file('attachment'), 'po', false);
            }
            $po->save();
            $redirectUrl = '';
            if($po->document_status == ConstantHelper::APPROVED) {
                $redirectUrl = url(request()->route('type') . '/' . $po->id . '/pdf');
            }

            // Save Po Payment Terms
            $this->savePoPaymentTerm($request->payment_term_id, $po->id, $request->credit_days);

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
            $html = view('procurement.po.partials.edit-address-modal', compact('type', 'addresses', 'selectedAddress'))->render();
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

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $delivery = json_decode($request->delivery,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $poItem = PoItem::find($request->po_item_id ?? null);
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
        $html = view('procurement.po.partials.comp-item-detail',compact('item','selectedAttr','remark','uomName','qty','delivery','specifications','piItems','totalPoQnt','poItem'))->render();
        return response()->json(['data' => ['html' => $html,'po_item' => $poItem], 'status' => 200, 'message' => 'fetched.']);
    }

    # Edit Po
    public function edit(Request $request,$type, $id)
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
        $serviceAlias = ConstantHelper::PO_SERVICE_ALIAS;
        $title = 'Purchase Order';
        $short_title = 'PO';
        $reference_from_title = 'Purchase Indent';
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $po = PurchaseOrder::ofType($this->type)->where('id',$id)->first();
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
        $termsAndConditions = TermsAndCondition::where('status',ConstantHelper::ACTIVE)->get();
        $view = 'procurement.po.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $po->revision_number) {
            $po = $po->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'procurement.po.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $departments = Department::where('organization_id', $organization->id)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->get();
        $selectedDepartmentId = null;
        $userCheck = $user;
        if($userCheck) {
            $selectedDepartmentId = $user?->department_id;
        }
        $locations = $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK, $po->store_id);
        $shortClose = 0;
        if(intval($po->revision_number) > 0) {
            $shortClose = 1;
        } else {
            if($po->document_status == ConstantHelper::APPROVED || $po->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) {
                $shortClose = 1;
            }
        }
        $pendingOrder = PoItem::where('purchase_order_id', $po->id)
            ->whereRaw('order_qty > (GREATEST(COALESCE(grn_qty, 0), COALESCE(ge_qty, 0), COALESCE(asn_qty, 0)) + COALESCE(short_close_qty, 0))')
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

        $companyCountryId = null;
        $companyStateId = null;
        $firstAddress = $organization->addresses->first();
        if ($firstAddress) {
            $companyCountryId = $firstAddress->country_id;
            $companyStateId = $firstAddress->state_id;
        }

        return view($view, [
            'users' => $users,
            'isEdit'=> $isEdit,
            'books'=> $books,
            'fromCountry'=> $companyCountryId,
            'fromState'=> $companyStateId,
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
            'selectedDepartmentId' => $selectedDepartmentId,
            'departments' => $departments,
            'locations' => $locations,
            'shortClose' => $shortClose,
            'saleOrders' => $saleOrders,
            'serviceAlias' => $serviceAlias,
            'currencyName' => $currencyName,
            'isDifferentCurrency' => $isDifferentCurrency

        ]);
    }

    // genrate pdf
    public function generatePdf(Request $request, $type, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();

        $po = PurchaseOrder::with([
            'vendor',
            'currency',
            'po_items',
            'book',
            'headerExpenses',
            'TermsCondition',
            'pi_item_mappings'
        ])->findOrFail($id);

        // ✅ Totals
        $totalItemValue      = $po->po_items()->selectRaw('SUM(order_qty * rate) as total')->value('total') ?? 0.00;
        $totalItemDiscount   = $po->po_items()->sum('item_discount_amount') ?? 0.00;
        $totalHeaderDiscount = $po->po_items()->sum('header_discount_amount') ?? 0.00;

        // ✅ Taxable Value
        $totalTaxableValue = ($totalItemValue - ($totalItemDiscount + $totalHeaderDiscount));

        // ✅ Get grouped tax TEDs
        $taxes = PurchaseOrderTed::where('purchase_order_id', $po->id)
            ->where('ted_type', 'Tax')
            ->select(
                'ted_type',
                'ted_id',
                'ted_name',
                'ted_perc',
                DB::raw('SUM(ted_amount) as total_amount'),
                DB::raw('SUM(assessment_amount) as total_assessment_amount')
            )
            ->groupBy('ted_name', 'ted_perc')
            ->get();

        // ✅ Sum tax amount
        $totalTaxes = $taxes->sum('total_amount');

        // ✅ After tax
        $totalAfterTax = $totalTaxableValue + $totalTaxes;

        // ✅ Expenses
        $totalExpenses = $po->headerExpenses->sum(fn($exp) => $exp->ted_amount + $exp->tax_amount);

        // ✅ Final amount
        $totalAmount = $totalAfterTax + $totalExpenses;

        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);

        $imagePath = public_path('assets/css/midc-logo.jpg');
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';
        $path = 'pdf.po2';
        $fileName = 'Purchase-Order-' . date('Y-m-d') . '.pdf';

        // ✅ Reference text from PI
        $uniquePiIds = $po->pi_item_mappings->pluck('pi_id')->unique()->values()->toArray();
        $references = PurchaseIndent::whereIn('id', $uniquePiIds)
            ->select('id', 'book_code', 'document_number')
            ->get();
        $referenceText = $references?->count()
            ? $references->map(fn($ref) => "{$ref->book_code} - {$ref->document_number}")->implode(', ')
            : '';

        // ✅ SO Tracking
        $soTracking = $po->po_items()->whereNotNull('so_id')->exists();

        $isDifferentCurrency = intval($po?->currency_id) !== intval($po?->org_currency_id);

        $sellerShippingAddress = $po->latestShippingAddress();
        $sellerBillingAddress  = $po->latestBillingAddress();
        $buyerAddress          = $po->latestDeliveryAddress();

        $hsnSummary = $po->po_items->groupBy(fn($item) => $item->hsn?->code)->map(function($items, $hsn) {
            $taxableValue = $items->sum(fn($i) => ($i->order_qty * $i->rate) - $i->item_discount_amount - $i->header_discount_amount);
            $cgst = $items->sum(fn($i) => $i->cgst_value['value']);
            $sgst = $items->sum(fn($i) => $i->sgst_value['value']);
            $igst = $items->sum(fn($i) => $i->igst_value['value']);
            return [
                'hsn' => $hsn,
                'taxable_value' => $taxableValue,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total_tax' => $cgst + $sgst + $igst,
            ];
        });
                
        $pdf = PDF::loadView($path, [
            'referenceText'       => $referenceText,
            'user'                => $user,
            'po'                  => $po,
            'organization'        => $organization,
            'organizationAddress' => $organizationAddress,
            'totalItemValue'      => $totalItemValue,
            'totalItemDiscount'   => $totalItemDiscount,
            'totalHeaderDiscount' => $totalHeaderDiscount,
            'totalTaxes'          => $totalTaxes,
            'totalTaxableValue'   => $totalTaxableValue,
            'totalAfterTax'       => $totalAfterTax,
            'totalExpenses'       => $totalExpenses,
            'totalAmount'         => $totalAmount,
            'amountInWords'       => $amountInWords,
            'imagePath'           => $imagePath,
            'docStatusClass'      => $docStatusClass,
            'taxes'               => $taxes,
            'sellerShippingAddress'=> $sellerShippingAddress,
            'sellerBillingAddress' => $sellerBillingAddress,
            'buyerAddress'        => $buyerAddress,
            'hsnSummary'          => $hsnSummary,
            'soTracking'          => $soTracking,
            'isDifferentCurrency' => $isDifferentCurrency,
        ]);

        return $pdf->stream($fileName);
    }
# Get PI Item List
    public function getPi(Request $request)
    {
        $storeId = $request->store_id ?? null;
        $query = $this->buildPiQuery($request);
        return DataTables::of($query)
        ->addColumn('select_checkbox', fn($row) => app(\App\View\Components\Po\CheckBox::class, ['row' => $row])->resolveView()->render())
        ->addColumn('book_name', fn($row) => $row?->pi?->book?->book_name ?? '')
        ->addColumn('doc_no', fn($row) => $row?->pi?->document_number ?? '')
        ->addColumn('doc_date', fn($row) => $row?->pi?->getFormattedDate('document_date') ?? '')
        ->addColumn('item_name', fn($row) => $row?->item?->item_name ?? '')
        ->addColumn('item_code', fn($row) => $row?->item?->item_code ?? '')
        ->addColumn('attributes', fn($row) => app(\App\View\Components\Po\Attribute::class, ['row' => $row])->resolveView()->render())
        ->addColumn('uom', fn($row) => $row?->uom?->name ?? '')
        ->addColumn('balance_qty', fn($row) => number_format(($row?->indent_qty - $row?->order_qty),2) ?? '')
        ->addColumn('pending_po', fn($row) => number_format($row?->pending_po,2) ?? '')
        ->addColumn('avl_stock', fn($row) => number_format($row?->getAvlStock($storeId),2))
        ->addColumn('vendor_select', fn($row) => app(\App\View\Components\Po\Vendor::class, [
            'row' => $row,
            'documentDate' => request()->get('document_date'),
        ])->resolveView()->render())
        ->addColumn('so_no', fn($row) => $row?->pi?->so?->book_code ?? '')
        ->addColumn('location', fn($row) => $row?->pi?->sub_store_id ? $row?->pi?->sub_store?->name : $row?->pi?->requester?->name)
        ->addColumn('requester', fn($row) => $row?->po?->department?->name ?? '')
        ->addColumn('remarks', fn($row) => $row?->remarks ?? '')
        ->rawColumns([
            'book_name',
            'doc_no',
            'doc_date',
            'item_code',
            'item_name',
            'attributes',
            'uom',
            'vendor_select',
            'so_no',
            'location',
            'requester',
            'remarks',
            'select_checkbox'
            ])
        ->make(true);
    }
    # This for both bulk and single po
    protected function buildPiQuery(Request $request)
    {
        $poType = ucfirst(strtolower($request->po_type ?? 'Goods'));
        $seriesId = $request->series_id ?? null;
        $indentId = $request->document_number ?? null;
        $storeId = $request->store_id ?? null;
        $subStoreId = $request->sub_store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $departmentId = $request->department_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $soId= $request->so_id ?? null;
        $requesterId = $request->requester_id ?? null;
        $piItems = null;
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $selected_pi_ids = json_decode($request->selected_pi_ids) ?? [];
        $selectColumn = ['id','pi_id','so_id','item_id','item_code','item_name','uom_id','uom_code','vendor_id','indent_qty','order_qty','adjusted_qty','required_qty','remarks'];
        $piItems = PiItem::select($selectColumn)
                    ->where(function($query) use ($seriesId,$applicableBookIds,$vendorId, $departmentId, $selected_pi_ids, $itemSearch,$storeId,$subStoreId, $soId, $indentId,$requesterId,$poType) {
                    if(count($selected_pi_ids)) {
                        $query->whereNotIn('id',$selected_pi_ids);
                    }
                    $query->whereHas('pi', function($pi) use ($seriesId,$applicableBookIds,$departmentId,$storeId,$subStoreId,$indentId,$requesterId) {
                        $pi->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
                        // if($seriesId) {
                        //     $pi->where('book_id',$seriesId);
                        // } else {
                        //     if(count($applicableBookIds)) {
                        //         $pi->whereIn('book_id',$applicableBookIds);
                        //     }
                        // }
                        if(count($applicableBookIds)) {
                            $pi->whereIn('book_id',$applicableBookIds);
                        }
                        if($storeId) {
                            $pi->where('store_id', $storeId);
                        }
                        if($subStoreId) {
                            $pi->where('sub_store_id', $subStoreId);
                        }
                        if($indentId) {
                            $pi->where('id', $indentId);
                        }
                        if($requesterId) {
                            $pi->where('user_id', $requesterId);
                        }
                        if($departmentId) {
                            $pi->where('department_id', $departmentId);
                        }
                    });
                    if($soId) {
                        $query->where('so_id', $soId);
                    }
                    $query->whereHas('item', function($itemQuery) use ($vendorId, $poType) {
                        $itemQuery->where('type', $poType);
                        if($vendorId) {
                            $itemQuery->whereHas('approvedVendors', function($av) use ($vendorId) {
                                $av->where('vendor_id', $vendorId);
                            });
                        }
                    });
                    if ($itemSearch) {
                        $query->whereHas('item', function ($query) use ($itemSearch) {
                            $query->searchByKeywords($itemSearch);
                        });
                    }
                    $query->whereRaw('indent_qty > order_qty');
                });
        return $piItems;
    }

    # Get PI Buld Item List
    public function getPiBulk(Request $request)
    {
        $query = $this->buildPiQuery($request);
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user?->organization_id)->first();
        $orgCurrencyId = $organization?->currency_id;
        $rowCount = 0;
        $documentDate = $request->get('document_date');
        // Map keys to partial views instead of class components
        $partials = [
            'select_checkbox' => 'components.po-bulk.check-box',
            'doc_number'      => 'components.po-bulk.doc-num',
            'doc_date'        => 'components.po-bulk.doc-date',
            'item_code'       => 'components.po-bulk.item-code',
            'item_name'       => 'components.po-bulk.item-name',
            'attributes'      => 'components.po-bulk.attribute',
            'uom'             => 'components.po-bulk.uom',
            'qty'             => 'components.po-bulk.qty',
            'delivery_date'   => 'components.po-bulk.delivery-date',
            'so_doc'          => 'components.po-bulk.so-doc',
            'store'           => 'components.po-bulk.store',
            'department'      => 'components.po-bulk.department',
            'requester'       => 'components.po-bulk.requester',
            'remark'          => 'components.po-bulk.remark',
        ];
        $dataTable = DataTables::of($query)->addIndexColumn();
        $dataTable
        ->addColumn('vendor_id', function ($row) use (&$rowCount, $documentDate) {
            $rowCount++;
            return $this->renderComponent(\App\View\Components\PoBulk\Vendor::class, [
                'row' => $row,
                'documentDate' => $documentDate,
                'defaultOption' => false,
                'rowCount' => $rowCount,
            ]);
        })
        ->addColumn('rate', function ($row) use (&$rowCount, $orgCurrencyId, $documentDate) {
            return $this->renderComponent(\App\View\Components\PoBulk\Rate::class, [
                'row' => $row,
                'rowCount' => $rowCount,
                'currencyId' => $row?->vendor?->currency_id ?? $orgCurrencyId,
                'documentDate' => $documentDate,
            ]);
        })
        ->addColumn('pending_po', fn($row) => number_format($row?->pending_po,2) ?? '')
        ->addColumn('avl_stock', fn($row) => number_format($row?->getAvlStock($row?->pi?->store_id),2));

        foreach ($partials as $key => $partialView) {
            $dataTable->addColumn($key, function ($row) use (&$rowCount, $partialView) {
                return view($partialView, [
                    'row' => $row,
                    'rowCount' => $rowCount,
                ])->render();
            });
        }
        return $dataTable
        ->rawColumns(array_merge(array_keys($partials), ['vendor_id', 'rate']))
        ->make(true);
    }

    public function renderComponent(string $class, array $params = []): string
    {
        return app($class, $params)->resolveView()->render();
    }
    # Submit PI Item list
    public function processPiItem(Request $request)
    {
        $ids = json_decode($request->ids,true) ?? [];
        $vendor = null;
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $groupItems = json_decode($request->groupItems, TRUE) ?? [];
        $piItemGrouped = DB::table(function ($query) use ($ids) {
            $query->from('erp_pi_items')
                ->leftJoin('erp_pi_item_attributes', 'erp_pi_items.id', '=', 'erp_pi_item_attributes.pi_item_id') // Use LEFT JOIN
                ->select(
                    'erp_pi_items.id as pi_item_id',
                    'erp_pi_items.so_id',
                    'erp_pi_items.item_id',
                    'erp_pi_items.uom_id',
                    'erp_pi_items.remarks',
                    DB::raw("GROUP_CONCAT(
                        CONCAT(erp_pi_item_attributes.item_attribute_id, ':', erp_pi_item_attributes.attribute_value)
                        ORDER BY erp_pi_item_attributes.item_attribute_id SEPARATOR ', '
                    ) as attributes"),
                    'erp_pi_items.indent_qty',
                    'erp_pi_items.order_qty'
                )
                ->whereIn('erp_pi_items.id', $ids)
                ->groupBy('erp_pi_items.id', 'erp_pi_items.item_id', 'erp_pi_items.uom_id', 'erp_pi_items.so_id');
        })
        ->select(
            'item_id',
            'so_id',
            'uom_id',
            DB::raw("IFNULL(attributes, '') as attributes"),
            DB::raw("MIN(remarks) as remarks"),
            DB::raw("SUM(indent_qty - order_qty) as total_qty"),
            DB::raw("GROUP_CONCAT(pi_item_id ORDER BY pi_item_id SEPARATOR ',') as pi_item_ids")
        )
        ->groupBy('item_id', 'uom_id', 'attributes','so_id')
        ->get();
        $updatedGroupItems = [];
        $newItems = [];
        foreach ($piItemGrouped as $piItem) {
            $found = false;
            foreach ($groupItems as &$groupItem) {
                if (
                    $groupItem['item_id'] == $piItem->item_id &&
                    $groupItem['uom_id'] == $piItem->uom_id &&
                    $groupItem['attributes'] == $piItem->attributes &&
                    $groupItem['so_id'] == $piItem->so_id
                ) {
                    $groupItem['total_qty'] += $piItem->total_qty;
                    $existingIds = explode(',', $groupItem['pi_item_ids'] ?? '');
                    $newIds = explode(',', $piItem->pi_item_ids);
                    $mergedIds = array_unique(array_merge($existingIds, $newIds));
                    $groupItem['pi_item_ids'] = implode(',', $mergedIds);
                    $updatedGroupItems[] = $groupItem;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $newItems[] = (array)$piItem;
            }
        }
        $newItems = array_map(function ($item) {
            return (object)$item;
        }, $newItems);
        $transactionDate = $request->d_date ?? date('Y-m-d');
        $vendorId = $request->vendor_id ?? null;
        $vendor = Vendor::with(['currency:id,name', 'paymentTerms:id,name'])->find($vendorId);
        $vendor->paymentTerm = $vendor->paymentTerms;
        $currencyId = $vendor?->currency->id ?? $request->currency_id ?? null;
        $current_row_count = intval($request->current_row_count);
        $html = view('procurement.po.partials.item-row-pi', [
            'piItemGrouped' => $newItems,
            'transactionDate' => $transactionDate,
            'currencyId' => $currencyId,
            'vendorId' => $vendorId,
            'current_row_count' => $current_row_count
            ])->render();
        return response()->json(['data' => ['pos' => $html, 'vendor' => $vendor,'finalDiscounts' => $finalDiscounts,'finalExpenses' => $finalExpenses, 'updatedGroupItems' => @$updatedGroupItems], 'status' => 200, 'message' => "fetched!"]);
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
                $shortCloseItems =  PoItem::where('id',$shortCloseIds)->get();
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

    # Po Bulk create
    public function bulkCreate()
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $termsAndConditions = TermsAndCondition::where('status',ConstantHelper::ACTIVE)->get();
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Add New';
        $short_title = '';
        $reference_from_title = '';
        $serviceAlias = ConstantHelper::PO_SERVICE_ALIAS;
        $title = 'Purchase Order';
        $short_title = 'PO';
        $reference_from_title = 'Purchase Indent';
        $user = Helper::getAuthenticatedUser();
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)
                ->whereHas('patterns', function($patternQuery){
                    $patternQuery->where('series_numbering',ConstantHelper::DOC_NO_TYPE_AUTO);
                })
                ->get();

        $organization = Organization::where('id', $user->organization_id)->first();
        $departments = Department::where('organization_id', $organization->id)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->get();

        $selectedDepartmentId = null;
        $userCheck = $user;
        if($userCheck) {
            $selectedDepartmentId = $user?->department_id;
        }
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        if (count($books) == 0) {
            return redirect()->back();
        }
        return view('procurement.po.bulk-create', [
            'books'=> $books,
            'termsAndConditions' => $termsAndConditions,
            'title' => $title,
            'menu' => $menu,
            'menu_url' => $menu_url,
            'sub_menu' => $sub_menu,
            'short_title' => $short_title,
            'reference_from_title' => $reference_from_title,
            'selectedDepartmentId' => $selectedDepartmentId,
            'departments' => $departments,
            'locations' => $locations
        ]);
    }

    # Po Bulk Store
    public function bulkStore(PoBulkRequest $request)
    {
        $selectedRowCount = false;
        $selectedVendorIds = [];
        foreach ($request->input('components', []) as $index => $component) {
            if (!empty($component['is_pi_item_id'])) {
                $selectedRowCount = true;
                if(!empty($component['vendor_id']))
                    $selectedVendorIds[] = $component['vendor_id'];
                }
        }
        if(!$selectedRowCount) {
            return response()->json([
                'message' => 'Please select atleast one row.'
            ], 422);
        }
        $selectedVendorIds = array_unique($selectedVendorIds);
        $vendorCheck = 0;
        foreach($selectedVendorIds as $selectedVendorId) {
            $checkVendor = Vendor::find($selectedVendorId);
            if($checkVendor->addresses()->count() == 0) {
                return response()->json([
                    'message' => "{$checkVendor->company_name} vendor does not have any address."
                ], 422);

            }

            // $validVendor = ItemHelper::validateVendor($selectedVendorId, $request->input('document_date'));
            // if(!$validVendor) {
            //     $vendorCheck = $selectedVendorId;
            //     break;
            // }
        }

        // if($vendorCheck) {
        //     $vendorGet = Vendor::find(intval($vendorCheck));
        //     return response()->json([
        //         'message' => "{$vendorGet->company_name} vendor is not updated."
        //     ], 422);
        // }

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

        $groupedDatas = [];
        foreach ($request->input('components', []) as $index => $component) {
            if (!empty($component['is_pi_item_id'])) {
                $vendorId = $component['vendor_id'] ?? null;
                $vendor = Vendor::find($vendorId);
                if (!isset($groupedDatas[$vendorId])) {
                    $shipping = ErpAddress::where('addressable_id', $vendorId)
                    ->where('addressable_type', Vendor::class)
                    ->latest()
                    ->first();
                    $store = ErpStore::find($request->store_id ?? null);
                    $billing = $store?->address;
                    $groupedDatas[$vendorId] = [
                        'vendor_id' => $vendorId,
                        'vendor_code' => $vendor?->vendor_code,
                        'currency_id' => $vendor?->currency_id,
                        'payment_terms_id' => $vendor->payment_terms_id,
                        'payment_term_code' => $vendor?->paymentTerm?->name,
                        'billing_address' => $billing?->id,
                        'shipping_address' => $shipping?->id,
                        'pi_items' => []
                    ];
                }
                $attributes = [];
                $item = Item::find($component['item_id']);
                foreach($item->itemAttributes as $itemAttribute) {
                    if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $poAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $attributes[] = [
                            'item_attribute_id' => intval($itemAttribute->id),
                            'attribute_group_id' => intval($itemAttribute->attribute_group_id),
                            'attribute_id' => intval($poAttrName)
                        ];
                    }
                }
                $groupedDatas[$vendorId]['pi_items'][] = [
                    'so_id' => $component['so_id'] ?? null,
                    'pi_item_id' => $component['pi_item_id'],
                    'item_id' => $component['item_id'],
                    'item_code' => $component['item_code'],
                    'uom_id' => $component['uom_id'],
                    'hsn_id' => $component['hsn_id'],
                    'hsn_code' => $component['hsn_code'],
                    'uom_code' => $component['uom_code'],
                    'inventory_uom_id' => $component['inventory_uom_id'],
                    'inventory_uom_code' => $component['inventory_uom_code'],
                    'inventory_uom_qty' => $component['inventory_uom_qty'],
                    'remarks' => $component['remark'],
                    'qty' => $component['qty'],
                    'rate' => $component['rate'],
                    'delivery_date' => $component['delivery_date'] ?? date('Y-m-d'),
                    'attributes' => $attributes
                ];
            }
        }
        $groupedDatas = array_values($groupedDatas);
        $finalGroupedDatas = [];
        foreach ($groupedDatas as $vendorData) {
            $vendorId = $vendorData['vendor_id'];
            if (!isset($finalGroupedDatas[$vendorId])) {
                $finalGroupedDatas[$vendorId] = [
                    'vendor_id' => $vendorId,
                    'vendor_code' => $vendorData['vendor_code'],
                    'currency_id' => $vendorData['currency_id'],
                    'payment_terms_id' => $vendorData['payment_terms_id'],
                    'payment_term_code' => $vendorData['payment_term_code'],
                    'pi_items' => []
                ];
            }
            $groupedPiItems = [];
            foreach ($vendorData['pi_items'] as $piItem) {
                $key = $piItem['so_id'] . '-'.$piItem['item_id'] . '-' . $piItem['uom_id'] . '-' . json_encode($piItem['attributes']);
                if (!isset($groupedPiItems[$key])) {
                    $groupedPiItems[$key] = [
                        'so_id' => $piItem['so_id'],
                        'item_id' => $piItem['item_id'],
                        'item_code' => $piItem['item_code'],
                        'uom_id' => $piItem['uom_id'],
                        'hsn_id' => $piItem['hsn_id'],
                        'hsn_code' => $piItem['hsn_code'],
                        'uom_code' => $piItem['uom_code'],
                        'inventory_uom_id' => $piItem['inventory_uom_id'],
                        'inventory_uom_code' => $piItem['inventory_uom_code'],
                        'inventory_uom_qty' => $piItem['inventory_uom_qty'],
                        'remarks' => $piItem['remarks'],
                        'qty' => 0,
                        'rate' => $piItem['rate'],
                        'delivery_date' => $piItem['delivery_date'] ?? date('Y-m-d'),
                        'attributes' => $piItem['attributes'],
                        'pi_item_ids' => []
                    ];
                }
                $groupedPiItems[$key]['qty'] += $piItem['qty'];
                $groupedPiItems[$key]['pi_item_ids'][] = $piItem['pi_item_id'];
            }
            $finalGroupedDatas[$vendorId]['pi_items'] = array_values($groupedPiItems);
        }
        $finalGroupedDatas = array_values($finalGroupedDatas);
        DB::beginTransaction();
        try {
            $type = $this->type;
            $storeId = $request->store_id;
            $bookId = $request->book_id;
            $bookCode = $request->book_code;
            $documentDate = $request->document_date;
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }
            $isTax = false;
            if(isset($parameters['tax_required']) && !empty($parameters['tax_required']))
            {
                if (in_array('yes', array_map('strtolower', $parameters['tax_required']))) {
                    $isTax = true;
                }
            }
            foreach($finalGroupedDatas as $groupedData) {
                $totalValue = 0;
                $totalTax = 0;
                $po = new PurchaseOrder;
                $po->type = $type;
                $po->organization_id = $organization->id;
                $po->group_id = $organization->group_id;
                $po->company_id = $organization->company_id;
                $po->store_id = $storeId;
                $po->book_id = $bookId;
                $po->book_code = $bookCode;
                $document_number = $request->document_number ?? null;
                $numberPatternData = Helper::generateDocumentNumberNew($bookId, $documentDate);
                if (!isset($numberPatternData)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
                $regeneratedDocExist = PurchaseOrder::where('book_id', $bookId)
                                        ->where('document_number', $document_number)
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
                $po->document_date = $documentDate;
                $po->vendor_id = $groupedData['vendor_id'];
                $po->vendor_code = $groupedData['vendor_code'];
                $po->currency_id = $groupedData['currency_id'];
                $currency = Currency::find($groupedData['currency_id'] ?? null);
                $po->currency_code = $currency?->short_name;
                $po->gate_entry_required = $parameters['gate_entry_required'][0] ?? 'no';
                $po->partial_delivery = $parameters['partial_delivery_allowed'][0] ?? 'no';
                $po->document_status = $request->document_status;
                $po->payment_term_id = $groupedData['payment_terms_id'];
                $po->payment_term_code = $groupedData['payment_term_code'];
                $po->save();
                if($po?->vendor?->supplier_books?->count()) {
                    $po->supp_invoice_required = 'yes';
                    $po->save();
                }

                $vendorAddress = ErpAddress::where('addressable_id', $groupedData['vendor_id'])->where('addressable_type', Vendor::class)->latest()->first();
                $store = ErpStore::find($storeId);
                $locationAddress = $store?->address;
                $po->billing_address = $locationAddress?->id ?? null;
                $po->shipping_address = $vendorAddress?->id ?? null;
                $po->save();

                $vendorBillingAddress = $po->bill_address ?? null;
                if ($vendorBillingAddress) {
                    $billingAddress = $po->bill_address_details()->firstOrNew([
                        'type' => 'billing',
                    ]);
                    $billingAddress->fill([
                        'address' => $vendorBillingAddress?->address,
                        'country_id' => $vendorBillingAddress?->country_id,
                        'state_id' => $vendorBillingAddress?->state_id,
                        'city_id' => $vendorBillingAddress?->city_id,
                        'pincode' => $vendorBillingAddress->pincode ?? $vendorBillingAddress->postal_code,
                        'phone' => $vendorBillingAddress->phone ?? $vendorBillingAddress->mobile,
                        'fax_number' => $vendorBillingAddress?->fax_number ?? null,
                    ]);
                    $billingAddress->save();
                }
                $vendorShippingAddress = $po?->ship_address ?? null;
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
                # Store location address
                $vendorDeliveryAddress = $locationAddress;
                if($vendorDeliveryAddress) {
                    $storeLocation = $po->store_address()->firstOrNew();
                    $storeLocation->fill([
                        'type' => 'location',
                        'address' => $vendorDeliveryAddress->address,
                        'country_id' => $vendorDeliveryAddress->country_id,
                        'state_id' => $vendorDeliveryAddress->state_id,
                        'city_id' => $vendorDeliveryAddress->city_id,
                        'pincode' => $vendorDeliveryAddress->pincode,
                        'phone' => $vendorDeliveryAddress->phone,
                        'fax_number' => $vendorDeliveryAddress->fax_number,
                    ]);
                    $storeLocation->save();
                }

                if(count($groupedData['pi_items'])) {
                    foreach($groupedData['pi_items'] as $piItemRow) {
                        $poDetail = new PoItem;
                        $poDetail->purchase_order_id = $po->id;
                        $poDetail->so_id = $piItemRow['so_id'];
                        $poDetail->item_id = $piItemRow['item_id'];
                        $poDetail->item_code = $piItemRow['item_code'];
                        $poDetail->hsn_id = $piItemRow['hsn_id'];
                        $poDetail->hsn_code = $piItemRow['hsn_code'];
                        $poDetail->uom_id = $piItemRow['uom_id'];
                        $poDetail->uom_code = $piItemRow['uom_code'];
                        $poDetail->order_qty = $piItemRow['qty'];
                        $poDetail->inventory_uom_id = $piItemRow['inventory_uom_id'];
                        $poDetail->inventory_uom_code = $piItemRow['inventory_uom_code'];
                        $poDetail->inventory_uom_qty = $piItemRow['inventory_uom_qty'];
                        $poDetail->rate = $piItemRow['rate'];
                        // $poDetail->company_currency_id = $poItem['company_currency_id'];
                        // $poDetail->group_currency_id = $poItem['group_currency_id'];
                        // $poDetail->group_currency_exchange_rate = $poItem['group_currency_exchange_rate'];
                        $poDetail->remarks = $piItemRow['remarks'];
                        $poDetail->delivery_date = $piItemRow['delivery_date'];
                        $poDetail->save();
                        if(!$poDetail->hsn_id) {
                            $poDetail->hsn_id = $poDetail?->item?->hsn?->id;
                            $poDetail->hsn_code = $poDetail?->item?->hsn?->code;
                            $poDetail->save();
                        }
                        if(isset($piItemRow['attributes']) && count($piItemRow['attributes'])) {
                            foreach($piItemRow['attributes'] as $poAttr) {
                                $poAttribute = new PoItemAttribute;
                                $poAttribute->purchase_order_id = $po->id;
                                $poAttribute->po_item_id = $poDetail->id;
                                $poAttribute->item_attribute_id = $poAttr['item_attribute_id'];
                                $poAttribute->item_code = $piItemRow['item_code'];
                                $poAttribute->attribute_name = $poAttr['attribute_group_id'];
                                $poAttribute->attribute_value = $poAttr['attribute_id'];
                                $poAttribute->save();
                            }
                        }
                        if($isTax) {
                            $itemTax = 0;
                            $itemPrice = floatval($poDetail->rate) * floatval($poDetail->order_qty);
                            $shippingAddress = $po->ship_address;
                            $partyCountryId = isset($shippingAddress) ? $shippingAddress->country_id : null;
                            $partyStateId = isset($shippingAddress) ? $shippingAddress->state_id : null;
                            $taxDetails = TaxHelper::calculateTax($poDetail->hsn_id, $itemPrice, $companyCountryId, $companyStateId, $partyCountryId, $partyStateId, 'purchase');

                            if (isset($taxDetails) && count($taxDetails) > 0) {
                                foreach ($taxDetails as $taxDetail) {
                                    if($taxDetail['applicability_type'] == 'collection') {
                                        $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $itemPrice);
                                    } else {
                                        $itemTax -= ((double)$taxDetail['tax_percentage'] / 100 * $itemPrice);
                                    }
                                }
                            }

                            $poDetail->tax_amount = abs($itemTax);
                            $poDetail->save();
                            $totalTax += $itemTax;
                        }
                        $totalValue += floatval($poDetail->rate) * floatval($poDetail->order_qty);
                        # Store PI Po mapping
                        $pi_item_ids = $piItemRow['pi_item_ids'] ?? [];
                        $piItems = PiItem::whereIn('id',$pi_item_ids)
                                    ->where('item_id',$poDetail->item_id)
                                    ->where('uom_id',$poDetail->uom_id)
                                    ->when($poDetail->so_id, function($query) use($poDetail) {
                                        $query->where('so_id', $poDetail->so_id);
                                    })
                                    ->when(count($poDetail->attributes), function ($query) use ($poDetail) {
                                        $query->whereHas('attributes', function ($piAttributeQuery) use ($poDetail) {
                                            $piAttributeQuery->where(function ($subQuery) use ($poDetail) {
                                                foreach ($poDetail->attributes as $poAttribute) {
                                                    $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                        $q->where('item_attribute_id', $poAttribute->item_attribute_id)
                                                        ->where('attribute_value', $poAttribute->attribute_value);
                                                    });
                                                }
                                            });
                                        }, '=', count($poDetail->attributes));
                                    })
                                    ->get();
                        $poQty = $poDetail->order_qty;
                        foreach($piItems as $piItem) {
                            $piPoMapping = new PiPoMapping;
                            $piPoMapping->so_id = $piItem->so_id;
                            $piPoMapping->pi_id = $piItem->pi_id;
                            $piPoMapping->pi_item_id = $piItem->id;
                            $piPoMapping->po_id = $poDetail->purchase_order_id;
                            $piPoMapping->po_item_id = $poDetail->id;
                            $indentQty = min($piItem->indent_qty,$poQty);
                            $piPoMapping->po_qty = $indentQty;
                            // if($piItem->indent_qty < ($piItem->order_qty + $indentQty)) {
                            //     $itemName = $piItem?->item?->item_name;
                            //     DB::rollBack();
                            //     return response()->json([
                            //             'message' => "Po is more than indent qty for item $itemName",
                            //             'error' => "",
                            //         ], 422);
                            // }
                            $piPoMapping->save();
                            $piItem->order_qty += $indentQty;
                            $piItem->save();
                            $poQty -= $indentQty;
                            if($poQty <= 0) {
                                break;
                            }
                        }

                        if($poDetail?->itemDelivery?->count() < 1) {
                            $poItemDelivery = new PoItemDelivery;
                            $poItemDelivery->purchase_order_id = $po->id;
                            $poItemDelivery->po_item_id = $poDetail->id;
                            $poItemDelivery->qty = $poDetail->order_qty ?? 0.00;
                            $poItemDelivery->delivery_date = $poDetail->delivery_date ?? now();
                            $poItemDelivery->save();
                        }

                    }
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
                $po->total_item_value = $totalValue;
                $po->total_tax_value = abs($totalTax) ?? 0.00;
                $po->save();

            }
            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => []
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
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
        $po = PurchaseOrder::with(['vendor'])->find($request->id);
        $vendor = $po->vendor;
        $sendTo = $request->email_to ?? null;
        if(!$sendTo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipient email is required.',
            ], 422);
        }
        $vendor->email = $sendTo;
        $title = "Purchase Order Generated";
        $pattern = "Purchase Order";
        $remarks = $request->remarks ?? null;
        $mail_from = '';
        $mail_from_name = '';
        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
        $attachment = $request->file('attachments') ?? null;
        $name = $vendor->company_name; // Assuming vendor is already defined
        $viewLink = route('po.generate-pdf', ['id'=>$request->id,'type'=>'purchase-order']);
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
        return self::sendMail($vendor,$title,$description,$cc,$attachment,$mail_from,$mail_from_name);
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
        ],200);

    }

    public function poReport(Request $request)
    {
        $pathUrl = route('po.index', ['type' => request()->route('type')]);
        $orderType = ConstantHelper::PO_SERVICE_ALIAS;
        $poItems = PoItem::whereHas('po', function ($headerQuery) use($orderType, $pathUrl, $request) {
            $headerQuery -> where('type', $orderType) -> withDraftListingLogic();
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
                $itemQuery -> withWhereHas('po_items', function ($itemSubQuery) use($request) {
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
            $dynamicFields = DynamicFieldHelper::getServiceDynamicFields(ConstantHelper::PO_SERVICE_ALIAS);
            $datatables = DataTables::of($poItems) ->addIndexColumn()
            ->editColumn('status', function ($row) use($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->po->document_status ?? ConstantHelper::DRAFT];
                $displayStatus = ucfirst($row -> po -> document_status);
                $editRoute = null;
                $editRoute = route('po.edit', ['id' => $row->po->id,'type' => request()->route('type')]);
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

    private function savePoPaymentTerm($paymentTermId, $poId, $creditDays){
        $paymentTermDetails = PaymentTermDetail::where('payment_term_id',$paymentTermId)->get();

        if ($paymentTermDetails->isEmpty()) {
            return;
        }

        foreach($paymentTermDetails as $paymentTermDetail){
            $poPaymentTerm = ErpPoPaymentTerm::firstOrNew([
                'po_header_id' => $poId,
                'payment_term_id' => $paymentTermDetail->payment_term_id,
                'payment_term_detail_id' => $paymentTermDetail->id,
                'trigger_type' => $paymentTermDetail->trigger_type,
            ]);

            $poPaymentTerm->po_header_id = $poId;
            $poPaymentTerm->payment_term_id = $paymentTermDetail->payment_term_id;
            $poPaymentTerm->payment_term_detail_id = $paymentTermDetail->id;
            $poPaymentTerm->credit_days = $paymentTermDetail->trigger_type == ConstantHelper::POST_DELIVERY ? ($creditDays ? $creditDays : 0) : 0;
            $poPaymentTerm->percent = $paymentTermDetail->percent;
            $poPaymentTerm->trigger_type = $paymentTermDetail->trigger_type;
            $poPaymentTerm->save();
        }
    }

}
