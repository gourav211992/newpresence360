<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\ErpTransporterRequest;
use App\Models\ErpTransporterRequestLocation;
use App\Models\ErpTransporterRequestMedia;
use App\Models\MailBox;
use App\Models\State;
use App\Models\Vendor;
use App\Services\Mailers\Mailer;
use Carbon\Carbon;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\DynamicFieldHelper;
use App\Models\ErpTrDynamicField;
use App\Helpers\NumberHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ServiceParametersHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PwoRequest;
use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Country;
use App\Models\ErpAttribute;
use App\Models\ErpVehicleType;
use App\Models\ErpItem;
use App\Models\ErpSaleOrder;
use App\Models\ErpSaleOrderItem;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\Unit;
use Arr;
use Auth;
use DB;
use FontLib\TrueType\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PDF;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendEmailJob;




class ErpTransporterRequestController extends Controller
{
    //
    public function index(Request $request)
    {

        $user = Helper::getAuthenticatedUser();
        $pathUrl = request()->segments()[0];
        $orderType = "Transporter-request";
        $redirectUrl = route('transporter.index');
        $createRoute = route('transporter.create');
        request()->merge(['type' => $orderType]);

        $typeName = "Transporter Request";

        if ($request->ajax()) {
            $TransporterRequests = ErpTransporterRequest::withDefaultGroupCompanyOrg()
                ->orderByDesc('id');
            return DataTables::of($TransporterRequests)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) use ($orderType) {
                    $displayStatus = '';
                    if (Carbon::parse($row->loading_date_time)->isPast()) {  // Ensure bid_end is a Carbon instance
                        $displayStatus = 'Closed';
                        $row->document_status = ConstantHelper::CLOSED;
                        $row->save();
                        $approveDocument = Helper::approveDocument($row->book_id, $row->document_number, 0, 'auto-Closed', [],$row->approval_level , 'auto-closed',get_class($row));
                    }
                    if ($row && in_array($row->document_status, [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])) {
                        if (Carbon::parse($row->bid_end)->isPast()) {  // Ensure bid_end is a Carbon instance
                            $displayStatus = 'Bid Closed';
                            $row->document_status = ConstantHelper::COMPLETED;
                            $row->save();
                            $approveDocument = Helper::approveDocument($row->book_id, $row->document_number, 0, 'auto-completed', [],$row->approval_level , 'auto-completed',get_class($row));
                        } else {
                            $displayStatus = 'Active';
                        }
                    }
                    elseif($row->document_status==ConstantHelper::COMPLETED){
                        $displayStatus = "Bid Closed";
                    }
                    else{
                        $displayStatus = ucfirst($row->document_status);
                    }
                    $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? ConstantHelper::DRAFT;
                    $editRoute = route('transporter.edit', ['id' => $row->id]);
                    return "
                        <div style='text-align:right;'>
                            <span class='badge rounded-pill $statusCss badgeborder-radius'>$displayStatus</span>
                            <div class='dropdown' style='display:inline;'>
                                <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                    <i data-feather='more-vertical'></i>
                                </button>
                                <div class='dropdown-menu dropdown-menu-end'>
                                    <a class='dropdown-item' href='" . $editRoute . "'>
                                        <i data-feather='edit-3' class='me-50'></i>
                                        <span>View/ Edit Detail</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    ";
                })
                ->addColumn('bid_type', function ($row) {
                    return 'Transporter Request';
                })
                // ->addColumn('trip_id', function ($row) {
                //     return $row->transporter_request_id;
                // })
                ->addColumn('book_name', function ($row) {
                    return $row->book_code ? $row->book_code : 'N/A';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? 'N/A';
                })
                ->editColumn('document_number', function ($row) {
                    return $row->document_number ?? 'N/A';
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->editColumn('reference_number', function ($row) {
                    return strval($row->reference_number);
                })
                ->addColumn('Transporter_id', function ($row) {
                    return $row->Transporter_id;
                })
                ->addColumn('bid_end', function ($row) {
                    return $row->bid_end;
                })
                ->addColumn('vehicle_type', function ($row) {
                    return $row?->vehicle?->vehicle_type;
                })
                ->addColumn('weight', function ($row) {
                    return $row->total_weight." ".$row->uom_code;
                })
                ->addColumn('pick_up', function ($row) {
                    $locations = $row->pickup()->pluck('location_name');
                    $locationIds = $row->pickup->pluck('id')->toArray();
                    $locationCount = count($locations);
                    $locationName = '';
                
                    if ($locationCount > 0) {
                        // Display the first location
                        $locationName .= '<span location-ids="' . htmlspecialchars(json_encode($locationIds), ENT_QUOTES, 'UTF-8') . '" onclick="showLocation(this, \'Pickup\')" class="badge rounded-pill badge-light-secondary badgeborder-radius">' . $locations[0] . '</span>';
                        
                        // If more than one location, display a badge with the remaining count
                        if ($locationCount > 1) {
                            $remainingCount = $locationCount - 1;
                            $locationName .= ' <span location-ids="' . htmlspecialchars(json_encode($locationIds), ENT_QUOTES, 'UTF-8') . '" onclick="showLocation(this, \'Pickup\')" class="badge rounded-pill badge-light-secondary badgeborder-radius">+' . $remainingCount . '</span>';
                        }
                    }
                
                    return $locationName;
                })
                ->addColumn('drop_off', function ($row) {
                    $locations = $row->dropoff()->pluck('location_name');
                    $locationIds = $row->dropoff->pluck('id')->toArray();
                    $locationCount = count($locations);
                    $locationName = '';
                
                    if ($locationCount > 0) {
                        // Display the first location
                        $locationName .= '<span location-ids="' . htmlspecialchars(json_encode($locationIds), ENT_QUOTES, 'UTF-8') . '" onclick="showLocation(this, \'Dropoff\')" class="badge rounded-pill badge-light-secondary badgeborder-radius">' . $locations[0] . '</span>';
                        
                        // If more than one location, display a badge with the remaining count
                        if ($locationCount > 1) {
                            $remainingCount = $locationCount - 1;
                            $locationName .= ' <span location-ids="' . htmlspecialchars(json_encode($locationIds), ENT_QUOTES, 'UTF-8') . '" onclick="showLocation(this, \'Dropoff\')" class="badge rounded-pill badge-light-secondary badgeborder-radius">+' . $remainingCount . '</span>';
                        }
                    }
                
                    return $locationName;
                })
                
                ->addColumn('loading_time', function ($row) {
                    return $row->loading_date_time;
                })
                // ->addColumn('unloading_time', function ($row) {
                //     return $row->unloading_time;
                // })
                ->addColumn('bid_submitted', function ($row) {
                    return " ".$row?->bids?->count()??"0";
                })
                
                ->rawColumns(['document_status', 'pick_up','drop_off'])
                ->make(true);
        }

        return view('transporter.index', [
            'typeName' => $typeName,
            'redirect_url' => $redirectUrl,
            'create_route' => $createRoute
        ]);
    }

