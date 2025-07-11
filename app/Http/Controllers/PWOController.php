<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\FinancialPostingHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ServiceParametersHelper;
use App\Models\MfgOrder;
use App\Models\MoItem;
use App\Models\AttributeGroup;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Bom;
use App\Models\Organization;
use App\Models\Address;
use App\Http\Requests\MoRequest;
use App\Http\Requests\PwoRequest;
use App\Models\BomDetail;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpSoItem;
use App\Models\MoBomMapping;
use App\Models\MoItemAttribute;
use App\Models\MoItemLocation;
use App\Models\MoMedia;
use App\Models\MoProduct;
use App\Models\MoProductAttribute;
use App\Models\PwoSoMapping;
use App\Models\StockLedger;
use App\Models\Attribute;
use App\Models\ErpPwoItem;
use App\Models\ErpPwoItemAttribute;
use App\Models\ErpSoItemBom;
use App\Models\ErpStore;
use App\Models\ProductionRoute;
use App\Models\PwoBomMapping;
use App\Models\PwoStationConsumption;
use App\Models\SubType;
use App\Models\Unit;
use App\Services\BomService;
use Yajra\DataTables\DataTables;
use DB;
use PDF;
use Illuminate\Support\Facades\Storage;
use NumberToWords\Legacy\Numbers\Words\Locale\Es;

class PWOController extends Controller
{
     # Bill of material list
     public function index(Request $request)
     {
         $parentUrl = request()->segments()[0];
         if (request()->ajax()) {
             $boms = ErpProductionWorkOrder::withDraftListingLogic();
             return DataTables::of($boms)
                 ->addIndexColumn()
                 ->editColumn('document_status', function ($row) {
                    return view('partials.action-dropdown', [
                        'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                        'displayStatus' => $row->display_status,
                        'row' => $row,
                        'actions' => [
                            [
                                'url' => fn($r) => route('pwo.edit', $r->id),
                                'icon' => 'edit-3',
                                'label' => 'View/ Edit Detail',
                            ]
                        ]
                    ])->render();
                })
                 ->addColumn('book_name', function ($row) {
                     return $row->book ? $row->book?->book_code : 'N/A';
                 })
                 ->addColumn('items', function ($row) {
                    $firstItemName = $row?->mapping[0]?->item?->item_name ?? '';
                    $count = $row?->mapping?->count() ?? 0;
                    if ($count > 1) {
                        $remaining = intval($count - 1);
                        return $firstItemName . " <span class='badge rounded-pill badge-light-primary badgeborder-radius'>+$remaining</span>";
                    }
                    return $firstItemName;
                })
                ->addColumn('so_no', function ($row) {
                    $bookCode = strtoupper($row?->last_so()?->book_code);
                    return $row?->last_so() ? ($bookCode.' - '. $row?->last_so()?->document_number)  : '';
                })
                ->addColumn('location', function ($row) {
                    return $row?->location->store_name ?? '';
                })
                 ->editColumn('document_date', function ($row) {
                     return $row->getFormattedDate('document_date') ?? 'N/A';
                 })
                 ->rawColumns(['document_status','items'])
                 ->make(true);
         }
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
         return view('pwo.index', ['servicesBooks' => $servicesBooks]);
     }
 
