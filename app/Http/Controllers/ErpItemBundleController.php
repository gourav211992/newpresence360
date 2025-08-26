<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ErpBundleRequest;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper; 
use App\Helpers\ServiceParametersHelper; 
use App\Models\ErpItemBundle;
use App\Models\Item; 
use App\Models\ErpBundleItemDetail;
use App\Models\ItemAttribute;
use App\Models\Attribute;
use App\Models\Unit;
use App\Models\ErpBundleItemAttribute;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Exception;
use stdClass;



class ErpItemBundleController extends Controller
{
    /**
     * Show all bundles.
     */
 public function index(Request $request)
{
    if ($request->ajax()) {
        $itemBundles = ErpItemBundle::query()
            ->when($request->sku_code, fn($query) => $query->where('sku_code', 'LIKE', '%' . $request->sku_code . '%'))
            ->when($request->sku_name, fn($query) => $query->where('sku_name', 'LIKE', '%' . $request->sku_name . '%'))
            ->when($request->front_sku_code, fn($query) => $query->where('front_sku_code', 'LIKE', '%' . $request->front_sku_code . '%'))
            ->when($request->book_id, fn($query) => $query->where('book_id', $request->book_id))
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->when($request->document_status, fn($query) => $query->where('document_status', $request->document_status))
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($itemBundles)
            ->addIndexColumn()
            ->addColumn('actions', function ($row) {
                $editUrl = route('item-bundles.edit', $row->id);
                $deleteUrl = route('item-bundles.destroy', $row->id);
                return '<a href="' . $editUrl . '">Edit</a> | <a href="' . $deleteUrl . '">Delete</a>';
            })
            ->editColumn('document_status', function ($row) {
                $statusKey = strtolower($row->document_status ?? 'draft');
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$statusKey] ?? 'badge-light-secondary';
                $statusLabel = $row->display_status ?? 'N/A';
                return '<span class="badge rounded-pill ' . $statusClass . '">' . $statusLabel . '</span>';
            })
            ->rawColumns(['actions', 'document_status'])
            ->make(true);
    }

