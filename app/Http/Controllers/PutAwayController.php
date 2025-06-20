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
use App\Http\Requests\PutAwayRequest;
use App\Http\Requests\EditPutAwayRequest;

use App\Models\PutAwayHeader;
use App\Models\PutAwayDetail;
use App\Models\PutAwayAttribute;
use App\Models\PutAwayItemLocation;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\MrnItemLocation;
use App\Models\MrnExtraAmount;

use App\Models\ErpItem;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Employee;
use App\Models\ErpVendor;
use App\Models\WhStructure;
use App\Models\AlternateUOM;
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
use App\Models\WhDetail;
use App\Models\VendorBook;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\Organization;
use App\Models\ErpSaleOrder;
use App\Models\PurchaseOrder;
use App\Models\NumberPattern;
use App\Models\AttributeGroup;

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
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;
use App\Models\User;

class PutAwayController extends Controller
{
    protected $putawayService;

    protected $organization_id;
    protected $group_id;

    public function __construct(MrnService $putawayService)
    {
        $this->mrnService = $putawayService;
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
        $parentUrl = 'material-receipts';
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
                    'currency'
                ]
            )
            ->withDefaultGroupCompanyOrg()
            ->withDraftListingLogic()
            ->bookViewAccess($parentUrl)
            ->where('company_id', $organization->company_id)
            ->where('is_warehouse_required', 1)
            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED])
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $route = route('put-away.edit', $row->id);
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
                ->editColumn('location', function ($row) {
                    return strval($row->erpStore?->store_name) ?? 'N/A';
                })
                ->editColumn('store', function ($row) {
                    return strval($row->erpSubStore?->name) ?? 'N/A';
                })
                ->editColumn('currency', function ($row) {
                    return strval($row->currency->short_name) ?? 'N/A';
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
                ->rawColumns(['document_status'])
                ->make(true);
        }
        return view('procurement.put-away.index', [
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
        return view('procurement.put-away.create', [
            'books'=>$books,
            'vendors' => $vendors,
            'locations'=>$locations,
            'servicesBooks'=>$servicesBooks,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    # MRN store
    public function store(PutAwayRequest $request)
    {
        $user = Helper::getAuthenticatedUser();

        DB::beginTransaction();
        try {
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

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request -> currency_id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }

            $putaway = new PutAwayHeader();
            $putaway->fill($request->all());
            $putaway->store_id = $request->header_store_id;
            $putaway->sub_store_id = $request->sub_store_id;
            $putaway->organization_id = $organization->id;
            $putaway->group_id = $organization->group_id;
            $putaway->book_code = $request->book_code;
            $putaway->series_id = $request->book_id;
            $putaway->book_id = $request->book_id;
            $putaway->book_code = $request->book_code ?? null;
            $putaway->vendor_code = $request->vendor_code;
            $putaway->company_id = $organization->company_id;
            $putaway->gate_entry_date = $request->gate_entry_date ? date('Y-m-d', strtotime($request->gate_entry_date)) : '';
            $putaway->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $putaway->billing_to = $request->billing_id;
            $putaway->ship_to = $request->shipping_id;
            $putaway->billing_address = $request->billing_address;
            $putaway->shipping_address = $request->shipping_address;
            $putaway->revision_number = 0;
            $document_number = $request->document_number ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_number;
            $regeneratedDocExist = PutAwayHeader::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
            //Again check regenerated doc no
            if (isset($regeneratedDocExist)) {
                return response()->json([
                    'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                    'error' => "",
                ], 422);
            }

            $putaway->doc_number_type = $numberPatternData['type'];
            $putaway->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $putaway->doc_prefix = $numberPatternData['prefix'];
            $putaway->doc_suffix = $numberPatternData['suffix'];
            $putaway->doc_no = $numberPatternData['doc_no'];

            $putaway->document_number = $document_number;
            $putaway->document_date = $request->document_date;
            $putaway->final_remark = $request->remarks ?? null;
            $putaway->cost_center_id = $request->cost_center_id ?? '';
            $putaway->save();

            $vendorBillingAddress = $putaway->billingAddress ?? null;
            $vendorShippingAddress = $putaway->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $putaway->bill_address_details()->firstOrNew([
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
                $shippingAddress = $putaway->ship_address_details()->firstOrNew([
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
            if($putaway?->erpStore)
            {
                $storeAddress  = $putaway?->erpStore->address;
                $storeLocation = $putaway->store_address()->firstOrNew();
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

            if (isset($request->all()['components'])) {
                $putawayItemArr = [];
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $mrnDetail = MrnDetail::with('mrnHeader')->find($component['mrn_detail_id']);
                    $putaway_detail_id = null;
                    $so_id = null;
                    if (isset($component['mrn_detail_id']) && $component['mrn_detail_id']) {
                        $putawayDetail = MrnDetail::find($component['mrn_detail_id']);
                        $mrn_detail_id = $putawayDetail->id ?? null;
                        $mrnHeaderId = $component['mrn_header_id'];
                        if ($putawayDetail) {
                            // $putawayDetail->pr_qty += floatval($component['accepted_qty']);
                            // $putawayDetail->save();
                            // $so_id = $putawayDetail->so_id;
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

                    $uom = Unit::find($component['uom_id'] ?? null);
                    $putawayItemArr[] = [
                        'header_id' => $putaway->id,
                        'mrn_detail_id' => $mrnDetail->id,
                        'item_id' => $component['item_id'] ?? null,
                        'item_code' => $component['item_code'] ?? null,
                        'hsn_id' => $component['hsn_id'] ?? null,
                        'hsn_code' => $component['hsn_code'] ?? null,
                        'uom_id' =>  $component['uom_id'] ?? null,
                        'uom_code' => $uom->name ?? null,
                        'store_id' => $putaway->store_id ?? null,
                        'store_code' => $putaway?->erpStore?->store_code ?? null,
                        'sub_store_id' => $putaway->sub_store_id ?? null,
                        'order_qty' => floatval($component['order_qty']) ?? 0.00,
                        'accepted_qty' => floatval($component['accepted_qty']) ?? 0.00,
                        'inventory_uom_id' => $inventory_uom_id ?? null,
                        'inventory_uom_code' => $inventory_uom_code ?? null,
                        'inventory_uom_qty' => $inventory_uom_qty ?? 0.00,
                        'remark' => $component['remark'] ?? null,
                    ];
                }

                foreach($putawayItemArr as $_key => $putawayItem) {
                    $putawayDetail = new PutAwayDetail();

                    $putawayDetail->header_id = $putawayItem['header_id'];
                    $putawayDetail->mrn_detail_id = $putawayItem['mrn_detail_id'];
                    $putawayDetail->item_id = $putawayItem['item_id'];
                    $putawayDetail->item_code = $putawayItem['item_code'];
                    $putawayDetail->hsn_id = $putawayItem['hsn_id'];
                    $putawayDetail->hsn_code = $putawayItem['hsn_code'];
                    $putawayDetail->uom_id = $putawayItem['uom_id'];
                    $putawayDetail->uom_code = $putawayItem['uom_code'];
                    $putawayDetail->store_id = $putawayItem['store_id'];
                    $putawayDetail->store_code = $putawayItem['store_code'];
                    $putawayDetail->sub_store_id = $putawayItem['sub_store_id'];
                    $putawayDetail->receipt_qty = $putawayItem['order_qty'];
                    $putawayDetail->accepted_qty = $putawayItem['accepted_qty'];
                    $putawayDetail->inventory_uom_id = $putawayItem['inventory_uom_id'];
                    $putawayDetail->inventory_uom_code = $putawayItem['inventory_uom_code'];
                    $putawayDetail->inventory_uom_qty = $putawayItem['inventory_uom_qty'];
                    $putawayDetail->remark = $putawayItem['remark'];
                    $putawayDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach($putawayDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $putawayAttr = new PutAwayAttribute();
                            $putawayAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $putawayAttr->header_id = $putaway->id;
                            $putawayAttr->detail_id = $putawayDetail->id;
                            $putawayAttr->item_attribute_id = $itemAttribute->id;
                            $putawayAttr->item_code = $component['item_code'] ?? null;
                            $putawayAttr->attr_name = $itemAttribute->attribute_group_id;
                            $putawayAttr->attr_value = $putawayAttrName ?? null;
                            $putawayAttr->save();
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
                                $storagePoint = new PutAwayItemLocation();
                                $storagePoint->header_id = $putaway->id;
                                $storagePoint->detail_id = $putawayDetail->id;
                                $storagePoint->store_id = $putawayDetail->store_id;
                                $storagePoint->sub_store_id = $putawayDetail->sub_store_id;
                                $storagePoint->item_id = $val['item_location_id'] ?? null;
                                $storagePoint->wh_detail_id = $val['wh_detail_id'] ?? null;
                                $storagePoint->quantity = $val['quantity'] ?? 0.00;
                                $storagePoint->inventory_uom_qty = $val['quantity'] ?? 0.00;
                                $storagePoint->status = 'draft';
                                $storagePoint->save();

                                $packetNumber = '';
                                $storageNumber = '';
                                $whDetail = WhDetail::find($storagePoint->wh_detail_id);
                                // ✅ Generate storage number if not present
                                if($whDetail->storage_number){
                                    $storageNumber = $whDetail->storage_number;
                                } else{
                                    $randomNumber = strtoupper(Str::random(rand(6, 8)));
                                    $storageNumber = strtoupper(str_replace(' ', '-', $whDetail?->name)) .'-'. $randomNumber;
                                }

                                $storagePoint->storage_number = $storageNumber;
                                $storagePoint->save();

                                $mrnItemLocation = MrnItemLocation::find(@$storagePoint->item_id);
                                if($mrnItemLocation){
                                    $mrnItemLocation->storage_number = $storageNumber;
                                    $mrnItemLocation->wh_detail_id = $storagePoint->wh_detail_id;
                                    $mrnItemLocation->save();
                                } else{
                                    $mrnItemLocation = new MrnItemLocation();
                                    $mrnItemLocation->mrn_header_id = $putaway->id;
                                    $mrnItemLocation->mrn_detail_id = $putawayDetail->id;
                                    $mrnItemLocation->store_id = $putawayDetail->store_id;
                                    $mrnItemLocation->sub_store_id = $putawayDetail->sub_store_id;
                                    $mrnItemLocation->item_id = $putawayDetail->item_id;
                                    $mrnItemLocation->wh_detail_id = $val['wh_detail_id'] ?? null;
                                    $mrnItemLocation->quantity = $val['quantity'] ?? 0.00;
                                    $mrnItemLocation->inventory_uom_qty = $val['quantity'] ?? 0.00;
                                    $mrnItemLocation->status = 'draft';
                                    $mrnItemLocation->save();

                                    // ✅ Generate packet number if not present
                                    $packetNumber = $mrnDetail?->mrnHeader?->book_code . '-' . $mrnDetail?->mrnHeader?->document_number . '-' . $mrnDetail->item_code . '-' . $mrnDetail->id . '-' . ($mrnItemLocation->id ?? $i + 1);

                                    $mrnItemLocation->packet_number = $val['packet_number'] ?? $packetNumber;
                                    $mrnItemLocation->save();
                                }

                                // $storagePoint->packet_number = $val['packet_number'] ?? strtoupper(Str::random(rand(8, 10)));
                                $storagePoint->packet_number = $val['packet_number'] ?? $packetNumber;
                                $storagePoint->save();
                            }
                        } else {
                            \Log::warning("Invalid JSON for storage_points_data: " . print_r($component['storage_packets'], true));
                        }
                    }
                }

                /*Update po header id in main header Putaway*/
                $putaway->mrn_header_id = $mrnHeaderId ?? null;
                $putaway->save();

            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please add atleast one row in component table.',
                    'error' => "",
                ], 422);
            }

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $putaway->book_id;
                $docId = $putaway->id;
                $remarks = $putaway->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $putaway->approval_level ?? 1;
                $revisionNumber = $putaway->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($putaway);
                $totalValue = 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);

            }

            $putaway = PutAwayHeader::find($putaway->id);
            if ($request->document_status == 'submitted') {
                $putaway->document_status = $approveDocument['approvalStatus'] ?? $putaway->document_status;
            } else {
                $putaway->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            if(($putaway->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) || ($putaway->document_status == ConstantHelper::APPROVED) || ($putaway->document_status == ConstantHelper::POSTED)) {
                $updateStockLedger = self::maintainStockLedger($putaway->mrn);
            }

            $redirectUrl = '';
            if(($putaway->document_status == ConstantHelper::APPROVED) || ($putaway->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $putaway->id . '/pdf');
            }

            TransactionUploadItem::where('created_by', $user->id)->forceDelete();

            $status = DynamicFieldHelper::saveDynamicFields(ErpMrnDynamicField::class, $putaway -> id, $request -> dynamic_field ?? []);
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
                'data' => $putaway,
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

        $putaway = PutAwayHeader::with([
            'vendor',
            'currency',
            'items',
            'book'
        ])
        ->findOrFail($id);

        $totalItemValue = $putaway->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($putaway->series_id,$putaway->document_status , $putaway->id, $putaway->total_amount, $putaway->approval_level, $putaway->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory($putaway->series_id, $putaway->id, $putaway->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$putaway->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();

        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();

        return view('procurement.put-away.view',
        [
            'mrn' => $putaway,
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
        ])
        ->findOrFail($id);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mrn->document_status] ?? '';
        $view = 'procurement.put-away.edit';
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
        return view($view, [
            'mrn' => $mrn,
            'books'=>$books,
            'locations'=>$locations,
            'erpStores' => $erpStores,
            'orgAddress'=> $orgAddress,
            'subStoreCount' => $subStoreCount,
            'servicesBooks' => $servicesBooks,
            'docStatusClass' => $docStatusClass,
            'deliveryAddress'=> $deliveryAddress,
            'services' => $servicesBooks['services'],
        ]);
    }

    # Bom Update
    public function update(Request $request, $id)
    {
        $putaway = MrnHeader::find($id);
        $user = Helper::getAuthenticatedUser();
        
        DB::beginTransaction();
        try {
            // $keys = ['deletedItemLocationIds'];
            // $deletedData = [];

            // if (count($deletedData['deletedItemLocationIds'])) {
            //     MrnItemLocation::whereIn('id',$deletedData['deletedItemLocationIds'])->delete();
            // }

            # MRN Header save
            if (isset($request->all()['components'])) {
                $mrnItemArr = [];
                foreach($request->all()['components'] as $_key => $component) {
                    # PutAway Detail Save
                    $putawayDetail = MrnDetail::find($component['putaway_detail_id'] ?? null);

                    #Save item packets
                    $inventoryUomQuantity = 0.00;
                    $putawayQty = 0.00;
                    if (!empty($component['storage_packets'])) {
                        $storagePoints = is_string($component['storage_packets'])
                            ? json_decode($component['storage_packets'], true)
                            : $component['storage_packets'];

                        if (is_array($storagePoints)) {
                            foreach ($storagePoints as $i => $val) {
                                $storagePoint = PutAwayItemLocation::find(@$val['id']) ?? new PutAwayItemLocation;

                                $storagePoint->header_id = $putaway->id;
                                $storagePoint->detail_id = $putawayDetail->id;
                                $storagePoint->store_id = $putawayDetail->store_id;
                                $storagePoint->sub_store_id = $putawayDetail->sub_store_id;
                                $storagePoint->item_id = $val['item_location_id'] ?? null;
                                $storagePoint->wh_detail_id = $val['wh_detail_id'] ?? null;
                                $storagePoint->quantity = $val['quantity'] ?? 0.00;
                                $storagePoint->inventory_uom_qty = $val['quantity'] ?? 0.00;
                                $storagePoint->status = $putaway->document_status;
                                $storagePoint->save();

                                $packetNumber = '';
                                $storageNumber = '';
                                $whDetail = WhDetail::find($storagePoint->wh_detail_id);
                                // ✅ Generate storage number if not present
                                if($whDetail->storage_number){
                                    $storageNumber = $whDetail->storage_number;
                                } else{
                                    $randomNumber = strtoupper(Str::random(rand(6, 8)));
                                    $storageNumber = strtoupper(str_replace(' ', '-', $whDetail?->name)) .'-'. $randomNumber;
                                }

                                $storagePoint->storage_number = $storageNumber;
                                $storagePoint->save();

                                $mrnItemLocation = MrnItemLocation::find(@$storagePoint->item_id);
                                if($mrnItemLocation){
                                    $mrnItemLocation->storage_number = $storageNumber;
                                    $mrnItemLocation->wh_detail_id = $storagePoint->wh_detail_id;
                                    $mrnItemLocation->status = $putaway?->mrn?->document_status;
                                    $mrnItemLocation->save();
                                } else{
                                    $mrnItemLocation = new MrnItemLocation();
                                    $mrnItemLocation->mrn_header_id = $putaway->id;
                                    $mrnItemLocation->mrn_detail_id = $putawayDetail->id;
                                    $mrnItemLocation->store_id = $putawayDetail->store_id;
                                    $mrnItemLocation->sub_store_id = $putawayDetail->sub_store_id;
                                    $mrnItemLocation->item_id = $putawayDetail->item_id;
                                    $mrnItemLocation->wh_detail_id = $val['wh_detail_id'] ?? null;
                                    $mrnItemLocation->quantity = $val['quantity'] ?? 0.00;
                                    $mrnItemLocation->inventory_uom_qty = $val['quantity'] ?? 0.00;
                                    $mrnItemLocation->status = $putaway?->mrn?->document_status;
                                    $mrnItemLocation->save();

                                    // ✅ Generate packet number if not present
                                    $packetNumber = $putawayDetail?->mrnHeader?->book_code . '-' . $putawayDetail?->mrnHeader?->document_number . '-' . $putawayDetail->item_code . '-' . $putawayDetail->id . '-' . ($mrnItemLocation->id ?? $i + 1);

                                    $mrnItemLocation->packet_number = $val['packet_number'] ?? $packetNumber;
                                    $mrnItemLocation->save();
                                }

                                // $storagePoint->packet_number = $val['packet_number'] ?? strtoupper(Str::random(rand(8, 10)));
                                $storagePoint->packet_number = $val['packet_number'] ?? $packetNumber;
                                $storagePoint->save();

                                $putawayQty += $val['quantity'];
                            }
                        } else {
                            \Log::warning("Invalid JSON for storage_points_data: " . print_r($component['storage_packets'], true));
                        }
                    }
                    // dd($putawayQty);
                    $actualPutawayQty =  ItemHelper::convertToAltUom($putawayDetail->item_id, $putawayDetail->uom_id, $putawayQty);
                    $putawayDetail->putaway_qty += $actualPutawayQty;
                    $putawayDetail->save();
                }
            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            if(($putaway->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) || ($putaway->document_status == ConstantHelper::APPROVED) || ($putaway->document_status == ConstantHelper::POSTED)) {
                $updateStockLedger = self::maintainStockLedger($putaway);
            }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $putaway,
                'redirect_url' => ''
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
        $html = view('procurement.put-away.partials.item-row',compact('rowCount', 'locations'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $detailItemId = $request->mrn_detail_id ?? null;
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

        $html = view('procurement.put-away.partials.comp-attribute',compact('item','rowCount','selectedAttr','itemAttributes'))->render();
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
        $html = view('procurement.put-away.partials.add-disc-row',compact('tblRowCount','rowCount','disName','disAmount','disPerc'))->render();
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
            $html = view('procurement.put-away.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
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
            $html = view('procurement.put-away.partials.edit-address-modal',compact('addresses','selectedAddress'))->render();
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
        $selectedAttr = json_decode($request->selectedAttr, 200) ?? [];
        $itemId = $request->item_id;
        $item = Item::find($request->item_id ?? null);
        $putawayDetail = MrnDetail::find($request->mrn_detail_id ?? null);
        $poItem = PoItem::with('po')->find($putawayDetail->purchase_order_item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = intval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';
        $storeId = $request->store_id ?? null;
        $subStoreId = $request->sub_store_id ?? null;
        if ($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = @$alUom->conversion_to_inventory * $qty;
        }
        $remark = $request->remark ?? null;
        $mrn = MrnHeader::find($request->mrn_header_id);
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $totalStockData = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttr,  $uomId, $storeId, $subStoreId);
        $storagePoints = StoragePointHelper::getStoragePoints($itemId, $qty, $storeId, $subStoreId);
        $html = view(
            'procurement.put-away.partials.comp-item-detail',
            compact(
                'item',
                'mrn',
                'qty',
                'remark',
                'poItem',
                'uomName',
                'selectedAttr',
                'storagePoints',
                'specifications',
                'totalStockData',
            )
        )
        ->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'storagePoints' => $storagePoints, 'message' => 'fetched.']);
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
        $putaway = PutAwayHeader::with(['vendor', 'currency', 'items', 'book', 'expenses'])
            ->findOrFail($id);


        $shippingAddress = $putaway->shippingAddress;
        $billingAddress = $putaway->billingAddress;

        $totalItemValue = $putaway->total_item_amount ?? 0.00;
        $totalDiscount = $putaway->total_discount ?? 0.00;
        $totalTaxes = $putaway->total_taxes ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $putaway->expense_amount ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($putaway->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$putaway->document_status] ?? '';
        $taxes = MrnExtraAmount::where('mrn_header_id', $putaway->id)
            ->where('ted_type', 'Tax')
            ->select('ted_type','ted_id','ted_name', 'ted_percentage', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assesment_amount) as total_assesment_amount'))
            ->groupBy('ted_name', 'ted_percentage')
            ->get();
        $sellerShippingAddress = $putaway->latestShippingAddress();
        $sellerBillingAddress = $putaway->latestBillingAddress();
        $buyerAddress = $putaway?->erpStore?->address;

        $pdf = PDF::loadView(
            'pdf.mrn',
            [
                'mrn' => $putaway,
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
            $putawayHeader = PutAwayHeader::find($id);
            if(!$putawayHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $putawayHeaderData = $putawayHeader->toArray();
            unset($putawayHeaderData['id']); // You might want to remove the primary key, 'id'
            $putawayHeaderData['mrn_header_id'] = $putawayHeader->id;
            $headerHistory = PutAwayHeaderHistory::create($putawayHeaderData);
            $headerHistoryId = $headerHistory->id;


            $vendorBillingAddress = $putawayHeader->billingAddress ?? null;
            $vendorShippingAddress = $putawayHeader->shippingAddress ?? null;

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
            $putawayDetails = MrnDetail::where('mrn_header_id', $putawayHeader->id)->get();
            if(!empty($putawayDetails)){
                foreach($putawayDetails as $key => $detail){
                    $putawayDetailData = $detail->toArray();
                    unset($putawayDetailData['id']); // You might want to remove the primary key, 'id'
                    $putawayDetailData['mrn_detail_id'] = $detail->id;
                    $putawayDetailData['mrn_header_history_id'] = $headerHistoryId;
                    $detailHistory = MrnDetailHistory::create($putawayDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $putawayAttributes = MrnAttribute::where('mrn_header_id', $putawayHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->get();

                    if(!empty($putawayAttributes)){
                        foreach($putawayAttributes as $key1 => $attribute){
                            $putawayAttributeData = $attribute->toArray();
                            unset($putawayAttributeData['id']); // You might want to remove the primary key, 'id'
                            $putawayAttributeData['mrn_attribute_id'] = $attribute->id;
                            $putawayAttributeData['mrn_header_history_id'] = $headerHistoryId;
                            $putawayAttributeData['mrn_detail_history_id'] = $detailHistoryId;
                            $attributeHistory = MrnAttributeHistory::create($putawayAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Item Locations History
                    $itemLocations = MrnItemLocation::where('mrn_header_id', $putawayHeader->id)
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
                    $itemExtraAmounts = MrnExtraAmount::where('mrn_header_id', $putawayHeader->id)
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
            $putawayExtraAmounts = MrnExtraAmount::where('mrn_header_id', $putawayHeader->id)
                ->where('ted_level', '=', 'H')
                ->get();

            if(!empty($putawayExtraAmounts)){
                foreach($putawayExtraAmounts as $key4 => $extraAmount){
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
            $putawayHeader->revision_number += 1;
            // $putawayHeader->status = "draft";
            // $putawayHeader->document_status = "draft";
            // $putawayHeader->save();

            /*Create document submit log*/
            if ($putawayHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $putawayHeader->series_id;
                $docId = $putawayHeader->id;
                $remarks = $putawayHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $putawayHeader->approval_level ?? 1;
                $revisionNumber = $putawayHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                $putawayHeader->document_status = $approveDocument['approvalStatus'];
            }
            $putawayHeader->save();

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $putawayHeader,
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
        $incomingQty = '';
        $item = Item::find($request->item_id);
        if(!$item){
            return response() -> json([
                'data' => array(
                    'error_message' => 'Item not found.'
                )
            ]);
        }
        if(isset($request->mrnDetailId) && $request->mrnDetailId){
            $putawayDetail = MrnDetail::find($request->mrnDetailId);
            if(!$putawayDetail){
                return response() -> json([
                    'data' => array(
                        'error_message' => 'Mrn detail not found.'
                    )
                ]);
            }
            if(($putawayDetail->purchase_bill_qty) && ($putawayDetail->purchase_bill_qty > $request->qty)){
                return response() -> json([
                    'data' => array(
                        'error_message' => "Accepted qty can not be less than purchase bill quantity(which is : ".$putawayDetail->purchase_bill_qty.") as it has been already used there for this this item."
                    )
                ]);
            }
            $poDetail = PoItem::find($request->poDetailId);
            if($poDetail){
                $availableQty = 0.00;
                if($poDetail->order_qty < $request->qty){
                    return response() -> json([
                        'data' => array(
                            'error_message' => "Accepted qty can not be greater than po quantity."
                        )
                    ]);
                }
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
            $inputQty = ($request->qty ?? 0);
            $balanceQty = 0;
            $poTelerenaceCheck = 0;
            $tolerenceInputQty = 0;
            $tolerenceBalanceQty = 0;
            $poDetail = PoItem::find($request->poDetailId);
            if($request->geDetailId){
                $gateEntryDetail = GateEntryDetail::find($request->geDetailId);
                $balanceQty = ($gateEntryDetail->accepted_qty - ($gateEntryDetail->mrn_qty ?? 0.00));
                if($balanceQty < $inputQty){
                    $poTelerenaceCheck = 0;
                    $errorMessage = "Input qty can not be greater than ge qty.";
                    $incomingQty = $gateEntryDetail->accepted_qty;
                } else{
                    $poTelerenaceCheck = 1;
                }
            } elseif($request->siDetailId){
                $supplierInvDetail = PoItem::find($request->siDetailId);
                $balanceQty = ($supplierInvDetail->order_qty - ($gateEntryDetail->grn_qty ?? 0.00));
                if($balanceQty < $inputQty){
                    $poTelerenaceCheck = 0;
                    $errorMessage = "Input qty can not be greater than si qty.";
                    $incomingQty = $supplierInvDetail->order_qty;
                } else{
                    $poTelerenaceCheck = 1;
                }
            } elseif(!$request->siDetailId && !$request->geDetailId && ($request->poDetailId)){
                $poTelerenaceCheck = 1;
            } else{
                $poTelerenaceCheck = 1;
            }

            if($poTelerenaceCheck == 0){
                return response() -> json([
                    'data' => array(
                        'order_qty' => $incomingQty,
                        'error_message' => $errorMessage
                    )
                ]);
            }

            if($poTelerenaceCheck == 1){
                $tolerenceInputQty = ($inputQty ?? 0.00) + ($poDetail->grn_qty ?? 0.00);
                $tolerenceBalanceQty = ($poDetail->order_qty - ($tolerenceInputQty ?? 0.00));
                if(($item->po_positive_tolerance && ($item->po_positive_tolerance > 0)) || ($item->po_negative_tolerance && ($item->po_negative_tolerance > 0))){
                    $positiveTolerenceAmt = $item->po_positive_tolerance ? (($item->po_positive_tolerance/$poDetail->order_qty)*100) : 0;
                    $negativeTolerenceAmt = $item->po_negative_tolerance ? (($item->po_negative_tolerance/$poDetail->order_qty)*100) : 0;
                    if($tolerenceInputQty <= ($poDetail->order_qty + $positiveTolerenceAmt)){

                    } else{
                        $errorMessage = "Input Qty can not be greater than balance qty.";
                        $incomingQty = $poDetail->order_qty;
                        return response() -> json([
                            'data' => array(
                                'order_qty' => $incomingQty,
                                'error_message' => $errorMessage
                            )
                        ]);
                    }
                } else{
                    if($tolerenceInputQty > $poDetail->order_qty){
                        $errorMessage = "Input Qty can not be greater than order qty.";
                        $incomingQty = $poDetail->order_qty;
                        return response() -> json([
                            'data' => array(
                                'order_qty' => $incomingQty,
                                'error_message' => $errorMessage
                            )
                        ]);
                    } else{

                    }
                }
            }
        }
        return response()->json(['data' => ['quantity' => $request->qty], 'status' => 200, 'message' => 'fetched']);
    }

    // Get MRN
    public function getMrn(Request $request)
    {
        $putawayData = '';
        $applicableBookIds = array();
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $storeId = $request->store_id ?? null;
        $itemId = $request->item_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $selected_mrn_ids = json_decode($request->selected_mrn_ids) ?? [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $putawayItems = MrnDetail::where(function ($query) use ($seriesId, $applicableBookIds, $docNumber, $itemId, $vendorId, $storeId, $selected_mrn_ids) {
            $query->whereHas('item');
            $query->whereHas('mrnHeader', function ($putaway) use ($seriesId, $applicableBookIds, $docNumber, $vendorId, $storeId) {
                $putaway->where('is_warehouse_required', 1)
                    ->where('store_id', $storeId)
                    ->withDefaultGroupCompanyOrg();
                $putaway->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED]);
                if ($seriesId) {
                    $putaway->where('book_id', $seriesId);
                } else {
                    if (count($applicableBookIds)) {
                        $putaway->whereIn('book_id', $applicableBookIds);
                    }
                }
                if ($docNumber) {
                    $putaway->where('document_number', $docNumber);
                }
                if ($vendorId) {
                    $putaway->where('vendor_id', $vendorId);
                }
            });

            if ($itemId) {
                $query->where('item_id', $itemId);
            }
        });

        if(count($selected_mrn_ids)) {
            $putawayData = MrnDetail::with('mrnHeader')->whereIn('id', $selected_mrn_ids)->first();
            $putawayItems->whereNotIn('id',$selected_mrn_ids);
        }
        $putawayItems = $putawayItems->get();

        $html = view('procurement.put-away.partials.mrn-item-list', [
            'mrnItems' => $putawayItems,
            'mrnData' => $putawayData
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
        $putawayItems = MrnDetail::whereIn('id', $ids)
            ->get();
        $uniqueMrnIds = MrnDetail::whereIn('id', $ids)
            ->distinct()
            ->pluck('mrn_header_id')
            ->toArray();
        if(count($uniqueMrnIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time inspection create from one MRN."]);
        }
        $putawayData = MrnHeader::whereIn('id', $uniqueMrnIds)->where('is_warehouse_required', 1)->first();
        $putawayHeaders = MrnHeader::whereIn('id', $uniqueMrnIds)->where('is_warehouse_required', 1)->get();
        $discounts = collect();
        $expenses = collect();

        foreach ($putawayHeaders as $putaway) {
            foreach ($putaway->headerDiscount as $headerDiscount) {
                if (!intval($headerDiscount->ted_percentage)) {
                    $tedPerc = (floatval($headerDiscount->ted_amount) / floatval($headerDiscount->assesment_amount)) * 100;
                    $headerDiscount['ted_percentage'] = $tedPerc;
                }
                $discounts->push($headerDiscount);
            }

            foreach ($putaway->expenses as $headerExpense) {
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
        $putawayIds = $putawayItems->pluck('mrn_header_id')->all();
        $vendorId = MrnHeader::whereIn('id', $putawayIds)->pluck('vendor_id')->toArray();
        $vendorId = array_unique($vendorId);
        $putawayHeader = MrnHeader::whereIn('id', $uniqueMrnIds)->first();
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
        $html = view('procurement.put-away.partials.mrn-item-row',
        [
                'mrnItems' => $putawayItems,
            ]
        )
        ->render();

        return response()->json(
            [
                'data' => [
                    'pos' => $html,
                    'vendor' => $vendor,
                    'mrnData' => $putawayData,
                    'mrnHeader' => $putawayHeader,
                    'finalExpenses' => $finalExpenses,
                    'finalDiscounts' => $finalDiscounts,
                ],
                'status' => 200,
                'message' => "fetched!"
            ]
        );
    }

    // Maintain Stock Ledger
    private static function maintainStockLedger($mrn)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $mrn->items->pluck('id')->toArray();
        $stockLedgerResponse = StoragePointHelper::saveStoragePoints($mrn, $detailIds, ConstantHelper::MRN_SERVICE_ALIAS, $mrn->document_status);

        return $stockLedgerResponse;
    }

    // Update Po Qty
    private static function updatePoQty($item, $poDetail, $inputQty, $type){
        $user = Helper::getAuthenticatedUser();
        $tolerenceInputQty = ($inputQty ?? 0.00) + ($poDetail->grn_qty ?? 0.00);
        $tolerenceBalanceQty = ($poDetail->order_qty - ($tolerenceInputQty ?? 0.00));
        if(($item->po_positive_tolerance && ($item->po_positive_tolerance > 0)) || ($item->po_negative_tolerance && ($item->po_negative_tolerance > 0))){
            $positiveTolerenceAmt = $item->po_positive_tolerance ? (($item->po_positive_tolerance/$poDetail->order_qty)*100) : 0;
            $negativeTolerenceAmt = $item->po_negative_tolerance ? (($item->po_negative_tolerance/$poDetail->order_qty)*100) : 0;
            if($tolerenceInputQty <= ($poDetail->order_qty + $positiveTolerenceAmt)){
                if(($tolerenceBalanceQty <= $negativeTolerenceAmt) && ($tolerenceBalanceQty >= 0)){
                    $poDetail->grn_qty += floatval($inputQty);
                    $poDetail->short_close_qty += floatval($tolerenceBalanceQty);
                    $poDetail->save();
                }
                if(($tolerenceBalanceQty < 0) && (-($positiveTolerenceAmt) >= $tolerenceBalanceQty)){
                    $poDetail->grn_qty += floatval($inputQty);
                    $poDetail->save();
                }
            } else{
                DB::rollBack();
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
            $putaway = PutAwayHeader::find($request->id);
            if (isset($putaway)) {
                $revoke = Helper::approveDocument($putaway->book_id, $putaway->id, $putaway->revision_number, '', [], 0, ConstantHelper::REVOKE, $putaway->total_amount, get_class($putaway));
                if ($revoke['message']) {
                    \DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $putaway->document_status = $revoke['approvalStatus'];
                    $putaway->save();
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
        $view = 'procurement.put-away.partials.import-item-row';

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
        $purchaseOrderIds = PutAwayHeader::withDefaultGroupCompanyOrg()
                            ->distinct()
                            ->pluck('purchase_order_id');
        $purchaseOrders = PurchaseOrder::whereIn('id', $purchaseOrderIds)->get();
        $soIds = MrnDetail::whereHas('mrnHeader', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->distinct()
                ->pluck('so_id');

        $so = ErpSaleOrder::whereIn('id', $soIds)->get();
        $gateEntry = PutAwayHeader::withDefaultGroupCompanyOrg()
        ->distinct()
        ->whereNotNull('gate_entry_no')
        ->where('gate_entry_no', '!=', '')
        ->pluck('gate_entry_no');
        $lot_no = PutAwayHeader::withDefaultGroupCompanyOrg()
        ->distinct()
        ->whereNotNull('lot_number')
        ->where('lot_number', '!=', '')
        ->pluck('lot_number');
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;
        // $attributes = Attribute::get();
        return view('procurement.put-away.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'purchaseOrders', 'gateEntry', 'lot_no', 'statusCss'));
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

        $query = PutAwayHeader::query()
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
        return view('procurement.put-away.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'purchaseOrders', 'gateEntry', 'lot_no', 'statusCss'));
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
        $materialReceipts = PutAwayHeader::withDefaultGroupCompanyOrg()
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
            } else if ($request->status === ConstantHelper::SUBMITTED) {
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

        foreach ($materialReceipts as $putaway) {
            foreach ($putaway->items as $putawayItem) {
                $reportRow = new stdClass();

                // Header Details
                $header = $putawayItem->header;
                $total_item_value = (($putawayItem?->rate ?? 0.00) * ($putawayItem?->accepted_qty ?? 0.00)) - ($putawayItem?->discount_amount ?? 0.00);
                $reportRow->id = $putawayItem->id;
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
                $reportRow->category_name = $putawayItem->item ?->category ?-> name;
                $reportRow->sub_category_name = $putawayItem->item ?->category ?-> name;
                $reportRow->item_type = $putawayItem->item ?->type;
                $reportRow->sub_type = null;
                $reportRow->item_name = $putawayItem->item ?->item_name;
                $reportRow->item_code = $putawayItem->item ?->item_code;

                // Amount Details
                $reportRow->receipt_qty = number_format($putawayItem->order_qty, 2);
                $reportRow->accepted_qty = number_format($putawayItem->accepted_qty, 2);
                $reportRow->rejected_qty = number_format($putawayItem->rejected_qty, 2);
                $reportRow->pr_qty = number_format($putawayItem->pr_qty, 2);
                $reportRow->pr_rejected_qty = number_format($putawayItem->pr_rejected_qty, 2);
                $reportRow->purchase_bill_qty = number_format($putawayItem->purchase_bill_qty, 2);
                $reportRow->store_name = $putawayItem?->erpStore?->store_name;
                $reportRow->sub_store_name = $putawayItem?->subStore?->name;
                $reportRow->rate = number_format($putawayItem->rate);
                $reportRow->basic_value = number_format($putawayItem->basic_value, 2);
                $reportRow->item_discount = number_format($putawayItem->discount_amount, 2);
                $reportRow->header_discount = number_format($putawayItem->header_discount_amount, 2);
                $reportRow->item_amount = number_format($total_item_value, 2);

                // Attributes UI
                // $attributesUi = '';
                // if (count($putawayItem->item_attributes) > 0) {
                //     foreach ($putawayItem->item_attributes as $putawayAttribute) {
                //         $attrName = $putawayAttribute->attribute_name;
                //         $attrValue = $putawayAttribute->attribute_value;
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
        $inventoryQty = ItemHelper::convertToBaseUom($item->id, $item->uom_id, $request->qty);

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

    # Putaway Get Labels
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
        $ptwHeader = PutAwayHeader::withDefaultGroupCompanyOrg()
            ->where('id', $id)
            ->first();

        if (!$ptwHeader) {
            return response()->json([
                'status' => 204,
                "is_setup" => false,
                'message' => 'Put Away not found.',
            ], 422);
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$ptwHeader->document_status] ?? '';

        if (request()->ajax()) {
            $records = $ptwHeader->itemLocations()
                ->with([
                    'header',
                    'detail',
                    'detail.item'
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
        return view('procurement.put-away.print-labels', [
            'mrn' => $ptwHeader,
            'servicesBooks' => $servicesBooks,
            'docStatusClass' => $docStatusClass
        ]);
    }

    # Putaway Print Labels
    public function printBarcodes($id)
    {
        $packets = PutAwayItemLocation::with([
            'header',
            'detail'
        ])
        ->where('header_id', $id)
        ->get();

        $html = view('procurement.put-away.print-barcodes', compact('packets'))->render();

        return response()->json([
            'status' => 200,
            'html' => $html
        ]);
    }

    public static function locationListing(Request $request)
    {
        if (!$request->has('user_id') || empty($request->user_id)) {
            return response()->json([
                'status' => 200,
                'result' => []
            ]);
        }

        if ($request->employee_type == "employee") {
            $employee = Employee::find($request->user_id);
        } else {
            $employee = User::find($request->user_id);
        }

        $storesQuery = ErpStore::where('status', ConstantHelper::ACTIVE);

        if (!empty($request->organization_id)) {
            $storesQuery->where('organization_id', $request->organization_id);
        }

        if (!empty($request->group_id)) {
            $storesQuery->where('group_id', $request->group_id);
        }

        if (!empty($request->company_id)) {
            $storesQuery->where('company_id', $request->company_id);
        }

        if ($request->employee_type == "employee" && $employee) {
            $storesQuery->whereHas('employees', function ($employeeQuery) use ($employee) {
                $employeeQuery->where('employee_id', $employee->id);
            });
        }

        $stores = $storesQuery->get();

        return array(
                'message' => 'Records retrieved successfully.',
                'data' => array(
                    'stores' => $stores
                )
            );
    }


    public static function subLocationListing(Request $request)
    {
        if (!$request->filled('store_id')) {
            return response()->json([
                'status' => 200,
                'result' => []
            ]);
        }

        $query = ErpSubStoreParent::query();

        $query->where('store_id', $request->store_id);

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        $subStoreIds = $query->get()->pluck('sub_store_id')->toArray();

        $subStores = ErpSubStore::select('id', 'name', 'code', 'station_wise_consumption', 'is_warehouse_required')
            ->whereIn('id', $subStoreIds)
            ->where(function ($query) {
                $query->where('status', ConstantHelper::ACTIVE)
                    ->where('is_warehouse_required', '1');
            })
            ->get();

        return array(
                'message' => 'Records retrieved successfully.',
                'data' => array(
                    'sub_stores' => $subStores
                )
            );
    }


    public function mrnListing(Request $request)
    {
        $query = MrnHeader::with([
            'items',
            'vendor',
            'erpStore',
            'erpSubStore',
            'currency',
            'itemLocations'
        ])
        ->withDraftListingLogic()
        ->where('is_warehouse_required', '1');

        $query->when(!blank($request->company_id), function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });

        $query->when(!blank($request->organization_id), function ($q) use ($request) {
            $q->where('organization_id', $request->organization_id);
        });

        $query->when(!blank($request->store_id), function ($q) use ($request) {
            $q->where('store_id', $request->store_id);
        });

        $query->when(!blank($request->group_id), function ($q) use ($request) {
            $q->where('group_id', $request->group_id);
        });

        $query->when(!blank($request->sub_store_id), function ($q) use ($request) {
            $q->where('sub_store_id', $request->sub_store_id);
        });

        $records = $query->get();
        return array(
                'message' => 'Records retrieved successfully.',
                'data' => array(
                    'records' => $records
                )
            );
    }


}