     # Bill of material Create
     public function create(Request $request)
     {
         $parentUrl = request()->segments()[0];
         $servicesAliasParam = ConstantHelper::PWO_SERVICE_ALIAS;
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);
         if (count($servicesBooks['services']) == 0) {
             return redirect()->back();
         }
         $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
         $locations = InventoryHelper::getAccessibleLocations();
         return view('pwo.create', [
             'books' => $books,
             'servicesBooks' => $servicesBooks,
             'serviceAlias' => $servicesAliasParam,
             'locations' => $locations
         ]);
     }
 
     #Bill of material store
     public function store(PwoRequest $request)
     {
         DB::beginTransaction();
         try {
             # Mo Header Save
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }
             $user = Helper::getAuthenticatedUser();
             $organization = Organization::where('id', $user->organization_id)->first(); 
             $mo = new ErpProductionWorkOrder;
             $mo->organization_id = $organization->id;
             $mo->group_id = $organization->group_id;
             $mo->company_id = $organization->company_id;
             $mo->revision_number = $request->revision_number ?? 0;
             $mo->remarks = $request->remarks;
             $mo->location_id = $request->store_id;
             $mo->station_wise_consumption = 'yes';
             $mo->so_tracking_required = 'yes';

             # Extra Column
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
             $regeneratedDocExist = ErpProductionWorkOrder::where('book_id', $request->book_id)
                                 ->where('document_number', $document_number)
                                 ->first();
                 //Again check regenerated doc no
                 if (isset($regeneratedDocExist)) {
                     return response()->json([
                         'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                         'error' => "",
                     ], 422);
                 }
 
             $mo->doc_number_type = $numberPatternData['type'];
             $mo->doc_reset_pattern = $numberPatternData['reset_pattern'];
             $mo->doc_prefix = $numberPatternData['prefix'];
             $mo->doc_suffix = $numberPatternData['suffix'];
             $mo->doc_no = $numberPatternData['doc_no'];
             /**/
 
             $mo->book_id = $request->book_id;
             $mo->book_code = $request->book_code;
             $mo->document_number = $document_number;
             $mo->document_date = $request->document_date ?? now(); 
             $mo->save();
 
             if (isset($request->all()['components'])) {
                // $soIds = [];
                foreach($request->all()['components'] as $component) {
                    # Save PWO SO Mapping
                    // $soIds[] = $component['so_id'];
                    $item = Item::find($component['item_id'] ?? null);
                    $pwoSoMapping = new PwoSoMapping;
                    $pwoSoMapping->pwo_id = $mo->id;
                    if(isset($component['so_id']) && $component['so_id']) {
                        $pwoSoMapping->so_id = $component['so_id'] ?? null;
                    }
                    if(isset($component['so_item_id']) && $component['so_item_id']) {
                        $pwoSoMapping->so_item_id = $component['so_item_id'] ?? null;
                    }
                    $pwoSoMapping->store_id = $component['store_id'] ?? null;
                    // if(intval($component['main_so_item'])) {
                    // if (!empty(intval($component['main_so_item']))) {
                    //     $pwoSoMapping->main_so_item = true;
                    // } else {
                    //     $pwoSoMapping->main_so_item = false;
                    // }
                    $pwoSoMapping->main_so_item = isset($component['main_so_item']) && intval($component['main_so_item']) != 0;
                    $pwoSoMapping->item_id = $component['item_id'] ?? null;
                    $pwoSoMapping->item_code = $component['item_code'] ?? null;
                    $attributes = [];
                    $selectedAttributes = [];
                    foreach ($item?->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $attribute = Attribute::find($component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'] ?? null);
                            $attributes[] = [
                                'item_attribute_id' => $itemAttribute?->id,
                                'attribute_group_id' => $itemAttribute?->attribute_group_id, // Color ID
                                'attribute_group_name' => $itemAttribute?->attributeGroup->name, // Color
                                'attribute_id' => $attribute?->id, // Red ID
                                'attribute_name' => $attribute?->value // Red
                            ];
                            $selectedAttributes[] = ['attribute_id' => $itemAttribute?->id, 'attribute_value' => intval($attribute?->id)];
                        }
                    }
                    $pwoSoMapping->attributes = $attributes;
                    $unit = Unit::find($component['uom_id']);
                    $pwoSoMapping->uom_id = $component['uom_id'];
                    $pwoSoMapping->uom_code = $component['uom_code'] ?? $unit?->name;
                    $pwoSoMapping->inventory_uom_id = $component['uom_id'];
                    $pwoSoMapping->inventory_uom_code = $component['uom_code'] ?? $unit?->name;
                    $pwoSoMapping->inventory_uom_qty = $component['qty'];
                    $pwoSoMapping->qty = $component['qty'];

                    $checkBomExist = ItemHelper::checkItemBomExists($component['item_id'], $selectedAttributes);
                    if(!$checkBomExist['bom_id']) {
                        DB::rollBack();
                        return response()->json([
                                'message' => 'Bom Not Exists.',
                                'error' => "",
                            ], 422);
                    }
                    $pwoSoMapping->bom_id = $checkBomExist['bom_id'];
                    $pwoSoMapping->save();
                    $bom = Bom::find($checkBomExist['bom_id']);
                    if($pwoSoMapping->bom) {
                        $pwoSoMapping->production_route_id = $pwoSoMapping?->bom?->production_route_id;
                        $pwoSoMapping->save();
                    }

                    if(isset($pwoSoMapping->soItem) && $pwoSoMapping->soItem && $pwoSoMapping->main_so_item) {
                        $pwoSoMapping->soItem->pwo_qty += $component['qty'];
                        $pwoSoMapping->soItem->save();
                    }

                    # Station  Wise Entry
                    $productionRouteId = $pwoSoMapping->production_route_id;
                    if($productionRouteId && in_array('yes', [$mo->station_wise_consumption])) {
                        $productionRoute = ProductionRoute::find($productionRouteId);
                        $productionStations = [];
                        if($productionRoute) {
                            $productionStations = $productionRoute->details()->orderBy('level', 'asc')->get();
                        }
                        foreach($productionStations as $productionStation) {
                            $pwoStationConsum = PwoStationConsumption::where('pwo_mapping_id', $pwoSoMapping->id)
                                                    ->where('station_id', $productionStation->station_id)
                                                    ->first() ?? new PwoStationConsumption;
                            $pwoStationConsum->pwo_mapping_id = $pwoSoMapping->id;
                            $pwoStationConsum->station_id = $productionStation->station_id;
                            $pwoStationConsum->level = $productionStation?->productionLevel?->level;
                            $pwoStationConsum->mo_product_qty = 0;
                            $pwoStationConsum->save();
                        }
                    }

                    $bomDetails = (strtolower($bom->customizable) === 'no')
                        ? BomDetail::where('bom_id', $checkBomExist['bom_id'])->get()
                        : ErpSoItemBom::where('bom_id', $checkBomExist['bom_id'])
                            ->where('sale_order_id', $pwoSoMapping?->so_id)
                            ->where('so_item_id', $pwoSoMapping?->so_item_id)
                            ->get();
                    if (strtolower($bom->customizable) === 'yes' && $bomDetails->isEmpty()) {
                        $bomDetails = BomDetail::where('bom_id', $checkBomExist['bom_id'])->get();
                    }

                    foreach ($bomDetails as $bomDetail) {
                        $bomDetailId = null;
                        $sectionId = null;
                        $subSectionId = null;

                        if ($bomDetail instanceof \App\Models\BomDetail) {
                            $bomAttributes = $bomDetail->attributes->map(fn($attribute) => [
                                'attribute_id' => intval($attribute->item_attribute_id),
                                'attribute_value' => intval($attribute->attribute_value),
                                'attribute_name' => intval($attribute->attribute_name),
                            ])->toArray();
                            $bomDetailId = $bomDetail->id;
                            $sectionId = $bomDetail?->section_id;
                            $subSectionId = $bomDetail?->sub_section_id;
                        } elseif ($bomDetail instanceof \App\Models\ErpSoItemBom) {
                            $bomAttributes = array_map(function ($attribute) {
                                return [
                                    'attribute_id' => intval($attribute['attribute_id']),
                                    'attribute_value' => intval($attribute['attribute_value_id']),
                                    'attribute_name' => intval($attribute['attribute_group_id'])
                                ];
                            }, $bomDetail->item_attributes ?? []);
                            $bomDetailId = $bomDetail->bom_detail_id;
                            $sectionId = $bomDetail?->bomDetail?->section_id;
                            $subSectionId = $bomDetail?->bomDetail?->sub_section_id;
                        }

                        $moBomMapping = new PwoBomMapping;
                        $moBomMapping->pwo_id = $mo->id;
                        $moBomMapping->so_id = $pwoSoMapping->so_id ?? null;
                        $moBomMapping->pwo_mapping_id = $pwoSoMapping->id;
                        $moBomMapping->bom_id = $bomDetail->bom_id;
                        $moBomMapping->bom_detail_id = $bomDetailId;
                        $moBomMapping->item_id = $bomDetail->item_id;
                        $moBomMapping->item_code = $bomDetail->item_code;
                        $moBomMapping->attributes = $bomAttributes;
                        $moBomMapping->uom_id = $bomDetail->uom_id;
                        $moBomMapping->bom_qty = floatval($bomDetail->qty);
                        $moBomMapping->qty = floatval($pwoSoMapping->qty) * floatval($bomDetail->qty);
                        $moBomMapping->station_id = $bomDetail->station_id;
                        $moBomMapping->section_id = $sectionId;
                        $moBomMapping->sub_section_id = $subSectionId;
                        $moBomMapping->save();

                    }    
                }
                 # Store Data In MoItem
                 $groupedDatas = PwoBomMapping::selectRaw('pwo_id, so_id, item_id, item_code, uom_id, attributes, SUM(qty) as total_qty')
                 ->where('pwo_id', $mo->id)
                 ->groupBy('pwo_id', 'so_id', 'item_id', 'item_code', 'uom_id', 'attributes')
                 ->get();
                 foreach($groupedDatas as $groupedData) {
                     # PWO Item Save                  
                     $moItem = new ErpPwoItem;
                     $moItem->pwo_id = $mo->id;
                     $moItem->so_id = $groupedData->so_id;
                     $moItem->item_id = $groupedData->item_id;
                     $moItem->item_code = $groupedData->item_code;
                     $moItem->item_name = $groupedData?->item?->item_name;
                     $moItem->uom_id = $groupedData->uom_id;
                     $moItem->uom_code = $groupedData->uom_code;
                     $moItem->order_qty = $groupedData->total_qty;
                     $moItem->inventory_uom_id = $groupedData->uom_id;
                     $moItem->inventory_uom_code = $groupedData->uom_code;
                     $moItem->inventory_uom_qty = $groupedData->total_qty;
                     $moItem->hsn_id = $groupedData?->item?->hsn?->id;
                     $moItem->hsn_code = $groupedData?->item?->hsn?->code;                     
                     $moItem->save();
                     # PWO Item Attribute Save
                     $moItemAttributes = $groupedData->attributes ?? [];
                     foreach($moItemAttributes as $moItemAttribute) {
                         $moItemAttr = new ErpPwoItemAttribute;
                         $moItemAttr->pwo_id = $mo->id;
                         $moItemAttr->pwo_item_id = $moItem->id;
                         $moItemAttr->item_id = $groupedData->item_id;
                         $moItemAttr->item_code = $groupedData->item_code;
                         $moItemAttr->item_attribute_id = $moItemAttribute['attribute_id'];
                        //  $moItemAttr->attribute_name = $moItemAttribute['attribute_group_name'];
                         $moItemAttr->attribute_id = $moItemAttribute['attribute_value'];
                         $moItemAttr->attribute_group_id = $moItemAttribute['attribute_name'];
                        //  $moItemAttr->attribute_value = $moItemAttribute['attribute_name'];
                         $moItemAttr->save();
                     }

                 }
 
             } else {
                 DB::rollBack();
                 return response()->json([
                         'message' => 'Please add atleast one row in component table.',
                         'error' => "",
                     ], 422);
             }
 
             $mo->save();
 
             /*Create document submit log*/
             $modelName = get_class($mo);
             $totalValue = 0;
             if ($request->document_status == ConstantHelper::SUBMITTED) {
                 $bookId = $mo->book_id; 
                 $docId = $mo->id;
                 $remarks = $mo->remarks;
                 $attachments = $request->file('attachment');
                 $currentLevel = $mo->approval_level ?? 1;
                 $revisionNumber = $mo->revision_number ?? 0;
                 $actionType = 'submit'; // Approve // reject // submit
                 $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                 $mo->document_status = $approveDocument['approvalStatus'] ?? $request->document_status;
             } else {
                 $mo->document_status = $request->document_status ?? ConstantHelper::DRAFT;
 
             }
             /*Mo Attachment*/
             if ($request->hasFile('attachment')) {
                 $mediaFiles = $mo->uploadDocuments($request->file('attachment'), 'pwo', false);
             }
             $mo->save();
             DB::commit();
 
             return response()->json([
                 'message' => 'Record created successfully',
                 'data' => $mo,
             ]);   
         } catch (Exception $e) {
             DB::rollBack();
             return response()->json([
                 'message' => 'Error occurred while creating the record.',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }
 
     # On change item code
     public function changeItemCode(Request $request)
     {
         $attributeGroups = AttributeGroup::with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
         $item = Item::find($request->item_id);
         $specifications = collect();
         if($item) {
             $item->uom;
             $specifications = $item->specifications()->whereNotNull('value')->get();
         }
         $html = view('pwo.partials.header-attribute', compact('item','attributeGroups','specifications'))->render();
         $componentHtml = ''; 
         $bomChanged = false;
         $moId = $request->mo_id ?? null;
         if(!$item?->itemAttributes?->count()) {
             $bomExists = ItemHelper::checkItemBomExists($item?->id, []);
             if($bomExists['bom_id']) {
                 $bom = Bom::find($bomExists['bom_id'] ?? null);
                 $mo = MfgOrder::find($moId);
                 if($mo) {
                     if($mo->production_bom_id != $bom->id) {
                         $bomChanged = true;
                     }
                 }
                 $response = BookHelper::fetchBookDocNoAndParameters($bom->book_id, $bom->document_date);
                 $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
                 $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
                 $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
                 $stationRequired = isset($parameters['station_required']) && is_array($parameters['station_required']) && in_array('yes', array_map('strtolower', $parameters['station_required']));
                 $componentWasteRequired = isset($parameters['component_waste_required']) && is_array($parameters['component_waste_required']) && in_array('yes', array_map('strtolower', $parameters['component_waste_required']));
                 $componentOverheadRequired = isset($parameters['component_overhead_required']) && is_array($parameters['component_overhead_required']) && in_array('yes', array_map('strtolower', $parameters['component_overhead_required']));
                 $componentHtml = view('pwo.partials.item-row-edit', [
                     'bom' => $bom,
                     'sectionRequired' => $sectionRequired,
                     'subSectionRequired' => $subSectionRequired,
                     'stationRequired' => $stationRequired,
                     'componentWasteRequired' => $componentWasteRequired,
                     'componentOverheadRequired' => $componentOverheadRequired
                     ])
                 ->render();
             } else {
                 return response()->json(['data' => ['component_html' => $componentHtml,'html' => '', 'item' => $item], 'status' => 404, 'message' => $bomExists['message']]);
             }
         }
 
         return response()->json(['data' => ['component_html' => $componentHtml, 'html' => $html, 'item' => $item, 'bomChanged' => $bomChanged], 'status' => 200, 'message' => 'fetched.']);
     }
 
     # On change item Attr
     public function changeItemAttr(Request $request)
     {
         $itemId = $request->item_id ?? null;
         $moId = $request->mo_id ?? null;
         $headerSelectedAttr = json_decode($request->header_attr,true) ?? []; 
         $attributes = [];
         if(count($headerSelectedAttr)) {
                foreach($headerSelectedAttr as $headerAttr) {
                 $itemAttr = ItemAttribute::where("item_id", $itemId)
                                 ->where("attribute_group_id", $headerAttr['attr_name'])
                                 ->first();
                 $attributes[] = ['attribute_id' => intval($itemAttr?->id), 'attribute_value' => intval($headerAttr['attr_value'])];
                }
         }
         $bomExists = ItemHelper::checkItemBomExists($itemId, $attributes);
         if (!$bomExists['bom_id']) {
             $bomExists['message'] = "Bom Not Found for this item.";
             return response()->json(['data' => [], 'status' => 422, 'message' => $bomExists['message']]);
         }
         $componentHtml = '';
         $bomChanged = false;
         if($bomExists['bom_id']) {
             $bom = Bom::find($bomExists['bom_id'] ?? null);
             $mo = MfgOrder::find($moId);
             if($mo) {
                 if($mo->production_bom_id != $bom->id) {
                     $bomChanged = true;
                 }
             }
             $response = BookHelper::fetchBookDocNoAndParameters($bom->book_id, $bom->document_date);
             $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
             $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
             $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
             $stationRequired = isset($parameters['station_required']) && is_array($parameters['station_required']) && in_array('yes', array_map('strtolower', $parameters['station_required']));
             $componentWasteRequired = isset($parameters['component_waste_required']) && is_array($parameters['component_waste_required']) && in_array('yes', array_map('strtolower', $parameters['component_waste_required']));
             $componentOverheadRequired = isset($parameters['component_overhead_required']) && is_array($parameters['component_overhead_required']) && in_array('yes', array_map('strtolower', $parameters['component_overhead_required']));
             $componentHtml = view('pwo.partials.item-row-edit', [
                 'bom' => $bom,
                 'sectionRequired' => $sectionRequired,
                 'subSectionRequired' => $subSectionRequired,
                 'stationRequired' => $stationRequired,
                 'componentWasteRequired' => $componentWasteRequired,
                 'componentOverheadRequired' => $componentOverheadRequired
                 ])
             ->render();
         } else {
             return response()->json(['data' => ['component_html' => $componentHtml], 'status' => 422, 'message' => $bomExists['message']]);
         }
         return response()->json(['data' => ['component_html' => $componentHtml, 'bomChanged' => $bomChanged], 'status' => 200, 'message' => 'fetched.']);
     }
 
     # On change item attribute
     public function getItemAttribute(Request $request)
     {
         $rowCount = intval($request->rowCount) ?? 1;
         $item = Item::find($request->item_id);
         $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
         $detailItemId = $request->pwo_so_mapping_id ?? null;
         $itemAttIds = [];
         $itemAttributeArray = [];
         $pwo_so_mapping_id = null;
         if($detailItemId) {
             $detail = PwoSoMapping::where('id', $detailItemId)->where('item_id', $item?->id)->first();
             $pwo_so_mapping_id = $detail?->so_item_id ?? null;
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
         $pwo_so_mapping_id = $pwo_so_mapping_id ?? $request->pwo_so_mapping_id ?? null;
         $html = view('pwo.partials.comp-attribute',compact('item','rowCount','selectedAttr','itemAttributes','pwo_so_mapping_id'))->render();
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
 
     # Add item row
     public function addItemRow(Request $request)
     {
         $componentItem = json_decode($request->component_item,true) ?? [];
         if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
             if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                 return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
             }
         }
         $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
         $storeId = $request->store_id ?? null;
         $store = ErpStore::where('id', $storeId)->select('id', 'store_code', 'store_name')->first();
         $html = view('pwo.partials.item-row', [
             'rowCount' => $rowCount,
             'store' => $store,
         ])->render();
         return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
     }
 
     # On select row get item detail
     public function getItemDetail(Request $request)
     {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $remark = $request->remark ?? null;
        $html = view('pwo.partials.comp-item-detail',compact('item','selectedAttr','specifications','remark'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
     }
 
     # On select row get item detail
     public function getItemDetail2(Request $request)
     {
        $item = Item::find($request->item_id ?? null);
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $moItem = ErpPwoItem::find($request->mo_item_id ?? null);
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $html = view('pwo.partials.comp-item-detail2',compact('item','specifications','selectedAttr'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
     }
 
     # Bom edit
     public function edit(Request $request, $id)
     {
         $parentUrl = request()->segments()[0];
         $servicesAliasParam = ConstantHelper::PWO_SERVICE_ALIAS;
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);
         if (count($servicesBooks['services']) == 0) {
             return redirect()->back();
         }
         $bom = ErpProductionWorkOrder::find($id);
         $createdBy = $bom->created_by; 
         $revision_number = $bom->revision_number;
         $books = Helper::getBookSeriesNew($servicesAliasParam,$parentUrl, true)->get();
         $creatorType = Helper::userCheck()['type'];
         $totalValue = 0;
         $buttons = Helper::actionButtonDisplay($bom->book_id,$bom->document_status , $bom->id, $totalValue, $bom->approval_level, $bom->created_by ?? 0, $creatorType, $revision_number);
         $revNo = $bom->revision_number;
         if($request->has('revisionNumber')) {
             $revNo = intval($request->revisionNumber);
         } else {
             $revNo = $bom->revision_number;
         }
         $docValue = 0;
         $approvalHistory = Helper::getApprovalHistory($bom->book_id, $bom->id, $revNo, $docValue, $createdBy);
         $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$bom->document_status] ?? '';
         $view = 'pwo.edit';
 
         if($request->has('revisionNumber') && $request->revisionNumber != $bom->revision_number) {
             $bom = $bom->source()->where('revision_number', $request->revisionNumber)->first();
             $view = 'pwo.view';
         }

         $locations = InventoryHelper::getAccessibleLocations();
         $isEdit = $buttons['submit'];
        if(!$isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true: false;
        }
         return view($view, [
             'isEdit' => $isEdit,
             'books' => $books,
             'bom' => $bom,
             'item' => isset($bom->item) ? $bom->item : null,
             'buttons' => $buttons,
             'approvalHistory' => $approvalHistory,
             'docStatusClass' => $docStatusClass,
             'revision_number' => $revision_number,
             'servicesBooks' => $servicesBooks,
             'serviceAlias' => $servicesAliasParam,
             'locations' => $locations,
         ]); 
     }
 
     # Bom Update
     public function update(PwoRequest $request, $id)
     {
        DB::beginTransaction();
         try {
             $mo = ErpProductionWorkOrder::find($id);
 
             $currentStatus = $mo->document_status;
             $actionType = $request->action_type;
             if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
             {
                //  $revisionData = [
                //      ['model_type' => 'header', 'model_name' => 'ErpProductionWorkOrder', 'relation_column' => ''],
                //      ['model_type' => 'detail', 'model_name' => 'MoProduct', 'relation_column' => 'pwo_id'],
                //      ['model_type' => 'detail', 'model_name' => 'MoItem', 'relation_column' => 'pwo_id'],
                //      ['model_type' => 'detail', 'model_name' => 'MoBomMapping', 'relation_column' => 'pwo_id'],
                //      ['model_type' => 'sub_detail', 'model_name' => 'MoItemAttribute', 'relation_column' => 'pwo_id'],
                //      ['model_type' => 'sub_detail', 'model_name' => 'MoAttribute', 'relation_column' => 'pwo_item_id'],
                //  ];
                //  $a = Helper::documentAmendment($revisionData, $id);
             }
 
             $keys = ['deletedBomItemIds', 'deletedAttachmentIds'];
             $deletedData = [];
 
             foreach ($keys as $key) {
                 $deletedData[$key] = json_decode($request->input($key, '[]'), true);
             }
 
            //  if (count($deletedData['deletedAttachmentIds'])) {
            //      $medias = MoMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
            //      foreach ($medias as $media) {
            //          if ($request->document_status == ConstantHelper::DRAFT) {
            //              Storage::delete($media->file_name);
            //          }
            //          $media->delete();
            //      }
            //  }
 
             if (count($deletedData['deletedBomItemIds'])) {
                 $pwoSoMappings = PwoSoMapping::whereIn('id',$deletedData['deletedBomItemIds'])->get();
                 foreach($pwoSoMappings as $pwoSoMapping) {

                    if($pwoSoMapping->mo_product_qty > 0) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Can't delete, MO created.",
                            'error' => "",
                        ], 422);
                    }

                    $checkStationQty = $pwoSoMapping?->pwoStationConsumption()->where('mo_product_qty','>',0)->first();
                    if($checkStationQty) {
                        DB::rollBack();
                        return response()->json([
                                'message' => "Can't delete, MO created at {$checkStationQty?->station?->name}.",
                                'error' => "",
                            ], 422);
                    }


                    $groupedItems = $pwoSoMapping->pwoBomMapping()->groupBy('pwo_id','item_id','attributes','uom_id')->selectRaw('pwo_id, item_id, attributes, uom_id, SUM(qty) as total_qty')->get();
                    foreach($groupedItems as $groupedItem) {
                       $pwoItem = ErpPwoItem::where('pwo_id', $groupedItem->pwo_id)
                                ->where('item_id', $groupedItem->item_id)
                                ->where('uom_id', $groupedItem->uom_id)
                                ->where(function($query) use($groupedItem) {
                                    if(count($groupedItem->attributes)) {
                                        $query->whereHas('attributes', function($pwoItemAttrQuery) use($groupedItem) {
                                            foreach($groupedItem->attributes as $attribute) {
                                                $pwoItemAttrQuery->where('item_attribute_id', $attribute['attribute_id'])
                                                ->where('attribute_id', $attribute['attribute_value']);
                                            }
                                        });
                                    }
                                })
                                ->first();

                        if($groupedItem->total_qty < $pwoItem->mi_qty) {
                            DB::rollBack();
                            return response()->json([
                                'message' => "Can't delete, MI created.",
                                'error' => "",
                            ], 422);
                        }

                        if($pwoItem->inventory_uom_qty <= $groupedItem->total_qty) {
                            $pwoItem?->attributes()?->delete();
                            $pwoItem->delete();
                        } else {
                            $pwoItem->inventory_uom_qty -= $groupedItem->total_qty;
                            $pwoItem->order_qty -= $groupedItem->total_qty;
                            $pwoItem->save();
                        }
                    }
                    if($pwoSoMapping->soItem) {
                        $pwoSoMapping->soItem->pwo_qty -= $pwoSoMapping->inventory_uom_qty; 
                        $pwoSoMapping->soItem->save();
                    }
                    $pwoSoMapping?->pwoBomMapping()?->delete();
                    $pwoSoMapping?->pwoStationConsumption()->delete();
                    $pwoSoMapping->delete();
                 }
             }
 
             $mo->document_status = $request->document_status ?? ConstantHelper::DRAFT;
             $mo->remarks = $request->remarks;
            
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            $mo->station_wise_consumption = 'yes';
            // $mo->station_wise_consumption = @$parameters['station_wise_consumption'][0];

             # Extra Column
             $mo->save();
 
             if (isset($request->all()['components'])) {
                 // $soIds = [];
                foreach($request->all()['components'] as $component) {
                    # Save PWO SO Mapping
                    // $soIds[] = $component['so_id'];
                    $attributes = [];
                    $selectedAttributes = [];
                    $item = Item::find($component['item_id'] ?? null);
                    $pwoSoMappingId = $component['pwo_so_mapping_id'] ?? null;
                    $pwoSoMapping = PwoSoMapping::find($pwoSoMappingId) ?? new PwoSoMapping;
                    
                    $pwoSoMapping->pwo_id = $mo->id;
                    if(isset($component['so_id']) && $component['so_id']) {
                        $pwoSoMapping->so_id = $component['so_id'] ?? null;
                    }
                    if(isset($component['so_item_id']) && $component['so_item_id']) {
                        $pwoSoMapping->so_item_id = $component['so_item_id'] ?? null;
                    }
                    $pwoSoMapping->item_id = $component['item_id'] ?? null;
                    $pwoSoMapping->item_code = $component['item_code'] ?? null;
                    $pwoSoMapping->store_id = $component['store_id'] ?? null;
                    // if(intval($component['main_so_item'])) {
                    // if (!empty(intval($component['main_so_item']))) {
                    //     $pwoSoMapping->main_so_item = true;
                    // } else {
                    //     $pwoSoMapping->main_so_item = false;
                    // }
                    $pwoSoMapping->main_so_item = isset($component['main_so_item']) && intval($component['main_so_item']) != 0;
                    foreach($item?->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $attribute = Attribute::find(@$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name']);
                            $attributes[] = [
                                'item_attribute_id' => $itemAttribute?->id,
                                'attribute_group_id' => $itemAttribute?->attribute_group_id, // Color ID
                                'attribute_group_name' => $itemAttribute?->attributeGroup->name,  // Color
                                'attribute_id' => $attribute?->id, // Red Id
                                'attribute_name' =>  $attribute?->value // Red
                            ];
                            $selectedAttributes[] = ['attribute_id' => $itemAttribute?->id, 'attribute_value' => intval($attribute?->id)];
                        }
                    }

                    $unit = Unit::find($component['uom_id']);
                    $pwoSoMapping->attributes = $attributes;
                    $pwoSoMapping->uom_id = $component['uom_id'];
                    $pwoSoMapping->uom_code = $component['uom_code'] ?? $unit?->name;
                    $pwoSoMapping->inventory_uom_id = $component['uom_id'];
                    $pwoSoMapping->inventory_uom_code = $component['uom_code'] ?? $unit?->name;

                    if(isset($pwoSoMapping->soItem) && $pwoSoMapping->soItem) {
                        $updatedQty = floatval($component['qty']) - $pwoSoMapping->inventory_uom_qty;
                        $pwoSoMapping->soItem->pwo_qty += $updatedQty;
                        $pwoSoMapping->soItem->save();
                    }

                    $pwoSoMapping->inventory_uom_qty = $component['qty'];
                    $pwoSoMapping->qty = $component['qty'];
                    $pwoSoMapping->save();
                    
                    if($pwoSoMapping->inventory_uom_qty < $pwoSoMapping->mo_product_qty) {
                        DB::rollBack();
                        return response()->json([
                                'message' => "Pwo can't be less than MO.",
                                'error' => "",
                            ], 422);
                    }

                    $checkStationQty = $pwoSoMapping?->pwoStationConsumption()->where('mo_product_qty','>',$pwoSoMapping->inventory_uom_qty)->first();
                    if($checkStationQty) {
                        DB::rollBack();
                        return response()->json([
                                'message' => "Pwo can't be less than MO {$checkStationQty?->station?->name}.",
                                'error' => "",
                            ], 422);
                    }
                    

                    $checkBomExist = ItemHelper::checkItemBomExists($component['item_id'], $selectedAttributes);
                    if(!$checkBomExist['bom_id']) {
                        DB::rollBack();
                        return response()->json([
                                'message' => 'Bom Not Exists.',
                                'error' => "",
                            ], 422);
                    }
                    $pwoSoMapping->bom_id = $checkBomExist['bom_id'];
                    $pwoSoMapping->save();

                    $bom = Bom::find($checkBomExist['bom_id']);

                    if($pwoSoMapping->bom) {
                        $pwoSoMapping->production_route_id = $pwoSoMapping?->bom?->production_route_id;
                        $pwoSoMapping->save();
                    }

                    # Station  Wise Entry
                    $productionRouteId = $pwoSoMapping?->production_route_id;
                    if($productionRouteId && in_array('yes', [$mo->station_wise_consumption])) {
                        $productionRoute = ProductionRoute::find($productionRouteId);
                        $productionStations = [];
                        if($productionRoute) {
                            $productionStations = $productionRoute->details()->orderBy('level', 'asc')->get();
                        }
                        foreach($productionStations as $productionStation) {
                            $pwoStationConsum = PwoStationConsumption::where('pwo_mapping_id', $pwoSoMapping?->id)
                                                    ->where('station_id', $productionStation->station_id)
                                                    ->first() ?? new PwoStationConsumption;
                            $pwoStationConsum->pwo_mapping_id = $pwoSoMapping?->id;
                            $pwoStationConsum->station_id = $productionStation->station_id;
                            $pwoStationConsum->level = $productionStation?->productionLevel?->level;
                            if(!$pwoStationConsum) {
                                $pwoStationConsum->mo_product_qty = 0;
                            }
                            $pwoStationConsum->save();
                        }
                    }

                    $bomDetails = (strtolower($bom->customizable) === 'no')
                        ? BomDetail::where('bom_id', $checkBomExist['bom_id'])->get()
                        : ErpSoItemBom::where('bom_id', $checkBomExist['bom_id'])
                            ->where('sale_order_id', $pwoSoMapping?->so_id)
                            ->where('so_item_id', $pwoSoMapping?->so_item_id)
                            ->get();
                    if (strtolower($bom->customizable) === 'yes' && $bomDetails->isEmpty()) {
                        $bomDetails = BomDetail::where('bom_id', $checkBomExist['bom_id'])->get();
                    }

                    foreach ($bomDetails as $bomDetail) {
                        $bomDetailId = null;
                        $sectionId = null;
                        $subSectionId = null;
                        if ($bomDetail instanceof \App\Models\BomDetail) {
                            $bomAttributes = $bomDetail->attributes->map(fn($attribute) => [
                                'attribute_id' => intval($attribute->item_attribute_id),
                                'attribute_value' => intval($attribute->attribute_value),
                                'attribute_name' => intval($attribute->attribute_name),
                            ])->toArray();
                            $bomDetailId = $bomDetail->id;
                            $sectionId = $bomDetail?->section_id;
                            $subSectionId = $bomDetail?->sub_section_id;
                        } elseif ($bomDetail instanceof \App\Models\ErpSoItemBom) {
                            $bomAttributes = array_map(function ($attribute) {
                                return [
                                    'attribute_id' => intval($attribute['attribute_id']),
                                    'attribute_value' => intval($attribute['attribute_value_id']),
                                    'attribute_name' => intval($attribute['attribute_group_id']),
                                ];
                            }, $bomDetail->item_attributes ?? []);
                            $bomDetailId = $bomDetail->bom_detail_id;
                            $sectionId = $bomDetail?->bomDetail?->section_id;
                            $subSectionId = $bomDetail?->bomDetail?->sub_section_id;
                        }

                        $existPwoBomMapping = PwoBomMapping::where('pwo_id',$mo->id)
                                            ->where('pwo_mapping_id',$pwoSoMapping->id)
                                            ->where('station_id', $bomDetail->station_id)
                                            ->where('item_id',$bomDetail->item_id)
                                            ->where('uom_id',$bomDetail->uom_id)
                                            ->when(count($bomAttributes), function ($query) use ($bomAttributes) {
                                                foreach ($bomAttributes as $attribute) {
                                                    $query->whereJsonContains('attributes', $attribute);
                                                }
                                            })
                                            ->where(function($query) use($pwoSoMapping) {
                                                if($pwoSoMapping?->so_id) {
                                                    $query->where('so_id',$pwoSoMapping->so_id);
                                                }
                                            })
                                            ->first();
                        $moBomMapping = $existPwoBomMapping ?? new PwoBomMapping;
                        $moBomMapping->pwo_id = $mo->id;
                        $moBomMapping->so_id = $pwoSoMapping->so_id;
                        $moBomMapping->pwo_mapping_id = $pwoSoMapping->id;
                        $moBomMapping->bom_id = $bomDetail->bom_id;
                        $moBomMapping->bom_detail_id = $bomDetailId;
                        $moBomMapping->item_id = $bomDetail->item_id;
                        $moBomMapping->item_code = $bomDetail->item_code;
                        $moBomMapping->attributes = $bomAttributes;
                        $moBomMapping->uom_id = $bomDetail->uom_id;
                        $moBomMapping->bom_qty = floatval($bomDetail->qty);
                        $moBomMapping->qty = floatval($pwoSoMapping->qty) * floatval($bomDetail->qty);
                        $moBomMapping->station_id = $bomDetail->station_id;
                        $moBomMapping->section_id = $sectionId;
                        $moBomMapping->sub_section_id = $subSectionId;
                        $moBomMapping->save();

                    } 
                }
                
                # Store Data In MoItem
                $groupedDatas = PwoBomMapping::selectRaw('pwo_id, so_id, item_id, item_code, uom_id, attributes, SUM(qty) as total_qty')
                    ->where('pwo_id', $mo->id)
                    ->groupBy('pwo_id', 'so_id', 'item_id', 'item_code', 'uom_id', 'attributes')
                    ->get();

                foreach($groupedDatas as $groupedData) {
                    # PWO Item Save   
                    $pwoItemExist = ErpPwoItem::where('pwo_id', $groupedData->pwo_id)
                    ->where('item_id', $groupedData->item_id)
                    ->where('uom_id', $groupedData->uom_id)
                    ->where(function($query) use($groupedData) {
                        if(count($groupedData->attributes)) {
                            $query->whereHas('attributes', function($pwoItemAttrQuery) use($groupedData) {
                                foreach($groupedData->attributes as $attribute) {
                                    $pwoItemAttrQuery->where('item_attribute_id', $attribute['attribute_id'])
                                    ->where('attribute_id', $attribute['attribute_value']);
                                }
                            });
                        }
                        if($groupedData?->so_id) {
                            $query->where('so_id', $groupedData->so_id);
                        }
                    })
                    ->first();

                    if($pwoItemExist) {
                        $pwoItemExist->order_qty = $groupedData->total_qty;
                        $pwoItemExist->inventory_uom_qty = $groupedData->total_qty;
                        $pwoItemExist->save();

                        if($pwoItemExist->inventory_uom_qty < $pwoItemExist->mi_qty) {
                            DB::rollBack();
                            return response()->json([
                                    'message' => "Qty can't be less than MI Qty.",
                                    'error' => "",
                                ], 422);
                        }


                    } else {
                        $pwoItem = new ErpPwoItem;
                        $pwoItem->pwo_id = $mo->id;
                        $pwoItem->so_id = $groupedData->id;
                        $pwoItem->item_id = $groupedData->item_id;
                        $pwoItem->item_code = $groupedData->item_code;
                        $pwoItem->item_name = $groupedData?->item?->item_name;
                        $pwoItem->uom_id = $groupedData->uom_id;
                        $pwoItem->uom_code = $groupedData->uom_code;
                        $pwoItem->order_qty = $groupedData->total_qty;
                        $pwoItem->inventory_uom_id = $groupedData->uom_id;
                        $pwoItem->inventory_uom_code = $groupedData->uom_code;
                        $pwoItem->inventory_uom_qty = $groupedData->total_qty;
                        $pwoItem->hsn_id = $groupedData?->item?->hsn?->id;
                        $pwoItem->hsn_code = $groupedData?->item?->hsn?->code;                     
                        $pwoItem->save();
                        # PWO Item Attribute Save
                        $pwoItemAttributes = $groupedData->attributes ?? [];
                        foreach($pwoItemAttributes as $pwoItemAttribute) {
                            $pwoItemAttr = new ErpPwoItemAttribute;
                            $pwoItemAttr->pwo_id = $mo->id;
                            $pwoItemAttr->pwo_item_id = $pwoItem->id;
                            $pwoItemAttr->item_id = $groupedData->item_id;
                            $pwoItemAttr->item_code = $groupedData->item_code;
                            $pwoItemAttr->item_attribute_id = $pwoItemAttribute['attribute_id'];
                            //  $pwoItemAttr->attribute_name = $pwoItemAttribute['attribute_group_name'];
                            $pwoItemAttr->attribute_id = $pwoItemAttribute['attribute_value'];
                            $pwoItemAttr->attribute_group_id = $pwoItemAttribute['attribute_name'];
                            //  $pwoItemAttr->attribute_value = $pwoItemAttribute['attribute_name'];
                            $pwoItemAttr->save();
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
     
             $mo->save();
 
             /*Bom Attachment*/
             if ($request->hasFile('attachment')) {
                 $mediaFiles = $mo->uploadDocuments($request->file('attachment'), 'pwo', true);
             }
 
             /*Update Bom header*/
             $mo->save();
 
             /*Create document submit log*/
             $bookId = $mo->book_id; 
             $docId = $mo->id;
             $amendRemarks = $request->amend_remarks ?? null;
             $remarks = $mo->remarks;
             $amendAttachments = $request->file('amend_attachment');
             $attachments = $request->file('attachment');
             $currentLevel = $mo->approval_level;
             $modelName = get_class($mo);
             $totalValue = 0;
             if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
             {
                 //*amendmemnt document log*/
                 $revisionNumber = $mo->revision_number + 1;
                 $actionType = 'amendment';
                 $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                 $mo->revision_number = $revisionNumber;
                 $mo->approval_level = 1;
                 $mo->revision_date = now();
                 $amendAfterStatus = $approveDocument['approvalStatus'] ??  $mo->document_status;
                 $mo->document_status = $amendAfterStatus;
                 $mo->save();
             } else {
                 if ($request->document_status == ConstantHelper::SUBMITTED) {
                     $revisionNumber = $mo->revision_number ?? 0;
                     $actionType = 'submit'; // Approve // reject // submit
                     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                     $mo->document_status = $approveDocument['approvalStatus'] ?? $mo->document_status;
                 } else {
                     $mo->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                 }
             }
             $mo->save();
             DB::commit();
 
             return response()->json([
                 'message' => 'Record updated successfully',
                 'data' => $mo,
             ]);   
         } catch (Exception $e) {
             DB::rollBack();
             dd($e->getLine());
             return response()->json([
                 'message' => 'Error occurred while updating the record.',
                 'error' => $e->getMessage(),
             ], 500);
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
        $pwo = ErpProductionworkorder::findOrFail($id);
        $specifications = collect();
        $products = collect();
        $items = collect();
        if(isset($pwo -> items) && $pwo -> items) {
            $items = $pwo -> items;
        }
        if(isset($pwo->mapping))
        {
            $products = $pwo -> mapping;            
        }

        $totalAmount = 0;
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $approvedBy = Helper::getDocStatusUser(get_class($pwo), $pwo -> id, $pwo -> document_status);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pwo->document_status] ?? '';
        $dynamicFields = $pwo -> dynamic_fields ?? [];
        $pdf = PDF::loadView(
        // return view(
        'pdf.pwo',
        [
            'order'=> $pwo,
            'items' => $items,
            'user'=>$user,
            'products' => $products,
            'organization' => $organization,
            'organizationAddress' => $organizationAddress,
            'totalAmount'=>$totalAmount,
            'amountInWords'=>$amountInWords,
            'approvedBy' => $approvedBy,
            'imagePath' => $imagePath,
            'specifications' => $specifications,
            'docStatusClass' => $docStatusClass,
            'dynamicFields' => $dynamicFields
        ]
        );
        // $pdf->setPaper('a4', 'landscape');
        // $pdf->setOption('isHtml5ParserEnabled', true);
        return $pdf->stream('MfgOrder-' . date('Y-m-d') . '.pdf');
    }

     public function revokeDocument(Request $request)
     {
         DB::beginTransaction();
         try {
             $bom = ErpProductionWorkOrder::find($request->id);
             if (isset($bom)) {
                 $revoke = Helper::approveDocument($bom->book_id, $bom->id, $bom->revision_number, '', [], 0, ConstantHelper::REVOKE, 0, get_class($bom));
                 if ($revoke['message']) {
                     DB::rollBack();
                     return response() -> json([
                         'status' => 'error',
                         'message' => $revoke['message'],
                     ]);
                 } else {
                     $bom->document_status = $revoke['approvalStatus'];
                     $bom->save();
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
 
     public function closeDocument(Request $request)
     {
         DB::beginTransaction();
         try {
             $bom = MfgOrder::find($request->id);
             if (isset($bom)) {
                 $errorMoItemIds = [];
                 foreach($bom->moItems as $key => $moItem) {
                     $key += 1;
                     $selectedAttr = $moItem->attributes->map(fn($attribute) => intval($attribute->attribute_value))->toArray();
                     $inventoryStock = InventoryHelper::totalInventoryAndStock($moItem->item_id, $selectedAttr, $moItem->uom_id, $moItem->mo->store_id);
                     if (!$inventoryStock['confirmedStocks']) {
                         $errorMoItemIds[] = [
                             'field' => "component_item_name[$key]", // Corrected format
                             'message' => "Stock not available.",
                         ];
                     }
                 }
                 
                 if (count($errorMoItemIds)) {
                     return response()->json([
                         'status' => 'error',
                         'errors' => $errorMoItemIds,
                     ], 422);
                 }
 
                 $remarks = $request->close_remarks ?? '';
                 $attachments = $request->file('attachment');
                 $currentLevel = $bom->approval_level;
                 $actionType = 'close';
                 $close = Helper::approveDocument($bom->book_id, $bom->id, $bom->revision_number, $remarks, $attachments, $currentLevel, $actionType, 0, get_class($bom));
                 if ($close['message']) {
                     DB::rollBack();
                     return response() -> json([
                         'status' => 'error',
                         'message' => $close['message'],
                     ]);
                 } else {
                     $bom->document_status = $close['approvalStatus'];
                     $bom->save();
                 }
 
                 $maintainStockLedger = self::maintainStockLedger($bom);
 
                 if(!$maintainStockLedger) {
                     DB::rollBack();
                     return response() -> json([
                         'status' => 'error',
                         'message' => "Error while updating stock ledger.",
                     ]);
                 }
 
                 DB::commit();
 
                 return response() -> json([
                     'status' => 'success',
                     'message' => 'closed succesfully',
                 ]);
                 
             } else {
                 DB::rollBack();
                 throw new ApiGenericException("No Document found");
             }
         } catch(Exception $ex) {
             DB::rollBack();
             throw new ApiGenericException($ex -> getMessage());
         }
     }
     
     private static function maintainStockLedger(MfgOrder $mo)
     {
         $user = Helper::getAuthenticatedUser();
         $detailIds = $mo->moItems->pluck('id')->toArray();
         $issueRecords = InventoryHelper::settlementOfInventoryAndStock($mo->id, $detailIds, ConstantHelper::MO_SERVICE_ALIAS, $mo->document_status, 'issue');
         
         if(!empty($issueRecords['records'])){
             MoItemLocation::where('mo_id', $mo->id)
             // ->whereIn('mo_item_id', $detailIds)
             ->delete();
 
             foreach($issueRecords['records'] as $key => $val){
 
                 $moItem = MoItem::find(@$val->issuedBy->document_detail_id);
 
                 MoItemLocation::create([
                     'mo_id' => $mo->id,
                     'mo_item_id' => @$val->issuedBy->document_detail_id,
                     'item_id' => $val->issuedBy->item_id,
                     'item_code' => $val->issuedBy->item_code,
                     'store_id' => $val->issuedBy->store_id,
                     'store_code' => $val->issuedBy->store,
                     'rack_id' => $val->issuedBy->rack_id,
                     'rack_code' => $val->issuedBy->rack,
                     'shelf_id' => $val->issuedBy->shelf_id,
                     'shelf_code' => $val->issuedBy->shelf,
                     'bin_id' => $val->issuedBy->bin_id,
                     'bin_code' => $val->issuedBy->bin,
                     'quantity' => ItemHelper::convertToAltUom($val->issuedBy->item_id, $moItem?->uom_id, $val->issuedBy->issue_qty),
                     'inventory_uom_qty' => $val->issuedBy->issue_qty
                 ]);
             }
 
             $stockLedgers = StockLedger::where('book_type',ConstantHelper::MO_SERVICE_ALIAS)
                                 ->where('document_header_id',$mo->id)
                                 ->where('organization_id',$mo->organization_id)
                                 ->selectRaw('document_detail_id,sum(org_currency_cost) as cost')
                                 ->groupBy('document_detail_id')
                                 ->get();
 
             foreach($stockLedgers as $stockLedger) {
                 $moItem = MoItem::find($stockLedger->document_detail_id);
                 $moItem->rate = floatval($stockLedger->cost) / floatval($moItem->qty);
                 $moItem->save();
 
             }
             
             return true;
         } else {
             return false;
         }
     }
 
     # Get Quotation Bom Item List
     public function getSoItem(Request $request)
     {
        $isAttribute = intval($request->is_attribute) ?? 0;
        $subTypeIds = SubType::whereIn('name', [ConstantHelper::FINISHED_GOODS, ConstantHelper::WIP_SEMI_FINISHED])
                -> get() -> pluck('id') -> toArray();
        $selectedPwoIds = json_decode($request->selected_so_item_ids,true) ?? [];
        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $customerId = $request->customer_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $storeId = $request->store_id ?? null;
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $soItems = ErpSoItem::whereExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('erp_boms')
                  ->where('type','bom')
                  ->whereColumn('erp_boms.item_id', 'erp_so_items.item_id')
                  ->whereIn('erp_boms.document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
        })
        ->whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $docNumber, $customerId, $storeId) {
                 $subQuery->whereIn('book_id', $applicableBookIds)
                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                ->when($request->book_id, function ($bookQuery) use ($request) {
                    $bookQuery->where('book_id', $request->book_id);
                })
                ->when($storeId, function ($query) use ($storeId) {
                    $query->where('store_id', $storeId);
                })
                ->when($docNumber, function ($query) use ($docNumber) {
                    $query->where('document_number', 'LIKE', "%{$docNumber}%");
                })
                ->when($customerId, function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                });
        })
        ->whereColumn('inventory_uom_qty', '>', 'pwo_qty')
        ->where(function ($query) use ($itemSearch, $selectedPwoIds, $subTypeIds) {
             if(count($selectedPwoIds)) {
                 $query->whereNotIn('id', $selectedPwoIds);
             }
            $query->whereHas('item', function ($itemQuery) use ($itemSearch,$subTypeIds) {
                $itemQuery->whereHas('subTypes', function($subTypesQuery) use($subTypeIds) {
                    $subTypesQuery->whereIn('sub_type_id',$subTypeIds);
                });
                if ($itemSearch) {
                    $itemQuery->where('item_name', 'like', '%' . $itemSearch . '%')
                    ->orWhere('item_code', 'like', '%' . $itemSearch . '%');
                }
            });
        })
        ->with(['header', 'item']);

        if(!$isAttribute) {
            $groupByColumns = ['sale_order_id', 'item_id', 'item_name', 'item_code', 'inventory_uom_code','bom_id'];
            $soItems = $soItems->groupBy($groupByColumns)
                    ->selectRaw(implode(',', array_merge($groupByColumns, [
                        'SUM(inventory_uom_qty) as inventory_uom_qty',
                        'SUM(pwo_qty) as pwo_qty'
                    ])));
        }
        $soItems = $soItems->get();

         $html = view('pwo.partials.so-item-list', ['soItems' => $soItems, 'isAttribute' => $isAttribute])->render();
         return response()->json(['data' => ['pis' => $html, 'isAttribute' => $isAttribute], 'status' => 200, 'message' => "fetched!"]);
     }
 
     # Submit PWO Item list
     public function processSoItem(Request $request)
     {
        //  $ids = json_decode($request->ids,true) ?? [];
        //  $ids = array_values(array_unique($ids));
        //  if(!$isAttribute) {
        //      $selectedData = json_decode($request->selected_items,true); 
        //      $ids = ErpSoItem::where(function ($query) use ($selectedData) {
        //         foreach ($selectedData as $selectedItem) {
        //             $query->orWhere(function ($q) use ($selectedItem) {
        //                 $q->where('sale_order_id', $selectedItem['sale_order_id'])
        //                   ->where('item_id', $selectedItem['item_id']);
        //             });
        //         }
        //     })->pluck('id')->toArray();
        // } 
        //  $pwoItems = ErpSoItem::whereIn('id', $ids)->get();
        $isAttribute = intval($request->is_attribute) ?? 0;
        $selectedItems = $request->selected_items;
        $pwoItems = is_array($selectedItems) 
        ? $selectedItems 
        : (is_string($selectedItems) && is_array(json_decode($selectedItems, true)) 
            ? json_decode($selectedItems, true) 
            : []);
        
        $extendedPwoItems = $pwoItems;
        if(!$isAttribute) {
            foreach ($pwoItems as $index => $item) {
                if (!empty($item['main_so_item']) && !empty($item['so_item_ids'])) {
                // if ($item['main_so_item'] && !empty($item['so_item_ids'])) {
                    $soItems = ErpSoItem::where('sale_order_id', $item['so_id'])
                        ->whereIn('id', $item['so_item_ids'])
                        ->get();
                    $newItems = [];
                    unset($item['so_item_ids']);
                    foreach ($soItems as $soItem) {
                        $newItem = $item;
                        $newItem['item_id']      = $soItem->item_id;
                        $newItem['item_name']    = $soItem->item_name;
                        $newItem['item_code']    = $soItem->item_code;
                        $newItem['uom_id']       = $soItem->uom_id;
                        $newItem['uom_name']     = $soItem->uom->name;
                        $newItem['total_qty']    = $soItem->order_qty;
                        $newItem['so_item_id']   = $soItem->id;
                        $newItem['attribute']    = $soItem->item_attributes_array();
                        $newItems[] = $newItem;
                    }
                    array_splice($extendedPwoItems, $index, 1, $newItems);
                }
            }
        }
        $rowCount = intval($request->rowCount) ? intval($request->rowCount) + 1  : 1;
        $html = view('pwo.partials.mo-item-pull', [
             'pwoItems' => $extendedPwoItems,
             'is_pull' => true,
             'rowCount' => $rowCount
        ])->render();
 
         return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
     }
     
    public function analyzeSoItem(Request $request)
    {
        $ids = json_decode($request->ids,true) ?? [];
        $ids = array_values(array_unique($ids));
        $isAttribute = intval($request->is_attribute) ?? 0;
        if(!$isAttribute) {
            $selectedData = json_decode($request->selected_items,true); 
            $ids = ErpSoItem::where(function ($query) use ($selectedData) {
                foreach ($selectedData as $selectedItem) {
                    $query->orWhere(function ($q) use ($selectedItem) {
                        $q->where('sale_order_id', $selectedItem['sale_order_id'])
                          ->where('item_id', $selectedItem['item_id']);
                    });
                }
            })->pluck('id')->toArray();
        } 
        $pwoItems = ErpSoItem::whereIn('id', $ids)->get();
        $soItemIds = $pwoItems->pluck('id')->toArray();
        $bomService = new BomService;
        $femifishedItems = $bomService->getRawMaterialBreakdown($soItemIds, 'semi');
        if(!$isAttribute) {
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
                    if(count($items) > 1) {
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
            foreach($femifishedItems as $soItemId => $femifishedItem) {
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
        
        $html = view('pwo.partials.analyze-item', [
             'femifishedItems' => $femifishedItems,
             'isAttribute' => $isAttribute
            //  'rowCount' => $rowCount
             ])->render();
        return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

     public function destroy($id)
     {
         try {
             $bom = MfgOrder::find($id);
             $bom->moItems()->delete();
             $bom->moOverheadAllItems()->delete();
             $bom->moAllAttributes()->delete();    
             $bom->production_bom_id = null;
             $bom->qty_produced = 0;
             $bom->total_item_value = 0;
             $bom->item_waste_amount = 0;
             $bom->item_overhead_amount = 0;
             $bom->header_waste_perc = 0;
             $bom->header_waste_amount = 0;
             $bom->header_overhead_amount = 0;
             $bom->item_id = null;
             $bom->item_code = null;
             $bom->item_name = null;
             $bom->uom_id = null;
             $bom->save();
             return response()->json([
                 'status' => true,
                 'message' => 'Record deleted successfully',
             ], 200);
             
         } catch (\Exception $e) {
             return response()->json([
                 'status' => false,
                 'message' => 'An error occurred while deleting the MO: ' . $e->getMessage()
             ], 500);
         }
     }
 
     # Get Posting details
     public function getPostingDetails(Request $request)
     {
         try {
         $data = FinancialPostingHelper::financeVoucherPosting((int)$request->book_id ?? 0, $request->document_id ?? 0, $request->type ?? 'get');
         $document_date = $data['data']['document_date'] ?? '';
         $book_code = $data['data']['book_code'] ?? '';
         $document_number = $data['data']['document_number'] ?? '';
         $currency_code = $data['data']['currency_code'] ?? '';
         $html = view('pwo.partials.post-voucher-list',['data' => $data])->render();
             return response() -> json([
                 'status' => 'success',
                 'data' => [
                     'html' => $html,
                     'document_date' => $document_date,
                     'book_code' => $book_code,
                     'document_number' => $document_number,
                     'currency_code' => $currency_code
                     ]
             ]);
         } catch(Exception $ex) {
             return response() -> json([
                 'status' => 'exception',
                 'message' => 'Some internal error occured',
                 'error' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
             ]);
         }
     }
 
     # Submit Posting
     public function postMo(Request $request)
     {
         try {
             DB::beginTransaction();
             $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post");
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
             return response()->json([
                 'status' => 'exception',
                 'message' => 'Some internal error occured',
                 'error' => $ex->getMessage()
             ]);
         }
     }

    # Get Stock
    public function getStock(Request $request)
    {
        $explodeAttributes = explode(',', $request->selected_attributes ?? '');
        $selectedAttributes = array_map('intval', $explodeAttributes);
        $itemId = $request->item_id ?? null;
        $uomId = $request->uom_id ?? null;
        $storeId = $request->store_id ?? null;
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributes, $uomId, $storeId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        $stockBalanceQty = ItemHelper::convertToAltUom($itemId, $uomId, (float)$stockBalanceQty);
        return response()->json([
            'status' => 200,
            'data' => [
                'avl_stock' => $stockBalanceQty
            ]
        ]);
    }
}
