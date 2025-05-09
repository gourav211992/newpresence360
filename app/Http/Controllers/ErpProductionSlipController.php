<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ServiceParametersHelper;
use App\Http\Requests\PslipRequest;
use App\Models\ErpProductionSlip;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemAttribute;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpPslipItemLocation;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\MfgOrder;
use App\Models\MoBomMapping;
use App\Models\MoItem;
use App\Models\MoProduct;
use App\Models\Organization;
use App\Models\PslipBomConsumption;
use App\Models\PslipConsumptionLocation;
use App\Models\PwoBomMapping;
use App\Models\PwoSoMapping;
use App\Models\PwoStationConsumption;
use App\Models\Shift;
use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use App\Models\Unit;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\DataTables;

class ErpProductionSlipController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $redirectUrl = route('production.slip.index');
        $createRoute = route('production.slip.create');
        $typeName = "Packing Slip";
        if ($request -> ajax()) {
            try {
            $docs = ErpProductionSlip::withDefaultGroupCompanyOrg() ->  bookViewAccess($pathUrl) ->  withDraftListingLogic() -> orderByDesc('id') -> get();
            return DataTables::of($docs) ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = $row -> display_status;
                $editRoute = route('production.slip.edit', ['id' => $row -> id]); 
                return "
                    <div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClass badgeborder-radius'>$displayStatus</span>
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
            ->addColumn('book_name', function ($row) {
                return $row->book_code ? $row->book_code : 'N/A';
            })
            ->addColumn('store_code', function ($row) {
                return $row->store?->store_code ? $row->store?->store_code  : 'N/A';
            })
            ->addColumn('curr_name', function ($row) {
                return $row->currency ? ($row->currency?->short_name ?? $row->currency?->name) : 'N/A';
            })
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
            })
            ->editColumn('revision_number', function ($row) {
                return strval($row->revision_number);
            })
            ->addColumn('items_count', function ($row) {
                return $row->items->count();
            })
            ->rawColumns(['document_status'])
            ->make(true);
            }
            catch (Exception $ex) {
                return response() -> json([
                    'message' => $ex -> getMessage()
                ]);
            }
        }
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        return view('productionSlip.index', ['typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'create_button' => count($servicesBooks['services'])]);
    }

    public function create(Request $request)
    {
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $redirectUrl = route('production.slip.index');
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $typeName = "Packing Slip";
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $currentBundleNo = ErpPslipItemDetail::orderByDesc('id')->first() ?-> bundle_no ?? 0;
        $startingBundleNo = $currentBundleNo + 1;
        $editableBundle = true;
        if ($currentBundleNo > 0) {
            $editableBundle = false;
        }
        $authUser = Helper::getAuthenticatedUser();
        $organization = Organization::find($authUser ?->organization_id);
        $organizationId = $organization ?-> id ?? null;
        $shifts = Shift::where('organization_id',$organizationId)->where("status", ConstantHelper::ACTIVE)->get();
        $data = [
            'user' => $user,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => array(),
            'typeName' => $typeName,
            'stores' => $stores,
            'startingBundleNo' => $startingBundleNo,
            'editableBundle' => $editableBundle,
            'redirect_url' => $redirectUrl,
            'shifts' => $shifts
        ];
        return view('productionSlip.create_edit', $data);
    }

    public function edit(Request $request, String $id)
    {
        try {
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('production.slip.index');
            $user = Helper::getAuthenticatedUser();
            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpProductionSlip::with(['media_files']) -> with('items', function ($query) {
                    $query -> with(['to_item_locations', 'bundles', 'item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'to_item_locations']);
                    }]);
                }) -> where('source_id', $request -> id)->first();
                $ogDoc = ErpProductionSlip::find($id);
            } else {
                $doc = ErpProductionSlip::with(['media_files']) -> with('items', function ($query) {
                    $query -> with(['to_item_locations', 'bundles', 'item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom']);
                    }]);
                }) -> find($id);
                $ogDoc = $doc;
            }
            $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl,$doc -> book ?-> service ?-> alias);
            }            
            $revision_number = $doc->revision_number;
            $totalValue = 0;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, $doc->approval_level, $doc -> created_by ?? 0, $userType['type'], $revision_number);
            $books = Helper::getBookSeriesNew(ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) -> get();
            $revNo = $doc->revision_number;
            if($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $doc->revision_number;
            }
            $docValue = $doc->total_amount ?? 0;
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $ogDoc->id, $revNo, $docValue, $doc -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
            $typeName = "Packing Slip";
            $currentBundleNo = ErpPslipItemDetail::orderByDesc('id')->first() ?-> bundle_no ?? 0;
            $startingBundleNo = $currentBundleNo + 1;
            $editableBundle = true;
            if ($currentBundleNo > 0) {
                $editableBundle = false;
            }
            foreach ($doc -> items as $docItem) {
                if (isset($docItem -> so_item_id)) {
                    $soItem = ErpSoItem::find($docItem -> so_item_id);
                    if ($soItem) {
                        $soItem -> pslip_qty = $docItem -> qty;
                        $soItem -> save();
                    }
                }
            }
            $authUser = Helper::getAuthenticatedUser();
            $organization = Organization::find($authUser ?->organization_id);
            $organizationId = $organization ?-> id ?? null;
            $shifts = Shift::where('organization_id',$organizationId)->where("status", ConstantHelper::ACTIVE)->get();
            $data = [
                'user' => $user,
                'shifts' => $shifts,
                'series' => $books,
                'slip' => $doc,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'stores' => $stores,
                'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($doc -> media_files)) : 10,
                'services' => $servicesBooks['services'],
                'startingBundleNo' => $startingBundleNo,
                'editableBundle' => $editableBundle,
                'redirect_url' => $redirect_url
            ];
            return view('productionSlip.create_edit', $data);  
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }

    public function store(PslipRequest $request)
    {
        try {
            //Reindex
            $request -> item_qty =  array_values($request -> item_qty ?? []);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id ?? []);

            DB::beginTransaction();

            if ($request -> item_id && count($request -> item_id) < 1) {
                return response()->json([
                    'message' => 'Please select Items',
                    'error' => "",
                ], 422);
            }
            $user = Helper::getAuthenticatedUser();
            //Auth credentials
            $organization = Organization::find($user -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $itemAttributeIds = [];
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($organization -> currency -> id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422); 
            }

            if (!$request -> production_slip_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpProductionSlip::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $productionSlip = null;
            $store = ErpStore::find($request -> store_id);
            if ($request -> pslip_id) {
                $productionSlip = ErpProductionSlip::find($request -> pslip_id);
                $productionSlip -> document_date = $request -> document_date;
                // $productionSlip -> reference_number = $request -> reference_no;
                //Store and department keys
                $productionSlip -> store_id = $request -> store_id ?? null;
                // $productionSlip -> store_code = $store ?-> store_code ?? null;
                $productionSlip -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup
                if(($productionSlip -> document_status == ConstantHelper::APPROVED || $productionSlip -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    // $revisionData = [
                    //     ['model_type' => 'header', 'model_name' => 'ErpProductionSlip', 'relation_column' => ''],
                    //     ['model_type' => 'detail', 'model_name' => 'ErpMiItem', 'relation_column' => 'material_issue_id'],
                    //     ['model_type' => 'sub_detail', 'model_name' => 'ErpMiItemAttribute', 'relation_column' => 'mi_item_id'],
                    //     ['model_type' => 'sub_detail', 'model_name' => 'ErpMiItemLocation', 'relation_column' => 'mi_item_id'],
                    // ];
                    // $a = Helper::documentAmendment($revisionData, $productionSlip->id);

                }
                $keys = ['deletedSiItemIds', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }

                if (count($deletedData['deletedSiItemIds'])) {
                    $psItems = ErpPslipItem::whereIn('id',$deletedData['deletedSiItemIds'])->get();
                    # all ted remove item level
                    foreach($psItems as $psItem) {
                        if (isset($psItem -> so_item_id)) {
                            //Back update in SO ITEM
                            $soItem = ErpSoItem::find($psItem -> so_item_id);
                            if (isset($soItem)) {
                                $soItem -> pslip_qty = $soItem -> pslip_qty - $psItem -> qty;
                                $soItem -> save();
                            }
                            //Update in mapping table 
                            $pwoSoMappingItem = PwoSoMapping::where('so_item_id', $psItem -> so_item_id) -> first();
                            if (isset($pwoSoMappingItem)) {
                                $pwoSoMappingItem -> pslip_qty = $pwoSoMappingItem -> pslip_qty - $psItem -> qty;
                                $pwoSoMappingItem -> save();
                            }
                        }
                        # all attr remove
                        $psItem->attributes()->delete();
                        $psItem->delete();
                    }
                }
            } else { //Create
                $productionSlip = ErpProductionSlip::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'mo_id' => $request->mo_id ? $request->mo_id[0] : $request->mo_id,
                    'is_last_station' => $request->is_last_station ?? 0,
                    'station_id' => $request->mo_station_id,
                    'book_id' => $request -> book_id,
                    'book_code' => $request -> book_code,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request -> document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    // 'reference_number' => $request -> reference_no,
                    'store_id' => $request -> store_id ?? null,
                    'sub_store_id' => $request -> sub_store_id ?? null,
                    'shift_id' => $request -> shift_id ?? null,
                    // 'store_code' => $store ?-> store_code ?? null,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => $request -> final_remarks,
                    'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                ]);
            }

                $productionSlip -> save();
                //Seperate array to store each item calculation
                $itemsData = array();
                if ($request -> item_id && count($request -> item_id) > 0) {
                    //Items
                    foreach ($request -> item_id as $itemKey => $itemId) {
                        $item = Item::find($itemId);
                        if (isset($item))
                        {
                            $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0);
                            $uom = Unit::find($request -> uom_id[$itemKey] ?? null);
                            array_push($itemsData, [
                                'pslip_id' => $productionSlip -> id,
                                'station_id' => isset($request -> station_id[$itemKey]) ? $request -> station_id[$itemKey] : null,
                                'item_id' => $item -> id,
                                'so_id' => isset($request -> so_id[$itemKey]) ? $request -> so_id[$itemKey] : null,
                                'so_item_id' => isset($request -> so_item_id[$itemKey]) ? $request -> so_item_id[$itemKey] : null,
                                'mo_id' => isset($request -> mo_id[$itemKey]) ? $request -> mo_id[$itemKey] : null,
                                'mo_product_id' => isset($request -> mo_product_id[$itemKey]) ? $request -> mo_product_id[$itemKey] : null,
                                'item_code' => $item -> item_code,
                                'item_name' => $item -> item_name,
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null, //Need to change
                                'uom_code' => isset($uom) ? $uom -> name : null,
                                'store_id' => $productionSlip->store_id,
                                'sub_store_id' => $productionSlip->sub_store_id,
                                'qty' => isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0,
                                'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
                                'customer_id' => isset($request -> customer_id[$itemKey]) ? $request -> customer_id[$itemKey] : 0,
                                'inventory_uom_id' => $item -> uom ?-> id,
                                'inventory_uom_code' => $item -> uom ?-> name,
                                'inventory_uom_qty' => $inventoryUomQty,
                                'remarks' => isset($request -> item_remarks[$itemKey]) ? $request -> item_remarks[$itemKey] : null,
                            ]);
                            
                        }
                    }
                    foreach ($itemsData as $itemDataKey => $itemDataValue) {
                        $itemRowData = [
                            'pslip_id' => $productionSlip -> id,
                            'store_id' => $productionSlip ?->store_id,
                            'sub_store_id' => $productionSlip ?-> sub_store_id,
                            'so_id' => $itemDataValue['so_id'],
                            'so_item_id' => $itemDataValue['so_item_id'],
                            'mo_product_id' => $itemDataValue['mo_product_id'],
                            'station_id' => $itemDataValue['station_id'],
                            'item_id' => $itemDataValue['item_id'],
                            'item_code' => $itemDataValue['item_code'],
                            'item_name' => $itemDataValue['item_name'],
                            'uom_id' => $itemDataValue['uom_id'],
                            'uom_code' => $itemDataValue['uom_code'],
                            'qty' => $itemDataValue['qty'],
                            'rate' => $itemDataValue['rate'],
                            'customer_id' => $itemDataValue['customer_id'],
                            'inventory_uom_id' => $itemDataValue['inventory_uom_id'],
                            // 'inventory_uom_code' => $itemDataValue['inventory_uom_code'],
                            'inventory_uom_qty' => $itemDataValue['inventory_uom_qty'],
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        if (isset($request -> ps_item_id[$itemDataKey])) {
                            $psItem = ErpPslipItem::updateOrCreate(['id' => $request -> pslip_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $psItem = ErpPslipItem::create($itemRowData);
                        }

                        // $stationId = $psItem->station_id ?? null;
                        // $bomDetails = PwoBomMapping::where('pwo_mapping_id', $psItem?->mo_product?->pwo_mapping_id)
                        // ->where(function($query) use($stationId) {
                        //     if($stationId) {
                        //         $query->where('station_id', $stationId);
                        //     }
                        // })        
                        // ->get();
                        $bomDetails = MoBomMapping::where('mo_product_id', $psItem->mo_product_id)->get();

                        foreach ($bomDetails as $bomDetail) {   
                            $pslipBomMapping = new PslipBomConsumption;
                            $pslipBomMapping->pslip_id = $productionSlip?->id;
                            $pslipBomMapping->pslip_item_id = $psItem?->id;
                            $pslipBomMapping->so_id = $psItem->so_id ?? null;
                            $pslipBomMapping->so_item_id = $psItem->so_item_id ?? null;
                            $pslipBomMapping->bom_id = $bomDetail->bom_id;
                            $pslipBomMapping->bom_detail_id = $bomDetail->bom_detail_id;
                            $pslipBomMapping->item_id = $bomDetail->item_id;
                            $pslipBomMapping->item_code = $bomDetail->item_code;
                            $pslipBomMapping->attributes = $bomDetail->attributes;
                            $pslipBomMapping->uom_id = $bomDetail->uom_id;
                            $pslipBomMapping->qty = $bomDetail->bom_qty;
                            $pslipBomMapping->consumption_qty = floatval($bomDetail->bom_qty)*floatval($itemDataValue['qty']);
                            $pslipBomMapping->inventory_uom_qty = floatval($bomDetail->bom_qty)*floatval($itemDataValue['qty']);
                            $pslipBomMapping->station_id = $bomDetail->station_id;
                            $pslipBomMapping->section_id = $bomDetail->section_id;
                            $pslipBomMapping->sub_section_id = $bomDetail->sub_section_id;
                            $pslipBomMapping->save();

                            // Back Update Mo Item Consumption
                            $moProductAttributes = $bomDetail->attributes ?? [];
                            $moItem = MoItem::where('mo_id',$itemDataValue['mo_id'])
                                            ->where('so_id',$psItem->so_id)
                                            ->where('item_id', $bomDetail->item_id)
                                            ->when(count($moProductAttributes), function ($query) use ($moProductAttributes) {
                                                $query->whereHas('attributes', function ($piAttributeQuery) use ($moProductAttributes) {
                                                    $piAttributeQuery->where(function ($subQuery) use ($moProductAttributes) {
                                                        foreach ($moProductAttributes as $poAttribute) {
                                                            $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                                $q->where('item_attribute_id', $poAttribute['item_attribute_id'] ?? $poAttribute['attribute_id'])
                                                                    ->where('attribute_value', $poAttribute['attribute_value']);
                                                            });
                                                        }
                                                    });
                                                }, '=', count($moProductAttributes));
                                            })
                                            ->first();
                            if($moItem) {
                                $moItem->consumed_qty += $pslipBomMapping->consumption_qty;
                                $moItem->save();
                            }
                        }  
                        // //Order Pulling condition 
                        // if (isset($request -> pwo_item_id[$itemDataKey])) {
                        //     //Back update in mapping table
                        //     $pwoSoMapping = PwoSoMapping::where('id', $request -> pwo_item_id[$itemDataKey]) -> first();
                        //     if (isset($pwoSoMapping)) {
                        //         $pwoSoMapping -> pslip_qty = ($pwoSoMapping -> pslip_qty - (isset($oldMPsItem) ? $oldMPsItem -> qty : 0)) + $itemDataValue['qty'];
                        //         $pwoSoMapping -> save();
                        //     }
                        //     //Back update in so item
                        //     $soItem = ErpSoItem::find($pwoSoMapping ?-> so_item_id);
                        //     if (isset($soItem)) {
                        //         $soItem -> pslip_qty = ($soItem -> pslip_qty - (isset($oldMPsItem) ? $oldMPsItem -> qty : 0)) + $itemDataValue['qty'];
                        //         $soItem -> save();
                        //     }
                            
                        // }
                        //Item Attributes
                        if (isset($request -> item_attributes[$itemDataKey])) {
                            $attributesArray = json_decode($request -> item_attributes[$itemDataKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($attributesArray)) {
                                foreach ($attributesArray as $attributeKey => $attribute) {
                                    $attributeVal = "";
                                    $attributeValId = null;
                                    foreach ($attribute['values_data'] as $valData) {
                                        if ($valData['selected']) {
                                            $attributeVal = $valData['value'];
                                            $attributeValId = $valData['id'];
                                            break;
                                        }
                                    }
                                    $itemAttribute = ErpPslipItemAttribute::updateOrCreate(
                                        [
                                            'pslip_id' => $productionSlip -> id,
                                            'pslip_item_id' => $psItem -> id,
                                            'item_attribute_id' => $attribute['id'],
                                        ],
                                        [
                                            'item_code' => $psItem -> item_code,
                                            'attribute_name' => $attribute['group_name'],
                                            'attr_name' => $attribute['attribute_group_id'],
                                            'attribute_value' => $attributeVal,
                                            'attr_value' => $attributeValId,
                                        ]
                                    );
                                    array_push($itemAttributeIds, $itemAttribute -> id);
                                }
                            } else {
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid attributes',
                                    'error' => ''
                                ], 422);
                            }
                        }

                        //Bundle data
                        // if ($item -> storage_type == ConstantHelper::BUNDLE) {
                            $bundlesArray = json_decode($request -> item_bundles[$itemDataKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($bundlesArray)) {
                                $itemQtyBundleWise = 0;
                                ErpPslipItemDetail::where('pslip_item_id', $psItem -> id) -> delete();
                                foreach ($bundlesArray as $bundleElement) {
                                    $currentBundleNo = (ErpPslipItemDetail::orderByDesc('id')->first() ?-> bundle_no ?? 0) + 1;

                                    if (isset($bundleElement['id']) && $bundleElement['id']) {
                                        $existingBundle = ErpPslipItemDetail::find($bundleElement['id']);
                                        if (isset($bundleElement['deleted']) && $bundleElement['deleted']) {
                                            $existingBundle ?-> delete();
                                        } else {
                                            if (isset($existingBundle)) {
                                                $existingBundle -> qty = $bundleElement['qty'];
                                                $existingBundle -> save();
                                            }
                                            $itemQtyBundleWise += (double)$bundleElement['qty'];
                                        }
                                    } else {
                                        $itemQtyBundleWise += (double)$bundleElement['qty'];
                                        ErpPslipItemDetail::create([
                                            'pslip_id' => $productionSlip -> id,
                                            'pslip_item_id' => $psItem -> id,
                                            'bundle_no' => $currentBundleNo,
                                            'bundle_type' => 'bundle',
                                            'qty' => $bundleElement['qty']
                                        ]);
                                    }
                                }
                                if ($itemQtyBundleWise != $psItem -> qty) {
                                    DB::rollBack();
                                    return response() -> json([
                                        'message' => 'Item No. ' . ($itemDataKey + 1) . ' has exceeded bundle qty',
                                        'error' => ''
                                    ], 422);
                                }
                            } else {
                                DB::rollBack();
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid bundle data',
                                    'error' => ''
                                ], 422);
                            }
                        // }  
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);
                }
                ErpPslipItemAttribute::where([
                    'pslip_id' => $productionSlip -> id,
                    'pslip_item_id' => $psItem -> id,
                ]) -> whereNotIn('id', $itemAttributeIds) -> delete();
                //Header TED (Discount)
               
                //Approval check
                if ($request -> pslip_id) { //Update condition
                    $bookId = $productionSlip->book_id; 
                    $docId = $productionSlip->id;
                    $amendRemarks = $request->amend_remarks ?? null;
                    $remarks = $productionSlip->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachment');
                    $currentLevel = $productionSlip->approval_level;
                    $modelName = get_class($productionSlip);
                    $actionType = $request -> action_type ?? "";
                    if(($productionSlip -> document_status == ConstantHelper::APPROVED || $productionSlip -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $productionSlip->revision_number + 1;
                        $actionType = 'amendment';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                        $productionSlip->revision_number = $revisionNumber;
                        $productionSlip->approval_level = 1;
                        $productionSlip->revision_date = now();
                        $amendAfterStatus = $productionSlip->document_status;
                        $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                        if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                            $totalValue = $productionSlip->grand_total_amount ?? 0;
                            $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        } else {
                            $actionType = 'approve';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }
                        if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        }
                        $productionSlip->document_status = $amendAfterStatus;
                        $productionSlip->save();

                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $productionSlip->revision_number ?? 0;
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                            $totalValue = $productionSlip->grand_total_amount ?? 0;
                            $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                            $productionSlip->document_status = $document_status;
                        } else {
                            $productionSlip->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { //Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $productionSlip->book_id;
                        $docId = $productionSlip->id;
                        $remarks = $productionSlip->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $productionSlip->approval_level;
                        $revisionNumber = $productionSlip->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($productionSlip);
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    if ($request->document_status == 'submitted') {
                        $totalValue = $productionSlip->total_amount ?? 0;
                        $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        $productionSlip->document_status = $document_status;
                    } else {
                        $productionSlip->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                    $productionSlip -> save();
                }
                $productionSlip -> save();
                //Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $productionSlip->uploadDocuments($singleFile, 'production_slips', false);
                    }
                }
                
                # Issue Raws Materials
                $maintainStockLedger = self::maintainStockLedger($productionSlip);
                if(!$maintainStockLedger) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => "Error while updating stock ledger for issue.",
                    ]);
                }

                # Update rate in  Pslip Item & insert in Pslip Item Location
                $moProdItems = ErpPslipItem::where('pslip_id', $productionSlip->id)->get();
                $detailIds = [];
                foreach($moProdItems as $moProdItem) {
                    $moItemValue = PslipBomConsumption::where('pslip_id', $productionSlip->id)
                                    ->where('pslip_item_id', $moProdItem->id)
                                    ->sum(DB::raw('consumption_qty * rate'));
                    $prodItemRate = $moItemValue / $moProdItem->qty;
                    $detailIds[] = $moProdItem->id;
                    $moProdItem->rate = $prodItemRate;
                    $moProdItem->save();
                    $moProdItemLocation = new ErpPslipItemLocation;
                    $moProdItemLocation->pslip_id = $productionSlip->id;
                    $moProdItemLocation->pslip_item_id = $moProdItem->id;
                    $moProdItemLocation->item_id = $moProdItem->item_id;
                    $moProdItemLocation->store_id = $moProdItem?->mo?->store_id;
                    $moProdItemLocation->sub_store_id = $moProdItem?->mo?->sub_store_id;
                    $moProdItemLocation->station_id = $moProdItem?->mo?->station_id;
                    $moProdItemLocation->quantity = $moProdItem->qty;
                    $moProdItemLocation->inventory_uom_qty = $moProdItem->qty;
                    $moProdItemLocation->save();
                }
                
                $moProdItemReceipt = InventoryHelper::settlementOfInventoryAndStock($productionSlip->id, $detailIds, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS, $productionSlip->document_status, 'receipt');
                if($moProdItemReceipt['status'] != 'success') {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => "Error while updating stock ledger for receipt.",
                    ]);
                }               

                // Back Update Mo Product Qty
                foreach($productionSlip->items as $pslipItem) {
                    $moProduct = $pslipItem?->mo_product ?? null;
                    if($moProduct) {
                        $moProduct->pslip_qty += floatval($pslipItem->qty);
                        $moProduct->save();
                        $pwoStation = PwoStationConsumption::where('pwo_mapping_id',$moProduct?->pwoMapping?->id)
                                            ->where('mo_id',$moProduct->mo_id)
                                            ->where('station_id',$moProduct?->mo?->station_id)
                                            ->first();
                        if($pwoStation) {
                            $pwoStation->pslip_qty += floatval($pslipItem->qty);
                            $pwoStation->save();
                        }
                        if($moProduct?->mo?->is_last_station && in_array($productionSlip->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)) {
                            $moProduct->pwoMapping->pslip_qty += floatval($pslipItem->qty);
                            $moProduct->pwoMapping->save();
                            if($moProduct?->soItem) {
                                #to be used after reservation is handled
                                $stockLedgerId = StockLedger::where('book_type', ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS)
                                ->where('document_header_id',$pslipItem->pslip_id)
                                ->where('document_detail_id', $pslipItem->id)
                                ->where('organization_id',$productionSlip->organization_id)
                                ->where('transaction_type','receipt')
                                ->value('id');
                                # Stock Reservation
                                #to be used after reservation is handled
                                // if($stockLedgerId) {
                                //     $soBalQty = $pslipItem->so_item->order_qty - $pslipItem->so_item->pslip_qty;
                                //     $reserveQty = min($soBalQty,$pslipItem->qty);  
                                //     $stockReservation = new StockLedgerReservation;
                                //     $stockReservation->stock_ledger_id = $stockLedgerId;
                                //     $stockReservation->pslip_id = $pslipItem->pslip_id;
                                //     $stockReservation->pslip_item_id = $pslipItem->id;
                                //     $stockReservation->so_id = $pslipItem?->so_id;
                                //     $stockReservation->so_item_id = $pslipItem?->so_item_id;
                                //     $stockReservation->quantity = $reserveQty;
                                //     $stockReservation->save();
                                //     $stockReservation->stockLedger->reserved_qty += $stockReservation->quantity;
                                //     $stockReservation->stockLedger->save();
                                // }
                                $moProduct->soItem->pslip_qty += floatval($pslipItem->qty);
                                $moProduct->soItem->save();
                            }
                        }
                    }
                }

                DB::commit();
                $module = "Production Slip";
                return response() -> json([
                    'message' => $module .  " created successfully",
                    'redirect_url' => route('production.slip.index')
                ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'line' => $ex -> getLine(),
                'error' => $ex->getMessage() . ' at ' . $ex -> getLine() . ' in ' . $ex -> getFile(),
            ], 500);
        }
    }

    public function revoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $doc = ErpProductionSlip::find($request -> id);
            if (isset($doc)) {
                $revoke = Helper::approveDocument($doc -> book_id, $doc -> id, $doc -> revision_number, '', [], 0, ConstantHelper::REVOKE, $doc -> total_amount, get_class($doc));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $doc -> document_status = $revoke['approvalStatus'];
                    $doc -> save();
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
    
    //Function to get all items of pwo
    public function getPwoItemsForPulling(Request $request)
    {
        try {
            $selectedIds = $request -> selected_ids ?? [];
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
            if ($request->doc_type === ConstantHelper::MO_SERVICE_ALIAS) {
                $order = MoProduct::withWhereHas('mo', function ($subQuery) use($request, $applicableBookIds) {
                    $subQuery->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                    ->whereIn('book_id', $applicableBookIds) 
                    ->when($request->store_id, function ($storeQuery) use($request) {
                        $storeQuery->where('store_id', $request->store_id);
                    })
                    ->when($request->sub_store_id, function ($subStoreQuery) use($request) {
                        $subStoreQuery->where('sub_store_id', $request->sub_store_id);
                    })
                    ->when($request->book_id, function ($bookQuery) use($request) {
                        $bookQuery->where('book_id', $request->book_id);
                    })
                    ->when($request->document_id, function ($docQuery) use($request) {
                        $docQuery->where('id', $request->document_id);
                    });
                })
                ->with('attributes')->with('uom')->with('so')
                ->when($request->so_doc_number, function ($refQuery) use($request) {
                    $refQuery->whereHas('so', function ($soQuery) use($request) {
                        $soQuery->where('document_number', 'like', '%' . $request->so_doc_number . '%');
                    });
                })
                ->when($request->mo_doc_number, function ($refQuery) use($request) {
                    $refQuery->whereHas('mo', function ($soQuery) use($request) {
                        $soQuery->where('document_number', 'like', '%' . $request->mo_doc_number . '%');
                    });
                })
                ->when($request->item_id, function ($refQuery) use($request) {
                    $refQuery->where('item_id', $request->item_id);
                })
                ->when($request->customer_id, function ($refQuery) use($request) {
                    $refQuery->where('customer_id', $request->customer_id);
                })
                ->when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery->whereNotIn('id', $selectedIds);
                })
                ->whereColumn('qty', ">", 'pslip_qty');
            }
            else {
                $order = null;
            }
            if ($request->item_id && isset($order)) {
                $order = $order->where('item_id', $request->item_id);
            }
            $order = isset($order) ? $order->get() : new Collection();
            $order = $order -> values();
            $html = view('productionSlip.partials.mo-product-item', ['orders' => $order])->render();
            return response() -> json([
                'data' => ['html' => $html],
                'status' => 200,
                'message' => "Fetched!"
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'message' => 'Some internal error occurred',
                'error' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
            ]);
        }
    }
    //Function to get all items of pwo module
    public function processPulledItems(Request $request)
    {
        try {
            $docIds = json_decode($request->docIds, true) ?? []; 
            $order = MoProduct::whereIn('id', $docIds)
                    ->with('attributes')
                    ->with('uom')
                    ->with('so')
                    ->get();
            $mo = [];
            if($order?->count()) {
                $mo['mo_id'] = $order[0]->mo?->id ?? '';
                $mo['mo_no'] = $order[0]->mo->book_code. " - ". $order[0]->mo->document_number;
                $mo['mo_date'] = $order[0]->mo->getFormattedDate('document_date') ?? '';
                $mo['mo_product_id'] = $order[0]->mo->item_id ?? '';
                $mo['mo_product_name'] = $order[0]->mo->item->item_name ?? '';
                $mo['is_last_station'] = $order[0]->mo->is_last_station ?? false;
                $mo['mo_type'] = $order[0]->mo->is_last_station == true ? 'Final' : 'WIP';
                $mo['mo_station_id'] = $order[0]->mo->station_id ?? '';
                $mo['mo_station_name'] = $order[0]->mo->station?->name ?? '';
            }
            $stationWise = $request->station_wise_consumption ?? 'no';
            $consumptions = MoBomMapping::whereIn('mo_product_id',$docIds)->orderBy('mo_product_id')->get();
            $consHtml = view('productionSlip.partials.process-consumtion', ['consumptions' => $consumptions])->render();
            $html = view('productionSlip.partials.pull-row', ['orders' => $order, 'stationWise' => $stationWise])->render();
            return response() -> json([
                'message' => 'Data found',
                'data' => ['html' => $html, 'mo' => $mo, 'consHtml' => $consHtml],
                'status' => 200
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'message' => 'Some internal error occurred',
                'error' => $ex -> getMessage(),
                'line' => $ex -> getLine(),
            ]);
        }
    }

    public function getSubStore(Request $request)
    {
        $storeId = $request->store_id;
        $results = InventoryHelper::getAccesibleSubLocations($storeId ?? 0,null, [ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR]);
        return response()->json(['data' => $results, 'status' => 200, 'message' => "fetched!"]);
    }

    private static function maintainStockLedger(ErpProductionSlip $pslip)
    {
        $pslipStatus = $pslip->document_status;
        $user = Helper::getAuthenticatedUser();
        $detailIds = $pslip->consumptions->pluck('id')->toArray();
        $issueRecords = InventoryHelper::settlementOfInventoryAndStock($pslip->id, $detailIds, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS, $pslipStatus, 'issue');
        if(!empty($issueRecords['data'])){
            foreach($issueRecords['data'] as $key => $val){
                $pslipConsumption = PslipBomConsumption::where('id',@$val->issuedBy->document_detail_id)->first();
                $qty = ItemHelper::convertToAltUom($val->issuedBy->item_id, $pslipConsumption?->uom_id, $val->issuedBy->issue_qty);
                PslipConsumptionLocation::create([
                    'pslip_id' => $pslip->id,
                    'pslip_consumption_id' => @$val->issuedBy->document_detail_id,
                    'item_id' => $val->issuedBy->item_id,
                    'store_id' => $pslip->store_id,
                    'sub_store_id' => $pslip->sub_store_id,
                    'station_id' => $pslip->station_id,
                    'rack_id' => $val->issuedBy->rack_id,
                    'shelf_id' => $val->issuedBy->shelf_id,
                    'bin_id' => $val->issuedBy->bin_id,
                    'quantity' => $qty,
                    'inventory_uom_qty' => $qty
                ]);
            }

            $stockLedgers = StockLedger::where('book_type',ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS)
                                ->where('document_header_id',$pslip->id)
                                ->where('organization_id',$pslip->organization_id)
                                ->where('transaction_type','issue')
                                ->selectRaw('document_detail_id,sum(org_currency_cost) as cost')
                                ->groupBy('document_detail_id')
                                ->get();

            foreach($stockLedgers as $stockLedger) {
                $psConsumption = PslipBomConsumption::find($stockLedger->document_detail_id);
                $psConsumption->rate = floatval($stockLedger->cost) / floatval($psConsumption->qty);
                $psConsumption->save();
            }
            return true;
        } else {
            return false;
        }
    }

}
