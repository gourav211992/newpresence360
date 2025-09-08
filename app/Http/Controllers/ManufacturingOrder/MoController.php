<?php

namespace App\Http\Controllers\ManufacturingOrder;

use App\Exceptions\ApiGenericException;
use App\Helpers\DynamicFieldHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
use App\Models\BomDetail;
use App\Models\MoBomMapping;
use App\Models\MoItemAttribute;
use App\Models\MoItemLocation;
use App\Models\MoProduct;
use App\Models\MoProductAttribute;
use App\Models\MoProductionItem;
use App\Models\MoProductionItemAttribute;
use App\Models\MoProductionItemLocation;
use App\Models\PwoSoMapping;
use App\Models\PwoStationConsumption;
use App\Models\Station;
use App\Models\StockLedger;
use App\Models\Attribute;
use App\Models\ErpMachineDetail;
use App\Models\ErpSubStore;
use App\Models\PwoBomMapping;
use Yajra\DataTables\DataTables;
use DB;
use PDF;

class MoController extends Controller
{
    # Bill of material list
    public function index(Request $request)
    {
        $parentUrl = request()->segments()[0];
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));
            $boms = MfgOrder::withDraftListingLogic()->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']]);
            return DataTables::of($boms)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    return view('partials.action-dropdown', [
                        'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                        'displayStatus' => $row->display_status,
                        'row' => $row,
                        'actions' => [
                            [
                                'url' => fn($r) => route('mo.edit', $r->id),
                                'icon' => 'edit-3',
                                'label' => 'View/ Edit Detail',
                            ]
                        ]
                    ])->render();
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book ? $row->book?->book_code : ' ';
                })
                ->addColumn('item_name', function ($row) {
                    return $row?->item ? $row?->item?->item_name : ' ';
                })
                ->addColumn('item_code', function ($row) {
                    return $row?->item ? $row?->item?->item_code : ' ';
                })
                ->addColumn('location_name', function ($row) {
                    return $row?->store_location ? $row?->store_location?->store_name : ' ';
                })
                ->addColumn('sub_location_name', function ($row) {
                    return $row?->sub_store ? $row?->sub_store?->name : ' ';
                })
                ->addColumn('station_name', function ($row) {
                    return $row?->station ? $row->station?->name : ' ';
                })
                ->addColumn('total_qty', function ($row) {
                    return isset($row?->moProducts) ? (number_format($row?->moProducts()->sum('qty'),4)) : ' ';
                })
                ->addColumn('produced_qty', function ($row) {
                    return isset($row?->moProducts) ? (number_format($row?->moProducts()->sum('pslip_qty'),4)) : ' ';
                })
                ->addColumn('so_no', function ($row) {
                    $bookCode = strtoupper($row?->last_so()?->book_code);
                    return $row?->last_so() ? ($bookCode.' - '. $row?->last_so()?->document_number)  : '';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? ' ';
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        return view('mfgOrder.index', ['servicesBooks' => $servicesBooks]);
    }

    # Bill of material Create
    public function create(Request $request)
    {
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = ConstantHelper::MO_SERVICE_ALIAS;
        $currentfyYear = Helper::getCurrentFinancialYear();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $currentfyYear['current_date'] = Carbon::now() -> format('Y-m-d');
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);

        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
        $wasteTypes = ConstantHelper::DISCOUNT_TYPES;
        $stations = Station::where('status', ConstantHelper::ACTIVE)
        ->get();
        $locations = InventoryHelper::getAccessibleLocations();
        return view('mfgOrder.create', [
            'wasteTypes' => $wasteTypes,
            'books' => $books,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'stations' => $stations,
            'current_financial_year' => $selectedfyYear,
            'locations' => $locations
        ]);
    }

    #Bill of material store
    public function store(MoRequest $request)
    {
        DB::beginTransaction();
        try {
            # Mo Header Save
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();

            $bomExist = ItemHelper::checkItemBomExists($request->item_id, []);
            if(!$bomExist['bom_id']) {
                return response()->json([
                        'message' => 'Bom Not Exists.',
                        'error' => "",
                    ], 422);
            }
            $subStore = ErpSubStore::find($request->sub_store_id);
            $isConsumption = $subStore?->station_wise_consumption ?? null;
            $bom = Bom::find($bomExist['bom_id'] ?? null);
            $getProductionRoute = $bom?->productionRoute;
            if(strpos($isConsumption,'yes') !== false) {
                $productionDetails = optional($getProductionRoute)
                ->details()
                ->orderBy('level')
                ->orderBy('id')
                ->get();
            } else {
                $productionDetails = optional($getProductionRoute)
                ->details()
                ->whereNull('pr_parent_id')
                ->get();
            }

            foreach($productionDetails as $productionDetail) {
                if(strpos($isConsumption,'yes') !== false) {
                    $isLastStation = $productionDetail?->pr_parent_id ? false : true;
                    $stationId = $productionDetail->station_id ?? null;
                } else {
                    $isLastStation = true;
                    $stationId = null;
                }
                $mo = new MfgOrder;
                $mo->organization_id = $organization->id;
                $mo->group_id = $organization->group_id;
                $mo->company_id = $organization->company_id;
                $mo->revision_number = $request->revision_number ?? 0;
                $mo->remarks = $request->remarks;
                $mo->store_id = $request->store_id;
                $mo->sub_store_id = $request?->sub_store_id ?? null;
                $mo->item_id = $request->item_id;
                $mo->machine_id = $request?->main_machine_id ?? null;


                $mo->station_id = $stationId;
                $mo->is_last_station = $isLastStation;
                $mo->production_bom_id = $bomExist['bom_id'] ?? null;
                $document_number = $request->document_number ?? null;
                $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
                if (!isset($numberPatternData)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
                $regeneratedDocExist = MfgOrder::where('book_id', $request->book_id)
                                    ->where('document_number', $document_number)
                                    ->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    DB::rollBack();
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
                $mo->book_id = $request->book_id;
                $mo->book_code = $request->book_code;
                $mo->document_number = $document_number;
                $mo->document_date = $request->document_date ?? now();
                /*Store currency data*/
                $currency = CurrencyHelper::getOrganizationCurrency();
                $mo->currency_id = $currency->id;
                $mo->currency_code = $currency->short_name;
                $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($currency->id, $mo->document_date);
                $mo->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
                $mo->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
                $mo->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
                $mo->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
                $mo->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
                $mo->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
                $mo->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
                $mo->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
                $mo->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
                $mo->production_route_id = $productionDetail->production_route_id;
                $mo->save();

                # Save Component
                if (isset($request->all()['components'])) {
                    # Get Product Route By Item_id
                    $selectedRow = false;
                    foreach($request->all()['components'] as $component) {
                        if (!empty($component['is_pi_item_id'])) {
                            if(!isset($component['selected'])) {
                                continue;
                            }
                            $selectedRow = true;
                            # MoProductDetail
                            $moProdDetail = new MoProduct;
                            $moProdDetail->mo_id = $mo->id;
                            $moProdDetail->item_id = $component['item_id'];
                            $moProdDetail->item_code = $component['item_code'];
                            $moProdDetail->customer_id = $component['customer_id'];
                            $moProdDetail->uom_id = $component['uom_id'];
                            $moProdDetail->qty = $component['qty'];
                            $moProdDetail->so_id = $component['so_id'] ?? null;
                            $moProdDetail->so_item_id = $component['so_item_id'] ?? null;
                            $moProdDetail->pwo_mapping_id = $component['pwo_mapping_id'] ?? null;
                            $moProdDetail->remark = $component['remark'] ?? null;
                            $moProdDetail->machine_id = $component['machine_id'] ?? null;
                            $moProdDetail->number_of_sheet = $component['sheet'] ?? 0;
                            $moProdDetail->save();
                            #Save MoProductDetailAttr component Attr
                            $attributes = [];
                            $newAttributes = [];
                            foreach($moProdDetail?->item?->itemAttributes as $itemAttribute) {
                                if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                                    $moProdAttr = new MoProductAttribute;
                                    $moProdAttr->mo_id = $mo->id;
                                    $moProdAttr->mo_product_id = $moProdDetail->id;
                                    $moProdAttr->item_attribute_id = $itemAttribute->id;
                                    $moProdAttr->item_code = $component['item_code'];
                                    $moProdAttr->attribute_name = $itemAttribute->attribute_group_id;
                                    $moProdAttr->attribute_value = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                                    $moProdAttr->save();
                                    $attributes[] = ['attribute_id' => intval($itemAttribute?->id), 'attribute_value' => intval($moProdAttr->attribute_value)];
                                    $newAttributes[] = [
                                        'attribute_id' => intval($moProdAttr->attribute_value),
                                        'attribute_name' => $moProdAttr?->headerAttribute?->name,
                                        'attribute_value' => $moProdAttr?->headerAttributeValue?->value,
                                        'item_attribute_id' => intval($moProdAttr->item_attribute_id),
                                        'attribute_group_id' => intval($moProdAttr->attribute_name)
                                    ];
                                }
                            }

                            # Back update PWO station consumption
                            if(isset($moProdDetail->pwoMapping) && $moProdDetail->pwoMapping) {
                                $pwoStation = PwoStationConsumption::where('pwo_mapping_id', $moProdDetail?->pwoMapping?->id)
                                                        ->where('station_id', $mo->station_id)
                                                        ->first();
                                if($pwoStation) {
                                    $pwoStation->mo_product_qty += $moProdDetail->qty;
                                    $pwoStation->mo_id = $mo->id;
                                    $pwoStation->save();
                                } else {
                                    $moProdDetail->pwoMapping->mo_id = $mo->id;
                                    $moProdDetail->pwoMapping->mo_product_qty += $moProdDetail->qty;
                                    $moProdDetail->pwoMapping->save();
                                }
                            }

                            # Mo Item Store
                            $bomDetails = PwoBomMapping::where('pwo_mapping_id', $moProdDetail->pwo_mapping_id)
                                        ->where(function($query) use($stationId) {
                                            if($stationId) {
                                                $query->where('station_id', $stationId);
                                            }
                                        })
                                        ->get();

                            foreach ($bomDetails as $bomDetail) {
                                $moBomMapping = new MoBomMapping;
                                $moBomMapping->mo_id = $mo->id;
                                $moBomMapping->mo_product_id = $moProdDetail->id;
                                $moBomMapping->so_id = $bomDetail->so_id ?? null;
                                $moBomMapping->bom_id = $bomDetail->bom_id;
                                $moBomMapping->bom_detail_id = $bomDetail->bom_detail_id;
                                $moBomMapping->item_id = $bomDetail->item_id;
                                $moBomMapping->item_code = $bomDetail->item_code;
                                $moBomMapping->attributes = $bomDetail->attributes;
                                $moBomMapping->uom_id = $bomDetail->uom_id;
                                $moBomMapping->bom_qty = $bomDetail->bom_qty;
                                $moBomMapping->consumption_qty = $bomDetail->qty;
                                $moBomMapping->station_id = $bomDetail->station_id;
                                $moBomMapping->section_id = $bomDetail->section_id;
                                $moBomMapping->sub_section_id = $bomDetail->sub_section_id;
                                $moBomMapping->save();
                            }
                        }
                    }
                    if(!$selectedRow) {
                        DB::rollBack();
                        return response()->json([
                                'message' => 'Please select atleast one row.',
                                'error' => "",
                            ], 422);
                    }

                    if($mo->station_id) {
                        $prDetailParents = $mo->productionRoute->details()->where('pr_parent_id', $productionDetail->station_id)->get();

                        if($prDetailParents?->count()) {
                            foreach($mo->moProducts as $moProduct) {
                                foreach($prDetailParents as $prDetailParent) {
                                    $pwoStationConsumption = PwoStationConsumption::where('pwo_mapping_id', $moProduct->pwo_mapping_id)
                                                    ->where('station_id', $prDetailParent->station_id)->first();
                                    $oldMo = MfgOrder::find($pwoStationConsumption->mo_id);

                                    $moProductAttributes = $moProduct->attributes ?? [];
                                    if($oldMo) {
                                        $oldMoProduct = $oldMo->moProducts()
                                                        ->where('item_id', $moProduct->item_id)
                                                        ->when($oldMo->so_id, function ($query) use ($oldMo) {
                                                            $query->where('so_id', $oldMo->so_id);
                                                        })
                                                        ->when(count($moProductAttributes), function ($query) use ($moProductAttributes) {
                                                            $query->whereHas('attributes', function ($piAttributeQuery) use ($moProductAttributes) {
                                                                $piAttributeQuery->where(function ($subQuery) use ($moProductAttributes) {
                                                                    foreach ($moProductAttributes as $poAttribute) {
                                                                        $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                                            $q->where('item_attribute_id', $poAttribute->item_attribute_id)
                                                                                ->where('attribute_value', $poAttribute->attribute_value);
                                                                        });
                                                                    }
                                                                });
                                                            }, '=', count($moProductAttributes));
                                                        })
                                                        ->first();

                                        $moBomAttributes = [];
                                        foreach($moProductAttributes as $moProdAttr) {
                                            $moBomAttributes[] = [
                                                'attribute_id' => $moProdAttr->item_attribute_id,
                                                'attribute_name' => $moProdAttr->attribute_name,
                                                'attribute_value' => $moProdAttr->attribute_value
                                            ];
                                        }
                                        $moBomMapping = new MoBomMapping;
                                        $moBomMapping->mo_id = $mo->id;
                                        $moBomMapping->mo_product_id = $moProduct->id;
                                        $moBomMapping->old_mo_product_id = $oldMoProduct->id ?? null;
                                        $moBomMapping->so_id = $moProduct->so_id ?? null;
                                        $moBomMapping->item_id = $moProduct->item_id;
                                        $moBomMapping->item_code = $moProduct->item_code;
                                        $moBomMapping->attributes = $moBomAttributes;
                                        $moBomMapping->uom_id = $moProduct->uom_id;
                                        $moBomMapping->rm_type = 'sf';
                                        $moBomMapping->bom_qty = 1;
                                        $moBomMapping->consumption_qty = $moProduct->qty;
                                        $moBomMapping->station_id = $prDetailParent->station_id;
                                        $moBomMapping->save();
                                    }
                                }
                            }
                        }
                    }

                    $groupedDatas = MoBomMapping::selectRaw('mo_id, so_id, station_id, bom_detail_id, item_id, item_code, uom_id, rm_type, attributes, SUM(consumption_qty) as total_qty')
                        ->where('mo_id', $mo->id)
                        ->groupBy('mo_id', 'so_id', 'station_id', 'bom_detail_id', 'item_id', 'item_code', 'uom_id', 'rm_type', 'attributes')
                        ->get();

                    foreach($groupedDatas as $groupedData) {
                        # Mo Item Save
                        $moItem = new MoItem;
                        $moItem->mo_id = $mo->id;
                        $moItem->so_id = $groupedData->so_id ?? null;
                        $moItem->bom_detail_id = $groupedData->bom_detail_id;
                        $moItem->station_id = $groupedData->station_id;
                        $moItem->item_id = $groupedData->item_id;
                        $moItem->item_code = $groupedData->item_code;
                        $moItem->uom_id = $groupedData->uom_id;
                        $moItem->rm_type = $groupedData->rm_type;
                        $moItem->qty = $groupedData->total_qty;
                        $moItem->inventory_uom_id = $groupedData?->item?->uom_id;
                        $moItem->inventory_uom_code = $groupedData?->item?->uom?->name;
                        $moItem->inventory_uom_qty = $groupedData->total_qty;
                        $moItem->save();
                        # Mo Item Attribute Save
                        $moItemAttributes = $groupedData->attributes;
                        foreach($moItemAttributes as $moItemAttribute) {
                            $moItemAttr = new MoItemAttribute;
                            $moItemAttr->mo_id = $mo->id;
                            $moItemAttr->mo_item_id = $moItem->id;
                            $moItemAttr->item_id = $groupedData->item_id;
                            $moItemAttr->item_code = $groupedData->item_code;
                            $moItemAttr->item_attribute_id = $moItemAttribute['attribute_id'];
                            $moItemAttr->attribute_name = $moItemAttribute['attribute_name'];
                            $moItemAttr->attribute_value = $moItemAttribute['attribute_value'];
                            $moItemAttr->save();
                        }
                    }
                }
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
                    $mediaFiles = $mo->uploadDocuments($request->file('attachment'), 'mo', false);
                }
                $mo->save();
            }

            foreach($request->all()['components'] as $component){
                $pwoMapping=PwoSoMapping::whereId($component['pwo_mapping_id'])->first();
                $pwoMapping->mo_product_qty+=$component['qty'];
                $pwoMapping->save();
            }
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
        $html = view('mfgOrder.partials.header-attribute', compact('item','attributeGroups','specifications'))->render();
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
                $componentHtml = view('mfgOrder.partials.item-row-edit', [
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

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];

        $detailItemId = $request->mo_item_id ?? null;
        $itemAttIds = [];
        if($detailItemId) {
            $detail = MoItem::where('id',$detailItemId)->where('item_id',$item?->id)->first();
            if($detail) {
                $itemAttIds = $detail->attributes()->pluck('item_attribute_id')->toArray();
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

        $html = view('mfgOrder.partials.comp-attribute',compact('item','rowCount','selectedAttr','itemAttributes'))->render();
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

    # Add item row
    public function addItemRow(Request $request)
    {
        $componentItem = json_decode($request->component_item,true) ?? [];
        $itemId = $componentItem['item_id'] ?? null;
        // $customerId = $request->customer_id ?? null;
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $compSelectedAttr = json_decode($request->comp_attr,true) ?? [];
        $attributes = [];
        if(count($compSelectedAttr)) {
               foreach($compSelectedAttr as $compAttr) {
                $itemAttr = ItemAttribute::where("item_id",$componentItem['item_id'])->first();
                if(!$itemAttr->all_checked) {
                    $itemAttr = ItemAttribute::where("item_id",$componentItem['item_id'])
                                    ->where("attribute_group_id",$compAttr['attr_name'])
                                    ->first();
                }
                $attributes[] = ['attribute_id' => $itemAttr?->id, 'attribute_value' => $compAttr['attr_value']];
               }
        }
        if(intval($itemId)) {
            $bomExists = ItemHelper::checkItemBomExists($itemId, $attributes);
            if (!$bomExists['bom_id']) {
                // $bomExists['message'] = $bomExists['message'];
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => $bomExists['message']]);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('mfgOrder.partials.item-row', [
            'rowCount' => $rowCount
        ])->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $storeId = $request->store_id;
        $subStoreId = $request->sub_store_id;
        $item = Item::find($request->item_id ?? null);
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $remark = $request->remark ?? null;
        $html = view('mfgOrder.partials.comp-item-detail',compact('item','selectedAttr','specifications','remark'))->render();

        // Fetch BOM (Bill of Materials) details with related item and UOM data
        $bomDetails = PwoBomMapping::with([
            // Eager load 'item' relationship, but only select required columns
            'item' => function ($query) {
                $query->select('id', 'item_code', 'item_name');
            },
            'uom' => function ($query) {
                $query->select('id', 'name');
            }
        ])
        ->where('pwo_mapping_id', $request->pwo_papping_id)
        ->select('id', 'item_id', 'uom_id', 'attributes', 'bom_qty', 'qty','station_id')
        ->get();

        return response()->json([
            'data' => [
                'html' => $html,
                'mo_product_component_html' => view('mfgOrder.partials.mo-product-components', compact('item', 'bomDetails', 'storeId', 'subStoreId'))->render(),
            ],
            'status' => 200,
            'message' => 'fetched.'
        ]);
    }

    # On select row get item detail
    public function getItemDetail2(Request $request)
    {

        $item = Item::find($request->item_id ?? null);
        $moItem = MoItem::find($request->mo_item_id ?? null);
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $inventoryStock = [];
        if($moItem) {
            $inventoryStock = InventoryHelper::totalInventoryAndStock($moItem->item_id, $selectedAttr, $moItem->uom_id, $moItem->mo->store_id);
        }
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $html = view('mfgOrder.partials.comp-item-detail2',compact('item','specifications','inventoryStock','selectedAttr'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # Bom edit
    public function edit(Request $request, $id)
    {
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = ConstantHelper::MO_SERVICE_ALIAS;
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $bom = MfgOrder::find($id);
        $createdBy = $bom->created_by;
        $revision_number = $bom->revision_number;
        $books = Helper::getBookSeriesNew($servicesAliasParam,$parentUrl, true)->get();
        $wasteTypes = ConstantHelper::DISCOUNT_TYPES;
        $selectedfyYear = Helper::getFinancialYear($doc->document_date ?? Carbon::now()->format('Y-m-d'));
        $creatorType = Helper::userCheck()['type'];
        $totalValue = 0;
        $buttons = Helper::actionButtonDisplay($bom->book_id,$bom->document_status , $bom->id, $totalValue, $bom->approval_level, $bom->created_by ?? 0, $creatorType, $revision_number);
        $revNo = $bom->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $bom->revision_number;
        }
        $docValue = $bom->total_value ?? 0;
        $approvalHistory = Helper::getApprovalHistory($bom->book_id, $bom->id, $revNo, $docValue, $createdBy);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$bom->document_status] ?? '';
        $view = 'mfgOrder.edit';

        if($request->has('revisionNumber') && $request->revisionNumber != $bom->revision_number) {
            $bom = $bom->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'mfgOrder.view';
        }

        $stations = Station::where('status', ConstantHelper::ACTIVE)
        ->get();
        $locations = InventoryHelper::getAccessibleLocations();
        $isEdit = $buttons['submit'];
        if(!$isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true: false;
        }

        $checkBomExist = ItemHelper::checkItemBomExists($bom->item_id, []);
        $productionBom = Bom::find($checkBomExist['bom_id'] ?? null);

        $response = BookHelper::fetchBookDocNoAndParameters($productionBom->book_id, $productionBom->document_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));

        $productionBomInstructions = optional($productionBom)
                                    ->bomInstructions()
                                    ->where('station_id', $bom->station_id)
                                    ->get() ?? collect();

        $machines = collect();
        if($productionBom) {
            $machines = $productionBom?->productionRoute?->machines()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        }
        return view($view, [
            'isEdit' => $isEdit,
            'wasteTypes' => $wasteTypes,
            'books' => $books,
            'bom' => $bom,
            'item' => isset($bom->item) ? $bom->item : null,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revision_number' => $revision_number,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'stations' => $stations,
            'locations' => $locations,
            'productionBomInstructions' => $productionBomInstructions,
            'current_financial_year' => $selectedfyYear,
            'sectionRequired' => $sectionRequired,
            'subSectionRequired' => $subSectionRequired,
            'machines' => $machines
        ]);
    }

    # Bom Update
    public function update(MoRequest $request, $id)
    {
       DB::beginTransaction();
        try {
            $mo = MfgOrder::find($id);
            $currentStatus = $mo->document_status;
            $actionType = $request->action_type;
            // if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            // {
            //     $revisionData = [
            //         ['model_type' => 'header', 'model_name' => 'MfgOrder', 'relation_column' => ''],
            //         ['model_type' => 'detail', 'model_name' => 'MoProduct', 'relation_column' => 'mo_id'],
            //         ['model_type' => 'detail', 'model_name' => 'MoItem', 'relation_column' => 'mo_id'],
            //         ['model_type' => 'detail', 'model_name' => 'MoBomMapping', 'relation_column' => 'mo_id'],
            //         ['model_type' => 'sub_detail', 'model_name' => 'MoItemAttribute', 'relation_column' => 'mo_id'],
            //         ['model_type' => 'sub_detail', 'model_name' => 'MoAttribute', 'relation_column' => 'mo_item_id'],
            //     ];
            //     $a = Helper::documentAmendment($revisionData, $id);
            // }

            $keys = ['deletedPiItemIds', 'deletedAttachmentIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }

            // if (count($deletedData['deletedAttachmentIds'])) {
            //     $medias = MoMedia::whereIn('id',$deletedData['deletedAttachmentIds'])->get();
            //     foreach ($medias as $media) {
            //         if ($request->document_status == ConstantHelper::DRAFT) {
            //             Storage::delete($media->file_name);
            //         }
            //         $media->delete();
            //     }
            // }
            $ctr = 0;

            if (count($deletedData['deletedPiItemIds'])) {
                MoItemAttribute::where('mo_id', $mo->id)->delete();
                MoItem::where('mo_id', $mo->id)->delete();
                MoBomMapping::where('mo_id', $mo->id)->delete();
                $moProductItems = MoProduct::whereIn('id', $deletedData['deletedPiItemIds'])->get();
                foreach($moProductItems as $moProductItem) {
                    # Back update PWO station consumption
                    if(isset($moProductItem->pwoMapping) && $moProductItem->pwoMapping) {
                        $pwoStation = PwoStationConsumption::where('pwo_mapping_id',$moProductItem?->pwoMapping?->id)
                                                ->where('station_id',$mo->station_id)
                                                ->first();
                        if($pwoStation) {
                            $pwoStation->mo_product_qty -= $moProductItem->qty;
                            $pwoStation->mo_id = null;
                            $pwoStation->save();
                        } else {
                            $moProductItem->pwoMapping->mo_id = null;
                            $moProductItem->pwoMapping->mo_product_qty -= $moProductItem->qty;
                            $moProductItem->pwoMapping->save();
                        }
                    }

                    $moProductItem->attributes()->delete();
                    $moProductItem->delete();
                }
                $ctr++;
            }

            $mo->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $mo->remarks = $request->remarks;
            # Extra Column
            $mo->save();

            if (isset($request->all()['components'])) {
                foreach($request->all()['components'] as $component) {
                    # MoProductDetail
                    if(isset($component['mo_product_id']) && $component['mo_product_id']) {
                        continue;
                    }
                    $ctr++;
                    $moProdDetail = new MoProduct;
                    $moProdDetail->mo_id = $mo->id;
                    $moProdDetail->item_id = $component['item_id'];
                    $moProdDetail->item_code = $component['item_code'];
                    $moProdDetail->customer_id = $component['customer_id'];
                    $moProdDetail->uom_id = $component['uom_id'];
                    $moProdDetail->qty = $component['qty'];
                    $moProdDetail->so_id = $component['so_id'] ?? null;
                    $moProdDetail->so_item_id = $component['so_item_id'] ?? null;
                    $moProdDetail->pwo_mapping_id = $component['pwo_mapping_id'] ?? null;
                    $moProdDetail->remark = $component['remark'] ?? null;
                    $moProdDetail->machine_id = $component['machine_id'] ?? null;
                    $moProdDetail->number_of_sheet = $component['sheet'] ?? 0;
                    $moProdDetail->save();
                    #Save MoProductDetailAttr component Attr
                    $attributes = [];
                    $newAttributes = [];
                    foreach($moProdDetail?->item?->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $moProdAttr = new MoProductAttribute;
                            $moProdAttr->mo_id = $mo->id;
                            $moProdAttr->mo_product_id = $moProdDetail->id;
                            $moProdAttr->item_attribute_id = $itemAttribute->id;
                            $moProdAttr->item_code = $component['item_code'];
                            $moProdAttr->attribute_name = $itemAttribute->attribute_group_id;
                            $moProdAttr->attribute_value = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $moProdAttr->save();
                            $attributes[] = ['attribute_id' => intval($itemAttribute?->id), 'attribute_value' => intval($moProdAttr->attribute_value)];
                            $newAttributes[] = [
                                'attribute_id' => intval($moProdAttr->attribute_value),
                                'attribute_name' => $moProdAttr?->headerAttribute?->name,
                                'attribute_value' => $moProdAttr?->headerAttributeValue?->value,
                                'item_attribute_id' => intval($moProdAttr->item_attribute_id),
                                'attribute_group_id' => intval($moProdAttr->attribute_name)
                            ];
                        }
                    }

                    # Back update PWO station consumption
                    if(isset($moProdDetail->pwoMapping) && $moProdDetail->pwoMapping) {
                        $pwoStation = PwoStationConsumption::where('pwo_mapping_id',$moProdDetail?->pwoMapping?->id)
                                                ->where('station_id',$mo->station_id)
                                                ->first();
                        if($pwoStation) {
                            $pwoStation->mo_product_qty += $moProdDetail->qty;
                            $pwoStation->mo_id = $mo->id;
                            $pwoStation->save();
                        } else {
                            $moProdDetail->pwoMapping->mo_id = $mo->id;
                            $moProdDetail->pwoMapping->mo_product_qty += $moProdDetail->qty;
                            $moProdDetail->pwoMapping->save();
                        }
                    }
                }

                if($ctr) {
                    MoItemAttribute::where('mo_id', $mo->id)->delete();
                    MoItem::where('mo_id', $mo->id)->delete();
                    MoBomMapping::where('mo_id', $mo->id)->delete();
                    $stationId = $mo->station_id ?? null;
                    foreach($mo->moProducts as $moProdDetail) {
                        $bomDetails = PwoBomMapping::where('pwo_mapping_id', $moProdDetail->pwo_mapping_id)
                                    ->where(function($query) use($stationId) {
                                        if($stationId) {
                                            $query->where('station_id', $stationId);
                                        }
                                    })
                                    ->get();
                        foreach ($bomDetails as $bomDetail) {
                            $moBomMapping = new MoBomMapping;
                            $moBomMapping->mo_id = $mo->id;
                            $moBomMapping->mo_product_id = $moProdDetail->id;
                            $moBomMapping->so_id = $bomDetail->so_id ?? null;
                            $moBomMapping->bom_id = $bomDetail->bom_id;
                            $moBomMapping->bom_detail_id = $bomDetail->bom_detail_id;
                            $moBomMapping->item_id = $bomDetail->item_id;
                            $moBomMapping->item_code = $bomDetail->item_code;
                            $moBomMapping->attributes = $bomDetail->attributes;
                            $moBomMapping->uom_id = $bomDetail->uom_id;
                            $moBomMapping->bom_qty = $bomDetail->bom_qty;
                            $moBomMapping->consumption_qty = $bomDetail->qty;
                            $moBomMapping->station_id = $bomDetail->station_id;
                            $moBomMapping->section_id = $bomDetail->section_id;
                            $moBomMapping->sub_section_id = $bomDetail->sub_section_id;
                            $moBomMapping->save();
                        }
                    }

                    if($mo->station_id) {
                        $prDetailParents = $mo->productionRoute->details()->where('pr_parent_id', $mo->station_id)->get();

                        if($prDetailParents?->count()) {
                            foreach($mo->moProducts as $moProduct) {
                                foreach($prDetailParents as $prDetailParent) {
                                    $pwoStationConsumption = PwoStationConsumption::where('pwo_mapping_id', $moProduct->pwo_mapping_id)
                                                    ->where('station_id', $prDetailParent->station_id)->first();
                                    $oldMo = MfgOrder::find($pwoStationConsumption->mo_id);

                                    $moProductAttributes = $moProduct->attributes ?? [];
                                    if($oldMo) {
                                        $oldMoProduct = $oldMo->moProducts()
                                                        ->where('item_id', $moProduct->item_id)
                                                        ->when($oldMo->so_id, function ($query) use ($oldMo) {
                                                            $query->where('so_id', $oldMo->so_id);
                                                        })
                                                        ->when(count($moProductAttributes), function ($query) use ($moProductAttributes) {
                                                            $query->whereHas('attributes', function ($piAttributeQuery) use ($moProductAttributes) {
                                                                $piAttributeQuery->where(function ($subQuery) use ($moProductAttributes) {
                                                                    foreach ($moProductAttributes as $poAttribute) {
                                                                        $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                                            $q->where('item_attribute_id', $poAttribute->item_attribute_id)
                                                                                ->where('attribute_value', $poAttribute->attribute_value);
                                                                        });
                                                                    }
                                                                });
                                                            }, '=', count($moProductAttributes));
                                                        })
                                                        ->first();

                                        $moBomAttributes = [];
                                        foreach($moProductAttributes as $moProdAttr) {
                                            $moBomAttributes[] = [
                                                'attribute_id' => $moProdAttr->item_attribute_id,
                                                'attribute_name' => $moProdAttr->attribute_name,
                                                'attribute_value' => $moProdAttr->attribute_value
                                            ];
                                        }
                                        $moBomMapping = new MoBomMapping;
                                        $moBomMapping->mo_id = $mo->id;
                                        $moBomMapping->mo_product_id = $moProduct->id;
                                        $moBomMapping->old_mo_product_id = $oldMoProduct->id ?? null;
                                        $moBomMapping->so_id = $moProduct->so_id ?? null;
                                        $moBomMapping->item_id = $moProduct->item_id;
                                        $moBomMapping->item_code = $moProduct->item_code;
                                        $moBomMapping->attributes = $moBomAttributes;
                                        $moBomMapping->uom_id = $moProduct->uom_id;
                                        $moBomMapping->rm_type = 'sf';
                                        $moBomMapping->bom_qty = 1;
                                        $moBomMapping->consumption_qty = $moProduct->qty;
                                        $moBomMapping->station_id = $prDetailParent->station_id;
                                        $moBomMapping->save();
                                    }
                                }
                            }
                        }
                    }
                    # Store Data In MoItem
                    $groupedDatas = MoBomMapping::selectRaw('mo_id, so_id, station_id, bom_detail_id, item_id, item_code, uom_id, rm_type, attributes, SUM(consumption_qty) as total_qty')
                        ->where('mo_id', $mo->id)
                        ->groupBy('mo_id', 'so_id', 'station_id', 'bom_detail_id', 'item_id', 'item_code', 'uom_id', 'rm_type', 'attributes')
                        ->get();

                        foreach($groupedDatas as $groupedData) {
                            # Mo Item Save
                            $moItem = new MoItem;
                            $moItem->mo_id = $mo->id;
                            $moItem->so_id = $groupedData->so_id ?? null;
                            $moItem->bom_detail_id = $groupedData->bom_detail_id;
                            $moItem->station_id = $groupedData->station_id;
                            $moItem->item_id = $groupedData->item_id;
                            $moItem->item_code = $groupedData->item_code;
                            $moItem->uom_id = $groupedData->uom_id;
                            $moItem->rm_type = $groupedData->rm_type;
                            $moItem->qty = $groupedData->total_qty;
                            $moItem->inventory_uom_id = $groupedData?->item?->uom_id;
                            $moItem->inventory_uom_code = $groupedData?->item?->uom?->name;
                            $moItem->inventory_uom_qty = $groupedData->total_qty;
                            $moItem->save();
                            # Mo Item Attribute Save
                            $moItemAttributes = $groupedData->attributes;
                            foreach($moItemAttributes as $moItemAttribute) {
                                $moItemAttr = new MoItemAttribute;
                                $moItemAttr->mo_id = $mo->id;
                                $moItemAttr->mo_item_id = $moItem->id;
                                $moItemAttr->item_id = $groupedData->item_id;
                                $moItemAttr->item_code = $groupedData->item_code;
                                $moItemAttr->item_attribute_id = $moItemAttribute['attribute_id'];
                                $moItemAttr->attribute_name = $moItemAttribute['attribute_name'];
                                $moItemAttr->attribute_value = $moItemAttribute['attribute_value'];
                                $moItemAttr->save();
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
                }
            }

            $mo->save();

            /*Bom Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $mo->uploadDocuments($request->file('attachment'), 'mo', true);
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
            return response()->json([
                'message' => 'Error occurred while updating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // genrate pdf
    public function generatePdf(Request $request, $id , $pattern = "potrait")
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $mo = MfgOrder::findOrFail($id);
        $specifications = collect();
        $products = collect();
        $items = collect();
        if(isset($mo->moProducts) && $mo->moProducts)
        {
            $products = $mo -> moProducts;
        }

        if(isset($mo->moItems) && $mo->moItems) {
            $items = $mo->moItems;
        }

        $totalAmount = $mo->total_value;
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $approvedBy = "";
        $approvedBy = Helper::getDocStatusUser(get_class($mo), $mo -> id, $mo -> document_status);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mo->document_status] ?? '';
        $dynamicFields = $mo -> dynamic_fields ?? [];
        $dataArray=[
            'order'=> $mo,
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
        ];
        if($pattern == "landscape")
        {
            $pdf =  PDF::loadView('pdf.mo',$dataArray)->setPaper('a4', 'landscape');
        } else {
            $pdf =  PDF::loadView('pdf.mo',$dataArray)->setPaper('a4', 'potrait');
        }
        // $pdf->setPaper('a4', 'landscape');
        // $pdf->setOption('isHtml5ParserEnabled', true);
        return $pdf->stream('MfgOrder-' . date('Y-m-d') . '.pdf');
    }

    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $bom = MfgOrder::find($request->id);
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
            if(isset($request->all()['productions'])) {
                foreach($request->all()['productions'] as $index => $component) {
                    $moProdItemId = $component['mo_production_item_id'];
                    $moProdItem = MoProductionItem::find($moProdItemId);
                    $stockData = $moProdItem->getInventoryAndStock();
                    $toBeProduced = $moProdItem->required_qty - floatval($stockData['confirmedStocks'] ?? 0);
                    $inputProduced = floatval($component['produced_qty']) ?? 0;

                    if($toBeProduced > $inputProduced) {
                        return response() -> json([
                            'status' => 'error',
                            'message' => "Insufficient produced quantity. Required: {$toBeProduced}, Provided: {$inputProduced}. Found at row " . ($index + 1) . "."
                        ]);
                    }
                    $moProdItem->produced_qty = $inputProduced;
                    $moProdItem->save();
                    $stationId = $moProdItem?->mo?->station_id ?? null;

                    if($moProdItem->required_qty != $inputProduced) {
                        $bomDetails = BomDetail::selectRaw('item_id, SUM(qty) as total_qty')
                        ->where('bom_id', $moProdItem->production_bom_id)
                        ->where(function($query) use($stationId){
                            if($stationId) {
                                $query->where('station_id', $stationId);
                            }
                        })
                        ->groupBy('item_id')
                        ->get();

                        foreach ($bomDetails as $bomDetail) {
                            $moItem = MoItem::where('mo_id',$bom->id)
                                        ->where('item_id', $bomDetail->item_id)
                                        ->first();
                            $moItem->inventory_uom_qty = $bomDetail->total_qty * $inputProduced;
                            $moItem->qty = $bomDetail->total_qty * $inputProduced;
                            $moItem->save();
                        }
                    }

                }
            }
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
                        'message' => "Error while updating stock ledger for issue.",
                    ]);
                }

                # Update rate in  Mo Prod Item & insert in Production Item Location
                $moProdItems = MoProductionItem::where('mo_id', $bom->id)->get();
                $moProdItemQty = MoProductionItem::where('mo_id', $bom->id)->sum('produced_qty');

                if(floatval($moProdItemQty) > 0) {
                    $detailIds = [];
                    $moItemValue = MoItem::where('mo_id', $bom->id)->sum(DB::raw('qty * rate'));
                    $prodItemRate = $moItemValue / $moProdItemQty;

                    foreach($moProdItems as $moProdItem) {
                        $detailIds[] = $moProdItem->id;
                        $moProdItem->rate = $prodItemRate;
                        $moProdItem->save();
                        $moProdItemLocation = new MoProductionItemLocation;
                        $moProdItemLocation->mo_id = $moProdItem->mo_id;
                        $moProdItemLocation->mo_production_item_id = $moProdItem->id;
                        $moProdItemLocation->item_id = $moProdItem->item_id;
                        $moProdItemLocation->item_code = $moProdItem->item_code;
                        $moProdItemLocation->store_id = $moProdItem?->mo?->store_id;
                        $moProdItemLocation->sub_store_id = $moProdItem?->mo?->sub_store_id;
                        $moProdItemLocation->store_code = $moProdItem?->mo?->store_location?->store_code;
                        $moProdItemLocation->quantity = $moProdItem->produced_qty;
                        $moProdItemLocation->inventory_uom_qty = $moProdItem->produced_qty;
                        $moProdItemLocation->save();
                    }

                    $moProdItemReceipt = InventoryHelper::settlementOfInventoryAndStock($bom->id, $detailIds, ConstantHelper::MO_SERVICE_ALIAS, ConstantHelper::APPROVED, 'receipt');

                    if($moProdItemReceipt['messsage'] != 'Success') {
                        DB::rollBack();
                        return response() -> json([
                            'status' => 'error',
                            'message' => "Error while updating stock ledger for receipt.",
                        ]);
                    }
                }

                #to be used after reservation is handled
                // $moProductionId = $bom?->moProductions[0]->id;
                // $stockLedgerId = StockLedger::where('book_type',ConstantHelper::MO_SERVICE_ALIAS)
                //                 ->where('document_header_id',$bom->id)
                //                 ->where('document_detail_id', $moProductionId)
                //                 ->where('organization_id',$bom->organization_id)
                //                 ->where('transaction_type','receipt')
                //                 ->value('id');
                foreach($bom->moProducts as $moProdDetail) {
                    # Stock Reservation
                    #to be used after reservation is handled
                    // if($stockLedgerId) {
                    //     $stockReservation = new StockLedgerReservation;
                    //     $stockReservation->stock_ledger_id = $stockLedgerId;
                    //     $stockReservation->mo_id = $bom->id;
                    //     $stockReservation->mo_production_item_id = $moProductionId;
                    //     $stockReservation->so_id = $moProdDetail?->pwoMapping?->so_id;
                    //     $stockReservation->so_item_id = $moProdDetail?->pwoMapping?->so_item_id;
                    //     $stockReservation->quantity = ($moProdDetail->qty * $bom->sf_qty );
                    //     $stockReservation->save();
                    //     $stockReservation->stockLedger->reserved_qty += $stockReservation->quantity;
                    //     $stockReservation->stockLedger->save();
                    // }

                    # Update PWO
                    if(isset($moProdDetail->pwoMapping) && $moProdDetail->pwoMapping) {

                        # Update Mo Value
                        $moProductCost = MoBomMapping::where('mo_id',$bom->id)
                                        ->where('mo_product_id',$moProdDetail->id)
                                        ->selectRaw('SUM(qty*rate) as total_value')
                                        ->first();
                        $moProductRate = $moProductCost->total_value / $moProdDetail->qty;
                        $moProdDetail->rate = round($moProductRate,6);
                        $moProdDetail->save();

                        $pwoStation = PwoStationConsumption::where('pwo_mapping_id', $moProdDetail->pwo_mapping_id)
                        ->where('station_id', $bom->station_id)
                        ->first();

                        if($pwoStation) {
                            $pwoStation->mo_value = round(($pwoStation->mo_product_qty*$moProdDetail->rate),2);
                            $pwoStation->save();
                            $currentLevel = $moProdDetail?->pwoMapping?->current_level ?? 1;
                            $pwoQty = $moProdDetail?->pwoMapping?->inventory_uom_qty;

                            $pwoStationExit = PwoStationConsumption::where('pwo_mapping_id',$moProdDetail?->pwoMapping?->id)
                                                    ->where('level', $currentLevel)
                                                    ->where('mo_product_qty', '<', $pwoQty)
                                                    ->first();
                            if(!$pwoStationExit) {
                                $moIds = MoProduct::where('pwo_mapping_id',$moProdDetail?->pwoMapping?->id)
                                            ->pluck('mo_id')
                                            ->toArray();
                                $pendingStatus = [ConstantHelper::DRAFT,ConstantHelper::SUBMITTED,ConstantHelper::REJECTED,ConstantHelper::PARTIALLY_APPROVED,ConstantHelper::APPROVED,ConstantHelper::APPROVAL_NOT_REQUIRED];
                                if(count($moIds)) {
                                    $pendingMo = MfgOrder::whereIn('id',$moIds)
                                    ->whereIn('document_status',$pendingStatus)
                                    ->count();
                                    if(!$pendingMo) {
                                        $lastLevel = PwoStationConsumption::where('pwo_mapping_id',$moProdDetail?->pwoMapping?->id)
                                                                    ->max('level');
                                        if($currentLevel < $lastLevel) {
                                            $moProdDetail->pwoMapping->current_level += 1;
                                            $moProdDetail->pwoMapping->save();
                                        } else {
                                           $totalValue = PwoStationConsumption::where('pwo_mapping_id',$moProdDetail->pwo_mapping_id)
                                                                                ->sum('mo_value');
                                            $moProdDetail->pwoMapping->mo_product_qty = $moProdDetail->pwoMapping->inventory_uom_qty;
                                            $moProdDetail->pwoMapping->mo_value = $totalValue;
                                            $moProdDetail->pwoMapping->save();
                                        }
                                    }
                                }
                            }
                        } else {
                            $moProdDetail->pwoMapping->mo_product_qty = $moProdDetail->qty;
                            $moProdDetail->pwoMapping->mo_value = round(($moProdDetail->qty*$moProdDetail->rate),2);
                            $moProdDetail->pwoMapping->save();
                        }
                    }
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
        $issueRecords = InventoryHelper::settlementOfInventoryAndStock($mo->id, $detailIds, ConstantHelper::MO_SERVICE_ALIAS, ConstantHelper::APPROVED, 'issue');
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
                                ->where('transaction_type','issue')
                                ->selectRaw('document_detail_id,sum(org_currency_cost) as cost')
                                ->groupBy('document_detail_id')
                                ->get();

            foreach($stockLedgers as $stockLedger) {
                $moItem = MoItem::find($stockLedger->document_detail_id);
                $moItem->rate = floatval($stockLedger->cost) / floatval($moItem->qty);
                $moItem->save();
                $attributes = [];
                foreach($moItem->attributes as $moItemAttribute) {
                    $attributes[] = ['attribute_id' => $moItemAttribute->item_attribute_id, 'attribute_value' => $moItemAttribute->attribute_value];
                }
                MoBomMapping::where('mo_id',$mo->id)
                            ->where('item_id',$moItem->item_id)
                            ->whereJsonContains('attributes', $attributes)
                            ->update(['rate' => $moItem->rate]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function getPwo(Request $request)
    {
       $selectedPwoIds = json_decode($request->selected_pwo_ids,true) ?? [];
       $seriesId = $request->series_id ?? null;
       $docNumber = $request->document_number ?? null;
       $itemId = $request->item_id ?? null;
       $customerId = $request->customer_id ?? null;
       $headerBookId = $request->header_book_id ?? null;
       $stationId = $request->station_id ?? null;
        //$itemSearch = $request->item_search ?? null;
       $storeId = $request->store_id ?? null;
       $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
       $pwoItems = PwoSoMapping::whereHas('pwo', function ($subQuery) use ($request, $applicableBookIds, $docNumber, $stationId) {
                $subQuery->whereIn('book_id', $applicableBookIds)
                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                ->where(function($pwoQuery) use($stationId) {
                    if($stationId) {
                        $pwoQuery->whereIn('station_wise_consumption', ['yes']);
                    } else {
                        $pwoQuery->whereIn('station_wise_consumption', ['no']);
                    }
                })
               ->when($request->book_id, function ($bookQuery) use ($request) {
                   $bookQuery->where('book_id', $request->book_id);
               })
               ->when($docNumber, function ($query) use ($docNumber) {
                   $query->where('document_number', 'LIKE', "%{$docNumber}%");
               });
       })
       ->whereColumn('qty', '>', 'mo_product_qty')
       ->withCount('stations')
       ->where(function ($query) use($stationId) {
            $query->whereDoesntHave('stations')
                  ->orWhereHas('stations', function ($stationQuery) use($stationId) {
                    if($stationId) {
                        $stationQuery->where('station_id', $stationId);
                    }
                  });
       })
       ->where(function ($query) use ($selectedPwoIds, $customerId, $itemId, $storeId) {
            $query->where('store_id', $storeId);
            if($itemId) {
                $query->where('item_id', $itemId);
            }
            $query->whereHas('bom', function ($subQuery) {
                $subQuery->whereIn('production_type', ['In-house']);
            });
            if(count($selectedPwoIds)) {
                $query->whereNotIn('id', $selectedPwoIds);
            }
            if($customerId) {
                $query->whereHas('so',function($soQuery) use ($customerId) {
                    $soQuery->where('customer_id', $customerId);
                });
            }
       });

       if ($stationId) {
            $pwoItems->whereHas('stations', function ($query) use ($stationId) {
                $query->join('erp_pwo_so_mapping as pwo', 'pwo.id', '=', 'erp_pwo_station_consumptions.pwo_mapping_id')
                    ->whereColumn('pwo.inventory_uom_qty', '>', 'erp_pwo_station_consumptions.mo_product_qty')
                    ->whereColumn('pwo.current_level', '=', 'erp_pwo_station_consumptions.level')
                    ->where('erp_pwo_station_consumptions.station_id', $stationId);
            });
        }
        $pwoItems = $pwoItems->with(['pwo', 'item'])->get();


        $html = view('mfgOrder.partials.pwo-item-list', ['pwoItems' => $pwoItems, 'station_id' => $stationId])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Get Quotation Bom Item List
    public function getPwoCreate(Request $request)
    {
       $storeId = $request->store_id ?? null;
       $seriesId = $request->series_id ?? null;
       $docNumber = $request->document_number ?? null;
       $soSeriesId = $request->so_series_id ?? null;
       $soSocNumber = $request->so_document_number ?? null;
       $itemId = $request->item_id ?? null;
        if($itemId) {
            $bomExists = Bom::where('item_id', $itemId)
                 ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
                 ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                 ->exists();
             if(!$bomExists) {
                return response()->json(['data' => ['pis' => ""], 'status' => 422, 'message' => "Bom not exist!"]);
             }
            $bomExists = Bom::where('item_id', $itemId)
                         ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
                         ->whereIn('production_type', ['In-house'])
                         ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                         ->exists();
             if (!$bomExists ) {
                 return response()->json(['data' => ['pis' => ""], 'status' => 422, 'message' => "Only products with production type In-house are allowed."]);
             }
        }
       $customerId = $request->customer_id ?? null;
       $headerBookId = $request->header_book_id ?? null;
       $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
       $pwoItems = PwoSoMapping::whereHas('pwo', function ($subQuery) use ($applicableBookIds, $docNumber, $seriesId) {
                $subQuery->whereIn('book_id', $applicableBookIds)
               ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
               ->when($seriesId, function ($bookQuery) use ($seriesId) {
                   $bookQuery->where('book_id', $seriesId);
               })
               ->when($docNumber, function ($query) use ($docNumber) {
                   $query->where('document_number', 'LIKE', "%{$docNumber}%");
               });
       })
       ->whereColumn('qty', '>', 'mo_product_qty')
       ->where(function ($query) use ($customerId, $itemId, $soSeriesId, $soSocNumber, $storeId) {
            if($itemId){
                $query->where('item_id', $itemId);
            }
            $query->whereHas('bom', function ($subQuery) {
                $subQuery->whereIn('production_type', ['In-house']);
            });
            $query->where('store_id', $storeId);
            if($soSeriesId) {
                $query->whereHas('so', function ($soQuery) use ($soSeriesId) {
                    $soQuery->where('book_id', $soSeriesId);
                });
            }
            if($soSocNumber) {
                $query->whereHas('so', function ($soQuery) use ($soSocNumber) {
                    $soQuery->where('document_number', 'LIKE', "%{$soSocNumber}%");
                });
            }
            if($customerId) {
                $query->whereHas('so',function($soQuery) use ($customerId) {
                    $soQuery->where('customer_id', $customerId);
                });
            }
       });

        $pwoItems->whereHas('stations', function ($query) {
            $query->join('erp_pwo_so_mapping as pwo', 'pwo.id', '=', 'erp_pwo_station_consumptions.pwo_mapping_id')
                ->whereColumn('pwo.inventory_uom_qty', '>', 'erp_pwo_station_consumptions.mo_product_qty')
                ->whereColumn('pwo.current_level', '=', 'erp_pwo_station_consumptions.level');
        });

        $pwoItems = $pwoItems->with(['pwo', 'item'])->get();
        $bom = Bom::where('item_id', $itemId)
            ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->first();
        $machines = collect();
        if($bom) {
            $machines = $bom?->productionRoute?->machines()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        }
        $rowCount = 1;
        $html = view('mfgOrder.partials.mo-item-pull', ['pwoItems' => $pwoItems, 'rowCount' => $rowCount, 'machines' => $machines])->render();
        return response()->json(['data' => ['pis' => $html, 'is_machine' => $machines->count() > 0, 'machines' => $machines], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit PWO Item list
    public function processPwoItem(Request $request)
    {
        $rowCount = intval($request->rowCount) ? intval($request->rowCount) + 1  : 1;
        $ids = json_decode($request->ids,true) ?? [];
        $pwoItems = PwoSoMapping::whereIn('id', $ids)->get();
        $itemId = $request->item_id ?? null;
        $bom = Bom::where('item_id', $itemId)
            ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->first();
        $machines = collect();
        if($bom) {
            $machines = $bom?->productionRoute?->machines()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        }

        $html = view('mfgOrder.partials.mo-item-pull', [
            'pwoItems' => $pwoItems,
            'is_pull' => true,
            'rowCount' => $rowCount,
            'machines' => $machines
            ])->render();
        return response()->json(['data' => ['pos' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Get Posting details
    public function getPostingDetails(Request $request)
    {
        try {
        $data = FinancialPostingHelper::financeVoucherPosting((int)$request->book_id ?? 0, $request->document_id ?? 0, $request->type ?? 'get');
        if(!$data['status']) {
            return response() -> json([
                'status' => false,
                'data' => [],
                'message' => $data['message']
            ]);
        }
        $document_date = $data['data']['document_date'] ?? '';
        $book_code = $data['data']['book_code'] ?? '';
        $document_number = $data['data']['document_number'] ?? '';
        $currency_code = $data['data']['currency_code'] ?? '';
        $html = view('mfgOrder.partials.post-voucher-list',['data' => $data])->render();
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

    public function getSubStore(Request $request)
    {
        $storeId = $request->store_id;
        $results = InventoryHelper::getAccesibleSubLocations($storeId ?? 0,null, [ConstantHelper::SHOP_FLOOR]);
        return response()->json(['data' => $results, 'status' => 200, 'message' => "fetched!"]);
    }
    public function moReport(Request $request)
    {
        $pathUrl = route('mo.index');
        $orderType = ConstantHelper::MO_SERVICE_ALIAS;
        $moProduct = MoProduct::with([
                'mo',
                'mo.store_location',
                'mo.sub_store',
                'mo.moItems.item',
                'mo.moItems.uom',
                'mo.moItems.attributes',
                'item',
                'uom',
                'attributes',
                'mo.dynamic_fields'
            ])->whereHas('mo', function ($headerQuery) use($orderType, $pathUrl, $request) {
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
                $itemQuery -> withWhereHas('items', function ($itemSubQuery) use($request) {
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
            $dynamicFields = DynamicFieldHelper::getServiceDynamicFields(ConstantHelper::MO_SERVICE_ALIAS);

            $datatables = DataTables::of($moProduct)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $status = $row->mo->document_status ?? ConstantHelper::DRAFT;
                    $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$status];
                    $displayStatus = ucfirst($status);
                    $editRoute = route('mo.edit', ['id' => $row->mo->id]);

                    return "
                        <div style='text-align:right;'>
                            <span class='badge rounded-pill $statusClass badgeborder-radius'>$displayStatus</span>
                            <a href='$editRoute'>
                                <i class='cursor-pointer' data-feather='eye'></i>
                            </a>
                        </div>
                    ";
                })
                ->addColumn('book_name', fn($row) => $row->mo->book_code)
                ->addColumn('document_number', fn($row) => $row->mo->document_number)
                ->addColumn('document_date', fn($row) => $row->mo->document_date)
                ->addColumn('store_name', fn($row) => $row->mo?->store_location?->store_name)
                ->addColumn('sub_store_name', fn($row) => $row->mo?->sub_store?->name)
                ->addColumn('product_name', fn($row) => $row?->item?->item_name)
                ->addColumn('product_code', fn($row) => $row->item_code)
                ->addColumn('product_uom_name', fn($row) => $row->uom?->name)
                ->editColumn('product_qty', fn($row) => number_format($row->qty, 2))
                ->addColumn('product_attributes', function ($row) {
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
                })
                ->addColumn('item_code', fn($row) => $row->mo->moItems?->item?->item_code ?? '')
                ->addColumn('item_name', fn($row) => $row->mo->moItems?->item?->item_name ?? '')
                ->addColumn('item_uom_name', fn($row) => $row->mo->moItems?->uom?->name ?? '')
                ->addColumn('item_qty', fn($row) => number_format($row->mo->moItems?->qty ?? 0, 2))
                ->addColumn('item_consumed_qty', fn($row) => number_format($row->mo->moItems?->consumed_qty ?? 0, 2))
                ->addColumn('item_attributes', function ($row) {
                    $attributesUi = '';
                    $attrs = $row->mo->moItems->attributes ?? collect();
                    if (count($attrs) > 0) {
                        foreach ($attrs as $soAttribute) {
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
                $datatables = $datatables->addColumn($field->name, function ($row) use ($field) {
                    $fieldMap = $row->mo->dynamic_fields->keyBy('dynamic_field_detail_id');
                    return $fieldMap[$field->id]->value ?? '';
                });
            }

            return $datatables
                ->rawColumns(['status', 'product_attributes', 'item_attributes'])
                ->make(true);
        }

    public function getMachineDetail(Request $request)
    {
        $machineId = $request->machine_id;
        $attrGroupIds = explode(',', $request->attr_ids ?? '');
        $attrValueIds = explode(',', $request->attr_value_ids ?? '');

        $machineDetail = ErpMachineDetail::where('machine_id', $machineId)
                            ->whereIn('attribute_group_id', $attrGroupIds)
                            ->whereIn('attribute_id', $attrValueIds)
                            ->first();
        if(!$machineDetail) {
            return response()->json(['data' => [], 'status' => 404, 'message' => "Machine detail not found!"]);
        }
        $moQty = floatval($request->qty)  ?? 0;
        $sheet = $moQty / $machineDetail->no_of_pairs;
        $sheet = ceil($sheet);
        return response()->json(['data' => ['sheet' => $sheet], 'status' => 200, 'message' => "fetched!"]);
    }

    // Check bom inhouse
    public function checkBomInhouse(Request $request)
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

        $bomExists = Bom::where('item_id', $item->id)
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

        $bomExists = Bom::where('item_id', $item->id)
        ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
        ->whereIn('production_type', ['In-house'])
        ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
        ->exists();

        return response()->json([
            'data' => ['is_bom' => $bomExists],
            'status' => 200,
            'message' => $bomExists ? 'Fetched!' : "Only products with production type In-house are allowed."
        ]);
    }
}
