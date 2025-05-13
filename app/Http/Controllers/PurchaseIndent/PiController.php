<?php

namespace App\Http\Controllers\PurchaseIndent;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PiRequest;
use App\Models\Address;
use App\Models\ErpSaleOrder;
use App\Models\AttributeGroup;
use App\Models\Bom;
use App\Models\ErpSoItem;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\PiItem;
use App\Models\PiItemAttribute;
use App\Models\PiItemDelivery;
use App\Models\PiSoMapping;
use App\Models\PurchaseIndent;
use App\Models\BomDetail;
use App\Models\PurchaseIndentMedia;
use App\Models\PiSoMappingItem;
use App\Models\Department;
use App\Models\ErpSoItemBom;
use App\Models\Unit;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use Yajra\DataTables\DataTables;

class PiController extends Controller
{
     # Po List
    public function index(Request $request)
    {
        if (request()->ajax()) {

            $pis = PurchaseIndent::withDefaultGroupCompanyOrg()
                    ->withDraftListingLogic()
                    ->latest()
                    ->with('vendor')
                    ->get();

            return DataTables::of($pis)
            ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                $displayStatus = $row->display_status;
                $editRoute = route('pi.edit', $row->id);
                return "<div style='text-align:right;'>
                    <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
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
                </div>";
            })
            ->addColumn('book_name', function ($row) {
                return $row->book ? $row->book?->book_code : 'N/A';
            })
            ->addColumn('sales_order', function ($row) {
                $saleReferences = ErpSaleOrder::whereIn('id', $row->so_id)
                ->get()
                ->map(function ($item) {
                    return strtoupper($item->book_code) . ' - ' . $item->document_number;
                })
                ->unique()
                ->implode(', ');
                return $saleReferences;
            })
            ->addColumn('location', function ($row) {
                return $row?->store ? $row?->store?->store_name : 'N/A';
            })
            ->addColumn('department', function ($row) {
                if($row->sub_store_id) {
                    return $row?->sub_store ? $row?->sub_store?->name : 'N/A';
                } else {
                    return $row?->requester ? $row->requester?->name : 'N/A';
                }
            })
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
            })
            ->editColumn('revision_number', function ($row) {
                return strval($row->revision_number);
            })
            ->addColumn('components', function ($row) {
                return $row->pi_items->count() ?? 0;
            })
            ->rawColumns(['document_status'])
            ->make(true);
        }

        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        return view('procurement.pi.index',['servicesBooks' => $servicesBooks]);
    }

    // # Po create
    public function create()
    {
        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $user = Helper::getAuthenticatedUser();
        $serviceAlias = ConstantHelper::PI_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias,$parentUrl)->get();
        // $deparmentData = UserHelper::getDepartments($user->auth_user_id);
        $users = UserHelper::getUserSubOrdinates($user->auth_user_id);
        $selecteduserId = $user -> auth_user_id;
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);

        return view('procurement.pi.create', [
            'books'=> $books,
            // 'departments' => $deparmentData['departments'],
            // 'selectedDepartmentId' => $deparmentData['selectedDepartmentId'],
            'users' => $users['data'],
            'selecteduserId' => $selecteduserId,
            'locations' => $locations
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
        $html = view('procurement.pi.partials.item-row',compact('rowCount'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $isSo = intval($request->isSo) ?? 0;
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];

        $piItemId = $request->pi_item_id ?? null;
        $itemAttIds = [];
        if($piItemId) {
            $piItem = PiItem::find($piItemId);
            if($piItem) {
                $itemAttIds = $piItem->attributes()->pluck('item_attribute_id')->toArray();
            }
        }
        $itemAttributes = collect();
        if(count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
            if(count($itemAttributes) < 1) {
                $itemAttributes = $item?->itemAttributes;
            }
        } else {
            $itemAttributes = $item?->itemAttributes;
        }

        $html = view('procurement.pi.partials.comp-attribute',compact('item','rowCount','selectedAttr','isSo','itemAttributes'))->render();
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
        return response()->json(['data' => ['attr' => $item->itemAttributes->count(),'html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
    }


    # Purchase Indent store
    public function store(PiRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first(); 
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;

            # Bom Header save
            $pi = new PurchaseIndent;
            $pi->organization_id = $organization->id;
            $pi->group_id = $organization->group_id;
            $pi->company_id = $organization->company_id;
            $pi->department_id = $request->department_id ?? null;
            $pi->requester_type = isset($request->sub_store_id) && $request->sub_store_id ? 'Department' : 'User';
            $pi->user_id = $request->user_id ?? null;
            $pi->book_id = $request->book_id;
            $pi->book_code = $request->book_code;
            $pi->store_id = $request->store_id ?? null;
            $pi->sub_store_id = $request->sub_store_id ?? null;
            $pi->so_tracking_required = $request->so_tracking_required ?? 'no';
            $document_number = $request->document_number ?? null;

            /**/
            $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
            $regeneratedDocExist = PurchaseIndent::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }

            $pi->doc_number_type = $numberPatternData['type'];
            $pi->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $pi->doc_prefix = $numberPatternData['prefix'];
            $pi->doc_suffix = $numberPatternData['suffix'];
            $pi->doc_no = $numberPatternData['doc_no'];
            /**/
            $pi->document_number = $document_number;
            $pi->document_date = $request->document_date;
            $pi->reference_number = $request->reference_number;
            $pi->document_status = $request->document_status;
            $pi->remarks = $request->remarks ?? null;
            $pi->save();

            if (isset($request->all()['components']) && count($request->all()['components'])) {
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    # Purchase Order Detail Save
                    $piDetail = new PiItem;
                    $unit = Unit::find($component['uom_id']);

                    $piDetail->pi_id = $pi->id;
                    $piDetail->item_id = $component['item_id'] ?? null;
                    $piDetail->item_code = $component['item_code'] ?? null;
                    $piDetail->item_name = $component['item_name'] ?? null;
                    $piDetail->hsn_id = $component['hsn_id'] ?? null;
                    $piDetail->hsn_code = $component['hsn_code'] ?? null;
                    $piDetail->uom_id = $component['uom_id'] ?? null;
                    $piDetail->uom_code = $unit?->name ?? null;
                    $piDetail->indent_qty = $component['qty'] ?? 0.00;
                    $piDetail->inventory_uom_code = $item->uom->name ?? null;
                    if(@$component['uom_id'] == $item->uom_id) {
                        $piDetail->inventory_uom_id = $component['uom_id'] ?? null;
                        $piDetail->inventory_uom_code = $component['uom_code'] ?? null;
                        $piDetail->inventory_uom_qty = $component['qty'] ;
                    } else {
                        $piDetail->inventory_uom_id = $component['uom_id'] ?? null;
                        $piDetail->inventory_uom_code = $component['uom_code'] ?? null;
                        $alUom = $item->alternateUOMs()->where('uom_id',$component['uom_id'])->first();
                        if($alUom) {
                            $piDetail->inventory_uom_qty = floatval($component['qty']) * $alUom->conversion_to_inventory;
                        }
                    }

                    $piDetail->remarks = $component['remark'] ?? null;
                    $piDetail->vendor_id = $component['vendor_id'] ?? null;
                    $piDetail->vendor_code = $component['vendor_code'] ?? null;
                    $piDetail->vendor_name = $component['vendor_name'] ?? null;
                    $piDetail->so_id = $component['so_id'] ?? null; 
                    $piDetail->save();
                    $piDetail->refresh();
                    /*Pi_So_Mapping Update*/
                    if(@$component['so_pi_mapping_item_id']) {
                        if(intval($component['so_pi_mapping_item_id']) == $piDetail->item_id) {

                            $showAttribute = intval($request->show_attribute) ?? 0;
                            $so_item_ids = $request->so_item_ids ? explode(',',$request->so_item_ids) : [];
                            
                            if(!$showAttribute) {
                                $itemIds = $request->item_ids ? explode(',',$request->item_ids) : [];
                                $so_item_ids = ErpSoItem::whereIn('sale_order_id', $so_item_ids)
                                    ->whereIn('item_id', $itemIds)
                                    ->pluck('id')
                                    ->toArray();
                            }

                            $attributes = $piDetail->attributes->map(fn($attribute) => [
                                'attribute_id' => $attribute->item_attribute_id,
                                'attribute_value' => intval($attribute->attribute_value),
                            ])->toArray();

                            $indent_qty = $piDetail->indent_qty;

                            $datas = PiSoMapping::where('item_id', $piDetail->item_id)
                                        ->whereIn('so_item_id',$so_item_ids)
                                        ->whereJsonContains('attributes', $attributes)
                                        ->where(function($query) use($piDetail) {
                                            if($piDetail->so_id) {
                                                $query->where('so_id', $piDetail->so_id);
                                            }
                                        })
                                        ->get();
                            foreach ($datas as $data) {
                                $availableQty = $data->qty - $data->pi_item_qty;
                                if ($availableQty > 0) {
                                    $allocatedQty = min($indent_qty, $availableQty);
                                    $data->pi_item_qty += $allocatedQty;
                                    $data->save();

                                    $indent_qty -= $allocatedQty; 
                                    $piSoMappingItem = PiSoMappingItem::firstOrNew([
                                        'pi_so_mapping_id' => $data->id,
                                        'pi_item_id' => $piDetail->id
                                    ]);
                            
                                    $piSoMappingItem->qty += $allocatedQty;
                                    $piSoMappingItem->save();
                                    if ($indent_qty <= 0) {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    #Save component Attr
                    foreach($piDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $piAttr = new PiItemAttribute;
                        $piAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $piAttr->pi_id = $pi->id;
                        $piAttr->pi_item_id = $piDetail->id;
                        $piAttr->item_attribute_id = $itemAttribute->id;
                        $piAttr->item_code = $component['item_code'] ?? null;
                        $piAttr->attribute_name = $itemAttribute->attribute_group_id;
                        $piAttr->attribute_value = $piAttrName ?? null;
                        $piAttr->attribute_group_id = $itemAttribute->attribute_group_id;
                        $piAttr->attribute_id = $piAttrName ?? null;
                        $piAttr->save();
                        }
                    }

                    #Save Componet Delivery
                    if(isset($component['delivery'])) {
                        foreach($component['delivery'] as $delivery) {
                            if(isset($delivery['d_qty']) && $delivery['d_qty']) {
                                $piItemDelivery = new PiItemDelivery;
                                $piItemDelivery->pi_id = $pi->id;
                                $piItemDelivery->pi_item_id = $piDetail->id;
                                $piItemDelivery->qty = $delivery['d_qty'] ?? 0.00;
                                $piItemDelivery->delivery_date = $delivery['d_date'] ?? now();
                                $piItemDelivery->save();
                            }
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


            /*Pi Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $pi->uploadDocuments($request->file('attachment'), 'pi', false);
            }

            $pi->save();

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $modelName = get_class($pi);
                $bookId = $pi->book_id;
                $docId = $pi->id;
                $remarks = $pi->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $pi->approval_level ?? 1;
                $revisionNumber = $pi->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                $pi->document_status = $approveDocument['approvalStatus'] ??  $pi->document_status;
            } else {
                $pi->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }

            $pi->save();

            $redirectUrl = '';
            if($pi->document_status == ConstantHelper::APPROVED) {
                $redirectUrl = route('pi.generate-pdf', $pi->id);
            }

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $pi,
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
    public function update(PiRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            # Pi Header save
            $pi = PurchaseIndent::find($id);
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first(); 
            $currentStatus = $pi->document_status;
            $actionType = $request->action_type;
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'PurchaseIndent', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'PiItem', 'relation_column' => 'pi_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PiItemAttribute', 'relation_column' => 'pi_item_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PiItemDelivery', 'relation_column' => 'pi_item_id']
                ];
                $a = Helper::documentAmendment($revisionData, $id);
            }
            $keys = ['deletedPiItemIds', 'deletedDelivery', 'deletedAttachmentIds'];
            $deletedData = [];
            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }
            if (count($deletedData['deletedAttachmentIds'])) {
                $medias = PurchaseIndentMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
                foreach ($medias as $media) {
                    if ($request->document_status == ConstantHelper::DRAFT) {
                        Storage::delete($media->file_name);
                    }
                    $media->delete();
                }
            }
            if (count($deletedData['deletedDelivery'])) {
                PiItemDelivery::whereIn('id',$deletedData['deletedDelivery'])->delete();
            }
            if (count($deletedData['deletedPiItemIds'])) {
                $piItems = PiItem::whereIn('id',$deletedData['deletedPiItemIds'])->get();
                foreach($piItems as $piItem) {
                    if($piItem?->so_pi_mapping_item->count()) {
                        foreach($piItem?->so_pi_mapping_item as $so_pi_mapping_item) {
                            $so_pi_mapping_item->pi_so_mapping->pi_item_qty -= $so_pi_mapping_item->qty;
                            $so_pi_mapping_item->pi_so_mapping->save();
                            $so_pi_mapping_item->delete();
                        }
                    }
                    $piItem->attributes()->delete();
                    $piItem->itemDelivery()->delete();
                    $piItem->delete();
                }
            }
            $pi->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $pi->remarks = $request->remarks ?? null;
            $pi->save();
            if (isset($request->all()['components']) && count($request->all()['components'])) {
                foreach($request->all()['components'] as $c_key => $component) {

                    $item = Item::find($component['item_id'] ?? null);
                    $unit = Unit::find($component['uom_id']);
                    # Purchase Order Detail Save
                    $piDetail = PiItem::find($component['pi_item_id'] ?? null) ?? new PiItem;
                    $updatedQty = 0;
                    if(isset($piDetail->id)) {
                        $updatedQty =  floatval($component['qty']) - $piDetail->indent_qty;
                    }

                    $piDetail->pi_id = $pi->id;
                    if(!$piDetail->po_item) {
                        $piDetail->item_id = $component['item_id'] ?? null;
                        $piDetail->item_code = $component['item_code'] ?? null;
                        $piDetail->item_name = $component['item_name'] ?? null;
                        $piDetail->hsn_id = $component['hsn_id'] ?? null;
                        $piDetail->hsn_code = $component['hsn_code'] ?? null;
                        $piDetail->uom_id = $component['uom_id'] ?? null;
                        $piDetail->uom_code = $unit?->name ?? null;
                        $piDetail->indent_qty = $component['qty'] ?? 0.00;
                        $piDetail->inventory_uom_id = $item->uom_id ?? null;
                        $piDetail->inventory_uom_code = $item->uom->name ?? null;
                        if(@$component['uom_id'] == $item->uom_id) {
                            $piDetail->inventory_uom_id = $component['uom_id'];
                            $piDetail->inventory_uom_code = $component['uom_code'] ?? null;
                            $piDetail->inventory_uom_qty = $component['qty'] ;
                        } else {
                            $alUom = $item->alternateUOMs()->where('uom_id',$component['uom_id'])->first();
                            if($alUom) {
                                $piDetail->inventory_uom_qty = floatval($component['qty']) * $alUom->conversion_to_inventory;
                            }
                        }
                    }
                    $piDetail->remarks = $component['remark'] ?? null;
                    $piDetail->vendor_id = $component['vendor_id'] ?? null;
                    $piDetail->vendor_code = $component['vendor_code'] ?? null;
                    $piDetail->vendor_name = $component['vendor_name'] ?? null;
                    $piDetail->so_id = $component['so_id'] ?? null;
                    $piDetail->save();

                    $piDetail->refresh();
                    /*Pi_So_Mapping Update*/
                    if($updatedQty < 0) {
                        $poSiMappingItems = PiSoMappingItem::where('pi_item_id', $piDetail->id)
                            ->leftJoin('erp_pi_so_mapping', 'erp_pi_so_mapping_items.pi_so_mapping_id', '=', 'erp_pi_so_mapping.id')
                            ->selectRaw('erp_pi_so_mapping_items.id, erp_pi_so_mapping.id as mapping_id, (erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty) as balQty')
                            ->orderBy('balQty', 'desc')
                            ->get();
                    } else {
                        $poSiMappingItems = PiSoMappingItem::where('pi_item_id', $piDetail->id)
                            ->leftJoin('erp_pi_so_mapping', 'erp_pi_so_mapping_items.pi_so_mapping_id', '=', 'erp_pi_so_mapping.id')
                            ->selectRaw('erp_pi_so_mapping_items.id, erp_pi_so_mapping.id as mapping_id, (erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty) as balQty')
                            ->orderBy('balQty', 'asc')
                            ->get();
                    }
                    foreach ($poSiMappingItems as $poSiMappingItem) {
                        $piSoMapping = PiSoMapping::find($poSiMappingItem->mapping_id);
                        if (!$piSoMapping) {
                            continue;
                        }

                        if ($updatedQty < 0) {
                            $balQty = $piSoMapping->pi_item_qty;    
                        } else {
                            $balQty = $poSiMappingItem->balQty;
                        }
                        
                        $allowedQty = min($updatedQty, $balQty);
                        if ($allowedQty < 0) {
                            if (abs($allowedQty) >= $balQty) {
                                $allowedQty = $balQty * -1;
                            }
                        }

                        // Update pi_item_qty in the related pi_so_mapping
                        $piSoMapping->pi_item_qty += $allowedQty;
                        $piSoMapping->save();

                        
                        // Update qty in the current PiSoMappingItem
                        $poSiMapItem = PiSoMappingItem::find($poSiMappingItem->id);
                        $poSiMapItem->qty += $allowedQty;
                        $poSiMapItem->save();

                        $updatedQty -= $allowedQty;
                        if (0 == $updatedQty) {
                            break;
                        }
                    }

                    $showAttribute = intval($request->show_attribute) ?? 0;
                    $so_item_ids = $request->so_item_ids ? explode(',',$request->so_item_ids) : [];
                    
                    if(!$showAttribute) {
                        $itemIds = $request->item_ids ? explode(',',$request->item_ids) : [];
                        $so_item_ids = ErpSoItem::whereIn('sale_order_id', $so_item_ids)
                            ->whereIn('item_id', $itemIds)
                            ->pluck('id')
                            ->toArray();
                    }

                    // For new generate
                    if(!$poSiMappingItems?->count() && count($so_item_ids)) {
                        $attributes = $piDetail->attributes->map(fn($attribute) => [
                            'attribute_id' => $attribute->item_attribute_id,
                            'attribute_value' => intval($attribute->attribute_value),
                        ])->toArray();

                        $indent_qty = $piDetail->indent_qty;

                        $datas = PiSoMapping::where('item_id', $piDetail->item_id)
                                    ->whereIn('so_item_id',$so_item_ids)
                                    ->whereJsonContains('attributes', $attributes)
                                    ->where(function($query) use($piDetail) {
                                        if($piDetail->so_id) {
                                            $query->where('so_id', $piDetail->so_id);
                                        }
                                    })
                                    ->get();

                        foreach ($datas as $data) {
                            $availableQty = $data->qty - $data->pi_item_qty;
                            if ($availableQty > 0) {
                                $allocatedQty = min($indent_qty, $availableQty);
                                $data->pi_item_qty += $allocatedQty;
                                $data->save();

                                $indent_qty -= $allocatedQty; 
                                $piSoMappingItem = PiSoMappingItem::firstOrNew([
                                    'pi_so_mapping_id' => $data->id,
                                    'pi_item_id' => $piDetail->id
                                ]);
                        
                                $piSoMappingItem->qty += $allocatedQty;
                                $piSoMappingItem->save();
                                if ($indent_qty <= 0) {
                                    break;
                                }
                            }
                        }
                    }

                    #Save component Attr
                    foreach($piDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $piAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                        $piAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            if(!$piDetail->po_item) {
                                $piAttr = PiItemAttribute::find($piAttrId) ?? new PiItemAttribute;
                                $piAttr->pi_id = $pi->id;
                                $piAttr->pi_item_id = $piDetail->id;
                                $piAttr->item_attribute_id = $itemAttribute->id;
                                $piAttr->item_code = $component['item_code'] ?? null;
                                $piAttr->attribute_name = $itemAttribute->attribute_group_id;
                                $piAttr->attribute_value = $piAttrName ?? null;
                                $piAttr->save();
                            }
                        }
                    }
                    #Save Componet Delivery
                    if(isset($component['delivery'])) {
                        foreach($component['delivery'] as $delivery) {
                            if(isset($delivery['d_qty']) && $delivery['d_qty']) {
                                $piItemDelivery = PiItemDelivery::find($delivery['id'] ?? null) ?? new PiItemDelivery;
                                $piItemDelivery->pi_id = $pi->id;
                                $piItemDelivery->pi_item_id = $piDetail->id;
                                $piItemDelivery->qty = $delivery['d_qty'] ?? 0.00;
                                $piItemDelivery->delivery_date = $delivery['d_date'] ?? now();
                                $piItemDelivery->save();
                            }
                        }
                    }
                }
            }
            /*Pi Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $pi->uploadDocuments($request->file('attachment'), 'pi', false);
            }
            $pi->save();
            
            /*Create document submit log*/
            $bookId = $pi->book_id; 
            $docId = $pi->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $pi->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $pi->approval_level;
            $modelName = get_class($pi);
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                //*amendmemnt document log*/
                $revisionNumber = $pi->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                $pi->revision_number = $revisionNumber;
                $pi->approval_level = 1;
                $pi->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ?? $pi->document_status;
                // $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                // if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                //     $totalValue = 0;
                //     $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                // }
                // if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                //     $actionType = 'submit';
                //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                // }
                $pi->document_status = $amendAfterStatus;
                $pi->save();
            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $modelName = get_class($pi);
                    $bookId = $pi->book_id;
                    $docId = $pi->id;
                    $remarks = $pi->remarks;
                    $attachments = $request->file('attachment');
                    $currentLevel = $pi->approval_level;
                    $revisionNumber = $pi->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    $pi->document_status = $approveDocument['approvalStatus'] ?? $pi->document_status;
                } else {
                    $pi->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }
            $pi->save();
            $redirectUrl = '';
            if($pi->document_status == ConstantHelper::APPROVED) {
                $redirectUrl = route('pi.generate-pdf', $pi->id);
            }
            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $pi,
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

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $delivery = json_decode($request->delivery,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';
        if($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
            // $uomName = $alUom->uom->name ?? 'NA';
        }

        $specifications = $item->specifications()->whereNotNull('value')->get();

        $remark = $request->remark ?? null;
        $delivery = isset($delivery) ? $delivery  : null;
        $piItemIds = $request->pi_item_id ? [$request->pi_item_id] : []; 
        $soId = $request->so_id ?? null; 
        $uniqueSoIds = PiItem::whereIn('id',$piItemIds)->whereNotNull('so_id')->pluck('so_id')->toArray();
        $saleReferences = ErpSaleOrder::whereIn('id', $uniqueSoIds)->get();

        $inventoryStock = InventoryHelper::totalInventoryAndStock($item->id, $selectedAttr, $item->uom_id, null);
        $html = view('procurement.pi.partials.comp-item-detail',compact('item','selectedAttr','remark','uomName','qty','delivery','specifications','saleReferences','inventoryStock'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # Edit Po
    public function edit(Request $request, $id)
    {
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $user = Helper::getAuthenticatedUser();
        $serviceAlias = ConstantHelper::PI_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $pi = PurchaseIndent::find($id);
        $createdBy = $pi->created_by;
        $revision_number = $pi->revision_number ?? 0;
        $creatorType = Helper::userCheck()['type'];
        $buttons = Helper::actionButtonDisplay($pi->book_id,$pi->document_status , $pi->id, 0, $pi->approval_level, $pi->created_by ?? 0, $creatorType, $revision_number);

        $revNo = $pi->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $pi->revision_number;
        }

        $approvalHistory = Helper::getApprovalHistory($pi->book_id, $pi->id, $revNo, 0, $createdBy);
        $view = 'procurement.pi.edit';

        if($request->has('revisionNumber') && $request->revisionNumber != $pi->revision_number) {
            $pi = $pi->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'procurement.pi.view';
        }

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pi->document_status] ?? '';
        $organization = Organization::where('id', $user->organization_id)->first();
        $departmentsData = UserHelper::getDepartments($user->auth_user_id);
        $users = UserHelper::getUserSubOrdinates($user->auth_user_id);
        $selecteduserId = $pi ?-> user_id;
        $isEdit = $buttons['submit'];
        if(!$isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true: false;
        }
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $saleOrders = ErpSaleOrder::whereIn('id', $pi->so_id ?? [])
        ->get();
        return view($view, [
            'isEdit'=> $isEdit,
            'books'=> $books,
            'pi' => $pi,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revision_number' => $revision_number,
            // 'selectedDepartmentId' => $departmentsData['selectedDepartmentId'],
            'departments' => $departmentsData['departments'],
            'users' => $users['data'],
            'selecteduserId' => $selecteduserId,
            'locations' => $locations,
            'saleOrders' => $saleOrders
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
        $pi = PurchaseIndent::with(['vendor', 'pi_items', 'book'])
            ->findOrFail($id);
        $shippingAddress = $pi->shippingAddress;

        $totalItemValue = 0.00;
        $totalItemDiscount = 0.00;
        $totalHeaderDiscount = 0.00;
        $totalTaxes = 0.00;
        $totalTaxableValue = ($totalItemValue - ($totalItemDiscount + $totalHeaderDiscount));
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalAmount = ($totalAfterTax + $pi->total_expense_value ?? 0.00);
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pi->document_status] ?? '';
        $saleReferences = ErpSaleOrder::whereIn('id', $pi->so_id)
        ->get()
        ->map(function ($item) {
            return strtoupper($item->book_code) . ' - ' . $item->document_number;
        })
        ->unique()
        ->implode(', ');

        $pdf = PDF::loadView(
            // return view(
            'pdf.pi',
            [
                'pi'=> $pi,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress,
                'shippingAddress' =>     $shippingAddress,
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
                'saleReferences' => $saleReferences
            ]
        );
        return $pdf->stream('Purchase-Indent-' . date('Y-m-d') . '.pdf');
    }

   # Get So Item List
   public function getSo(Request $request)
   {
       $isAttribute = intval($request->is_attribute) ?? 0;
       $seriesId = $request->series_id ?? null;
       $docNumber = $request->document_number ?? null;
       $itemId = $request->item_id ?? null;
       $customerId = $request->customer_id ?? null;
       $headerBookId = $request->header_book_id ?? null;
       $itemSearch = $request->item_search ?? null;
       $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
    //    $soItems = ErpSoItem::where(function ($query) {
    //        $query->whereExists(function ($subQuery) {
    //            $subQuery->select(DB::raw(1))
    //                ->from('erp_pi_so_mapping')
    //                ->whereRaw('erp_pi_so_mapping.so_item_id = erp_so_items.id')
    //                ->where(function ($q) {
    //                     $q->whereRaw('ROUND(erp_pi_so_mapping.qty, 4) < ROUND(erp_so_items.order_qty, 4)');
    //                 });
    //             //    ->where(function ($q) {
    //             //         $q->whereRaw('ROUND(erp_pi_so_mapping.qty,4) > ROUND(erp_pi_so_mapping.pi_item_qty,4)')
    //             //         ->orWhereRaw('ROUND(erp_pi_so_mapping.order_qty,4) < ROUND(erp_so_items.order_qty,4)');
    //             //     });
    //        })
    //        ->orWhere(function ($subQuery) {
            //    $subQuery->whereNotExists(function ($subQuery2) {
            //        $subQuery2->select(DB::raw(1))
            //            ->from('erp_pi_so_mapping')
            //            ->whereRaw('erp_pi_so_mapping.so_item_id = erp_so_items.id');
            //    });
    //        });
    //    })
    //    ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $docNumber) {
    //        $subQuery->withDefaultGroupCompanyOrg()
    //            ->whereIn('book_id', $applicableBookIds)
    //            -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
    //            ->when($request->customer_id, function ($custQuery) use ($request) {
    //                $custQuery->where('customer_id', $request->customer_id);
    //            })
    //            ->when($request->book_id, function ($bookQuery) use ($request) {
    //                $bookQuery->where('book_id', $request->book_id);
    //            })
    //            ->when($request->document_id, function ($docQuery) use ($request) {
    //                $docQuery->where('id', $request->document_id);
    //            })
    //            ->when($docNumber, function ($query) use ($docNumber) {
    //                $query->where('document_number', 'LIKE', "%{$docNumber}%");
    //            });
    //    })
    //    ->whereColumn('invoice_qty', '<', 'order_qty')
    //    ->where(function ($query) use ($itemSearch) {
    //         if ($itemSearch) {
    //             $query->whereHas('item', function ($query) use ($itemSearch) {
    //                 $query->where('item_name', 'like', '%' . $itemSearch . '%')
    //                     ->orWhere('item_code', 'like', '%' . $itemSearch . '%');
    //             });
    //         }
    //    })
    //    ->with(['header', 'item']);
        $soItems = ErpSoItem::where(function ($query) {
            $query->whereNotExists(function ($subQuery2) {
                $subQuery2->select(DB::raw(1))
                    ->from('erp_pi_so_mapping')
                    ->whereRaw('erp_pi_so_mapping.so_item_id = erp_so_items.id')
                    ->whereRaw('erp_pi_so_mapping.pi_item_qty > 0');
            });
            // $query->whereHas('soItemMapping', function ($q) {
            //     $q->where(function ($q) {
            //         $q->whereRaw('ROUND(erp_pi_so_mapping.qty,2) > ROUND(erp_pi_so_mapping.pi_item_qty,2)');
            //         // ->orWhereRaw('ROUND(erp_pi_so_mapping.order_qty,4) < ROUND(erp_so_items.order_qty,4)');
            //     });
            // })
            // ->orWhereDoesntHave('soItemMapping');
        })
        ->whereColumn('invoice_qty', '<', 'order_qty')
        ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $docNumber) {
            $subQuery->withDefaultGroupCompanyOrg()
                ->whereIn('book_id', $applicableBookIds)
                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                ->when($request->customer_id, function ($q) use ($request) {
                    $q->where('customer_id', $request->customer_id);
                })
                ->when($request->book_id, function ($q) use ($request) {
                    $q->where('book_id', $request->book_id);
                })
                ->when($request->document_id, function ($q) use ($request) {
                    $q->where('id', $request->document_id);
                })
                ->when($docNumber, function ($q) use ($docNumber) {
                    $q->where('document_number', 'LIKE', "%{$docNumber}%");
                });
        })
        ->when($itemSearch, function ($q) use ($itemSearch) {
            $q->whereHas('item', function ($q2) use ($itemSearch) {
                $q2->where('item_name', 'like', "%$itemSearch%")
                    ->orWhere('item_code', 'like', "%$itemSearch%");
            });
        })
        ->with(['header', 'item', 'soItemMapping']);

        if(!$isAttribute) {
            $groupByColumns = ['sale_order_id', 'item_id','item_name','item_code'];
            $soItems = $soItems->groupBy($groupByColumns)
                    ->selectRaw(implode(',', array_merge($groupByColumns, [
                        'SUM(order_qty) as order_qty',
                        'SUM(invoice_qty) as invoice_qty'
                    ])));
       }
       $soItems = $soItems->get();

       $html = view('procurement.pi.partials.so-item-list', ['soItems' => $soItems, 'isAttribute' => $isAttribute])->render();
       return response()->json(['data' => ['pis' => $html, 'isAttribute' => $isAttribute], 'status' => 200, 'message' => "fetched!"]);
   }

   # Submit PI Item list
   public function processSoItem(Request $request)
   {
       $isAttribute = intval($request->is_attribute) ?? 0;
       $user = Helper::getAuthenticatedUser();
       $ids = json_decode($request->ids,true) ?? [];
       $ids = array_values(array_unique($ids));
       if(!$isAttribute) {
            $selectedData = json_decode($request->selected_items,true); 
            $saleOrderIds = array_column($selectedData, 'sale_order_id');
            $itemIds = array_column($selectedData, 'item_id');
            $ids = ErpSoItem::whereIn('sale_order_id', $saleOrderIds)
                            ->whereIn('item_id', $itemIds)
                            ->pluck('id')
                            ->toArray();
       } 
       $soItems = ErpSoItem::whereIn('id', $ids)
                   ->where(function($query) {
                       $query->whereNotExists(function ($subQuery) {
                           $subQuery->select(DB::raw(1))
                               ->from('erp_pi_so_mapping')
                               ->whereRaw('erp_pi_so_mapping.so_item_id = erp_so_items.id')
                               ->whereRaw('erp_pi_so_mapping.qty <= erp_pi_so_mapping.pi_item_qty')
                               ->whereColumn('erp_pi_so_mapping.order_qty', '>=', 'erp_so_items.order_qty');
                       });
                   })
                   ->get();
       $soItemIdArr = [];
       $createdBy = $user?->auth_user_id;

       DB::beginTransaction();
       foreach($soItems as $key => $soItem) {
           $soItemIdArr[] = $soItem->id;
           $soId = $soItem?->header?->id ?? null;
           $soItemId = $soItem->id;
           $itemId = $soItem->item_id;
        //    $uomConversion = floatval($soItem->inventory_uom_qty) / floatval($soItem->order_qty);
        //    $q = $soItem?->soItemMapping->count() ? $soItem?->soItemMapping->first()->qty : 0;
        //    $avlQty = $soItem->order_qty - $soItem->invoice_qty;
        //    $avlQty = round($avlQty * $uomConversion,2);
        //    $avlQty = $avlQty - $q;
        //    $avlQty = max($avlQty, 0);
            $q = $soItem?->soItemMapping->count() ? $soItem?->soItemMapping->first()->order_qty : 0;
            $avlQty = $soItem->order_qty - $soItem->invoice_qty - $q;
            $avlQty = max($avlQty, 0);
           // if($soItem?->soItemMapping->count() < 1) {
               if($avlQty > 0) {
                $soAttribute = $soItem->attributes->map(fn($soAttribute) => [
                    'attribute_id' => $soAttribute->item_attribute_id,
                    'attribute_value' => intval($soAttribute->attr_value)
                ])->toArray();
               $res = $this->syncPiSoMapping($soId, $soItemId, $itemId, $soAttribute, $avlQty, $createdBy, $avlQty);
                   if($res['status'] == 422) {
                       DB::rollBack();
                       return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => $res['message']]);
                   }

               }
           // }
       }

       do {
           $soProcessItems = PiSoMapping::whereIn('so_item_id', $soItemIdArr)
           ->where('created_by', $user->auth_user_id)
           // ->whereNull('pi_item_id')
           ->whereNotNull('child_bom_id')
           ->get();
        //    dd($soProcessItems);
           foreach($soProcessItems as $soProcessItem) {
               $soId = $soProcessItem->so_id;
               $soItemId = $soProcessItem->so_item_id;
               $itemId = $soProcessItem->item_id;
               $attributes = json_decode($soProcessItem->attributes,true);
               $soItemOrderQty = $soProcessItem->order_qty;
               $mappingExit = PiSoMapping::where([
                   ['so_id', $soId],
                   ['so_item_id', $soItemId],
                   ['item_id', $itemId]
                   ])
                   ->whereJsonContains('attributes', $attributes)
                   ->first();
                $updatedQty = $soProcessItem->qty;
                if(isset($mappingExit) && $mappingExit) {
                    $updatedQty = $mappingExit->qty;
                }

               $res = $this->syncPiSoMapping($soId, $soItemId, $itemId, $attributes, $updatedQty, $createdBy, $soItemOrderQty);
               if($res['status'] == 422) {
                   DB::rollBack();
                   return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => $res['message']]);
               }
               $soProcessItem->delete();
           }
       } while(PiSoMapping::whereIn('so_item_id', $soItemIdArr)
       ->where('created_by', $user->auth_user_id)
       // ->whereNull('pi_item_id')
       ->whereNotNull('child_bom_id')
       ->exists());

       $soTracking = $request?->so_tracking_required ?? 'no';

        if ($soTracking === 'yes') {
            $soProcessItems = PiSoMapping::whereIn('so_item_id', $soItemIdArr)
            ->select(
                'erp_pi_so_mapping.so_id',
                'erp_pi_so_mapping.item_id',
                DB::raw('erp_pi_so_mapping.attributes'),
                DB::raw('ROUND(SUM(erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty),2) as total_qty')
            )
            ->groupBy('erp_pi_so_mapping.so_id','erp_pi_so_mapping.item_id', 'erp_pi_so_mapping.attributes')
            ->havingRaw('total_qty > 0')
            ->get();
        } else {
            $soProcessItems = PiSoMapping::whereIn('so_item_id', $soItemIdArr)
            ->select(
                DB::raw('NULL as so_id'),
                'erp_pi_so_mapping.item_id',
                DB::raw('erp_pi_so_mapping.attributes'),
                DB::raw('ROUND(SUM(erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty),2) as total_qty')
            )
            ->groupBy('erp_pi_so_mapping.item_id', 'erp_pi_so_mapping.attributes')
            ->havingRaw('total_qty > 0')
            ->get();
        }

       DB::commit();

       $html = view('procurement.pi.partials.so-process-data', ['soTracking' => $soTracking,'soProcessItems' => $soProcessItems])->render();
       // $html = view('procurement.pi.partials.item-row-so', ['soItems' => $soItems])->render();
       return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
   }

   /**
    * Sync or Create PiSoMapping
    */
   private function syncPiSoMapping($soId, $soItemId, $itemId, $attr, $soQty, $createdBy, $soItemOrderQty)
   {
       $so = ErpSaleOrder::find($soId);
       $customerId = $so?->customer_id; 
       $item = Item::find($itemId);
       $checkBomExist = ItemHelper::checkItemBomExists($itemId, $attr);
       if($checkBomExist['bom_id']) {
            $bom = Bom::find($checkBomExist['bom_id']);
            $bomDetails = (strtolower($bom->customizable) === 'no')
                ? BomDetail::where('bom_id', $checkBomExist['bom_id'])->get()
                : ErpSoItemBom::where('bom_id', $checkBomExist['bom_id'])
                    ->where('sale_order_id', $soId)
                    ->where('so_item_id', $soItemId)
                    ->get();

            if (strtolower($bom->customizable) === 'yes' && $bomDetails->isEmpty()) {
                $bomDetails = BomDetail::where('bom_id', $checkBomExist['bom_id'])->get();
            }
            
            if($bom->production_type == 'In-house') {
                foreach($bomDetails as $bomDetail) {
                    
                    $bomDetailId = null;
                    $attributes = [];
                    if ($bomDetail instanceof \App\Models\BomDetail) {
                        $attributes = $bomDetail->attributes->map(fn($attribute) => [
                            'attribute_id' => intval($attribute->item_attribute_id),
                            'attribute_value' => intval($attribute->attribute_value),
                        ])->toArray();
                        $bomDetailId = $bomDetail->id;
                    } elseif ($bomDetail instanceof \App\Models\ErpSoItemBom) {
                        $attributes = array_map(function ($attribute) {
                            return [
                                'attribute_id' => intval($attribute['attribute_id']),
                                'attribute_value' => intval($attribute['attribute_value_id']),
                            ];
                        }, $bomDetail->item_attributes ?? []);
                        $bomDetailId = $bomDetail->bom_detail_id;
                    }

                       $checkBomExist = ItemHelper::checkItemBomExists($bomDetail->item_id, $attributes);
                       if(in_array($checkBomExist['sub_type'],['Finished Goods', 'WIP/Semi Finished'])) {
                           if(!$checkBomExist['bom_id']) {
                               $name = $bomDetail?->item?->item_name;
                               $parentName = $item?->item_name;
                               $message = "Child Bom doesn't exist for $name used under $parentName";
                               return ['status' => 422, 'message' => $message];
                           }
                       }
                       if(!in_array($checkBomExist['sub_type'], ['Expense'])) {
                           $mappingData = [
                               'so_id' => $soId,
                               'so_item_id' => $soItemId,
                               'item_id' => $bomDetail->item_id,
                               'created_by' => $createdBy,
                               'bom_id' => $bomDetail->bom_id ?? null,
                               'bom_detail_id' => $bomDetailId ?? null,
                               'item_code' => $bomDetail->item_code,
                               'order_qty' => floatval($soItemOrderQty),
                               'bom_qty' => floatval($bomDetail->qty),
                               'qty' => floatval($soQty) * floatval($bomDetail->qty),
                               'attributes' => json_encode($attributes),
                               'child_bom_id' => $checkBomExist['bom_id']
                           ];
                           $mappingExit = PiSoMapping::where([
                               ['so_id', $soId],
                               ['so_item_id', $soItemId],
                               ['item_id', $mappingData['item_id']]
                           ])
                        //    ->where('attributes', json_encode($attributes))
                           ->whereJsonContains('attributes', $attributes)
                           ->first();
                            
                           
                        //    if($soId == 27 && $soItemId == 29 && $soQty == 20) {
                        //         dd($soId, $soItemId,$soQty,$mappingData['item_id'],$attributes);
                        //     }
                           if($mappingExit) {
                            
                            //    $mappingData['order_qty'] = $mappingData['order_qty'] + $soQty;
                            //    $mappingData['bom_qty'] = $mappingData['bom_qty'] + floatval($bomDetail->qty);
                               $mappingData['qty'] = $mappingData['qty'] + $mappingExit->qty;
                           }
        
                           if ($mappingExit) {
                               $mappingExit->update($mappingData);
                           } else {
                               PiSoMapping::create($mappingData);
                           }
                           
                           

                       }
                       
                   }
            } else {
                $attributes = $bom->bomAttributes->map(fn($attribute) => [
                    'attribute_id' => $attribute->item_attribute_id,
                    'attribute_value' => intval($attribute->attribute_value),
                ])->toArray();

                $mappingData = [
                    'so_id' => $soId,
                    'so_item_id' => $soItemId,
                    'item_id' => $bom->item_id,
                    'created_by' => $createdBy,
                    'bom_id' => $bom->id ?? null,
                    'bom_detail_id' => null,
                    'item_code' => $bom->item_code,
                    'order_qty' => floatval($soQty),
                    'bom_qty' => floatval($bom->qty_produced),
                    'qty' => floatval($soQty) * floatval($bom->qty_produced ?? 1),
                    'attributes' => json_encode($attributes),
                    'child_bom_id' => null
                ];

                $mappingExit = PiSoMapping::where([
                    ['so_id', $soId],
                    ['so_item_id', $soItemId],
                    ['item_id', $mappingData['item_id']]
                ])
                ->whereJsonContains('attributes', $attributes)
                ->first();

                if($mappingExit) {
                    $mappingData['order_qty'] = $mappingData['order_qty'] + $soQty;
                    $mappingData['qty'] = $mappingData['qty'] + $mappingExit->qty;
                }

                if ($mappingExit) {
                    $mappingExit->update($mappingData);
                } else {
                    PiSoMapping::create($mappingData);
                }

            }
       }
       return ['status' => 200, 'message' => 'Saved!'];
   }


   public function processSoItemSubmit(Request $request) 
   {
       $soItems = $request->selectedData ? json_decode($request->selectedData, TRUE) : [];
       $html = view('procurement.pi.partials.item-row-so', ['soItems' => $soItems])->render();
       return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
   }

   public function revokeDocument(Request $request)
   {
       DB::beginTransaction();
       try {
           $pi = PurchaseIndent::find($request->id);
           if (isset($pi)) {
               $revoke = Helper::approveDocument($pi->book_id, $pi->id, $pi->revision_number, '', [], 0, ConstantHelper::REVOKE, $pi->grand_total_amount, get_class($pi));
               if ($revoke['message']) {
                   DB::rollBack();
                   return response() -> json([
                       'status' => 'error',
                       'message' => $revoke['message'],
                   ]);
               } else {
                   $pi->document_status = $revoke['approvalStatus'];
                   $pi->save();
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

   public function getSelectedDepartment(Request $request)
   {
        $departments = UserHelper::getDepartments($request -> user_id);
        return array(
            'selectedDeaprtmentId' => $departments['selectedDepartmentId'] 
        );
   }
}