    public function create(Request $request)
    {
        $parentURL = request()->segments()[0];
        $redirectUrl = route('transporter.index');
        $user = Helper::getAuthenticatedUser();
        $type = "Transporter-requests";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        $firstService = $servicesBooks['services'][0];
        $bookType = "tr";
        $typeName = "Transporter Request";
        $vendor = Vendor::withDefaultGroupCompanyOrg()->get();
        $vehicle = ErpVehicleType::where('status','active')->get();
        $uom = Unit::withDefaultGroupCompanyOrg()->get();
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $organization = Organization::where('id', $user->organization_id)->first();
        $books = Helper::getBookSeries($bookType)->get();
        $countries = Country::select('id AS value', 'name AS label')->where('status', ConstantHelper::ACTIVE)->get();
        $data = [
            'user' => $user,
            'services' => $servicesBooks['services'],
            'selectedService' => $firstService?->id ?? null,
            'vendors' => $vendor,
            'vehicle' => $vehicle,
            'weight' => $uom,
            'series' => $books,
            'countries' => $countries,
            'type' => $type,
            'stores' => $stores,
            'typeName' => $typeName,
            'redirect_url' => $redirectUrl,

        ];
        return view('transporter.create_edit', $data);
    }
    public function edit(Request $request, string $id)
    {
        try {

            $user = Helper::getAuthenticatedUser();

            $order = ErpTransporterRequest::with(['bid'])->find($id);
            $orderBids = $order->bids()->whereNot('bid_status','cancelled')->orderBy('bid_price')->get();

            $ogReturn = $order;
            $parentURL = request()->segments()[0];
            $redirectUrl = route('transporter.index');
            if (isset($order)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL, $order?->book?->service?->alias);
                $firstService = $servicesBooks['services'][0];
            }
            $vendor = Vendor::withDefaultGroupCompanyOrg()->get();
            $vehicle = ErpVehicleType::where('status','active')->get();
            $uom = Unit::withDefaultGroupCompanyOrg()->get();
            $revision_number = $order->revision_number ?? null;
            $countries = Country::select('id AS value', 'name AS label')->where('status', ConstantHelper::ACTIVE)->get();
            $totalWeight = $order->total_weight;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($order->book_id, $order->document_status, $order->id, $totalWeight, $order->approval_level, $order->created_by ?? 0, $userType['type'], $revision_number);
            $type = ConstantHelper::TR_SERVICE_ALIAS;
            $books = Helper::getBookSeries($type)->get();
            $revNo = $order->revision_number;
            if ($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $order->revision_number;
            }
            $docValue = $order?->total_amount ?? 0;
            $loadingCheck = Carbon::parse($order->loading_date_time)->setTimezone(config('app.timezone'))->isPast();
            $bidEndCheck = Carbon::parse($order->bid_end)->setTimezone(config('app.timezone'))->isPast();

            $approvalHistory = Helper::getApprovalHistory($order->book_id, $ogReturn->id, $revNo, $docValue);
            if (Carbon::parse($order->loading_date_time)->isPast() && !in_array($order->document_status, [ConstantHelper::CLOSED,])) {  // Ensure bid_end is a Carbon instance
                $displayStatus = 'Closed';
                $order->document_status = ConstantHelper::CLOSED;
                $order->save();
                $approveDocument = Helper::approveDocument($order->book_id, $order->document_number, 0, 'auto-closed', [],$order->approval_level , 'auto-closed',get_class($order));
            }
            if ($order && in_array($order->document_status, [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])) {
                if (Carbon::parse($order->bid_end)->isPast()) {  // Ensure bid_end is a Carbon instance
                    $displayStatus = 'Bid Closed';
                    $order->document_status = ConstantHelper::COMPLETED;
                    $order->save();
                    $approveDocument = Helper::approveDocument($order->book_id, $order->document_number, 0, 'auto-completed', [],$order->approval_level , 'auto-completed',get_class($order));

                } else {
                    $displayStatus = 'Active';
                }
            }
            else if($order->document_status==ConstantHelper::COMPLETED){
                $displayStatus = "Bid Closed";
            }
            else if(Carbon::parse($order->loading_date_time)->setTimezone(config('app.timezone'))->isPast()){
                $displayStatus = "Closed";
                $order->document_status = ConstantHelper::CLOSED;
                $order->save();
            }
            else{
                $displayStatus = ucfirst($order->document_status);
            }
            $dynamicFieldsUI = $order -> dynamicfieldsUi();

            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$order->document_status] ?? '';
            $typeName = "Transporter Requests";
            $pick_loc = $order->pickup;
            $drop_loc = $order->dropoff;
            // dd($order->location_address_details);
            $stores = InventoryHelper::getAccessibleLocations();
            $data = [
                'user' => $user,
                'services' => $servicesBooks['services'],
                'stores' => $stores,
                'vendors' => $vendor,
                'vehicle' => $vehicle,
                'weight' => $uom,
                'selectedService' => $firstService?->id ?? null,
                'series' => $books,
                'order' => $order,
                'orderbids' => $orderBids,
                'buttons' => $buttons,
                'countries' => $countries,
                'pick_loc' => $pick_loc,
                'drop_loc' => $drop_loc,
                'approvalHistory' => $approvalHistory,
                'type' => $type,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'display_status' => $displayStatus,
                'redirect_url' => $redirectUrl,
            ];
            return view('transporter.create_edit', $data);

        } catch (Exception $ex) {
            dd($ex);
        }
    }

    public function store(Request $request)
    {


        // dd($request->all());

        try {
            // dd($request->all());
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            $type = 'transporter-requests';
            $request->merge(['type' => $type]);

            //Auth credentials
            // $store = ErpStore::find($request->store_id);
            $organization = Organization::find($user->organization_id);
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            $transporters=Vendor::withDefaultGroupCompanyOrg()->pluck('id')->toArray();
            $documentNo = $request->document_no ?? null;
            if (!$request->tr_id) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_no;
                $regeneratedDocExist = ErpTransporterRequest::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
            $tr = null;
            if ($request->tr_id) { //Update
                $tr = ErpTransporterRequest::find($request->tr_id);
                $tr->document_date = $request->document_date;
                // $tr->reference_number = $request->reference_no;
                // $tr->consignee_name = $request->consignee_name;
                // $tr->consignment_no = $request->consignment_no;
                // $tr->vehicle_no = $request->vehicle_no;
                // $tr->transporter_name = $request->transporter_name;
                // $tr->eway_bill_no = $request->eway_bill_no;
                $tr->remarks = $request->remarks;
                $actionType = $request->action_type ?? '';
                //Amend backup
                if (($tr->document_status == ConstantHelper::APPROVED || $tr->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpTransporterRequest', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpTransportRequestLocation', 'relation_column' => 'transporter_request_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpTransportRequestBid', 'relation_column' => 'transporter_request_id'],
                    ];
                    // ['model_type' => 'sub_detail', 'model_name' => 'ErpPwoItemDelivery', 'relation_column' => 'pwo_item_id'],
                    $a = Helper::documentAmendment($revisionData, $tr->id);

                }
                // $keys = ['deletedLocationIds'];
                // $deletedData = [];

                // foreach ($keys as $key) {
                //     $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                // }
                // if (count($deletedData['deletedpwoItemIds'])) {
                //     $trItems = ErpPWOItem::with(['mapping'])->whereIn('id', $deletedData['deletedpwoItemIds'])->get();
                //     # all ted remove item level
                //     foreach ($trItems as $trItem) {
                //         // if (count($trItem->mapping)) {
                //         //     foreach($trItem->mapping as $refd){
                //         //         $refSoItem = ErpSoItem::find($refd->so_item_id);
                //         //         if (isset($refSoItem)) {
                //         //             $refSoItem->pwo_qty -= $refd->qty;
                //         //             $refSoItem->save();
                //         //         }
                //         //     }
                //         //     $trItem->mapping->delete();
                            
                //         // }
                //         #delivery remove
                //         $trItem->item_deliveries()->delete();
                //         # all attr remove
                //         $trItem->attributes()->delete();

                //         // $refereceItemIds = $trItem -> mapped_so_item_ids();
                //         // if (count($refereceItemIds) > 0) {
                //         //     foreach ($refereceItemIds as $referenceFromId) {
                //         //         $referenceItem = ErppwoItem::where('id', $referenceFromId) -> first();
                //         //         $existingMapping = ErpSoDnMapping::where([
                //         //             ['sale_order_id', $referenceItem -> sale_order_id],
                //         //             ['so_item_id', $referenceItem -> id],
                //         //             ['delivery_note_id', $tr -> id],
                //         //             ['dn_item_id', $trItem -> id],
                //         //         ]) -> first();
                //         //         if (isset($existingMapping)) {
                //         //             $referenceItem -> dnote_qty = $referenceItem -> dnote_qty - $trItem -> order_qty;
                //         //             if (!$invoiceRequiredParam) {
                //         //                 $referenceItem -> invoice_qty = $referenceItem -> invoice_qty - $trItem -> order_qty;
                //         //             }
                //         //             $referenceItem -> save();
                //         //             $existingMapping -> delete();
                //         //         }
                //         //     }
                //         // }

                //         $trItem->delete();
                //         // if ($trItem->so_item_id) {
                //         //     $trItem = ErpInvoiceItem::find($trItem->so_item_id);

                //         //     if (isset($trItem)) {
                //         //         $trItem->srn_qty -= $trItem->order_qty;
                //         //         if (!$trItem->header->invoice_required) {
                //         //             $trItem->srn_qty -= $trItem->order_qty;
                //         //         }
                //         //         $trItem->save();

                //         //         if ($trItem->so_item_id) {
                //         //             $trItem = ErpInvoiceItem::find($trItem->so_item_id);
                //         //             if (isset($trItem)) {
                //         //                 $trItem->srn_qty -= $trItem->order_qty;
                //         //                 if (!$trItem->header->invoice_required) {
                //         //                     $trItem->srn_qty -= $trItem->order_qty;
                //         //                 }
                //         //                 $trItem->save();
                //         //             }

                //         //         }
                //         //     }
                //         // }
                //     }
                // }
                // if (count($deletedData['deletedDelivery'])) {
                //     ErpPwoItemDelivery::whereIn('id', $deletedData['deletedDelivery'])->delete();
                // }

                // if (count($deletedData['deletedpLocationIds'])) {
                //     $files = ErpTransporterRequestLocation::whereIn('id', $deletedData['deletedpLocationIds'])->get();
                //     foreach ($files as $singleMedia) {
                //         $singleMedia->address->delete();
                //         $singleMedia->delete();
                //     }
                // }
                // if (count($deletedData['deleteddLocationIds'])) {
                //     $files = ErpTransporterRequestLocation::whereIn('id', $deletedData['deleteddLocationIds'])->get();
                //     foreach ($files as $singleMedia) {
                //         $singleMedia->address->delete();
                //         $singleMedia->delete();
                //     }
                // }
            }



            if (!$request->tr_id) {
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request->document_no;
                $regeneratedDocExist = ErpTransporterRequest::withDefaultGroupCompanyOrg()->where('book_id', $request->book_id)
                    ->where('document_number', $document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }
            }
            $tr = null;
            if ($request->tr_id) { //Update
                $tr = ErpTransporterRequest::find($request->tr_id);
                $tr->document_date = $request->document_date;
                // $tr->reference_number = $request->reference_no;
                $tr->remarks = $request->remarks;

                //Update all location
                $tr->pickup()->delete();
                $tr->dropoff()->delete();
                for ($i=1; $i <count($request->location_pick_up) ; $i++) { 
                    # 
                    $pickUpLocation = ErpStore::find($request->location_pick_up[$i]);
                    $locationAddress = $tr->location_address_details()->create([
                        'address' => $request->p_address_id[$i],
                        'country_id' => $request->p_country_id[$i],
                        'state_id' => $request->p_state_id[$i],
                        'city_id' => $request->p_city_id[$i],
                        'pincode' => $request->p_pin_code[$i],
                    ]);
                    $transporter_location = ErpTransporterRequestLocation::create(
                        [
                            'transporter_request_id' => $tr->id,
                            'address_id' => $locationAddress->id,
                            'location_id' => $pickUpLocation->id,
                            'location_name' => $pickUpLocation->store_code,
                            'location_type' =>"pick_up" ,
                            ]
                        );
                }
                for ($i=1; $i <count($request->location_drop) ; $i++) { 

                    $locationAddress = $tr->location_address_details()->create([
                        'address' => $request->d_address_id[$i],
                        'country_id' => $request->d_country_id[$i],
                        'state_id' => $request->d_state_id[$i],
                        'city_id' => $request->d_city_id[$i],
                        'pincode' => $request->d_pin_code[$i],
                    ]);
                    $transporter_location = ErpTransporterRequestLocation::create(
                        [
                            'transporter_request_id' => $tr->id,
                            'address_id' => $locationAddress->id,
                            'location_name' => $request->location_drop[$i],
                            'location_type' =>"drop_off" ,
                        ]
                    );
                }
                //Update all Item references
                // foreach ($tr->items as $item) {
                //     InventoryHelper::addReturnedStock($tr->id, $item->id, $item->item_id, 'return', 'receive');
                // if (($request -> type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $request -> type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS)) {
                // }
                // $item -> item_attributes() -> forceDelete();
                // $item -> discount_ted() -> forceDelete();
                // $item -> tax_ted() -> forceDelete();
                // $item -> item_locations() -> forceDelete();
                // $item -> forceDelete();
                // }
            } else { //Create
                // dd($request);
                $uom = Unit::find($request->uom_id);
                if (isset($request->transporter_ids)) {
                    $transporter_ids = array_values($request->transporter_ids);

                } else {
                    $transporter_ids = null;
                }

                // dd($transporter_ids);
                
                
                if(!$request->vehicle_type){
                    return response()->json([
                        'message' => "Please Select Vehicle Type",
                        'error' => "",
                    ], 422);
                }
                if(!$request->weight){
                    return response()->json([
                        'message' => "Please Enter Valid Weight",
                        'error' => "",
                    ], 422);
                }
                $tr = ErpTransporterRequest::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'document_number' => $request->document_no,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request->document_date,
                    'loading_date_time' => $request->loading_date,
                    // 'reference_number' => $request->reference_no,
                    // 'location_id' => $request->store_id ?? null,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => $request->remarks,
                    // 'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    // 'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    // 'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    // 'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    // 'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    // 'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    // 'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    // 'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    // 'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                    // 'total_return_value' => 0,
                    // 'total_discount_value' => 0,
                    // 'total_tax_value' => 0,
                    // 'total_expense_value' => 0,
                    'vehicle_type' => $request->vehicle_type,	
                    "total_weight"=>$request->weight,	
                    "uom_id"=>$request->uom_id,	
                    "uom_code"=>$uom->name,
                    "bid_end"=>$request->bid_end_date,
                    "transporter_ids" => !empty($transporter_ids) ? ($transporter_ids) : null,
                ]);
                //Location Address
                if(count($request->location_pick_up)>1)
                {
                    for ($i=1; $i <count($request->location_pick_up) ; $i++) { 
                        # 
                        $pickUpLocation = ErpStore::find($request->location_pick_up[$i]);
                        // if (!isset($pickUpLocation) || !isset($pickUpLocation->address)) {
                        //     DB::rollBack();
                        //     return response()->json([
                        //         'message' => 'Location Address not assigned',
                        //         'error' => ''
                        //     ], 422);
                        // }
                        $locationAddress = $tr->location_address_details()->create([
                            'address' => $request->p_address_id[$i],
                            'country_id' => $request->p_country_id[$i],
                            'state_id' => $request->p_state_id[$i],
                            'city_id' => $request->p_city_id[$i],
                            'type' => 'location',
                            'pincode' => $request->p_pin_code[$i],
                        ]);
                        $transporter_location = ErpTransporterRequestLocation::create(
                            [
                                'transporter_request_id' => $tr->id,
                                'address_id' => $locationAddress->id,
                                'location_id' => $pickUpLocation->id,
                                'location_name' => $pickUpLocation->store_code,
                                'location_type' =>"pick_up" ,
                                ]
                            );
                            // dd($pickUpLocation);
                    }
                }
                else
                {
                    return response()->json([
                        'message' => 'Pick Up Location Not Set',
                        'error' => "",
                    ], 422);
                }
                if(count($request->location_drop)>1)
                {
                    for ($i=1; $i <count($request->location_drop) ; $i++) { 
                        # 
                        $dropOffLocation = ErpStore::find($request->location_drop);
                        // if (!isset($dropOffLocation) || !isset($dropOffLocation->address)) {
                        //     DB::rollBack();
                        //     return response()->json([
                        //         'message' => 'Location Address not assigned',
                        //         'error' => ''
                        //     ], 422);
                        // }
                        $locationAddress = $tr->location_address_details()->create([
                            'address' => $request->d_address_id[$i],
                            'country_id' => $request->d_country_id[$i],
                            'state_id' => $request->d_state_id[$i],
                            'city_id' => $request->d_city_id[$i],
                            'type' => 'location',
                            'pincode' => $request->d_pin_code[$i],
                        ]);
                        $transporter_location = ErpTransporterRequestLocation::create(
                            [
                                'transporter_request_id' => $tr->id,
                                'address_id' => $locationAddress->id,
                                'location_name' => $request->location_drop[$i],
                                'location_type' =>"drop_off" ,
                            ]
                        );
                    }
                }
                else
                {
                    return response()->json([
                        'message' => 'Drop Off Location Not Set',
                        'error' => "",
                    ], 422);
                }
            }
            $tr->save();
                    //Media
                    if ($request->hasFile('attachments')) {
                        foreach ($request->file('attachments') as $singleFile) {
                            $mediaFiles = $tr->uploadDocuments($singleFile, 'transporter_request', false);
                        }
                    }

            if ($request->tr_id) { //Update condition
                $bookId = $tr->book_id;
                $docId = $tr->id;
                $amendRemarks = $request->amend_remarks ?? null;
                $remarks = $tr->remarks;
                $amendAttachments = $request->file('amend_attachments');
                $attachments = $request->file('attachment');
                $currentLevel = $tr->approval_level;
                $modelName = get_class($tr);
                $actionType = $request->action_type ?? "";
                
                if (($tr->document_status == ConstantHelper::APPROVED || $tr->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    //*amendmemnt document log*/
                    $revisionNumber = $tr->revision_number + 1;
                    $actionType = 'amendment';
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                    $tr->revision_number = $revisionNumber;
                    $tr->approval_level = 1;
                    $tr->revision_date = now();
                    $amendAfterStatus = $tr->document_status;
                    $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                    if (isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                        $totalWeight = $tr->grand_total_amount ?? 0;
                        $amendAfterStatus = Helper::checkApprovalRequired($request->book_id, $totalWeight);
                    } else {
                        $actionType = 'approve';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }
                    if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                        $actionType = 'submit';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }
                    $tr->document_status = $amendAfterStatus;
                    $tr->save();
                    
                } else {
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $revisionNumber = $tr->revision_number ?? 0;
                        $actionType = 'submit';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        
                        $totalWeight = $tr->grand_total_amount ?? 0;
                        $document_status = Helper::checkApprovalRequired($request->book_id, $totalWeight);
                        $tr->document_status = $document_status;
                    } else {
                        $tr->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                }
                
            } else { //Create condition
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $bookId = $tr->book_id;
                    $docId = $tr->id;
                    $remarks = $tr->remarks;
                    $attachments = $request->file('attachment');
                    $currentLevel = $tr->approval_level;
                    $revisionNumber = $tr->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $modelName = get_class($tr);
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                }
                
                if ($request->document_status == 'submitted') {
                    $totalWeight = $tr->total_amount ?? 0;
                    $document_status = Helper::checkApprovalRequired($request->book_id, $totalWeight);
                    $tr->document_status = $document_status;
                } else {
                    $tr->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
                if($tr->document_status && in_array($tr->document_status,[ConstantHelper::APPROVAL_NOT_REQUIRED,ConstantHelper::APPROVED])){
                    if ($transporter_ids) {
                        $vendors = Vendor::whereIn('id', $transporter_ids)->get(); // Keep as a collection
                    }
                    else{
                        $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
                    }
                    foreach ($vendors as $vendor) { 
                        $sendTo = $vendor->email;
                        $title = "New Transporter Request";
                        $bidLink = route('supplier.transporter.index',[$vendor->id]); // Generate route in PHP
                        $name = $vendor->company_name;
                        $mail_from = '';
                        $mail_from_name = '';
                        $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
                        $attachment = $request->file('attachments') ?? null;
                        $description = <<<HTML
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                            <tr>
                                <td align="left" style="padding: 10px 0;">
                                    <h2 style="color: #333;">New Transporter Request – Invitation to Bid</h2>
                                    <p>Dear {$name},</p>
                                    <p>A new transporter request has been created for delivery. As a valued logistics partner, we invite you to place your bid for it.</p>
                                    <p>Kindly submit your bid in a timely manner to be considered for this opportunity.</p>
                                    <p style="text-align: center;">
                                        <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                            Place Your Bid
                                        </a>
                                    </p>
                                    <p>If you have any questions or require further details, please do not hesitate to contact us.</p>
                                </td>
                            </tr>
                        </table>
                        HTML;
                        self::sendMail($vendor,$title,$description,$cc,$attachment,$mail_from,$mail_from_name);
                    }
                    $tr->save();
                }
            }

            //Images
            //Media
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $singleFile) {
                    $mediaFiles = $tr->uploadDocuments($singleFile, 'work_order', false);
                }
            }
            //Dynamic Fields
            $status = DynamicFieldHelper::saveDynamicFields(ErpTrDynamicField::class, $tr -> id, $request -> dynamic_field ?? []);
            if ($status && !$status['status'] ) {
                DB::rollBack();
                return response() -> json([
                    'message' => $status['message'],
                    'error' => ''
                ], 422);
            }
            $tr->save();
            DB::commit();
            $module = "Transporter Requests";
            return response()->json([
                'message' => $module . " created successfully",
                'redirect_url' => route('transporter.index', ['type' => $request->type ?? ConstantHelper::TR_SERVICE_ALIAS])
            ]);
        

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . $ex->getFile() . $ex->getLine(),
            ], 500);
        }
    }

    public function get_address(Request $request)
    {
        $store = ErpStore::find($request -> store_id);
        return response()->json([
            's_address' => $store->address,
            'address' => $store->address->address,
            'city' => $store->address->city->id,
            'pincode' => $store->address->pincode

        ]);
    }
    public function get_state(Request $request)
    {
        $states = State::where('country_id',$request -> country_id)->get();
        
        $statedata=[];
        foreach($states as $state){
            $statedata[]=['id'=>$state->id,'name'=>$state->name];
        }
        return response()->json([
            'states' => $statedata,
        ]);
    }
    public function get_city(Request $request)
    {
        $cities = City::where('state_id',$request -> state_id)->get();
        
        $citydata=[];
        foreach($cities as $city){
            $citydata[]=['id'=>$city->id,'name'=>$city->name];
        }
        return response()->json([
            'cities' => $citydata,
        ]);
    }
    
    public function closeBid(Request $request){
        // dd($request->all());
        try{

            $remarks = $request->remarks??"";
            $tr =ErpTransporterRequest::find($request->tr_id);
            $tr->document_status='closed';
            $tr->save();
            $approveDocument = Helper::approveDocument($tr->book_id, $tr->id, 0, $remarks, [], $tr->approval_level, 'bid-closed' , 0,get_class($tr));
            // $transporter_ids = json_decode($tr->transporter_ids);
            // if ($transporter_ids) {
            //     $vendors = Vendor::whereIn('id', $transporter_ids)->get();
            // }
            // else{
            //     $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
            // }
            // foreach ($vendors as $vendor) { 
            //     $sendTo = $vendor->email;
            //     $title = "Transporter Request Closed";
            //     $bid_name = $tr->document_number;
            //     $name = $vendor->company_name;
            //     $description = <<<HTML
            //     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
            //         <tr>
            //             <td align="left" style="padding: 10px 0;">
            //                 <h2 style="color: #333;">Bid Closed – Thank You for Your Participation</h2>
            //                 <p>Dear {$name},</p>
            //                 <p>We would like to inform you that the bid <strong>{$bid_name}</strong> has been officially closed.</p>
            //                 <p>We sincerely appreciate your participation and efforts in the bidding process.</p>
            //                 <p>Should you have any questions or require further information, please feel free to reach out to us.</p>
            //                 <p>We look forward to your participation in future opportunities.</p>
            //                 <p>Thank you for being a valued logistics partner.</p>
            //             </td>
            //         </tr>
            //     </table>
            //     HTML;
            //     self::sendMail($vendor,$title,$description);
            // }
            

            return response()->json([
                'message' => 'Bid Closed Successfully.',
                'title' =>'Success !',
                'type' => 'success'
            ], 200);
        }
        catch(Exception $ex){
            return response()->json([
                'message' => 'Some Error Occured.',
                'title' =>'Error !',
                'type' => 'error'
            ], 500);
        }
    }
    // public function sendBulkEmails($transporter_ids, $title, $description)
    // {
    //     $vendors = Vendor::whereIn('id', $transporter_ids)->get();

    //     $jobs = [];
    //     foreach ($vendors as $vendor) {
    //         $jobs[] = new SendEmailJob($vendor, $title, $description);
    //     }

    //     $batch = Bus::batch($jobs)
    //         ->then(function ($batch) {
    //             // All jobs were completed successfully
    //             \Log::info("All emails sent successfully!");
    //         })
    //         ->catch(function ($batch, Throwable $e) {
    //             // Handle batch failure
    //             \Log::error("Email batch failed: " . $e->getMessage());
    //         })
    //         ->finally(function ($batch) {
    //             // Always executed, whether successful or failed
    //             \Log::info("Email batch processing completed.");
    //         })
    //         ->dispatch();

    //     return response()->json([
    //         'message' => 'Email batch dispatched successfully!',
    //         'batch_id' => $batch->id,
    //     ]);
    // }

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
        // Check if receiver object exists and has an email
        // if (!$receiver || !isset($receiver->email)) {
        //     return response()->json([
        //         'message' => "Error: Receiver details are missing or invalid.",
        //         'title' =>'Error !',
        //         'type' => 'error'
        //     ], 500);
        // }

        // Extract and validate receiver details
        // $sendTo = trim($receiver->email);
        // $name = $receiver->company_name ?? $receiver->name ?? 'Valued Partner';

        // // Validate email
        // $validator = Validator::make(['email' => $sendTo], [
        //     'email' => 'required|email',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'message' => "Error: Invalid email address provided.",
        //         'title' =>'Error !',
        //         'type' => 'error'
        //     ], 500);
        // }

        // // Validate title and action
        // if (empty($title)) {
        //     return response()->json([
        //         'message' => "Error: Missing email title.",
        //         'title' =>'Error !',
        //         'type' => 'error'
        //     ], 500);
        // }

        // // Validate description
        // if (empty($description)) {
        //     return response()->json([
        //         'message' =>"Error: Email description is missing.",
        //         'title' =>'Error !',
        //         'type' => 'error'
        //     ], 500);
        // }
        // Prepare mail
        // $mailer = new Mailer;
        // $mailBox = new MailBox;
        // $mailBox->mail_to = $sendTo;
        // $mailBox->layout = "emails.template";
        // $mailBox->mail_body = json_encode([
        //     'title' => $title,
        //     'description' => $description,
        // ]);
        // $mailBox->subject = $title;
        // $mailer->emailTo($mailBox);

        // Send mail
        // try {
        //     return response()->json([
        //         'message' =>"Success: Email sent successfully to {$sendTo}",
        //         'title' =>'Success !',
        //         'type' => 'success'
        //     ], 200);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' =>"Error: Failed to send email. " . $e->getMessage(),
        //         'title' =>'Error !',
        //         'type' => 'error'
        //     ], 500);
        // }
    // }

    public function reOpenBid(Request $request){
        // dd($request->all());
        try{
            $remarks = $request->remarks??"";
            $tr =ErpTransporterRequest::find($request->tr_id);
            $tr->document_status='draft';
            $tr->bid_end = Carbon::now()->addHours(2);
            $tr->loading_date_time = Carbon::now()->addHours(4);
            $tr->save();
            $transporter_ids = $tr->transporter_ids;
            if ($transporter_ids) {
                $vendors = Vendor::whereIn('id', $transporter_ids)->get();
            }
            else{
                $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
            }
            foreach ($vendors as $vendor) { 
                $sendTo = $vendor->email;
                $title = "Transporter Request Reopened";
                $bidLink = route('supplier.transporter.index',[$vendor->id]); // Generate route in PHP
                $bid_name = $tr->document_number;
                $name = $vendor->company_name;
                $mail_from = '';
                $mail_from_name = '';
                $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
                $attachment = $request->file('attachments') ?? null;
                $description = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                    <tr>
                        <td align="left" style="padding: 10px 0;">
                            <h2 style="color: #333;">Bid Reopened – Invitation to Bid Again</h2>
                            <p>Dear {$name},</p>
                            <p>We would like to inform you that the bid <strong>{$bid_name}</strong> has been reopened.</p>
                            <p>As a valued logistics partner, we invite you to review the bid details and place your bid again.</p>
                            <p>Kindly submit your bid in a timely manner to be considered for this opportunity.</p>
                            <p style="text-align: center;">
                                <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                    Place Your Bid
                                </a>
                            </p>
                            <p>If you have any questions or require further details, please do not hesitate to contact us.</p>
                        </td>
                    </tr>
                </table>
                HTML;
                self::sendMail($vendor,$title,$description,$cc,$attachment,$mail_from,$mail_from_name);
            }
            
            $approveDocument = Helper::approveDocument($tr->book_id, $tr->id, 0, $remarks, [], $tr->approval_level, 'bid-reopened' , 0,get_class($tr));
            return response()->json([
                'message' => 'Bid Reopened Successfully.',
                'title' =>'Success !',
                'type' => 'success'
            ], 200);
        }
        catch(Exception $ex){
            return response()->json([
                'message' => 'Some Error Occured.',
                'title' =>'Error !',
                'type' => 'error'
            ], 500);
        }
    }
    
    public function get_locations(Request $request)
    {
        $loc = json_decode($request->location_ids);
        $location_data=[];
        foreach($loc as $id){
            $location = ErpTransporterRequestLocation::with(['address'])->find($id);
            $location_name = $location->location_name;
            $location_address = $location->address->getDisplayAddressAttribute(); 
            $location_data[]=[$location_name,$location_address];
            
        }
        return response()->json([
            'data' => $location_data
        ]);
    }


}
