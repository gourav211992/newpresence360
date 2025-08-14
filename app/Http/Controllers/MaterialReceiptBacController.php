<?php
namespace App\Http\Controllers;

use DB;
use Str;
use PDF;
use Auth;
use View;
use Session;
use stdClass;
use DateTime;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

use Illuminate\Http\Request;
use App\Http\Requests\MaterialReceiptRequest;
use App\Http\Requests\EditMaterialReceiptRequest;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\AlternateUOM;
use App\Models\MrnExtraAmount;
use App\Models\MrnItemLocation;

use App\Models\MrnHeaderHistory;
use App\Models\MrnDetailHistory;
use App\Models\MrnAttributeHistory;
use App\Models\MrnItemLocationHistory;
use App\Models\MrnExtraAmountHistory;

use App\Models\ErpItem;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Employee;
use App\Models\ErpVendor;
use App\Models\WhStructure;
use App\Models\WhItemMapping;

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
use App\Models\VendorAsn;
use App\Models\VendorBook;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\Organization;
use App\Models\ErpSaleOrder;
use App\Models\VendorAsnItem;
use App\Models\PurchaseOrder;
use App\Models\NumberPattern;
use App\Models\AttributeGroup;
use App\Models\GateEntryDetail;
use App\Models\GateEntryHeader;

use App\Models\StockLedger;
use App\Models\StockLedgerItemAttribute;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\BookHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\StoragePointHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\ServiceParametersHelper;

use App\Services\MrnService;
use App\Services\MrnDeleteService;
use App\Services\MrnCheckAndUpdateService;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Jobs\SendEmailJob;
use App\Services\CommonService;
use App\Models\ErpMrnDynamicField;
use App\Helpers\DynamicFieldHelper;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TransactionUploadItem;
use App\Imports\TransactionItemImport;
use App\Exports\MaterialReceiptExport;
use App\Exports\TransactionItemsExport;
use App\Services\ItemImportExportService;
use App\Exports\FailedTransactionItemsExport;
use App\Lib\Services\WHM\WhmJob;
use App\Models\ErpMiItem;
use App\Models\ErpSoItem;
use App\Models\ErpSoJobWorkItem;
use App\Models\ErpSubStoreParent;
use App\Models\JobOrder\JoBomMapping;
use App\Models\JobOrder\JobOrder;
use App\Models\JobOrder\JobOrderTed;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoProduct;
use App\Models\MrnJoItem;
use App\Models\VendorLocation;
use App\Models\PurchaseOrderTed;

class MaterialReceiptController extends Controller
{
    protected $mrnService;

