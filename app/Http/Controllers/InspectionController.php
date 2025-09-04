<?php
namespace App\Http\Controllers;

use DB;
use PDF;
use DateTime;
use stdClass;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Http\Request;
use App\Http\Requests\InspectionRequest;
use App\Http\Requests\EditInspectionRequest;

use App\Models\InspectionTed;
use App\Models\InspChecklist;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\InspBatchDetail;
use App\Models\InspectionItemAttribute;

use App\Models\InspectionTedHistory;
use App\Models\InspBatchDetailHistory;
use App\Models\InspectionHeaderHistory;
use App\Models\InspectionDetailHistory;
use App\Models\InspectionItemLocation;
use App\Models\InspectionItemAttributeHistory;


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
use App\Models\ErpItem;
use App\Models\ErpStore;
use App\Models\Currency;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Employee;
use App\Models\ErpVendor;
use App\Models\Customer;
use App\Models\MrnDetail;
use App\Models\MrnHeader;
use App\Models\CostCenter;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\AlternateUOM;
use App\Models\ErpSaleOrder;
use App\Models\Organization;
use App\Models\NumberPattern;
use App\Models\MrnBatchDetail;
use App\Models\AttributeGroup;
use App\Models\EwayBillMaster;
use App\Models\Configuration;
use App\Models\InspChecklistHistory;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\BookHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\GstInvoiceHelper;
use App\Helpers\InspectionHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\ServiceParametersHelper;

use App\Jobs\SendEmailJob;

use App\Lib\Services\WHM\WhmJob;
use App\Services\Inspection\DeleteService;
use App\Services\Inspection\InspectionService;
use App\Services\Inspection\UpdateAddressService;
use App\Services\Inspection\CheckAndUpdateService;

use App\Exports\PurchaseReturnExport;
use App\Lib\Services\WHM\PutawayJob;

