<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Helpers\ItemHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ServiceParametersHelper;
use App\Http\Requests\RgrRequest;
use App\Models\Organization;
use App\Models\ErpRgr;
use App\Models\ErpRgrHistory;
use App\Models\ErpRgrItem;
use App\Models\ErpRgrItemAttribute;
use App\Models\ErpRgrMedia;
use App\Models\ErpRgrMediaHistory;
use App\Models\ErpPickupSchedule;
use App\Models\ErpPickupItem;
use App\Models\ErpPickupItemAttribute;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\ItemAttribute;
use App\Models\ErpRgrStoreMapping;
use App\Models\Item;
use App\Models\ErpStore;
use Yajra\DataTables\DataTables;
use App\Helpers\RGR\Constants as RGRConstant;
use App\Lib\Services\WHM\RGRJob;
use App\Models\Unit;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Facades\Storage;
use DB;
use Exception;
use App\Lib\Services\WHM\WhmJob;
use App\Models\WHM\ErpItemUniqueCode;

class RgrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
    {
        $parentUrl = request()->segments()[0];

        if ($request->ajax()) {
            $rgrs = ErpRgr::with(['book','store','items'])->latest();

            if ($request->filled('status')) {
                $rgrs->where('document_status', $request->status);
            }

            if ($request->filled('book_id')) {
                $rgrs->where('book_id', $request->book_id);
            }

            if ($request->filled('location_id')) {
                $rgrs->where('store_id', $request->location_id);
            }

            return DataTables::of($rgrs)
                ->addIndexColumn()
                ->addColumn('book_name', fn($row) => $row->book->book_code ?? 'N/A')
                ->addColumn('location', fn($row) => $row->store->store_name ?? 'N/A')
                ->editColumn('document_date', fn($row) => $row->getFormattedDate('document_date') ?? 'N/A')
                ->addColumn('items', function($row) {
                    $firstItem = $row->items[0]->item_name ?? '';
                    $count = $row->items->count();
                    return $count > 1
                        ? $firstItem . " <span class='badge rounded-pill badge-light-primary badgeborder-radius'>+" . ($count-1) . "</span>"
                        : $firstItem;
                })
                ->addColumn('action', function($row) {
                    $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary';
                    $displayStatus = $row->display_status ?? 'N/A'; 
                    return view('partials.action-dropdown', [
                        'row'           => $row,
                        'statusClass'   => $statusClass,
                        'displayStatus' => $displayStatus,  
                        'actions'       => [
                            [
                                'url'   => route('rgr.edit', $row->id),
                                'icon'  => 'edit-3',
                                'label' => 'View/Edit Detail',
                            ]
                        ]
                    ])->render();
                })
                ->rawColumns(['items','action'])
                ->make(true);
        }

         $servicesAliasParam =  RgrConstant::SERVICE_ALIAS; 
         $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
         $locations = InventoryHelper::getAccessibleLocations();
        return view('rgr.index', ['books' => $books,'locations'=>$locations]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $parentUrl = request()->segments()[0];
        $servicesAliasParam =  RgrConstant::SERVICE_ALIAS; 
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
        $locations = InventoryHelper::getAccessibleLocations();
        return view('rgr.create', [ 
            'books' => $books,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(RgrRequest $request)
    {
        DB::beginTransaction();
        try {
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->input('book_id'), $request->input('document_date'));

            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();

            $rgr = new ErpRgr;
            $rgr->organization_id = $organization->id;
            $rgr->group_id = $organization->group_id;
            $rgr->company_id = $organization->company_id;
            $rgr->book_id = $request->input('book_id');
            $rgr->book_code = $request->input('book_code');
            $rgr->store_id = $request->input('store_id');
            $rgr->store_name = $request->input('store_name');
            $rgr->pickup_schdule_id = $request->input('pickup_schedule_id');
            $rgr->pickup_schdule_no = $request->input('pickup_schedule_no');

            // 4. Generate Document Number
            $document_number = $request->input('document_number') ?? null;
            $numberPatternData = Helper::generateDocumentNumberNew($request->input('book_id'), $request->input('document_date'));

            if (!isset($numberPatternData)) {
                DB::rollBack();
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }

            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
            $regeneratedDocExist = ErpRgr::where('book_id', $request->input('book_id'))
                ->where('document_number', $document_number)
                ->first();

            if (isset($regeneratedDocExist)) {
                DB::rollBack();
                return response()->json([
                    'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                    'error' => "",
                ], 422);
            }

            $rgr->doc_number_type = $numberPatternData['type'];
            $rgr->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $rgr->doc_prefix = $numberPatternData['prefix'];
            $rgr->doc_suffix = $numberPatternData['suffix'];
            $rgr->doc_no = $numberPatternData['doc_no'];
            $rgr->document_number = $document_number;
            $rgr->document_date = $request->input('document_date') ?? now();
            $rgr->due_date = $request->input('due_date') ?? now();
            $rgr->revision_number = $request->input('revision_number') ?? 0;
            $rgr->reference_number = $request->input('reference_number') ?? '';
            $rgr->trip_no = $request->input('trip_no') ?? '';
            $rgr->vehicle_no = $request->input('vehicle_no') ?? '';
            $rgr->champ_name = $request->input('champ_name') ?? '';
            $rgr->remark = $request->input('remark') ?? '';
            $rgr->final_remark = $request->input('final_remark') ?? '';

            // 5. Save RGR Header
            $rgr->save();
             ErpPickupSchedule::where('id', $request->input('pickup_schedule_id'))
                ->update([
                    'rgr_id' => $rgr->id
                ]);
    
            // 6. Save RGR Items
         if ($request->has('rgr_items') && is_array($request->input('rgr_items'))) {
                foreach ($request->input('rgr_items') as $itemData) {

                    $item = Item::with('uom')->find($itemData['item_id']);
                    $inventoryUomQty = ItemHelper::convertToBaseUom( $item->id, $itemData['uom_id'] ?? 0, $itemData['qty'] ?? 0);
                    $subStoreId = null;
                    $categoryId = $itemData['category_id'] ?? $item->subcategory_id ?? null;
                    if ($request->input('store_id') && $categoryId) {
                        $storeMapping = ErpRgrStoreMapping::where('store_id', $request->input('store_id'))
                            ->where('category_id', $categoryId)
                            ->first();

                        $subStoreId = $storeMapping ? $storeMapping->sub_store_id : null;
                    }
                    $subStoreId = $storeMapping ? $storeMapping->sub_store_id : null;

                    $rgrItem = new ErpRgrItem;
                    $rgrItem->rgr_id = $rgr->id;
                    $rgrItem->item_id = $itemData['item_id'];
                    $rgrItem->hsn_id = $itemData['hsn_id'] ?? null;
                    $rgrItem->category_id = $itemData['category_id'] ?? null;
                    $rgrItem->sub_store_id = $subStoreId; 
                    $rgrItem->item_uid = $itemData['item_uid'] ?? '';
                    $rgrItem->item_code = $itemData['item_code'] ?? '';
                    $rgrItem->item_name = $itemData['item_name'] ?? '';
                    $rgrItem->uom_id = $itemData['uom_id'];
                    $rgrItem->uom_name = $itemData['uom_name'];
                    $rgrItem->qty = $itemData['qty'];
                    $rgrItem->remarks = $itemData['remarks'] ?? '';
                    $rgrItem->customer_id = $itemData['customer_id'] ?? null;
                    $rgrItem->customer_name = $itemData['customer_name'] ?? null;
                    $rgrItem->inventory_uom_id   = $item->uom?->id;
                    $rgrItem->inventory_uom_code = $item->uom?->name;
                    $rgrItem->inventory_uom_qty  = $inventoryUomQty;
                    $rgrItem->save();

                    # rgr iteam attrivute Save
                    $rgrItemAttributes = $itemData['rgr_item_attributes'] ?? [];
                    foreach ($rgrItemAttributes as $rgrItemAttribute) {
                        $rgrItemAttr = new ErpRgrItemAttribute;
                        $rgrItemAttr->rgr_id = $rgr->id;
                        $rgrItemAttr->rgr_item_id = $rgrItem->id;
                        $rgrItemAttr->item_attribute_id = $rgrItemAttribute['item_attribute_id'];
                        $rgrItemAttr->attribute_name = $rgrItemAttribute['attribute_name'];
                        $rgrItemAttr->attr_name = $rgrItemAttribute['attr_name'];
                        $rgrItemAttr->attribute_value = $rgrItemAttribute['attribute_value'];
                        $rgrItemAttr->attr_value = $rgrItemAttribute['attr_value'];
                        $rgrItemAttr->save();
                    }
                }
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Please add at least one row in the component table.',
                    'error' => "",
                ], 422);
            }

            // 7. Handle Attachments
            if ($request->hasFile('attachment')) {
                $mediaFiles = $rgr->uploadDocuments($request->file('attachment'), 'rgr', false);
            }

            // 8. Document Submission Logic
            $modelName = get_class($rgr);
            $totalValue = 0; 
            if ($request->input('document_status') == ConstantHelper::SUBMITTED) {
                $bookId = $rgr->book_id;
                $docId = $rgr->id;
                $remarks = $rgr->remark;
                $attachments = $request->file('attachment');
                $currentLevel = $rgr->approval_level ?? 1;
                $revisionNumber = $rgr->revision_number ?? 0;
                $actionType = 'submit';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $rgr->document_status = $approveDocument['approvalStatus'] ?? $request->input('document_status');
            } else {
                $rgr->document_status = $request->input('document_status') ?? ConstantHelper::DRAFT;
            }

            $rgr->save();
            // Create job
                if (in_array($rgr->document_status, [
                    ConstantHelper::APPROVED,
                    ConstantHelper::APPROVAL_NOT_REQUIRED 
                ])) {
                (new RGRJob)->createJob($rgr->id,'App\Models\ErpRgr');
            }
            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $rgr,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating RGR',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
   
       public function edit(Request $request, $id)
    {
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = RgrConstant::SERVICE_ALIAS;
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);

        if ($servicesBooks['services']->isEmpty()) {
            return redirect()->back();
        }

        if ($request->has('revisionNumber')) {
            $rgr = ErpRgrHistory::with(['items.attributes', 'items.uom'])  
                ->where('source_id', $id)
                ->where('revision_number', $request->revisionNumber)
                ->firstOrFail();
            $originalRgr = ErpRgr::findOrFail($id); 
        } else {
            $rgr = ErpRgr::with(['items.attributes', 'items.uom'])
                ->findOrFail($id);
            $originalRgr = $rgr;
        }

        $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
        $creatorType = Helper::userCheck()['type'];
        $totalValue = 0;

        $revision_number = $rgr->revision_number;
        $buttons = Helper::actionButtonDisplay(
            $rgr->book_id,
            $rgr->document_status,
            $rgr->id,
            $totalValue,
            $rgr->approval_level,
            $rgr->created_by ?? 0,
            $creatorType,
            $revision_number
        );

        $revNo = $rgr->revision_number;
        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $rgr->revision_number;
        }

        $docValue = 0;
        $approvalHistory = Helper::getApprovalHistory($originalRgr->book_id, $originalRgr->id, $revNo, $docValue, $originalRgr->created_by);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$rgr->document_status] ?? '';
        $locations = InventoryHelper::getAccessibleLocations();
        $isEdit = $buttons['submit'] || ($buttons['amend'] && intval(request('amendment') ?? 0));
        $view = 'rgr.edit';

        return view($view, [
            'isEdit' => $isEdit,
            'books' => $books,
            'rgr' => $rgr,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revision_number' => $revision_number,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(RgrRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $rgr = ErpRgr::find($id);

            if (!$rgr) {
                return response()->json([
                    'message' => 'RGR not found',
                    'error' => '',
                ], 404);
            }

            $currentStatus = $rgr->document_status;
            $actionType = $request->input('action_type');
        
            // Amendment logic 
            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'ErpRgr', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'ErpRgrItem', 'relation_column' => 'rgr_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'ErpRgrItemAttribute', 'relation_column' => 'rgr_item_id'],
                ];
                $a = Helper::documentAmendment($revisionData, $id);
            }
            // Update basic RGR attributes
            $rgr->document_status = $request->input('document_status');
            $rgr->remark = $request->input('remark');
            $rgr->final_remark = $request->input('final_remark') ?? '';
            $rgr->pickup_schdule_id = $request->input('pickup_schdule_id');
            $rgr->pickup_schdule_no = $request->input('pickup_schdule_no');
            $rgr->store_id = $request->input('store_id');
            $rgr->store_name = $request->input('store_name');
            $rgr->book_id = $request->input('book_id');
            $rgr->book_code = $request->input('book_code');

            // Update RGR Items
           if ($request->has('rgr_items') && is_array($request->input('rgr_items'))) {
                foreach ($request->input('rgr_items') as $itemData) {

                    $item = Item::with('uom')->find($itemData['item_id']);
                    $inventoryUomQty = ItemHelper::convertToBaseUom($item->id,$itemData['uom_id'] ?? 0,$itemData['qty'] ?? 0);
                    $subStoreId = null;
                    $categoryId = $itemData['category_id'] ?? $item->subcategory_id ?? null;
                    if ($request->input('store_id') && $categoryId) {
                        $storeMapping = ErpRgrStoreMapping::where('store_id', $request->input('store_id'))
                            ->where('category_id', $categoryId)
                            ->first();

                        $subStoreId = $storeMapping ? $storeMapping->sub_store_id : null;
                    }
                    if (isset($itemData['id'])) {
                        // Update existing item
                        $rgrItem = ErpRgrItem::find($itemData['id']);
                        if ($rgrItem) {
                            $rgrItem->item_id = $itemData['item_id'];
                            $rgrItem->hsn_id = $itemData['hsn_id'] ?? null;
                            $rgrItem->category_id = $itemData['category_id'] ?? null;
                            $rgrItem->sub_store_id = $subStoreId;
                            $rgrItem->item_uid = $itemData['item_uid'] ?? '';
                            $rgrItem->item_code = $itemData['item_code'] ?? '';
                            $rgrItem->item_name = $itemData['item_name'] ?? '';
                            $rgrItem->uom_id = $itemData['uom_id'];
                            $rgrItem->uom_name = $itemData['uom_name'];
                            $rgrItem->qty = $itemData['qty'];
                            $rgrItem->remarks = $itemData['item_remark'] ?? '';
                            $rgrItem->customer_id = $itemData['customer_id'] ?? null;
                            $rgrItem->customer_name = $itemData['customer_name'] ?? null;
                            $rgrItem->inventory_uom_id   = $item->uom?->id;
                            $rgrItem->inventory_uom_code = $item->uom?->name;
                            $rgrItem->inventory_uom_qty  = $inventoryUomQty;
                            $rgrItem->save();

                            $rgrItemAttributes = $itemData['rgr_item_attributes'] ?? [];
                            foreach ($rgrItemAttributes as $rgrItemAttribute) {
                                $rgrItemAttr = ErpRgrItemAttribute::find($rgrItemAttribute['id']) ?? new ErpRgrItemAttribute;
                                $rgrItemAttr->rgr_id = $rgr->id;
                                $rgrItemAttr->rgr_item_id = $rgrItem->id;
                                $rgrItemAttr->item_attribute_id = $rgrItemAttribute['item_attribute_id'];
                                $rgrItemAttr->attribute_name = $rgrItemAttribute['attribute_name'];
                                $rgrItemAttr->attr_name = $rgrItemAttribute['attr_name'];
                                $rgrItemAttr->attribute_value = $rgrItemAttribute['attribute_value'];
                                $rgrItemAttr->attr_value = $rgrItemAttribute['attr_value'];
                                $rgrItemAttr->save();
                            }
                        }
                    } else {
                        // Create a new item
                        $rgrItem = new ErpRgrItem;
                        $rgrItem->rgr_id = $rgr->id;
                        $rgrItem->item_id = $itemData['item_id'];
                        $rgrItem->hsn_id = $itemData['hsn_id'] ?? null;
                        $rgrItem->category_id = $itemData['category_id'] ?? null;
                        $rgrItem->sub_store_id = $subStoreId;
                        $rgrItem->item_uid = $itemData['item_uid'] ?? '';
                        $rgrItem->item_code = $itemData['item_code'] ?? '';
                        $rgrItem->item_name = $itemData['item_name'] ?? '';
                        $rgrItem->uom_id = $itemData['uom_id'];
                        $rgrItem->uom_name = $itemData['uom_name'];
                        $rgrItem->qty = $itemData['qty'];
                        $rgrItem->remarks = $itemData['item_remark'] ?? '';
                        $rgrItem->customer_id = $itemData['customer_id'] ?? null;
                        $rgrItem->customer_name = $itemData['customer_name'] ?? null;
                        $rgrItem->inventory_uom_id   = $item->uom?->id;
                        $rgrItem->inventory_uom_code = $item->uom?->name;
                        $rgrItem->inventory_uom_qty  = $inventoryUomQty;
                        $rgrItem->save();
                        $rgrItemAttributes = $itemData['rgr_item_attributes'] ?? [];
                            foreach ($rgrItemAttributes as $rgrItemAttribute) {
                                $rgrItemAttr = new ErpRgrItemAttribute;
                                $rgrItemAttr->rgr_id = $rgr->id;
                                $rgrItemAttr->rgr_item_id = $rgrItem->id;
                                $rgrItemAttr->item_attribute_id = $rgrItemAttribute['item_attribute_id'];
                                $rgrItemAttr->attribute_name = $rgrItemAttribute['attribute_name'];
                                $rgrItemAttr->attr_name = $rgrItemAttribute['attr_name'];
                                $rgrItemAttr->attribute_value = $rgrItemAttribute['attribute_value'];
                                $rgrItemAttr->attr_value = $rgrItemAttribute['attr_value'];
                                $rgrItemAttr->save();
                            }
                    }
                }
            } else {
                //Or log here
            }

            if ($request->hasFile('attachment')) {
                $mediaFiles = $rgr->uploadDocuments($request->file('attachment'), 'rgr', true);
            }

            $bookId = $rgr->book_id;
            $docId = $rgr->id;
            $amendRemarks = $request->input('amend_remarks') ?? null;
            $remarks = $rgr->remark;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $rgr->approval_level;
            $modelName = get_class($rgr);
            $totalValue = 0;

            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $revisionNumber = $rgr->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                $rgr->revision_number = $revisionNumber;
                $rgr->approval_level = 1;
                $rgr->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ??   $currentStatus;
                $rgr->document_status = $amendAfterStatus;
             
                $rgr->save();
            } else {
                if ($request->input('document_status') == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $rgr->revision_number ?? 0;
                    $actionType = 'submit'; 
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                    $rgr->document_status = $approveDocument['approvalStatus'] ?? $rgr->document_status;
                } else {
                    $rgr->document_status = $request->input('document_status') ?? ConstantHelper::DRAFT;
                }
            }

            $rgr->save();
            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $rgr,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while updating the RGR.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $rgr = ErpRgr::find($request->id);
            if (isset($rgr)) {
                $revoke = Helper::approveDocument($rgr->book_id, $rgr->id, $rgr->revision_number, '', [], 0, ConstantHelper::REVOKE, 0, get_class($rgr));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $rgr->document_status = $revoke['approvalStatus'];
                    $rgr->save();
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Revoked successfully',
                    ]);
                }
            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch (Exception $ex) {
            DB::rollBack();
             return response()->json([
                    'status' => 'error',
                    'message' => $ex -> getMessage(),
                ], 500); 
        }
    }

   # Get Quotation Bom Item List
 public function getPickupScheduleItems(Request $request)
    {
        $isAttribute = intval($request->is_attribute) ?? 0;
        $headerBookId = $request->header_book_id ?? null;
        $vehicleNo = $request->vehicle_no ?? null;
        $tripNo = $request->trip_no ?? null;
        $itemSearch = $request->item_search ?? null;
        $storeId = $request->store_id ?? null;
        $documentStatuses = [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED];
        $type = 'PickUp';

        $applicableBookIds = [];
        if ($headerBookId) {
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        }

        $pickupSchedules = ErpPickupSchedule::whereIn('document_status', $documentStatuses)
            ->when(!empty($applicableBookIds), function ($query) use ($applicableBookIds) {
                $query->whereIn('book_id', $applicableBookIds);
            })
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->when($vehicleNo, function ($query) use ($vehicleNo) {
                $query->where('vehicle_no', 'like', '%' . $vehicleNo . '%');
            })
            ->when($tripNo, function ($query) use ($tripNo) {
                $query->where('trip_no', 'like', '%' . $tripNo . '%');
            })
            ->when($itemSearch, function ($query) use ($itemSearch) {
                $query->whereHas('items', function ($itemQuery) use ($itemSearch) {
                    $itemQuery->where('item_name', 'like', '%' . $itemSearch . '%')
                            ->orWhere('item_code', 'like', '%' . $itemSearch . '%');
                });
            })
            ->whereNull('rgr_id')
            ->get();

        $html = view('rgr.partials.pickup-schdule-list', [
            'pickupSchedules' => $pickupSchedules,
            'isAttribute' => $isAttribute,
        ])->render();

        return response()->json([
            'data' => ['pis' => $html, 'isAttribute' => $isAttribute],
            'status' => 200,
            'message' => "Pickup schedule items fetched!",
        ]);
    }

   # Submit Pickup Item list
  public function processPickupItem(Request $request)
    {
        $isAttribute = intval($request->is_attribute) ?? 0;
        $selectedItems = [];
        if (is_array($request->selected_items)) {
            $selectedItems = $request->selected_items;
        } else if (is_string($request->selected_items)) {
            $decoded = json_decode($request->selected_items, true);
            $selectedItems = is_array($decoded) ? $decoded : [];
        }

        $extendedPickupItems = [];
        $pickupScheduleId = null; 

        foreach ($selectedItems as $item) {
            if (!empty($item['main_item'])) {
                $relatedItems = ErpPickupItem::where('pickup_schedule_id', $item['pickup_schedule_id'])
                    ->get();
                if (!$pickupScheduleId) {
                    $pickupScheduleId = $item['pickup_schedule_id'];
                }

                foreach ($relatedItems as $relatedItem) {
                     $erpItem = Item::with('hsn')->find($relatedItem->item_id);

                    $extendedPickupItems[] = [
                        'pickup_schedule_id' => $relatedItem->pickup_schedule_id, 
                        'item_id'      => $relatedItem->item_id,
                        'item_code'    => $relatedItem->item_code,
                        'item_name'    => $relatedItem->item_name,
                        'uom_id'       => $relatedItem->uom_id,
                        'uom_name'     => $relatedItem->uom_code,
                        'customer_id'  => $relatedItem->customer_id,
                        'customer_name'=> $relatedItem->customer_name,
                        'uid'          => $relatedItem->uid,
                        'qty'          => $relatedItem->qty,
                        'remarks'      => $relatedItem->remarks,
                        'customer_email' => $relatedItem->customer_email,
                        'customer_phone' => $relatedItem->customer_phone,
                        'item_uid'     => $erpItem ? $erpItem->item_uid : '',
                        'hsn_id'       => $erpItem ? $erpItem->hsn_id : '',
                        'hsn_code'     => $erpItem?->hsn?->code,  
                        'item_remark'   => $erpItem?->item_remark,  
                        'category_id'  => $erpItem ? $erpItem->subcategory_id : '',
                        'sub_store_id' => $erpItem ? $erpItem->sub_store_id : '',
                        'attribute'    => $relatedItem->item_attributes_array(),
                       
                    ];
                }
            }
        }

        $headerDetail = [];
        if ($pickupScheduleId) {
            $header = ErpPickupSchedule::find($pickupScheduleId);
            if ($header) {
                $headerDetail = [
                    'pickup_schedule_no' => $header->document_number,
                    'pickup_schedule_id' => $header->id,
                    'trip_no'            => $header->trip_no,
                    'vehicle_no'         => $header->vehicle_no,
                    'champ_name'         => $header->champ,
                    'remark'             => $header->remark,
                ];
            }
        }

        $rowCount = intval($request->rowCount) ? intval($request->rowCount) + 1 : 1;

        $html = view('rgr.partials.pickup-schdule-pull', [
            'pickupItems' => $extendedPickupItems,
            'is_pull'     => true,
            'rowCount'    => $rowCount,
        ])->render();

        return response()->json([
            'data' => [
                'pos'    => $html,
                'header' => $headerDetail,
            ],
            'status'  => 200,
            'message' => "fetched!"
        ]);
    }
}