    protected $organization_id;
    protected $group_id;
    protected $moduleType;


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
        $orderType = ConstantHelper::MRN_SERVICE_ALIAS;
        request() -> merge(['type' => $orderType]);
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = MrnHeader::with(
                [
                    'items',
                    'vendor',
                    'erpStore',
                    'erpSubStore',
                    'costCenters',
                    'currency',
                    'po',
                    'jobOrder',
                    'saleOrder'
                ]
            )
            ->withDefaultGroupCompanyOrg()
            ->withDraftListingLogic()
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
                ->addColumn('reference_number', function ($row) {
                    if ($row->reference_type === 'jo') {
                        // Multiple POs from related items
                        $joReferences = collect($row->items)
                            ->filter(function ($item) {
                                return isset($item->jo) && $item->jo; // only if jo exists
                            })
                            ->map(function ($item) {
                                return $item->jo->book_code . '-' . $item->jo->document_number;
                            })
                            ->unique() // avoid duplicates
                            ->implode(', '); // convert to comma-separated string
                
                        return $joReferences ?: 'N/A';
                    } elseif ($row->reference_type === 'po') {
                        // Multiple POs from related items
                        $joReferences = collect($row->items)
                            ->filter(function ($item) {
                                return isset($item->po) && $item->po; // only if po exists
                            })
                            ->map(function ($item) {
                                return $item->po->book_code . '-' . $item->po->document_number;
                            })
                            ->unique() // avoid duplicates
                            ->implode(', '); // convert to comma-separated string
                
                        return $joReferences ?: 'N/A';
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
                ->addColumn('store', function ($row) {
                    return strval($row->erpSubStore?->name) ?? 'N/A';
                })
                ->addColumn('cost_center', function ($row) {
                    return strval($row->costCenters?->name) ?? 'N/A';
                })
                ->addColumn('lot_no', function ($row) {
                    return strval($row->lot_number) ?? 'N/A';
                })
                ->addColumn('currency', function ($row) {
                    return strval($row->currency->short_name) ?? 'N/A';
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
        $user = Helper::getAuthenticatedUser();

        DB::beginTransaction();
        try {
            // dd($request->all());
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

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
            $isInspection = 1;

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request -> currency_id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }

            $mrn = new MrnHeader();
            $mrn->fill($request->all());
            $mrn->store_id = $request->header_store_id;
            $mrn->sub_store_id = $request->sub_store_id;
            $mrn->organization_id = $organization->id;
            $mrn->bill_to_follow = $request->bill_to_follow;
            $mrn->is_warehouse_required = $request->is_warehouse_required ?? 0;
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
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_number;
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
            $mrn->final_remarks = $request->remarks ?? null;
            $mrn->cost_center_id = $request->cost_center_id ?? '';

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
                    $inputQty = 0.00;
                    $balanceQty = 0.00;
                    $availableQty = 0.00;
                    $po_detail_id = null;
                    $purchaseOrderId = null;
                    $jo_detail_id = null;
                    $jobOrderId = null;
                    $gate_entry_detail_id = null;
                    $gateEntryHeaderId = null;
                    $supplier_inv_detail_id = null;
                    $so_id = null;
                    if($component['is_inspection'] == 1){
                        $isInspection = 0;
                    }
                    $refType = $request->input('reference_type');
                    $inputQty = floatval($component['order_qty'] ?? $component['accepted_qty'] ?? 0);
                    $item = Item::find($component['item_id'] ?? null);

                    if (!$item) {
                        \DB::rollBack();
                        return response()->json(['message' => 'Item not found.'], 422);
                    }

                    switch ($refType) {
                        case ConstantHelper::JO_SERVICE_ALIAS:
                            $result = self::processJobOrderComponent($component, $item, $inputQty);
                            break;
                    
                        case ConstantHelper::SO_SERVICE_ALIAS:
                            $result = self::processSaleOrderComponent($component, $item, $inputQty);
                            break;
                    
                        default:
                            $result = self::processPurchaseOrderComponent($component, $item, $inputQty);
                            break;
                    }
                    
                    if ($result !== true) {
                        return $result; // return response from updatePoQty or entry logic
                    }

                    if ($request->all()['reference_type'] == ConstantHelper::JO_SERVICE_ALIAS) {
                        if(isset($component['gate_entry_detail_id']) && $component['gate_entry_detail_id']){
                            $gateEntryDetail =  GateEntryDetail::find($component['gate_entry_detail_id']);
                            $gate_entry_detail_id = $gateEntryDetail->id ?? null;
                            $gateEntryHeaderId = $gateEntryDetail->header_id;
                            $joDetail =  JoProduct::find($component['jo_detail_id']);
                            $jo_detail_id = $joDetail->id ?? null;
                            $jobOrderId = $joDetail->jo_id;
                            if($gateEntryDetail){
                                $inputQty = ($component['order_qty'] ?? 0);
                                $balanceQty = ($gateEntryDetail->accepted_qty - ($gateEntryDetail->mrn_qty ?? 0.00));
                                if($balanceQty < $inputQty){
                                    DB::rollBack();
                                    return response()->json([
                                        'message' => 'Input qty can not be greater than balance qty.'
                                    ], 422);
                                }
                                $gateEntryDetail->mrn_qty += floatval($inputQty);
                                $gateEntryDetail->save();

                                $so_id = $joDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $joDetail, $inputQty, 'gate-entry');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            } else{
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Gate Entry Not Found'
                                ], 422);
                            }
                        } elseif (isset($component['vendor_asn_dtl_id']) && $component['vendor_asn_dtl_id']) {
                            $supplierInvDetail =  VendorAsnItem::find($component['vendor_asn_dtl_id']);
                            $jo_detail_id = $supplierInvDetail->jo_prod_id ?? null;
                            $asn_detail_id = $supplierInvDetail->id ?? null;
                            $jobOrderId = $supplierInvDetail?->jo_id;
                            $asnId = $supplierInvDetail?->vendor_asn_id;
                            $joDetail =  JoProduct::find($jo_detail_id);
                            if($supplierInvDetail){
                                $inputQty = ($component['order_qty'] ?? $component['accepted_qty']);
                                $balanceQty = ($supplierInvDetail->order_qty - ($gateEntryDetail->grn_qty ?? 0.00));
                                if($balanceQty < $inputQty){
                                    DB::rollBack();
                                    return response()->json([
                                        'message' => 'Input qty can not be greater than balance qty.'
                                    ], 422);
                                }
                                $supplierInvDetail->grn_qty += floatval($inputQty);
                                $supplierInvDetail->save();
                                $so_id = $joDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $joDetail, $inputQty, 'supplier-invoice');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            } else{
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Gate Entry Not Found'
                                ], 422);
                            }
                        } else {
                            if(isset($component['jo_detail_id']) && $component['jo_detail_id']){
                                $inputQty = ($component['order_qty'] ?? $component['accepted_qty']);
                                $balanceQty = 0.00;
                                $availableQty = 0.00;
                                $joDetail =  JoProduct::find($component['jo_detail_id']);
                                $jo_detail_id = $joDetail->id ?? null;
                                $jobOrderId = $joDetail->jo_id;
                                $so_id = $joDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $joDetail, $inputQty, 'job-order');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            }
                        }
                    }
                    elseif ($request->all()['reference_type'] == ConstantHelper::SO_SERVICE_ALIAS) {
                        if(isset($component['gate_entry_detail_id']) && $component['gate_entry_detail_id']){
                            $gateEntryDetail =  GateEntryDetail::find($component['gate_entry_detail_id']);
                            $gate_entry_detail_id = $gateEntryDetail->id ?? null;
                            $gateEntryHeaderId = $gateEntryDetail->header_id;
                            $poDetail =  ErpSoJobWorkItem::find($component['po_detail_id']);
                            $po_detail_id = $poDetail->id ?? null;
                            $purchaseOrderId = $poDetail->so_id;
                            if($gateEntryDetail){
                                $inputQty = ($component['order_qty'] ?? 0);
                                $balanceQty = ($gateEntryDetail->accepted_qty - ($gateEntryDetail->mrn_qty ?? 0.00));
                                if($balanceQty < $inputQty){
                                    DB::rollBack();
                                    return response()->json([
                                        'message' => 'Input qty can not be greater than balance qty.'
                                    ], 422);
                                }
                                $gateEntryDetail->mrn_qty += floatval($inputQty);
                                $gateEntryDetail->save();

                                // $so_id = $poDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $poDetail, $inputQty, 'sale-order');

                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            } else{
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Gate Entry Not Found'
                                ], 422);
                            }
                        } else{
                            if(isset($component['po_detail_id']) && $component['po_detail_id']){
                                $inputQty = ($component['order_qty'] ?? $component['accepted_qty']);
                                $balanceQty = 0.00;
                                $availableQty = 0.00;
                                $poDetail =  ErpSoJobWorkItem::find($component['po_detail_id']);
                                $po_detail_id = $poDetail->id ?? null;
                                $purchaseOrderId = $poDetail->sale_order_id;
                                $so_id = $component['purchase_order_id'];
                                $updatePoQty = self::updatePoQty($item, $poDetail, $inputQty, 'sale-order');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            }
                        }
                    }
                    else
                    {
                        if (isset($component['gate_entry_detail_id']) && $component['gate_entry_detail_id']) {
                            $gateEntryDetail =  GateEntryDetail::find($component['gate_entry_detail_id']);
                            $gate_entry_detail_id = $gateEntryDetail->id ?? null;
                            $gateEntryHeaderId = $gateEntryDetail->header_id;
                            $poDetail =  PoItem::find($component['po_detail_id']);
                            $po_detail_id = $poDetail->id ?? null;
                            $purchaseOrderId = $poDetail->purchase_order_id;
                            if ($gateEntryDetail) {
                                $inputQty = ($component['order_qty'] ?? 0);
                                $balanceQty = ($gateEntryDetail->accepted_qty - ($gateEntryDetail->mrn_qty ?? 0.00));
                                if ($balanceQty < $inputQty) {
                                    DB::rollBack();
                                    return response()->json([
                                        'message' => 'Input qty can not be greater than balance qty.'
                                    ], 422);
                                }
                                $gateEntryDetail->mrn_qty += floatval($inputQty);
                                $gateEntryDetail->save();

                                $so_id = $poDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $poDetail, $inputQty, 'gate-entry');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            } else {
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Gate Entry Not Found'
                                ], 422);
                            }
                        } elseif (isset($component['supplier_inv_detail_id']) && $component['supplier_inv_detail_id']) {
                            $supplierInvDetail =  PoItem::find($component['supplier_inv_detail_id']);
                            $supplier_inv_detail_id = $supplierInvDetail->id ?? null;
                            $supplierInvHeaderId = $supplierInvDetail->purchase_order_id;
                            $poDetail =  PoItem::find($component['po_detail_id']);
                            $po_detail_id = $poDetail->id ?? null;
                            $purchaseOrderId = $poDetail->purchase_order_id;
                            if ($supplierInvDetail) {
                                $inputQty = ($component['order_qty'] ?? $component['accepted_qty']);
                                $balanceQty = ($supplierInvDetail->order_qty - ($gateEntryDetail->grn_qty ?? 0.00));
                                if ($balanceQty < $inputQty) {
                                    DB::rollBack();
                                    return response()->json([
                                        'message' => 'Input qty can not be greater than balance qty.'
                                    ], 422);
                                }
                                $supplierInvDetail->grn_qty += floatval($inputQty);
                                $supplierInvDetail->save();
                                $so_id = $poDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $poDetail, $inputQty, 'supplier-invoice');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            } else {
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Gate Entry Not Found'
                                ], 422);
                            }
                        } else {
                            if (isset($component['po_detail_id']) && $component['po_detail_id']) {
                                $inputQty = ($component['order_qty'] ?? $component['accepted_qty']);
                                $balanceQty = 0.00;
                                $availableQty = 0.00;
                                $poDetail =  PoItem::find($component['po_detail_id']);
                                $po_detail_id = $poDetail->id ?? null;
                                $purchaseOrderId = $poDetail->purchase_order_id;
                                $so_id = $poDetail->so_id;
                                $updatePoQty = self::updatePoQty($item, $poDetail, $inputQty, 'purchase-order');
                                if($updatePoQty !== true) {
                                    return $updatePoQty;
                                }
                            }
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    $reqQty = ($component['accepted_qty'] ?? $component['order_qty']);
                    $inventoryUom = Unit::find($item->uom_id ?? null);
                    $itemUomId = $item->uom_id ?? null;
                    $inventory_uom_id = $inventoryUom->id;
                    $inventory_uom_code = $inventoryUom->name;
                    if(@$component['uom_id'] == $itemUomId) {
                        $inventory_uom_qty = floatval($reqQty) ?? 0.00 ;
                    } else {
                        $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $inventory_uom_qty = floatval($reqQty) * $alUom->conversion_to_inventory;
                        }
                    }

                    $itemValue = floatval($reqQty) * floatval($component['rate']);
                    $itemDiscount = floatval(@$component['discount_amount']) ?? 0.00;

                    $itemTotalValue += $itemValue;
                    $itemTotalDiscount += $itemDiscount;
                    $itemValueAfterDiscount = $itemValue - $itemDiscount;
                    $totalValueAfterDiscount += $itemValueAfterDiscount;
                    $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                    $uom = Unit::find($component['uom_id'] ?? null);
                    $mrnItemArr[] = [
                        'mrn_header_id' => $mrn->id,
                        'purchase_order_item_id' => $po_detail_id ?? null,
                        'po_id' => $purchaseOrderId ?? null,
                        'job_order_item_id' => $jo_detail_id ?? null,
                        'jo_id' => $jobOrderId ?? null,
                        'vendor_asn_id' => $component['vendor_asn_id'] ?? null,
                        'vendor_asn_item_id' => $component['vendor_asn_dtl_id'] ?? null,
                        'gate_entry_detail_id' => $gate_entry_detail_id ?? null,
                        'ge_id' => $gateEntryHeaderId ?? null,
                        'so_id' => $so_id ?? null,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'item_name' => $component['item_name'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'is_inspection' =>  $component['is_inspection'] ?? 0,
                        'order_qty' => floatval($component['order_qty']) ?? 0.00,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'rejected_qty' => floatval($component['rejected_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id ?? null,
                        'inventory_uom_code' => $inventory_uom_code ?? null,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'store_id' => $mrn->store_id ?? null,
                        'store_code' => $mrn?->erpStore?->store_code ?? null,
                        'sub_store_id' => $mrn->sub_store_id ?? null,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'discount_amount' => floatval(@$component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'expense_amount' => floatval($component['exp_amount_header']) ?? 0.00,
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
                        $billingAddress = $mrn->billingAddress;

                        $partyCountryId = isset($billingAddress) ? $billingAddress -> country_id : null;
                        $partyStateId = isset($billingAddress) ? $billingAddress -> state_id : null;
                        if($request->all()['reference_type'] !== ConstantHelper::SO_SERVICE_ALIAS)
                        {
                            $taxDetails = TaxHelper::calculateTax($mrnItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> hidden_country_id, $partyStateId ?? $request -> hidden_state_id, 'collection');
                        }

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
                    // $itemPriceAterBothDis =  $mrnItem['basic_value'] - $mrnItem['discount_amount'] - $mrnItem['header_discount_amount'];
                    // $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                    // $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;
                    $itemHeaderExp = floatval($mrnItem['expense_amount']);
                    $mrnDetail = new MrnDetail;
                    $mrnDetail->mrn_header_id = $mrnItem['mrn_header_id'];
                    $mrnDetail->purchase_order_item_id = $mrnItem['purchase_order_item_id'];
                    $mrnDetail->gate_entry_detail_id = $mrnItem['gate_entry_detail_id'];
                    $mrnDetail->job_order_item_id = $mrnItem['job_order_item_id'];
                    $mrnDetail->vendor_asn_id = $mrnItem['vendor_asn_id'];
                    $mrnDetail->vendor_asn_item_id = $mrnItem['vendor_asn_item_id'];
                    $mrnDetail->gate_entry_detail_id = $mrnItem['gate_entry_detail_id'];
                    $mrnDetail->po_id = $mrnItem['po_id'];
                    $mrnDetail->jo_id = $mrnItem['jo_id'];
                    $mrnDetail->ge_id = $mrnItem['ge_id'];
                    // $mrnDetail->sale_order_item_id = $mrnItem['sale_order_item_id'];
                    $mrnDetail->so_id = $mrnItem['so_id'];
                    $mrnDetail->item_id = $mrnItem['item_id'];
                    $mrnDetail->item_code = $mrnItem['item_code'];
                    $mrnDetail->item_name = $mrnItem['item_name'];
                    $mrnDetail->hsn_id = $mrnItem['hsn_id'];
                    $mrnDetail->hsn_code = $mrnItem['hsn_code'];
                    $mrnDetail->uom_id = $mrnItem['uom_id'];
                    $mrnDetail->uom_code = $mrnItem['uom_code'];
                    $mrnDetail->is_inspection = $mrnItem['is_inspection'];
                    $mrnDetail->order_qty = $mrnItem['order_qty'];
                    $mrnDetail->accepted_qty = $mrnItem['accepted_qty'];
                    $mrnDetail->rejected_qty = $mrnItem['rejected_qty'];
                    $mrnDetail->inventory_uom_id = $mrnItem['inventory_uom_id'];
                    $mrnDetail->inventory_uom_code = $mrnItem['inventory_uom_code'];
                    $mrnDetail->inventory_uom_qty = $mrnItem['inventory_uom_qty'];
                    $mrnDetail->store_id = $mrnItem['store_id'];
                    $mrnDetail->store_code = $mrnItem['store_code'];
                    $mrnDetail->sub_store_id = $mrnItem['sub_store_id'];
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

                    #Save item packets
                    $inventoryUomQuantity = 0.00;
                    if (!empty($component['storage_packets'])) {
                        $storagePoints = is_string($component['storage_packets'])
                            ? json_decode($component['storage_packets'], true)
                            : $component['storage_packets'];

                        if (is_array($storagePoints)) {
                            foreach ($storagePoints as $i => $val) {
                                $storagePoint = new MrnItemLocation();
                                $storagePoint->mrn_header_id = $mrn->id;
                                $storagePoint->mrn_detail_id = $mrnDetail->id;
                                $storagePoint->item_id = $mrnDetail->item_id;
                                $storagePoint->store_id = $mrnDetail->store_id;
                                $storagePoint->sub_store_id = $mrnDetail->sub_store_id;
                                $storagePoint->quantity = $val['quantity'] ?? 0.00;
                                $storagePoint->inventory_uom_qty = $val['quantity'] ?? 0.00;
                                $storagePoint->status = 'draft';
                                $storagePoint->save();

                                // ✅ Generate packet number if not present
                                $packetNumber = $mrn->book_code . '-' . $mrn->document_number . '-' . $mrnDetail->item_code . '-' . $mrnDetail->id . '-' . ($storagePoint->id ?? $i + 1);
                                // $storagePoint->packet_number = $val['packet_number'] ?? strtoupper(Str::random(rand(8, 10)));
                                $storagePoint->packet_number = $packetNumber;
                                $storagePoint->save();
                            }
                        } else {
                            \Log::warning("Invalid JSON for storage_points_data: " . print_r($component['storage_packets'], true));
                        }
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
                            $ted->po_id = $dis['e_purch_id'] ?? null;
                            $ted->jo_id = $dis['e_job_id'] ?? null;
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
                    $mrn->is_inspection_completion = $isInspection ?? 0;
                    $mrn->save();
                }
                elseif($request->all()['reference_type'] == ConstantHelper::SO_SERVICE_ALIAS)
                {
                    $mrn->sale_order_id = $purchaseOrderId ?? null;
                    $mrn->reference_type = $request->all()['reference_type'] ?? null;
                    $mrn->is_inspection_completion = $isInspection ?? 0;
                    $mrn->save();
                }
                else
                {
                    $mrn->purchase_order_id = $purchaseOrderId ?? null;
                    $mrn->reference_type = $request->all()['reference_type'] ?? null;
                    $mrn->is_inspection_completion = $isInspection ?? 0;
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
            }

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
            $lotNumber = date('Y/M/d', strtotime($mrn->document_date)) . '/' . $mrn->book_code . '/' . $mrn->document_number;
            $mrn->lot_number = strtoupper(@$lotNumber);
            $mrn->save();
            if($mrn){
                $invoiceLedger = self::maintainStockLedger($mrn);
                if($mrn->reference_type == ConstantHelper::JO_SERVICE_ALIAS)
                {
                    $errorStatus = self::checkRawMaterial($mrn);
                    if ($errorStatus) {
                        DB::rollBack();
                        return response() -> json([
                            'message' => $errorStatus,
                            'error' => ''
                        ], 422);
                    }
                }
                if($invoiceLedger['status'] == 'error') {
                    DB::rollBack();
                    return response()->json([
                        'message' => $invoiceLedger['message'],
                        'error' => ''
                    ], 422);
                }
            }

            $redirectUrl = '';
            if(($mrn->document_status == ConstantHelper::APPROVED) || ($mrn->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $mrn->id . '/pdf');
            }

            TransactionUploadItem::where('created_by', $user->id)->forceDelete();

            $status = DynamicFieldHelper::saveDynamicFields(ErpMrnDynamicField::class, $mrn -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            
            if(in_array($mrn->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)){
                (new WhmJob)->createJob($mrn->id,'App\Models\MrnHeader');
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

        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
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
        $mrn = MrnHeader::with([
            'vendor',
            'currency',
            'items',
            'book',
            'costCenters',
            'purchaseOrder',
            'jobOrder',
            'saleOrder'
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
        $view = 'procurement.material-receipt.edit';
        if($request->has('revisionNumber') && $request->revisionNumber != $mrn->revision_number) {
            $mrn = $mrn->source;
            $mrn = MrnHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('mrn_header_id', $mrn->mrn_header_id)
                ->first();
            $view = 'procurement.material-receipt.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mrn->document_status] ?? '';
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $store = $mrn->erpStore;
        $deliveryAddress = $store?->address?->display_address;
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;
        $subStoreCount = $mrn->items()->where('sub_store_id', '!=', null)->count() ?? 0;

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
            'subStoreCount' => $subStoreCount,
            'erpStores' => $erpStores,
            'dynamicFieldsUI' => $dynamicFieldsUI
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
            $componentCheck = true;
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
                    // ['model_type' => 'sub_detail', 'model_name' => 'MrnItemLocation', 'relation_column' => 'mrn_detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'MrnExtraAmount', 'relation_column' => 'mrn_detail_id']
                ];
                // $a = Helper::documentAmendment($revisionData, $id);
                $this->amendmentSubmit($request, $id);

            }

            $keys = ['deletedItemDiscTedIds', 'deletedHeaderDiscTedIds', 'deletedHeaderExpTedIds', 'deletedMrnItemIds', 'deletedItemLocationIds'];
            $deletedData = [];
            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }
            // ✅ Capture response
            $deleteService = new MrnDeleteService();
            $deleteResponse = $deleteService->deleteByRequest($deletedData, $mrn);
            if($deleteResponse['status'] === 'error') {
                \DB::rollBack();
                return response()->json([
                    'message' => $deleteResponse['message'],
                    'error' => ''
                ], 422);
            }

            # MRN Header save
            $totalTaxValue = 0.00;
            $mrn->gate_entry_no = $request->gate_entry_no ?? '';
            $mrn->store_id = $request->header_store_id;
            $mrn->sub_store_id = $request->sub_store_id;
            $mrn->gate_entry_date = $request->gate_entry_date ? date('Y-m-d', strtotime($request->gate_entry_date)) : '';
            $mrn->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $mrn->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $mrn->eway_bill_no = $request->eway_bill_no ?? '';
            $mrn->consignment_no = $request->consignment_no ?? '';
            $mrn->transporter_name = $request->transporter_name ?? '';
            $mrn->vehicle_no = $request->vehicle_no ?? '';
            $mrn->final_remarks = $request->remarks ?? '';
            $mrn->cost_center_id = $request->cost_center_id ?? '';
            $mrn->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            if(@$request->reference_type)
            {
                $mrn->reference_type = $request->reference_type;
            }
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
            $isInspection = 1;

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
                    if($component['is_inspection'] == 1){
                        $isInspection = 0;
                    }
                    $mrnDetail = MrnDetail::find($component['mrn_detail_id']);
                    $validateQty = self::validateQuantityBackend($component, $mrn->reference_type);
                    if ($validateQty['status'] === 'error') {
                        DB::rollBack();
                        return response()->json([
                            'message' => $validateQty['message']
                        ], 422);
                    }

                    if(isset($component['po_detail_id']) && $component['po_detail_id']) {
                        $poItem = PoItem::find($component['po_detail_id'] ?? $mrnDetail->purchase_order_item_id);
                        if(isset($poItem) && $poItem) {
                            if(isset($poItem->id) && $poItem->id) {
                                $orderQty = floatval($mrnDetail->order_qty);
                                $componentQty = floatval($component['order_qty'] ?? $component['accepted_qty']);
                                $qtyDifference = $componentQty - $orderQty;
                                if($qtyDifference) {
                                    $poItem->grn_qty += $qtyDifference;
                                }
                            } else {
                                // $poItem->order_qty += $component['qty'];
                            }
                            $poItem->save();
                        }
                    } else if(isset($component['jo_detail_id']) && $component['jo_detail_id']) {
                        $mrnDetail = MrnDetail::find($component['mrn_detail_id']);
                        $joItem = JoProduct::find($component['jo_detail_id'] ?? $mrnDetail->job_order_item_id);
                        if(isset($joItem) && $joItem) {
                            if(isset($joItem->id) && $joItem->id) {
                                $orderQty = floatval($mrnDetail->order_qty);
                                $componentQty = floatval($component['order_qty'] ?? $component['accepted_qty']);
                                $qtyDifference = $componentQty - $orderQty;
                                if($qtyDifference) {
                                    $joItem->grn_qty += $qtyDifference;
                                }
                            } else {
                                // $joItem->order_qty += $component['qty'];
                            }
                            $joItem->save();
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $inventory_uom_qty = 0.00;
                    $reqQty = $component['accepted_qty'] ?? $component['order_qty'];
                    $inventoryUom = Unit::find($item->uom_id ?? null);
                    $inventory_uom_id = $inventoryUom->id;
                    $inventory_uom_code = $inventoryUom->name;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $inventory_uom_qty = floatval($reqQty) ?? 0.00 ;
                    } else {
                        $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $inventory_uom_qty = floatval($reqQty) * $alUom->conversion_to_inventory;
                        }
                    }
                    $itemValue = floatval($reqQty) * floatval($component['rate']);
                    $itemDiscount = floatval(@$component['discount_amount']) ?? 0.00;

                    $itemTotalValue += $itemValue;
                    $itemTotalDiscount += $itemDiscount;
                    $itemValueAfterDiscount = $itemValue - $itemDiscount;
                    $totalValueAfterDiscount += $itemValueAfterDiscount;
                    $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                    $uom = Unit::find($component['uom_id'] ?? null);
                    $mrnItemArr[] = [
                        'mrn_header_id' => $mrn->id,
                        'purchase_order_item_id' => $component['po_detail_id'] ?? null,
                        'po_id' => $component['purchase_order_id'] ?? null,
                        'job_order_item_id' => $component['jo_detail_id'] ?? null,
                        'jo_id' => $component['job_order_id'] ?? null,
                        'vendor_asn_id' => $component['job_order_id'] ?? null,
                        'vendor_asn_item_id' => $component['vendor_asn_dtl_id'] ?? null,
                        'gate_entry_detail_id' => $component['gate_entry_detail_id'] ?? null,
                        'ge_id' => $component['gate_entry_header_id'] ?? null,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'item_name' => $component['item_name'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'is_inspection' =>  $component['is_inspection'] ?? 0,
                        'uom_code' => $uom->name ?? null,
                        'order_qty' => floatval($component['order_qty']) ?? 0.00,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'rejected_qty' => floatval($component['rejected_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id ?? null,
                        'inventory_uom_code' => $inventory_uom_code ?? null,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'store_id' => $mrn->store_id ?? null,
                        'store_code' => $mrn?->erpStore?->store_code ?? null,
                        'sub_store_id' => $mrn->sub_store_id ?? null,
                        'rate' => floatval($component['rate']) ?? 0.00,
                        'discount_amount' => floatval(@$component['discount_amount']) ?? 0.00,
                        'header_discount_amount' => 0.00,
                        'expense_amount' => floatval($component['exp_amount_header']) ?? 0.00,
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
                        $itemTax = 0;
                        $itemPrice = ($mrnItem['basic_value'] - $headerDiscount - $mrnItem['discount_amount']);
                        $billingAddress = $mrn->billingAddress;

                        $partyCountryId = isset($billingAddress) ? $billingAddress -> country_id : null;
                        $partyStateId = isset($billingAddress) ? $billingAddress -> state_id : null;
                        if(@$request->all()['reference_type'] !== ConstantHelper::SO_SERVICE_ALIAS)
                        {
                            $taxDetails = TaxHelper::calculateTax($mrnItem['hsn_id'], $itemPrice, $companyCountryId, $companyStateId, $partyCountryId ?? $request -> hidden_country_id, $partyStateId ?? $request -> hidden_state_id, 'collection');
                        }

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
                    // $itemPriceAterBothDis =  $mrnItem['basic_value'] - $mrnItem['discount_amount'] - $mrnItem['header_discount_amount'];
                    // $totalAfterTax =   $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
                    // $itemHeaderExp =  $itemPriceAterBothDis / $totalAfterTax * $totalHeaderExpense;
                    $itemHeaderExp = floatval($mrnItem['expense_amount']);

                    # Mrn Detail Save
                    $mrnDetail = MrnDetail::find($component['mrn_detail_id'] ?? null) ?? new MrnDetail;

                    $isNewItem = false;
                    if(isset($mrnDetail->item_id) && $mrnDetail->item_id) {
                        $isNewItem = $mrnDetail->item_id != ($mrnItem['item_id'] ?? null);
                    }

                    $mrnDetail->mrn_header_id = $mrnItem['mrn_header_id'];
                    $mrnDetail->purchase_order_item_id = $mrnItem['purchase_order_item_id'];
                    $mrnDetail->gate_entry_detail_id = $mrnItem['gate_entry_detail_id'];
                    $mrnDetail->job_order_item_id = $mrnItem['job_order_item_id'];
                    $mrnDetail->vendor_asn_id = $mrnItem['vendor_asn_id'];
                    $mrnDetail->vendor_asn_item_id = $mrnItem['vendor_asn_item_id'];
                    $mrnDetail->gate_entry_detail_id = $mrnItem['gate_entry_detail_id'];
                    $mrnDetail->po_id = $mrnItem['po_id'];
                    $mrnDetail->jo_id = $mrnItem['jo_id'];
                    $mrnDetail->ge_id = $mrnItem['ge_id'];
                    $mrnDetail->item_id = $mrnItem['item_id'];
                    $mrnDetail->item_code = $mrnItem['item_code'];
                    $mrnDetail->item_name = $mrnItem['item_name'];
                    $mrnDetail->hsn_id = $mrnItem['hsn_id'];
                    $mrnDetail->hsn_code = $mrnItem['hsn_code'];
                    $mrnDetail->uom_id = $mrnItem['uom_id'];
                    $mrnDetail->uom_code = $mrnItem['uom_code'];
                    $mrnDetail->is_inspection = $mrnItem['is_inspection'];
                    $mrnDetail->accepted_qty = $mrnItem['accepted_qty'];
                    $mrnDetail->inventory_uom_id = $mrnItem['inventory_uom_id'];
                    $mrnDetail->inventory_uom_code = $mrnItem['inventory_uom_code'];
                    $mrnDetail->inventory_uom_qty = $mrnItem['inventory_uom_qty'];
                    $mrnDetail->store_id = @$mrnItem['store_id'];
                    $mrnDetail->store_code = @$mrnItem['store_code'];
                    $mrnDetail->sub_store_id = @$mrnItem['sub_store_id'];
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
                    if ($isNewItem && $poDetail->id) {
                        MrnAttribute::where('mrn_detail_id', $mrnDetail->id)
                            ->delete();
                    }
                    foreach($mrnDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $mrnAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $mrnAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            
                            $mrnAttr = MrnAttribute::firstOrNew([
                                'mrn_header_id' => $mrn->id,
                                'mrn_detail_id' => $mrnDetail->id,
                                'item_attribute_id' => $itemAttribute->id
                            ]);
                            // $mrnAttr = MrnAttribute::find($mrnAttrId) ?? new MrnAttribute;
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

                    // #Save item packets
                    // $inventoryUomQuantity = 0.00;
                    // if (!empty($component['storage_packets'])) {
                    //     $storagePoints = is_string($component['storage_packets'])
                    //         ? json_decode($component['storage_packets'], true)
                    //         : $component['storage_packets'];

                    //     if (is_array($storagePoints)) {
                    //         foreach ($storagePoints as $i => $val) {
                    //             $storagePoint = MrnItemLocation::find(@$val['id']) ?? new MrnItemLocation;
                    //             $storagePoint->mrn_header_id = $mrn->id;
                    //             $storagePoint->mrn_detail_id = $mrnDetail->id;
                    //             $storagePoint->item_id = $mrnDetail->item_id;
                    //             $storagePoint->store_id = $mrnDetail->store_id;
                    //             $storagePoint->sub_store_id = $mrnDetail->sub_store_id;
                    //             $storagePoint->quantity = $val['quantity'] ?? 0.00;
                    //             $storagePoint->inventory_uom_qty = $val['quantity'] ?? 0.00;
                    //             $storagePoint->status = 'draft';
                    //             $storagePoint->save();

                    //             if(empty($val['packet_number'])){
                    //                 // ✅ Generate packet number if not present
                    //                 $packetNumber = $mrn->book_code . '-' . $mrn->document_number . '-' . $mrnDetail->item_code . '-' . $mrnDetail->id . '-' . ($storagePoint->id ?? $i + 1);
                    //                 // $storagePoint->packet_number = $val['packet_number'] ?? strtoupper(Str::random(rand(8, 10)));
                    //                 $storagePoint->packet_number = $packetNumber;
                    //                 $storagePoint->save();
                    //             }
                    //         }
                    //     } else {
                    //         \Log::warning("Invalid JSON for storage_points_data: " . print_r($component['storage_packets'], true));
                    //     }
                    // }
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

                if($request->document_status == ConstantHelper::SUBMITTED) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
                } else{
                    // No items left — reset all values
                    $mrn->total_discount = 0.00;
                    $mrn->taxable_amount = 0.00;
                    $mrn->total_taxes = 0.00;
                    $mrn->total_after_tax_amount = 0.00;
                    $mrn->expense_amount = 0.00;
                    $mrn->total_amount = 0.00;
                    $mrn->total_item_amount = 0.00;
                    $mrn->save();
                }
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
            $mrn->is_inspection_completion = $isInspection;
            $mrn->save();
            if($mrn && $mrn->items->count() > 0) {
                $invoiceLedger = self::maintainStockLedger($mrn);
                if($mrn->reference_type == ConstantHelper::JO_SERVICE_ALIAS)
                {
                    $errorStatus = self::checkRawMaterial($mrn);
                    if ($errorStatus) {
                        DB::rollBack();
                        return response() -> json([
                            'message' => $errorStatus,
                            'error' => ''
                        ], 422);
                    }
                }
                if($invoiceLedger['status'] == 'error') {
                    DB::rollBack();
                    return response()->json([
                        'message' => $invoiceLedger['message'],
                        'error' => ''
                    ], 422);
                }
            }

            $redirectUrl = '';
            if(($mrn->document_status == ConstantHelper::APPROVED) || ($mrn->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $mrn->id . '/pdf');
            }
            TransactionUploadItem::where('created_by', $user->id)->forceDelete();

            $status = DynamicFieldHelper::saveDynamicFields(ErpMrnDynamicField::class, $mrn -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }

            if(in_array($mrn->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)){
                (new WhmJob)->createJob($mrn->id,'App\Models\MrnHeader');
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
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.material-receipt.partials.item-row',compact('rowCount', 'locations'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $detailItemId = $request->mrn_detail_id ?? null;
        $checkAttr = intval($request->checkAttr) ?? 0;
        $itemAttIds = [];
        $itemAttributeArray = [];
        if($detailItemId) {
            $detail = MrnDetail::find($detailItemId);
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

        $html = view('procurement.material-receipt.partials.comp-attribute',compact('item','rowCount','selectedAttr','itemAttributes', 'checkAttr'))->render();
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
        $html = view('procurement.material-receipt.partials.add-disc-row',compact('tblRowCount','rowCount','disName','disAmount','disPerc'))->render();
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
            $html = view('procurement.material-receipt.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get Address
    public function getAddress(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $vendorId = $request?->id ?? null;
        $type = $request?->type ?? null;
        $typeId = $request?->typeId ?? null;

        $vendor = Vendor::withDefaultGroupCompanyOrg()
        ->with(['currency:id,name', 'paymentTerms:id,name'])->find($vendorId);

        $moduleTypeId = match ($type) {
            'po' => $typeId,
            'jo' => $typeId,
            default => $vendorId,
        };

        $typeData = match ($type) {
            'po' => PurchaseOrder::withDefaultGroupCompanyOrg()
                ->with(['currency:id,name', 'paymentTerms:id,name'])
                ->find($typeId),
            'jo' => JobOrder::withDefaultGroupCompanyOrg()
                ->with(['currency:id,name', 'paymentTerms:id,name'])
                ->find($typeId),
            default => Vendor::withDefaultGroupCompanyOrg()
                ->with(['currency:id,name', 'paymentTerms:id,name'])
                ->find($vendorId),
        };

        $currency = $typeData?->currency;
        $paymentTerm = $typeData?->paymentTerms;

        $documentDate = $request?->document_date;

        $vendorAddress = match ($type) {
            'po' => $typeData?->latestBillingAddress() ?? $typeData?->bill_address,
            'jo' => $typeData?->latestBillingAddress() ?? $typeData?->bill_address,
            default => ErpAddress::where('addressable_id', $moduleTypeId)
                ->where('addressable_type', Vendor::class)
                ->latest()
                ->first(),
        };

        if (!$vendorAddress) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Address not found for '. $vendor?->company_name
                )
            ]);
        }
        if (!isset($typeData->currency_id)) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Currency not found for '. $vendor?->company_name
                )
            ]);
        }
        if (!isset($paymentTerm)) {
            return response() -> json([
                'data' => array(
                    'error_message' => 'Payment Terms not found for '. $vendor?->company_name
                )
            ]);
        }
        $currencyData = CurrencyHelper::getCurrencyExchangeRates($typeData?->currency_id ?? 0, $documentDate ?? '');

        $storeId = $request?->store_id ?? null;
        $store = ErpStore::find($storeId);
        $locationAddress = $store?->address;

        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;

        return response()->json(
            [
                'data' => [
                    'status' => 200,
                    'vendor' =>$vendor,
                    'message' => 'fetched',
                    'currency' => $currency,
                    'org_address' => $orgAddress,
                    'paymentTerm' => $paymentTerm,
                    'vendor_address' => $vendorAddress,
                    'currency_exchange' => $currencyData
                ],
                'delivery_address' => $locationAddress,
            ]
        );
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
        $storeId = $request->store_id;
        $subStoreId = $request->sub_store_id;
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
        $purchaseOrder = '';
        $gateEntry = '';
        $poDetail = '';
        $mrn = MrnHeader::find($request->headerId);
        $totalCost = MrnJoItem::where('mrn_header_id', $request->headerId)->sum('total_cost');
        if($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
        }
        $remark = $request->remark ?? null;
        $totalStockData = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttr,  $storeId, $rackId, $shelfId, $binId);
        $storagePoints = StoragePointHelper::getStoragePoints($itemId, $qty, $storeId, $subStoreId);
        $gateEntry = '';
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $type = $request->type;
        if($type == 'po')
        {
            $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);
            if($purchaseOrder && $purchaseOrder->gate_entry_required == 'yes'){
                $gateEntry = GateEntryHeader::where('purchase_order_id', $purchaseOrder->id)->first();
            }
            $poDetail = PoItem::find($request->po_detail_id ?? $request->supplier_inv_detail_id);
        }
        if($type == 'jo')
        {
            $purchaseOrder = JobOrder::find($request->job_order_id);
            // if($purchaseOrder && $purchaseOrder->gate_entry_required == 'yes')
            if($purchaseOrder)
            {
                $gateEntry = GateEntryHeader::where('job_order_id', $purchaseOrder->id)->first();
            }
            $poDetail = JoProduct::find($request->jo_detail_id ?? $request->supplier_inv_detail_id);
        }
        if($type == 'so')
        {
            $purchaseOrder = ErpSaleOrder::find($request->sale_order_id);
            if($purchaseOrder && $purchaseOrder->gate_entry_required == 'yes')
            if($purchaseOrder)
            {
                // $gateEntry = GateEntryHeader::where('sale_order_id', $purchaseOrder->id)->first();
                $gateEntry = [];
            }
            $poDetail = ErpSoJobWorkItem::find($request->so_detail_id ?? $request->supplier_inv_detail_id);
        }
        
        $html = view(
            'procurement.material-receipt.partials.comp-item-detail',
            compact(
                'item',
                'purchaseOrder',
                'selectedAttr',
                'remark',
                'uomName',
                'qty',
                'totalStockData',
                'headerId',
                'detailId',
                'specifications',
                'poDetail',
                'gateEntry',
                'storagePoints',
                'type',
                'totalCost',
                'mrn'
            )
        )
        ->render();
        return response()->json(['data' => ['html' => $html, 'totalStockData' => $totalStockData, 'totalCost' => $totalCost], 'status' => 200, 'storagePoints' => $storagePoints, 'message' => 'fetched.']);
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
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
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
        $billingAddress = $mrn->billingAddress;

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
        $taxes = MrnExtraAmount::where('mrn_header_id', $mrn->id)
            ->where('ted_type', 'Tax')
            ->select('ted_type','ted_id','ted_name', 'ted_percentage', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assesment_amount) as total_assesment_amount'))
            ->groupBy('ted_name', 'ted_percentage')
            ->get();
        $sellerShippingAddress = $mrn->latestShippingAddress();
        $sellerBillingAddress = $mrn->latestBillingAddress();
        $buyerAddress = $mrn?->erpStore?->address;

        $pdf = PDF::loadView(
            'pdf.mrn',
            [
                'mrn' => $mrn,
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
            $mrnHeader = MrnHeader::find($id);
            if(!$mrnHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $mrnHeaderData = $mrnHeader->toArray();
            unset($mrnHeaderData['id']); // You might want to remove the primary key, 'id'
            $mrnHeaderData['mrn_header_id'] = $mrnHeader->id;
            $headerHistory = MrnHeaderHistory::create($mrnHeaderData);
            $headerHistoryId = $headerHistory->id;


            $vendorBillingAddress = $mrnHeader->billingAddress ?? null;
            $vendorShippingAddress = $mrnHeader->shippingAddress ?? null;

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
                $mediaFiles = $headerHistory->uploadDocuments($request->file('amend_attachment'), 'mrn', false);
            }
            $headerHistory->save();

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
            // $mrnHeader->status = "draft";
            // $mrnHeader->document_status = "draft";
            // $mrnHeader->save();

            /*Create document submit log*/
            if ($mrnHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $mrnHeader->series_id;
                $docId = $mrnHeader->id;
                $remarks = $mrnHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $mrnHeader->approval_level ?? 1;
                $revisionNumber = $mrnHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                $mrnHeader->document_status = $approveDocument['approvalStatus'];
            }
            $mrnHeader->save();

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $mrnHeader,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Amendment Submit Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while amendement.',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    // Validate Order Qty For Frontend
    public function validateQuantity(Request $request)
    {
        $inputData = [
            'item_id'            => $request->item_id,
            'purchase_order_id'  => $request->purchase_order_id,
            'po_detail_id'       => $request->po_detail_id,
            'job_order_id'       => $request->job_order_id,
            'jo_detail_id'       => $request->jo_detail_id,
            'sale_order_id'      => $request->sale_order_id,
            'so_detail_id'       => $request->so_detail_id,
            'ge_detail_id'       => $request->ge_detail_id,
            'asn_detail_id'      => $request->asn_detail_id,
            'mrn_detail_id'      => $request->mrn_detail_id,
            'qty'                => $request->qty,
            'type'               => $request->type,
        ];
        
        $checkService = new MrnCheckAndUpdateService();
        $data = $checkService->validateOrderQuantity($inputData);
        if ($data['status'] === 'success') {
            return response()->json(['message' => $data['message'], 'status' => 200, 'order_qty' => $data['order_qty']['order_qty'] ?? 0.00]);
        } else {
            return response()->json(['message' => $data['message'], 'status' => 422, 'order_qty' => $data['order_qty']['order_qty'] ?? 0.00]);
        }
    }

    # Validate Order Qty For Frontend
    private static function validateQuantityBackend($component, $refType)
    {
        $inputData = [
            'item_id'            => $component['item_id'],
            'purchase_order_id'  => $component['purchase_order_id'],
            'po_detail_id'       => $component['po_detail_id'],
            'job_order_id'       => $component['job_order_id'],
            'jo_detail_id'       => $component['jo_detail_id'],
            'sale_order_id'      => $component['sale_order_id'],
            'so_detail_id'       => $component['so_detail_id'],
            'ge_detail_id'       => $component['ge_detail_id'],
            'asn_detail_id'      => $component['asn_detail_id'],
            'mrn_detail_id'      => $component['mrn_detail_id'],
            'qty'                => $component['order_qty'],
            'type'               => $refType,
        ];
        
        $checkService = new MrnCheckAndUpdateService();
        $data = $checkService->validateOrderQuantity($inputData);
        return $data;
    }

    # Get PO/ASN/GE Item List
    public function getPo(Request $request)
    {
        $query = $this->buildPoQuery($request);
        return DataTables::of($query)
            ->addColumn('select_checkbox', function ($row) use ($request) {
                $this->moduleType = match (true) {
                    $row?->po?->gate_entry_required === 'yes' => 'gate-entry',
                    $row?->po?->supp_invoice_required === 'yes' => 'suppl-inv',
                    default => 'p-order',
                };
                $ref_no = match ($this->moduleType) {
                    'gate-entry' => ($row?->gateEntryHeader?->book?->book_code ?? 'NA') . '-' . ($row?->gateEntryHeader?->document_number ?? 'NA'),
                    'suppl-inv'  => ($row?->vendorAsn?->book_code ?? 'NA') . '-' . ($row?->vendorAsn?->document_number ?? 'NA'),
                    default      => ($row?->po?->book?->book_code ?? 'NA') . '-' . ($row?->po?->document_number ?? 'NA'),
                };
                $dataCurrentPo = match ($this->moduleType) {
                    'gate-entry' => $row->purchase_order_id ?? 'null',
                    'suppl-inv'  => $row->purchase_order_id ?? 'null',
                    default      => $row->purchase_order_id ?? 'null',
                };
                $dataCurrentAsn = match ($this->moduleType) {
                    'gate-entry' => 'null',
                    'suppl-inv'  => ($row->vendorAsn->id ?? 'null'),
                    default      => 'null',
                };
                $dataCurrentAsnItem = match ($this->moduleType) {
                    'gate-entry' => 'null',
                    'suppl-inv'  => ($row->asn_item_id ?? 'null'),
                    default      => 'null',
                };
                $dataCurrentGe = match ($this->moduleType) {
                    'gate-entry' => ($row->gateEntryHeader->id ?? 'null'),
                    'suppl-inv'  => 'null',
                    default      => 'null',
                };
                $dataCurrentGeItem = match ($this->moduleType) {
                    'gate-entry' => ($row->ge_item_id ?? 'null'),
                    'suppl-inv'  => 'null',
                    default      => 'null',
                };
                $dataExistingPo = $request->type == 'create' && $row?->purchase_order_id
                    ? ($request->selected_po_ids[0] ?? 'null')
                    : 'null';

                return "<div class='form-check form-check-inline me-0'>
                            <input class='form-check-input po_item_checkbox' type='checkbox' name='po_item_check' value='{$row->id}' data-module='{$this->moduleType}'
                            data-current-po='{$dataCurrentPo}' data-existing-po='{$dataExistingPo}'
                            data-current-asn='{$dataCurrentAsn}' data-current-asn-item='{$dataCurrentAsnItem}'
                            data-current-ge='{$dataCurrentGe}' data-current-ge-item='{$dataCurrentGeItem}'>
                            <input type='hidden' name='reference_no' id='reference_no' value='{$ref_no}'>
                        </div>";
            })
            ->addColumn('vendor', fn($row) => $row?->po?->vendor?->company_name ?? 'NA')
            ->addColumn('po_doc', fn($row) => ($row?->po?->book?->book_code ?? 'NA') . ' - ' . ($row?->po?->document_number ?? 'NA'))
            ->addColumn('po_date', fn($row) => $row?->po?->getFormattedDate('document_date') ?? '-')
            ->addColumn('si_doc', fn($row) => $row?->po?->supp_invoice_required == 'yes' ? ($row?->vendorAsn?->book_code ?? 'NA') . ' - ' . ($row?->vendorAsn?->document_number ?? 'NA') : '-')
            ->addColumn('si_date', fn($row) => $row?->po?->supp_invoice_required == 'yes' ? ($row?->vendorAsn?->getFormattedDate('document_date') ?? '-') : '-')
            ->addColumn('ge_doc', fn($row) => $row?->po?->gate_entry_required == 'yes' ? ($row?->gateEntryHeader?->book_code ?? 'NA') . ' - ' . ($row?->gateEntryHeader?->document_number ?? 'NA') : '-')
            ->addColumn('ge_date', fn($row) => $row?->po?->gate_entry_required == 'yes' ? ($row?->gateEntryHeader?->getFormattedDate('document_date') ?? '-') : '-')
            ->addColumn('item_code', fn($row) => $row?->item?->item_code ?? 'NA')
            ->addColumn('item_name', fn($row) => $row?->item?->item_name ?? 'NA')
            ->addColumn('attributes', function ($row) {
                return $row?->attributes->map(function ($attr) {
                    return "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->headerAttribute->name}</strong>: {$attr->headerAttributeValue->value}</span>";
                })->implode(' ');
            })
            ->addColumn('order_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format(($row->order_qty ?? 0), 2);
                } elseif ($this->moduleType === 'suppl-inv') {
                    return number_format(($row->order_qty ?? 0), 2);
                } else {
                    return number_format(($row->order_qty ?? 0), 2);
                }
            })
            ->addColumn('inv_order_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format(($row->supplied_qty ?? 0), 2);
                } elseif ($this->moduleType === 'suppl-inv') {
                    return number_format(($row->supplied_qty ?? 0), 2);
                } else {
                    return number_format(0, 2);
                }
            })
            ->addColumn('ge_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format((($row->gate_entry_qty ?? 0)), 2);
                } elseif ($this->moduleType === 'suppl-inv') {
                    return number_format(0, 2);
                } else {
                    return number_format(0, 2);
                }
            })
            ->addColumn('grn_qty', fn($row) => number_format(($row->mrn_qty ?? 0), 2))
            ->addColumn('balance_qty', function ($row) {
                return number_format(($row->balance_qty ?? 0), 2);
            })
            ->addColumn('rate', fn($row) => number_format(($row->rate ?? 0), 2))
            ->addColumn('total_amount', function ($row) {
                return number_format(($row->balance_qty ?? 0) * ($row->rate ?? 0), 2);
            })
            ->rawColumns([
                'select_checkbox', 'attributes', 'vendor', 'po_doc', 'po_date', 'si_doc', 'si_date', 'ge_doc', 'ge_date', 'item_name', 'order_qty', 'inv_order_qty', 'ge_qty', 'grn_qty', 'balance_qty', 'rate', 'total_amount'
            ])
            ->make(true);
    }

    # This for both bulk and single po
    protected function buildPoQuery($request)
    {
        $documentDate = $request->document_date ?? null;
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $storeId = $request->store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        if($request->type == 'create')
        {
            $decoded = urldecode(urldecode($request->selected_po_ids));
            $selected_po_ids = json_decode($decoded, true) ?? [];
        }
        else{
            $selected_po_ids = $request->selected_po_ids ?? [];
            $selected_po_ids = is_string($selected_po_ids)
                ? array_map('trim', explode(',', $selected_po_ids))
                : (is_array($selected_po_ids) ? $selected_po_ids : []);
        }

        $poData = '';
        $poItemIds = [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);

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
            ->whereRaw('(ROUND(order_qty - short_close_qty) > ROUND(grn_qty))')
            ->whereHas('item', function($item) use($itemSearch){
                $item->where('type', 'Goods');
                if ($itemSearch) {
                    $item->where(function ($query) use ($itemSearch) {
                        $query->where('erp_items.item_name', 'LIKE', "%{$itemSearch}%")
                            ->orWhere('erp_items.item_code', 'LIKE', "%{$itemSearch}%");
                    });
                }
            })
            ->with(['po', 'item', 'attributes', 'po.book', 'po.vendor'])
            ->whereHas('po', function ($po) use ($seriesId, $docNumber, $vendorId, $storeId) {
                $po->withDefaultGroupCompanyOrg();
                $po->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                if ($seriesId) {
                    $po->where('erp_purchase_orders.book_id', $seriesId);
                }
                if ($docNumber) {
                    $po->where('erp_purchase_orders.id', $docNumber);
                }
                if ($vendorId) {
                    $po->where('erp_purchase_orders.vendor_id', $vendorId);
                }
                if ($storeId) {
                    $po->where('erp_purchase_orders.store_id', $storeId);
                }
            });

        if ($itemId) {
            $poItems->where('item_id', $itemId);
        }

        if ($request->type == 'create' && count($selected_po_ids)) {
            $poData = PoItem::with('po')->whereIn('id', $selected_po_ids)->first();
            $poItems->whereNotIn('erp_po_items.id', $selected_po_ids);
        } elseif ($request->type == 'edit' && count($selected_po_ids)) {
            $poData = PoItem::with('po')->whereIn('purchase_order_id', $selected_po_ids)->first();
            $poItems->whereIn('erp_po_items.purchase_order_id', $selected_po_ids);
        }

        $poItems = $poItems->orderBy('po_id', 'desc')->get();
        $poItemIds = [];
        $poItemMap = [];

        foreach ($poItems as $poItem) {
            if ($poItem->gate_entry_required === 'yes') {
                $geItems = GateEntryDetail::where('purchase_order_item_id', $poItem->id)
                    ->whereRaw('(accepted_qty > mrn_qty)')
                    ->with(['gateEntryHeader', 'po_item']) // ensure po_item is loaded
                    ->get();
        
                foreach ($geItems as $geItem) {
                    $poItemKey = $geItem->purchase_order_item_id . '+' . $geItem->header_id;
        
                    if (!isset($poItemMap[$poItemKey])) {
                        $item = clone $geItem->po_item; // Clone to avoid overwriting references
                        $item->balance_qty = 0;
                        $item->gateEntryHeader = $geItem->gateEntryHeader;
                        $item->vendorAsn = $geItem->vendorAsn ?? null;
                        $item->ge_item_id = $geItem->id;
                        $item->mrn_qty = $geItem->mrn_qty ?? 0;
                        $item->supplied_qty = 0;
                        $item->gate_entry_qty = $geItem->accepted_qty ?? 0;
        
                        $poItemMap[$poItemKey] = $item;
                    }
        
                    $poItemMap[$poItemKey]->balance_qty += ($geItem->accepted_qty - $geItem->mrn_qty);
                }
            } elseif ($poItem->supp_invoice_required === 'yes') {
                $siItems = VendorAsnItem::where('po_item_id', $poItem->id)
                    ->whereRaw('((supplied_qty - short_close_qty) > grn_qty)')
                    ->with(['vendorAsn', 'vendorAsn.po', 'po_item'])
                    ->get();
        
                foreach ($siItems as $siItem) {
                    $poItemKey = $siItem->po_item_id . '+' . $siItem->vendor_asn_id;
        
                    if (!isset($poItemMap[$poItemKey])) {
                        $item = clone $siItem->po_item;
                        $item->balance_qty = 0;
                        $item->vendorAsn = $siItem->vendorAsn;
                        $item->mrn_qty = $siItem->grn_qty ?? 0;
                        $item->supplied_qty = $siItem->supplied_qty ?? 0;
                        $item->gate_entry_qty = $siItem->ge_qty ?? 0;
                        $item->asn_item_id = $siItem->id;
        
                        $poItemMap[$poItemKey] = $item;
                    }
        
                    $poItemMap[$poItemKey]->balance_qty += ($siItem->supplied_qty - $siItem->short_close_qty) - $siItem->grn_qty;
                }
            } else {
                $poItemKey = $poItem->id;
        
                if (!isset($poItemMap[$poItemKey])) {
                    $item = clone $poItem;
                    $item->mrn_qty = $poItem->grn_qty ?? 0;
                    $item->gate_entry_qty = $poItem->ge_qty ?? 0;
                    $item->supplied_qty = ($poItem->order_qty - $poItem->short_close_qty) - $poItem->asn_qty;
                    $item->balance_qty = ($poItem->order_qty - $poItem->short_close_qty) - $poItem->grn_qty;
        
                    $poItemMap[$poItemKey] = $item;
                }
            }
        }
        return $poItemMap;
    }

    // Process PO Item
    public function processPoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $type = 'po';
        $ids = json_decode($request->ids, true) ?? [];
        $asnIds = json_decode($request->asnIds, true) ?? [];
        $asnItemIds = json_decode($request->asnItemIds, true) ?? [];
        $geIds = json_decode($request->geIds, true) ?? [];
        $geItemIds = json_decode($request->geItemIds, true) ?? [];
        $moduleTypes = json_decode($request->moduleTypes, true) ?? [];
        $vendor = null;
        // Ensure all module types are the same
        if (count(array_unique($moduleTypes)) > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "Multiple different module types are not allowed."
            ]);
        }

        $vendorAsn = null;
        // Determine module type
        $moduleType = $moduleTypes[0] ?? null;
        $tableRowCount = $request->tableRowCount ?: 0;

        if ($moduleType === 'gate-entry') {
            $filteredGeIds = array_filter($geIds);
            $uniqueGeIds = array_unique($filteredGeIds);

            if (count($uniqueGeIds) > 1) {
                return response()->json([
                    'data' => ['pos' => ''],
                    'status' => 422,
                    'message' => "Multiple Gate Entry are not allowed."
                ]);
            }
            $geHeader = GateEntryHeader::whereIn('id', $uniqueGeIds)->first();

            $gateEntryItems = GateEntryDetail::whereIn('id', $geItemIds)
                ->with(['gateEntryHeader', 'po_item.item', 'po_item.attributes'])
                ->get();

            $poItems = $gateEntryItems->map(function ($geItem) {
                $poItem = $geItem->po_item;
                if ($poItem) {
                    $poItem->avail_order_qty = $geItem?->po_item?->order_qty;
                    $poItem->balance_qty = $geItem->accepted_qty;
                    $poItem->ge_id = $geItem->header_id;
                    $poItem->ge_item_id = $geItem->id;
                    $poItem->vendor_asn_id = $geItem->vendor_asn_id;
                    $poItem->vendor_asn_item_id = $geItem->vendor_asn_item_id;
                    $poItem->available_qty = (($geItem->accepted_qty ?? 0) - ($geItem->mrn_qty ?? 0));
                    $poItem->gateEntryHeader = $geItem->gateEntryHeader;
                }
                return $poItem;
            })->filter()->values();
            
            $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
        } elseif($moduleType === 'suppl-inv'){
            $filteredAsnIds = array_filter($asnIds);
            $uniqueAsnIds = array_unique($filteredAsnIds);

            if (count($uniqueAsnIds) > 1) {
                return response()->json([
                    'data' => ['pos' => ''],
                    'status' => 422,
                    'message' => "Multiple ASN are not allowed."
                ]);
            }
            $vendorAsn = VendorAsn::whereIn('id', $uniqueAsnIds)->first();

            $vendorAsnItems = VendorAsnItem::whereIn('id', $asnItemIds)
                ->with(['vendorAsn', 'po_item.item', 'po_item.attributes'])
                ->get();

            $poItems = $vendorAsnItems->map(function ($asnItem) {
                $poItem = $asnItem->po_item;
                if ($poItem) {
                    $poItem->avail_order_qty = $asnItem->order_qty;
                    $poItem->balance_qty = $asnItem->balance_qty;
                    $poItem->vendor_asn_id = $asnItem->vendor_asn_id;
                    $poItem->vendor_asn_item_id = $asnItem->id;
                    $poItem->available_qty = ((($asnItem->supplied_qty) - ($asnItem->short_close_qty ?? 0)) - ($asnItem->grn_qty ?? 0));
                    $poItem->vendorAsn = $asnItem->vendorAsn;
                }
                return $poItem;
            })->filter()->values();

            $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
        } else {
            $poItems = PoItem::whereIn('id', $ids)->get();
            foreach ($poItems as $poItem) {
                $poItem->avail_order_qty = $poItem->order_qty ?? 0;
                $poItem->available_qty = ((($poItem->order_qty ?? 0) - ($poItem->short_close_qty ?? 0)) - ($poItem->grn_qty ?? 0));
            }
            $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
        }

        $locations = InventoryHelper::getAccessibleLocations('stock');
        $pos = PurchaseOrder::whereIn('id', $uniquePoIds)->get();

        $purchaseData = PurchaseOrder::whereIn('id', $uniquePoIds)
            ->with(['items' => function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            }])
            ->get();

        $purchaseOrder = PurchaseOrder::whereIn('id', $uniquePoIds)->first();

        $finalExpenses = [];
        $poExpenses = PurchaseOrder::whereIn('id', $uniquePoIds)
            ->with(['headerExpenses' => function ($query) {
                $query->where('ted_level', 'H');
            }])
            ->get()
            ->keyBy('id');

        $selectedPoItemValues = PoItem::whereIn('id', $ids)
            ->select('purchase_order_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('purchase_order_id')
            ->pluck('total', 'purchase_order_id');

        $poValues = PoItem::whereIn('purchase_order_id', $uniquePoIds)
            ->select('purchase_order_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('purchase_order_id')
            ->pluck('total', 'purchase_order_id');

        foreach ($poExpenses as $poId => $po) {
            $poValue = $poValues[$poId] ?? 0;
            $selectedPoItemValue = $selectedPoItemValues[$poId] ?? 0;

            foreach ($po->headerExpenses as $expense) {
                $perc = $poValue > 0 ? ($expense->ted_amount / $poValue) * 100 : 0;
                $amount = number_format(($selectedPoItemValue * $perc / 100), 2);

                $finalExpenses[] = [
                    'id' => $expense->id,
                    'ref_type' => 'po',
                    'purchase_order_id' => $expense->purchase_order_id,
                    'ted_id' => $expense->ted_id,
                    'ted_name' => $expense->ted_name,
                    'ted_amount' => $amount,
                    'ted_perc' => round($perc, 8),
                ];
            }
        }

        $vendorId = $pos->pluck('vendor_id')->unique();
        if ($vendorId->count() > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "You can not select multiple vendors of PO items at a time."
            ]);
        } else {
            $vendor = Vendor::find($vendorId->first());
        }

        $html = view('procurement.material-receipt.partials.po-item-row', [
            'pos' => $pos,
            'type' => $type,
            'poItems' => $poItems,
            'locations' => $locations,
            'moduleType' => $moduleType,
            'purchaseData' => $purchaseData,
            'tableRowCount' => $tableRowCount
        ])->render();
        
        return response()->json([
            'data' => [
                'pos' => $html,
                'vendor' => $vendor,
                'vendorAsn' => $vendorAsn ?? null,
                'geHeader' => $geHeader ?? null,
                'moduleType' => $moduleType,
                'finalExpenses' => $finalExpenses,
                'purchaseOrder' => $purchaseOrder,
            ],
            'status' => 200,
            'message' => "fetched!"
        ]);
    }

    # Get JO/SI/GE Item List
    public function getJo(Request $request)
    {
        $query = $this->buildJoQuery($request);

        return DataTables::of($query)
            ->addColumn('select_checkbox', function ($row) use ($request) {
                $this->moduleType = match (true) {
                    $row?->jo?->gate_entry_required === 'yes' => 'gate-entry',
                    $row?->jo?->supp_invoice_required === 'yes' => 'suppl-inv',
                    default => 'j-order',
                };
                $ref_no = match ($this->moduleType) {
                    'gate-entry' => ($row?->gateEntryHeader?->book?->book_code ?? 'NA') . '-' . ($row?->gateEntryHeader?->document_number ?? 'NA'),
                    'suppl-inv'  => ($row?->vendorAsn?->book_code ?? 'NA') . '-' . ($row?->vendorAsn?->document_number ?? 'NA'),
                    default      => ($row?->jo?->book?->book_code ?? 'NA') . '-' . ($row?->jo?->document_number ?? 'NA'),
                };
                $dataCurrentJo = match ($this->moduleType) {
                    'gate-entry' => $row->jo_id ?? 'null',
                    'suppl-inv'  => $row->jo_id ?? 'null',
                    default      => $row->jo_id ?? 'null',
                };
                $dataCurrentAsn = match ($this->moduleType) {
                    'gate-entry' => 'null',
                    'suppl-inv'  => ($row->vendorAsn->id ?? 'null'),
                    default      => 'null',
                };
                $dataCurrentAsnItem = match ($this->moduleType) {
                    'gate-entry' => 'null',
                    'suppl-inv'  => ($row->asn_item_id ?? 'null'),
                    default      => 'null',
                };
                $dataCurrentGe = match ($this->moduleType) {
                    'gate-entry' => ($row->gateEntryHeader->id ?? 'null'),
                    'suppl-inv'  => 'null',
                    default      => 'null',
                };
                $dataCurrentGeItem = match ($this->moduleType) {
                    'gate-entry' => ($row->ge_item_id ?? 'null'),
                    'suppl-inv'  => 'null',
                    default      => 'null',
                };
                $dataExistingJo = $request->type == 'create' && $row?->jo_id
                    ? ($request->selected_jo_ids[0] ?? 'null')
                    : 'null';

                return "<div class='form-check form-check-inline me-0'>
                            <input class='form-check-input jo_item_checkbox' type='checkbox' name='jo_item_check' value='{$row->id}' data-module='{$this->moduleType}'
                            data-current-jo='{$dataCurrentJo}' data-existing-jo='{$dataExistingJo}'
                            data-current-asn='{$dataCurrentAsn}' data-current-asn-item='{$dataCurrentAsnItem}'
                            data-current-ge='{$dataCurrentGe}' data-current-ge-item='{$dataCurrentGeItem}'>
                            <input type='hidden' name='reference_no' id='reference_no' value='{$ref_no}'>
                        </div>";
            })
            ->addColumn('vendor', fn($row) => $row?->jo?->vendor?->company_name ?? 'NA')
            ->addColumn('jo_doc', fn($row) => ($row?->jo?->book_code ?? 'NA') . ' - ' . ($row?->jo?->document_number ?? 'NA'))
            ->addColumn('jo_date', fn($row) => $row?->jo?->getFormattedDate('document_date') ?? '-')
            ->addColumn('si_doc', fn($row) => $row?->jo?->supp_invoice_required == 'yes' ? ($row?->vendorAsn?->book_code ?? 'NA') . ' - ' . ($row?->vendorAsn?->document_number ?? 'NA') : '-')
            ->addColumn('si_date', fn($row) => $row?->jo?->supp_invoice_required == 'yes' ? ($row?->vendorAsn?->getFormattedDate('document_date') ?? '-') : '-')
            ->addColumn('ge_doc', fn($row) => $row?->jo?->gate_entry_required == 'yes' ? ($row?->gateEntryHeader?->book_code ?? 'NA') . ' - ' . ($row?->gateEntryHeader?->document_number ?? 'NA') : '-')
            ->addColumn('ge_date', fn($row) => $row?->jo?->gate_entry_required == 'yes' ? ($row?->gateEntryHeader?->getFormattedDate('document_date') ?? '-') : '-')
            ->addColumn('item_code', fn($row) => $row?->item?->item_code ?? 'NA')
            ->addColumn('item_name', fn($row) => $row?->item?->item_name ?? 'NA')
            ->addColumn('attributes', function ($row) {
                return $row?->attributes->map(function ($attr) {
                    return "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->headerAttribute->name}</strong>: {$attr->headerAttributeValue->value}</span>";
                })->implode(' ');
            })
            ->addColumn('order_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format(($row->order_qty ?? 0), 2);
                } elseif ($this->moduleType === 'suppl-inv') {
                    return number_format(($row->order_qty ?? 0), 2);
                } else {
                    return number_format(($row->order_qty ?? 0), 2);
                }
            })
            ->addColumn('inv_order_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format(($row->supplied_qty ?? 0), 2);
                } elseif ($this->moduleType === 'suppl-inv') {
                    return number_format(($row->supplied_qty ?? 0), 2);
                } else {
                    return number_format(0, 2);
                }
            })
            ->addColumn('ge_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format((($row->gate_entry_qty ?? 0)), 2);
                } elseif ($this->moduleType === 'suppl-inv') {
                    return number_format(0, 2);
                } else {
                    return number_format(0, 2);
                }
            })
            ->addColumn('grn_qty', fn($row) => number_format(($row->mrn_qty ?? 0), 2))
            ->addColumn('balance_qty', function ($row) {
                return number_format(($row->balance_qty ?? 0), 2);
            })
            ->addColumn('rate', fn($row) => number_format(($row->rate ?? 0), 2))
            ->addColumn('total_amount', function ($row) {
                return number_format(($row->balance_qty ?? 0) * ($row->rate ?? 0), 2);
            })
            ->rawColumns([
                'select_checkbox', 'attributes', 'vendor', 'jo_doc', 'jo_date', 'si_doc', 'si_date', 'ge_doc', 'ge_date', 'item_name', 'order_qty', 'inv_order_qty', 'ge_qty', 'grn_qty', 'balance_qty', 'rate', 'total_amount'
            ])
            ->make(true);
    }


    # This for both bulk and single jo
    protected function buildJoQuery(Request $request)
    {
        $documentDate = $request->document_date ?? null;
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $storeId = $request->store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;

        if($request->type == 'create')
        {
            $decoded = urldecode(urldecode($request->selected_jo_ids));
            $selected_jo_ids = json_decode($decoded, true) ?? [];
        }
        else{
            $selected_jo_ids = $request->selected_jo_ids ?? [];
            $selected_jo_ids = is_string($selected_jo_ids)
                ? array_map('trim', explode(',', $selected_jo_ids))
                : (is_array($selected_jo_ids) ? $selected_jo_ids : []);
        }

        $poData = '';
        $joItemIds = [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);

        $joItems = JoProduct::select(
                'erp_jo_products.*',
                'erp_job_orders.id as job_id',
                'erp_job_orders.vendor_id as vendor_id',
                'erp_job_orders.book_id as book_id',
                'erp_job_orders.gate_entry_required as gate_entry_required',
                'erp_job_orders.supp_invoice_required as supp_invoice_required'
            )
            ->leftJoin('erp_job_orders', 'erp_job_orders.id', 'erp_jo_products.jo_id')
            ->whereIn('erp_job_orders.book_id', $applicableBookIds)
            ->whereRaw('(ROUND(order_qty - short_close_qty) > ROUND(grn_qty))')
            ->whereHas('item', function($item) use($itemSearch){
                $item->where('type', 'Goods');
                if ($itemSearch) {
                    $item->where(function ($query) use ($itemSearch) {
                        $query->where('erp_items.item_name', 'LIKE', "%{$itemSearch}%")
                            ->orWhere('erp_items.item_code', 'LIKE', "%{$itemSearch}%");
                    });
                }
            })
            ->with(['jo', 'item', 'attributes', 'jo.book', 'jo.vendor'])
            ->whereHas('jo', function ($jo) use ($seriesId, $docNumber, $vendorId, $storeId) {
                $jo->withDefaultGroupCompanyOrg();
                $jo->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                if ($seriesId) {
                    $jo->where('erp_job_orders.book_id', $seriesId);
                }
                if ($docNumber) {
                    $jo->where('erp_job_orders.id', $docNumber);
                }
                if ($vendorId) {
                    $jo->where('erp_job_orders.vendor_id', $vendorId);
                }
                if ($storeId) {
                    $jo->where('erp_job_orders.store_id', $storeId);
                }
            });

        if ($itemId) {
            $joItems->where('item_id', $itemId);
        }

        if ($request->type == 'create' && count($selected_jo_ids)) {
            $joData = JoProduct::with('jo')->whereIn('id', $selected_jo_ids)->first();
            $joItems->whereNotIn('erp_jo_products.id', $selected_jo_ids);
        } elseif ($request->type == 'edit' && count($selected_jo_ids)) {
            $joData = JoProduct::with('jo')->whereIn('jo_id', $selected_jo_ids)->first();
            $joItems->whereIn('erp_jo_products.jo_id', $selected_jo_ids);
        }

        $joItems = $joItems->orderBy('jo_id', 'desc')->get();
        $joItemIds = [];
        $joItemMap = [];
        foreach ($joItems as $joItem) {
            if ($joItem->gate_entry_required === 'yes') {
                $geItems = GateEntryDetail::where('job_order_item_id', $joItem->id)
                    ->whereRaw('(accepted_qty > mrn_qty)')
                    ->with(['gateEntryHeader', 'jo_item']) // ensure po_item is loaded
                    ->get();
        
                foreach ($geItems as $geItem) {
                    $joItemKey = $geItem->job_order_item_id . '+' . $geItem->header_id;
        
                    if (!isset($joItemMap[$joItemKey])) {
                        $item = clone $geItem->jo_item; // Clone to avoid overwriting references
                        $item->balance_qty = 0;
                        $item->gateEntryHeader = $geItem->gateEntryHeader;
                        $item->vendorAsn = $geItem->vendorAsn ?? null;
                        $item->ge_item_id = $geItem->id;
                        $item->mrn_qty = $geItem->mrn_qty ?? 0;
                        $item->supplied_qty = 0;
                        $item->gate_entry_qty = $geItem->accepted_qty ?? 0;
        
                        $joItemMap[$joItemKey] = $item;
                    }
        
                    $joItemMap[$joItemKey]->balance_qty += ($geItem->accepted_qty - $geItem->mrn_qty);
                }
            } elseif ($joItem->supp_invoice_required === 'yes') {
                $siItems = VendorAsnItem::where('jo_prod_id', $joItem->id)
                    ->whereRaw('((supplied_qty - short_close_qty) > grn_qty)')
                    ->with(['vendorAsn', 'vendorAsn.po', 'jo_item'])
                    ->get();
        
                foreach ($siItems as $siItem) {
                    $joItemKey = $siItem->jo_prod_id . '+' . $siItem->vendor_asn_id;
        
                    if (!isset($joItemMap[$joItemKey])) {
                        $item = clone $siItem->jo_item;
                        $item->balance_qty = 0;
                        $item->vendorAsn = $siItem->vendorAsn;
                        $item->mrn_qty = $siItem->grn_qty ?? 0;
                        $item->supplied_qty = $siItem->supplied_qty ?? 0;
                        $item->gate_entry_qty = $siItem->ge_qty ?? 0;
                        $item->asn_item_id = $siItem->id;
        
                        $joItemMap[$joItemKey] = $item;
                    }
        
                    $joItemMap[$joItemKey]->balance_qty += ($siItem->supplied_qty - $siItem->short_close_qty) - $siItem->grn_qty;
                }
            } else {
                $joItemKey = $joItem->id;
        
                if (!isset($joItemMap[$joItemKey])) {
                    $item = clone $joItem;
                    $item->mrn_qty = $joItem->grn_qty ?? 0;
                    $item->gate_entry_qty = $joItem->ge_qty ?? 0;
                    $item->supplied_qty = ($joItem->order_qty - $joItem->short_close_qty) - $joItem->asn_qty;
                    $item->balance_qty = ($joItem->order_qty - $joItem->short_close_qty) - $joItem->grn_qty;
        
                    $joItemMap[$joItemKey] = $item;
                }
            }
        }
        return $joItemMap;
    }

    // Process JO Item
    public function processJoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $type = 'jo';
        $ids = json_decode($request->ids, true) ?? [];
        $asnIds = json_decode($request->asnIds, true) ?? [];
        $asnItemIds = json_decode($request->asnItemIds, true) ?? [];
        $geIds = json_decode($request->geIds, true) ?? [];
        $geItemIds = json_decode($request->geItemIds, true) ?? [];
        $moduleTypes = json_decode($request->moduleTypes, true) ?? [];
        $vendor = null;
        // Ensure all module types are the same
        if (count(array_unique($moduleTypes)) > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "Multiple different module types are not allowed."
            ]);
        }

        $vendorAsn = null;
        // Determine module type
        $moduleType = $moduleTypes[0] ?? null;
        $tableRowCount = $request->tableRowCount ?: 0;

        if ($moduleType === 'gate-entry') {
            $filteredGeIds = array_filter($geIds);
            $uniqueGeIds = array_unique($filteredGeIds);

            if (count($uniqueGeIds) > 1) {
                return response()->json([
                    'data' => ['pos' => ''],
                    'status' => 422,
                    'message' => "Multiple Gate Entry are not allowed."
                ]);
            }
            $geHeader = GateEntryHeader::whereIn('id', $uniqueGeIds)->first();

            $gateEntryItems = GateEntryDetail::whereIn('id', $geItemIds)
                ->with(['gateEntryHeader', 'jo_item.item', 'jo_item.attributes'])
                ->get();

            $joItems = $gateEntryItems->map(function ($geItem) {
                $joItem = $geItem->jo_item;
                if ($joItem) {
                    $joItem->avail_order_qty = $geItem?->jo_item?->order_qty;
                    $joItem->balance_qty = $geItem->accepted_qty;
                    $joItem->ge_id = $geItem->header_id;
                    $joItem->ge_item_id = $geItem->id;
                    $joItem->vendor_asn_id = $geItem->vendor_asn_id;
                    $joItem->vendor_asn_item_id = $geItem->vendor_asn_item_id;
                    $joItem->available_qty = (($geItem->accepted_qty ?? 0) - ($geItem->mrn_qty ?? 0));
                    $joItem->gateEntryHeader = $geItem->gateEntryHeader;
                }
                return $joItem;
            })->filter()->values();
            
            $uniqueJoIds = $joItems->pluck('jo_id')->unique()->toArray();
        } elseif($moduleType === 'suppl-inv'){
            $filteredAsnIds = array_filter($asnIds);
            $uniqueAsnIds = array_unique($filteredAsnIds);

            if (count($uniqueAsnIds) > 1) {
                return response()->json([
                    'data' => ['pos' => ''],
                    'status' => 422,
                    'message' => "Multiple ASN are not allowed."
                ]);
            }
            $vendorAsn = VendorAsn::whereIn('id', $uniqueAsnIds)->first();

            $vendorAsnItems = VendorAsnItem::whereIn('id', $asnItemIds)
                ->with(['vendorAsn', 'jo_item.item', 'jo_item.attributes'])
                ->get();

            $joItems = $vendorAsnItems->map(function ($asnItem) {
                $joItem = $asnItem->jo_item;
                if ($joItem) {
                    $joItem->avail_order_qty = $asnItem->order_qty;
                    $joItem->balance_qty = $asnItem->balance_qty;
                    $joItem->vendor_asn_id = $asnItem->vendor_asn_id;
                    $joItem->vendor_asn_item_id = $asnItem->id;
                    $joItem->available_qty = ((($asnItem->supplied_qty) - ($asnItem->short_close_qty ?? 0)) - ($asnItem->grn_qty ?? 0));
                    $joItem->vendorAsn = $asnItem->vendorAsn;
                }
                return $joItem;
            })->filter()->values();

            $uniqueJoIds = $joItems->pluck('jo_id')->unique()->toArray();
        } else {
            $joItems = JoProduct::whereIn('id', $ids)->get();
            foreach ($joItems as $joItem) {
                $joItem->avail_order_qty = $joItem->order_qty ?? 0;
                $joItem->available_qty = ((($joItem->order_qty ?? 0) - ($joItem->short_close_qty ?? 0)) - ($joItem->grn_qty ?? 0));
            }
            $uniqueJoIds = $joItems->pluck('jo_id')->unique()->toArray();
        }

        $locations = InventoryHelper::getAccessibleLocations('stock');
        $pos = JobOrder::whereIn('id', $uniqueJoIds)->get();

        $jobData = JobOrder::whereIn('id', $uniqueJoIds)
            ->with(['items' => function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            }])
            ->get();

        $jobOrder = JobOrder::whereIn('id', $uniqueJoIds)->first();

        $finalExpenses = [];
        $joExpenses = JobOrder::whereIn('id', $uniqueJoIds)
            ->with(['headerExpenses' => function ($query) {
                $query->where('ted_level', 'H');
            }])
            ->get()
            ->keyBy('id');

        $selectedJoItemValues = JoProduct::whereIn('id', $ids)
            ->select('jo_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('jo_id')
            ->pluck('total', 'jo_id');

        $joValues = JoProduct::whereIn('jo_id', $uniqueJoIds)
            ->select('jo_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('jo_id')
            ->pluck('total', 'jo_id');

        foreach ($joExpenses as $joId => $jo) {
            $joValue = $joValues[$joId] ?? 0;
            $selectedJoItemValue = $selectedJoItemValues[$joId] ?? 0;

            foreach ($jo->headerExpenses as $expense) {
                $perc = $joValue > 0 ? ($expense->ted_amount / $joValue) * 100 : 0;
                $amount = number_format(($selectedJoItemValue * $perc / 100), 2);

                $finalExpenses[] = [
                    'id' => $expense->id,
                    'ref_type' => 'jo',
                    'job_order_id' => $expense->jo_id,
                    'ted_id' => $expense->ted_id,
                    'ted_name' => $expense->ted_name,
                    'ted_amount' => $amount,
                    'ted_perc' => round($perc, 8),
                ];
            }
        }

        $vendorId = $pos->pluck('vendor_id')->unique();
        if ($vendorId->count() > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "You can not select multiple vendors of JO items at a time."
            ]);
        } else {
            $vendor = Vendor::find($vendorId->first());
        }

        $html = view('procurement.material-receipt.partials.jo-item-row', [
            'pos' => $pos,
            'type' => $type,
            'poItems' => $joItems,
            'locations' => $locations,
            'purchaseData' => $jobData,
            'moduleType' => $moduleType,
            'tableRowCount' => $tableRowCount
        ])->render();
        
        return response()->json([
            'data' => [
                'pos' => $html,
                'vendor' => $vendor,
                'vendorAsn' => $vendorAsn ?? null,
                'geHeader' => $geHeader ?? null,
                'moduleType' => $moduleType,
                'finalExpenses' => $finalExpenses,
                'jobOrder' => $jobOrder,
            ],
            'status' => 200,
            'message' => "fetched!"
        ]);
    }

     # Get PO/SI/GE Item List
    public function getSo(Request $request)
    {
        $query = $this->buildSoQuery($request);

        return DataTables::of($query)
            ->addColumn('select_checkbox', function ($row) use ($request) {
                $this->moduleType = match (true) {
                    $row?->header?->gate_entry_required === 'yes' => 'gate-entry',
                    default => 'w-order',
                };

                $ref_no = match ($this->moduleType) {
                    'gate-entry' => ($row?->gateEntryHeader?->book?->book_code ?? 'NA') . '-' . ($row?->gateEntryHeader?->document_number ?? 'NA'),
                    default      => ($row?->header?->book?->book_code ?? 'NA') . '-' . ($row?->header?->document_number ?? 'NA'),
                };

                $dataCurrentSo = match ($this->moduleType) {
                    'gate-entry' => $row->purchase_order_id ?? 'null',
                    default      => $row->sale_order_id ?? 'null',
                };

                $ref_no = ($row?->header?->book_code ?? 'NA') . '-' . ($row?->header?->document_number ?? 'NA');

                $dataCurrentSo = ($row->sale_order_id ?? 'null');
                $dataExistingSo = $request->type == 'create' && $row?->sale_order_id
                    ? ($request->selected_so_ids[0] ?? 'null')
                    : 'null';
                // $disabled = ($dataExistingPo !== 'null' && $dataExistingPo != $row->purchase_order_id) ? 'disabled' : '';

                return "<div class='form-check form-check-inline me-0'>
                            <input class='form-check-input so_item_checkbox' type='checkbox' name='so_item_check' value='{$row->id}' data-module='{$this->moduleType}' data-current-so='{$dataCurrentSo}' data-existing-so='{$dataExistingSo}'>
                            <input type='hidden' name='reference_no' id='reference_no' value='{$ref_no}'>
                        </div>";
            })
            ->addColumn('vendor', fn($row) => $row?->header?->vendor?->company_name ?? 'NA')
            ->addColumn('so_doc', fn($row) => ($row?->header?->book_code ?? 'NA') . ' - ' . ($row?->header?->document_number ?? 'NA'))
            ->addColumn('so_date', fn($row) => $row?->header?->getFormattedDate('document_date') ?? '-')
            ->addColumn('ge_doc', fn($row) => $row?->header?->gate_entry_required == 'yes' ? ($row?->gateEntryHeader?->book_code ?? 'NA') . ' - ' . ($row?->gateEntryHeader?->document_number ?? 'NA') : '-')
            ->addColumn('ge_date', fn($row) => $row?->header?->gate_entry_required == 'yes' ? ($row?->gateEntryHeader?->getFormattedDate('document_date') ?? '-') : '-')
            ->addColumn('item_code', fn($row) => $row?->item?->item_code ?? 'NA')
            ->addColumn('item_name', fn($row) => $row?->item?->item_name ?? 'NA')
            ->addColumn('attributes', function ($row) {
                return $row?->attributes->map(function ($attr) {
                    return "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->headerAttribute->name}</strong>: {$attr->headerAttributeValue->value}</span>";
                })->implode(' ');
            })
            ->addColumn('order_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format(($row?->so_item?->order_qty ?? 0), 2);
                }
                else {
                    return number_format(($row->qty ?? 0), 2);
                }
            })
            ->addColumn('inv_order_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format((($row->supplied_qty ?? 0) - ($row->short_close_qty ?? 0)), 2);
                } else {
                    return number_format(0, 2);
                }
            })
            ->addColumn('ge_qty', function ($row) {
                if ($this->moduleType === 'gate-entry') {
                    return number_format((($row->accepted_qty ?? 0)), 2);
                } else {
                    return number_format(0, 2);
                }
            })
            ->addColumn('grn_qty', fn($row) => number_format(($row->grn_qty ?? 0), 2))
            ->addColumn('balance_qty', function ($row) {
                $orderQty = 0;
                $grnQty = $row->grn_qty ?? $row->mrn_qty;
                if ($this->moduleType === 'gate-entry') {
                    $orderQty = ($row->qty ?? 0);
                } else {
                    $orderQty = ($row->qty ?? 0);
                }
                return number_format(($orderQty - $grnQty), 2);
            })
            ->addColumn('rate', fn($row) => number_format(($row->rate ?? 0), 2))
            ->addColumn('total_amount', function ($row) {
                $orderQty = 0;
                $grnQty = $row->grn_qty ?? $row->mrn_qty;
                if ($this->moduleType === 'gate-entry') {
                    $orderQty = ($row->qty ?? 0);
                } else {
                    $orderQty = ($row->qty ?? 0);
                }
                return number_format(($orderQty - $grnQty) * ($row->rate ?? 0), 2);
            })
            ->rawColumns([
                'select_checkbox', 'vendor', 'so_doc', 'so_date', 'ge_doc', 'ge_date', 'item_code', 'item_name', 'attributes', 'order_qty', 'inv_order_qty', 'ge_qty', 'grn_qty', 'balance_qty', 'rate', 'total_amount'
            ])
            ->make(true);
    }


    # This for both bulk and single po
    protected function buildSoQuery($request)
    {
        $documentDate = $request->document_date ?? null;
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $storeId = $request->store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        if($request->type == 'create')
        {
            $decoded = urldecode(urldecode($request->selected_po_ids));
            $selected_so_ids = json_decode($decoded, true) ?? [];
        }
        else{
            $selected_po_ids = $request->selected_po_ids ?? [];
            $selected_so_ids = is_string($selected_po_ids)
                ? array_map('trim', explode(',', $selected_po_ids))
                : (is_array($selected_po_ids) ? $selected_po_ids : []);
        }

        $soItemIds = [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $orderTypes = [ConstantHelper::TYPE_SUBCONTRACTING, ConstantHelper::TYPE_JOB_ORDER, 'Sub Contracting'];

        $soItems = ErpSoJobWorkItem::select(
                'erp_so_job_work_items.*',
                'erp_sale_orders.id as so_id',
                'erp_sale_orders.vendor_id as vendor_id',
                'erp_sale_orders.book_id as book_id',
                'erp_sale_orders.gate_entry_required as gate_entry_required',
            )
            ->leftJoin('erp_sale_orders', 'erp_sale_orders.id', 'erp_so_job_work_items.sale_order_id')
            ->whereIn('erp_sale_orders.book_id', $applicableBookIds)
            ->whereIn('erp_sale_orders.order_type', $orderTypes)
            ->whereRaw('(ROUND(qty) > ROUND(grn_qty))')
            ->whereHas('item', function($item) use($itemSearch){
                $item->where('type', 'Goods');
                if ($itemSearch) {
                    $item->where(function ($query) use ($itemSearch) {
                        $query->where('erp_items.item_name', 'LIKE', "%{$itemSearch}%")
                            ->orWhere('erp_items.item_code', 'LIKE', "%{$itemSearch}%");
                    });
                }
            })
            ->with(['header', 'item', 'attributes', 'header.vendor'])
            ->whereHas('header', function ($so) use ($seriesId, $docNumber, $vendorId, $storeId) {
                $so->withDefaultGroupCompanyOrg();
                $so->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                if ($seriesId) {
                    $so->where('erp_sale_orders.book_id', $seriesId);
                }
                if ($docNumber) {
                    $so->where('erp_sale_orders.id', $docNumber);
                }
                if ($vendorId) {
                    $so->where('erp_sale_orders.vendor_id', $vendorId);
                }
                if ($storeId) {
                    $so->where('erp_sale_orders.store_id', $storeId);
                }
            });

        if ($itemId) {
            $soItems->where('item_id', $itemId);
        }

        if ($request->type == 'create' && count($selected_so_ids)) {
            $soItems->whereNotIn('erp_so_job_work_items.id', $selected_so_ids);
        } elseif ($request->type == 'edit' && count($selected_so_ids)) {
            $soItems->whereIn('erp_so_job_work_items.sale_order_id', $selected_so_ids);
        }

        $soItems = $soItems->get();
        $soItemIds = [];
        $finalSoItems = [];

        foreach ($soItems as $soItem) {
            if ($soItem->gate_entry_required == 'yes') {
                // Fetch Gate Entry Details
                $geItems = GateEntryDetail::where('sale_order_item_id', $soItem->id)
                    ->whereRaw('(accepted_qty > mrn_qty)')
                    ->with(['gateEntryHeader'])
                    ->get();

                foreach ($geItems as $geItem) {
                    if (in_array($geItem->id, $selected_so_ids)) {
                        continue;
                    }
                    $soItemIds[] = $geItem->id;
                    $geItem->balance_qty = $geItem->accepted_qty - $geItem->mrn_qty;
                    $geItem->so = $geItem->so_item->so;
                    $geItem->item = $geItem->so_item->item;
                    $geItem->attributes = $geItem->so_item->attributes;
                    $finalSoItems[] = $geItem;
                }
            } else {
                if (!in_array($soItem->id, $selected_so_ids)) {
                    $finalSoItems[] = $soItem;
                    $soItemIds[] = $soItem->id;
                }
            }
        }
        return $finalSoItems;
    }

    # Submit PI Item list
    public function processSoItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $view = '';
        $soItems = [];
        $vendor = null;
        $vendorId = '';
        $gateEntry = '';
        $subStoreCount = 0;
        $uniqueSoIds = [];
        $finalDiscounts = collect();
        $finalExpenses = collect();
        $requestIds = json_decode($request->ids, true) ?: [];
        $moduleTypes = json_decode($request->moduleTypes, true) ?: [];
        $type = "so";
        $tableRowCount = $request->tableRowCount ?: 0;

        // Ensure all module types are the same
        if (count(array_unique($moduleTypes)) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "Multiple different module types are not allowed."]);
        }
        // Determine module type
        $moduleType = $moduleTypes[0] ?? null;

        if ($moduleType === 'gate-entry') {
            $soItems = GateEntryDetail::with(
                [
                    'gateEntryHeader',
                    'gateEntryHeader.purchaseOrder',
                    'soItem',
                    'soItem.so',
                ]
            )
            ->whereIn('id', $requestIds)
            ->groupBy('sale_order_item_id')
            ->get();
            // $subStoreCount = $poItems->where('sub_store_id', '!=', null)->count();
            $soItemIds = $soItems->pluck('sale_order_item_id')->unique()->toArray();
            $gateEntryIds = $soItems->pluck('header_id')->unique()->toArray();
            $gateEntry = GateEntryHeader::whereIn('id', $gateEntryIds)->first();

            $uniqueGateEntryIds = GateEntryDetail::whereIn('id', $requestIds)
                ->distinct()
                ->pluck('header_id')
                ->toArray();
            if(count($uniqueGateEntryIds) > 1) {
                return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time work order create from one Gate Entry."]);
            }

            // Fetch the unique PO IDs linked to selected gate entries
            $uniqueSoIds = ErpSoJobWorkItem::whereIn('id', $soItemIds)
                ->distinct()
                ->pluck('sale_order_id')
                ->toArray();

                $view = 'procurement.material-receipt.partials.gate-entry-item-row';
        } else {
            $soItems = ErpSoJobWorkItem::whereIn('id', $requestIds)->get();
            $uniqueSoIds = $soItems->pluck('sale_order_id')->unique()->toArray();
            $view = 'procurement.material-receipt.partials.so-item-row';
        }

        if(count($uniqueSoIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time mrn create from one SO."]);
        }

        // Fetch purchase order and vendor details
        $purchaseOrder = ErpSaleOrder::whereIn('id', $uniqueSoIds)->first();
        $vendorId = ErpSaleOrder::whereIn('id', $uniqueSoIds)->pluck('vendor_id')->unique()->toArray();
        if (count($vendorId) > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "You cannot select multiple vendors for work order items at once."
            ]);
        }

        $vendor = Vendor::find($vendorId[0] ?? null);
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

        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        // Fetch discounts & expenses efficiently
        $discounts = collect();
        $expenses = collect();

        $pos = ErpSaleOrder::whereIn('id', $uniqueSoIds)->with(['discount_ted', 'expense_ted'])->get();

        foreach ($pos as $so) {
            foreach ($so->discount_ted as $headerDiscount) {
                if (!intval($headerDiscount->ted_perc)) {
                    $tedPerc = (floatval($headerDiscount->ted_amount) / floatval($headerDiscount->assessment_amount)) * 100;
                    $headerDiscount['ted_perc'] = $tedPerc;
                }
                $discounts->push($headerDiscount);
            }

            foreach ($so->expense_ted as $headerExpense) {
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

        // **Gate Entry Quantity Calculation**
        $totalGateEntryQty = 0;
        if ($moduleType === 'gate-entry') {
            $totalGateEntryQty = GateEntryDetail::with(
                [
                    'gateEntryHeader',
                    // 'gateEntryHeader.purchaseOrder',
                    'soItem',
                    'joItem.header',
                ]
            )
            ->whereIn('id', $requestIds)
            ->groupBy('sale_order_item_id')
            ->sum('accepted_qty'); // Sum of selected gate entry quantities
        }


        $html = view($view,
        [
            'soItems' => $soItems,
            'locations'=>$locations,
            'moduleType'=>$moduleType,
            'totalGateEntryQty' => $totalGateEntryQty,
            'type' => $type,
            'tableRowCount' => $tableRowCount
        ])
        ->render();

        return response()->json([
            'data' => [
                'pos' => $html,
                'vendor' => $vendor,
                'gateEntry' => $gateEntry,
                'moduleType' => $moduleType,
                'subStoreCount' => $subStoreCount,
                'saleOrder' => $purchaseOrder,
                'finalExpenses' => $finalExpenses,
                'finalDiscounts' => $finalDiscounts,
                'totalGateEntryQty' => $totalGateEntryQty
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
        $data = InventoryHelper::settlementOfInventoryAndStock($mrn->id, $detailIds, ConstantHelper::MRN_SERVICE_ALIAS, $mrn->document_status);
        return $data;
    }

    // Update Po Qty
    private static function updatePoQty($item, $poDetail, $inputQty, $type){
        $user = Helper::getAuthenticatedUser();
        $tolerenceInputQty = ($inputQty ?? 0.00) + ($poDetail->grn_qty ?? 0.00);
        $tolerenceBalanceQty = ($poDetail->qty - ($tolerenceInputQty ?? 0.00));
        if(($item->po_positive_tolerance && ($item->po_positive_tolerance > 0)) || ($item->po_negative_tolerance && ($item->po_negative_tolerance > 0))){
            $positiveTolerenceAmt = $item->po_positive_tolerance ? (($item->po_positive_tolerance/$poDetail->order_qty)*100) : 0;
            $negativeTolerenceAmt = $item->po_negative_tolerance ? (($item->po_negative_tolerance/$poDetail->order_qty)*100) : 0;
            if($tolerenceInputQty <= ($poDetail->order_qty + $positiveTolerenceAmt)){
                if(($tolerenceBalanceQty <= $negativeTolerenceAmt) && ($tolerenceBalanceQty >= 0)){
                    if($type == 'sale-order')
                    {
                        $poDetail->grn_qty += floatval($inputQty);
                        $poDetail->save();
                    }
                    $poDetail->grn_qty += floatval($inputQty);
                    $poDetail->short_close_qty += floatval($tolerenceBalanceQty);
                    $poDetail->save();
                }
                if(($tolerenceBalanceQty < 0) && (-($positiveTolerenceAmt) >= $tolerenceBalanceQty)){
                    $poDetail->grn_qty += floatval($inputQty);
                    $poDetail->save();
                }
            } else{
                // DB::rollBack();
                return response()->json([
                    'message' => 'Input Qty cn not be greater than balance qty.'
                ], 422);
                // $poDetail->grn_qty += floatval($inputQty);
                // $poDetail->save();
            }
        } else{
            if($tolerenceInputQty > $poDetail->order_qty){
                DB::rollBack();
                return response()->json([
                    'message' => 'Input Qty cn not be greater than order qty.'
                ], 422);
            } else{
                $poDetail->grn_qty += floatval($inputQty);
                $poDetail->save();
            }
        }

        return true;
    }

    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::financeVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, $request->type ?? 'get');
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
            $data = FinancialPostingHelper::financeVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, 'post');
            if ($data['status']) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch(\Exception $ex) {
            \DB::rollBack();
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
        \DB::beginTransaction();
        try {
            $mrn = MrnHeader::find($request->id);
            if (isset($mrn)) {
                $revoke = Helper::approveDocument($mrn->book_id, $mrn->id, $mrn->revision_number, '', [], 0, ConstantHelper::REVOKE, $mrn->total_amount, get_class($mrn));
                if ($revoke['message']) {
                    \DB::rollBack();
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
                throw new \ApiGenericException("No Document found");
            }
        } catch(\Exception $ex) {
            DB::rollBack();
            throw new \ApiGenericException($ex -> getMessage());
        }
    }

    public function itemsImport(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:30720',
            ]);
            if (!$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }

            $file = $request->file('file');
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(filename: $file);

            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file format is incorrect or corrupted. Please upload a valid Excel file.',
                ], 400);
            }

            TransactionUploadItem::where('created_by', $user->id)->delete();
            Excel::import(new TransactionItemImport($request->store_id, $request->type, $request->mrn_header_id), $file);

            $successfulItems =  TransactionUploadItem::where('status', 'Success')
                ->where('created_by', $user->id)
                ->get();
            $failedItems = TransactionUploadItem::where('status', 'Failed')
                ->where('created_by', $user->id)
                ->get();

            if (count($failedItems) > 0) {
                $message = 'Items import failed.';
                $status = 'failure';
            } else {
                $message = 'Items imported successfully.';
                $status = 'success';
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 5MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import items: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportSuccessfulItems()
    {
        $user = Helper::getAuthenticatedUser();
        $uploadItems = TransactionUploadItem::where('status','Success')
        ->where('created_by', $user->id)
        ->where('is_sync', 0)
        ->get();
        return Excel::download(new TransactionItemsExport($uploadItems), "successful-transaction-items.xlsx");
    }

    public function exportFailedItems()
    {
        $user = Helper::getAuthenticatedUser();
        $failedItems = TransactionUploadItem::where('created_by', $user->id)
        ->where('is_sync', 0)
        ->get();
        return Excel::download(new FailedTransactionItemsExport($failedItems), "failed-transaction-items.xlsx");
    }

    # Process Import Items
    public function processImportItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $uploadedItems = TransactionUploadItem::where('status','Success')
            ->where('is_sync', 0)
            ->where('created_by', $user->id)
            ->get();
        $uniqueId = TransactionUploadItem::where('status','Success')
            ->where('is_sync', 0)
            ->where('created_by', $user->id)
            ->first();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $view = 'procurement.material-receipt.partials.import-item-row';

        $html = view($view,
        [
            'locations'=>$locations,
            'uploadedItems'=>$uploadedItems,
        ])
        ->render();

        return response()->json([
            'data' => [
                'pos' => $html
            ],
            'status' => 200,
            'message' => "fetched!",
            'uniqueId' => $uniqueId,
        ]);
    }

    # Process Import Items
    public function updateImportItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $uploadedItems = TransactionUploadItem::where('status','Success')
            ->where('is_sync', 0)
            ->where('created_by', $user->id)
            ->update(['is_sync' => 1]);

        return response()->json([
            'status' => 200,
            'message' => "fetched!"
        ]);
    }

    // Mrn Report
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
        $purchaseOrderIds = MrnHeader::withDefaultGroupCompanyOrg()
                            ->distinct()
                            ->pluck('purchase_order_id');
        $purchaseOrders = PurchaseOrder::whereIn('id', $purchaseOrderIds)->get();
        $soIds = MrnDetail::whereHas('mrnHeader', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->distinct()
                ->pluck('so_id');

        $so = ErpSaleOrder::whereIn('id', $soIds)->get();
        $gateEntry = MrnHeader::withDefaultGroupCompanyOrg()
        ->distinct()
        ->whereNotNull('gate_entry_no')
        ->where('gate_entry_no', '!=', '')
        ->pluck('gate_entry_no');
        $lot_no = MrnHeader::withDefaultGroupCompanyOrg()
        ->distinct()
        ->whereNotNull('lot_number')
        ->where('lot_number', '!=', '')
        ->pluck('lot_number');
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;
        // $attributes = Attribute::get();
        return view('procurement.material-receipt.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'purchaseOrders', 'gateEntry', 'lot_no', 'statusCss'));
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
        $lotId = $request->query('lotNo');
        $vendorId = $request->query('vendor');
        $itemId = $request->query('item');
        $status = $request->query('status');
        $mCategoryId = $request->query('m_category');
        $mSubCategoryId = $request->query('m_subCategory');
        $mAttribute = $request->query('m_attribute');
        $mAttributeValue = $request->query('m_attributeValue');

        $query = MrnHeader::query()
        ->withDefaultGroupCompanyOrg();

        if ($poId) {
            $query->where('purchase_order_id', $poId);
        }
        if ($gateEntryId) {
            $query->where('gate_entry_no', 'like', '%' . $gateEntryId . '%');
        }
        if ($lotId) {
            $query->where('lot_number', 'like', '%' . $lotId . '%');
        }

        $query->with([
            'items' => function($query) use ($itemId, $soId, $mCategoryId, $mSubCategoryId, $mAttribute, $mAttributeValue) {
            $query->whereHas('item', function($q) use ($itemId, $soId, $mCategoryId, $mSubCategoryId, $mAttribute, $mAttributeValue) {
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
        'items.item', 'items.item.category', 'items.item.subCategory', 'vendor', 'items.so', 'po', 'items.erpStore', 'items.subStore'])
        ->withDefaultGroupCompanyOrg();

        // if ($mAttribute || $mAttributeValue) {
        //     $query->whereHas('items_attribute', function($subQuery) use ($mAttribute, $mAttributeValue) {
        //         // Filters for items_attribute
        //         $subQuery->whereHas('itemAttribute', function($q) use ($mAttribute, $mAttributeValue) {
        //             if ($mAttribute) {
        //                 $q->where('attribute_group_id', $mAttribute);
        //             }
        //             if ($mAttributeValue) {
        //                 $jsonValue = json_encode([$mAttributeValue]);
        //                 // Filter on JSON_CONTAINS

        //                 $q->whereRaw('JSON_CONTAINS(attribute_id, ?)', [$jsonValue]);
        //             }
        //         });
        //     });
        // }

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

        // DB::enableQueryLog();

        // return response()->json($po_reports);

        $po_reports = $query->get();
        if ($request->ajax()) {
            return DataTables::of($po_reports)->make(true);
        }
        return view('procurement.material-receipt.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'purchaseOrders', 'gateEntry', 'lot_no', 'statusCss'));
    }

    public function addScheduler(Request $request)
    {
        try{
            $user = Helper::getAuthenticatedUser();
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

            $fileName = 'material-receipt_'+ $user->id +'.xlsx';
            $filePath = storage_path('app/public/material-receipt/' . $fileName);
            $directoryPath = storage_path('app/public/material-receipt');
            if($formattedstartDate && $formattedendDate)
            {
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Material Receipt Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else{
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Material Receipt Report' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }

            $remainingSpaces = $blankSpaces - count($filters) + 1;
            $filterHeader = array_merge($filters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new MaterialReceiptExport($customHeader, $filterHeader, $headers, $data), \Maatwebsite\Excel\Excel::XLSX);

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
                $title = "Material Receipt Report Generated";
                $heading = "Material Receipt Report";
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
                                We hope this email finds you well. Please find your material receipt report attached below.
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

    public function materialReceiptReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $pathUrl = route('material-receipt.index');
        $orderType = ConstantHelper::MRN_SERVICE_ALIAS;  // Adjust based on actual constant for MRN service type
        $materialReceipts = MrnHeader::withDefaultGroupCompanyOrg()
            // ->where('document_type', $orderType)
            // ->bookViewAccess($pathUrl)
            ->withDraftListingLogic()
            ->orderByDesc('id');

        // Vendor Filter
        $materialReceipts = $materialReceipts->when($request->vendor, function ($vendorQuery) use ($request) {
            $vendorQuery->where('vendor_id', $request->vendor);
        });

        // PO No Filter
        $materialReceipts = $materialReceipts->when($request->po_no, function ($poQuery) use ($request) {
            $poQuery->where('purchase_order_id', $request->po_no);
        });

        // LOT Number Filter
        $materialReceipts = $materialReceipts->when($request->lot_number, function ($docQuery) use ($request) {
            $docQuery->where('lot_number', 'LIKE', '%' . $request->lot_number . '%');
        });

        // Gate Entry Filter
        $materialReceipts = $materialReceipts->when($request->gate_entry_no, function ($gateEntryQuery) use ($request) {
            $gateEntryQuery->where('gate_entry_no', 'LIKE', '%' . $request->gate_entry_no . '%');
        });

        // // Organization Filter
        // $materialReceipts = $materialReceipts->when($request->organization_id, function ($orgQuery) use ($request) {
        //     $orgQuery->where('organization_id', $request->organization_id);
        // });

        // Document Status Filter
        $materialReceipts = $materialReceipts->when($request->status, function ($docStatusQuery) use ($request) {
            $searchDocStatus = [];
            if ($request->status === ConstantHelper::DRAFT) {
                $searchDocStatus = [ConstantHelper::DRAFT];
            } elseif ($request->status === ConstantHelper::SUBMITTED) {
                $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
            } else {
                $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
            }
            $docStatusQuery->whereIn('document_status', $searchDocStatus);
        });

        // Date Filters
        $dateRange = $request->date_range ?? Carbon::now()->startOfMonth()->format('Y-m-d') . " to " . Carbon::now()->endOfMonth()->format('Y-m-d');
        $materialReceipts = $materialReceipts->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
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

        $materialReceipts->with([
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
            'items.erpStore',
            'items.subStore'
        ]);


        $materialReceipts = $materialReceipts->get();
        $processedMaterialReceipts = collect([]);

        foreach ($materialReceipts as $mrn) {
            foreach ($mrn->items as $mrnItem) {
                $reportRow = new stdClass();

                // Header Details
                $header = $mrnItem->header;
                $total_item_value = (($mrnItem?->rate ?? 0.00) * ($mrnItem?->accepted_qty ?? 0.00)) - ($mrnItem?->discount_amount ?? 0.00);
                $reportRow->id = $mrnItem->id;
                $reportRow->book_code = $header->book_code;
                $reportRow->document_number = $header->document_number;
                $reportRow->document_date = $header->document_date;
                $reportRow->po_no = !empty($header->po?->book_code) && !empty($header->po?->document_number)
                                    ? $header->po?->book_code . ' - ' . $header->po?->document_number
                                    : '';
                $reportRow->ge_no = $header->gate_entry_no;
                $reportRow->so_no = !empty($header->so?->book_code) && !empty($header->so?->document_number)
                                    ? $header->so?->book_code . ' - ' . $header->so?->document_number
                                    : '';
                $reportRow->lot_no = $header->lot_no;
                $reportRow->vendor_name = $header->vendor ?-> company_name;
                $reportRow->vendor_rating = null;
                $reportRow->category_name = $mrnItem->item ?->category ?-> name;
                $reportRow->sub_category_name = $mrnItem->item ?->category ?-> name;
                $reportRow->item_type = $mrnItem->item ?->type;
                $reportRow->sub_type = null;
                $reportRow->item_name = $mrnItem->item ?->item_name;
                $reportRow->item_code = $mrnItem->item ?->item_code;

                // Amount Details
                $reportRow->receipt_qty = number_format($mrnItem->order_qty, 2);
                $reportRow->accepted_qty = number_format($mrnItem->accepted_qty, 2);
                $reportRow->rejected_qty = number_format($mrnItem->rejected_qty, 2);
                $reportRow->pr_qty = number_format($mrnItem->pr_qty, 2);
                $reportRow->pr_rejected_qty = number_format($mrnItem->pr_rejected_qty, 2);
                $reportRow->purchase_bill_qty = number_format($mrnItem->purchase_bill_qty, 2);
                $reportRow->store_name = $mrnItem?->erpStore?->store_name;
                $reportRow->sub_store_name = $mrnItem?->subStore?->name;
                $reportRow->rate = number_format($mrnItem->rate);
                $reportRow->basic_value = number_format($mrnItem->basic_value, 2);
                $reportRow->item_discount = number_format($mrnItem->discount_amount, 2);
                $reportRow->header_discount = number_format($mrnItem->header_discount_amount, 2);
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
                $processedMaterialReceipts->push($reportRow);
            }
        }

        return DataTables::of($processedMaterialReceipts)
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

    # Check Warehouse Setup
    public function checkWarehouseSetup(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $whStructure = WhStructure::withDefaultGroupCompanyOrg()
                ->where('store_id', $request->store_id)
                ->where('sub_store_id', $request->sub_store_id)
                ->first();
        if (!$whStructure) {
            return response()->json([
                'status' => 204,
                "is_setup" => false,
                'message' => 'Please setup warehouse structure first.',
            ], 422);
        }
        $mapping = WhItemMapping::where('store_id', $request->store_id)
                ->where('sub_store_id', $request->sub_store_id)
                ->first();
        if (!$mapping) {
            return response()->json([
                'status' => 204,
                "is_setup" => false,
                'message' => 'Please setup item mapping first.',
            ], 422);
        }

        return response()->json([
            'status' => 200,
            "is_setup" => true,
            'message' => "fetched!"
        ]);
    }

    # Check Warehouse Item Uom Info
    public function warehouseItemUomInfo(Request $request)
    {
        $user = Helper::getAuthenticatedUser();

        $item = Item::find($request->item_id);
        if (!$item) {
            return response()->json([
                'status' => 204,
                "is_setup" => false,
                'message' => 'Item not found.',
            ], 422);
        }
        $inventoryUom = Unit::find($item->uom_id ?? null);
        $storageUom = Unit::find($item->storage_uom_id ?? null);
            $inventoryQty = ItemHelper::convertToBaseUom($item->id, $request->uom_id, $request->qty);
            if (!$inventoryQty) {
                return response()->json([
                    'status' => 204,
                    "is_setup" => false,
                    'message' => 'Inventory Qty not exist.',
                ], 422);
        }

        $data = [
            'item' => $item,
            'qty' => $request->qty,
            'inventory_qty' => $inventoryQty,
            'inventory_uom_name' => @$inventoryUom->name,
            'storage_uom_name' => @$storageUom->name
        ];

        return response()->json([
            'status' => 200,
            "data" => $data,
            'message' => "fetched!"
        ]);
    }

    # MRN Get Labels
    public function printLabels($id)
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (!$servicesBooks) {
            return response()->json([
                'status' => 204,
                "is_setup" => false,
                'message' => 'You do not have access to this service.',
            ], 422);
        }

        $user = Helper::getAuthenticatedUser();
        $mrnHeader = MrnHeader::withDefaultGroupCompanyOrg()
            ->where('id', $id)
            ->first();

        if (!$mrnHeader) {
            return response()->json([
                'status' => 204,
                "is_setup" => false,
                'message' => 'MRN not found.',
            ], 422);
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mrnHeader->document_status] ?? '';

        if (request()->ajax()) {
            $records = $mrnHeader->itemLocations()
                ->with([
                    'mrnHeader',
                    'mrnDetail',
                    'mrnDetail.item'
                ])
                ->latest();

            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->status];
                    $displayStatus = $row->status;
                    return "<div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <div class='dropdown' style='display:inline;'>
                            <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                <i data-feather='more-vertical'></i>
                            </button>
                            <div class='dropdown-menu dropdown-menu-end'>
                                <a class='dropdown-item' href='#'>
                                    <i data-feather='edit-3' class='me-50'></i>
                                    <span>Print</span>
                                </a>
                            </div>
                        </div>
                    </div>";
                })
                ->addColumn('inventory_uom', function ($row) {
                    return $row->mrnDetail ? strval($row->mrnDetail?->inventory_uom_code) : 'N/A';
                })
                ->editColumn('inventory_uom_quantity', function ($row) {
                    return number_format($row->inventory_uom_qty, 2) ?? 'N/A';
                })
                ->editColumn('packet_number', function ($row) {
                    return strval($row->packet_number) ?? 'N/A';
                })
                ->addColumn('bar_code', function ($row) {
                    $barCode = EInvoiceHelper::generateQRCodeBase64($row->packet_number);
                    return "<img class='qr-code' src='{$barCode}' alt='{$row->packet_number}' style='width: 60px; height: 60px;'>";
                })
                ->rawColumns(['bar_code', 'status'])
                ->make(true);
        }
        return view('procurement.material-receipt.print-labels', [
            'mrn' => $mrnHeader,
            'servicesBooks' => $servicesBooks,
            'docStatusClass' => $docStatusClass
        ]);
    }

    # MRN Print Labels
    public function printBarcodes($id)
    {
        $packets = MrnItemLocation::with([
            'mrnHeader',
            'mrnDetail'
        ])
        ->where('mrn_header_id', $id)
        ->get();

        $html = view('procurement.material-receipt.print-barcodes', compact('packets'))->render();

        return response()->json([
            'status' => 200,
            'html' => $html
        ]);
    }

    # checkRawMaterial
    private static function checkRawMaterial($mrn)
    {
        try {
            $errorMessage = '';
            $storeMrnJo = [];
            $joType = $mrn->jobOrder->job_order_type;
            $mrnData = MrnDetail::where('mrn_header_id', $mrn->id)->get();
            foreach ($mrnData as $detail) {
                $vendor = $mrn->vendor;
                $vendorLocation = VendorLocation::where('vendor_id', $vendor->id)->first();
                $subStore = $vendorLocation->store_id ?? null;

                if (!$subStore) {
                    $errorMessage = 'Sub store not found for Vendor.';
                    break;
                }

                $storeData = ErpSubStoreParent::where('sub_store_id', $subStore)->first();
                $storeId = $storeData->store_id ?? null;

                if (!$storeId) {
                    $errorMessage = 'Main store not found for sub store.';
                    break;
                }
                if ($joType === ConstantHelper::TYPE_SUBCONTRACTING) {
                    $joData = JoBomMapping::where('jo_product_id', $detail->job_order_item_id)
                        ->with(['joProduct'])
                        ->get();
                    foreach ($joData as $miMapping) {
                        $selectedAttr = collect($miMapping['attributes'])->pluck('attribute_value')->toArray();
                        $bomQty = (float) ($miMapping->bom_qty ?? 0);
                        $checkQty = $detail->inventory_uom_qty * $bomQty;

                        $availableStock = InventoryHelper::totalInventoryAndStock(
                            $miMapping->item_id,
                            $selectedAttr,
                            $miMapping->uom_id,
                            $storeId,
                            $subStore
                        );

                        $availStock = (float)$availableStock['confirmedStocks'];
                        $pendingStock = (float)$checkQty;
                        if ($availStock < $pendingStock) {
                            $errorMessage = 'Available stock for item ' .$detail->item_code. ' is less than required.';
                            break;
                        }
                        $storeMrnJo = self::storeMrnJoItem($mrn, $detail, $miMapping, $joType, $storeId, $subStore);
                        if($storeMrnJo['status'] == 'error') {
                            $errorMessage = $storeMrnJo['message'] ?? '';
                            break;
                        }
                    }

                } if($joType === ConstantHelper::TYPE_JOB_ORDER) {
                    $selectedAttr = array_column($detail->attributes->toArray(), 'attr_value');
                    $availableStock = InventoryHelper::totalInventoryAndStock(
                        $detail->item_id,
                        $selectedAttr,
                        $detail->uom_id,
                        $storeId,
                        $subStore
                    );
                    $availStock = (float)$availableStock['confirmedStocks'];
                    $pendingStock = (float)$detail->inventory_uom_qty;
                    if (($availStock < $pendingStock)) {
                        $errorMessage = 'Available stock for item ' .$detail->item_code. ' is less than required.';
                        break;
                    }
                    // Build dummy $miMapping to reuse store logic
                    $miMapping = (object)[
                        'jo_product_id' => $detail->job_order_item_id,
                        'item_id'       => $detail->item_id,
                        'uom_id'        => $detail->uom_id,
                        'qty'           => $detail->accepted_qty
                    ];

                    $storeMrnJo = self::storeMrnJoItem($mrn, $detail, $miMapping, $joType, $storeId, $subStore);
                    if($storeMrnJo['status'] == 'error') {
                        $errorMessage = $storeMrnJo['message'] ?? '';
                        break;
                    }
                }
            }

            return $errorMessage; // No error
        } catch (\Exception $e) {
            \Log::error('Error in settlementOfInventoryAndStock: ' . $e->getMessage());
            $errorMessage = 'Error in settlementOfInventoryAndStock: ' . $e->getMessage();
            return $errorMessage;  // Return error message
        }
    }

    private static function storeMrnJoItem($mrn, $detail, $miMapping, $joType, $storeId, $subStore, $selectedAttr = [])
    {
        $mrnJoItem = new MrnJoItem();
        $mrnJoItem->type = $joType;
        $mrnJoItem->mrn_header_id = $mrn->id;
        $mrnJoItem->mrn_detail_id = $detail->id;
        $mrnJoItem->jo_product_id = $miMapping->jo_product_id;
        $mrnJoItem->jo_item_id = $miMapping->item_id;
        $mrnJoItem->store_id = $storeId;
        $mrnJoItem->sub_store_id = $subStore;
        $mrnJoItem->consumed_qty = $miMapping->qty;
        $mrnJoItem->item_id = $miMapping->item_id;
        $mrnJoItem->item_code = $miMapping->item_code;
        $mrnJoItem->uom_id = $miMapping->uom_id;
        $mrnJoItem->attributes = json_encode($miMapping->attributes);

        $inventoryQty = ItemHelper::convertToBaseUom($miMapping->item_id, $miMapping->uom_id, $miMapping->qty);
        $mrnJoItem->inventory_uom_qty = $inventoryQty;
        $mrnJoItem->save();
        $response = InventoryHelper::settlementForMIForIssueFromMrn(
            $mrnJoItem,
            ConstantHelper::MRN_SERVICE_ALIAS,
            $mrn->document_status,
            'issue'
        );

        return $response;
    }

    // Get Selected Item Amount
    public function getSelectedItemAmount(Request $request)
    {
        try{
            $poItemIds = array_filter($request->po_item_ids ?? [], 'is_numeric');
            $poIds = array_filter($request->po_ids ?? [], 'is_numeric');
            $itemQtys = $request->itemQtys ?? [];
            $tedId = $request->ted_id;
            $edit = $request->edit;
            $refType = strtolower($request->reference_type);

            if (empty($poItemIds) || empty($poIds) || !$tedId || !$refType) {
                return response()->json(['status' => 422, 'message' => 'Invalid input.']);
            }

            // Determine TED and associated ID
            if ($refType === 'po') {
                if($edit){
                    $poTed = MrnExtraAmount::find($tedId);
                    if(!$poTed){
                        return response()->json(['status' => 422, 'message' => 'Ted not found.']);
                    }
                    $relatedId = $poTed->po_id;
                } else{
                    $poTed = PurchaseOrderTed::find($tedId);
                    if(!$poTed){
                        return response()->json(['status' => 422, 'message' => 'Ted not found.']);
                    }
                    $relatedId = $poTed->purchase_order_id;
                }

                $items = PoItem::whereIn('id', $poItemIds)
                    ->where('purchase_order_id', $relatedId)
                    ->get();
            } elseif ($refType === 'jo') {
                if($edit){
                    $poTed = MrnExtraAmount::find($tedId);
                    // dd($poTed);
                    if(!$poTed){
                        return response()->json(['status' => 422, 'message' => 'Ted not found.']);
                    }
                    $relatedId = $poTed->jo_id;
                } else{
                    $poTed = JobOrderTed::find($tedId);
                    if(!$poTed){
                        return response()->json(['status' => 422, 'message' => 'Ted not found.']);
                    }
                    $relatedId = $poTed->job_order_id;
                }
                $relatedId = $poTed->job_order_id ?? $poTed->jo_id;
                $items = JoProduct::whereIn('id', $poItemIds)
                    ->where('jo_id', $relatedId)
                    ->get();
            } else {
                return response()->json(['status' => 422, 'message' => 'Invalid reference type.']);
            }

            // Calculate value
            $poItemValue = $items->reduce(function ($carry, $item) use ($itemQtys) {
                $qty = isset($itemQtys[$item->id]) ? floatval($itemQtys[$item->id]) : floatval($item->order_qty);
                $rate = floatval($item->rate);
                return $carry + ($qty * $rate);
            }, 0);

            return response()->json([
                'status' => 200,
                'message' => 'Success',
                'data' => [
                    'poItemValue' => round($poItemValue, 2),
                ],
            ]);
        } catch(\Exception $e){
            dd($e);
        }
    }

    # Process Job Order Component
    private static function processJobOrderComponent($component, $item, $inputQty)
    {
        if (!empty($component['gate_entry_detail_id'])) {
            $ge = GateEntryDetail::find($component['gate_entry_detail_id']);
            $jo = JoProduct::find($component['jo_detail_id']);
            if (!$ge || !$jo) return self::notFoundResponse('Gate Entry or Job Order');
            
            if (($ge->accepted_qty - $ge->mrn_qty) < $inputQty) {
                \DB::rollBack();
                return self::exceedsQtyResponse();
            }

            $ge->mrn_qty += $inputQty;
            $ge->save();
            return self::updatePoQty($item, $jo, $inputQty, 'gate-entry');
        }

        if (!empty($component['vendor_asn_dtl_id'])) {
            $asn = VendorAsnItem::find($component['vendor_asn_dtl_id']);
            $jo = JoProduct::find($asn?->jo_prod_id);
            if (!$asn || !$jo) return self::notFoundResponse('ASN or Job Order');

            if (($asn->order_qty - $asn->grn_qty) < $inputQty) {
                DB::rollBack();
                return self::exceedsQtyResponse();
            }

            $asn->grn_qty += $inputQty;
            $asn->save();
            return self::updatePoQty($item, $jo, $inputQty, 'supplier-invoice');
        }

        $jo = JoProduct::find($component['jo_detail_id']);
        return $jo ? self::updatePoQty($item, $jo, $inputQty, 'job-order') : self::notFoundResponse('Job Order');
    }

    # Process Sale Order Component
    private static function processSaleOrderComponent($component, $item, $inputQty)
    {
        if (!empty($component['gate_entry_detail_id'])) {
            $ge = GateEntryDetail::find($component['gate_entry_detail_id']);
            $so = ErpSoJobWorkItem::find($component['po_detail_id']);
            if (!$ge || !$so) return self::notFoundResponse('Gate Entry or Sale Order');

            if (($ge->accepted_qty - $ge->mrn_qty) < $inputQty) {
                DB::rollBack();
                return self::exceedsQtyResponse();
            }

            $ge->mrn_qty += $inputQty;
            $ge->save();
            return self::updatePoQty($item, $so, $inputQty, 'sale-order');
        }

        $so = ErpSoJobWorkItem::find($component['po_detail_id']);
        return $so ? self::updatePoQty($item, $so, $inputQty, 'sale-order') : self::notFoundResponse('Sale Order');
    }

    # Process Purchase Order Component
    private static function processPurchaseOrderComponent($component, $item, $inputQty)
    {
        if (!empty($component['gate_entry_detail_id'])) {
            $ge = GateEntryDetail::find($component['gate_entry_detail_id']);
            $po = PoItem::find($component['po_detail_id']);
            if (!$ge || !$po) return self::notFoundResponse('Gate Entry or PO');

            if (($ge->accepted_qty - $ge->mrn_qty) < $inputQty) {
                DB::rollBack();
                return self::exceedsQtyResponse();
            }

            $ge->mrn_qty += $inputQty;
            $ge->save();
            return self::updatePoQty($item, $po, $inputQty, 'gate-entry');
        }

        if (!empty($component['supplier_inv_detail_id'])) {
            $inv = PoItem::find($component['supplier_inv_detail_id']);
            $po = PoItem::find($component['po_detail_id']);
            if (!$inv || !$po) return self::notFoundResponse('Supplier Invoice or PO');

            if (($inv->order_qty - $inv->grn_qty) < $inputQty) {
                DB::rollBack();
                return self::exceedsQtyResponse();
            }

            $inv->grn_qty += $inputQty;
            $inv->save();
            return self::updatePoQty($item, $po, $inputQty, 'supplier-invoice');
        }

        $po = PoItem::find($component['po_detail_id']);
        return $po ? self::updatePoQty($item, $po, $inputQty, 'purchase-order') : self::notFoundResponse('PO Item');
    }

    // Update Purchase Order Quantity
    private static function updatePoQty($item, $poDetail, $inputQty, $type)
    {
        $orderQty = floatval($poDetail->order_qty);
        $grnQty = floatval($poDetail->grn_qty ?? 0);
        $totalQty = $grnQty + $inputQty;

        $posTol = floatval($item->po_positive_tolerance);
        $negTol = floatval($item->po_negative_tolerance);

        $maxAllowed = $orderQty + $posTol;
        $minAllowed = max(0, $orderQty - $negTol);
        $remaining = $orderQty - $totalQty;

        if ($posTol > 0 || $negTol > 0) {
            if ($totalQty > $maxAllowed) {
                return response()->json(['message' => 'Input Qty cannot exceed positive tolerance.'], 422);
            }

            if ($remaining <= $negTol && $remaining >= 0) {
                $poDetail->short_close_qty += $remaining;
            }
        } elseif ($totalQty > $orderQty) {
            return response()->json(['message' => 'Input Qty cannot exceed order qty.'], 422);
        }

        $poDetail->grn_qty += $inputQty;
        $poDetail->save();

        return true;
    }

    # Helper Functions for Responses
    private static function notFoundResponse($label)
    {
        \DB::rollBack();
        return response()->json(['message' => "{$label} not found."], 422);
    }

    private static function exceedsQtyResponse()
    {
        \DB::rollBack();
        return response()->json(['message' => 'Input qty cannot be greater than balance qty.'], 422);
    }


}