class InspectionController extends Controller
{
    protected $inspectionService;

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
        $orderType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
        request() -> merge(['type' => $orderType]);
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $records = InspectionHeader::with(
                [
                    'items',
                    'vendor',
                ]
            )
            // ->withDefaultGroupCompanyOrg()
            ->withDraftListingLogic()
            ->bookViewAccess($parentUrl)
            ->latest();
            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $route = route('inspection.edit', $row->id);
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
                ->editColumn('document_date', function ($row) {
                    return date('d/m/Y', strtotime($row->document_date)) ?? 'N/A';
                })
                ->addColumn('location_name', function ($row) {
                    return $row->erpStore ? $row->erpStore?->store_name : 'N/A';
                })
                ->addColumn('store_name', function ($row) {
                    return $row->erpSubStore ? $row->erpSubStore?->name : 'N/A';
                })
                ->addColumn('rejected_store_name', function ($row) {
                    return $row->rejectedSubStore ? $row->rejectedSubStore?->name : 'N/A';
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('vendor_name', function ($row) {
                    return $row->vendor ? $row->vendor?->company_name : 'N/A';
                })
                ->addColumn('currency', function ($row) {
                    return $row->currency ? $row->currency?->short_name : 'N/A';
                })
                ->addColumn('total_items', function ($row) {
                    return $row->items ? count($row->items) : 0;
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }
        return view('procurement.inspection.index', [
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
        $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);

        return view('procurement.inspection.create', [
            'books' => $books,
            'vendors' => $vendors,
            'locations' =>$locations,
            'servicesBooks'=>$servicesBooks,
            'materialReceipts' => $materialReceipts,
            'transportationModes' => $transportationModes
        ]);
    }

    # Purchase Bill store
    public function store(InspectionRequest $request)
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

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request->currency_id, $request->document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }

            $transportationMode = EwayBillMaster::find($request->transportation_mode);

            $inspection = new InspectionHeader();
            $inspection->fill($request->all());
            $inspection->store_id = $request->header_store_id;
            $inspection->sub_store_id = $request->sub_store_id;
            $inspection->rejected_sub_store_id = $request->rejected_sub_store_id;
            $inspection->organization_id = $organization->id;
            $inspection->group_id = $organization->group_id;
            $inspection->company_id = $organization->company_id;
            $inspection->book_code = $request->book_code;
            $inspection->series_id = $request->book_id;
            $inspection->book_id = $request->book_id;
            $inspection->book_code = $request->book_code;
            $inspection->vendor_id = $request->vendor_id;
            $inspection->vendor_code = $request->vendor_code;
            $inspection->supplier_invoice_no = $request->supplier_invoice_no;
            $inspection->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $inspection->transporter_name = $request->transporter_name;
            $inspection->vehicle_no = $request->vehicle_no;
            $inspection->billing_to = $request->billing_id;
            $inspection->ship_to = $request->shipping_id;
            $inspection->billing_address = $request->billing_address;
            $inspection->shipping_address = $request->shipping_address;
            $inspection->reference_type = $request->reference_type;
            $inspection->revision_number = 0;
            $document_number = $request->document_number ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_number;
            $regeneratedDocExist = InspectionHeader::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                ->where('document_number', $document_number)->first();
            //Again check regenerated doc no
            if (isset($regeneratedDocExist)) {
                return response()->json([
                    'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                    'error' => "",
                ], 422);
            }

            $inspection->doc_number_type = $numberPatternData['type'];
            $inspection->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $inspection->doc_prefix = $numberPatternData['prefix'];
            $inspection->doc_suffix = $numberPatternData['suffix'];
            $inspection->doc_no = $numberPatternData['doc_no'];

            $inspection->document_number = $document_number;
            $inspection->document_date = $request->document_date;
            $inspection->final_remark = $request->remarks ?? null;

            $inspection->total_item_amount = 0.00;
            $inspection->total_discount = 0.00;
            $inspection->taxable_amount = 0.00;
            $inspection->total_taxes = 0.00;
            $inspection->total_after_tax_amount = 0.00;
            $inspection->expense_amount = 0.00;
            $inspection->total_amount = 0.00;
            $inspection->save();

            // ✅ Capture Address response
            $addressService = new UpdateAddressService();
            $addressResponse = $addressService->updateAddress($inspection);
            if($addressResponse['status'] === 'error') {
                \DB::rollBack();
                return response()->json([
                    'message' => $addressResponse['message'],
                    'error' => 'ERR04'
                ], 422);
            }

            if (isset($request->all()['components'])) {
                $inspectionItemArr = [];
                foreach ($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    if ($item) {
                        // Normalize to something countable
                        $checklists = $item->loadInspectionChecklists();

                        $hasChecklist = $checklists instanceof Collection
                            ? $checklists->isNotEmpty()
                            : (is_array($checklists) ? !empty($checklists) : false);

                        if ($hasChecklist) {
                            // inspectionData may be JSON string, array, or null
                            $raw = $component['inspectionData'] ?? null;
                            $inspectionData = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $inspectionData = []; // or handle as error if you prefer
                            }

                            $inspectionValidator = InspectionHelper::validateInspectionCheckList((array)$inspectionData, $item);
                            if (!$inspectionValidator['status']) {
                                DB::rollBack(); // keep only if you're inside a transaction
                                return response()->json([
                                    'message' => $inspectionValidator['message'],
                                    'error'   => 'Inspection001',
                                ], 422);
                            }
                        }
                    }

                    // Check Item Batches
                    if($item && $item->is_batch_no) {
                        $batchDetails = (isset($component['batch_details']) && is_string($component['batch_details']))
                                ? json_decode($component['batch_details'], true)
                                : $component['batch_details'];
                        $inspectionValidator = InspectionHelper::validateBatches((array) $batchDetails, $item);
                        if(!$inspectionValidator['status']) {
                            DB::rollBack();
                            return response() -> json([
                                'message' => $inspectionValidator['message'],
                                'error' => 'Inspection001'
                            ], 422);
                        }
                    }

                    $so_id = null;
                    $inputQty = 0.00;
                    $balanceQty = 0.00;
                    $availableQty = 0.00;
                    $mrn_detail_id = null;
                    if (isset($component['mrn_detail_id']) && $component['mrn_detail_id']) {
                        $mrnDetail = MrnDetail::find($component['mrn_detail_id']);
                        $mrn_detail_id = $mrnDetail->id ?? null;
                        $mrnHeaderId = $component['mrn_header_id'];
                        if ($mrnDetail) {
                            $inputQty = $component['order_qty'];
                            $acceptedQty = $component['accepted_qty'];
                            $rejectedQty = $component['rejected_qty'];
                            $balanceQty = ($mrnDetail->order_qty - ($mrnDetail->inspection_qty ?? 0.00));
                            $rejectQty = ($inputQty - $acceptedQty);

                            if($balanceQty < $inputQty){
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Inspected qty can not be greater than '.$balanceQty.'.'
                                ], 422);
                            }

                            if($inputQty < $acceptedQty){
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Accepted qty can not be greater than '.$inputQty.'.'
                                ], 422);
                            }

                            if($rejectQty < $rejectedQty){
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Rejected qty can not be greater than ' .$rejectQty. '.'
                                ], 422);
                            }
                            $mrnDetail->inspection_qty += floatval($inputQty);
                            $mrnDetail->save();
                            $so_id = $mrnDetail->so_id;
                        } else{
                            DB::rollBack();
                            return response()->json([
                                'message' => 'MRN Not Found'
                            ], 422);
                        }
                    }
                    $inventory_uom_id = null;
                    $inventory_uom_code = null;
                    $order_inventory_uom_qty = 0.00;
                    $accepted_inventory_uom_qty = 0.00;
                    $rejected_inventory_uom_qty = 0.00;
                    $orderQty = $component['order_qty'];
                    $acceptedQty = $component['accepted_qty'];
                    $rejectedQty = $component['rejected_qty'];
                    $inventoryUom = Unit::find($item->uom_id ?? null);
                    $itemUomId = $item->uom_id ?? null;
                    $inventory_uom_id = $inventoryUom->id;
                    $inventory_uom_code = $inventoryUom->name;
                    if(@$component['uom_id'] == $itemUomId) {
                        $order_inventory_uom_qty = floatval($orderQty) ?? 0.00 ;
                        $accepted_inventory_uom_qty = floatval($acceptedQty) ?? 0.00 ;
                        $rejected_inventory_uom_qty = floatval($rejectedQty) ?? 0.00 ;
                    } else {
                        $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $order_inventory_uom_qty = floatval($orderQty) * $alUom->conversion_to_inventory;
                            $accepted_inventory_uom_qty = floatval($acceptedQty) * $alUom->conversion_to_inventory;
                            $rejected_inventory_uom_qty = floatval($rejectedQty) * $alUom->conversion_to_inventory;
                        }
                    }

                    $uom = Unit::find($component['uom_id'] ?? null);
                    $inspectionItemArr[] = [
                        'header_id' => $inspection->id,
                        'mrn_detail_id' => $mrn_detail_id,
                        'mrn_header_id' => $mrnHeaderId,
                        'so_id' => $so_id,
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
                        'inventory_uom_qty' => $order_inventory_uom_qty ?? 0.00,
                        'accepted_inventory_uom_id' => $inventory_uom_id ?? null,
                        'accepted_inventory_uom_code' => $inventory_uom_code ?? null,
                        'accepted_inventory_uom_qty' => $accepted_inventory_uom_qty ?? 0.00,
                        'rejected_inventory_uom_id' => $inventory_uom_id ?? null,
                        'rejected_inventory_uom_code' => $inventory_uom_code ?? null,
                        'rejected_inventory_uom_qty' => $rejected_inventory_uom_qty ?? 0.00,
                        'remark' => $component['remark'] ?? null,
                    ];
                }

                unset($inspectionItem);

                foreach ($inspectionItemArr as $_key => $inspectionItem) {
                    # Inspection Detail Save
                    $inspectionDetail = new InspectionDetail;

                    $inspectionDetail->header_id = $inspectionItem['header_id'];
                    $inspectionDetail->mrn_detail_id = $inspectionItem['mrn_detail_id'];
                    $inspectionDetail->mrn_header_id = $inspectionItem['mrn_header_id'];
                    $inspectionDetail->so_id = $inspectionItem['so_id'];
                    $inspectionDetail->item_id = $inspectionItem['item_id'];
                    $inspectionDetail->item_code = $inspectionItem['item_code'];
                    $inspectionDetail->item_name = $inspectionItem['item_name'];
                    $inspectionDetail->hsn_id = $inspectionItem['hsn_id'];
                    $inspectionDetail->hsn_code = $inspectionItem['hsn_code'];
                    $inspectionDetail->uom_id = $inspectionItem['uom_id'];
                    $inspectionDetail->uom_code = $inspectionItem['uom_code'];
                    $inspectionDetail->order_qty = $inspectionItem['order_qty'];
                    $inspectionDetail->accepted_qty = $inspectionItem['accepted_qty'];
                    $inspectionDetail->rejected_qty = $inspectionItem['rejected_qty'];
                    $inspectionDetail->inventory_uom_id = $inspectionItem['inventory_uom_id'];
                    $inspectionDetail->inventory_uom_code = $inspectionItem['inventory_uom_code'];
                    $inspectionDetail->inventory_uom_qty = $inspectionItem['inventory_uom_qty'];
                    $inspectionDetail->accepted_inv_uom_id = $inspectionItem['accepted_inventory_uom_id'];
                    $inspectionDetail->accepted_inv_uom_code = $inspectionItem['accepted_inventory_uom_code'];
                    $inspectionDetail->accepted_inv_uom_qty = $inspectionItem['accepted_inventory_uom_qty'];
                    $inspectionDetail->rejected_inv_uom_id = $inspectionItem['rejected_inventory_uom_id'];
                    $inspectionDetail->rejected_inv_uom_code = $inspectionItem['rejected_inventory_uom_code'];
                    $inspectionDetail->rejected_inv_uom_qty = $inspectionItem['rejected_inventory_uom_qty'];
                    $inspectionDetail->remark = $inspectionItem['remark'];
                    $inspectionDetail->save();
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];

                    #Save component Attr
                    foreach ($inspectionDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $inspectionAttr = new InspectionItemAttribute;
                            $inspectionAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $inspectionAttr->header_id = $inspection->id;
                            $inspectionAttr->detail_id = $inspectionDetail->id;
                            $inspectionAttr->item_attribute_id = $itemAttribute->id;
                            $inspectionAttr->item_code = $component['item_code'] ?? null;
                            $inspectionAttr->attr_name = $itemAttribute->attribute_group_id;
                            $inspectionAttr->attr_value = $inspectionAttrName ?? null;
                            $inspectionAttr->save();
                        }
                    }

                    #Save item checklists
                    if (!empty($component['inspectionData'])) {
                        $itemChecklists = is_string($component['inspectionData'])
                            ? json_decode($component['inspectionData'], true)
                            : $component['inspectionData'];
                        if (is_array($itemChecklists)) {
                            $inspChecklist = InspectionService::manageChecklistData($itemChecklists, $inspection, $inspectionDetail);
                            if ($inspChecklist['status'] == 'error') {
                                \DB::rollBack();
                                return response()->json([
                                    'message' => $inspChecklist['message'],
                                    'error' => ''
                                ], 422);
                            }
                        } else {
                            \DB::rollBack();
                            return response()->json([
                                'message' => "Invalid JSON for itemChecklists: " .$component['inspectionData'],
                                'error' => ''
                            ], 422);
                        }
                    }

                    #Save batch details
                    if (!empty($component['batch_details'])) {
                        $batchDetails = is_string($component['batch_details'])
                            ? json_decode($component['batch_details'], true)
                            : $component['batch_details'];

                        if (is_array(value: $batchDetails)) {
                            $inspBatchDetail = InspectionService::manageBatchDetails($batchDetails, $inspection, $inspectionDetail);
                            if ($inspBatchDetail['status'] == 'error') {
                                \DB::rollBack();
                                return response()->json([
                                    'message' => $inspBatchDetail['message'],
                                    'error' => ''
                                ], 422);
                            }
                        } else {
                            \DB::rollBack();
                            return response()->json(['message' => 'Invalid JSON for batch details.'], 422);
                        }
                    }
                }

            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please add atleast one row in component table.',
                    'error' => "",
                ], 422);
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($inspection->vendor->currency_id, $inspection->document_date);

            $inspection->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $inspection->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $inspection->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $inspection->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $inspection->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $inspection->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $inspection->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $inspection->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $inspection->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $inspection->save();

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $inspection->book_id;
                $docId = $inspection->id;
                $remarks = $inspection->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $inspection->approval_level ?? 1;
                $revisionNumber = $inspection->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($inspection);
                $totalValue = $inspection->total_amount ?? 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                if ($approveDocument['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $approveDocument['message'],
                    ],422);
                }
            }

            $inspection = InspectionHeader::find($inspection->id);
            if ($request->document_status == 'submitted') {
                $inspection->document_status = $approveDocument['approvalStatus'] ?? $inspection->document_status;
            } else {
                $inspection->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }
            /*Inspection Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $inspection->uploadDocuments($request->file('attachment'), 'pb', false);
            }
            $inspection->mrn_header_id = $mrnHeaderId;
            $inspection->is_enforce_uic_scanning = 1;
            $inspection->save();
            if(in_array($inspection->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)){
                $updateMrn = InspectionHelper::updateMrnDetail($inspection);
                if($updateMrn['status'] == 'error') {
                    \DB::rollBack();
                    return response()->json([
                        'message' => $updateMrn['message'],
                        'error' => ''
                    ], 422);
                }
            }

            $redirectUrl = '';
            if(($inspection->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $inspection->id . '/pdf');
            }

            $config = Configuration::where('type','organization')
                ->where('type_id', $user->organization_id)
                ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
                ->whereNull('deleted_at')
                ->first();

            // Preconditions: approved status AND config = 'yes'
            $approvedSet = (array) ConstantHelper::DOCUMENT_STATUS_APPROVED;
            $isApproved  = in_array($inspection->document_status, $approvedSet, true);
            $cfgYes      = $config && strcasecmp((string) $config->config_value, 'yes') === 0;
            if ($isApproved && $cfgYes) {
                // Cache relations (will lazy-load if not eager-loaded)
                $mainSubStore     = $inspection->erpSubStore;
                $rejectedSubStore = $inspection->rejectedSubStore;

                // Compute flags (treat null as false)
                // $mainNeedsPutaway     = (int) ($mainSubStore->is_warehouse_required ?? 0) === 1;
                // $rejectedNeedsPutaway = (int) ($rejectedSubStore->is_warehouse_required ?? 0) === 1;

                // Build targets and create jobs in one pass
                $targets = [];
                if ($mainSubStore) {
                    $targets[] = 'main_store';
                }
                if ($mainSubStore && $rejectedSubStore) {
                    $targets[] = 'rejected_store';
                }

                if (!empty($targets)) {
                    $whmJob = new PutawayJob();
                    foreach ($targets as $target) {
                        $whmJob->createJob($inspection->id, \App\Models\InspectionHeader::class, $jobType = null, $target);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $inspection,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
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

        $inspection = InspectionHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);
        $totalItemValue = $inspection->items()->sum('basic_value');
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($inspection->series_id, $inspection->document_status, $inspection->id, $inspection->total_amount, $inspection->approval_level, $inspection->created_by ?? 0, $userType['type']);
        $approvalHistory = Helper::getApprovalHistory($inspection->series_id, $inspection->id, $inspection->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$inspection->document_status];
        $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        return view('procurement.inspection.view', [
            'pb' => $inspection,
            'buttons' => $buttons,
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
        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $serviceAlias = ConstantHelper::INSPECTION_SERVICE_ALIAS;
        $mrnServiceAlias = ConstantHelper::MRN_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $user = Helper::getAuthenticatedUser();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $inspection = InspectionHeader::with(['vendor', 'currency', 'items', 'book', 'source'])
            ->findOrFail($id);
        $totalItemValue = $inspection->items()->sum('basic_value');
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $revision_number = $inspection->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($inspection->book_id, $inspection->document_status, $inspection->id, $inspection->total_amount, $inspection->approval_level, $inspection->created_by ?? 0, $userType['type'], $revision_number);
        $revNo = $inspection->revision_number;
        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $inspection->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($inspection->book_id, $inspection->id, $revNo, $inspection->total_amount);
        $view = 'procurement.inspection.edit';
        if ($request->has('revisionNumber') && $request->revisionNumber != $inspection->revision_number) {
            $inspection = $inspection->source;
            $inspection = InspectionHeaderHistory::where('revision_number', $request->revisionNumber)
                ->where('source_id', $inspection->source_id)
                ->first();
            $view = 'procurement.inspection.view';
        }
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$inspection->document_status] ?? '';
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $store = $inspection->erpStore;
        $deliveryAddress = $store?->address?->display_address;
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $orgAddress = $organizationAddress?->display_address;
        $eInvoice = $inspection->irnDetail()->first();

        $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
        $subStoreCount = $inspection->items()->where('sub_store_id', '!=', null)->count() ?? 0;
        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'DESC')
            ->get();

        $headerIds = $pb->mrn_header_id ?? null;
        $headerIds = $headerIds ? (array) $headerIds : [];

        $detailsIds = collect($pb->items ?? [])
            ->pluck('mrn_detail_id')
            ->filter()
            ->values()
            ->all();

        return view($view, [
            'user' => $user,
            'users' => $users,
            'books' => $books,
            'mrn' => $inspection,
            'buttons' => $buttons,
            'vendors' => $vendors,
            'eInvoice' => $eInvoice,
            'headerIds'=> $headerIds,
            'erpStores' => $erpStores,
            'locations' => $locations,
            'orgAddress'=> $orgAddress,
            'detailsIds'=> $detailsIds,
            'subStoreCount' => $subStoreCount,
            'totalItemValue' => $totalItemValue,
            'docStatusClass' => $docStatusClass,
            'mrnServiceAlias'=>$mrnServiceAlias,
            'deliveryAddress'=> $deliveryAddress,
            'revision_number' => $revision_number,
            'approvalHistory' => $approvalHistory,
            'transportationModes' => $transportationModes,
        ]);
    }

    # Inspection Update
    public function update(EditInspectionRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();

        $inspection = InspectionHeader::find($id);
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

            $currentStatus = $inspection->document_status;
            $actionType = $request->action_type;

            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $this->amendmentSubmit($request, $id);
            }

            $keys = ['deletedMrnItemIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }
            if (count($deletedData['deletedMrnItemIds'])) {
                $inspectionItems = InspectionDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();
                # all ted remove item level
                foreach ($inspectionItems as $inspectionItem) {
                    $mrnDetail = MrnDetail::find($inspectionItem->mrn_detail_id);
                    $mrnDetail->inspection_qty -= (float) $inspectionItem->order_qty;
                    $mrnDetail->save();
                    # all attr remove
                    $inspectionItem->attributes()->delete();
                    $inspectionItem->delete();
                }
            }

            # Inspection Header save
            $inspection->store_id = $request->header_store_id;
            $inspection->sub_store_id = $request->sub_store_id;
            // $inspection->rejected_sub_store_id = $request->rejected_sub_store_id;
            $inspection->gate_entry_no = $request->gate_entry_no ?? '';
            $inspection->gate_entry_date = $request->gate_entry_date ? date('Y-m-d', strtotime($request->gate_entry_date)) : '';
            $inspection->supplier_invoice_date = $request->supplier_invoice_date ? date('Y-m-d', strtotime($request->supplier_invoice_date)) : '';
            $inspection->supplier_invoice_no = $request->supplier_invoice_no ?? '';
            $inspection->eway_bill_no = $request->eway_bill_no ?? '';
            $inspection->consignment_no = $request->consignment_no ?? '';
            $inspection->transporter_name = $request->transporter_name ?? '';
            $inspection->vehicle_no = $request->vehicle_no ?? '';
            $inspection->final_remark = $request->remarks ?? '';
            $inspection->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $inspection->save();

            $vendorBillingAddress = $inspection->billingAddress ?? null;
            $vendorShippingAddress = $inspection->shippingAddress ?? null;

            if ($vendorBillingAddress) {
                $billingAddress = $inspection->bill_address_details()->firstOrNew([
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
                $shippingAddress = $inspection->ship_address_details()->firstOrNew([
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
            if($inspection?->erpStore)
            {
                $storeAddress  = $inspection?->erpStore->address;
                $storeLocation = $inspection->store_address()->firstOrNew();
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
                $inspectionItemArr = [];
                foreach ($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    $mrn_detail_id = null;
                    if (isset($component['inspection_dtl_id']) && $component['inspection_dtl_id']) {
                        $inspectionDetail = InspectionDetail::find($component['inspection_dtl_id']);
                    }
                    $reference_type = $inspection->reference_type;
                    if ($inspection->mrn_header_id) {
                        $reference_type = 'mrn';
                    }
                    // Check Inspection
                    if ($item) {
                        // Normalize to something countable
                        $checklists = $item->loadInspectionChecklists();

                        $hasChecklist = $checklists instanceof Collection
                            ? $checklists->isNotEmpty()
                            : (is_array($checklists) ? !empty($checklists) : false);

                        if ($hasChecklist) {
                            // inspectionData may be JSON string, array, or null
                            $raw = $component['inspectionData'] ?? null;
                            $inspectionData = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $inspectionData = []; // or handle as error if you prefer
                            }

                            $inspectionValidator = InspectionHelper::validateInspectionCheckList((array)$inspectionData, $item);

                            if (!$inspectionValidator['status']) {
                                DB::rollBack(); // keep only if you're inside a transaction
                                return response()->json([
                                    'message' => $inspectionValidator['message'],
                                    'error'   => 'Inspection001',
                                ], 422);
                            }
                        }
                    }

                    // Validate Quantity Backend
                    $validateQty = self::validateQuantityBackend($component, $reference_type);
                    if ($validateQty['status'] === 'error') {
                        \DB::rollBack();
                        return response()->json([
                            'message' => $validateQty['message']
                        ], 422);
                    }

                    if (isset($component['mrn_detail_id']) && $component['mrn_detail_id']) {
                        $mrnDetail = MrnDetail::find($component['mrn_detail_id']);
                        $mrn_detail_id = $mrnDetail->id ?? null;
                        $mrnHeaderId = $component['mrn_header_id'];
                        if ($mrnDetail) {
                            $orderQty = floatval(@$mrnDetail->order_qty) ?? 0.00;
                            $componentQty = floatval($component['order_qty']);
                            $qtyDifference = $componentQty - $orderQty;
                            if($qtyDifference) {
                                $mrnDetail->inspection_qty += $qtyDifference;
                            }
                            $mrnDetail->save();
                        }
                    }
                    $inventory_uom_code = null;
                    $order_inventory_uom_qty = 0.00;
                    $accepted_inventory_uom_qty = 0.00;
                    $rejected_inventory_uom_qty = 0.00;
                    $orderQty = $component['order_qty'];
                    $acceptedQty = $component['accepted_qty'];
                    $rejectedQty = $component['rejected_qty'];
                    $inventoryUom = Unit::find($item->uom_id ?? null);
                    $itemUomId = $item->uom_id ?? null;
                    $inventory_uom_id = $inventoryUom->id;
                    $inventory_uom_code = $inventoryUom->name;
                    if(@$component['uom_id'] == $itemUomId) {
                        $order_inventory_uom_qty = floatval($orderQty) ?? 0.00 ;
                        $accepted_inventory_uom_qty = floatval($acceptedQty) ?? 0.00 ;
                        $rejected_inventory_uom_qty = floatval($rejectedQty) ?? 0.00 ;
                    } else {
                        $alUom = AlternateUOM::where('item_id', $component['item_id'])->where('uom_id', $component['uom_id'])->first();
                        if($alUom) {
                            $order_inventory_uom_qty = floatval($orderQty) * $alUom->conversion_to_inventory;
                            $accepted_inventory_uom_qty = floatval($acceptedQty) * $alUom->conversion_to_inventory;
                            $rejected_inventory_uom_qty = floatval($rejectedQty) * $alUom->conversion_to_inventory;
                        }
                    }
                    $uom = Unit::find($component['uom_id'] ?? null);
                    $inspectionItemArr[] = [
                        'header_id' => $inspection->id,
                        'mrn_detail_id' => $mrn_detail_id,
                        'mrn_header_id' => $mrnHeaderId,
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
                        'inventory_uom_qty' => $order_inventory_uom_qty ?? 0.00,
                        'accepted_inventory_uom_id' => $inventory_uom_id ?? null,
                        'accepted_inventory_uom_code' => $inventory_uom_code ?? null,
                        'accepted_inventory_uom_qty' => $accepted_inventory_uom_qty ?? 0.00,
                        'rejected_inventory_uom_id' => $inventory_uom_id ?? null,
                        'rejected_inventory_uom_code' => $inventory_uom_code ?? null,
                        'rejected_inventory_uom_qty' => $rejected_inventory_uom_qty ?? 0.00,
                        'remark' => $component['remark'] ?? null,
                    ];
                }
                unset($inspectionItem);

                foreach ($inspectionItemArr as $_key => $inspectionItem) {
                    $_key = $_key + 1;
                    $component = $request->all()['components'][$_key] ?? [];
                    # Inspection Detail Save
                    $inspectionDetail = InspectionDetail::find($component['inspection_dtl_id'] ?? null) ?? new InspectionDetail;

                    $isNewItem = false;
                    if(isset($inspectionDetail->item_id) && $inspectionDetail->item_id) {
                        $isNewItem = $inspectionDetail->item_id != ($inspectionItem['item_id'] ?? null);
                    }

                    $inspectionDetail->header_id = $inspectionItem['header_id'];
                    $inspectionDetail->mrn_detail_id = $inspectionItem['mrn_detail_id'];
                    $inspectionDetail->item_id = $inspectionItem['item_id'];
                    $inspectionDetail->item_code = $inspectionItem['item_code'];

                    $inspectionDetail->hsn_id = $inspectionItem['hsn_id'];
                    $inspectionDetail->hsn_code = $inspectionItem['hsn_code'];
                    $inspectionDetail->uom_id = $inspectionItem['uom_id'];
                    $inspectionDetail->uom_code = $inspectionItem['uom_code'];
                    $inspectionDetail->order_qty = $inspectionItem['order_qty'];
                    $inspectionDetail->accepted_qty = $inspectionItem['accepted_qty'];
                    $inspectionDetail->rejected_qty = $inspectionItem['rejected_qty'];
                    $inspectionDetail->inventory_uom_id = $inspectionItem['inventory_uom_id'];
                    $inspectionDetail->inventory_uom_code = $inspectionItem['inventory_uom_code'];
                    $inspectionDetail->inventory_uom_qty = $inspectionItem['inventory_uom_qty'];
                    $inspectionDetail->accepted_inv_uom_id = $inspectionItem['accepted_inventory_uom_id'];
                    $inspectionDetail->accepted_inv_uom_code = $inspectionItem['accepted_inventory_uom_code'];
                    $inspectionDetail->accepted_inv_uom_qty = $inspectionItem['accepted_inventory_uom_qty'];
                    $inspectionDetail->rejected_inv_uom_id = $inspectionItem['rejected_inventory_uom_id'];
                    $inspectionDetail->rejected_inv_uom_code = $inspectionItem['rejected_inventory_uom_code'];
                    $inspectionDetail->rejected_inv_uom_qty = $inspectionItem['rejected_inventory_uom_qty'];
                    $inspectionDetail->remark = $inspectionItem['remark'];
                    $inspectionDetail->save();

                    #Save component Attr
                    if ($isNewItem && $inspectionDetail->id) {
                        InspectionItemAttribute::where('detail_id', $inspectionDetail->id)
                            ->delete();
                    }
                    foreach($inspectionDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $inspAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $inspAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];

                            $inspAttr = InspectionItemAttribute::firstOrNew([
                                'header_id' => $inspection->id,
                                'detail_id' => $inspectionDetail->id,
                                'item_attribute_id' => $itemAttribute->id
                            ]);
                            // $mrnAttr = MrnAttribute::find($mrnAttrId) ?? new MrnAttribute;
                            $inspAttr->item_code = $component['item_code'] ?? null;
                            $inspAttr->attr_name = $itemAttribute->attribute_group_id;
                            $inspAttr->attr_value = $inspAttrName ?? null;
                            $inspAttr->save();
                        }
                    }

                    #Save item checklists
                    if (!empty($component['inspectionData'])) {
                        $itemChecklists = is_string($component['inspectionData'])
                            ? json_decode($component['inspectionData'], true)
                            : $component['inspectionData'];
                        if (is_array($itemChecklists)) {
                            $inspChecklist = InspectionService::manageChecklistData($itemChecklists, $inspection, $inspectionDetail);
                            if ($inspChecklist['status'] == 'error') {
                                \DB::rollBack();
                                return response()->json([
                                    'message' => $inspChecklist['message'],
                                    'error' => ''
                                ], 422);
                            }
                        } else {
                            \Log::warning("Invalid JSON for itemChecklists: " . print_r($component['inspectionData'], true));
                        }
                    }

                    #Save batch details
                    if (!empty($component['batch_details'])) {
                        $batchDetails = is_string($component['batch_details'])
                            ? json_decode($component['batch_details'], true)
                            : $component['batch_details'];

                        if (is_array(value: $batchDetails)) {
                            $inspBatchDetail = InspectionService::manageBatchDetails($batchDetails, $inspection, $inspectionDetail);
                            if ($inspBatchDetail['status'] == 'error') {
                                \DB::rollBack();
                                return response()->json([
                                    'message' => $inspBatchDetail['message'],
                                    'error' => ''
                                ], 422);
                            }
                        } else {
                            \DB::rollBack();
                            return response()->json(['message' => 'Invalid JSON for batch details.'], 422);
                        }
                    }

                    #Save item checklists
                    if (!empty($component['inspectionData'])) {
                        $itemChecklists = is_string($component['inspectionData'])
                            ? json_decode($component['inspectionData'], true)
                            : $component['inspectionData'];

                        if (is_array($itemChecklists)) {
                            foreach ($itemChecklists as $i => $val) {
                                $inspChecklist = InspChecklist::find($val['insp_checklist_id']) ?? new InspChecklist();
                                $inspChecklist->header_id = $inspection->id;
                                $inspChecklist->detail_id = $inspectionDetail->id;
                                $inspChecklist->item_id = $inspectionDetail->item_id;
                                $inspChecklist->checklist_id = $val['checkList_id'];
                                $inspChecklist->checklist_name = $val['checkList_name'];
                                $inspChecklist->checklist_detail_id = $val['detail_id'];
                                $inspChecklist->name = $val['parameter_name'];
                                $inspChecklist->value = $val['parameter_value'];
                                $inspChecklist->result = $val['result'];
                                $inspChecklist->save();

                            }
                        } else {
                            \Log::warning("Invalid JSON for itemChecklists: " . print_r($component['inspectionData'], true));
                        }
                    }
                }
            } else {
                if($request->document_status == ConstantHelper::SUBMITTED) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
                } else{}
            }

            /*Store currency data*/
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($inspection->vendor->currency_id, $inspection->document_date);

            $inspection->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            $inspection->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            $inspection->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            $inspection->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            $inspection->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            $inspection->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            $inspection->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            $inspection->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            $inspection->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $inspection->save();

            /*Create document submit log*/
            $bookId = $inspection->book_id;
            $docId = $inspection->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $inspection->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $inspection->approval_level ?? 1;
            $modelName = get_class($inspection);
            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                //*amendmemnt document log*/
                $revisionNumber = $inspection->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, $inspection->total_amount, $modelName);
                $inspection->revision_number = $revisionNumber;
                $inspection->approval_level = 1;
                $inspection->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ?? $inspection->document_status;
                $inspection->document_status = $amendAfterStatus;
                $inspection->save();

            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $inspection->revision_number ?? 0;
                    $actionType = 'submit';
                    $totalValue = $inspection->total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);

                    $document_status = $approveDocument['approvalStatus'] ?? $inspection->document_status;
                    $inspection->document_status = $document_status;
                } else {
                    $inspection->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }

            /*Inspection Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $inspection->uploadDocuments($request->file('attachment'), 'pb', false);
            }

            $inspection->save();
            if(in_array($inspection->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)){
                $updateMrn = InspectionHelper::updateMrnDetail($inspection);
                if($updateMrn['status'] == 'error') {
                    \DB::rollBack();
                    return response()->json([
                        'message' => $updateMrn['message'],
                        'error' => ''
                    ], 422);
                }
            }

            $redirectUrl = '';
            if(($inspection->document_status == ConstantHelper::POSTED)) {
                $parentUrl = request() -> segments()[0];
                $redirectUrl = url($parentUrl. '/' . $inspection->id . '/pdf');
            }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $inspection,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            // dd($e->getMessage(), $e->getLine());
            \DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage() . ' on line ' . $e->getLine(),
            ], 500);
        }
    }

    public function addItemRow(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $item = json_decode($request->item, true) ?? [];
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
        $html = view('procurement.inspection.partials.item-row', compact(['rowCount', 'locations']))->render();
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
            'procurement.inspection.partials.mrn-item-row',
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
        $inspectionDetailId = $request->detail_id ?? null;
        $itemAttIds = [];
        if ($inspectionDetailId) {
            $inspectionDetail = InspectionDetail::find($inspectionDetailId);
            if ($inspectionDetail) {
                $itemAttIds = $inspectionDetail->attributes()->pluck('item_attribute_id')->toArray();
            }
        }
        $itemAttributes = collect();
        if (count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id', $itemAttIds)->get();
        } else {
            $itemAttributes = $item?->itemAttributes;
        }
        $html = view('procurement.inspection.partials.comp-attribute', compact('item', 'attributeGroups', 'rowCount', 'selectedAttr'))->render();
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
        $html = view('procurement.inspection.partials.add-disc-row', compact('tblRowCount', 'rowCount', 'disName', 'disAmount', 'disPerc'))->render();
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
            $taxDetails = TaxHelper::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType,$document_date);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = floatval($request->price) ?? 0;
            $html = view('procurement.inspection.partials.item-tax', compact('taxDetails', 'rowCount', 'itemPrice'))->render();
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
            'mrn' => $typeId,
            default => $vendorId,
        };

        $typeData = match ($type) {
            'mrn' => MrnHeader::withDefaultGroupCompanyOrg()
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
            'mrn' => $typeData?->latestBillingAddress() ?? $typeData?->bill_address,
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
            $html = view('procurement.inspection.partials.edit-address-modal', compact('addresses', 'selectedAddress'))->render();
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
        $detailedStocks = InventoryHelper::fetchStockSummary($itemId, $selectedAttr,  $uomId, $qty, $storeId, $subStoreId);

        $html = view(
            'procurement.inspection.partials.comp-item-detail',
            compact(
                'item',
                'mrn',
                'qty',
                'poItem',
                'remark',
                'uomName',
                'selectedAttr',
                'specifications',
                'totalStockData',
                'detailedStocks'
            )
        )
        ->render();
        return response()->json(['data' => ['html' => $html, 'detailedStocks' => $detailedStocks], 'status' => 200, 'message' => 'fetched.']);
    }

    # Validate Order Qty For Backend
    private static function validateQuantityBackend($component, $refType)
    {
        $inputData = [
            'item_id'            => $component['item_id'] ?? null,
            'mrn_header_id'      => $component['mrn_header_id'] ?? null,
            'mrn_detail_id'      => $component['mrn_detail_id'] ?? null,
            'inspection_dtl_id'  => $component['inspection_dtl_id'] ?? null,
            'qty'                => $component['order_qty'] ?? null,
            'accepted_qty'       => $component['accepted_qty'] ?? null,
            'rejected_qty'       => $component['rejected_qty'] ?? null,
            'type'               => $refType ?? '',
        ];

        $checkService = new CheckAndUpdateService();
        $data = $checkService->validateOrderQuantity($inputData);
        return $data;
    }

    // Validate Order Qty For Frontend
    public function validateQuantity(Request $request)
    {
        $inputData = [
            'item_id'            => $request->item_id,
            'mrn_header_id'      => $request->mrn_header_id,
            'mrn_detail_id'      => $request->mrn_detail_id,
            'inspection_dtl_id'  => $request->inspection_dtl_id,
            'qty'                => $request->qty,
            'accepted_qty'       => $component['accepted_qty'] ?? null,
            'rejected_qty'       => $component['rejected_qty'] ?? null,
            'type'               => $request->type,
        ];
        $checkService = new CheckAndUpdateService();
        $data = $checkService->validateOrderQuantity($inputData);
        if ($data['status'] === 'success') {
            return response()->json(['message' => $data['message'], 'status' => 200, 'order_qty' => $data['order_qty']['order_qty'] ?? 0.00]);
        } else {
            return response()->json(['message' => $data['message'], 'status' => 422, 'order_qty' => $data['order_qty']['order_qty'] ?? 0.00]);
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
        $inspection = InspectionHeader::with(['vendor', 'currency', 'items', 'book'])
            ->findOrFail($id);


        $shippingAddress = $inspection->shippingAddress;
        $billingAddress = $inspection->billingAddress;

        $totalItemValue = $inspection->total_item_amount ?? 0.00;
        $totalDiscount = $inspection->total_discount ?? 0.00;
        $totalTaxes = $inspection->total_taxes ?? 0.00;
        $totalTaxableValue = ($totalItemValue - $totalDiscount);
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalExpense = $inspection->expense_amount ?? 0.00;
        $totalAmount = ($totalAfterTax + $totalExpense);
        $amountInWords = NumberHelper::convertAmountToWords($inspection->total_amount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$inspection->document_status] ?? '';
        $sellerShippingAddress = $inspection->latestShippingAddress();
        $sellerBillingAddress = $inspection->latestBillingAddress();
        $buyerAddress = $inspection?->erpStore?->address;

        $pdf = PDF::loadView(
            'pdf.inspection',
            [
                'user' => $user,
                'inspection' => $inspection,
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
                'sellerShippingAddress' => $sellerShippingAddress,
                'sellerBillingAddress' => $sellerBillingAddress,
                'buyerAddress' => $buyerAddress
            ]
        );

        $fileName = 'Inspection-' . date('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    # Submit Amendment
    public function amendmentSubmit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Header History
            $inspectionHeader = InspectionHeader::find($id);
            if (!$inspectionHeader) {
                return response()->json(['error' => 'Inspection Header not found'], 404);
            }
            $inspectionHeaderData = $inspectionHeader->toArray();
            unset($inspectionHeaderData['id']); // You might want to remove the primary key, 'id'
            // $inspectionHeaderData['header_id'] = $inspectionHeader->id;
            $inspectionHeaderData['source_id'] = $inspectionHeader->id;
            $headerHistory = InspectionHeaderHistory::create($inspectionHeaderData);
            $headerHistoryId = $headerHistory->id;

            // Detail History
            $inspectionDetails = InspectionDetail::where('header_id', $inspectionHeader->id)->get();
            if (!empty($inspectionDetails)) {
                foreach ($inspectionDetails as $key => $detail) {
                    $inspectionDetailData = $detail->toArray();
                    unset($inspectionDetailData['id']); // You might want to remove the primary key, 'id'
                    // $inspectionDetailData['detail_id'] = $detail->id;
                    $inspectionDetailData['source_id'] = $detail->id;
                    $inspectionDetailData['header_id'] = $headerHistoryId;
                    $detailHistory = InspectionDetailHistory::create($inspectionDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $inspectionAttributes = InspectionItemAttribute::where('header_id', $inspectionHeader->id)
                        ->where('detail_id', $detail->id)
                        ->get();
                    if (!empty($inspectionAttributes)) {
                        foreach ($inspectionAttributes as $key1 => $attribute) {
                            $inspectionAttributeData = $attribute->toArray();
                            unset($inspectionAttributeData['id']); // You might want to remove the primary key, 'id'
                            $inspectionAttributeData['source_id'] = $attribute->id;
                            $inspectionAttributeData['attribute_id'] = $attribute->id;
                            $inspectionAttributeData['header_id'] = $headerHistoryId;
                            $inspectionAttributeData['detail_id'] = $detailHistoryId;
                            $attributeHistory = InspectionItemAttributeHistory::create($inspectionAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Batch History
                    $inspectionBatch = InspBatchDetail::where('header_id', $inspectionHeader->id)
                        ->where('detail_id', $detail->id)
                        ->get();

                    if (!empty($inspectionBatch)) {
                        foreach ($inspectionBatch as $key4 => $batch) {
                            $batchData = $batch->toArray();
                            unset($batchData['id']); // You might want to remove the primary key, 'id'
                            $batchData['source_id'] = $batch->id;
                            $batchData['header_id'] = $headerHistoryId;
                            $batchData['detail_id'] = $detailHistoryId;
                            $batchData['batch_detail_id'] = $batch->batch_detail_id;
                            $batchData['item_id'] = $batch->item_id;
                            $batchData['batch_number'] = $batch->batch_number;
                            $batchData['manufacturing_year'] = $batch->manufacturing_year;
                            $batchData['expiry_date'] = $batch->expiry_date;
                            $batchData['quantity'] = $batch->quantity;
                            $batchData['inventory_uom_qty'] = $batch->inventory_uom_qty;
                            $batchData['inspection_qty'] = $batch->inspection_qty;
                            $batchData['inspection_inv_uom_qty'] = $batch->inspection_inv_uom_qty;
                            $batchData['accepted_qty'] = $batch->accepted_qty;
                            $batchData['accepted_inv_uom_qty'] = $batch->accepted_inv_uom_qty;
                            $batchData['rejected_qty'] = $batch->rejected_qty;
                            $batchData['rejected_inv_uom_qty'] = $batch->rejected_inv_uom_qty;
                            $batchDataHistory = InspBatchDetailHistory::create($batchData);
                            $batchDataId = $batchDataHistory->id;
                        }
                    }

                    // CheckList History
                    $inspectionCheck = InspChecklist::where('header_id', $inspectionHeader->id)
                        ->where('detail_id', $detail->id)
                        ->get();

                    if (!empty($inspectionCheck)) {
                        foreach ($inspectionCheck as $key4 => $checkList) {
                            $checkListData = $checkList->toArray();
                            unset($checkListData['id']); // You might want to remove the primary key, 'id'
                            $checkListData['header_id'] = $headerHistoryId;
                            $checkListData['detail_id'] = $detailHistoryId;
                            $checkListData['item_id'] = $checkList->item_id;
                            $checkListData['checklist_id'] = $checkList->checklist_id;
                            $checkListData['checklist_name'] = $checkList->checklist_name;
                            $checkListData['checklist_detail_id'] = $checkList->checklist_detail_id;
                            $checkListData['name'] = $checkList->name;
                            $checkListData['value'] = $checkList->value;
                            $checkListData['type'] = $checkList->type;
                            $checkListData['result'] = $checkList->result;
                            $checkListDataHistory = InspChecklistHistory::create($checkListData);
                            $checkListDataId = $checkListDataHistory->id;
                        }
                    }
                }
            }

            $randNo = rand(10000, 99999);

            $revisionNumber = "Inspection" . $randNo;
            $inspectionHeader->revision_number += 1;
            // $inspectionHeader->document_status = "draft";
            // $inspectionHeader->save();

            /*Create document submit log*/
            if ($inspectionHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $inspectionHeader->series_id;
                $docId = $inspectionHeader->id;
                $remarks = $inspectionHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $inspectionHeader->approval_level ?? 1;
                $revisionNumber = $inspectionHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType);
                $inspectionHeader->document_status = $approveDocument['approvalStatus'];
            }
            $inspectionHeader->save();

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $inspectionHeader,
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

    # Get MRN Item List
    public function getMrn(Request $request)
    {
        $storeId = $request->store_id ?? null;
        $query = $this->buildMrnQuery($request);
        return DataTables::of($query)
        ->addColumn('select_checkbox', fn($row) => app(\App\View\Components\Inspection\CheckBox::class, ['row' => $row])->resolveView()->render())
        ->addColumn('vendor', fn($row) => $row?->mrnHeader?->vendor?->company_name ?? 'NA')
        ->addColumn('doc_no', fn($row) => ($row?->mrnHeader?->book?->book_code ?? 'NA') . ' - ' . ($row?->mrnHeader?->document_number ?? 'NA'))
        ->addColumn('doc_date', fn($row) => $row?->mrnHeader?->getFormattedDate('document_date') ?? '')
        ->addColumn('item_name', fn($row) => $row?->item?->item_name ?? '')
        ->addColumn('item_code', fn($row) => $row?->item?->item_code ?? '')
        ->addColumn('attributes', fn($row) => app(\App\View\Components\Inspection\Attribute::class, ['row' => $row])->resolveView()->render())
        // ->addColumn('attributes', function ($row) {
        //     return $row?->attributes->map(function ($attr) {
        //         return "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->headerAttribute->name}</strong>: {$attr->headerAttributeValue->value}</span>";
        //     })->implode(' ');
        // })
        ->addColumn('uom', fn($row) => $row?->uom?->name ?? '')
        ->addColumn('order_qty', fn($row) => number_format(($row?->order_qty),2) ?? '')
        ->addColumn('inspection_qty', fn($row) => number_format(($row?->inspection_qty),2) ?? '')
        ->addColumn('balance_qty', fn($row) => number_format(($row?->order_qty - $row?->inspection_qty),2) ?? '')
        ->addColumn('remark', fn($row) => $row?->remark ?? '')
        ->rawColumns([
            'doc_no',
            'doc_date',
            'item_code',
            'item_name',
            'attributes',
            'uom',
            'remark',
            'select_checkbox'
            ])
        ->make(true);
    }

    # This for both bulk and single mrn
    protected function buildMrnQuery(Request $request)
    {
        $seriesId = $request->series_id ?? null;
        $mrnDocNumber = $request->document_number ?? null;
        $storeId = $request->store_id ?? null;
        $subStoreId = $request->sub_store_id ?? null;
        $vendorId = $request->vendor_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $soId= $request->so_id ?? null;
        $detailsIds = $request->details_ids ?? '';
        $headerId = $request->header_id ?? '';
        $mrnItems = null;

        if (is_string($detailsIds)) {
            $detailsIds = array_filter(explode(',', $detailsIds));
        }

        $decoded = urldecode(urldecode($request->selected_mrn_ids));
        $selected_mrn_ids = json_decode($decoded, true) ?? [];

        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $selectColumn = ['id','mrn_header_id','so_id','item_id','item_code','item_name','uom_id','uom_code','order_qty','inspection_qty','remark'];
        $mrnItems = MrnDetail::select($selectColumn)
            ->where('is_inspection', 1)
            ->where(function($query) use ($seriesId,$applicableBookIds,$vendorId, $selected_mrn_ids, $itemSearch,$storeId,$subStoreId, $soId, $mrnDocNumber) {
            if(count($selected_mrn_ids)) {
                $query->whereNotIn('id',$selected_mrn_ids);
            }
            $query->whereHas('mrnHeader', function($mrnHeader) use ($seriesId,$applicableBookIds, $storeId,$subStoreId,$mrnDocNumber, $vendorId) {
                $mrnHeader->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
                if(count($applicableBookIds)) {
                    $mrnHeader->whereIn('book_id',$applicableBookIds);
                }
                if($storeId) {
                    $mrnHeader->where('store_id', $storeId);
                }
                if($mrnDocNumber) {
                    $mrnHeader->where('id', $mrnDocNumber);
                }
                if ($vendorId) {
                    $mrnHeader->where('vendor_id', $vendorId);
                }
            });
            if($soId) {
                $query->where('so_id', $soId);
            }
            if ($itemSearch) {
                $query->whereHas('item', function ($query) use ($itemSearch) {
                    $query->searchByKeywords($itemSearch);
                });
            }
            $query->whereRaw('order_qty > inspection_qty');
        });

        if ($request->type === 'create' && count($selected_mrn_ids)) {
            $mrnItems->whereNotIn('erp_mrn_details.id', $selected_mrn_ids);
        } elseif ($request->type === 'edit') {
            if (!empty($headerIds)) {
                $mrnItems->whereIn('erp_mrn_details.mrn_header_id', $headerIds);
            }

            if (!empty($detailsIds)) {
                $mrnItems->whereNotIn('erp_mrn_details.id', $detailsIds);
            }

            if (!empty($selected_po_ids)) {
                $mrnItems->whereNotIn('erp_mrn_details.id', $selected_po_ids);
            }
        }

        return $mrnItems;
    }

    # Process Mrn Item
    public function processMrnItem(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $ids = json_decode($request->ids, true) ?? [];
        $vendor = null;
        $tableRowCount = $request->tableRowCount ?: 0;

        $mrnItems = MrnDetail::whereIn('id', $ids)
            ->where('is_inspection', 1)
            ->get();
        $uniqueMrnIds = MrnDetail::whereIn('id', $ids)
            ->where('is_inspection', 1)
            ->distinct()
            ->pluck('mrn_header_id')
            ->toArray();
        if(count($uniqueMrnIds) > 1) {
            return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => "One time inspection create from one MRN."]);
        }
        $mrnHeaders = MrnHeader::whereIn('id', $uniqueMrnIds)->get();
        $mrnHeader = MrnHeader::whereIn('id', $uniqueMrnIds)->first();

        $vendorId = $mrnHeaders->pluck('vendor_id')->unique();
        if ($vendorId->count() > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "You can not select multiple vendors of MRN items at a time."
            ]);
        } else {
            $vendor = Vendor::find($vendorId->first());
        }
        $html = view('procurement.inspection.partials.mrn-item-row',
        [
                'mrnItems' => $mrnItems,
                'tableRowCount' => $tableRowCount
            ]
        )
        ->render();

        return response()->json(
            [
                'data' => [
                    'pos' => $html,
                    'vendor' => $vendor,
                    'mrnHeader' => $mrnHeader
                ],
                'status' => 200,
                'message' => "fetched!"
            ]
        );
    }

    // Revoke Document
    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $mrn = InspectionHeader::find($request->id);
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
        } catch(\Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex -> getMessage());
        }
    }

    public function prMail(Request $request)
    {
        $request->validate([
            'email_to'  => 'required|email',
        ], [
            'email_to.required' => 'Recipient email is required.',
            'email_to.email'    => 'Please enter a valid email address.',
        ]);
        $po = InspectionHeader::with(['vendor'])->find($request->id);
        $vendor = $po->vendor;
        $sendTo = $request->email_to ?? $vendor->email;
        $vendor->email = $sendTo;
        $title = "Purchase Return Generated";
        $pattern = "Purchase Return";
        $remarks = $request->remarks ?? null;
        $mail_from = '';
        $mail_from_name = '';
        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
        $bcc = null;
        $attachment = $request->file('attachments') ?? null;
        $name = $vendor->company_name; // Assuming vendor is already defined
        $viewLink = route('purchase-return.generate-pdf', ['id'=>$request->id]);
        $description = <<<HTML
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif;">
            <tr>
                <td>
                    <h2 style="color: #2c3e50;">Your Purchase Return </h2>
                    <p style="font-size: 16px; color: #555;">Dear {$name},</p>
                        <p style='font-size: 15px; color: #333;'>
                            {$remarks}
                        </p>
                    <p style="font-size: 15px; color: #333;">
                        Please click the button below to view or download your Purchase Return:
                    </p>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="{$viewLink}" target="_blank" style="background-color: #7415ae; color: #ffffff; padding: 12px 24px; border-radius: 5px; font-size: 16px; text-decoration: none; font-weight: bold;">
                            Purchase Return
                        </a>
                    </p>
                </td>
            </tr>
        </table>
        HTML;
        self::sendMail($vendor,$title,$description,$cc,$bcc,$attachment,$mail_from,$mail_from_name);
    }
    public function sendMail($receiver, $title, $description, $cc = null, $bcc= null, $attachment, $mail_from=null, $mail_from_name=null)
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

    // Purchase Return Report
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
        $MRNHeaderIds = InspectionHeader::withDefaultGroupCompanyOrg()
                            ->distinct()
                            ->pluck('mrn_header_id');
        $MRNHeaders = MrnHeader::whereIn('id', $MRNHeaderIds)->get();
        $soIds = InspectionDetail::whereHas('header', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->distinct()
                ->pluck('so_id');

        $so = ErpSaleOrder::whereIn('id', $soIds)->get();
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;
        return view('procurement.inspection.detail_report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups', 'so', 'MRNHeaders', 'statusCss'));
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

        $query = InspectionHeader::query()
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

            $fileName = 'purchase-return.xlsx';
            $filePath = storage_path('app/public/purchase-return/' . $fileName);
            $directoryPath = storage_path('app/public/purchase-return');
            if($formattedstartDate && $formattedendDate)
            {
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Purchase Return Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else{
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Purchase Return Report' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }

            $remainingSpaces = $blankSpaces - count($filters) + 1;
            $filterHeader = array_merge($filters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new PurchaseReturnExport($customHeader, $filterHeader, $headers, $data), \Maatwebsite\Excel\Excel::XLSX);

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
                $title = "Purchase Return Report Generated";
                $heading = "Purchase Return Report";

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
                                We hope this email finds you well. Please find your purchase return report attached below.
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

    public function purchaseReturnReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $pathUrl = route('purchase-return.index');
        $orderType = ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS;
        $purchaseReturns = InspectionHeader::with(['items'])
            // ->where('document_type', $orderType)
            ->bookViewAccess($pathUrl)
            ->withDefaultGroupCompanyOrg()
            ->withDraftListingLogic()
            ->orderByDesc('id');

        // Vendor Filter
        $purchaseReturns = $purchaseReturns->when($request->vendor, function ($vendorQuery) use ($request) {
            $vendorQuery->where('vendor_id', $request->vendor);
        });

        // PO No Filter
        $purchaseReturns = $purchaseReturns->when($request->mrn_no, function ($mrnQuery) use ($request) {
            $mrnQuery->where('mrn_header_id', $request->mrn_no);
        });

        // Document Status Filter
        $purchaseReturns = $purchaseReturns->when($request->status, function ($docStatusQuery) use ($request) {
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
        $purchaseReturns = $purchaseReturns->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
            $dateRanges = explode('to', $dateRange);
            if (count($dateRanges) == 2) {
                $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                $toDate = Carbon::parse(trim($dateRanges[1]))->format('Y-m-d');
                $dateRangeQuery->whereDate('document_date', ">=", $fromDate)->where('document_date', '<=', $toDate);
            }
        });

        // Item Id Filter
        // $purchaseReturns = $purchaseReturns->when($request->item_id, function ($itemQuery) use ($request) {
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

        $purchaseReturns->with([
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
        ])
        ->where('organization_id', $user->organization_id);

        $purchaseReturns = $purchaseReturns->get();
        $processedPurchaseReturns = collect([]);

        foreach ($purchaseReturns as $pr) {
            foreach ($pr->items as $prItem) {
                $reportRow = new stdClass();

                // Header Details
                $header = $prItem->header;
                $total_item_value = (($prItem?->rate ?? 0.00) * ($prItem?->accepted_qty ?? 0.00)) - ($prItem?->discount_amount ?? 0.00);
                $reportRow->id = $prItem->id;
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
                $reportRow->category_name = $prItem->item ?->category ?-> name;
                $reportRow->sub_category_name = $prItem->item ?->category ?-> name;
                $reportRow->item_type = $prItem->item ?->type;
                $reportRow->sub_type = null;
                $reportRow->item_name = $prItem->item ?->item_name;
                $reportRow->item_code = $prItem->item ?->item_code;
                $reportRow->qty_return_type = $header ->qty_return_type;

                // Amount Details
                $reportRow->receipt_qty = number_format($prItem->order_qty, 2);
                $reportRow->accepted_qty = number_format($prItem->accepted_qty, 2);
                $reportRow->rejected_qty = number_format($prItem->rejected_qty, 2);
                $reportRow->pr_qty = number_format($prItem->pr_qty, 2);
                $reportRow->pr_rejected_qty = number_format($prItem->pr_rejected_qty, 2);
                $reportRow->purchase_bill_qty = number_format($prItem->purchase_bill_qty, 2);
                $reportRow->store_name = $prItem?->erpStore?->store_name;
                $reportRow->sub_store_name = $prItem?->subStore?->name;
                $reportRow->rate = number_format($prItem->rate);
                $reportRow->basic_value = number_format($prItem->basic_value, 2);
                $reportRow->item_discount = number_format($prItem->discount_amount, 2);
                $reportRow->header_discount = number_format($prItem->header_discount_amount, 2);
                $reportRow->item_amount = number_format($total_item_value, 2);

                // Attributes UI
                // $attributesUi = '';
                // if (count($prItem->item_attributes) > 0) {
                //     foreach ($prItem->item_attributes as $prAttribute) {
                //         $attrName = $prAttribute->attribute_name;
                //         $attrValue = $prAttribute->attribute_value;
                //         $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName : $attrValue </span>";
                //     }
                // } else {
                //     $attributesUi = 'N/A';
                // }
                // $reportRow->item_attributes = $attributesUi;

                // Document Status
                $reportRow->status = $header->document_status;
                $processedPurchaseReturns->push($reportRow);
            }
        }

        return DataTables::of($processedPurchaseReturns)
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

    // Validate Inspection Checklist
    // private static function validateInspectionCheckList(array $component)
    // {
    //     $inspectionJson = $component['inspectionData'] ?? null;
    //     if (!$inspectionJson) return self::notFoundResponse('Checklist must be filled for item'. $component['item_name']);

    //     $inspectionItems = json_decode($inspectionJson, true);
    //     if (!is_array($inspectionItems) || count($inspectionItems) === 0) {
    //         return self::notFoundResponse('Checklist must be filled for item'. $component['item_name']);
    //     }

    //     $grouped = collect($inspectionItems)->groupBy('detail_id');
    //     foreach ($grouped as $detailId => $entries) {
    //         $param = collect($entries)->firstWhere('type', 'parameter_name') ?? $entries->firstWhere('parameter_name');
    //         $result = collect($entries)->firstWhere('type', 'result') ?? $entries->firstWhere('result');
    //         if (empty($param['parameter_name'])) {
    //             return self::notFoundResponse('Parameter name missing in checklist for item'. $component['item_name']);
    //         }

    //         if (!isset($result['result']) || !in_array($result['result'], ['pass', 'fail'], true)) {
    //             return self::notFoundResponse('Pass/Fail (result) missing in checklist for item'. $component['item_name']);
    //         }
    //     }

    //     return null; // ✅ No issues found
    // }


    # Helper Functions for Responses
    private static function notFoundResponse(string $label)
    {
        return response()->json([
            'message' => $label,
        ], 422);
    }


}
