<?php

namespace App\Http\Controllers\ReManufacturing\Scrap;

use DB;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\Unit;
use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Helpers\UserHelper;
use App\Models\ErpPslipItem;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\Scrap\ErpScrap;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use Yajra\DataTables\DataTables;
use App\Models\Scrap\ErpScrapItem;
use App\Http\Requests\ScrapRequest;
use App\Http\Controllers\Controller;
use App\Helpers\ServiceParametersHelper;
use App\Services\Scrap\ScrapDeleteService;
use App\Models\Scrap\ErpScrapItemAttribute;

class ScrapController extends Controller
{
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $selectedfyYear = Helper::getFinancialYear(Carbon::now());
            $selectColumns = ['id', 'document_date', 'document_status', 'book_id', 'store_id', 'sub_store_id', 'user_id', 'revision_number', 'document_number'];
            $scrapHeaders = ErpScrap::withDraftListingLogic()
                ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']])
                ->latest();

            return DataTables::of($scrapHeaders)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    return view('partials.action-dropdown', [
                        'statusClass' => ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-light-secondary',
                        'displayStatus' => $row->display_status,
                        'row' => $row,
                        'actions' => [
                            [
                                'url' => fn($r) => route('scrap.edit', $r->id),
                                'icon' => 'edit-3',
                                'label' => 'View/ Edit Detail',
                            ],
                        ],
                    ])->render();
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book ? $row->book?->book_name : '';
                })
                ->addColumn('store', function ($row) {
                    return $row?->store ? $row?->store?->store_name : '';
                })
                ->addColumn('sub_store', function ($row) {
                    return $row?->subStore ? $row?->subStore?->name : '';
                })
                ->addColumn('reference_from', function ($row) {
                    return $row?->reference_type ? $row?->reference_type : '';
                })
                ->addColumn('total_reference_qty', function ($row) {
                    return $row->reference_type === 'pslip'
                        ? $row?->pslipItems?->sum('rejected_qty')
                        : 0;
                })
                ->addColumn('total_qty', function ($row) {
                    return $row?->total_qty ? $row?->total_qty : '';
                })
                ->addColumn('total_cost', function ($row) {
                    return $row?->total_cost ? round($row?->total_cost, 4) : '';
                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? '';
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('components', function ($row) {
                    return $row->items->count() ?? 0;
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }
        $parentUrl = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

        return view('remanufacturing.scrap.index', ['servicesBooks' => $servicesBooks]);
    }

    public function create()
    {
        $parentUrl = request()->segments()[0];
        // $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        // if (count($servicesBooks['services']) == 0)  return redirect()->back();

        $user = Helper::getAuthenticatedUser();
        $serviceAlias = ConstantHelper::SCRAP_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now());
        $users = UserHelper::getUserSubOrdinates($user->auth_user_id ?? 0);
        $selecteduserId = $user->auth_user_id;
        $locations = InventoryHelper::getAccessibleLocations();

        return view('remanufacturing.scrap.create_edit', [
            'books' => $books,
            'users' => $users['data'],
            'locations' => $locations,
            'selecteduserId' => $selecteduserId,
            'current_financial_year' => $selectedfyYear,
        ]);
    }

    // Edit Po
    public function edit(Request $request, $id)
    {
        $parentUrl = request()->segments()[0];
        $user = Helper::getAuthenticatedUser();
        $serviceAlias = ConstantHelper::SCRAP_SERVICE_ALIAS;
        $books = Helper::getBookSeriesNew($serviceAlias, $parentUrl)->get();
        $scrap = ErpScrap::find($id);
        $createdBy = $scrap->created_by;
        $revision_number = $scrap->revision_number ?? 0;
        $creatorType = Helper::userCheck()['type'];
        $buttons = Helper::actionButtonDisplay($scrap->book_id, $scrap->document_status, $scrap->id, 0, $scrap->approval_level, $scrap->created_by ?? 0, $creatorType, $revision_number);

        $revNo = $scrap->revision_number;
        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $scrap->revision_number;
        }

        $selectedfyYear = Helper::getFinancialYear($scrap->document_date ?? Carbon::now()->format('Y-m-d'));
        $approvalHistory = Helper::getApprovalHistory($scrap->book_id, $scrap->id, $revNo, 0, $createdBy);

        $view = 'remanufacturing.scrap.create_edit';
        if ($request->has('revisionNumber') && $request->revisionNumber != $scrap->revision_number) {
            $scrap = $scrap->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'remanufacturing.scrap.view';
        }

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$scrap->document_status] ?? '';
        $departmentsData = UserHelper::getDepartments($user->auth_user_id ?? 0);
        $users = UserHelper::getUserSubOrdinates($user->auth_user_id ?? 0);
        $selecteduserId = $scrap?->user_id;
        $isEdit = $buttons['submit'];
        if (! $isEdit) {
            $isEdit = $buttons['amend'] && intval(request('amendment') ?? 0) ? true : false;
        }
        $locations = InventoryHelper::getAccessibleLocations();
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($scrap->book_id, $scrap->document_date);
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }



        return view($view, [
            'isEdit' => $isEdit,
            'books' => $books,
            'scrap' => $scrap,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revision_number' => $revision_number,
            'departments' => $departmentsData['departments'],
            'users' => $users['data'],
            'selecteduserId' => $selecteduserId,
            'locations' => $locations,
            'parameters' => $parameters,
            'current_financial_year' => $selectedfyYear,
        ]);
    }

    public function store(ScrapRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::findOrFail($user->organization_id);
            // $item_attributes = json_decode($request->item_attributes[0],  true) ?? [];

            $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (! $numberPatternData) {
                return response()->json(['message' => 'Invalid Book', 'error' => ''], 422);
            }

            $document_number = $numberPatternData['document_number'] ?? $request->document_number;
            $erpScrap = ErpScrap::where('book_id', $request->book_id)
                ->where('document_number', $document_number)
                ->first();

            /* check added */
            if ($erpScrap) {
                return response()->json(['message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER, 'error' => ''], 422);
            }

            if (!$erpScrap) {
                $erpScrap = new ErpScrap([
                    'organization_id' => $organization->id,
                    'group_id' => $organization->group_id,
                    'company_id' => $organization->company_id,
                    'user_id' => $request->user_id,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'store_id' => $request->store_id,
                    'sub_store_id' => $request->sub_store_id,
                    'remarks' => $request->document_remarks,
                    'document_date' => $request->document_date,
                    'reference_type' => $request->reference_type,
                ]);
            }

            $erpScrap->fill([
                'doc_number_type' => $numberPatternData['type'],
                'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                'doc_prefix' => $numberPatternData['prefix'],
                'doc_suffix' => $numberPatternData['suffix'],
                'doc_no' => $numberPatternData['doc_no'],
                'document_number' => $document_number,
            ]);

            $erpScrap->save();

            /** ------------------------------
             * Save Components
             * ------------------------------ */
            $components = $request->input('components', []);
            if (empty($components)) {
                DB::rollBack();
                return response()->json(['message' => 'Please add at least one row in component table.', 'error' => ''], 422);
            }

            $totalScrapQty = 0;
            $totalScrapCost = 0;
            foreach ($components as $component) {
                $item = Item::find($component['item_id'] ?? null);
                $unit = Unit::find($component['uom_id'] ?? null);

                $qty = floatval($component['qty']) ?? 0;
                $rate = floatval($component['rate']) ?? 0;
                $totalCost = floatval($component['total_cost']) ?? ($qty * $rate);

                $totalScrapQty += $qty;
                $totalScrapCost += $totalCost;

                $erpScrapItem = new ErpScrapItem([
                    'erp_scrap_id' => $erpScrap->id,
                    'item_id' => $component['item_id'],
                    'item_code' => $component['item_code'],
                    'item_name' => $component['item_name'],
                    'hsn_id' => $component['hsn_id'],
                    'hsn_code' => $component['hsn_code'],
                    'uom_id' => $component['uom_id'],
                    'uom_code' => $unit?->name,
                    'qty' => $qty,
                    'rate' => $rate,
                    'total_cost' => $totalCost,
                    'remarks' => $component['remark'],
                ]);

                $erpScrapItem->inventory_uom_code = $item?->uom?->name;
                if ($component['uom_id'] == $item?->uom_id) {
                    $erpScrapItem->inventory_uom_id = $component['uom_id'];
                    $erpScrapItem->inventory_uom_code = $component['uom_code'] ?? $item?->uom?->name;
                    $erpScrapItem->inventory_uom_qty = $component['qty'] ?? 0;
                } else {
                    $alUom = $item?->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                    $erpScrapItem->inventory_uom_id = $component['uom_id'];
                    $erpScrapItem->inventory_uom_code = $component['uom_code'] ?? $unit?->name;
                    $erpScrapItem->inventory_uom_qty = ($component['qty'] ?? 0) * ($alUom?->conversion_to_inventory ?? 1);
                }

                $erpScrapItem->save();

                foreach ($component['attr_group_id'] as $key => $value) {

                    $itemAttribute = $item?->itemAttributes()->where('attribute_group_id', $key)->first();
                    if (!$itemAttribute) continue;

                    $scrapAttr = new ErpScrapItemAttribute;
                    $scrapAttrName = $value['attr_name'];
                    $scrapAttr->erp_scrap_id = $erpScrap->id;
                    $scrapAttr->scrap_item_id = $erpScrapItem->id;
                    $scrapAttr->attribute_group_id = $itemAttribute->attribute_group_id;
                    $scrapAttr->item_attribute_id = $itemAttribute->id;
                    $scrapAttr->item_code = $component['item_code'] ?? null;
                    $scrapAttr->attribute_name = $key;
                    $scrapAttr->attribute_value = $scrapAttrName ?? null;
                    $scrapAttr->save();
                }
            }

            /** ------------------------------
             * Attachments
             * ------------------------------ */
            if ($request->hasFile('attachment')) {
                $erpScrap->uploadDocuments($request->file('attachment'), 'scrap', false);
            }

            /** ------------------------------
             * Document Status & Workflow
             * ------------------------------ */
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approveDocument = Helper::approveDocument($erpScrap->book_id, $erpScrap->id, $erpScrap->revision_number ?? 0, $erpScrap->remarks, $request->file('attachment'), $erpScrap->approval_level ?? 1, 'submit', 0, get_class($erpScrap));

                $erpScrap->document_status = $approveDocument['approvalStatus'] ?? $erpScrap->document_status;
            } else {
                $erpScrap->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            }

            $erpScrap->total_qty = $totalScrapQty;
            $erpScrap->total_cost = $totalScrapCost;
            $erpScrap->save();

            if ($erpScrap) {
                self::maintainStockLedger($erpScrap);
            }

            if ($request->reference_type) {
                switch ($request->reference_type) {
                    case 'pslip':
                        if (count($request->ps_item_ids)) {
                            ErpPslipItem::whereIn('id', $request->ps_item_ids)->update(['erp_scrap_id' => $erpScrap->id]);
                        }
                        break;
                    case 'repairOrder':
                        break;

                    default:
                        break;
                }
            }

            $redirectUrl = route('scrap.index');
            // $redirectUrl = route('scrap.edit', $erpScrap->id);

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $erpScrap,
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ScrapRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $erpScrap = ErpScrap::findOrFail($id);
            $currentStatus = $erpScrap->document_status;
            $actionType = $request->action_type;
            // $item_attributes = json_decode($request->item_attributes[0],  true) ?? [];

            if ($currentStatus == ConstantHelper::APPROVED && $actionType == 'amendment') {
                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'ErpScrap', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'ErpScrapItem', 'relation_column' => 'erp_scrap_id'],
                    ['model_type' => 'sub_detail', 'model_name' => 'ErpScrapItemAttribute', 'relation_column' => 'scrap_item_id']
                ];
                Helper::documentAmendment($revisionData, $id);
            }

            $deletedData = [
                'deletedPsItemIds' => json_decode($request->input('deleted_ps_item_ids', '[]'), true),
                'deletedErpScrapItemIds' => json_decode($request->input('deleted_scrap_item_ids', '[]'), true),
                'deletedAttachmentIds' => json_decode($request->input('deleted_attachment_ids', '[]'), true),
            ];

            if (!empty($deletedData['deletedPsItemIds'])) {
                $erpScrapDeleteService = new ScrapDeleteService();
                $response = $erpScrapDeleteService->removePsMapping($deletedData['deletedPsItemIds'], $erpScrap);
                if (isset($response['error'])) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error occurred while updating the record.',
                        'error' => $response['error'],
                    ], 500);
                }
            }

            if (!empty($deletedData['deletedAttachmentIds'])) {
                $erpScrapDeleteService = new ScrapDeleteService();
                $response = $erpScrapDeleteService->deleteAttachments($deletedData['deletedAttachmentIds'], $erpScrap);
                if ($response['status'] == 'error') {
                    DB::rollBack();
                    return response()->json([
                        'message' => $response['message'],
                        'error' => $response['status'],
                    ], 500);
                }
            }

            if (!empty($deletedData['deletedErpScrapItemIds'])) {
                $erpScrapDeleteService = new ScrapDeleteService();
                $response = $erpScrapDeleteService->deleteScrapItems($deletedData['deletedErpScrapItemIds'], $erpScrap);

                if ($response['status'] == 'error') {
                    DB::rollBack();
                    return response()->json([
                        'message' => $response['message'],
                        'error' => $response['status'],
                    ], 500);
                }
            }

            // Update header
            $erpScrap->document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $erpScrap->reference_type = $request->reference_type ?? $erpScrap->reference_type;
            $erpScrap->book_id = $request->book_id ?? $erpScrap->book_id;
            $erpScrap->book_code = $request->book_code ?? $erpScrap->book_code;
            $erpScrap->store_id = $request->store_id ?? $erpScrap->store_id;
            $erpScrap->sub_store_id = $request->sub_store_id ?? $erpScrap->sub_store_id;
            $erpScrap->remarks = $request->document_remarks ?? $erpScrap->document_remarks;
            $erpScrap->document_date = $request->document_date ?? $erpScrap->document_date;
            $erpScrap->reference_type = $request->reference_type ?? $erpScrap->reference_type;
            $erpScrap->save();

            $totalScrapQty = 0;
            $totalScrapCost = 0;

            // Update components
            $components = $request->input('components', []);
            foreach ($components as $component) {
                $item = Item::find($component['item_id'] ?? null);
                $unit = Unit::find($component['uom_id']);
                $erpScrapItem = ErpScrapItem::find($component['scrap_item_id'] ?? null) ?? new ErpScrapItem;

                $qty = floatval($component['qty']) ?? 0;
                $rate = floatval($component['rate']) ?? 0;
                $totalCost = floatval($component['total_cost']) ?? ($qty * $rate);

                $totalScrapQty += $qty;
                $totalScrapCost += $totalCost;

                $erpScrapItem->fill([
                    'erp_scrap_id' => $erpScrap->id,
                    'item_id' => $component['item_id'],
                    'item_code' => $component['item_code'],
                    'item_name' => $component['item_name'],
                    'hsn_id' => $component['hsn_id'],
                    'hsn_code' => $component['hsn_code'],
                    'uom_id' => $component['uom_id'],
                    'uom_code' => $unit?->name,
                    'qty' => $qty,
                    'rate' => $rate,
                    'total_cost' => $totalCost,
                    'remarks' => $component['remark'],
                ]);

                $erpScrapItem->inventory_uom_code = $item?->uom?->name;
                if ($component['uom_id'] == $item?->uom_id) {
                    $erpScrapItem->inventory_uom_id = $component['uom_id'];
                    $erpScrapItem->inventory_uom_code = $component['uom_code'] ?? $item?->uom?->name;
                    $erpScrapItem->inventory_uom_qty = $component['qty'] ?? 0;
                } else {
                    $alUom = $item?->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                    $erpScrapItem->inventory_uom_id = $component['uom_id'];
                    $erpScrapItem->inventory_uom_code = $component['uom_code'] ?? $unit?->name;
                    $erpScrapItem->inventory_uom_qty = ($component['qty'] ?? 0) * ($alUom?->conversion_to_inventory ?? 1);
                }

                $erpScrapItem->save();

                foreach ($component['attr_group_id'] as $key => $value) {
                    $itemAttribute = $item?->itemAttributes()->where('attribute_group_id', $key)->first();
                    if (!$itemAttribute) continue;

                    $scrapAttr = ErpScrapItemAttribute::firstOrNew([
                        'erp_scrap_id' => $erpScrap->id,
                        'scrap_item_id' => $erpScrapItem->id,
                        'item_attribute_id' => $itemAttribute->id,
                        'attribute_group_id' => $itemAttribute->attribute_group_id,
                    ]);

                    $scrapAttrName = $value['attr_name'];
                    $scrapAttr->item_code = $component['item_code'] ?? null;
                    $scrapAttr->attribute_name = $key;
                    $scrapAttr->attribute_value = $scrapAttrName ?? null;
                    $scrapAttr->save();
                }
            }

            if ($request->hasFile('attachment')) {
                $erpScrap->uploadDocuments($request->file('attachment'), 'erpScrap', false);
            }

            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $approveDocument = Helper::approveDocument(
                    $erpScrap->book_id,
                    $erpScrap->id,
                    $erpScrap->revision_number ?? 0,
                    $erpScrap->remarks,
                    $request->file('attachment'),
                    $erpScrap->approval_level ?? 1,
                    'submit',
                    0,
                    get_class($erpScrap)
                );
                $erpScrap->document_status = $approveDocument['approvalStatus'] ?? $erpScrap->document_status;
            }

            $erpScrap->total_qty = $totalScrapQty;
            $erpScrap->total_cost = $totalScrapCost;
            $erpScrap->save();

            if ($erpScrap) {
                self::maintainStockLedger($erpScrap);
            }

            if ($request->reference_type) {
                $pullItemIds =  $request->ps_item_ids ? json_decode($request->ps_item_ids, true) : [0];
                $erpScrap->pslipItems()->update(['erp_scrap_id' => null]);
                switch ($request->reference_type) {
                    case 'pslip':
                        if (count($pullItemIds)) {
                            ErpPslipItem::whereIn('id', $pullItemIds)->update(['erp_scrap_id' => $erpScrap->id]);
                        }
                        break;
                    case 'repairOrder':
                        break;

                    default:
                        break;
                }
            }

            $redirectUrl = route('scrap.index');

            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $erpScrap,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while updating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateApprove(ScrapRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $scrap = ErpScrap::findOrFail($id);
            $actionType = $request->action_type;

            // Update qty/uom for components
            foreach ($request->input('components', []) as $component) {
                $item = Item::find($component['item_id'] ?? null);
                $unit = Unit::find($component['uom_id']);
                $erpScrapItem = ErpScrapItem::find($component['scrap_item_id'] ?? null);

                if ($erpScrapItem) {
                    $erpScrapItem->qty = $component['qty'] ?? 0.00;

                    if ($component['uom_id'] == $item?->uom_id) {
                        $erpScrapItem->inventory_uom_qty = $component['qty'];
                    } else {
                        $alUom = $item?->alternateUOMs()->where('uom_id', $component['uom_id'])->first();
                        $erpScrapItem->inventory_uom_qty = floatval($component['qty']) * ($alUom?->conversion_to_inventory ?? 1);
                    }

                    $erpScrapItem->save();
                }
            }

            // Workflow approval
            $approveDocument = Helper::approveDocument(
                $scrap->book_id,
                $scrap->id,
                $scrap->revision_number ?? 0,
                $request->remarks,
                $request->file('attachment'),
                $scrap->approval_level,
                $actionType,
                0,
                get_class($scrap)
            );

            $scrap->approval_level = $approveDocument['nextLevel'];
            $scrap->document_status = $approveDocument['approvalStatus'];
            $scrap->save();

            DB::commit();
            return response()->json([
                'message' => 'Record approved successfully',
                'data' => $scrap,
                'redirect_url' => ''
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while approving the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getItemDetail(Request $request)
    {
        $tab = $request->tab ?? null;
        // get the tab name
        $itemId = $request->item_id;
        $storeId = $request->store_id;
        $subStoreId = $request->sub_store_id;
        $selectedAttr = json_decode($request->selectedAttr, true) ?? [];

        $item = Item::find($itemId);
        if (! $item) {
            return response()->json([
                'status' => 404,
                'message' => 'Item not found',
                'data' => [],
            ]);
        }

        /* Attribute handling */
        $attributeName = [];
        $attributeValue = [];
        foreach ($item->itemAttributes as $attribute) {
            $attributeGroupId = $attribute->attribute_group_id ?? null;
            $attributeIds = $attribute->attribute_id ?? [];

            if (! is_array($attributeIds)) {
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

        /* Qty & UOM */
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        $uomName = $item->uom->name ?? 'NA';

        if ($item->uom_id != $uomId && $uomId) {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
        }

        /* Extra Data */
        $remark = $request->remark ?? null;
        $specifications = $item->specifications()->whereNotNull('value')->get();
        $scrapItemIds = $request->scrap_item_id ? [$request->scrap_item_id] : [];
        $inventoryStock = InventoryHelper::totalInventoryAndStock($item->id, $selectedAttr, $item?->uom_id, $storeId, $subStoreId);

        switch ($tab) {
            case 'scavenging':
                $view = 'remanufacturing.scrap.partials.comp-item-detail';
                break;
            case 'repairOrder':
                $view = 'remanufacturing.scrap.partials.comp-item-detail';
                break;
            case 'productionSlip':
                $view = 'remanufacturing.scrap.partials.comp-item-detail';
                break;
            default:
                $view = 'remanufacturing.scrap.partials.comp-item-detail';
                break;
        }

        $html = view($view, compact(
            'item',
            'selectedAttr',
            'remark',
            'uomName',
            'qty',
            'specifications',
            'inventoryStock',
            'itemId',
            'storeId',
            'subStoreId',
            'attributes'
        ))->render();

        return response()->json([
            'data' => [
                'html' => $html,
                'inventoryStock' => $inventoryStock,
            ],
            'status' => 200,
            'message' => 'Fetched successfully',
        ]);
    }

    public function getPs(Request $request)
    {
        $query = $this->buildPsQuery($request);

        return DataTables::of($query)
            ->addColumn('select_checkbox', function ($row) {
                return '<div class="form-check form-check-inline me-0">
                            <input class="form-check-input ps_item_checkbox"
                                type="checkbox"
                                name="ps_item_check[]"

                                data-item-id="' . e($row?->item_id) . '"
                                value="' . e($row?->id) . '">
                        </div>';
            })
            ->addColumn('book_name', fn($row) => $row?->pslip?->book?->book_code ?? '')
            ->addColumn('doc_no', fn($row) => $row?->pslip?->document_number ?? '')
            ->addColumn('doc_date', fn($row) => $row?->pslip?->getFormattedDate('document_date') ?? '')
            ->addColumn('item_name', fn($row) => $row?->item?->item_name ?? '')
            ->addColumn('item_code', fn($row) => $row?->item?->item_code ?? '')
            ->addColumn('attributes', fn($row) => app(\App\View\Components\Po\Attribute::class, ['row' => $row])->resolveView()->render())
            ->addColumn('uom', fn($row) => $row?->uom?->name ?? '')
            ->addColumn('rejected_qty', fn($row) => number_format($row?->rejected_qty ?? 0, 2))
            ->addColumn('remarks', fn($row) => $row?->remarks ?? '')
            ->rawColumns([
                'select_checkbox',
                'attributes',
            ])
            ->make(true);
    }

    protected function buildPsQuery(Request $request)
    {
        $storeId = $request->store_id ?? null;
        $pslipId = $request->pslip_id ?? null;
        $seriesId = $request->series_id ?? null;
        $itemSearch = $request->item_search ?? null;
        $subStoreId = $request->sub_store_id ?? null;
        $headerBookId = $request->header_book_id ?? null;
        $ps_item_ids = json_decode($request->ps_item_ids, true) ?? [];
        $selected_ps_item_ids = json_decode($request->selected_ps_item_ids, true) ?? [];
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($headerBookId);

        $ErpPslipItem = ErpPslipItem::where(function ($query) use ($applicableBookIds, $selected_ps_item_ids, $ps_item_ids, $itemSearch, $storeId, $subStoreId, $pslipId) {
            if (count($selected_ps_item_ids)) {
                $query->whereNotIn('id', $selected_ps_item_ids);
            }

            if (count($ps_item_ids)) {
                $query->whereIn('id', $ps_item_ids);
            }

            $query->whereHas('pslip', function ($pslip) use ($applicableBookIds, $storeId, $subStoreId, $pslipId) {

                $pslip->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]);

                // if ($seriesId) {
                //     $pslip->where('book_id', $seriesId);
                // }

                if (count($applicableBookIds)) {
                    $pslip->whereIn('book_id', $applicableBookIds);
                }
                if ($storeId) {
                    $pslip->where('store_id', $storeId);
                }
                if ($subStoreId) {
                    $pslip->where('rg_sub_store_id', $subStoreId);
                }
                if ($pslipId) {
                    $pslip->where('id', $pslipId);
                }
            });

            if ($itemSearch) {
                $query->whereHas('item', function ($query) use ($itemSearch) {
                    $query->searchByKeywords($itemSearch);
                });
            }

            $query->whereNull('erp_scrap_id');
            $query->whereRaw('rejected_qty > 0');
        });

        return $ErpPslipItem;
    }

    // Add item row
    public function addItemRow(Request $request)
    {
        $item = json_decode($request->item, true) ?? [];
        $componentItem = json_decode($request->component_item, true) ?? [];

        /* Check last tr in table mandatory */
        if (isset($componentItem['attr_require']) && isset($componentItem['item_id']) && isset($componentItem['count'])) {
            if (($componentItem['attr_require'] == true || ! $componentItem['item_id']) && $componentItem['count'] != 0) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }

        $rowCount = intval($request->count);
        $html = view('remanufacturing.scrap.partials.item-row', compact('rowCount'))->render();

        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    // On change item attribute
    public function getItemAttribute(Request $request)
    {
        $hiddenHtml = '';
        $itemAttIds = [];
        $itemAttributeArray = [];
        $mode = $request->mode ?? 'edit';
        $item = Item::find($request->item_id);
        $rowCount = intval($request->rowCount) ?? 1;
        $scrapItemId = $request->scrap_item_id ?? null;
        $requestHeader = $request->type ? $request->type . '_components' : 'components';
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];
        $addHiddenInputs = $request->hidden_inputs && $request->hidden_inputs == true ? true : false;

        if ($scrapItemId) {
            $scrapItem = ErpPslipItem::where('id', $scrapItemId)->where('item_id', $item->id ?? null)->first();
            if ($scrapItem) {
                $itemAttIds = $scrapItem->attributes()->pluck('item_attribute_id')->toArray();
                $itemAttributeArray = $scrapItem->item_attributes_array();
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

        $html = view('remanufacturing.scrap.partials.comp-attribute', compact('item', 'rowCount', 'selectedAttr', 'mode', 'requestHeader', 'itemAttributes'))->render();

        foreach ($itemAttributes as $attribute) {
            $selected = '';
            foreach ($attribute->attributes() as $value) {
                if (in_array($value->id, $selectedAttr)) {
                    $selected = $value->id;
                }
            }
            $hiddenHtml .= "<input type='hidden' name='" . $requestHeader . "[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
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

    public function processItem(Request $request)
    {
        $html = '';
        $type = $request->type ?? null;
        $ids = json_decode($request->ids, true) ?? [];

        if ($type == 'pslip') {
            $items = ErpPslipItem::whereIn('id', $ids)->get();
        }

        $current_row_count = intval($request->current_row_count);

        foreach ($items as $item) {
            $html .= view('remanufacturing.scrap.partials.pull-items', [
                'type' => $type,
                'item' => $item,
                'rowCount' => $current_row_count++,
            ])->render();
        }

        return response()->json([
            'data' => ['pos' => $html],
            'status' => 200,
            'message' => 'fetched!'
        ]);
    }

    public function destroy($erpScrapId, $isAmedment = false)
    {
        $erpScrap = ErpScrap::find($erpScrapId);
        if (!$erpScrap) {
            return response()->json(['status' => false, 'message' => 'Production Slip not found.'], 404);
        }

        if (!$isAmedment && $erpScrap->document_status !== ConstantHelper::DRAFT) {
            return response()->json(['status' => false, 'message' => 'Only draft documents can be deleted.'], 422);
        }

        \DB::beginTransaction();
        try {

            $erpScrapDeleteService = new ScrapDeleteService();
            $response = $erpScrapDeleteService->deleteScrapHeader($erpScrap);

            if ($response['status'] === 'error') {
                \DB::rollBack();
                return response()->json(['status' => false, 'message' => $response['message']], 422);
            }

            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Document deleted successfully.'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Error deleting Production Slip: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $erpScrap = ErpScrap::find($request->id);
            if (isset($erpScrap)) {
                $revoke = Helper::approveDocument($erpScrap->book_id, $erpScrap->id, $erpScrap->revision_number, '', [], 0, ConstantHelper::REVOKE, $erpScrap->grand_total_amount, get_class($erpScrap));
                if ($revoke['message']) {
                    DB::rollBack();

                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $erpScrap->document_status = $revoke['approvalStatus'];
                    $erpScrap->save();
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

    // Maintain Stock Ledger
    private static function maintainStockLedger($scrap)
    {
        $user = Helper::getAuthenticatedUser();
        $detailIds = $scrap->items->pluck('id')->toArray();

        $data = InventoryHelper::settlementOfInventoryAndStock($scrap->id, $detailIds, ConstantHelper::SCRAP_SERVICE_ALIAS, $scrap->document_status, 'receipt');
        return $data;
    }
}