    return view('item-bundles.index');
}


    /**
     * Show create form.
     */
    public function create()
    {
        $itemCodeType = 'Manual';
        $parentUrl = ConstantHelper::ITEM_BUNDLE_SERVICE_ALIAS;
        $servicesInfo = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesInfo['services']) == 0) {
            return redirect()->route('/');
        }
        
        if ($servicesInfo && $servicesInfo['current_book']) {
            if (isset($servicesInfo['current_book'])) {
                $book = $servicesInfo['current_book'];
                if ($book) {
                    $parameters = new stdClass();
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->item_code_type) && is_array($parameters->item_code_type)) {
                        $itemCodeType = $parameters->item_code_type[0] ?? null;
                    }
                }
            }
        }

        return view('item-bundles.create', [
            'itemCodeType' => $itemCodeType,
        ]);
    }

    /**
     * Store bundle with spare parts.
     */
    public function store(ErpBundleRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $organization = $user->organization ?? null;

            $validatedData = $request->validated();
            $itemCodeType = 'Manual';

            if ($organization) {
                $parentUrl = ConstantHelper::ITEM_BUNDLE_SERVICE_ALIAS;
                $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

                if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
                    $firstService = $services['services']->first();
                    $serviceId = $firstService->service_id;
                    $policyData = Helper::getPolicyByServiceId($serviceId);
                    if ($policyData && isset($policyData['policyLevelData'])) {
                        $policyLevelData = $policyData['policyLevelData'];
                        $validatedData['group_id'] = $policyLevelData['group_id'];
                        $validatedData['company_id'] = $policyLevelData['company_id'];
                        $validatedData['organization_id'] = $policyLevelData['organization_id'];
                    } else {
                        $validatedData['group_id'] = $organization->group_id;
                        $validatedData['company_id'] = $organization->company_id;
                        $validatedData['organization_id'] = null;
                    }
                    if (isset($services['current_book'])) {
                        $book = $services['current_book'];
                        if ($book) {
                            $parameters = new stdClass();
                            foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                                $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                                $parameters->{$paramName} = $param;
                            }
                            if (isset($parameters->item_code_type) && is_array($parameters->item_code_type)) {
                                $itemCodeType = $parameters->item_code_type[0] ?? 'Manual';
                            }
                        }
                        $validatedData['book_id'] = $book ? $book->id : null;
                    } else {
                        $validatedData['book_id'] = null;
                    }
                } else {
                    $validatedData['group_id'] = $organization->group_id;
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
            }

            $validatedData['created_by'] = $user->id ?? null;
            $validatedData['updated_by'] = null;
            $validatedData['deleted_by'] = null;
            $validatedData['code_type'] = $itemCodeType;
            $bundle = ErpItemBundle::create($validatedData);

            if ($request->document_status === ConstantHelper::SUBMITTED) {
                $bookId = $bundle->book_id;
                $docId = $bundle->id;
                $remarks = $request->final_remarks ?? null;
                $attachments = $request->file('upload_document');
                $currentLevel = $bundle->approver_level ?? 1;
                $revisionNumber = $bundle->revision_number ?? 0;
                $actionType = 'submit';
                $modelName = get_class($bundle);
                $totalValue = 0;

                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $document_status = $approveDocument['approvalStatus'] ?? ConstantHelper::DRAFT;
                $bundle->document_status = $document_status;
                $bundle->status = (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) && $revisionNumber == 0)
                    ? ConstantHelper::ACTIVE
                    : $document_status;
            } else {
                $document_status = $request->document_status ?? ConstantHelper::DRAFT;
                $bundle->document_status = $document_status;
                $bundle->status = $document_status;
            }

            $bundle->final_remarks = $request->final_remarks ?? null;
            $bundle->save();

            // Save each bundle item.
            $bundleItems = $request->bundle_item_details ?? [];
            foreach ($bundleItems as $key => $item) {
                $bundleItem = ErpBundleItemDetail::create([
                    'bundle_id' => $bundle->id,
                    'item_id' => $item['item_id'] ?? null,
                    'item_name' => $item['item_name'] ?? null,
                    'item_code' => $item['item_code'] ?? null,
                    'uom_id' => $item['uom_id'] ?? null,
                    'qty' => $item['qty'] ?? null,
                    'hsn_id' => $item['hsn_id'] ?? null, 
                    'group_id' => $validatedData['group_id'] ?? null,
                    'company_id' => $validatedData['company_id'] ?? null,
                    'organization_id' => $validatedData['organization_id'] ?? null,
                ]);

                $attrJson = html_entity_decode($item['attributes'] ?? '[]');
                $attributes = json_decode($attrJson, true);
                if (!is_array($attributes)) $attributes = [];
                foreach($attributes as $attributeData) {
                    ErpBundleItemAttribute::create([
                        'bundle_id' => $bundle->id,
                        'bundle_item_id' => $bundleItem->id,
                        'item_attribute_id' => $attributeData['attribute_id'] ?? null,
                        'item_code' => $item['item_code'] ?? null,
                        'attribute_name' => $attributeData['group_name'] ?? null,
                        'attribute_value' => $attributeData['value_name'] ?? null,
                        'attr_name' => $attributeData['group_id'] ?? null,
                        'attr_value' => $attributeData['value_id'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $bundle,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create item bundle',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show edit form.
     */
     public function edit($id)
    {
        $bundle = ErpItemBundle::with(['bundleItems.attributes', 'attributes', 'bundleItems.hsn'])->findOrFail($id);
        $uoms = Unit::where('status', 'Active')->get();
        $itemCodeType = 'Manual';
        $parentUrl = ConstantHelper::ITEM_BUNDLE_SERVICE_ALIAS;

        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book = $services['current_book'];
                if ($book) {
                    $parameters = new stdClass();
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->item_code_type) && is_array($parameters->item_code_type)) {
                        $itemCodeType = $parameters->item_code_type[0] ?? null;
                    }
                }
            }
        }

        return view('item-bundles.edit', [
            'bundle' => $bundle,
            'uoms' => $uoms,
            'itemCodeType' => $itemCodeType, 
        ]);
    }
    /**
     * Update bundle.
     */
    public function update(ErpBundleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;

            $bundle = ErpItemBundle::findOrFail($id);
            $validatedData = $request->validated();
            $itemCodeType = 'Manual'; 

            if ($organization) {
                $parentUrl = ConstantHelper::ITEM_BUNDLE_SERVICE_ALIAS;
                $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

                if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
                    $firstService = $services['services']->first();
                    $serviceId = $firstService->service_id;
                    $policyData = Helper::getPolicyByServiceId($serviceId);

                    if ($policyData && isset($policyData['policyLevelData'])) {
                        $policyLevelData = $policyData['policyLevelData'];
                        $validatedData['group_id'] = $policyLevelData['group_id'];
                        $validatedData['company_id'] = $policyLevelData['company_id'];
                        $validatedData['organization_id'] = $policyLevelData['organization_id'];
                    } else {
                        $validatedData['group_id'] = $organization->group_id;
                        $validatedData['company_id'] = $organization->company_id;
                        $validatedData['organization_id'] = null;
                    }

                    // Item Code Type logic
                    $book = $services['current_book'] ?? null;
                    if ($book) {
                        $parameters = new stdClass();
                        foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                            $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                            $parameters->{$paramName} = $param;
                        }
                        if (isset($parameters->item_code_type) && is_array($parameters->item_code_type)) {
                            $itemCodeType = $parameters->item_code_type[0] ?? 'Manual';
                        }
                        $validatedData['book_id'] = $book->id;
                    } else {
                        $validatedData['book_id'] = null;
                    }
                } else {
                    $validatedData['group_id'] = $organization->group_id;
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                    $validatedData['book_id'] = null;
                }

                $validatedData['updated_by'] = $user->id ?? null;
                $validatedData['code_type'] = $itemCodeType;
                $bundle->update($validatedData);

                // Handle document status changes
                if ($request->document_status === ConstantHelper::SUBMITTED) {
                    $approveDocument = Helper::approveDocument(
                        $bundle->book_id,
                        $bundle->id,
                        $bundle->revision_number ?? 0,
                        $request->final_remarks ?? null,
                        $request->file('upload_document'),
                        $bundle->approver_level ?? 1,
                        'submit',
                        0,
                        get_class($bundle)
                    );

                    $document_status = $approveDocument['approvalStatus'] ?? ConstantHelper::DRAFT;
                    $bundle->document_status = $document_status;
                    $bundle->status = in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                        ? ConstantHelper::ACTIVE
                        : $document_status;
                } else {
                    $bundle->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    $bundle->status = $request->document_status ?? ConstantHelper::DRAFT;
                }

                $bundle->final_remarks = $request->final_remarks ?? null;
                $bundle->save();

                // Sync bundle items
                $newItemIds = [];
                if ($request->has('bundle_item_details') && is_array($request->bundle_item_details)) {
                    foreach ($request->bundle_item_details as $item) {
                        // 1. Update or create bundle item
                        if (isset($item['id'])) {
                            $bundleItem = ErpBundleItemDetail::find($item['id']);
                            if ($bundleItem) {
                                $bundleItem->update([
                                    'item_id' => $item['item_id'] ?? null,
                                    'item_name' => $item['item_name'] ?? null,
                                    'item_code' => $item['item_code'] ?? null,
                                    'uom_id' => $item['uom_id'] ?? null,
                                    'qty' => $item['qty'] ?? null,
                                    'hsn_id' => $item['hsn_id'] ?? null,
                                ]);
                                $newItemIds[] = $bundleItem->id;

                                // 2. Attribute update logic (no delete-all, update/create only)
                                $submittedAttrs = [];
                                $attrJson = html_entity_decode($item['attributes'] ?? '[]');
                                $attributes = json_decode($attrJson, true);
                                if (!is_array($attributes)) $attributes = [];

                                foreach ($attributes as $attr) {
                                    if (!empty($attr['id'])) {
                                        $submittedAttrs[] = $attr['id'];
                                        // Update existing attribute
                                        ErpBundleItemAttribute::where('id', $attr['id'])->update([
                                            'item_attribute_id' => $attr['attribute_id'] ?? null,
                                            'item_code'         => $item['item_code'] ?? null,
                                            'attribute_name'    => $attr['group_name'] ?? null,
                                            'attribute_value'   => $attr['value_name'] ?? null,
                                            'attr_name'         => $attr['group_id'] ?? null,
                                            'attr_value'        => $attr['value_id'] ?? null,
                                        ]);
                                    } else {
                                        // Create new attribute
                                        $newAttr = ErpBundleItemAttribute::create([
                                            'bundle_id'         => $bundle->id,
                                            'bundle_item_id'    => $bundleItem->id,
                                            'item_attribute_id' => $attr['attribute_id'] ?? null,
                                            'item_code'         => $item['item_code'] ?? null,
                                            'attribute_name'    => $attr['group_name'] ?? null,
                                            'attribute_value'   => $attr['value_name'] ?? null,
                                            'attr_name'         => $attr['group_id'] ?? null,
                                            'attr_value'        => $attr['value_id'] ?? null,
                                        ]);
                                        $submittedAttrs[] = $newAttr->id;
                                    }
                                }
                                // DELETE ONLY REMOVED ATTRIBUTES
                                if (count($submittedAttrs)) {
                                    ErpBundleItemAttribute::where('bundle_item_id', $bundleItem->id)
                                        ->whereNotIn('id', $submittedAttrs)
                                        ->delete();
                                } else {
                                    // If no attributes submitted, delete all
                                    ErpBundleItemAttribute::where('bundle_item_id', $bundleItem->id)->delete();
                                }
                            }
                        } else {
                            $bundleItem = ErpBundleItemDetail::create([
                                'bundle_id' => $bundle->id,
                                'item_id' => $item['item_id'] ?? null,
                                'item_name' => $item['item_name'] ?? null,
                                'item_code' => $item['item_code'] ?? null,
                                'uom_id' => $item['uom_id'] ?? null,
                                'qty' => $item['qty'] ?? null,
                                'hsn_id' => $item['hsn_id'] ?? null,
                                'group_id' => $validatedData['group_id'] ?? null,
                                'company_id' => $validatedData['company_id'] ?? null,
                                'organization_id' => $validatedData['organization_id'] ?? null,
                            ]);
                            $newItemIds[] = $bundleItem->id;

                            $attrJson = html_entity_decode($item['attributes'] ?? '[]');
                            $attributes = json_decode($attrJson, true);
                            if (!is_array($attributes)) $attributes = [];

                            foreach ($attributes as $attr) {
                                ErpBundleItemAttribute::create([
                                    'bundle_id'         => $bundle->id,
                                    'bundle_item_id'    => $bundleItem->id,
                                    'item_attribute_id' => $attr['attribute_id'] ?? null,
                                    'item_code'         => $item['item_code'] ?? null,
                                    'attribute_name'    => $attr['group_name'] ?? null,
                                    'attribute_value'   => $attr['value_name'] ?? null,
                                    'attr_name'         => $attr['group_id'] ?? null,
                                    'attr_value'        => $attr['value_id'] ?? null,
                                ]);
                            }
                        }
                    }
                }

                // Delete removed items (bundle items)
                $bundle->bundleItems()->whereNotIn('id', $newItemIds)->delete();

                DB::commit();
                return response()->json(['message' => 'Record updated successfully']);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update record',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Delete bundle.
     */
   public function destroyBundle($id, $isAmendment = false)
    {
        DB::beginTransaction();
        try {
            $bundle = ErpItemBundle::findOrFail($id);

            if (!$isAmendment && $bundle->document_status !== ConstantHelper::DRAFT) {
                return response()->json([
                    'status' => false,
                    'message' => 'Document cannot be deleted unless it is in draft status.',
                ], 422);
            }

            // Optional: Agar aapko revision check chahiye to uncomment karen
            // if ($bundle->revision_number) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Deletion is not allowed. The document has already been reviewed (amend).',
            //     ], 422);
            // }

            // Check dependencies if required (agar koi relation check karna ho to add karen)
            // Example:
            // if ($bundle->someRelation()->exists()) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Deletion is not allowed. It is referenced in other records.',
            //     ], 422);
            // }

            // Delete related bundle item attributes
            foreach ($bundle->attributes as $attribute) {
                $attribute->delete();
            }

            // Delete related bundle item details and their attributes
            foreach ($bundle->bundleItems as $bundleItem) {
                // Delete attributes linked to this bundle item detail
                foreach ($bundleItem->attributes as $itemAttribute) {
                    $itemAttribute->delete();
                }
                $bundleItem->delete();
            }

            // If you have files/documents linked to bundle, clear them here if method exists
            if (method_exists($bundle, 'clearExistingDocuments')) {
                $bundle->clearExistingDocuments('bundle');
            }

            // Finally delete the bundle itself
            $bundle->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item bundle deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the item bundle: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getItemAttribute(Request $request)
    {
        $rowCount     = intval($request->rowCount) ?? 1;
        $item         = Item::with('itemAttributes.attributeGroup')->find($request->item_id ?? null);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];

        $bundleDetailId = $request->bundle_item_detail_id ?? null;
        $itemAttIds     = [];

        if ($bundleDetailId) {
            $detail = ErpBundleItemDetail::find($bundleDetailId);
            if ($detail) {
                $itemAttIds = $detail->attributes()->pluck('item_attribute_id')->toArray();
            }
        }

        if (count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id', $itemAttIds)->get();
            if ($itemAttributes->isEmpty()) {
                $itemAttributes = $item?->itemAttributes;
            }
        } else {
            $itemAttributes = $item?->itemAttributes;
        }

        $oldAttributes = [];
        if ($bundleDetailId) {
            $bundleAttributes = ErpBundleItemAttribute::where('bundle_item_id', $bundleDetailId)->get();
            foreach ($bundleAttributes as $bundleAttr) {
                $attribute = $itemAttributes->firstWhere('id', $bundleAttr->item_attribute_id);
                if ($attribute) {
                    $currentIds = is_array($attribute->attribute_id)
                        ? array_map('intval', $attribute->attribute_id)
                        : array_map('intval', explode(',', $attribute->attribute_id));

                    if (!in_array($bundleAttr->attribute_value, $currentIds)) {
                        $oldAttributes[$bundleAttr->item_attribute_id] = Attribute::find($bundleAttr->attribute_value)?->value;
                    }
                }
            }
        }

        $html = view('item-bundles.partials.bundle-attribute', compact(
            'item', 'rowCount', 'selectedAttr', 'itemAttributes', 'oldAttributes'
        ))->render();

        return response()->json([
            'data' => [
                'attr' => $item->itemAttributes->count(),
                'html' => $html
            ],
            'status'  => 200,
            'message' => 'fetched.'
        ]);
    }


}
