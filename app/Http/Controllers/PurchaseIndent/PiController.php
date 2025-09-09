<?php

namespace App\Http\Controllers\PurchaseIndent;

use DB;
use PDF;
use stdClass;
use Carbon\Carbon;
use App\Models\Bom;
use App\Models\Item;
use App\Models\Unit;
use App\Models\PiItem;
use App\Models\Vendor;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Attribute;
use App\Models\BomDetail;
use App\Models\ErpSoItem;
use App\Helpers\BookHelper;
use App\Helpers\ItemHelper;
use App\Helpers\UserHelper;
use App\Models\PiSoMapping;
use App\Models\ErpSaleOrder;
use App\Models\ErpSoItemBom;
use App\Models\Organization;
use App\Services\BomService;
use Illuminate\Http\Request;
use App\Models\AttributeGroup;
use App\Models\PurchaseIndent;
use App\Helpers\ConstantHelper;
use App\Models\PiItemAttribute;
use App\Models\PiSoMappingItem;
use App\Helpers\InventoryHelper;
use App\Http\Requests\PiRequest;
use Yajra\DataTables\DataTables;
use App\Models\PurchaseIndentMedia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ServiceParametersHelper;

class PiController extends Controller
{
    # Po List
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $selectedfyYear = Helper::getFinancialYear(Carbon::now());
            $selectColumns = ['id', 'document_date', 'document_status', 'book_id', 'store_id', 'sub_store_id', 'user_id', 'requester_type', 'revision_number', 'document_number'];
            $pis = PurchaseIndent::select($selectColumns)->withDraftListingLogic()
                ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']])
                ->latest();
            // Apply filters
            if ($request->filled('date_range')) {
                $dates = explode(' to ', $request->date_range);

                if (count($dates) === 2) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate   = Carbon::parse($dates[1])->endOfDay();

                    $pis->whereBetween('document_date', [$startDate, $endDate]);
                }
            }
            if ($request->filled('book_id')) {
                $pis->whereIn('book_id', $request->book_id);
            }
            if ($request->filled('location_id')) {
                $pis->whereIn('store_id', $request->location_id);
            }
            if ($request->filled('requester_id')) {
                $pis->whereIn('user_id', $request->requester_id);
            }
            if ($request->filled('organization_id')) {
                $pis->whereIn('organization_id', $request->organization_id);
            }


            return DataTables::of($pis)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    return view('partials.action-dropdown', [
                        'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                        'displayStatus' => $row->display_status,
                        'row' => $row,
                        'actions' => [
                            [
                                'url' => fn($r) => route('pi.edit', $r->id),
                                'icon' => 'edit-3',
                                'label' => 'View/ Edit Detail',
                            ]
                        ]
                    ])->render();
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book ? $row->book?->book_code : '';
                })
                ->filterColumn('book_name', function ($query, $keyword) {
                    $query->whereHas('book', function ($q) use ($keyword) {
                        $q->where('book_code', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('location', function ($row) {
                    return $row?->store ? $row?->store?->store_name : '';
                })
                ->filterColumn('location', function ($query, $keyword) {
                    $query->whereHas('store', function ($q) use ($keyword) {
                        $q->where('store_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('department', function ($row) {
                    if ($row->sub_store_id) {
                        return $row?->sub_store ? $row?->sub_store?->name : '';
                    } else {
                        return $row?->requester ? $row->requester?->name : '';
                    }
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? '';
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
        $serviceAlias = ConstantHelper::PI_SERVICE_ALIAS;
        $user = Helper::getAuthenticatedUser();
        $applicableOrgIds = $user->organizations->pluck('id')->toArray();
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $requesters = Helper::getOrgWiseUserAndEmployees($user->organization_id);
        $locations = InventoryHelper::getAccessibleLocations();
        $applicableOrganizations = Organization::whereIn('id', $applicableOrgIds ?? [0])
            ->where('status', ConstantHelper::ACTIVE)
            ->get(['id', 'name']);
        return view('procurement.pi.index', [
            'servicesBooks' => $servicesBooks,
            'books' => $books,
            'requesters' => $requesters,
            'locations' => $locations,
            'applicableOrganizations' => $applicableOrganizations,
        ]);
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
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $users = UserHelper::getUserSubOrdinates($user->auth_user_id ?? 0);
        $selecteduserId = $user->auth_user_id;
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);

        return view('procurement.pi.create', [
            'books' => $books,
            'users' => $users['data'],
            'selecteduserId' => $selecteduserId,
            'locations' => $locations,
            'current_financial_year' => $selectedfyYear,
        ]);
    }

    # Add item row
    public function addItemRow(Request $request)
    {
        $item = json_decode($request->item, true) ?? [];
        $componentItem = json_decode($request->component_item, true) ?? [];
        /*Check last tr in table mandatory*/
        if (isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $soTrackingRequired = strtolower($request->so_tracking_required) == 'yes' ? true : false;
        $html = view('procurement.pi.partials.item-row', compact('rowCount', 'soTrackingRequired'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $isSo = intval($request->isSo) ?? 0;
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];
        $itemAttributeArray = [];
        $piItemId = $request->pi_item_id ?? null;
        $itemAttIds = [];
        if ($piItemId) {
            $piItem = PiItem::where('id', $piItemId)->where('item_id', $item->id ?? null)->first();
            if ($piItem) {
                $itemAttIds = $piItem->attributes()->pluck('item_attribute_id')->toArray();
                $itemAttributeArray = $piItem->item_attributes_array();
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

        $html = view('procurement.pi.partials.comp-attribute', compact('item', 'rowCount', 'selectedAttr', 'isSo', 'itemAttributes'))->render();
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


    # Purchase Indent store
    public function store(PiRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
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
            $pi->procurement_type = $request->procurement_type ?? 'rm';
            $document_number = $request->document_number ?? null;

            /**/
            $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
            $regeneratedDocExist = PurchaseIndent::where('book_id', $request->book_id)
                ->where('document_number', $document_number)->first();
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
                foreach ($request->all()['components'] as $c_key => $component) {
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
                    $piDetail->required_qty = $component['qty'] ?? 0.00;
                    $piDetail->adjusted_qty = $component['adj_qty'] ?? 0.00;
                    $piDetail->indent_qty = $component['indent_qty'] ?? 0.00;
                    $piDetail->inventory_uom_code = $item->uom->name ?? null;
                    if (@$component['uom_id'] == $item->uom_id) {
                        $piDetail->inventory_uom_id = $component['uom_id'] ?? null;
                        $piDetail->inventory_uom_code = $component['uom_code'] ?? null;
                        $piDetail->inventory_uom_qty = $component['indent_qty'];
                    } else {
                        $piDetail->inventory_uom_id = $component['uom_id'] ?? null;
                        $piDetail->inventory_uom_code = $component['uom_code'] ?? null;
                        $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        if ($alUom) {
                            $piDetail->inventory_uom_qty = floatval($component['indent_qty']) * $alUom->conversion_to_inventory;
                        }
                    }

                    $piDetail->remarks = $component['remark'] ?? null;
                    if ($component['vendor_id']) {
                        $vendor = Vendor::where('id', $component['vendor_id'])->first();
                        if ($vendor) {
                            $piDetail->vendor_id = $vendor?->id ?? null;
                            $piDetail->vendor_code = $vendor?->vendor_code ?? null;
                            $piDetail->vendor_name = $vendor?->company_name ?? null;
                        }
                    }
                    $piDetail->so_id = $component['so_id'] ?? null;
                    $piDetail->save();
                    $piDetail->refresh();
                    /*Pi_So_Mapping Update*/
                    if (@$component['so_pi_mapping_item_id']) {
                        if (intval($component['so_pi_mapping_item_id']) == $piDetail->item_id) {

                            $showAttribute = intval($request->show_attribute) ?? 0;
                            $so_item_ids = $request->so_item_ids ? explode(',', $request->so_item_ids) : [];

                            if (!$showAttribute) {
                                $itemIds = $request->item_ids ? explode(',', $request->item_ids) : [];
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
                                ->whereIn('so_item_id', $so_item_ids)
                                ->whereJsonContains('attributes', $attributes)
                                ->where(function ($query) use ($piDetail) {
                                    if ($piDetail?->so_id) {
                                        $query->where('so_id', $piDetail->so_id);
                                    }
                                    if ($piDetail?->vendor_id) {
                                        $query->where('vendor_id', $piDetail->vendor_id);
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
                    foreach ($piDetail->item->itemAttributes as $itemAttribute) {
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
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                $pi->document_status = $approveDocument['approvalStatus'] ??  $pi->document_status;
            } else {
                $pi->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }

            $pi->save();

            $redirectUrl = '';
            if ($pi->document_status == ConstantHelper::APPROVED) {
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
            $currentStatus = $pi->document_status;
            $actionType = $request->action_type;
            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'PurchaseIndent', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'PiItem', 'relation_column' => 'pi_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'PiItemAttribute', 'relation_column' => 'pi_item_id']
                ];
                $a = Helper::documentAmendment($revisionData, $id);
            }
            $keys = ['deletedPiItemIds', 'deletedAttachmentIds'];
            $deletedData = [];
            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }
            if (count($deletedData['deletedAttachmentIds'])) {
                $medias = PurchaseIndentMedia::whereIn('id', $deletedData['deletedAttachmentIds'])->get();
                foreach ($medias as $media) {
                    if ($request->document_status == ConstantHelper::DRAFT) {
                        Storage::delete($media->file_name);
                    }
                    $media->delete();
                }
            }
            if (count($deletedData['deletedPiItemIds'])) {
                $piItems = PiItem::whereIn('id', $deletedData['deletedPiItemIds'])->get();
                foreach ($piItems as $piItem) {
                    if ($piItem?->so_pi_mapping_item->count()) {
                        foreach ($piItem?->so_pi_mapping_item as $so_pi_mapping_item) {
                            $so_pi_mapping_item->pi_so_mapping->pi_item_qty -= $so_pi_mapping_item->qty;
                            $so_pi_mapping_item->pi_so_mapping->save();
                            $so_pi_mapping_item->delete();
                        }
                    }
                    $piItem->attributes()->delete();
                    $piItem->delete();
                }
            }
            $pi->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $pi->document_date = $request->document_date ?? $pi->document_date;
            $pi->remarks = $request->remarks ?? null;
            $pi->save();
            if (isset($request->all()['components']) && count($request->all()['components'])) {
                foreach ($request->all()['components'] as $c_key => $component) {

                    $item = Item::find($component['item_id'] ?? null);
                    $unit = Unit::find($component['uom_id']);
                    # Purchase Order Detail Save
                    $piDetail = PiItem::find($component['pi_item_id'] ?? null) ?? new PiItem;

                    $isNewItem = false;
                    if (isset($piDetail->item_id) && $piDetail->item_id) {
                        $isNewItem = $piDetail->item_id != ($component['item_id'] ?? null);
                    }

                    $updatedQty = 0;
                    if (isset($piDetail->id)) {
                        $updatedQty =  floatval($component['qty']) - $piDetail->indent_qty;
                    }

                    $piDetail->pi_id = $pi->id;
                    if (!$piDetail->po_item) {
                        $piDetail->item_id = $component['item_id'] ?? null;
                        $piDetail->item_code = $component['item_code'] ?? null;
                        $piDetail->item_name = $component['item_name'] ?? null;
                        $piDetail->hsn_id = $component['hsn_id'] ?? null;
                        $piDetail->hsn_code = $component['hsn_code'] ?? null;
                        $piDetail->uom_id = $component['uom_id'] ?? null;
                        $piDetail->uom_code = $unit?->name ?? null;
                        $piDetail->required_qty = $component['qty'] ?? 0.00;
                        $piDetail->adjusted_qty = $component['adj_qty'] ?? 0.00;
                        $piDetail->indent_qty = $component['indent_qty'] ?? 0.00;

                        $piDetail->inventory_uom_id = $item->uom_id ?? null;
                        $piDetail->inventory_uom_code = $item->uom->name ?? null;
                        if (@$component['uom_id'] == $item->uom_id) {
                            $piDetail->inventory_uom_id = $component['uom_id'];
                            $piDetail->inventory_uom_code = $component['uom_code'] ?? null;
                            $piDetail->inventory_uom_qty = $component['indent_qty'];
                        } else {
                            $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                            if ($alUom) {
                                $piDetail->inventory_uom_qty = floatval($component['indent_qty']) * $alUom->conversion_to_inventory;
                            }
                        }
                    }
                    $piDetail->remarks = $component['remark'] ?? null;
                    if ($component['vendor_id']) {
                        $vendor = Vendor::where('id', $component['vendor_id'])->first();
                        if ($vendor) {
                            $piDetail->vendor_id = $vendor?->id ?? null;
                            $piDetail->vendor_code = $vendor?->vendor_code ?? null;
                            $piDetail->vendor_name = $vendor?->company_name ?? null;
                        }
                    }

                    $piDetail->so_id = $component['so_id'] ?? null;
                    $piDetail->save();

                    $piDetail->refresh();
                    /*Pi_So_Mapping Update*/
                    if ($updatedQty < 0) {
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
                    $so_item_ids = $request->so_item_ids ? explode(',', $request->so_item_ids) : [];

                    if (!$showAttribute) {
                        $itemIds = $request->item_ids ? explode(',', $request->item_ids) : [];
                        $so_item_ids = ErpSoItem::whereIn('sale_order_id', $so_item_ids)
                            ->whereIn('item_id', $itemIds)
                            ->pluck('id')
                            ->toArray();
                    }

                    // For new generate
                    if (!$poSiMappingItems?->count() && count($so_item_ids)) {
                        $attributes = $piDetail->attributes->map(fn($attribute) => [
                            'attribute_id' => $attribute->item_attribute_id,
                            'attribute_value' => intval($attribute->attribute_value),
                        ])->toArray();

                        $indent_qty = $piDetail->indent_qty;

                        $datas = PiSoMapping::where('item_id', $piDetail->item_id)
                            ->whereIn('so_item_id', $so_item_ids)
                            ->whereJsonContains('attributes', $attributes)
                            ->where(function ($query) use ($piDetail) {
                                if ($piDetail->so_id) {
                                    $query->where('so_id', $piDetail->so_id);
                                }
                                if ($piDetail?->vendor_id) {
                                    $query->where('vendor_id', $piDetail->vendor_id);
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

                    if ($isNewItem) {
                        PiItemAttribute::where('pi_item_id', $piDetail->id)->delete();
                    }
                    #Save component Attr
                    foreach ($piDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            // $piAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $piAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $piAttr = PiItemAttribute::firstOrNew([
                                'pi_id' => $pi->id,
                                'pi_item_id' => $piDetail->id,
                                'item_attribute_id' => $itemAttribute->id
                            ]);
                            // $piAttr = PiItemAttribute::find($piAttrId) ?? new PiItemAttribute;
                            $piAttr->item_code = $component['item_code'] ?? null;
                            $piAttr->attribute_name = $itemAttribute?->attribute_group_id;
                            $piAttr->attribute_value = $piAttrName ?? null;
                            $piAttr->save();
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
            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                //*amendmemnt document log*/
                $revisionNumber = $pi->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                $pi->revision_number = $revisionNumber;
                $pi->approval_level = 1;
                $pi->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ?? $pi->document_status;
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
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    $pi->document_status = $approveDocument['approvalStatus'] ?? $pi->document_status;
                } else {
                    $pi->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }
            $pi->save();
            $redirectUrl = '';
            if ($pi->document_status == ConstantHelper::APPROVED) {
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

    # Update after submit
    public function updateApprove(PiRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pi = PurchaseIndent::find($id);
            $actionType = $request->action_type;
            if (isset($request->all()['components']) && count($request->all()['components'])) {
                foreach ($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    # Purchase Order Detail Save
                    $piDetail = PiItem::find($component['pi_item_id'] ?? null) ?? new PiItem;
                    $updatedQty = 0;
                    if (isset($piDetail->id)) {
                        $updatedQty =  floatval($component['qty']) - $piDetail->indent_qty;
                    }
                    // $piDetail->required_qty = $component['qty'] ?? 0.00;
                    $piDetail->adjusted_qty = $component['adj_qty'] ?? 0.00;
                    $piDetail->indent_qty = $component['indent_qty'] ?? 0.00;
                    if (@$component['uom_id'] == $item->uom_id) {
                        $piDetail->inventory_uom_qty = $component['indent_qty'];
                    } else {
                        $alUom = $item->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        if ($alUom) {
                            $piDetail->inventory_uom_qty = floatval($component['indent_qty']) * $alUom->conversion_to_inventory;
                        }
                    }
                    if ($component['vendor_id']) {
                        $vendor = Vendor::where('id', $component['vendor_id'])->first();
                        if ($vendor) {
                            $piDetail->vendor_id = $vendor?->id ?? null;
                            $piDetail->vendor_code = $vendor?->vendor_code ?? null;
                            $piDetail->vendor_name = $vendor?->company_name ?? null;
                        }
                    }
                    $piDetail->save();
                    $piDetail->refresh();
                    /*Pi_So_Mapping Update*/
                    if ($updatedQty < 0) {
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
                        $piSoMapping->pi_item_qty += $allowedQty;
                        $piSoMapping->save();
                        $poSiMapItem = PiSoMappingItem::find($poSiMappingItem->id);
                        $poSiMapItem->qty += $allowedQty;
                        $poSiMapItem->save();
                        $updatedQty -= $allowedQty;
                        if (0 == $updatedQty) {
                            break;
                        }
                    }
                }
            }
            $bookId = $pi->book_id;
            $docId = $pi->id;
            $remarks = $request->remarks;
            $revisionNumber = $pi->revision_number ?? 0;
            $attachments = $request->file('attachment');
            $currentLevel = $pi->approval_level;
            $modelName = get_class($pi);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
            $pi->approval_level = $approveDocument['nextLevel'];
            $pi->document_status = $approveDocument['approvalStatus'];
            $pi->save();
            $redirectUrl = '';
            if ($pi->document_status == ConstantHelper::APPROVED) {
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
        $itemId = $request->item_id;
        $storeId = $request->store_id;
        $subStoreId = $request->sub_store_id;
        $selectedAttr = json_decode($request->selectedAttr, 200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $attributeName = [];
        $attributeValue = [];
        foreach ($item->itemAttributes as $attribute) {
            $attributeGroupId = $attribute->attribute_group_id ?? null;
            $attributeIds = $attribute->attribute_id ?? [];

            if (!is_array($attributeIds)) {
                $attributeIds = [$attributeIds];
            }

            foreach ($attributeIds as $attrId) {
                $attrId = (string) trim($attrId);
                if (in_array($attrId, $selectedAttr, true)) {
                    $attributeName[] = $attributeGroupId;
                    $attributeValue[] = $attrId;
                }
            }
        }

        $attributes = [
            'attribute_name' => $attributeName,
            'attribute_value' => $attributeValue,
        ];
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';
        if ($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
        }
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $remark = $request->remark ?? null;
        $piItemIds = $request->pi_item_id ? [$request->pi_item_id] : [];
        $storeId = $request->store_id ?? null;
        $soId = $request->so_id ?? null;
        $uniqueSoIds = PiItem::whereIn('id', $piItemIds)->whereNotNull('so_id')->pluck('so_id')->toArray();
        $inventoryStock = InventoryHelper::totalInventoryAndStock($item->id, $selectedAttr, $item?->uom_id, $storeId);
        $pendingPo = InventoryHelper::getPendingPo($item?->id, $item?->uom_id, $selectedAttr, $storeId);
        $html = view('procurement.pi.partials.comp-item-detail', compact('item', 'selectedAttr', 'remark', 'uomName', 'qty', 'specifications', 'inventoryStock', 'itemId', 'storeId', 'subStoreId', 'attributes'))->render();
        return response()->json(['data' => ['html' => $html, 'inventoryStock' => $inventoryStock, 'pendingPo' => $pendingPo], 'status' => 200, 'message' => 'fetched.']);
    }

    # Edit Po
    public function edit(Request $request, $id)
    {
        $parentUrl = request()->segments()[0];
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
        $buttons = Helper::actionButtonDisplay($pi->book_id, $pi->document_status, $pi->id, 0, $pi->approval_level, $pi->created_by ?? 0, $creatorType, $revision_number);

        $revNo = $pi->revision_number;
        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $pi->revision_number;
        }
        $selectedfyYear = Helper::getFinancialYear($pi->document_date ?? Carbon::now()->format('Y-m-d'));

        $approvalHistory = Helper::getApprovalHistory($pi->book_id, $pi->id, $revNo, 0, $createdBy);
        $view = 'procurement.pi.edit';

        if ($request->has('revisionNumber') && $request->revisionNumber != $pi->revision_number) {
            $pi = $pi->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'procurement.pi.view';
        }

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pi->document_status] ?? '';
        $departmentsData = UserHelper::getDepartments($user->auth_user_id ?? 0);
        $users = UserHelper::getUserSubOrdinates($user->auth_user_id ?? 0);
        $selecteduserId = $pi?->user_id;
        $isEdit = $buttons['submit'];
        if (!$isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true : false;
        }
        $locations = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $saleOrders = ErpSaleOrder::whereIn('id', $pi->so_id ?? [])
            ->get();

        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($pi->book_id, $pi->document_date);
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        $soTrackingRequired = in_array('yes', $parameters['so_tracking_required']) ? true : false;
        return view($view, [
            'isEdit' => $isEdit,
            'books' => $books,
            'pi' => $pi,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revision_number' => $revision_number,
            'departments' => $departmentsData['departments'],
            'users' => $users['data'],
            'selecteduserId' => $selecteduserId,
            'locations' => $locations,
            'saleOrders' => $saleOrders,
            'current_financial_year' => $selectedfyYear,
            'soTrackingRequired' => $soTrackingRequired
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
        $pi = PurchaseIndent::with(['pi_items', 'book'])->findOrFail($id);

        $imagePath = public_path('assets/css/midc-logo.jpg');
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pi->document_status] ?? '';
        $pdf = PDF::loadView(
            'pdf.pi',
            [
                'pi' => $pi,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress,
                'imagePath' => $imagePath,
                'docStatusClass' => $docStatusClass,
                'user' => $user
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
        $soItems = ErpSoItem::where(function ($query) {
            $query->whereDoesntHave('soItemMapping')
                ->orWhereHas('soItemMapping', function ($subQuery) {
                    $subQuery->select(DB::raw('SUM(pi_item_qty)'))
                        ->groupBy('so_item_id')
                        ->havingRaw('SUM(pi_item_qty) < SUM(qty)');
                });
        })
            ->whereColumn('invoice_qty', '<', 'order_qty')
            ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $docNumber) {
                $subQuery->whereIn('book_id', $applicableBookIds)
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

        if (!$isAttribute) {
            $groupByColumns = ['sale_order_id', 'item_id', 'item_name', 'item_code'];
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
        $procurementType = $request->procurement_type ?? 'rm';
        $isAttribute = intval($request->is_attribute) ?? 0;
        $user = Helper::getAuthenticatedUser();
        $ids = json_decode($request->ids, true) ?? [];
        $ids = array_values(array_unique($ids));
        if (!$isAttribute) {
            $selectedData = json_decode($request->selected_items, true);
            $saleOrderIds = array_column($selectedData, 'sale_order_id');
            $itemIds = array_column($selectedData, 'item_id');
            $ids = ErpSoItem::whereIn('sale_order_id', $saleOrderIds)
                ->whereIn('item_id', $itemIds)
                ->pluck('id')
                ->toArray();
        }
        $soItems = ErpSoItem::whereIn('id', $ids)
            ->where(function ($query) {
                $query->whereDoesntHave('soItemMapping')
                    ->orWhereHas('soItemMapping', function ($subQuery) {
                        $subQuery->select(DB::raw('SUM(pi_item_qty)'))
                            ->groupBy('so_item_id')
                            ->havingRaw('SUM(pi_item_qty) < SUM(qty)');
                    });
            })
            ->get();
        $soItemIdArr = [];
        $createdBy = $user?->auth_user_id;
        DB::beginTransaction();
        if ($procurementType == 'rm') {
            // This for the RM
            foreach ($soItems as $key => $soItem) {
                $soItemIdArr[] = $soItem->id;
                $soId = $soItem?->header?->id ?? null;
                $soItemId = $soItem->id;
                $itemId = $soItem->item_id;
                $q = $soItem?->soItemMapping->count() ? $soItem?->soItemMapping->first()->order_qty : 0;
                $avlQty = $soItem->order_qty - $soItem->invoice_qty - $q;
                $avlQty = max($avlQty, 0);
                if ($avlQty > 0) {
                    $soAttribute = $soItem->attributes->map(fn($soAttribute) => [
                        'attribute_id' => $soAttribute->item_attribute_id,
                        'attribute_value' => intval($soAttribute->attr_value)
                    ])->toArray();
                    $res = $this->syncPiSoMapping($soId, $soItemId, $itemId, $soAttribute, $avlQty, $createdBy, $avlQty);
                    if ($res['status'] == 422) {
                        DB::rollBack();
                        return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => $res['message']]);
                    }
                }
            }

            do {
                $soProcessItems = PiSoMapping::whereIn('so_item_id', $soItemIdArr)
                    ->where('created_by', $user->auth_user_id)
                    ->whereNotNull('child_bom_id')
                    ->get();
                foreach ($soProcessItems as $soProcessItem) {
                    $soId = $soProcessItem->so_id;
                    $soItemId = $soProcessItem->so_item_id;
                    $itemId = $soProcessItem->item_id;
                    $attributes = json_decode($soProcessItem->attributes, true);
                    $soItemOrderQty = $soProcessItem->order_qty;
                    $mappingExit = PiSoMapping::where([
                        ['so_id', $soId],
                        ['so_item_id', $soItemId],
                        ['item_id', $itemId]
                    ])
                        ->whereJsonContains('attributes', $attributes)
                        ->first();
                    $updatedQty = $soProcessItem->qty;
                    if (isset($mappingExit) && $mappingExit) {
                        $updatedQty = $mappingExit->qty;
                    }

                    $res = $this->syncPiSoMapping($soId, $soItemId, $itemId, $attributes, $updatedQty, $createdBy, $soItemOrderQty);
                    if ($res['status'] == 422) {
                        DB::rollBack();
                        return response()->json(['data' => ['pos' => ''], 'status' => 422, 'message' => $res['message']]);
                    }
                    $soProcessItem->delete();
                }
            } while (PiSoMapping::whereIn('so_item_id', $soItemIdArr)
                ->where('created_by', $user->auth_user_id)
                ->whereNotNull('child_bom_id')
                ->exists()
            );
            $soTracking = $request?->so_tracking_required ?? 'no';
            if ($soTracking === 'yes') {
                $soProcessItems = PiSoMapping::whereIn('so_item_id', $soItemIdArr)
                    ->select(
                        'erp_pi_so_mapping.vendor_id',
                        'erp_pi_so_mapping.so_id',
                        'erp_pi_so_mapping.item_id',
                        DB::raw('erp_pi_so_mapping.attributes'),
                        DB::raw('ROUND(SUM(erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty),6) as total_qty')
                    )
                    ->groupBy('erp_pi_so_mapping.so_id', 'erp_pi_so_mapping.item_id', 'erp_pi_so_mapping.attributes', 'erp_pi_so_mapping.vendor_id')
                    ->havingRaw('total_qty > 0')
                    ->get();
            } else {
                $soProcessItems = PiSoMapping::whereIn('so_item_id', $soItemIdArr)
                    ->select(
                        DB::raw('NULL as so_id'),
                        'erp_pi_so_mapping.vendor_id',
                        'erp_pi_so_mapping.item_id',
                        DB::raw('erp_pi_so_mapping.attributes'),
                        DB::raw('ROUND(SUM(erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty),6) as total_qty')
                    )
                    ->groupBy('erp_pi_so_mapping.item_id', 'erp_pi_so_mapping.attributes', 'erp_pi_so_mapping.vendor_id')
                    ->havingRaw('total_qty > 0')
                    ->get();
            }
            $html = view('procurement.pi.partials.so-process-data', ['soTracking' => $soTracking, 'soProcessItems' => $soProcessItems])->render();
        } else {
            // this is for the FG
            foreach ($soItems as $key => $soItem) {
                $q = $soItem?->soItemMapping->count() ? $soItem?->soItemMapping->first()->order_qty : 0;
                $avlQty = $soItem->order_qty - $soItem->invoice_qty - $q;
                $avlQty = max($avlQty, 0);
                $attributes = collect($soItem->item_attributes ?? [])->map(function ($attribute) {
                    return [
                        'attribute_id' => (int) ($attribute->item_attribute_id ?? 0),
                        'attribute_value' => (int) ($attribute->attr_value ?? 0),
                    ];
                })->filter(function ($attr) {
                    return $attr['attribute_id'] > 0 && $attr['attribute_value'] > 0;
                })->values()->all();

                $mappingData = [
                    'so_id' => $soItem->sale_order_id ?? null,
                    'so_item_id' => $soItem->id ?? null,
                    'item_id' => $soItem->item_id,
                    'created_by' => $createdBy,
                    'bom_id' => $soItem->bom_id ?? null,
                    'bom_detail_id' => null,
                    'vendor_id' =>  null,
                    'item_code' => $soItem->item_code,
                    'order_qty' => floatval($avlQty),
                    'bom_qty' => 0,
                    'qty' => floatval($avlQty),
                    'attributes' => json_encode($attributes),
                    'child_bom_id' => null
                ];
                $mappingExit = PiSoMapping::where([
                    ['so_id', $mappingData['so_id']],
                    ['so_item_id', $mappingData['so_item_id']],
                    ['item_id', $mappingData['item_id']]
                ])
                    ->whereJsonContains('attributes', $attributes)
                    ->first();
                if ($mappingExit) {
                    $mappingData['qty'] = $mappingData['qty'] + $mappingExit->qty;
                }
                if ($mappingExit) {
                    $mappingExit->update($mappingData);
                } else {
                    PiSoMapping::create($mappingData);
                }
            }

            $soTracking = $request?->so_tracking_required ?? 'no';
            if ($soTracking === 'yes') {
                $soProcessItems = PiSoMapping::whereIn('so_item_id', $ids)
                    ->select(
                        'erp_pi_so_mapping.vendor_id',
                        'erp_pi_so_mapping.so_id',
                        'erp_pi_so_mapping.item_id',
                        DB::raw('erp_pi_so_mapping.attributes'),
                        DB::raw('ROUND(SUM(erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty),6) as total_qty')
                    )
                    ->groupBy('erp_pi_so_mapping.so_id', 'erp_pi_so_mapping.item_id', 'erp_pi_so_mapping.attributes', 'erp_pi_so_mapping.vendor_id')
                    ->havingRaw('total_qty > 0')
                    ->get();
            } else {
                $soProcessItems = PiSoMapping::whereIn('so_item_id', $ids)
                    ->select(
                        DB::raw('NULL as so_id'),
                        'erp_pi_so_mapping.vendor_id',
                        'erp_pi_so_mapping.item_id',
                        DB::raw('erp_pi_so_mapping.attributes'),
                        DB::raw('ROUND(SUM(erp_pi_so_mapping.qty - erp_pi_so_mapping.pi_item_qty),6) as total_qty')
                    )
                    ->groupBy('erp_pi_so_mapping.item_id', 'erp_pi_so_mapping.attributes', 'erp_pi_so_mapping.vendor_id')
                    ->havingRaw('total_qty > 0')
                    ->get();
            }
            $storeId = $request->store_id ?? null;
            $soTrackingRequired = strtolower($soTracking) == 'yes' ? true : false;
            $html = view('procurement.pi.partials.fg-item-row', ['soTrackingRequired' => $soTrackingRequired, 'soItems' => $soProcessItems, 'storeId' => $storeId])->render();
        }

        DB::commit();
        return response()->json(['data' => ['pos' => $html, 'procurement_type' => $procurementType], 'status' => 200, 'message' => "fetched!"]);
    }

    /**
     * Sync or Create PiSoMapping
     */
    private function syncPiSoMapping($soId, $soItemId, $itemId, $attr, $soQty, $createdBy, $soItemOrderQty)
    {
        $so = ErpSaleOrder::find($soId);
        $item = Item::find($itemId);
        $checkBomExist = ItemHelper::checkItemBomExists($itemId, $attr);
        if ($checkBomExist['bom_id']) {
            $bom = Bom::find($checkBomExist['bom_id']);
            $bufferPerc = ItemHelper::getBomSafetyBufferPerc($bom->id);

            $bomDetails = (strtolower($bom->customizable) === 'no')
                ? BomDetail::where('bom_id', $checkBomExist['bom_id'])->get()
                : ErpSoItemBom::where('bom_id', $checkBomExist['bom_id'])
                ->where('sale_order_id', $soId)
                ->where('so_item_id', $soItemId)
                ->get();
            if (strtolower($bom->customizable) === 'yes' && $bomDetails->isEmpty()) {
                $bomDetails = BomDetail::where('bom_id', $checkBomExist['bom_id'])->get();
            }

            // as discussed with inder sir, this is done due to the child BOM contains type: job work where parent has has in-house type.
            // if($bom->production_type == 'In-house') {
            foreach ($bomDetails as $bomDetail) {

                $bomDetailId = null;
                $vendorId = null;
                $attributes = [];
                if ($bomDetail instanceof \App\Models\BomDetail) {
                    $attributes = $bomDetail->attributes->map(fn($attribute) => [
                        'attribute_id' => intval($attribute->item_attribute_id),
                        'attribute_value' => intval($attribute->attribute_value),
                    ])->toArray();
                    $bomDetailId = $bomDetail->id;
                    $vendorId = $bomDetail?->vendor_id;
                } elseif ($bomDetail instanceof \App\Models\ErpSoItemBom) {
                    $attributes = array_map(function ($attribute) {
                        return [
                            'attribute_id' => intval($attribute['attribute_id']),
                            'attribute_value' => intval($attribute['attribute_value_id']),
                        ];
                    }, $bomDetail->item_attributes ?? []);
                    $bomDetailId = $bomDetail->bom_detail_id;
                    $vendorId = $bomDetail?->bomDetail?->vendor_id;
                }

                $checkBomExist = ItemHelper::checkItemBomExists($bomDetail->item_id, $attributes);
                if (in_array($checkBomExist['sub_type'], ['Finished Goods', 'WIP/Semi Finished'])) {
                    if (!$checkBomExist['bom_id']) {
                        $name = $bomDetail?->item?->item_name;
                        $parentName = $item?->item_name;
                        $message = "Child Bom doesn't exist for $name used under $parentName";
                        return ['status' => 422, 'message' => $message];
                    }
                }
                $requiredQty = floatval($soQty) * floatval($bomDetail->qty);
                if ($bufferPerc > 0) {
                    $requiredQty += $requiredQty * $bufferPerc / 100;
                }
                $requiredQty = $requiredQty;
                if (!in_array($checkBomExist['sub_type'], ['Expense'])) {
                    $mappingData = [
                        'so_id' => $soId,
                        'so_item_id' => $soItemId,
                        'item_id' => $bomDetail->item_id,
                        'created_by' => $createdBy,
                        'bom_id' => $bomDetail->bom_id ?? null,
                        'bom_detail_id' => $bomDetailId ?? null,
                        'vendor_id' => $vendorId ?? null,
                        'item_code' => $bomDetail->item_code,
                        'order_qty' => floatval($soItemOrderQty),
                        'bom_qty' => floatval($bomDetail->qty),
                        'qty' => $requiredQty,
                        'attributes' => json_encode($attributes),
                        'child_bom_id' => $checkBomExist['bom_id']
                    ];
                    $mappingExit = PiSoMapping::where([
                        ['so_id', $soId],
                        ['so_item_id', $soItemId],
                        ['item_id', $mappingData['item_id']]
                    ])
                        ->whereJsonContains('attributes', $attributes)
                        ->first();
                    if ($mappingExit) {
                        $mappingData['qty'] = $mappingData['qty'] + $mappingExit->qty;
                    }
                    if ($mappingExit) {
                        $mappingExit->update($mappingData);
                    } else {
                        PiSoMapping::create($mappingData);
                    }
                }
            }

            // }

            // as discussed with inder sir, this is done due to the child BOM contains type: job work where parent has has in-house type.

            // else {
            //     $attributes = $bom->bomAttributes->map(fn($attribute) => [
            //         'attribute_id' => $attribute->item_attribute_id,
            //         'attribute_value' => intval($attribute->attribute_value),
            //     ])->toArray();

            //     $requiredQty = floatval($soQty) * floatval($bom->qty_produced ?? 1);
            //     if($bufferPerc > 0) {
            //         $requiredQty += $requiredQty*$bufferPerc/100;
            //     }
            //     $requiredQty = ceil($requiredQty);

            //     $mappingData = [
            //         'so_id' => $soId,
            //         'so_item_id' => $soItemId,
            //         'item_id' => $bom->item_id,
            //         'created_by' => $createdBy,
            //         'bom_id' => $bom->id ?? null,
            //         'bom_detail_id' => null,
            //         'vendor_id' => null,
            //         'item_code' => $bom->item_code,
            //         'order_qty' => floatval($soQty),
            //         'bom_qty' => floatval($bom->qty_produced),
            //         'qty' => $requiredQty,
            //         'attributes' => json_encode($attributes),
            //         'child_bom_id' => null
            //     ];

            //     $mappingExit = PiSoMapping::where([
            //         ['so_id', $soId],
            //         ['so_item_id', $soItemId],
            //         ['item_id', $mappingData['item_id']]
            //     ])
            //     ->whereJsonContains('attributes', $attributes)
            //     ->first();

            //     if($mappingExit) {
            //         $mappingData['order_qty'] = $mappingData['order_qty'] + $soQty;
            //         $mappingData['qty'] = $mappingData['qty'] + $mappingExit->qty;
            //     }

            //     if ($mappingExit) {
            //         $mappingExit->update($mappingData);
            //     } else {
            //         PiSoMapping::create($mappingData);
            // }
            // }
        }
        return ['status' => 200, 'message' => 'Saved!'];
    }


    public function processSoItemSubmit(Request $request)
    {
        $storeId = $request->store_id ?? null;
        $selectedData = $request->selectedData ?? $request->selected_items ?? [];
        $soItems = [];

        if (is_array($selectedData)) {
            $soItems = $selectedData;
        } elseif (is_string($selectedData)) {
            $decoded = json_decode($selectedData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $soItems = $decoded;
            }
        }

        $isAttribute = intval($request->is_attribute) ?? 0;
        $extendedItems = $soItems;

        if (!$isAttribute) {
            foreach ($soItems as $index => $item) {
                if (!empty($item['main_so_item']) && !empty($item['so_item_ids'])) {
                    $soSubItems = ErpSoItem::where('sale_order_id', $item['so_id'])
                        ->whereIn('id', $item['so_item_ids'])
                        ->get();

                    $newItems = [];
                    unset($item['so_item_ids']);

                    foreach ($soSubItems as $soItem) {
                        $newItem = $item;
                        $newItem['item_id']    = $soItem->item_id;
                        $newItem['item_name']  = $soItem->item_name;
                        $newItem['item_code']  = $soItem->item_code;
                        $newItem['uom_id']     = $soItem->uom_id;
                        $newItem['uom_name']   = $soItem->uom->name;
                        $newItem['total_qty']  = $soItem->order_qty;
                        $newItem['so_item_id'] = $soItem->id;
                        $newItem['attribute']  = $soItem->item_attributes_array();
                        $newItems[] = $newItem;
                    }

                    array_splice($extendedItems, $index, 1, $newItems);
                }
            }
        }

        $soTrackingRequired = strtolower($request->so_tracking_required) == 'yes';

        if ($soTrackingRequired) {
            foreach ($extendedItems as &$piSoItemMapping) {
                $attributes = array_map(function ($item) {
                    return [
                        'attribute_id'    => $item['id'],
                        'attribute_value' => $item['values_data'][0]['id'] ?? null
                    ];
                }, $piSoItemMapping['attributes'] ?? []);

                $datas = PiSoMapping::where('item_id', $piSoItemMapping['item_id'])
                    ->when(count($attributes), function ($query) use ($attributes) {
                        $query->whereJsonContains('attributes', $attributes);
                    })
                    ->where(function ($query) use ($piSoItemMapping) {
                        if ($piSoItemMapping['so_id']) {
                            $query->where('so_id', $piSoItemMapping['so_id']);
                        }
                        if ($piSoItemMapping['vendor_id']) {
                            $query->where('vendor_id', $piSoItemMapping['vendor_id']);
                        }
                    })
                    ->first();

                if ($datas?->bomDetail) {
                    $piSoItemMapping['remark'] = $datas->bomDetail->remark;
                }
                unset($piSoItemMapping);
            }
        }

        $rowCount = intval($request->rowCount) ? intval($request->rowCount) + 1 : 1;
        $html = view('procurement.pi.partials.item-row-so', [
            'soItems'            => $extendedItems,
            'soTrackingRequired' => $soTrackingRequired,
            'storeId'            => $storeId,
            'rowCount'           => $rowCount,
            'is_pull'            => true,
        ])->render();

        return response()->json([
            'data'    => ['pos' => $html],
            'status'  => 200,
            'message' => "fetched!"
        ]);
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
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $pi->document_status = $revoke['approvalStatus'];
                    $pi->save();
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Revoked succesfully',
                    ]);
                }
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Document found',
                ]);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function getSelectedDepartment(Request $request)
    {
        $departments = UserHelper::getDepartments($request->user_id ?? 0);
        return array(
            'selectedDeaprtmentId' => $departments['selectedDepartmentId']
        );
    }

    public function piReport(Request $request)
    {
        $pathUrl = route('pi.index');
        $orderType = [ConstantHelper::PI_SERVICE_ALIAS];
        $puchaseIndents = PurchaseIndent::with('items')->withDraftListingLogic()->orderByDesc('id');
        //Vendor Filter
        $puchaseIndents = $puchaseIndents->when($request->vendor_id, function ($vendorQuery) use ($request) {
            $vendorQuery->where('vendor_id', $request->vendor_id);
        });
        //Book Filter
        $puchaseIndents = $puchaseIndents->when($request->book_id, function ($bookQuery) use ($request) {
            $bookQuery->where('book_id', $request->book_id);
        });
        //Document Id Filter
        $puchaseIndents = $puchaseIndents->when($request->document_number, function ($docQuery) use ($request) {
            $docQuery->where('document_number', 'LIKE', '%' . $request->document_number . '%');
        });
        //Location Filter
        $puchaseIndents = $puchaseIndents->when($request->location_id, function ($docQuery) use ($request) {
            $docQuery->where('store_id', $request->location_id);
        });
        //Company Filter
        $puchaseIndents = $puchaseIndents->when($request->company_id, function ($docQuery) use ($request) {
            $docQuery->where('store_id', $request->company_id);
        });
        //Organization Filter
        $puchaseIndents = $puchaseIndents->when($request->organization_id, function ($docQuery) use ($request) {
            $docQuery->where('organization_id', $request->organization_id);
        });
        //Document Status Filter
        $puchaseIndents = $puchaseIndents->when($request->doc_status, function ($docStatusQuery) use ($request) {
            $searchDocStatus = [];
            if ($request->doc_status === ConstantHelper::DRAFT) {
                $searchDocStatus = [ConstantHelper::DRAFT];
            } else if ($request->doc_status === ConstantHelper::SUBMITTED) {
                $searchDocStatus = [ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED];
            } else {
                $searchDocStatus = [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED];
            }
            $docStatusQuery->whereIn('document_status', $searchDocStatus);
        });
        //Date Filters
        $dateRange = $request->date_range ??  Carbon::now()->startOfMonth()->format('Y-m-d') . " to " . Carbon::now()->endOfMonth()->format('Y-m-d');
        $puchaseIndents = $puchaseIndents->when($dateRange, function ($dateRangeQuery) use ($request, $dateRange) {
            $dateRanges = explode('to', $dateRange);
            if (count($dateRanges) == 2) {
                $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                $toDate = Carbon::parse(trim($dateRanges[1]))->format('Y-m-d');
                $dateRangeQuery->whereDate('document_date', ">=", $fromDate)->where('document_date', '<=', $toDate);
            } else {
                $fromDate = Carbon::parse(trim($dateRanges[0]))->format('Y-m-d');
                $dateRangeQuery->whereDate('document_date', $fromDate);
            }
        });
        //Item Id Filter
        $puchaseIndents = $puchaseIndents->when($request->item_id, function ($itemQuery) use ($request) {
            $itemQuery->withWhereHas('items', function ($itemSubQuery) use ($request) {
                $itemSubQuery->where('item_id', $request->item_id)
                    //Compare Item Category
                    ->when($request->item_category_id, function ($itemCatQuery) use ($request) {
                        $itemCatQuery->whereHas('item', function ($itemRelationQuery) use ($request) {
                            $itemRelationQuery->where('category_id', $request->category_id)
                                //Compare Item Sub Category
                                ->when($request->item_sub_category_id, function ($itemSubCatQuery) use ($request) {
                                    $itemSubCatQuery->where('subcategory_id', $request->item_sub_category_id);
                                });
                        });
                    });
            });
        });
        $puchaseIndents = $puchaseIndents->get();
        $processedSalesOrder = collect([]);
        foreach ($puchaseIndents as $pi) {
            foreach ($pi->items as $piItem) {
                $reportRow = new stdClass();
                //Header Details
                $header = $piItem->pi;
                $reportRow->id = $header->id;
                $reportRow->book_name = $header->book_code;
                $reportRow->document_number = $header->document_number;
                $reportRow->document_date = $header->document_date;
                $reportRow->store_name = $header->store?->store_name;
                $reportRow->sub_store_name = $header->sub_store?->name;
                $reportRow->requester_type = $header->requester_type;
                $reportRow->requester_name = $header->requester_name();
                $reportRow->vendor_currency = $header->currency_code;
                $reportRow->payment_terms_name = $header->payment_term_code;
                //Item Details
                $reportRow->item_name = $piItem->item_name;
                $reportRow->item_code = $piItem->item_code;
                $reportRow->hsn_code = $piItem->hsn?->code;
                $reportRow->uom_name = $piItem->uom?->name;
                //Amount Details
                $reportRow->po_qty = number_format($piItem->indent_qty, 2);
                $reportRow->mi_qty = number_format($piItem->mi_qty ?? 0.00, 2);
                $reportRow->so_qty = number_format($piItem->order_qty ?? 0.00, 2);
                $reportRow->so_no = $piItem?->so ? $piItem?->so?->book_code . "-" . $piItem?->so?->document_number : " ";
                //Attributes UI
                $attributesUi = '';
                if (count($piItem->attributes) > 0) {
                    foreach ($piItem->attributes as $soAttribute) {
                        $attrName = AttributeGroup::find($soAttribute->attribute_group_id)?->name;
                        $attrValue = Attribute::find($soAttribute->attribute_value)?->value;
                        $attributesUi .= "<span class='badge rounded-pill badge-light-primary' > $attrName : $attrValue </span>";
                    }
                } else {
                    $attributesUi = 'N/A';
                }
                $reportRow->attributes = $attributesUi;
                //Main header Status
                $reportRow->status = $header->document_status;
                $processedSalesOrder->push($reportRow);
            }
        }
        return DataTables::of($processedSalesOrder)->addIndexColumn()
            ->editColumn('status', function ($row) use ($orderType) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->status ?? ConstantHelper::DRAFT];
                $displayStatus = ucfirst($row->status);
                $editRoute = null;
                $editRoute = route('pi.edit', ['id' => $row->id]);
                return "
                <div style='text-align:right;'>
                    <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <a href='" . $editRoute . "'>
                            <i class='cursor-pointer' data-feather='eye'></i>
                        </a>
                </div>
            ";
            })
            ->rawColumns(['attributes', 'delivery_schedule', 'status'])
            ->make(true);
    }

    public function analyzeSoItem(Request $request)
    {
        $ids = json_decode($request->ids, true) ?? [];
        $ids = array_values(array_unique($ids));
        $isAttribute = intval($request->is_attribute) ?? 0;

        if (!$isAttribute) {
            $selectedData = json_decode($request->selected_items, true);
            $ids = ErpSoItem::where(function ($query) use ($selectedData) {
                foreach ($selectedData as $selectedItem) {
                    $query->orWhere(function ($q) use ($selectedItem) {
                        $q->where('sale_order_id', $selectedItem['sale_order_id'])
                            ->where('item_id', $selectedItem['item_id']);
                    });
                }
            })->pluck('id')->toArray();
        }

        $soItems = ErpSoItem::whereIn('id', $ids)->get();
        $soItemIds = $soItems->pluck('id')->toArray();

        $bomService = new BomService;
        $femifishedItems = $bomService->getRawMaterialBreakdown($soItemIds, 'semi');

        if (!$isAttribute) {
            $temp = [];
            foreach ($femifishedItems as $soItemId => $item) {
                $fg = $item['semi_finished_goods']['fg'];
                $key = $fg['so_id'] . '_' . $fg['bom_id'];
                $temp[$key][] = [
                    'so_item_id' => $soItemId,
                    'fg' => $fg
                ];
            }

            $grouped = [];
            foreach ($temp as $key => $items) {
                if (count($items) > 0) {
                    $soId = $items[0]['fg']['so_id'];
                    $bomId = $items[0]['fg']['bom_id'];
                    $fg = $items[0]['fg'];
                    $fg['so_item_ids'] = [$items[0]['so_item_id']];
                    for ($i = 1; $i < count($items); $i++) {
                        $fg['total_qty'] += (float) $items[$i]['fg']['total_qty'];
                        $fg['so_item_ids'][] = $items[$i]['so_item_id'];
                    }
                    $fg['so_item_ids'] = implode(',', $fg['so_item_ids']);
                    if (count($items) > 1) {
                        $fg['attribute'] = [];
                    }
                    $grouped[$soId] = [
                        'semi_finished_goods' => [
                            'fg' => $fg
                        ]
                    ];
                }
            }
            $femifishedItems = $grouped;
        } else {
            $newGrouped = [];
            foreach ($femifishedItems as $soItemId => $femifishedItem) {
                $fg = $femifishedItem['semi_finished_goods']['fg'];
                $fg['so_item_id'] = $soItemId;
                $newGrouped[$soItemId] = [
                    'semi_finished_goods' => [
                        'fg' => $fg
                    ]
                ];
            }
            $femifishedItems = $newGrouped;
        }

        $html = view('procurement.pi.partials.analyze-item', [
            'femifishedItems' => $femifishedItems,
            'isAttribute' => $isAttribute
            //  'rowCount' => $rowCount
        ])->render();

        return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
    }
}
