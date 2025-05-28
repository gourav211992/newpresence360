<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\PackingList\Constants;
use App\Helpers\ServiceParametersHelper;
use App\Http\Requests\ErpSaleOrderRequest;
use App\Http\Requests\PackingListRequest;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\Organization;
use App\Models\PackingList;
use App\Models\PackingListDetail;
use App\Models\PackingListHistory;
use App\Models\PackingListItem;
use App\Models\PackingListItemAttribute;
use DB;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PackingListController extends Controller
{
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $createRoute = route('packingList.create');     
        $redirectUrl = route('packingList.index');
        $moduleName = Constants::MODULE_NAME;
        if ($request -> ajax()) {
            $packingLists = PackingList::bookViewAccess($pathUrl) -> withDefaultGroupCompanyOrg() -> withDraftListingLogic() -> withCount([
                'details as total_details',
            ]) -> orderByDesc('id');
            return DataTables::of($packingLists) ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT];    
                $displayStatus = $row -> display_status;   
                $editRoute = route('packingList.edit', ['id' => $row -> id]);
                return "
                <div style='text-align:right;'>
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
                </div>
            ";
            })
            ->addColumn('book_name', function ($row) {
                return $row->book_code ? $row->book_code : 'N/A';
            })
            ->addColumn('store_code', function ($row) {
                return $row->store?->store_name ?? 'N/A';
            })
            ->addColumn('sub_store_code', function ($row) {
                return $row->sub_store?->name ?? 'N/A';
            })
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
            })
            ->editColumn('revision_number', function ($row) {
                return strval($row->revision_number);
            })
            ->addColumn('details_count', function ($row) {
                return $row->total_details;
            })
            ->rawColumns(['document_status'])
            ->make(true);
        }
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        return view('packingList.index', [
            'redirectUrl' => $redirectUrl,
            'createRoute' => $createRoute,
            'createButton' => count($servicesBooks['services']),
            'moduleName' => $moduleName
        ]);
    }

    public function create(Request $request)
    {
        $redirectUrl = route('packingList.index');
        //Get the menu 
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $books = [];
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $moduleName = Constants::MODULE_NAME;
        $data = [
            'series' => $books,
            'user' => $user,
            'stores' => $stores,
            'services' => $servicesBooks['services'],
            'selectedService'  => $firstService ?-> id ?? null,
            'moduleName' => $moduleName,
            'redirectUrl' => $redirectUrl
        ];
        return view('packingList.create_edit', $data);
    }

    public function edit(Request $request, String $id)
    {
        try {
            $redirectUrl = route('packingList.index');
            $servicesBooks = [];
            $user = Helper::getAuthenticatedUser();
            $moduleName = Constants::MODULE_NAME;
            if (isset($request->revisionNumber)) {
                $doc = PackingListHistory::with(['media_files'])->with('details', function ($query) {
                    $query->with('items');
                })->where('source_id', $id)->where('revision_number', $request->revisionNumber)->first();
                $ogDoc = PackingList::find($id);
            } else {
                $doc = PackingList::with(['media_files'])->with('details', function ($query) {
                    $query->with('items');
                })->find($id);
            }
            foreach ($doc -> details as $docDetail) {
                $totalQty = 0;
                $itemsHTML = "";
                $extraItemsCount = 0;
                $soItemsArray = [];
                foreach ($docDetail -> items as $detailItemIndex => $detailItem) {
                    $attributesArray = [];
                    foreach($detailItem -> attributes as $itemAttr) {
                        $attributeName = $itemAttr -> attribute_name;
                        $attributeValue = $itemAttr -> attribute_value;
                        array_push($attributesArray, [
                            'label' => $attributeName,
                            'value' => $attributeValue
                        ]);
                    }
                    array_push($soItemsArray, [
                        'item_id' => $detailItem -> so_item_id,
                        'qty' => $detailItem -> qty,
                        'item_code' => $detailItem -> item_code,
                        'item_name' => $detailItem -> item_name,
                        'attributes' => $attributesArray
                    ]);
                    if ($detailItemIndex == 0) {
                        $totalQty += $detailItem -> qty;
                        //Build Items UI
                        $maxChar = 70;
                        $itemName = $detailItem -> item_name;
                        $totalChar = strlen($itemName);
                        $attributesHTML = "";
                        foreach($detailItem -> attributes as $itemAttr) {
                            $attributeName = $itemAttr -> attribute_name;
                            $attributeValue = $itemAttr -> attribute_value;
                            $totalChar += (strlen($attributeName) + strlen($attributeValue));
                            if ($totalChar <= $maxChar) {
                                $attributesHTML .= "<span class='badge rounded-pill badge-light-primary' > $attributeName : $attributeValue</span>";
                            } else {
                                $attributesHTML .= "..";
                            }
                        }
                        $itemsHTML .= "<span class='badge rounded-pill badge-light-primary' > $itemName</span> $attributesHTML";
                    } else {
                        $extraItemsCount += 1;
                    }                    
                }
                if ($extraItemsCount > 0) {
                    $itemsHTML += "<span class='badge rounded-pill badge-light-secondary' > + $extraItemsCount</span>";
                }
                $docDetail -> items_ui = $itemsHTML;
                $docDetail -> total_item_qty = $totalQty;
                $docDetail -> so_items_array = $soItemsArray;
            }
            $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
            $subStores = InventoryHelper::getAccesibleSubLocations($doc -> store_id);
            $parentUrl = request() -> segments()[0];
            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $doc -> book ?-> service ?-> alias);
            $revision_number = $doc->revision_number;
            $totalValue = 0;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($doc->book_id,$doc->document_status , $doc->id, $totalValue, $doc->approval_level, $doc -> created_by ?? 0, $userType['type'], $revision_number);
            $books = Helper::getBookSeriesNew(Constants::SERVICE_ALIAS) -> get();
            $revNo = $doc->revision_number;
            if($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $doc->revision_number;
            }
            $docValue = 0;
            $approvalHistory = Helper::getApprovalHistory($doc->book_id, $doc->id, $revNo, $docValue, $doc -> created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$doc->document_status] ?? '';
            // $dynamicFieldsUI = $order -> dynamicfieldsUi();
            $data = [
                'user' => $user,
                'series' => $books,
                'packingList' => $doc,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'maxFileCount' => isset($order -> mediaFiles) ? (10 - count($doc -> media_files)) : 10,
                'services' => $servicesBooks['services'],
                'redirectUrl' => $redirectUrl,
                'stores' => $stores,
                'subStores' => $subStores,
                'moduleName' => $moduleName
                // 'dynamicFieldsUi' => $dynamicFieldsUI
            ];
            return view('packingList.create_edit', $data);
        
        } catch(Exception $ex) {
            dd($ex -> getMessage());
        }
    }

    public function store(PackingListRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = $request -> user();
            //Auth credentials
            $organization = Organization::find($user -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $store = ErpStore::find($request -> store_id);
            $subStore = ErpSubStore::find($request -> sub_store_id);
            if (!$request -> packing_list_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = PackingList::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $packingList = null;
            if ($request -> packing_list_id) { //Update
                $packingList = PackingList::find($request -> packing_list_id);
                $packingList -> document_date = $request -> document_date;
                $packingList -> store_id = $request -> store_id;
                $packingList -> sub_store_id = $request -> sub_store_id;
                $packingList -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup
                if(($packingList -> document_status == ConstantHelper::APPROVED || $packingList -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'PackingList', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'PackingListDetail', 'relation_column' => 'plist_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'PackingListItem', 'relation_column' => 'plist_detail_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'PackingListItemAttribute', 'relation_column' => 'plist_item_id'],
                    ];
                    Helper::documentAmendment($revisionData, $packingList->id);
                }
                //Need to add logic to delete Items
            } else { //Create
                $packingList = PackingList::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request -> book_id,
                    'book_code' => $request -> book_code,
                    'store_id' => $store -> id,
                    'sub_store_id' => $subStore -> id,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request -> document_date,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => $request -> final_remarks
                ]);
            }
            // //Dynamic Fields
            // $status = DynamicFieldHelper::saveDynamicFields(ErpSoDynamicField::class, $saleOrder -> id, $request -> dynamic_field ?? []);
            // if ($status && !$status['status'] ) {
            //     DB::rollBack();
            //     return response() -> json([
            //         'message' => $status['message'],
            //         'error' => ''
            //     ], 422);
            // }
            foreach ($request -> items as $pickListItemIndex => $pickListItem) {
                $packingDetail = PackingListDetail::create([
                    'plist_id' => $packingList -> id,
                    'sale_order_id' => $pickListItem['sale_order_id'],
                    'packing_number' => $pickListItem['packet_name'],
                    'remarks' => ''
                ]);
                $itemsArray = json_decode($pickListItem['so_items'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($itemsArray)) {
                    foreach ($itemsArray as $itemArr) {
                        $soItem = ErpSoItem::with('item_attributes')->find($itemArr['item_id']);
                        if (!isset($soItem)) {
                            DB::rollBack();
                            return response() -> json([
                                'message' => 'Item No. ' . ($pickListItemIndex + 1) . ' has invalid items',
                                'error' => ''
                            ], 422);
                        }
                        $packingItem = PackingListItem::create([
                            'plist_id' => $packingList -> id,
                            'plist_detail_id' => $packingDetail -> id,
                            'sale_order_id' => $packingDetail -> sale_order_id,
                            'so_item_id' => $itemArr['item_id'],
                            'item_id' => $soItem -> item_id,
                            'item_code' => $soItem -> item_code,
                            'item_name' => $soItem -> item_name,
                            'qty' =>$itemArr['qty']
                        ]);
                        //Back Update SO Items
                        $soItem = ErpSoItem::find($itemArr['item_id']);
                        if (isset($soItem)) {
                            $soItem -> plist_qty += $packingItem -> qty;
                            $soItem -> plist_item_id = $packingItem -> id;
                            $soItem -> save();
                        }
                        foreach ($soItem -> item_attributes as $soItemAttr) {
                            PackingListItemAttribute::create([
                                'plist_id' => $packingList -> id,
                                'plist_detail_id' => $packingDetail -> id,
                                'plist_item_id' => $packingItem -> id,
                                'item_attribute_id' => $soItemAttr -> item_attribute_id,
                                'attribute_name' => $soItemAttr -> attribute_name,
                                'attr_name' => $soItemAttr -> attr_name,
                                'attribute_value' => $soItemAttr -> attribute_value,
                                'attr_value' => $soItemAttr -> attr_value
                            ]);
                        }
                    }
                } else {
                    DB::rollBack();
                    return response() -> json([
                        'message' => 'Item No. ' . ($pickListItemIndex + 1) . ' has invalid attributes',
                        'error' => ''
                    ], 422);
                }

            }
            //Approval check
            if ($request -> packing_list_id) { //Update condition
                $bookId = $packingList->book_id; 
                $docId = $packingList->id;
                $amendRemarks = $request->amend_remarks ?? null;
                $remarks = $packingList->remarks;
                $amendAttachments = $request->file('amend_attachments');
                $attachments = $request->file('attachment');
                $currentLevel = $packingList->approval_level;
                $modelName = get_class($packingList);
                $actionType = $request -> action_type ?? "";
                if(($packingList -> document_status == ConstantHelper::APPROVED || $packingList -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    //*amendmemnt document log*/
                    $revisionNumber = $packingList->revision_number + 1;
                    $actionType = 'amendment';
                    $totalValue = 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                    $packingList->revision_number = $revisionNumber;
                    $packingList->approval_level = 1;
                    $packingList->revision_date = now();
                    $amendAfterStatus = $approveDocument['approvalStatus'] ?? $packingList -> document_status;
                    $packingList->document_status = $amendAfterStatus;
                } else {
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $revisionNumber = $packingList->revision_number ?? 0;
                        $actionType = 'submit';
                        $totalValue = 0;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                        if ($approveDocument['message']) {
                            DB::rollBack();
                            return response()->json([
                                'message' => $approveDocument['message'],
                                'error' => "",
                            ], 422);
                        }

                        $document_status = $approveDocument['approvalStatus'] ?? $packingList -> document_status;
                        $packingList->document_status = $document_status;
                    } else {
                        $packingList->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                }
            } else { //Create condition
                if ($request->document_status == ConstantHelper::SUBMITTED) {
                    $bookId = $packingList->book_id;
                    $docId = $packingList->id;
                    $remarks = $packingList->remarks;
                    $attachments = $request->file('attachment');
                    $currentLevel = $packingList->approval_level;
                    $revisionNumber = $packingList->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $modelName = get_class($packingList);
                    $totalValue = $packingList->total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                    $packingList->document_status = $approveDocument['approvalStatus'] ?? $packingList->document_status;
                }
            }
            $packingList -> save();
            //Media
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $singleFile) {
                    $packingList->uploadDocuments($singleFile, 'packing_lists', false);
                }
            }
            DB::commit();
            return response() -> json([
                'message' => Constants::MODULE_NAME . " created successfully",
                'redirect_url' => route('packingList.index')
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => 'Server Error',
                'exception' => $ex -> getMessage() . $ex -> getFile() . $ex -> getLine()
            ], 500); 
        }
    }

    public function revoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $packingList = PackingList::find($request -> id);
            if (isset($packingList)) {
                $revoke = Helper::approveDocument($packingList -> book_id, $packingList -> id, $packingList -> revision_number, '', [], 0, ConstantHelper::REVOKE, 0, get_class($packingList));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $packingList -> document_status = $revoke['approvalStatus'];
                    $packingList -> save();
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

    public function getPullableDocuments(Request $request)
    {
        try {
            $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
            $storeId = $request -> store_id;
            $subStoreId = $request -> sub_store_id;
            $elements = ErpSoItem::whereHas('header', function ($subQuery) use ($request, $applicableBookIds, $storeId, $subStoreId) {
                $subQuery->where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                ->whereIn('book_id', $applicableBookIds)->when($request->customer_id, function ($custQuery) use ($request) {
                    $custQuery->where('customer_id', $request->customer_id);
                })->where('store_id', $storeId)->when($request->book_id, function ($bookQuery) use ($request) {
                    $bookQuery->where('book_id', $request->book_id);
                })->when($request->document_id, function ($docQuery) use ($request) {
                    $docQuery->where('id', $request->document_id);
                });
            })-> with('attributes') -> with('uom') -> with(['header.customer']) -> whereColumn('plist_qty', "<", "order_qty");

            if ($request->item_id) {
                $elements = $elements->where('item_id', $request->item_id);
            }

            $elements = $elements->get();

            foreach ($elements as &$element) {
                $element -> avl_stock = $element -> getAvailableStocks($storeId, $subStoreId);
            }

            return response()->json([
                'data' => $elements
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getDocumentItems(Request $request)
    {
        try {
            $saleOrderId = $request -> header_id;
            $uiSelectionArray = json_decode($request -> selection_array, true) ?? [];
            $items = ErpSoItem::select('id', 'item_name', 'item_code', 'order_qty', 'uom_id', 'item_id') 
            -> where('sale_order_id', $saleOrderId) -> whereHas('header', function ($headerQuery) {
                $headerQuery -> withDefaultGroupCompanyOrg() 
                -> whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);
            }) -> with('item_attributes') -> whereColumn('plist_qty', "<", "order_qty") -> get();
            foreach ($items as &$item) {
                $item -> avl_packing_qty = $item -> order_qty;
                $item -> uom_code = $item -> uom ?-> name;
                $totalStock = $item -> getAvailableStocks($request -> store_id, $request -> sub_store_id);
                $item -> avl_stock = $totalStock;
            }
            return response()->json([
                'status' => 200,
                'message' => 'Records retrieved successfully',
                'data' => $items
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getItemDetails(Request $request)
    {
        try {
            $plistDetailId = $request -> plist_detail_id ?? null;
            $soItems = json_decode($request -> so_item_ids, true);
            if (isset($plistDetailId)) { // Edit Case
                $plistDetail = PackingListDetail::find($plistDetailId);
                $itemsUI = "";
                if (isset($plistDetail)) {
                    foreach ($plistDetail -> items as $plistDetailItem) {
                        $itemName = $plistDetailItem -> item_name;
                        $attributesUI = "";
                        foreach ($plistDetailItem -> attributes as $plistDetailItemAttr) {
                            $attributeName = $plistDetailItemAttr -> attribute_name;
                            $attributeValue = $plistDetailItemAttr -> attribute_value;
                            $attributesUI .= "<span class='badge rounded-pill badge-light-primary' > $attributeName : $attributeValue</span>";
                        }
                        $itemsUI .= "<span class='badge rounded-pill badge-light-primary' > $itemName</span> $attributesUI <br/>";
                    }
                }
                return response() -> json([
                    'status' => 'success',
                    'message' => 'Details retrieved',
                    'data' => [
                        'items_ui' => $itemsUI
                    ]
                ]);
            } else { //Create Case
                return response() -> json([
                    'status' => 'success',
                    'message' => 'Details retrieved',
                    'data' => [
                        'items_ui' => ""
                    ]
                ]);
            }
        } catch(Exception $ex) {
            return response()->json([
                'message' => 'Some internal error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
