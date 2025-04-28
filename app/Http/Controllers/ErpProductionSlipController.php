<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ServiceParametersHelper;
use App\Models\ErpProductionSlip;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemAttribute;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpPslipItemLocation;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\Organization;
use App\Models\PwoSoMapping;
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
        //Get the menu 
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
        $data = [
            'user' => $user,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'series' => array(),
            'typeName' => $typeName,
            'stores' => $stores,
            'startingBundleNo' => $startingBundleNo,
            'editableBundle' => $editableBundle,
            'redirect_url' => $redirectUrl
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
            $data = [
                'user' => $user,
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

    public function store(Request $request)
    {
        try {
            //Reindex
            $request -> item_qty =  array_values($request -> item_qty);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id);

            DB::beginTransaction();
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
            if ($request -> pslip_id) { //Update
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
                //Get Header Discount
                $totalHeaderDiscount = 0;
                $totalHeaderDiscountArray = [];
                //Initialize item discount to 0
                $itemTotalDiscount = 0;
                $itemTotalValue = 0;
                $totalTax = 0;
                $totalItemValueAfterDiscount = 0;
                $productionSlip -> save();
                //Seperate array to store each item calculation
                $itemsData = array();
                if ($request -> item_id && count($request -> item_id) > 0) {
                    //Items
                    $totalValueAfterDiscount = 0;
                    foreach ($request -> item_id as $itemKey => $itemId) {
                        $item = Item::find($itemId);
                        if (isset($item))
                        {
                            $itemValue = ((float) ($request->item_qty[$itemKey] ?? 0)) * ((float) ($request->item_rate[$itemKey] ?? 0));
                            $itemDiscount = 0;
                            //Item Level Discount
                            $itemTotalValue += $itemValue;
                            $itemTotalDiscount += $itemDiscount;
                            $itemValueAfterDiscount = $itemValue - $itemDiscount;
                            $totalValueAfterDiscount += $itemValueAfterDiscount;
                            $totalItemValueAfterDiscount += $itemValueAfterDiscount;
                            //Check if discount exceeds item value
                            if ($totalItemValueAfterDiscount < 0) {
                                return response() -> json([
                                    'message' => '',
                                    'errors' => array(
                                        'item_name.' . $itemKey => "Discount more than value"
                                    )
                                ], 422);
                            }
                            $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $request -> uom_id[$itemKey] ?? 0, isset($request -> item_qty[$itemKey]) ? $request -> item_qty[$itemKey] : 0);
                            $uom = Unit::find($request -> uom_id[$itemKey] ?? null);
                            array_push($itemsData, [
                                'pslip_id' => $productionSlip -> id,
                                'item_id' => $item -> id,
                                'so_item_id' => isset($request -> so_item_id[$itemKey]) ? $request -> so_item_id[$itemKey] : null,
                                'item_code' => $item -> item_code,
                                'item_name' => $item -> item_name,
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null, //Need to change
                                'uom_code' => isset($uom) ? $uom -> name : null,
                                'store_id' => isset($request -> item_store_to[$itemKey]) ? $request -> item_store_to[$itemKey] : null,
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
                        //Discount
                        $headerDiscount = 0;
                        $valueAfterHeaderDiscount = 0 - $headerDiscount;
                        //Expense
                        $itemExpenseAmount = 0;
                        $itemHeaderExpenseAmount = 0;
                        //Tax
                        $itemTax = 0;
                        $totalTax += $itemTax;
                        //Update or create
                        $itemRowData = [
                            'pslip_id' => $productionSlip -> id,
                            'item_id' => $itemDataValue['item_id'],
                            'so_item_id' => $itemDataValue['so_item_id'],
                            'item_code' => $itemDataValue['item_code'],
                            'item_name' => $itemDataValue['item_name'],
                            'hsn_id' => $itemDataValue['hsn_id'],
                            'hsn_code' => $itemDataValue['hsn_code'],
                            'uom_id' => $itemDataValue['uom_id'], //Need to change
                            'uom_code' => $itemDataValue['uom_code'],
                            'store_id' => $itemDataValue['store_id'],
                            'qty' => $itemDataValue['qty'],
                            'rate' => $itemDataValue['rate'],
                            'customer_id' => $itemDataValue['customer_id'],
                            'inventory_uom_id' => $itemDataValue['inventory_uom_id'],
                            'inventory_uom_code' => $itemDataValue['inventory_uom_code'],
                            'inventory_uom_qty' => $itemDataValue['inventory_uom_qty'],
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        if (isset($request -> ps_item_id[$itemDataKey])) {
                            $oldMPsItem = ErpPslipItem::find($request -> pslip_item_id[$itemDataKey]);
                            $psItem = ErpPslipItem::updateOrCreate(['id' => $request -> pslip_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $psItem = ErpPslipItem::create($itemRowData);
                        }
                        //Order Pulling condition 
                        if (isset($request -> pwo_item_id[$itemDataKey])) {
                            //Back update in mapping table
                            $pwoSoMapping = PwoSoMapping::where('id', $request -> pwo_item_id[$itemDataKey]) -> first();
                            if (isset($pwoSoMapping)) {
                                $pwoSoMapping -> pslip_qty = ($pwoSoMapping -> pslip_qty - (isset($oldMPsItem) ? $oldMPsItem -> qty : 0)) + $itemDataValue['qty'];
                                $pwoSoMapping -> save();
                            }
                            //Back update in so item
                            $soItem = ErpSoItem::find($pwoSoMapping ?-> so_item_id);
                            if (isset($soItem)) {
                                $soItem -> pslip_qty = ($soItem -> pslip_qty - (isset($oldMPsItem) ? $oldMPsItem -> qty : 0)) + $itemDataValue['qty'];
                                $soItem -> save();
                            }
                            
                        }
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
                        //Locations Data
                        $toLocation = ErpStore::find($request -> item_store_to[$itemDataKey]);
                        ErpPslipItemLocation::where('pslip_item_id', $psItem -> id) -> delete();
                        if (isset($request -> item_locations_to[$itemDataKey])) {
                            $toLocationsArray = json_decode($request -> item_locations_to[$itemDataKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($toLocationsArray)) {
                                foreach ($toLocationsArray as $toLoc) {
                                    ErpPslipItemLocation::create([
                                        'pslip_id' => $productionSlip -> id,
                                        'pslip_item_id' => $psItem -> id,
                                        'item_id' => $psItem -> item_id,
                                        'item_code' => $psItem -> item_code,
                                        'store_id' => $toLoc['store_id'],
                                        'store_code' => $toLoc['store_code'],
                                        'rack_id' => $toLoc['rack_id'],
                                        'rack_code' => $toLoc['rack_code'],
                                        'shelf_id' => $toLoc['shelf_id'],
                                        'shelf_code' => $toLoc['shelf_code'],
                                        'bin_id' => $toLoc['bin_id'],
                                        'bin_code' => $toLoc['bin_code'],
                                        'quantity' => $toLoc['qty'],
                                        'inventory_uom_qty' => ItemHelper::convertToBaseUom($psItem -> item_id, $psItem -> uom_id, (float)$toLoc['qty'])
                                    ]);
                                }  
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

                //Header TED (Expense)
                $totalValueAfterTax = $totalItemValueAfterDiscount + $totalTax;
                $totalExpenseAmount = 0;
                $productionSlip -> total_discount_value = $totalHeaderDiscount + $itemTotalDiscount;
                $productionSlip -> total_item_value = $itemTotalValue;
                $productionSlip -> total_tax_value = $totalTax;
                $productionSlip -> total_expense_value = $totalExpenseAmount;
                $productionSlip -> total_amount = ($itemTotalValue - ($totalHeaderDiscount + $itemTotalDiscount)) + $totalTax + $totalExpenseAmount;

               
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
                // self::maintainStockLedger($productionSlip);
                DB::commit();
                $module = "Packing Slip";
                return response() -> json([
                    'message' => $module .  " created successfully",
                    'redirect_url' => route('production.slip.index')
                ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
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
            if ($request -> doc_type === ConstantHelper::PWO_SERVICE_ALIAS) {
                $referedHeaderId = ErpProductionSlip::whereIn('id', $selectedIds) -> first() ?-> header ?-> id;
                $order = PwoSoMapping::withWhereHas('header', function ($subQuery) use($request, $applicableBookIds, $referedHeaderId) {
                    $subQuery -> when($referedHeaderId, function ($refQuery) use($referedHeaderId) {
                        $refQuery -> where('id', $referedHeaderId);
                    })-> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) -> whereIn('book_id', $applicableBookIds) 
                    -> when($request -> book_id, function ($bookQuery) use($request) {
                        $bookQuery -> where('book_id', $request -> book_id);
                    }) -> when($request -> document_id, function ($docQuery) use($request) {
                        $docQuery -> where('id', $request -> document_id);
                    });
                }) -> with('attributes') -> with('uom') -> with('so') -> when(count($selectedIds) > 0, function ($refQuery) use($selectedIds) {
                    $refQuery -> whereNotIn('id', $selectedIds);
                }) -> whereColumn('mo_product_qty', ">", 'pslip_qty');
            }
            else {
                $order = null;
            }
            if ($request -> item_id && isset($order)) {
                $order = $order -> where('item_id', $request -> item_id);
            }
            $order = isset($order) ? $order -> get() : new Collection();
            foreach ($order as $currentOrder) {
                $currentOrder -> avl_stock = $currentOrder -> getAvlStock($request -> store_id_from);
                $currentOrder -> item_name = $currentOrder -> item ?-> item_name;
            }
            $order = $order -> values();
            return response() -> json([
                'data' => $order
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
            $headers = ErpProductionWorkOrder::with(['mapping' => function ($mappingQuery) use($request) {
                $mappingQuery -> whereIn('id', $request -> items_id) -> with(['item' => function ($itemQuery) {
                    $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
                }]);
            }]) -> get();

            foreach ($headers as &$header) {
                foreach ($header -> mapping as &$item) {
                    $item -> item_attributes_array = $item -> item_attributes_array();
                }
            }

            return response() -> json([
                'message' => 'Data found',
                'data' => $headers
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'message' => 'Some internal error occurred',
                'error' => $ex -> getMessage()
            ]);
        }
    }
    // private static function maintainStockLedger(ErpProductionSlip $productionSlip)
    // {
    //     $detailIds = $productionSlip->items->pluck('id')->toArray();
    //     InventoryHelper::settlementOfInventoryAndStock($productionSlip->id, $detailIds, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS, $productionSlip->document_status, 'receipt');
    // }
}
