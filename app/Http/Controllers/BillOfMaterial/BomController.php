<?php

namespace App\Http\Controllers\BillOfMaterial;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\CurrencyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BomRequest;
use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Bom;
use App\Models\BomAttribute;
use App\Models\BomDetail;
use App\Models\BomMedia;
use App\Models\BomOverhead;
use App\Models\Book;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\ItemAttribute;
use App\Models\BomNormsCalculation;
use Auth;
use DB;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\BookHelper;
use App\Models\BomInstruction;
use App\Models\Overhead;
use App\Models\ProductionRoute;
use App\Models\Station;

class BomController extends Controller
{
    # Bill of material list
    public function index(Request $request)
    {
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam === ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }
        if (request()->ajax()) {
            $type = $request->type == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
            $boms = Bom::withDefaultGroupCompanyOrg()
                    ->where('type',$type)
                    ->where('bom_type',ConstantHelper::FIXED)
                    ->withDraftListingLogic()
                    ->latest();
            return DataTables::of($boms)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) use ($type) {
                    return view('partials.action-dropdown', [
                        'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                        'displayStatus' => $row->display_status,
                        'row' => $row,
                        'actions' => [
                            [
                                'url' => function ($r) use ($type) {
                                    if ($type == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
                                        return url('quotation-bom/edit/' . $r->id);
                                    }
                                    return route('bill.of.material.edit', $r->id);
                                },
                                'icon' => 'edit-3',
                                'label' => 'View/ Edit Detail',
                            ]
                        ]
                    ])->render();
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book ? $row->book?->book_code : 'N/A';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? 'N/A';
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('item_code', function ($row) {
                    return $row->item ? $row->item?->item_code : 'N/A';
                })
                ->addColumn('item_name', function ($row) {
                    return $row->item ? $row->item?->item_name : 'N/A';
                })
                ->addColumn('attributes', function ($row) {
                   $attributes = $row->bomAttributes;
                   $html = '';
                   foreach($attributes as $attribute) {
                   $attr = AttributeGroup::where('id', intval($attribute->attribute_name))->first();
                   $attrValue = Attribute::where('id', intval($attribute->attribute_value))->first();
                       if ($attr && $attrValue) { 
                            $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->name}</strong>: {$attrValue->value}</span>";
                       } else {
                            // $html .= "<span class='badge rounded-pill badge-light-secondary'><strong>Attribute not found</strong></span>";
                       }
                   }
                    return $html;
                })
                ->addColumn('uom_name', function ($row) {
                    return $row->uom ? $row->uom?->name : 'N/A';
                })
                ->addColumn('components', function ($row) {
                    return $row->bomItems->count();
                })
                ->editColumn('total_item_value', function ($row) use($canView) {
                    if($canView) {
                        return number_format($row->total_item_value,2);
                    }
                    return "";
                })
                ->addColumn('overhead', function ($row) use($canView) {
                    if($canView) {
                        return number_format(($row->item_overhead_amount + $row->header_overhead_amount),2);
                    }
                    return "";
                })
                ->addColumn('total_cost', function ($row) use($canView) {
                    if($canView) {
                        return number_format(($row->total_item_value + $row->item_overhead_amount + $row->header_overhead_amount),2);
                    }
                    return "";
                })
                ->rawColumns(['document_status', 'attributes'])
                ->make(true);
        }
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);

        return view('billOfMaterial.index', ['servicesBooks' => $servicesBooks]);
    }

    # Bill of material Create
    public function create(Request $request)
    {
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);

        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $productionTypes = ['In-house','Job Work'];
        $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
        $productionRoutes = ProductionRoute::withDefaultGroupCompanyOrg()
                                ->where('status', ConstantHelper::ACTIVE)
                                ->get();
        
        $customizables = ['yes','no'];
        return view('billOfMaterial.create', [
            'books' => $books,
            'productionTypes' => $productionTypes,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'productionRoutes' => $productionRoutes,
            'customizables' => $customizables,
            'canView' => $canView
        ]);
    }

    #Bill of material store
    public function store(BomRequest $request)
    {
        # check validation
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }
        
        $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $stationRequired = isset($parameters['station_required']) && is_array($parameters['station_required']) && in_array('yes', array_map('strtolower', $parameters['station_required']));
        if($stationRequired && $request->document_status == ConstantHelper::SUBMITTED) {
            $allStations = [];
            foreach ($request->input('components', []) as $index => $component) {
                $stationId = isset($component['station_id']) ? $component['station_id'] : null;
                if($stationId) {
                    $allStations[] = intval($stationId);
                } 
            }
            $allStations = array_unique($allStations);
            $productionStationIds = [];
            $productionRouteId = $request->production_route_id;
            $productionRoute = ProductionRoute::find($productionRouteId);
            if($productionRoute) {
                $productionStationIds = $productionRoute->details()->where('consumption','yes')->pluck('station_id')->toArray(); 
            }
            if($allStations !== $productionStationIds) {
                $arrayDiff = array_diff($productionStationIds, $allStations);
                if(count($arrayDiff)) {
                    $arrayDiff = array_values($arrayDiff);
                    $station = Station::whereIn('id', $arrayDiff)->pluck('name')->implode(',');
                    $message = "Consumption not defined for {$station}.";
                    return response()->json([
                        'message' => $message,
                        'error' => "",
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            # Bom Header save
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first(); 
            $bom = new Bom;
            $bom->type = $request->type ?? ConstantHelper::BOM_SERVICE_ALIAS; 
            $bom->organization_id = $organization->id;
            $bom->group_id = $organization->group_id;
            $bom->company_id = $organization->company_id;
            $bom->uom_id = $request->uom_id;
            $bom->production_type = $request->production_type;
            $bom->item_id = $request->item_id;
            $bom->item_code = $request->item_code;
            $bom->item_name = $request->item_name;
            $bom->revision_number = $request->revision_number ?? 0;
            $bom->customer_id = $request->customer_id ?? null;
            $bom->production_route_id = $request->production_route_id ?? null;
            $bom->customizable = $request->customizable ?? 'no';
            $bom->safety_buffer_perc = $request->safety_buffer_perc ?? null;
            // $bom->status = $request->status;
            $bom->remarks = $request->remarks;
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
            $regeneratedDocExist = Bom::withDefaultGroupCompanyOrg()->where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
                }

            $bom->doc_number_type = $numberPatternData['type'];
            $bom->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $bom->doc_prefix = $numberPatternData['prefix'];
            $bom->doc_suffix = $numberPatternData['suffix'];
            $bom->doc_no = $numberPatternData['doc_no'];
            /**/

            $bom->book_id = $request->book_id;
            $bom->book_code = $request->book_code;
            $bom->document_number = $document_number;
            $bom->document_date = $request->document_date ?? now();
            $bom->save();
            # Store Instruction item
            if (isset($request->all()['instructions'])) {
                foreach($request->all()['instructions'] as $index => $instruction) {
                    $bomInstruction = new BomInstruction;
                    $bomInstruction->bom_id = $bom->id;
                    $bomInstruction->station_id = $instruction['station_id'];
                    $bomInstruction->section_id = $instruction['section_id'] ?? null;
                    $bomInstruction->sub_section_id = $instruction['sub_section_id'] ?? null;
                    $bomInstruction->instructions = $instruction['instructions'];
                    $bomInstruction->save();
                    # Instruction Attachment Save
                    if (!empty($instruction['attachment']) && is_array($instruction['attachment'])) {
                        $uploadedFiles = $request->file("instructions.$index.attachment");
                        if (!empty($uploadedFiles)) {
                            $mediaFiles = $bomInstruction->uploadDocuments($uploadedFiles, 'bom_instruction', false);
                        }
                    }
                }
            }

            if($bom->type == ConstantHelper::BOM_SERVICE_ALIAS) {
                $quote_bom_id = $request->quote_bom_id;
                $quoteBom = Bom::find($quote_bom_id);
                if($quoteBom) {
                    $quoteBom->production_bom_id = $bom->id;
                    $quoteBom->save();
                }
            }

            # Save header attribute
            foreach($bom->item->itemAttributes as  $key => $itemAttribute) {
                $key = $key + 1;
                $headerAttr = @$request->all()['attributes'][$key];
                if (isset($headerAttr['attr_group_id'][$itemAttribute->attribute_group_id])) {
                    $bomAttr = new BomAttribute;
                    $bomAttr->bom_id = $bom->id;
                    $bomAttr->item_attribute_id = $itemAttribute->id;
                    $bomAttr->item_id = $bom->item->id;
                    $bomAttr->type = 'H';
                    $bomAttr->item_code = $request->item_code;
                    $bomAttr->attribute_name = $itemAttribute->attribute_group_id;
                    $bomAttr->attribute_value = @$headerAttr['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                    $bomAttr->save();
                }
            }

            if (isset($request->all()['components'])) {
                $consumptionMethod = $request->consumption_method;
                foreach($request->all()['components'] as $component) {
                    # Bom Detail Save
                    $bomDetail = new BomDetail;
                    $bomDetail->bom_id = $bom->id;
                    $bomDetail->item_id = $component['item_id'];
                    $bomDetail->vendor_id = $component['vendor_id'] ?? null;
                    $bomDetail->item_code = $component['item_code'];
                    $bomDetail->qty = $component['qty'] ?? 0.00;
                    $bomDetail->uom_id = $component['uom_id'] ?? null;
                    $bomDetail->superceeded_cost = $component['superceeded_cost'] ?? 0.00;
                    $bomDetail->item_cost = $component['item_cost'] ?? 0.00;
                    $bomDetail->item_value = $component['item_value'] ?? 0.00;
                    $bomDetail->overhead_amount = $component['overhead_amount'] ?? 0.00;
                    $bomDetail->total_amount = $component['item_total_cost'] ?? 0.00;
                    $bomDetail->sub_section_id = $component['sub_section_id'] ?? null;
                    $bomDetail->section_name = $component['section_name'] ?? null;
                    $bomDetail->sub_section_name = $component['sub_section_name'] ?? null;
                    $bomDetail->station_name = $component['station_name'] ?? null;
                    $bomDetail->station_id = $component['station_id'] ?? null;
                    $bomDetail->remark = $component['remark'] ?? null;
                    $bomDetail->save();

                    # Store Norms
                    if($consumptionMethod != 'manual') {
                        # Norms
                        $normData = [
                            'bom_id' => $bom->id,
                            'bom_detail_id' => $bomDetail->id,
                        ];
                        $updateData = [
                            'qty_per_unit' => $component['qty_per_unit'] ?? 0.00,
                            'total_qty' => $component['total_qty'] ?? 0.00,
                            'std_qty' => $component['std_qty'] ?? 0.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        if($updateData['qty_per_unit'] && $updateData['total_qty'] && $updateData['std_qty']){
                            BomNormsCalculation::updateOrCreate($normData, $updateData);
                        }
                    }
                    // else {
                    //     # Manual
                    // }
                    #Save component Attr
                    foreach($bomDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $bomAttr = new BomAttribute;
                            $bomAttr->bom_id = $bom->id;
                            $bomAttr->bom_detail_id = $bomDetail->id;
                            $bomAttr->item_attribute_id = $itemAttribute->id;
                            $bomAttr->type = 'D';
                            $bomAttr->item_code = $component['item_code'];
                            $bomAttr->item_id = $component['item_id'];
                            $bomAttr->attribute_name = $itemAttribute->attribute_group_id;
                            $bomAttr->attribute_value = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $bomAttr->save();
                        }
                    }

                    #Save item overhead
                    if (isset($component['overhead'])) {
                        foreach($component['overhead'] as $overhead) {
                            if(isset($overhead['amnt']) && $overhead['amnt'] && $overhead['overhead_id']) {
                                $bomOverhead = new BomOverhead;
                                $bomOverhead->level = 1;
                                $bomOverhead->bom_id = $bom->id;
                                $bomOverhead->bom_detail_id = $bomDetail->id;
                                $bomOverhead->type = 'D';
                                $bomOverhead->overhead_id = $overhead['overhead_id'];
                                $bomOverhead->overhead_description = $overhead['description'];
                                $bomOverhead->overhead_perc = $overhead['perc'] !== '' ? floatval($overhead['perc']) : null;
                                $bomOverhead->overhead_amount = floatval($overhead['amnt'] ?? 0);
                                $bomOverhead->ledger_name = $overhead['ledger_name'];
                                $bomOverhead->ledger_id = $overhead['ledger_id'];
                                $bomOverhead->ledger_group_id = $overhead['ledger_group_id'];
                                $bomOverhead->save();
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

            // #Save summary overhead
            // if (isset($request->overhead) && $bom->id) {
            //     foreach($request->overhead as $overSummary) {
            //         if($overSummary['description'] && $overSummary['amnt']) {
            //             $bomOverhead = new BomOverhead;
            //             $bomOverhead->bom_id = $bom->id;
            //             // $bomOverhead->bom_detail_id = $bomDetail->id;
            //             $bomOverhead->type = 'H';
            //             $bomOverhead->overhead_description = $overSummary['description'];
            //             $bomOverhead->ledger_name = $overSummary['leadger'];
            //             $bomOverhead->overhead_amount = $overSummary['amnt'] ?? 0.00;
            //             $bomOverhead->save();
            //         }
            //     }
            // }

            $overheadLevelCount = intval($request->orverhead_level_count) ?? 1;
            $normalizedLevel = 1;
            for ($i = 1; $i <= $overheadLevelCount; $i++) {
                $headerOverheads = $request->input("header.$i.overhead", []);
                $validOverheads = array_filter($headerOverheads, function ($headerOverhead) {
                    return isset($headerOverhead['overhead_id']) && floatval($headerOverhead['amnt'] ?? 0) > 0;
                });
                if (empty($validOverheads)) {
                    continue;
                }
                foreach ($validOverheads as $headerOverhead) {
                    $bomOverhead = new BomOverhead;
                    $bomOverhead->level = $normalizedLevel;
                    $bomOverhead->bom_id = $bom->id;
                    $bomOverhead->type = 'H';
                    $bomOverhead->overhead_id = $headerOverhead['overhead_id'];
                    $bomOverhead->overhead_description = $headerOverhead['description'];
                    $bomOverhead->overhead_perc = $headerOverhead['perc'] !== '' ? floatval($headerOverhead['perc']) : null;
                    $bomOverhead->overhead_amount = floatval($headerOverhead['amnt'] ?? 0);
                    $bomOverhead->ledger_name = $headerOverhead['ledger_name'];
                    $bomOverhead->ledger_id = $headerOverhead['ledger_id'];
                    $bomOverhead->ledger_group_id = $headerOverhead['ledger_group_id'];
                    $bomOverhead->save();
                }
                $normalizedLevel++;
            }


            /*Update Bom header*/
            $bom->total_item_value = $bom->bomItems()->sum('item_value') ?? 0.00;
            $bom->item_overhead_amount = $bom->bomComponentOverheadItems()->sum('overhead_amount') ?? 0.00;
            $bom->header_overhead_amount = $bom->bomOverheadItems()->where('type','H')->sum('overhead_amount') ?? 0.00;
            $bom->save();

            /*Create document submit log*/
            $modelName = get_class($bom);
            $totalValue = $bom->total_value ?? 0;
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $bom->book_id; 
                $docId = $bom->id;
                $remarks = $bom->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $bom->approval_level ?? 1;
                $revisionNumber = $bom->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
            }

            if ($request->document_status == 'submitted') {
                // $totalValue = $bom->total_value ?? 0;
                // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                $bom->document_status = $approveDocument['approvalStatus'] ?? $request->document_status;
            } else {
                $bom->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }

            /*Bom Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $bom->uploadDocuments($request->file('attachment'), 'bom', false);
            }

            $bom->save();

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $bom,
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
        $attributeGroups = AttributeGroup::withDefaultGroupCompanyOrg()
                        ->with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
        $item = Item::find($request->item_id);
        $specifications = collect();
        $moduleType = $request->type ?? 'bom';
        $customerId = $request->customer_id ?? null;
        if($request->type == 'bom') {
            $bomExists = Bom::where('item_id', $item->id)
                ->where(function ($query) use ($customerId) {
                    if ($customerId) {
                        $query->where('customer_id', $customerId);
                    }
                })
                ->where('status', ConstantHelper::ACTIVE)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_SUBMITTED)
                ->first();
            if ($bomExists) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => "Bom already exists for this item."]);
            }
        }
        if($item) {
            $item->uom;
            $specifications = $item->specifications()->whereNotNull('value')->get();
        }
        $html = view('billOfMaterial.partials.header-attribute',compact('item','attributeGroups','specifications'))->render();
        return response()->json(['data' => ['html' => $html, 'item' => $item], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {

        $rowCount = intval($request->rowCount) ?? 1;
        $currentTab = $request->current_tab ?? '';
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];

        $detailItemId = $request->bom_detail_id ?? null;
        $itemAttIds = [];
        if($detailItemId) {
            $detail = BomDetail::find($detailItemId);
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
        // dd($selectedAttr);
        $html = view('billOfMaterial.partials.comp-attribute',compact('item','rowCount','selectedAttr','itemAttributes'))->render();
        $hiddenHtml = '';
        foreach ($itemAttributes as $attribute) {
                $selected = '';
                foreach ($attribute->attributes() as $value){
                    if (in_array($value->id, $selectedAttr)){
                        $selected = $value->id;
                    }
                }
            if($currentTab == 'production-items') {
                $hiddenHtml .= "<input type='hidden' name='productions[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
            } else {
                $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
            }
        }
        $vendorItem = $item?->approvedVendors?->first() ?? null;
        $vendorName = ""; 
        $vendorId = ""; 
        if($vendorItem) {
            $vendorName = $vendorItem->vendor ? $vendorItem->vendor->company_name : '';  
            $vendorId = $vendorItem->vendor ? $vendorItem->vendor->id : '';  
        }

        return response()->json(['data' => ['vendor_id' => $vendorId, 'vendor_name' => $vendorName, 'attr' => $item->itemAttributes->count(),'html' => $html, 'hiddenHtml' => $hiddenHtml,], 'status' => 200, 'message' => 'fetched.']);
    }

    # Add item row
    public function addItemRow(Request $request)
    {
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }

        $item = json_decode($request->item,true) ?? [];
        $componentItem = json_decode($request->component_item,true) ?? [];
        $moduleType = $request->type ?? null;
        $customerId = $request->customer_id ?? null;
        /*Check header mandatory*/
        if ($item['selectedAttrRequired'] && $item['item_code']) {
            if(!$item['item_code'] || $item['selectedAttrRequired']) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all the header details before adding components.']);
            }
        }
        if(!$item['item_id']) {
            return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all the header details before adding components.']);
        }

        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }

        $compSelectedAttr = json_decode($request->comp_attr,true) ?? []; 
        $attributes = [];
        if(count($compSelectedAttr)) {
               foreach($compSelectedAttr as $compAttr) {
                $itemAttr = ItemAttribute::where("item_id",$componentItem['item_id'])
                                ->where("attribute_group_id",$compAttr['attr_name'])
                                ->first();
                $attributes[] = ['attribute_id' => $itemAttr?->id, 'attribute_value' => $compAttr['attr_value']];
               }
        }


        $bomExists = ItemHelper::checkItemBomExists($componentItem['item_id'], $attributes);
        $itemType = $bomExists['sub_type'];

        if(in_array($itemType ,['Finished Goods', 'WIP/Semi Finished'])) {
            if (!$bomExists['bom_id']) {
                $compItem = Item::find($componentItem['item_id']);
                $name = $compItem?->item_name;
                $bomExists['message'] = "Bom doesn't exist for $itemType product $name";
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => $bomExists['message']]);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;

        $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->d_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $stationRequired = true;
        $supercedeCostRequired = false;
        $componentWasteRequired = false;
        $componentOverheadRequired = isset($parameters['component_overhead_required']) && is_array($parameters['component_overhead_required']) && in_array('yes', array_map('strtolower', $parameters['component_overhead_required']));

        $html = view('billOfMaterial.partials.item-row', [
            'rowCount' => $rowCount,
            'sectionRequired' => $sectionRequired,
            'subSectionRequired' => $subSectionRequired,
            'stationRequired' => $stationRequired,
            'supercedeCostRequired' => $supercedeCostRequired,
            'componentWasteRequired' => $componentWasteRequired,
            'componentOverheadRequired' => $componentOverheadRequired,
            'canView' => $canView,
        ])->render();

        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # Add Instruction row
    public function addInstructionRow(Request $request)
    {
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->d_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));

        $html = view('billOfMaterial.partials.instruction-row', [
            'rowCount' => $rowCount,
            'sectionRequired' => $sectionRequired,
            'subSectionRequired' => $subSectionRequired
        ])->render();

        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $sectionName = $request->section_name ?? '';
        $subSectionName = $request->sub_section_name ?? '';
        $stationName = $request->station_name ?? '';
        $remark = $request->remark ?? null;
        $qty_per_unit = floatval($request->qty_per_unit) ?? 0;
        $total_qty = floatval($request->total_qty) ?? 0;
        $std_qty = floatval($request->std_qty) ?? 0;
        $output = $total_qty > 0 ? ($std_qty / $total_qty * $qty_per_unit) : 0;
        $html = view('billOfMaterial.partials.comp-item-detail',compact('item','selectedAttr','specifications','sectionName','subSectionName','stationName','remark','qty_per_unit','total_qty','std_qty','output'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # Bom edit
    public function edit(Request $request, $id)
    {
        $parentUrl = request() -> segments()[0];
        $canView = true;
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->back();
        }
        $bom = Bom::find($id);
        $createdBy = $bom->created_by; 
        $revision_number = $bom->revision_number;
        $books = Helper::getBookSeriesNew($servicesAliasParam,$parentUrl, true)->get();
        $headerAttributes = $bom->bomAttributes()->where('type','H')->get();
        $selectedAttributes = $headerAttributes->pluck('attribute_value')->all();
        $totalValue = ($bom->total_item_value + $bom->item_overhead_amount + $bom->header_overhead_amount);
        $creatorType = Helper::userCheck()['type'];
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
        $specifications = collect();
        if(isset($bom->item) && $bom->item) {
            $specifications = $bom->item->specifications()->whereNotNull('value')->get();
        }
        $productionTypes = ['In-house','Job Work'];

        $view = 'billOfMaterial.edit';

        if($request->has('revisionNumber') && $request->revisionNumber != $bom->revision_number) {
            $bom = $bom->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'billOfMaterial.view';
        }

        $response = BookHelper::fetchBookDocNoAndParameters($bom->book_id, $bom->document_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $stationRequired = true;
        $supercedeCostRequired = false;
        $componentWasteRequired = false;
        $componentOverheadRequired = isset($parameters['component_overhead_required']) && is_array($parameters['component_overhead_required']) && in_array('yes', array_map('strtolower', $parameters['component_overhead_required']));
        $productionRoutes = ProductionRoute::withDefaultGroupCompanyOrg()
                                ->where('status', ConstantHelper::ACTIVE)
                                ->get();
        $customizables = ['yes','no'];
        $isEdit = $buttons['submit'];
        if(!$isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true: false;
        }
        $headerOverheads = $bom->bomOverheadItems()->where('type','H')->orderBy('level')->get();
        return view($view, [
            'isEdit' => $isEdit,
            'books' => $books,
            'bom' => $bom,
            'item' => isset($bom->item) ? $bom->item : null,
            'headerAttributes' => $headerAttributes,
            'selectedAttributes' => $selectedAttributes,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'specifications' => $specifications,
            'productionTypes' => $productionTypes,
            'revision_number' => $revision_number,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'sectionRequired' => $sectionRequired,
            'subSectionRequired' => $subSectionRequired,
            'stationRequired' => $stationRequired,
            'supercedeCostRequired' => $supercedeCostRequired,
            'componentWasteRequired' => $componentWasteRequired,
            'componentOverheadRequired' => $componentOverheadRequired,
            'productionRoutes' => $productionRoutes,
            'customizables' => $customizables,
            'headerOverheads' => $headerOverheads,
            'canView' => $canView
        ]); 
    }

    # Bom Update
    public function update(BomRequest $request, $id)
    {
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }
        # check validation
        $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $stationRequired = isset($parameters['station_required']) && is_array($parameters['station_required']) && in_array('yes', array_map('strtolower', $parameters['station_required']));
        if($stationRequired && $request->document_status == ConstantHelper::SUBMITTED) {
            $allStations = [];
            foreach ($request->input('components', []) as $index => $component) {
                $stationId = isset($component['station_id']) ? $component['station_id'] : null;
                if($stationId) {
                    $allStations[] = intval($stationId);
                } 
            }
            $allStations = array_unique($allStations);
            $productionStationIds = [];
            $productionRouteId = $request->production_route_id;
            $productionRoute = ProductionRoute::find($productionRouteId);
            if($productionRoute) {
                $productionStationIds = $productionRoute->details()->where('consumption','yes')->pluck('station_id')->toArray(); 
            }
            if($allStations !== $productionStationIds) {
                $arrayDiff = array_diff($productionStationIds, $allStations);
                if(count($arrayDiff)) {
                    $arrayDiff = array_values($arrayDiff);
                    $station = Station::whereIn('id', $arrayDiff)->pluck('name')->implode(',');
                    $message = "Consumption not defined for {$station}.";
                    return response()->json([
                        'message' => $message,
                        'error' => "",
                    ], 422);
                }
            }
        }

       DB::beginTransaction();
        try {
            $bom = Bom::find($id);
            $currentStatus = $bom->document_status;
            $actionType = $request->action_type;
            $bom->customizable = $request->customizable ?? 'no';
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'Bom', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'BomDetail', 'relation_column' => 'bom_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'BomAttribute', 'relation_column' => 'bom_detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'BomOverhead', 'relation_column' => 'bom_detail_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'BomNormsCalculation', 'relation_column' => 'bom_detail_id']
                ];
                $a = Helper::documentAmendment($revisionData, $id);
            }

            $keys = ['deletedHeaderOverheadIds', 'deletedItemOverheadIds', 'deletedBomItemIds', 'deletedAttachmentIds', 'deletedProdItemIds', 'deletedInstructionItemIds'];
            $deletedData = [];

            foreach ($keys as $key) {
                $deletedData[$key] = json_decode($request->input($key, '[]'), true);
            }
            if (count($deletedData['deletedHeaderOverheadIds'])) {
                BomOverhead::whereIn('id',$deletedData['deletedHeaderOverheadIds'])->delete();
            }

            if (count($deletedData['deletedItemOverheadIds'])) {
                BomOverhead::whereIn('id',$deletedData['deletedItemOverheadIds'])->delete();
            }

            if (count($deletedData['deletedAttachmentIds'])) {
                $medias = BomMedia::whereIn('id',$deletedData['deletedAttachmentIds'])
                        // ->where('model_type', get_class($bom))    
                        ->get();
                foreach ($medias as $media) {
                    if ($request->document_status == ConstantHelper::DRAFT) {
                        Storage::delete($media->file_name);
                    }
                    $media->delete();
                }
            }

            if (count($deletedData['deletedBomItemIds'])) {
                $bomItems = BomDetail::whereIn('id',$deletedData['deletedBomItemIds'])->get();
                foreach($bomItems as $bomItem) {
                    $bomItem->overheads()->delete();
                    $bomItem->delete();
                }
            }

            if (count($deletedData['deletedInstructionItemIds'])) {
                BomInstruction::whereIn('id',$deletedData['deletedInstructionItemIds'])->delete();
            }

            # Bom Header save
            $bom->uom_id = $request->uom_id;
            $bom->production_type = $request->production_type;
            $bom->item_id = $request->item_id;
            $bom->item_code = $request->item_code;
            $bom->item_name = $request->item_name;
            $bom->production_route_id = $request->production_route_id;
            $bom->safety_buffer_perc = $request->safety_buffer_perc ?? null;
            $bom->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $bom->remarks = $request->remarks;
            # Extra Column
            $bom->save();
            
            # Store Instruction item
            if (isset($request->all()['instructions'])) {
                foreach($request->all()['instructions'] as $index => $instruction) {
                    $bomInstruction = BomInstruction::find($instruction['id'] ?? null) ?? new BomInstruction;
                    $bomInstruction->bom_id = $bom->id;
                    $bomInstruction->station_id = $instruction['station_id'];
                    $bomInstruction->section_id = $instruction['section_id'] ?? null;
                    $bomInstruction->sub_section_id = $instruction['sub_section_id'] ?? null;
                    $bomInstruction->instructions = $instruction['instructions'];
                    $bomInstruction->save();
                    # Instruction Attachment Save
                    if (!empty($instruction['attachment']) && is_array($instruction['attachment'])) {
                        $uploadedFiles = $request->file("instructions.$index.attachment");
                        if (!empty($uploadedFiles)) {
                            $mediaFiles = $bomInstruction->uploadDocuments($uploadedFiles, 'bom_instruction', false);
                        }
                    }
                }
            }

            if($bom->type == ConstantHelper::BOM_SERVICE_ALIAS) {
                $quote_bom_id = $request->quote_bom_id;
                $quoteBom = Bom::find($quote_bom_id);
                if($quoteBom) {
                    Bom::where('production_bom_id', $bom->id)->update(['production_bom_id' => null]);
                    $quoteBom->production_bom_id = $bom->id;
                    $quoteBom->save();
                }
            }

            # Save header attribute
            foreach($bom->item->itemAttributes as  $key => $itemAttribute) {
                $key = $key + 1;
                $headerAttr = @$request->all()['attributes'][$key];
                if (isset($headerAttr['attr_group_id'][$itemAttribute->attribute_group_id])) {

                    $bomAttrId = @$headerAttr['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'] ?? null;

                    $bomAttr = BomAttribute::find($bomAttrId) ?? new BomAttribute;
                    $bomAttr->bom_id = $bom->id;
                    $bomAttr->item_attribute_id = $itemAttribute->id;
                    $bomAttr->item_id = $bom->item->id;
                    $bomAttr->type = 'H';
                    $bomAttr->item_code = $request->item_code;
                    $bomAttr->attribute_name = $itemAttribute->attribute_group_id;
                    $bomAttr->attribute_value = @$headerAttr['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                    $bomAttr->save();
                }
            }

            if (isset($request->all()['components'])) {
                $consumptionMethod = $request->consumption_method;
                foreach($request->all()['components'] as $component) {
                    # Bom Detail Save
                    $bomDetail = BomDetail::find(@$component['bom_detail_id']) ?? new BomDetail;
                    $bomDetail->bom_id = $bom->id;
                    $bomDetail->vendor_id = $component['vendor_id'] ?? null;
                    $bomDetail->item_id = $component['item_id'];
                    $bomDetail->item_code = $component['item_code'];
                    $bomDetail->qty = $component['qty'] ?? 0.00;
                    $bomDetail->uom_id = $component['uom_id'] ?? null;
                    $bomDetail->superceeded_cost = $component['superceeded_cost'] ?? 0.00;
                    $bomDetail->item_cost = $component['item_cost'] ?? 0.00;
                    $bomDetail->item_value = $component['item_value'] ?? 0.00;
                    $bomDetail->overhead_amount = $component['overhead_amount'] ?? 0.00;
                    $bomDetail->total_amount = $component['item_total_cost'] ?? 0.00;
                    $bomDetail->sub_section_id = $component['sub_section_id'] ?? null;
                    $bomDetail->section_name = $component['section_name'] ?? null;
                    $bomDetail->sub_section_name = $component['sub_section_name'] ?? null;
                    $bomDetail->station_name = $component['station_name'] ?? null;
                    $bomDetail->station_id = $component['station_id'] ?? null;
                    $bomDetail->remark = $component['remark'] ?? null;
                    $bomDetail->save();

                    # Norms Save
                    if($consumptionMethod != 'manual') {
                        # Norms
                        $normData = [
                            'bom_id' => $bom->id,
                            'bom_detail_id' => $bomDetail->id,
                        ];
                        $updateData = [
                            'qty_per_unit' => $component['qty_per_unit'] ?? 0.00,
                            'total_qty' => $component['total_qty'] ?? 0.00,
                            'std_qty' => $component['std_qty'] ?? 0.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        if($updateData['qty_per_unit'] && $updateData['total_qty'] && $updateData['std_qty']){
                            BomNormsCalculation::updateOrCreate($normData, $updateData);
                        }
                    }
                    // else {
                    //     # Manual
                    // }

                    #Save component Attr
                    foreach($bomDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                            $bomAttrId = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_id'];
                            $bomAttr = BomAttribute::find($bomAttrId) ?? new BomAttribute;
                            $bomAttr->bom_id = $bom->id;
                            $bomAttr->bom_detail_id = $bomDetail->id;
                            $bomAttr->item_id = $component['item_id'];
                            $bomAttr->item_attribute_id = $itemAttribute->id;
                            $bomAttr->type = 'D';
                            $bomAttr->item_code = $component['item_code'];
                            $bomAttr->attribute_name = $itemAttribute->attribute_group_id;
                            $bomAttr->attribute_value = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                            $bomAttr->save();
                        }
                    }

                    #Save item overhead
                    if (isset($component['overhead'])) {
                        foreach($component['overhead'] as $overhead) {
                            if(isset($overhead['amnt']) && $overhead['amnt'] && $overhead['overhead_id']) {
                                $bomOverheadId = @$overhead['id'];
                                $bomOverhead = BomOverhead::find($bomOverheadId) ?? new BomOverhead;
                                $bomOverhead->level = 1;
                                $bomOverhead->bom_id = $bom->id;
                                $bomOverhead->bom_detail_id = $bomDetail->id;
                                $bomOverhead->type = 'D';
                                $bomOverhead->overhead_id = $overhead['overhead_id'];
                                $bomOverhead->overhead_description = $overhead['description'];
                                $bomOverhead->overhead_perc = $overhead['perc'] !== '' ? floatval($overhead['perc']) : null;
                                $bomOverhead->overhead_amount = floatval($overhead['amnt'] ?? 0);
                                $bomOverhead->ledger_name = $overhead['ledger_name'];
                                $bomOverhead->ledger_id = $overhead['ledger_id'];
                                $bomOverhead->ledger_group_id = $overhead['ledger_group_id'];
                                $bomOverhead->save();
                            }
                        }
                    }

                    #Save item overhead
                    // if (isset($component['overhead'])) {
                    //     foreach($component['overhead'] as $overhead) {
                    //         if (isset($overhead['amnt']) && $overhead['amnt']) {
                    //             $bomOverheadId = @$overhead['id'];
                    //             $bomOverhead = BomOverhead::find($bomOverheadId) ?? new BomOverhead;
                    //             $bomOverhead->bom_id = $bom->id;
                    //             $bomOverhead->bom_detail_id = $bomDetail->id;
                    //             $bomOverhead->type = 'D';
                    //             $bomOverhead->overhead_description = $overhead['description'] ?? null;
                    //             $bomOverhead->ledger_name = $overhead['leadger'] ?? null;
                    //             $bomOverhead->overhead_amount = $overhead['amnt'] ?? 0.00;
                    //             $bomOverhead->save();
                    //         }
                    //     }
                    // }
                }
            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }

            #Save summary overhead
            // if (isset($request->overhead) && $bom->id) {
            //     foreach($request->overhead as $overSummary) {
            //         if($overSummary['description'] && $overSummary['amnt']) {
            //             $bomOverhead = BomOverhead::find(@$overSummary['id']) ?? new BomOverhead;
            //             $bomOverhead->bom_id = $bom->id;
            //             // $bomOverhead->bom_detail_id = $bomDetail->id;
            //             $bomOverhead->type = 'H';
            //             $bomOverhead->overhead_description = $overSummary['description'];
            //             $bomOverhead->ledger_name = $overSummary['leadger'];
            //             $bomOverhead->overhead_amount = $overSummary['amnt'] ?? 0.00;
            //             $bomOverhead->save();
            //         }
            //     }
            // }
            $overheadLevelCount = intval($request->orverhead_level_count) ?? 1;
            $normalizedLevel = 1;
            for($i = 1; $i <= $overheadLevelCount; $i++) {
                $headerOverheads = $request->input("header.$i.overhead", []);
                $validOverheads = array_filter($headerOverheads, function ($row) {
                    return isset($row['overhead_id']) && floatval($row['amnt'] ?? 0) > 0;
                });
                if (count($validOverheads) === 0) {
                    continue;
                }
                foreach ($validOverheads as $headerOverhead) {
                    $bomOverhead = BomOverhead::find($headerOverhead['id'] ?? null) ?? new BomOverhead;
                    $bomOverhead->level = $normalizedLevel;
                    $bomOverhead->bom_id = $bom->id;
                    $bomOverhead->type = 'H';
                    $bomOverhead->overhead_id = $headerOverhead['overhead_id'];
                    $bomOverhead->overhead_description = $headerOverhead['description'];
                    $bomOverhead->overhead_perc = $headerOverhead['perc'] !== '' ? floatval($headerOverhead['perc']) : null;
                    $bomOverhead->overhead_amount = floatval($headerOverhead['amnt'] ?? 0);
                    $bomOverhead->ledger_name = $headerOverhead['ledger_name'];
                    $bomOverhead->ledger_id = $headerOverhead['ledger_id'];
                    $bomOverhead->ledger_group_id = $headerOverhead['ledger_group_id'];
                    $bomOverhead->save();
                }
                $normalizedLevel++;
            }

            /*Bom Attachment*/
            if ($request->hasFile('attachment')) {
                $mediaFiles = $bom->uploadDocuments($request->file('attachment'), 'bom', false);
            }
            /*Update Bom header*/
            $bom->total_item_value = $bom->bomItems()->sum('item_value') ?? 0.00;
            $bom->item_overhead_amount = $bom->bomComponentOverheadItems()->sum('overhead_amount') ?? 0.00;
            $bom->header_overhead_amount = $bom->bomOverheadItems()->sum('overhead_amount') ?? 0.00;
            $bom->save();

            /*Create document submit log*/
            $bookId = $bom->book_id; 
            $docId = $bom->id;
            $amendRemarks = $request->amend_remarks ?? null;
            $remarks = $bom->remarks;
            $amendAttachments = $request->file('amend_attachment');
            $attachments = $request->file('attachment');
            $currentLevel = $bom->approval_level;
            $modelName = get_class($bom);
            $totalValue = $bom->total_value ?? 0;
            if($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment')
            {
                //*amendmemnt document log*/
                $revisionNumber = $bom->revision_number + 1;
                $actionType = 'amendment';
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                $bom->revision_number = $revisionNumber;
                $bom->approval_level = 1;
                $bom->revision_date = now();
                $amendAfterStatus = $approveDocument['approvalStatus'] ??  $bom->document_status;
                // $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                // if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                //     $totalValue = $bom->total_value ?? 0;
                //     $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                // }
                // if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                //     $actionType = 'submit';
                //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                // }
                $bom->document_status = $amendAfterStatus;
                $bom->save();
            } else {
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $revisionNumber = $bom->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                }
                if ($request->document_status == 'submitted') {
                    // $totalValue = $bom->total_value ?? 0;
                    // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    $bom->document_status = $approveDocument['approvalStatus'] ?? $bom->document_status;
                } else {
                    $bom->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                }
            }
            $bom->save();

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $bom,
            ]);   
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        } 
    }

    # Get Bom item cost
    public function getItemCost(Request $request)
    {
        $selectedAttributes = json_decode($request->itemAttributes,true);
        $itemId = $request->item_id;
        $result = ItemHelper::getChildBomItemCost($itemId, $selectedAttributes);
        $itemCost = $result['cost'];
        if(!floatval($itemCost)) {
            $uomId = $request->uom_id ?? null;
            $currency =  CurrencyHelper::getOrganizationCurrency();
            $currencyId = $currency->id ?? null; 
            $transactionDate = $request->transaction_date ?? date('Y-m-d');
            if($request->type == ConstantHelper::BOM_SERVICE_ALIAS) {
                $itemCost = ItemHelper::getItemCostPrice($itemId, $selectedAttributes, $uomId, $currencyId, $transactionDate);
            } else {
                $itemCost = ItemHelper::getItemSalePrice($itemId, $selectedAttributes, $uomId, $currencyId, $transactionDate);
            }
        }
        return response()->json(['data' => ['cost' => $itemCost,'route' => $result['route'] ?? null], 'status' => 200, 'message' => 'fetched bom header item cost']);
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
        $bom = Bom::findOrFail($id);

        $canView = true;

        if($bom?->type === ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.edit');
        } 
        if($bom?->type === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.edit');
        }

        $title = 'Production Bom';
        if($bom->type != ConstantHelper::BOM_SERVICE_ALIAS) {
            $title = 'Quotation Bom'; 
        }
        $specifications = collect();
        if(isset($bom->item) && $bom->item) {
            $specifications = $bom->item->specifications()->whereNotNull('value')->get();
        }

        $totalAmount = $bom->total_value;
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        $imagePath = public_path('assets/css/midc-logo.jpg');
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$bom->document_status] ?? '';

        $response = BookHelper::fetchBookDocNoAndParameters($bom->book_id, $bom->document_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $pdf = PDF::loadView(
            'pdf.bom',
            [
                'bom'=> $bom,
                'title'=> $title,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress,
                'totalAmount'=>$totalAmount,
                'amountInWords'=>$amountInWords,
                'imagePath' => $imagePath,
                'specifications' => $specifications,
                'docStatusClass' => $docStatusClass,
                'sectionRequired' => $sectionRequired,
                'subSectionRequired' => $subSectionRequired,
                'canView' => $canView
            ]
        );

        $pdf->setOption('isHtml5ParserEnabled', true);
        return $pdf->stream(str_replace(' ', '', $title) .'-'. date('Y-m-d') . '.pdf');
    }

    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $bom = Bom::find($request->id);
            if (isset($bom)) {
                $revoke = Helper::approveDocument($bom->book_id, $bom->id, $bom->revision_number, '', [], 0, ConstantHelper::REVOKE, $bom->total_value, get_class($bom));
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

    # Get Quotation Bom Item List
    public function getQuoteBom(Request $request)
    {
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }

        $seriesId = $request->series_id ?? null;
        $docNumber = $request->document_number ?? null;
        $itemId = $request->item_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $departmentId = $request->department_id ?? null;
        $customerId = $request->customer_id ?? null;
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);
        $piItems = Bom::where(function($query) use ($seriesId,$applicableBookIds,$docNumber,$itemId,$departmentId, $customerId) {
                        $query->whereHas('item');
                        $query->whereNull('production_bom_id');
                        $query->where('type',ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS);
                        $query->withDefaultGroupCompanyOrg();
                        $query->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
                        if($seriesId) {
                            $query->where('book_id',$seriesId);
                        } else {
                            if(count($applicableBookIds)) {
                                $query->whereIn('book_id',$applicableBookIds);
                            }
                        }
                        if($docNumber) {
                            $query->where('document_number', 'LIKE', "%$docNumber%");
                        }
                        if($departmentId) {
                            $query->where('department_id', $departmentId);
                        }
                        if ($itemId) {
                            $query->where('item_id', $itemId);
                        }
                        if($customerId) {
                            $query->where('customer_id', $customerId);
                        }
                })
                ->get();
        $html = view('billOfMaterial.partials.q-bom-list', ['piItems' => $piItems, 'canView' => $canView])->render();
        return response()->json(['data' => ['pis' => $html], 'status' => 200, 'message' => "fetched!"]);
    }

    # Submit PI Item list
    public function processBomItem(Request $request)
    {
        $canView = true;
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('quotation_bom.item_cost_view');
        } 
        if($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = request()->user()?->hasPermission('production_bom.item_cost_view');
        }

        $ids = json_decode($request->ids,true) ?? [];
        $bom = Bom::with('uom:id,name')
                ->whereIn('id', $ids)
                ->first();

        $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->d_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $stationRequired = true;
        $supercedeCostRequired = false;
        $componentWasteRequired = false;
        $componentOverheadRequired = isset($parameters['component_overhead_required']) && is_array($parameters['component_overhead_required']) && in_array('yes', array_map('strtolower', $parameters['component_overhead_required']));
        $consumption_method = isset($parameters['consumption_method']) && $parameters['consumption_method'][0] == 'manual' ? false : true;
        
        $html = view('billOfMaterial.partials.item-row-edit', [
            'bom' => $bom,
            'is_pull' => true,
            'sectionRequired' => $sectionRequired,
            'subSectionRequired' => $subSectionRequired,
            'stationRequired' => $stationRequired,
            'supercedeCostRequired' => $supercedeCostRequired,
            'componentWasteRequired' => $componentWasteRequired,
            'componentOverheadRequired' => $componentOverheadRequired,
            'consumption_method' => $consumption_method,
            'canView' => $canView
            ])->render();

        $specifications = collect();
        if(isset($bom->item) && $bom->item) {
            $specifications = $bom->item->specifications()->whereNotNull('value')->get();
        }

        $headerAttributes = $bom->bomAttributes()->where('type','H')->get();
        $selectedAttributes = $headerAttributes->pluck('attribute_value')->all();

        $headerAttrHtml = view('billOfMaterial.partials.header-attribute-edit', [
            'specifications' => $specifications,
            'item' => $bom->item,
            'bom' => $bom,
            'selectedAttributes' => $selectedAttributes
            ])->render();
        $headerOverheads = $bom->bomOverheadItems()->where('type','H')->orderBy('level')->get();
        $headerOverhead = view('billOfMaterial.partials.overhead.add-comp-level', ['headerOverheads' => $headerOverheads])->render();
        $instructionHtml = view('billOfMaterial.partials.instruction-row-edit', ['bom' => $bom,'sectionRequired' => $sectionRequired, 'subSectionRequired' => $subSectionRequired])->render();
        return response()->json(['data' => ['bom' => $bom, 'pos' => $html, 'headerAttrHtml' => $headerAttrHtml, 'instructionHtml' => $instructionHtml, 'headerOverhead' => $headerOverhead], 'status' => 200, 'message' => "fetched!"]);
    }

    # Add Overhead Level
    public function addOverheadLevel(Request $request)
    {
        $selectedIds = json_decode($request->ids,true) ?? [];
        $results = Overhead::withDefaultGroupCompanyOrg()
                    ->when(count($selectedIds), function ($overheadQuery) use($selectedIds) {
                        $overheadQuery->whereNotIn('id', $selectedIds);
                    })
                    -> where('status', ConstantHelper::ACTIVE)
                    ->count();
        if($results == 0) {
            return response()->json(['data' => ['html' => '', 'levelCount' => 0, 'rowCount' => 0], 'status' => 422, 'message' => 'No overhead available added already all.']);
        }
        $levelCount = intval($request->levelCount) ? intval($request->levelCount) + 1 : 1;
        $rowCount = 1;
        $html = view('billOfMaterial.partials.overhead.add-level', ['levelCount' => $levelCount, 'rowCount' => $rowCount])->render();
        return response()->json(['data' => ['html' => $html, 'levelCount' => $levelCount, 'rowCount' => $rowCount], 'status' => 200, 'message' => 'Overhead level added successfully.']);
    }

    # Add Overhead Row header level
    public function addOverheadRow(Request $request)
    {
        $selectedIds = json_decode($request->ids,true) ?? [];
        $results = Overhead::withDefaultGroupCompanyOrg()
                    ->when(count($selectedIds), function ($overheadQuery) use($selectedIds) {
                        $overheadQuery->whereNotIn('id', $selectedIds);
                    })
                    -> where('status', ConstantHelper::ACTIVE)
                    ->count();
        if($results == 0) {
            return response()->json(['data' => ['html' => '', 'levelCount' => 0, 'rowCount' => 0], 'status' => 422, 'message' => 'No overhead available added already all.']);
        }
        $levelCount = intval($request->levelCount) ?? 1;
        $rowCount = intval($request->rowCount) ? intval($request->rowCount) + 1 : 1;
        $html = view('billOfMaterial.partials.overhead.add-row', ['levelCount' => $levelCount, 'rowCount' => $rowCount])->render();
        return response()->json(['data' => ['html' => $html, 'levelCount' => $levelCount, 'rowCount' => $rowCount], 'status' => 200, 'message' => 'Overhead level row added successfully.']);
    }

    # Add Overhead Row item level
    public function addOverheadItemRow(Request $request)
    {
        $selectedIds = json_decode($request->ids,true) ?? [];
        $results = Overhead::withDefaultGroupCompanyOrg()
                    ->when(count($selectedIds), function ($overheadQuery) use($selectedIds) {
                        $overheadQuery->whereNotIn('id', $selectedIds);
                    })
                    -> where('status', ConstantHelper::ACTIVE)
                    ->count();
        if($results == 0) {
            return response()->json(['data' => ['html' => '', 'levelCount' => 0, 'rowCount' => 0], 'status' => 422, 'message' => 'No overhead available added already all.']);
        }
        $rowCount = intval($request->rowCount) ?? 1;
        $indexCount = intval($request->indexCount) ? intval($request->indexCount) + 1 : 1;
        $html = view('billOfMaterial.partials.overheadItemLevel.add-row', ['rowCount' => $rowCount, 'indexCount' => $indexCount])->render();
        return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount, 'indexCount' => $indexCount], 'status' => 200, 'message' => 'Overhead level row added successfully.']);
    }
    
    public function checkBomExist(Request $request)
    {
        $itemId = $request->item_id ?? null;
        $item = Item::find($itemId);
        if($item) {
            $bomExists = ItemHelper::checkItemBomExists($item->id, []);
            if (!$bomExists['bom_id']) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => $bomExists['message']]);
            } else {
                return response()->json(['data' => ['html' => '', 'bom_id' => $bomExists['bom_id']], 'status' => 200, 'message' => $bomExists['message']]);
            }
        } else {
            return response()->json(['data' => ['html' => ''], 'status' => 404, 'message' => "Item not found."]);
        }
    }
}
