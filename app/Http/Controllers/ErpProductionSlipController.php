<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\Configuration\Constants;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Common\MathHelper;

use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\InspectionHelper;
use App\Helpers\ItemHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Http\Requests\PslipRequest;
use App\Models\Address;
use App\Models\ErpProductionSlip;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemAttribute;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpPslipItemLocation;
use App\Models\ErpSoItem;
use App\Models\Item;
use App\Models\MoBomMapping;
use App\Models\ItemAttribute;
use App\Models\ErpAttribute;
use App\Models\AlternateItem;
use App\Models\MoItem;
use App\Models\MoProduct;
use App\Models\Organization;
use App\Models\PslipBomConsumption;
use App\Models\PslipConsumptionLocation;

use App\Models\PwoStationConsumption;
use App\Models\Shift;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Lib\Services\ErpInspChecklistService;

use App\Services\PslipDeleteService;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Helpers\BookHelper;
use App\Models\BomDetail;
use Yajra\DataTables\DataTables;

class ErpProductionSlipController extends Controller
{

    protected $productionSlipId;

    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $redirectUrl = route('production.slip.index');
        $createRoute = route('production.slip.create');
        $typeName = "Production Slip";
        if ($request -> ajax()) {
            try {
            $docs = ErpProductionSlip::bookViewAccess($pathUrl)
                    ->withDraftListingLogic();
            return DataTables::of($docs) ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                return view('partials.action-dropdown', [
                    'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status ?? ConstantHelper::DRAFT],
                    'displayStatus' => $row->display_status,
                    'row' => $row,
                    'actions' => [
                        [
                            'url' => fn($r) => route('production.slip.edit', ['id' => $r->id]),
                            'icon' => 'edit-3',
                            'label' => 'View/ Edit Detail',
                        ]
                    ]
                ])->render();
            })
            ->addColumn('book_name', function ($row) {
                return $row->book_code ? $row->book_code : '';
            })
            ->addColumn('store_name', function ($row) {
                return $row->store ? $row->store?->store_name  : '';
            })
            ->addColumn('sub_store_name', function ($row) {
                return $row->sub_store?->name ? $row->sub_store?->name  : '';
            })
            ->addColumn('station_name', function ($row) {
                return $row?->station ? $row->station?->name  : '';
            })
            ->addColumn('shift_name', function ($row) {
                return $row->shift?->label ? $row->shift?->label  : '';
            })
            ->addColumn('mo_no', function ($row) {
                return $row?->mo ? ($row?->mo->book_code .' - '. $row?->mo->document_number)  : '';
            })
            ->addColumn('mo_product', function ($row) {
                return $row?->mo ? $row?->mo?->item?->item_name  : '';
            })
            ->addColumn('type', function ($row) {
                return $row?->is_last_station ? 'Final'  : 'WIP';
            })
            ->addColumn('so_no', function ($row) {
                $bookCode = strtoupper($row?->last_so()?->book_code);
                return $row?->last_so() ? ($bookCode .' - '. $row?->last_so()?->document_number)  : '';
            })
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
            })
            ->addColumn('produced_qty', function ($row) {
                return isset($row?->pslip_items) ? (number_format($row?->pslip_items()->sum('qty'),4)) : ' ';
            })
            ->addColumn('accepted_qty', function ($row) {
                return isset($row?->pslip_items) ? (number_format($row?->pslip_items()->sum('accepted_qty'),4)) : ' ';
            })
            ->addColumn('subprime_qty', function ($row) {
                return isset($row?->pslip_items) ? (number_format($row?->pslip_items()->sum('subprime_qty'),4)) : ' ';
            })
            ->addColumn('rejected_qty', function ($row) {
                return isset($row?->pslip_items) ? (number_format($row?->pslip_items()->sum('rejected_qty'),4)) : ' ';
            })
            ->addColumn('wip_qty', function ($row) {
                return isset($row?->pslip_items) ? (number_format($row?->pslip_items()->sum('wip_qty'),4)) : ' ';
            })
            ->addColumn('total_qty', function ($row) {
                $t = $row?->pslip_items()->sum('qty') + $row?->pslip_items()->sum('wip_qty');
                return isset($row?->pslip_items) ? (number_format($t,4)) : ' ';
            })
            // ->addColumn('value', function ($row) {
            //     if ($row->pslip_items && $row->pslip_items()->exists()) {
            //         return number_format(
            //             $row->pslip_items()->select(DB::raw('SUM(qty * rate) as total'))->value('total'),
            //             2
            //         );
            //     }
            //     return ' ';
            // })
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
        $user = Helper::getAuthenticatedUser();
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL, '', $user);
        $groupAlias = $user?->auth_user?->group_alias ?? 'Staqo';
        $isWipQty = in_array($groupAlias, Constants::GROUP_PSLIP_WIP_QTY);
        return view('productionSlip.index', ['isWipQty' => $isWipQty, 'typeName' => $typeName, 'redirect_url' => $redirectUrl, 'create_route' => $createRoute, 'create_button' => count($servicesBooks['services'])]);
    }

    public function create(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $parentURL = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL, '', $user);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $redirectUrl = route('production.slip.index');
        $firstService = $servicesBooks['services'][0];
        $typeName = "Packing Slip";
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $currentBundleNo = ErpPslipItemDetail::orderByDesc('id')->first() ?-> bundle_no ?? 0;
        $startingBundleNo = $currentBundleNo + 1;
        $editableBundle = true;
        if ($currentBundleNo > 0) {
            $editableBundle = false;
        }
        // $authUser = Helper::getAuthenticatedUser();
        $organization = Organization::find($user ?->organization_id);
        $organizationId = $organization ?-> id ?? null;
        $shifts = Shift::where('organization_id', $organizationId)->where("status", ConstantHelper::ACTIVE)->get();
        $machines = collect();
        $stationLines = collect();
        $groupAlias = $user?->auth_user?->group_alias ?? '';
        $isWipQty = in_array($groupAlias, Constants::GROUP_PSLIP_WIP_QTY);

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
            'shifts' => $shifts,
            'machines' => $machines,
            'stationLines' => $stationLines,
            'isWipQty' => $isWipQty
        ];

        return view('productionSlip.create_edit', $data);
    }

    public function edit(Request $request, String $id)
    {
        $this->productionSlipId = $id;

        try {
            $user = Helper::getAuthenticatedUser();
            $parentUrl = request() -> segments()[0];
            $redirect_url = route('production.slip.index');

            $servicesBooks = [];
            if (isset($request -> revisionNumber))
            {
                $doc = ErpProductionSlip::with(['media_files']) -> with('items', function ($query) {
                    $query -> with(['to_item_locations', 'bundles',
                    'item' => function ($itemQuery) {
                        $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'to_item_locations']);
                    },
                    'checklists' => function ($inspQuery) {
                            $inspQuery->where('header_id', $this->productionSlipId);
                    }]);
                })->where('source_id', $request -> id)->first();
                $ogDoc = ErpProductionSlip::find($id);
            } else {
                $doc = ErpProductionSlip::with(['media_files']) -> with('items', function ($query) {
                    $query->with(['to_item_locations', 'bundles', 'item' => function ($itemQuery) {
                        $itemQuery-> with(['specifications', 'alternateUoms.uom', 'uom']);
                    },
                    'checklists' => function ($inspQuery) {
                        $inspQuery->where('header_id', $this->productionSlipId);
                    }]);
                })->find($id);

                $ogDoc = $doc;
            }


            if(!$doc) {
                return redirect('/production-slip')->with('error', 'The provided documnet id is invalid.');
            }

            $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
            if (isset($doc)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $doc->book?->service?->alias, $user);
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
            $organization = Organization::find($user ?->organization_id);
            $organizationId = $organization ?-> id ?? null;
            $shifts = Shift::where('organization_id',$organizationId)->where("status", ConstantHelper::ACTIVE)->get();
            $machines = collect();
            $productionBom = $doc?->mo?->productionRoute ?? null;
            if($productionBom) {
                $machines = $productionBom?->machines()
                ->where('status', ConstantHelper::ACTIVE)
                ->get();
            }

            $stationLines = collect();

            //  if($doc?->mo) {
            if($doc?->mo?->station?->lines) {
                $stationLines = $doc?->mo?->station?->lines;
            }

            $groupAlias = $user?->auth_user?->group_alias ?? 'Staqo';
            $isWipQty = in_array($groupAlias, Constants::GROUP_PSLIP_WIP_QTY);
            $data = [
                'isWipQty' => $isWipQty,
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
                'redirect_url' => $redirect_url,
                'machines' => $machines,
                'stationLines' => $stationLines
            ];

            return view('productionSlip.create_edit', $data);
        } catch(Exception $ex) {
            // dd($ex -> getMessage());
            return back()->with('error', 'Error: '.$ex -> getMessage());
        }
    }

    public function store(PslipRequest $request)
    {

        $consuptions = $request->cons;
        $productionSlipId = isset($request->id) ? $request->id : null;
        if(!$productionSlipId && !$consuptions)
        {
            return response()->json([
                'message' => 'Atleast one consuption line item must be there.',
                'error' => "MO items are missing, please pull.",
            ], 422);
        }
        try {

            // Handle Inspection Check
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }

            if(!isset($parameters['inspection_required'][0]))
            {
                return response()->json([
                    'message' => "Please update inspection in admin services"
                ], 422);
            }

            $inspectionReqired = ($parameters['inspection_required'][0] === 'no') ? 0 : 1;

            //Reindex
            $request -> item_qty =  array_values($request -> item_qty ?? []);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id ?? []);

            DB::beginTransaction();

            if ($request -> item_id && count($request -> item_id) < 1) {
                DB::rollBack();
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
                DB::rollBack();
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422);
            }

            if (!$request -> production_slip_id)
            {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpProductionSlip::where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        DB::rollBack();
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $productionSlip = null;
            // $store = ErpStore::find($request -> store_id);
            $productionSlip = ErpProductionSlip::find($request -> pslip_id);
            if ($productionSlip) {

                // Handle Reject Case
                if(isset($request->approve_reject_action_type) && $request->approve_reject_action_type == ConstantHelper::REJECTED) {
                    $modelName = get_class($productionSlip);
                    $revisionNumber = $productionSlip->revision_number;
                    $actionType = 'reject';
                    $approvalAttachment = $request->approver_reject_attachments;
                    $approveDocument = Helper::approveDocument($productionSlip->book_id, $productionSlip->id, $revisionNumber, $request->approver_reject_remarks, $approvalAttachment, $productionSlip->approval_level, $actionType, 0, $modelName);
                    $productionSlip->approval_level = $approveDocument['nextLevel'];
                    $productionSlip->document_status = $approveDocument['approvalStatus'];
                    $productionSlip->save();

                    DB::commit();
                    $module = "Production slip";
                    $docStatus = $request->approve_reject_action_type;
                    return response() -> json([
                        'message' => $module .  " $docStatus successfully",
                        'redirect_url' => route('production.slip.index')
                    ]);
                }

                $productionSlip->document_date = $request->document_date;
                $productionSlip->lot_number = $request->lot_number;
                $productionSlip->manufacturing_year = $request->manufacturing_year;
                $productionSlip->expiry_date = $request->expiry_date ? $request->expiry_date : null;
                // $productionSlip->reference_number = $request->reference_no;
                //Store and department keys
                $productionSlip->store_id = $request->store_id ?? null;
                $productionSlip->fg_sub_store_id = $request->fg_sub_store_id ?? null;
                $productionSlip->rg_sub_store_id = $request->rg_sub_store_id ?? null;
                // $productionSlip->store_code = $store ?-> store_code ?? null;
                $productionSlip->remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                $productionSlip->mo_id = $request->mo_id ? $request->mo_id[0] : $request->mo_id;
                $productionSlip->is_last_station = $request->is_last_station ?? 0;
                $productionSlip->station_id = $request->mo_station_id;
                $productionSlip->save();

                //Amend backup
                // if(($productionSlip -> document_status == ConstantHelper::APPROVED || $productionSlip -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                // {
                //     $revisionData = [
                //         ['model_type' => 'header', 'model_name' => 'ErpProductionSlip', 'relation_column' => ''],
                //         ['model_type' => 'detail', 'model_name' => 'ErpMiItem', 'relation_column' => 'material_issue_id'],
                //         ['model_type' => 'sub_detail', 'model_name' => 'ErpMiItemAttribute', 'relation_column' => 'mi_item_id'],
                //         ['model_type' => 'sub_detail', 'model_name' => 'ErpMiItemLocation', 'relation_column' => 'mi_item_id'],
                //     ];
                //     $a = Helper::documentAmendment($revisionData, $productionSlip->id);

                // }


                // ---------------------------------------------------------------
                // Handle Deletion of Items and Manage Production Slip Stocks
                // ---------------------------------------------------------------

                // Define the keys we expect from the request for deleted records
                $keys = ['deletedSiItemIds', 'deletedAttachmentIds', 'deletedConsItemIds'];

                // Decode deleted items from request JSON into PHP arrays
                // Example: request('deletedSiItemIds') = "[1,2,3]" -> [1,2,3]
                $deletedData = collect($keys)->mapWithKeys(function ($key) use ($request) {
                    return [$key => json_decode($request->input($key, '[]'), true)];
                })->toArray();

                // Initialise the deletion service
                $pslipDeleteService = new PslipDeleteService();

                // Centralised helper to handle service deletion responses
                $deletion = function ($response) {
                    if ($response['status'] === 'error') {
                        DB::rollBack(); // Revert DB changes if error occurs
                        return response()->json([
                            'message' => $response['message'],
                            'error'   => ''
                        ], 422);
                    }
                    return null; // Continue if success
                };

                // ---------------------------------------------------------------
                // Delete Production Items
                // ---------------------------------------------------------------
                if ($result = $deletion($pslipDeleteService->deleteProductionItems($deletedData, $productionSlip))) {
                    return $result;
                }

                // ---------------------------------------------------------------
                // Delete Consumption Items
                // ---------------------------------------------------------------
                if ($result = $deletion($pslipDeleteService->deleteConsumptionItems($deletedData, $productionSlip))) {
                    return $result;
                }

                // ---------------------------------------------------------------
                // If no items remain in production slip, reset slip properties
                // ---------------------------------------------------------------
                // if ($productionSlip->fresh()->items->isEmpty()) {
                //     $productionSlip->update([
                //         'mo_id'          => null,
                //         'is_last_station'=> 0,
                //         'station_id'     => null,
                //         'fg_sub_store_id'=> null,
                //         'rg_sub_store_id'=> null,
                //     ]);
                // }


            } else { //Create
                $productionSlip = ErpProductionSlip::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'mo_id' => $request->mo_id ? $request->mo_id[0] : $request->mo_id,
                    'bom_id' => $request->bom_id,
                    'is_last_station' => $request->is_last_station ?? 0,
                    'station_id' => $request->mo_station_id,
                    'book_id' => $request -> book_id,
                    'book_code' => $request -> book_code,
                    'lot_number' => $request -> lot_number,
                    'manufacturing_year' => $request -> manufacturing_year,
                    'expiry_date' => $request->expiry_date ? $request->expiry_date : null,
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
                    'fg_sub_store_id' => $request -> fg_sub_store_id ?? null,
                    'rg_sub_store_id' => $request -> rg_sub_store_id ?? null,
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
                                // 'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
                                'customer_id' => isset($request -> customer_id[$itemKey]) ? $request -> customer_id[$itemKey] : null,
                                'inventory_uom_id' => $item -> uom ?-> id,
                                'inventory_uom_code' => $item -> uom ?-> name,
                                'inventory_uom_qty' => $inventoryUomQty,
                                'remarks' => isset($request -> item_remarks[$itemKey]) ? $request -> item_remarks[$itemKey] : null,
                                'accepted_qty' => isset($request -> item_accepted_qty[$itemKey]) ? $request -> item_accepted_qty[$itemKey] : null,
                                'subprime_qty' => isset($request -> item_sub_prime_qty[$itemKey]) ? $request -> item_sub_prime_qty[$itemKey] : null,
                                'rejected_qty' => isset($request -> item_rejected_qty[$itemKey]) ? $request -> item_rejected_qty[$itemKey] : null,
                                'wip_qty' => isset($request -> item_wip_qty[$itemKey]) ? $request -> item_wip_qty[$itemKey] : null,
                                'machine_id' => isset($request -> machine_id[$itemKey]) ? $request -> machine_id[$itemKey] : [],
                                // 'station_line_id' => isset($request -> line[$itemKey]) ? $request -> line[$itemKey] : null,
                                'cycle_count' => isset($request -> cycle_count[$itemKey]) ? $request -> cycle_count[$itemKey] : null,
                                'station_line_id' => isset($request -> station_line_id[$itemKey]) ? $request -> station_line_id[$itemKey] : null,
                                'supervisor_name' => isset($request -> supervisor_name[$itemKey]) ? $request -> supervisor_name[$itemKey] : null,
                            ]);
                        }
                    }

                    $oldItem = [];

                    foreach ($itemsData as $itemDataKey => $itemDataValue) {

                        if($itemDataValue['rejected_qty'] > 0 && empty($request->rg_sub_store_id)) {
                             DB::rollBack();
                            return response()->json([
                                'message' => 'Please select rejected store.',
                                'error' => "Please select rejected store. If rejected qty is greater than 0",
                            ], 422);
                        }

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
                            // 'rate' => $itemDataValue['rate'],
                            'customer_id' => $itemDataValue['customer_id'],
                            'inventory_uom_id' => $itemDataValue['inventory_uom_id'],
                            // 'inventory_uom_code' => $itemDataValue['inventory_uom_code'],
                            'inventory_uom_qty' => $itemDataValue['inventory_uom_qty'],
                            'remarks' => $itemDataValue['remarks'],
                            'accepted_qty' => $itemDataValue['accepted_qty'] ?? 0,
                            'subprime_qty' => $itemDataValue['subprime_qty'] ?? 0,
                            'rejected_qty' => $itemDataValue['rejected_qty'] ?? 0,
                            'wip_qty' => $itemDataValue['wip_qty'] ?? 0,
                            'machine_id' => $itemDataValue['machine_id'] ?? [],
                            'cycle_count' => $itemDataValue['cycle_count'] ?? null,
                            'station_line_id' => $itemDataValue['station_line_id'] ?? null,
                            'supervisor_name' => $itemDataValue['supervisor_name'] ?? null,
                        ];
                        if (isset($request -> pslip_item_id[$itemDataKey])) {
                            $pslipItemExit = ErpPslipItem::where('id', $request -> pslip_item_id[$itemDataKey])
                                ->where('pslip_id', $productionSlip -> id)
                                ->first();
                            $psItem = ErpPslipItem::updateOrCreate(['id' => $request -> pslip_item_id[$itemDataKey]], $itemRowData);
                            $oldItem[$psItem->id] = [
                                'qty'    => $pslipItemExit?->qty ?? 0,
                                'is_new' => $psItem->wasRecentlyCreated,
                            ];
                        } else {
                            $psItem = ErpPslipItem::create($itemRowData);
                            $oldItem[$psItem->id] = ['qty' => 0, 'is_new' => true];
                        }

                        $inspectionData = isset($request->inspection_data[$itemKey])
                            ? (is_string($request->inspection_data[$itemKey])
                                ? json_decode($request->inspection_data[$itemKey], true) ?? []
                                : $request->inspection_data[$itemKey])
                            : [];

                        $itemCheck = Item::find($psItem->item_id);

                        if($inspectionReqired && $itemCheck && count($itemCheck->loadInspectionChecklists())) {
                            $inspectionValidator = InspectionHelper::validateInspectionCheckList($inspectionData, $itemCheck);
                            if(!$inspectionValidator['status']) {
                                DB::rollBack();
                                return response() -> json([
                                    'message' => $inspectionValidator['message'],
                                    'error' => 'Inspection001'
                                ], 422);
                            }

                            //ErpInspChecklistService class storing inspection checklist data into the `erp_insp_checklists` table.
                            $inspChecklistService = (new ErpInspChecklistService(
                                ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
                                $productionSlip->id,
                                $psItem->id,
                                $psItem->item_id
                            ))->sync($inspectionData);
                        }


                        // $stationId = $psItem->station_id ?? null;
                        // $bomDetails = PwoBomMapping::where('pwo_mapping_id', $psItem?->mo_product?->pwo_mapping_id)
                        // ->where(function($query) use($stationId) {
                        //     if($stationId) {
                        //         $query->where('station_id', $stationId);
                        //     }
                        // })
                        // ->get();
                        // $bomDetails = MoBomMapping::where('mo_product_id', $psItem->mo_product_id)->get();
                        // foreach ($bomDetails as $bomDetailKey => $bomDetail) {
                        $alternateId = null;
                        $consArr = [];
                        foreach ($consuptions as $consuption) {

                            $alternateId = $consuption['alternate_id'] ?? null;
                            $attachments = $request->attachments ?? [];

                            if($alternateId && !count($attachments)){
                                DB::rollBack();
                                return response() -> json([
                                    'message' => 'If alternate item added, please upload documents.',
                                    'error' => 'alternate_id remarks required'
                                ], 422);
                            }

                            if($alternateId && empty($consuption['consumption_qty'])){
                                DB::rollBack();
                                return response() -> json([
                                    'message' => 'Consumed qty required for alternate item: '.$consuption['item_name'],
                                    'error' => 'consumption_qty required'
                                ], 422);
                            }


                            $bomDetail = MoBomMapping::find($consuption['mo_bom_cons_id']);
                            $item = Item::find($consuption['item_id']);


                            $pslipBomConsId = @$consuption['pslip_bom_cons_id'];
                            $pslipBomMapping = PslipBomConsumption::find($pslipBomConsId) ??  new PslipBomConsumption;
                            // $pslipBomMapping = PslipBomConsumption::where('pslip_id', $productionSlip?->id)
                            //             ->where('pslip_item_id', $psItem?->id)
                            //             ->where('bom_detail_id', $bomDetail->bom_detail_id)
                            //             ->when(isset($consuption['pslip_bom_cons_id']) && !empty($consuption['pslip_bom_cons_id']),
                            //                 fn($q) => $q->where('id', $consuption['pslip_bom_cons_id'])
                            //             )
                            //             ->where('station_id', $bomDetail->station_id)
                            //             ->first() ?? new PslipBomConsumption;

                            $previousConsumption = $pslipBomMapping->exists ? $pslipBomMapping->consumption_qty : 0;
                            $newConsumption = floatval($bomDetail->bom_qty) * floatval($itemDataValue['qty']);

                            $pslipBomMapping->mo_bom_mapping_id = $bomDetail?->id;
                            // $pslipBomMapping->rm_type = $bomDetail?->rm_type;
                            $pslipBomMapping->rm_type = $alternateId ? $consuption['item_type'] : $bomDetail?->rm_type;
                            $pslipBomMapping->pslip_id = $productionSlip?->id;
                            $pslipBomMapping->pslip_item_id = $psItem?->id;
                            $pslipBomMapping->so_id = $psItem->so_id ?? null;
                            $pslipBomMapping->so_item_id = $psItem->so_item_id ?? null;
                            $pslipBomMapping->bom_id = $bomDetail->bom_id;
                            $pslipBomMapping->bom_detail_id = $bomDetail->bom_detail_id;
                            $pslipBomMapping->item_id = $consuption['item_id'];
                            $pslipBomMapping->item_code = $item?->item_code;
                            // $pslipBomMapping->item_id = $bomDetail->item_id;
                            // $pslipBomMapping->item_code = $bomDetail->item_code;
                            if(isset($consuption['attribute_value']) && !empty($consuption['attribute_value'])) {
                                $pslipBomMapping->attributes = json_decode($consuption['attribute_value']);
                            }else {
                                $pslipBomMapping->attributes = $bomDetail->attributes;
                            }
                            // $pslipBomMapping->attributes = $bomDetail->attributes;
                            $pslipBomMapping->uom_id = $consuption['uom_id'];
                            // $pslipBomMapping->uom_id = $bomDetail->uom_id;
                            $pslipBomMapping->qty = $bomDetail->bom_qty;
                            $pslipBomMapping->base_item_id = $alternateId;

                            $pslipBomMapping->required_qty = floatval($bomDetail->bom_qty)*floatval($itemDataValue['qty']);
                            $pslipBomMapping->consumption_qty = floatval($consuption['consumption_qty']);
                            $pslipBomMapping->inventory_uom_qty = floatval($consuption['consumption_qty']);
                            $pslipBomMapping->station_id = $bomDetail->station_id;
                            $pslipBomMapping->section_id = $bomDetail->section_id;
                            $pslipBomMapping->sub_section_id = $bomDetail->sub_section_id;
                            $pslipBomMapping->save();

                            $consArr[] = $pslipBomMapping->toArray();

                            $delta = $newConsumption - $previousConsumption;
                            // Back Update Mo Item Consumption
                            $moProductAttributes = $bomDetail->attributes ?? [];
                            $moItem = MoItem::where('mo_id',$itemDataValue['mo_id'])
                                            ->when($psItem->so_id, function ($query) use ($psItem) {
                                                $query->where('so_id', $psItem->so_id);
                                            })
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
                                $moItem->consumed_qty += $delta;
                                $moItem->save();
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
                                DB::rollBack();
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid attributes',
                                    'error' => ''
                                ], 422);
                            }
                        }

                        //Bundle data
                        // if ($item -> storage_type == ConstantHelper::BUNDLE) {
                            $bundlesArray = json_decode($request -> item_bundles[$itemDataKey], true);
                            if (!empty($bundlesArray) && is_array($bundlesArray) && json_last_error() === JSON_ERROR_NONE) {
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
                                $checkQty = floatval($psItem -> accepted_qty) + floatval($psItem->subprime_qty);
                                if ($itemQtyBundleWise != $checkQty) {
                                    DB::rollBack();
                                    return response() -> json([
                                        'message' => 'Item No. ' . ($itemDataKey + 1) . ' has exceeded bundle qty',
                                        'error' => ''
                                    ], 422);
                                }
                            } elseif (!empty($bundlesArray)) {
                                DB::rollBack();
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid bundle data',
                                    'error' => ''
                                ], 422);
                            }
                        // }
                    }
                } else {

                    // If no production slip ID and request has slip ID with "SUBMITTED" status
                    if (!$productionSlipId && $request->pslip_id && $request->document_status == ConstantHelper::SUBMITTED) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Please add at least one row in product table.',
                            'error'   => '',
                        ], 422);
                    }
                }

                //Approval check
                if ($request -> pslip_id)
                {
                    //Update condition
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

                    }
                    //Approved Case
                    else if($request->document_status == ConstantHelper::APPROVED)
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $productionSlip->revision_number;
                        $actionType = 'approve';
                        $approvalAttachment = $request->approver_reject_attachments;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $request->approver_reject_remarks, $approvalAttachment, $currentLevel, $actionType, 0, $modelName);
                        $productionSlip->approval_level = $approveDocument['nextLevel'];
                        $productionSlip->document_status = $approveDocument['approvalStatus'];
                        $productionSlip->save();

                        if($productionSlip->is_last_station && in_array($productionSlip->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)) {
                            foreach($productionSlip->items as $pslipItem) {
                                $moProduct = $pslipItem?->mo_product ?? null;
                                if($moProduct) {
                                    $moProduct->pwoMapping->pslip_qty += floatval($pslipItem->qty);
                                    $moProduct->pwoMapping->save();
                                    if($moProduct?->soItem) {
                                        $moProduct->soItem->pslip_qty += floatval($pslipItem->qty);
                                        $moProduct->soItem->save();
                                    }
                                }
                            }
                        }

                    }
                    else {

                        if ($request->document_status == ConstantHelper::SUBMITTED) {

                            $revisionNumber = $productionSlip->revision_number ?? 0;
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                            $totalValue = $productionSlip->grand_total_amount ?? 0;
                            // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                            $productionSlip->document_status = $approveDocument['approvalStatus'];
                        } else {
                            $productionSlip->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }


                    }
                } else
                { //Create condition

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
                        $totalValue = $productionSlip->total_amount ?? 0;

                        $productionSlip->document_status = $approveDocument['approvalStatus'];
                    }

                    // if ($request->document_status == 'submitted') {
                        // $totalValue = $productionSlip->total_amount ?? 0;
                        // $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        // $productionSlip->document_status = $document_status;
                    // } 
                    else {
                    
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

                // Issue Raw Materials
                if ($productionSlip->fresh()->items->count()) {
                    // Maintain stock ledger once (no need to call it in a loop)
                    $maintainStockLedger = self::maintainStockLedger($productionSlip);

                    if ($maintainStockLedger['status'] === 'error') {
                        DB::rollBack();
                        return response()->json([
                            'message' => $maintainStockLedger['message'],
                            'error'   => 'ERR_maintainStockLedger'
                        ], 422);
                    }

                    // Assign inherited Lot Numbers to items in $productionSlip
                    // Returns ['status' => bool, 'message' => string]
                    self::assignInheritedLotNumber($productionSlip);
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
                    $moProdItemLocation = ErpPslipItemLocation::where('pslip_id', $productionSlip->id)
                        ->where('pslip_item_id', $moProdItem->id)
                        ->first() ?? new ErpPslipItemLocation;
                    $moProdItemLocation->pslip_id = $productionSlip->id;
                    $moProdItemLocation->pslip_item_id = $moProdItem->id;
                    $moProdItemLocation->item_id = $moProdItem->item_id;
                    $moProdItemLocation->store_id = $moProdItem?->mo?->store_id;
                    $moProdItemLocation->sub_store_id = $moProdItem?->mo?->sub_store_id;
                    $moProdItemLocation->station_id = $moProdItem?->mo?->station_id;
                    $moProdItemLocation->quantity = $moProdItem->qty;
                    $moProdItemLocation->inventory_uom_qty = $moProdItem->qty;

                    $moProdItemLocation->accepted_qty = $moProdItem->accepted_qty ?? 0;
                    $moProdItemLocation->subprime_qty = $moProdItem->subprime_qty ?? 0;
                    $moProdItemLocation->rejected_qty = $moProdItem->rejected_qty ?? 0;

                    $moProdItemLocation->save();
                }

                if($productionSlip->fresh()->items->count()){

                    $moProdItemReceipt = InventoryHelper::settlementOfInventoryAndStock($productionSlip->id, $detailIds, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS, $productionSlip->document_status, 'receipt');
                    if($moProdItemReceipt['status'] == 'error') {
                        DB::rollBack();
                        return response()->json([
                            'message' => $moProdItemReceipt['message'],
                            'error' => ''
                        ], 422);
                    }

                }

                // Back Update Mo Product Qty
                foreach($productionSlip->items as $pslipItem) {

                    $moProduct = $pslipItem?->mo_product ?? null;
                    if($moProduct) {

                        $previousQty = floatval(($oldItem[$pslipItem->id]['accepted_qty'] ?? 0) + ($oldItem[$pslipItem->id]['subprime_qty'] ?? 0));
                        $newQty = floatval($pslipItem->accepted_qty ?? 0) + floatval($pslipItem->subprime_qty ?? 0);
                        $deltaQty = $newQty - $previousQty;
                        // $moProduct->pslip_qty += floatval($pslipItem->qty);
                        $moProduct->pslip_qty += $deltaQty;
                        $moProduct->save();
                        $pwoStation = PwoStationConsumption::where('pwo_mapping_id',$moProduct?->pwoMapping?->id)
                                            ->where('mo_id',$moProduct->mo_id)
                                            ->where('station_id',$moProduct?->mo?->station_id)
                                            ->first();
                        if($pwoStation) {
                            $pwoStation->pslip_qty += $deltaQty;
                            $pwoStation->save();
                        }
                        if($moProduct?->mo?->is_last_station && in_array($productionSlip->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)) {
                            $moProduct->pwoMapping->pslip_qty += $deltaQty;
                            $moProduct->pwoMapping->save();
                            if($moProduct?->soItem) {
                                $moProduct->soItem->pslip_qty += $deltaQty;
                                $moProduct->soItem->save();
                            }
                        }
                    }
                }

                DB::commit();

                $module = "Production slip";
                $docStatus = $request->document_status;
                return response() -> json([
                    'message' => $module .  " $docStatus successfully",
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
            $order = isset($order) ? $order->orderByDesc('id')->get() : new Collection();
            $order = $order->values();
            $html = view('productionSlip.partials.mo-product-item', ['orders' => $order])->render();
            return response()->json([
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
            $docIds = json_decode($request?->docIds, true) ?? [];
            $order = MoProduct::whereIn('id', $docIds)
                    ->with('attributes')
                    ->with('uom')
                    ->with('so')
                    ->get();
            $mo = [];
            if($order?->count()) {
                $bomId = $order[0]->mo?->production_bom_id ?? null;
                $mo['mo_id'] = $order[0]->mo?->id ?? '';
                $mo['mo_bom_id'] = $bomId;
                $mo['mo_no'] = $order[0]->mo->book_code. " - ". $order[0]->mo->document_number;
                $mo['mo_date'] = $order[0]->mo->getFormattedDate('document_date') ?? '';
                $mo['mo_product_id'] = $order[0]->mo->item_id ?? '';
                $mo['mo_product_name'] = $order[0]->mo->item->item_name ?? '';
                $mo['is_batch_no'] = $order[0]->mo->item->is_batch_no ?? 0;
                $mo['is_last_station'] = $order[0]->mo->is_last_station ?? false;
                $mo['mo_type'] = $order[0]->mo->is_last_station == true ? 'Final' : 'WIP';
                $mo['mo_station_id'] = $order[0]->mo->station_id ?? '';
                $mo['mo_station_name'] = $order[0]->mo->station?->name ?? '';
                $mo['mo_machine_id'] = $order[0]->mo->machine_id ?? '';
            }
            $stationWise = $request->station_wise_consumption ?? 'no';
            $productionBom = $order[0]->mo->productionRoute ?? null;

            $machines = collect();
            if($productionBom) {
                $machines = $productionBom?->machines()
                    ->where('status', ConstantHelper::ACTIVE)
                    ->get();
            }
            $stationLines = $order[0]?->mo?->station?->lines ?? collect();
            $consumptions = MoBomMapping::whereIn('mo_product_id',$docIds)->orderBy('mo_product_id')->get();
            $consHtml = view('productionSlip.partials.process-consumtion', ['consumptions' => $consumptions])->render();
            $user = Helper::getAuthenticatedUser();
            // $groupAlias = $user->group?->alias ?? '';
            $groupAlias = $user?->auth_user?->group_alias ?? '';
            $isWipQty = in_array($groupAlias, Constants::GROUP_PSLIP_WIP_QTY);

            $html = view('productionSlip.partials.pull-row', [
                'orders' => $order, 'stationWise' => $stationWise, 'mo' => $mo,
                'machines' => $machines, 'isWipQty' => $isWipQty,
                'stationLines' => $stationLines, 'inspection_required' => $request->inspection_required
            ])->render();

            return response() -> json([
                'message' => 'Data found',
                'data' => [
                    'html' => $html, 'mo' => $mo,
                    'consHtml' => $consHtml, 'is_machine' => $machines->count() > 0 ? true : false,
                    'stationLines' => $stationLines
                ],
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

    //Function to get sub store for finished goods
    public function getSubStore(Request $request)
    {
        $storeId = $request->store_id;
        $sub_store = InventoryHelper::getAccesibleSubLocations($storeId ?? 0,null, [ConstantHelper::SHOP_FLOOR]);
        $fg_sub_store = InventoryHelper::getAccesibleSubLocations($storeId ?? 0,null, [ConstantHelper::STOCKK]);
        $rg_sub_store = InventoryHelper::getAccesibleSubLocations($storeId ?? 0,null, [ConstantHelper::STOCKK]);
        $results = [
            'sub_store' => $sub_store,
            'fg_sub_store' => $fg_sub_store,
            'rg_sub_store' => $rg_sub_store
        ];
        return response()->json(['data' => $results, 'status' => 200, 'message' => "fetched!"]);
    }

    private static function maintainStockLedger(ErpProductionSlip $pslip)
    {
        $pslipStatus = $pslip->document_status;
        $detailIds = $pslip->fresh()->consumptions
            ->where('consumption_qty', '>', 0)
            ->pluck('id')->toArray();

        if(!count($detailIds)) {
            return [
                'status' => 'success',
                'message' => 'Success as per consumption qty 0'
            ];
        }

        $issueRecords = InventoryHelper::settlementOfInventoryAndStock($pslip->id, $detailIds, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS, $pslipStatus, 'issue');

        // if(isset($issueRecords['message']) && $issueRecords['message'] != 'Success') {
        //     return $issueRecords['message'];
        // }

        if(!empty($issueRecords['data'])){
            foreach($issueRecords['data'] as $key => $val){

                // $pslipConsumption = PslipBomConsumption::where('id',@$val->issuedBy->document_detail_id)->first();
                // $qty = ItemHelper::convertToAltUom($val->issuedBy->item_id, $pslipConsumption?->uom_id, $val->issuedBy->issue_qty);
                $qty = $val->issuedBy->issue_qty;
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
                // $a = PslipBomConsumption::where('id',$stockLedger?->document_detail_id)->first();
                $psConsumption = PslipBomConsumption::find($stockLedger->document_detail_id);
                // $psConsumption->rate = floatval($stockLedger->cost) / floatval($psConsumption->consumption_qty);
                $psConsumption->rate = MathHelper::safeDivide(floatval($stockLedger->cost), $psConsumption->consumption_qty, 0);
                $psConsumption->save();
            }
            // return 'Success';
        }
        return $issueRecords;
    }

    /**
     * Assigns inherited Lot Numbers to Production Slip Items based on BOM inheritance rules.
     *
     * @param  object $erpProductionSlip   The ERP Production Slip object (with items relation loaded).
     * @return array                       Status and message about assignment result.
     */
    private static function assignInheritedLotNumber($erpProductionSlip)
    {
        // 🔹 Step 1: Fetch BOM Detail that allows batch inheritance
        $bomDetail = BomDetail::select('id')
            ->where('is_inherit_batch_item', 1) // Only BOM details with batch inheritance enabled
            ->where('bom_id', $erpProductionSlip->bom_id) // Belongs to current BOM
            ->first();

        // If no such BOM detail exists, return failure response
        if (!$bomDetail) {
            return [
                'status' => false,
                'message' => 'No BOM Detail found for Inherited Lot Number.'
            ];
        }

        // 🔹 Step 2: Loop through each Production Slip Item
        foreach ($erpProductionSlip->items as $item) {

            // 🔹 Step 2.1: Find BOM consumption record for this item
            $pslipBomConsumption = PslipBomConsumption::select('id')
                ->where('pslip_item_id', $item->id)
                ->where('bom_detail_id', $bomDetail->id)
                ->first();

            // Skip if no consumption record found for this item
            if (!$pslipBomConsumption) {
                continue;
            }

            // 🔹 Step 2.2: Find corresponding Stock Ledger entry (issued transaction only)
            $stockLedger = StockLedger::select('id', 'lot_number')
                ->where([
                    'book_type'          => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
                    'document_header_id' => $erpProductionSlip->id,
                    'document_detail_id' => $pslipBomConsumption->id,
                    'organization_id'    => $erpProductionSlip->organization_id,
                    'transaction_type'   => 'issue'
                ])
                ->first();

            // Skip if no stock ledger entry exists
            if (!$stockLedger) {
                continue;
            }

            // 🔹 Step 2.3: Assign lot number from stock ledger to the production slip item
            if (!empty($stockLedger->lot_number)) {
                $item->lot_number = $stockLedger->lot_number;
                $item->save();
            }
        }

        // 🔹 Step 3: Return success response after processing all items
        return [
            'status' => true,
            'message' => 'Success'
        ];
    }


    public function getItemDetail(Request $request)
    {
        $pslip_bom_cons_id = $request->pslip_bom_cons_id ?? null;
        $mo_bom_cons_id = $request->mo_bom_cons_id ?? null;
        $pslipBom = PslipBomConsumption::where('id', $pslip_bom_cons_id)->first();
        $moBom = MoBomMapping::where('id', $mo_bom_cons_id)->first();
        $data = null;
        if($pslipBom) {
            $data = $pslipBom;
        }
        if($moBom) {
            $data = $moBom;
        }
        $selectedAttr = explode(',', $request->selected_attribute_ids) ?? [];
        $item = $data?->item;
        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $html = view('productionSlip.partials.item-detail', compact('item', 'selectedAttr', 'specifications'))->render();
        return response()->json(['data' => ['html' => $html, 'status' => 200, 'message' => 'fetched.']]);
    }
    public function generatePdf(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $pslip = ErpProductionSlip::findOrFail($id);
        $specifications = collect();
        $products = collect();
        $items = collect();
        if(isset($pslip -> items) && $pslip -> items) {
            $products = $pslip -> items;
        }
        if(isset($pslip->consumptions))
        {
            $items = $pslip -> consumptions;
        }

        $totalAmount = 0;
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $approvedBy = Helper::getDocStatusUser(get_class($pslip), $pslip -> id, $pslip -> document_status);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$pslip->document_status] ?? '';
        $dynamicFields = $pslip -> dynamic_fields ?? [];
        $pdf = PDF::loadView(
        // return view(
        'pdf.pslip',
        [
            'order'=> $pslip,
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
        return $pdf->stream('ProductionSlip-' . date('Y-m-d') . '.pdf');
    }

    public function getItemAttribute(Request $request)
    {

        $itemId = $request->item_id;
        $isSo = intval($request->isSo ?? 0);
        $rowCount = intval($request->rowCount ?? 1);
        $item = Item::find($itemId);
        $selectedAttr=[];
        array_push($selectedAttr,$request->selectedAttr);
        $piItemId = $request->pi_item_id ?? null;

        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $itemAttributeArray = [];
        $hiddenHtml = '';

        foreach ($itemAttributes as $attribute) {
            $attributeIds = is_array($attribute->attribute_id)
                ? $attribute->attribute_id
                : [$attribute->attribute_id];

            $attribute->group_name = $attribute->group?->name;
            $valuesData = [];

            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = ErpAttribute::where('id', $attributeValueId)
                    ->where('status', 'active')
                    ->select('id', 'value')
                    ->first();

                if ($attributeValueData) {
                    $attributeValueData->selected = in_array($attributeValueData->id, $selectedAttr);
                    $valuesData[] = $attributeValueData;
                }
            }

            $itemAttributeArray[] = [
                'id' => $attribute->id,
                'group_name' => $attribute->group_name,
                'values_data' => $valuesData,
                'attribute_group_id' => $attribute->attribute_group_id,
            ];

            $selected = '';
            foreach ($valuesData as $value) {
                if (!empty($value->selected)) {
                    $selected = $value->id;
                }
            }

            $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value='$selected'>";
        }

        $html = view('productionSlip.partials.comp-attributes', compact('item','rowCount','selectedAttr','isSo','itemAttributes'))->render();

        return response()->json([
            'data' => ['attr' => $itemAttributes->count(),'html' => $html,'hiddenHtml' => $hiddenHtml,'itemAttributeArray' => $itemAttributeArray,],
            'status' => 200,
            'message' => 'fetched.',
        ]);
    }



    public function getAlterItems(Request $request)
    {
        $itemId = $request->item_id;
        $so_item_id = $request->so_item_id;
        $erpAlternateItems = AlternateItem::select('id','item_id','alt_item_id','item_code','item_name')
        ->with(['item:id,item_name,item_code,uom_id',
                'item.uom:id,name',
                'item.itemAttributes:id,item_id,attribute_group_id,attribute_id,required_bom'
        ])
        ->whereItemId($itemId)->get();


        if($erpAlternateItems->count()==0){
                return response()->json([
                    'data' => '',
                    'status' => 404,
                    'message' => "No alternate items available."
                ], 404);
        }

        $html = view('productionSlip.partials.alternate-item', [
            'erpAlternateItems' => $erpAlternateItems,
            'itemId' => $itemId,
            'so_item_id' => $so_item_id,
            'itemType' => $request->itemType,
            'soDoc' => $request->soDoc,
            'item_qty' => $request->item_qty,
            'mo_bom_cons_id' => $request->mo_bom_cons_id,
            'rowIndex' => $request->rowlastIndex+1
        ])->render();

        return response()->json(['data' => $html,'item'=>$erpAlternateItems, 'status' => 200, 'message' => "fetched!"]);
    }

    public function getAvlStock(Request $request){
        $itemAttributes = $request['attributes'];
        if(empty($itemAttributes)){
            $itemAttributes=[];
        }

        $storeId = $request->store_id ?? null;
        $subStoreId = $request->sub_store_id ?? null;
        $stationId = $request->station_id ?? null;
        $uom_id = $request->uom_id ?? null;
        $moBomMappingId = $request->mo_bom_mapping_id;
        $moBomMapping = MoBomMapping::find($moBomMappingId);

        if(!$moBomMapping) {
            return response()->json(['status' => 500, 'message' => "Errors: Mo Bom Mapping not found."], 500);
        }

        $rm_type = 'R';
        $itemWipStationId = null;
        if($moBomMapping->rm_type =='sf') {
            $rm_type = 'W';
            $itemWipStationId = $moBomMapping->station_id;
        }
        $soItemId = null;
        $stocks = InventoryHelper::totalInventoryAndStock(
            $request->item_id,
            $itemAttributes,
            $uom_id,
            $storeId,
            $subStoreId,
            $soItemId,
            $stationId,
            $rm_type,
            $itemWipStationId
        );

        $stockBalanceQty = 0;
        if (isset($stocks)) {
            $stockBalanceQty = $stocks['confirmedStocks'] - $stocks['reservedStocks'];
        }

        return $stockBalanceQty;
    }



}
