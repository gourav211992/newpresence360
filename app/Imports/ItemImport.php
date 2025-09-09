<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\UploadItemMaster;
use App\Models\ItemSubType;
use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Services\ItemImportExportService;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ToCollection;
use Exception;
use stdClass;

class ItemImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;
    protected $user;

    public function chunkSize(): int
    {
        return 100;
    }

    public function __construct(ItemImportExportService $service, $user)
    {
        $this->user = $user;
        $this->service = $service;
    }


    public function onSuccess($row)
    {
        $this->successfulItems[] = [
            'item_code' => $row->item_code,
            'item_name' => $row->item_name,
            'uom' => $row->uom ? $row->uom : 'N/A',
            'hsn' => $row->hsn ?  $row->hsn : 'N/A',
            'type' => $row->type,
            'sub_type' => $row->sub_type,
            'status' => 'success',
            'item_remark' => $row->remarks,
        ];
    }

    public function onFailure($uploadedItem)
    {
        $errorDetails = $uploadedItem->remarks;
        if (is_array($errorDetails)) {
            $errorDetails = implode(', ', $errorDetails);
        }
        $this->failedItems[] = [
            'item_code' => $uploadedItem->item_code,
            'item_name' => $uploadedItem->item_name,
            'uom' => $uploadedItem->uom ? $uploadedItem->uom : 'N/A',
            'hsn' => $uploadedItem->hsn ? $uploadedItem->hsn : 'N/A',
            'type' => $uploadedItem->type,
            'sub_type' => $uploadedItem->sub_type,
            'status' => 'failed',
            'remarks' => 'Failed to import item: ' . $errorDetails,
        ];
    }

    public function getSuccessfulItems()
    {
        return $this->successfulItems;
    }

    public function getFailedItems()
    {
        return $this->failedItems;
    }

    protected function getServiceData($organization, $services)
    {
        $validatedData = [];
        $itemCodeType = 'Manual';

        if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId, $this->user);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                $validatedData['company_id'] = $policyLevelData['company_id'] ?? null;
                $validatedData['organization_id'] = $policyLevelData['organization_id'] ?? null;
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        if ($services && isset($services['current_book'])) {
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

        return [
            'validatedData' => $validatedData,
            'itemCodeType' => $itemCodeType,
        ];
    }


   public function collection($rows)
    {
        if (empty($rows) || count($rows) == 0) {
            return;
        }
        $user = $this->user ?: Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $batchNo = $this->service->generateBatchNo($organization->id, $organization->group_id, $organization->company_id, $user->id);
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);
        $serviceData = $this->getServiceData($organization, $services);
        $validatedData = $serviceData['validatedData'];
        $itemCodeType = $serviceData['itemCodeType'];

        $uploadedItems = [];
        $itemsToProcess = [];

        foreach ($rows as $row) {
            if (collect($row)->filter()->isEmpty()) {
                continue;
            }
            $uploadedItem = null;
            $errorMessages = [];
            $itemCode = null;
            $subCategory = null;
            $skipRow = false;

            try {
                $attributes = [];
                for ($i = 1; $i <= 10; $i++) {
                    if (isset($row["attribute_{$i}_name"])) {
                        $attributeName = $row["attribute_{$i}_name"];
                        $attributeValue = $row["attribute_{$i}_value"];
                        $requiredBom = $row["attribute_{$i}_bom_required"] ?? null;
                        $allChecked = 0;
                        if ($attributeName) {
                            if (empty($attributeValue)) {
                                $allChecked = 1;
                            } else {
                                $allChecked = ($row["attribute_{$i}_all_checked"] ?? 'N') === 'Y' ? 1 : 0;
                            }
                        }
                        if ($attributeName) {
                            $attributes[] = [
                                'name' => $attributeName,
                                'value' => $attributeValue,
                                'required_bom' => $requiredBom,
                                'all_checked' => $allChecked,
                            ];
                        }
                    }
                }

                $specifications = [];
                $specificationGroupName = $row['product_specification_group'] ?? 'Specification';
                $specsArr = [];
                for ($i = 1; $i <= 10; $i++) {
                    $specName = $row["specification_{$i}_name"] ?? null;
                    $specValue = $row["specification_{$i}_value"] ?? null;
                    if (isset($specValue) && $specValue !== '') {
                        $specsArr[] = [
                            'name' => $specName,
                            'value' => $specValue
                        ];
                    }
                }
                if (!empty($specsArr)) {
                    $specifications[] = [
                        'group_name' => $specificationGroupName,
                        'specifications' => $specsArr
                    ];
                }

                $alternateUoms = [];
                for ($i = 1; $i <= 10; $i++) {
                    if (isset($row["alternate_uom_{$i}"]) && isset($row["alternate_uom_{$i}_conversion"])) {
                        $alternateUoms[] = [
                            'uom' => $row["alternate_uom_{$i}"],
                            'conversion' => $row["alternate_uom_{$i}_conversion"],
                            'cost_price' => $row["alternate_uom_{$i}_cost_price"] ?? null,
                            'sell_price' => $row["alternate_uom_{$i}_sell_price"] ?? null,
                            'default' => $row["alternate_uom_{$i}_default"] ?? null,
                        ];
                    }
                }
                $subCategoryInitials = '';
                $itemName = $row['item_name'] ?? '';
                $cleanedItemName = preg_replace('/[^a-zA-Z0-9\s]/', '', $itemName);
                $words = preg_split('/\s+/', trim($cleanedItemName));
                $words = array_filter($words, fn($word) => strlen($word) > 0);

                if (count($words) === 1) {
                    $itemInitials = strtoupper(substr($words[0], 0, 3));
                } elseif (count($words) === 2) {
                    $itemInitials = strtoupper(substr($words[0], 0, 2) . substr($words[1], 0, 1));
                } elseif (count($words) >= 3) {
                    $itemInitials = strtoupper($words[0][0] . $words[1][0] . $words[2][0]);
                }
                $itemInitials = substr($itemInitials, 0, 3);
                $subTypeRaw = $row['sub_type'] ?? null;
                $subType = $subTypeRaw ? explode(',', $subTypeRaw) : [];
                $itemType = ($row['type'] === 'G') ? 'Goods' : (($row['type'] === 'S') ? 'Service' : 'Goods');

                if ($itemType === 'Goods' && empty($subType)) {
                    $errorMessages[] = "Sub Type is required .";
                    $skipRow = true;
                }

                $isTradedItem = 0;
                $isAsset = 0;
                $isScrap = 0;


                if (!empty($subTypeRaw)) {
                    try {
                        $subTypes = array_map('trim', explode(',', $subTypeRaw));
                        $subTypeData = $this->service->getSubTypeId($subTypes);
                        $isTradedItem = $subTypeData['is_traded_item'] ?? 0;
                        $isAsset = $subTypeData['is_asset'] ?? 0;
                        $isScrap      = $subTypeData['is_scrap'] ?? 0;
                    } catch (Exception $e) {
                        $errorMessages[] = $e->getMessage();
                        $skipRow = true;
                    }
                }

                 // Apply asset validation only when the type is 'G' (Goods)
                if ($itemType === 'Goods' && $isAsset == 1) {
                    $assetCategory = $row['asset_category'] ?? null;

                    $brandName = $row['brand'] ?? null;
                    $modelNo = $row['model_no'] ?? null;

                    if (empty($assetCategory)) {
                        $errorMessages[] = "Asset Category is required when item is marked as an asset.";
                        $skipRow = true;
                    }
                    if (empty($brandName)) {
                        $errorMessages[] = "Brand Name is required when item is marked as an asset.";
                        $skipRow = true;
                    }
                    if (empty($modelNo)) {
                        $errorMessages[] = "Model No. is required when item is marked as an asset.";
                        $skipRow = true;
                    }
                }

                if ($itemCodeType === 'Manual') {
                    $itemCode = isset($row['item_code']) && !empty($row['item_code']) ? $row['item_code'] : null;
                } elseif ($itemCodeType === 'Auto') {
                    try {
                        if (!$skipRow) {
                            $subCategory = $this->service->getSubCategory($row['group']);

                            if ($subCategory) {
                                if ($subCategory->sub_cat_initials) {
                                    $subCategoryInitials = $subCategory->sub_cat_initials;
                                } elseif ($subCategory->cat_initials) {
                                    $subCategoryInitials = $subCategory->cat_initials;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $errorMessages[] = "Error fetching category: " . $e->getMessage();
                        Log::error("Error fetching category: " . $e->getMessage());
                        $skipRow = true;
                    }

                    if (!$skipRow && !empty($subType) && !empty($itemInitials) && !empty($subCategoryInitials)) {
                        $itemCode = $this->service->generateItemCode($subType, $subCategoryInitials, $itemInitials);
                    }
                }

                if (($row['is_inspection'] ?? 'N') === 'Y' && empty($row['inspection_checklist'])) {
                    $errorMessages[] = "Inspection Checklist is required when Inspection is Yes.";
                    $skipRow = true;
                }

                $isExpiry = ($row['is_expiry'] ?? 'N') === 'Y';
                $shelfLifeDays = $row['shelf_life_days'] ?? null;

                if ($isExpiry && empty($shelfLifeDays)) {
                    $errorMessages[] = "Shelf Life Days is required when item has expiry.";
                    $skipRow = true;
                }

                if ($skipRow) {
                    $this->onFailure((object)[
                        'item_code' => $row['item_code'] ?? null,
                        'item_name' => $row['item_name'] ?? null,
                        'uom' => $row['inventory_uom'] ?? null,
                        'hsn' => $row['hsnsac'] ?? null,
                        'type' => $row['type'] ?? null,
                        'sub_type' => $row['sub_type'] ?? null,
                        'remarks' => implode(', ', $errorMessages),
                        'status' => 'Failed',
                    ]);
                    continue;
                }


                if ($itemType === 'Service') {
                    $attributes = [];
                    $alternateUoms = [];
                }

                $subTypeValue = ($itemType === 'Goods') ? ($row['sub_type'] ?? null) : null;


                $uploadedItem = UploadItemMaster::create([
                    'item_name' => $row['item_name'] ?? null,
                    'item_code' => $itemCode,
                    'item_code_type' => $itemCodeType,
                    'subcategory' => $row['group'] ?? null,
                    'hsn' => $row['hsnsac'] ?? null,
                    'uom' => $row['inventory_uom'] ?? null,
                    'cost_price' => $row['cost_price'] ?? null,
                    'cost_price_currency' => $row['cost_price_currency'] ?? null,
                    'sell_price' => $row['sale_price'] ?? null,
                    'sell_price_currency' => $row['sell_price_currency'] ?? null,
                    'type' => $itemType,
                    'status' => 'Processed',
                    'group_id' => $validatedData['group_id'],
                    'company_id' => $validatedData['company_id'],
                    'organization_id' => $validatedData['organization_id'],
                    'sub_type' => $subTypeValue,
                    'is_traded_item' => $isTradedItem,
                    'is_asset' => $isAsset,
                    'is_scrap' => $isScrap,
                    'asset_category_id' => $row['asset_category'] ?? null,
                    'brand_name' => $row['brand'] ?? null,
                    'model_no' => $row['model_no'] ?? null,
                    'remarks' => "Processing item upload",
                    'batch_no' => $batchNo,
                    'user_id' => $user->auth_user_id,
                    'min_stocking_level' => $row['min_stocking_level'] ?? null,
                    'max_stocking_level' => $row['max_stocking_level'] ?? null,
                    'reorder_level' => $row['reorder_level'] ?? null,
                    'min_order_qty' => $row['min_order_qty'] ?? null,
                    'lead_days' => $row['lead_days'] ?? null,
                    'safety_days' => $row['safety_days'] ?? null,
                    'shelf_life_days' => $row['shelf_life_days'] ?? null,
                    'po_positive_tolerance' => $row['po_positive_tolerance'] ?? null,
                    'po_negative_tolerance' => $row['po_negative_tolerance'] ?? null,
                    'so_positive_tolerance' => $row['so_positive_tolerance'] ?? null,
                    'so_negative_tolerance' => $row['so_negative_tolerance'] ?? null,
                    'is_serial_no' => ($row['is_serial_no'] ?? 'N') === 'Y' ? 1 : 0,
                    'is_batch_no' => ($row['is_batch_no'] ?? 'N') === 'Y' ? 1 : 0,
                    'is_expiry' => $isExpiry ? 1 : 0,
                    'is_inspection' => ($row['is_inspection'] ?? 'N') === 'Y' ? 1 : 0,
                    'inspection_checklist' => $row['inspection_checklist'] ?? null,
                    'storage_uom' => $row['storage_uom'] ?? null,
                    'storage_uom_conversion' => $row['storage_uom_conversion'] ?? null,
                    'storage_uom_count' => $row['storage_uom_count'] ?? null,
                    'storage_weight' => $row['storage_weight'] ?? null,
                    'storage_volume' => $row['storage_volume'] ?? null,
                    'attributes' => json_encode($attributes),
                    'specifications' => json_encode($specifications),
                    'alternate_uoms' => json_encode($alternateUoms),
                ]);
                if ($uploadedItem) {
                    $uploadedItems[] = $uploadedItem;
                    $itemsToProcess[] = $uploadedItem;
                }
            } catch (Exception $e) {
                $errorMessages[] = "Error creating UploadItemMaster: " . $e->getMessage();
                Log::error("Error creating UploadItemMaster: " . $e->getMessage(), [
                    'error' => $e,
                    'row' => $row
                ]);
                $this->onFailure((object)[
                    'item_code' => $row['item_code'] ?? null,
                    'item_name' => $row['item_name'] ?? null,
                    'uom' => $row['inventory_uom'] ?? null,
                    'hsn' => $row['hsnsac'] ?? null,
                    'type' => $row['type'] ?? null,
                    'sub_type' => $row['sub_type'] ?? null,
                    'remarks' => implode(', ', $errorMessages),
                    'status' => 'Failed',
                ]);
            }
        }

        if (!empty($itemsToProcess)) {
            $this->processItemFromUpload($itemsToProcess);
        }
    }

    private function processItemFromUpload($uploadedItems)
    {

        $user = $this->user ?: Helper::getAuthenticatedUser();
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);
        $bookId = null;
        if ($services && isset($services['current_book'])) {
            $book = $services['current_book'];
            if ($book) {
                $bookId = $book->id;
            }
        }
        $actionType = 'submit';
        $currentLevel = 1;
        $revisionNumber = 0;
        $modelName = 'App\\Models\\Item';
        $totalValue = 0;
        $remarks = null;
        $attachments = null;

        foreach ($uploadedItems as $uploadedItem) {
            $errors = [];
            $subTypeId = null;
            $hsnCodeId = null;
            $category=null;
            $subCategory = null;
            $uomId = null;
            $currencyId = null;
            $attributes = [];
            $specifications = [];
            $alternateUoms = [];
            $isTradedItem = 0;
            $isAsset = 0;
            $assetCategoryId = null;
            $expectedLife = null;
            $maintenanceSchedule = null;
            $storageUomId = $uomId; 
            $storageUomConversion = 1; 
            $storageUomCount = 1; 
            $inspectionChecklistId = null;
            if (!empty($uploadedItem->subcategory)) {
                try {
                    $subCategory = $this->service->getSubCategory($uploadedItem->subcategory);
                } catch (Exception $e) {
                    $errors[] = "Error fetching category: " . $e->getMessage();
                }
            }
            if (!empty($uploadedItem->hsn)) {
                try {
                    $hsnCodeId = $this->service->getHSNCode($uploadedItem->hsn);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            if (!empty($uploadedItem->uom)) {
                try {
                    $uomId = $this->service->getUomId($uploadedItem->uom);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            if (!empty($uploadedItem->cost_price_currency)) {
                try {
                    $costPriceCurrencyId= $this->service->getCurrencyId($uploadedItem->cost_price_currency);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            if (!empty($uploadedItem->sell_price_currency)) {
                try {
                    $sellPriceCurrencyId = $this->service->getCurrencyId($uploadedItem->sell_price_currency);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!empty($uploadedItem->sub_type)) {
                try {
                    $subTypes = array_map('trim', explode(',', $uploadedItem->sub_type));
                    $subTypeData = $this->service->getSubTypeId($subTypes);
                    $subTypeId = $subTypeData['sub_type_id'] ?? null;
                    $isTradedItem = $subTypeData['is_traded_item'] ?? 0;
                    $isAsset = $subTypeData['is_asset'] ?? 0;
                    $isScrap = $subTypeData['is_scrap'] ?? 0;
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

              if ($isAsset == 1) {
                try {
                    $assetDetails = $this->service->getAssetCategoryDetailsByName($uploadedItem->asset_category_id);
                    $assetCategoryId = $assetDetails['asset_category_id'];
                    $expectedLife = $assetDetails['expected_life_years'];
                    $maintenanceSchedule = $assetDetails['maintenance_schedule'];
                } catch (Exception $e) {
                    $errors[] = "Asset Category Error: " . $e->getMessage();
                }
            }

            if ($isAsset == 1) {
                if (empty($uploadedItem->brand_name)) {
                    $errors[] = "Brand Name is required for assets";
                }
                if (empty($uploadedItem->model_no)) {
                    $errors[] = "Model No. is required for assets";
                }
            }

            if (($uploadedItem->is_inspection ?? 0) == 1 && !empty($uploadedItem->inspection_checklist)) {
                try {
                    $inspectionChecklistId = $this->service->getInspectionByNameAndType($uploadedItem->inspection_checklist);
                    if (!$inspectionChecklistId) {
                        $errors[] = "Invalid Inspection Checklist Name.";
                    }
                } catch (Exception $e) {
                    $errors[] = "Error fetching inspection: " . $e->getMessage();
                }
            }

            $shelfLifeDays = (($uploadedItem->is_expiry ?? 0) == 1) ? ($uploadedItem->shelf_life_days ?? null) : null;
            // Storage UOM Handling
            if (!empty($uploadedItem->storage_uom)) {
                try {
                    $storageUomId = $this->service->getUomId($uploadedItem->storage_uom);
                } catch (Exception $e) {
                    $errors[] = "Error fetching storage UOM: " . $e->getMessage();
                }

                if ($storageUomId === $uomId) {
                    $storageUomConversion = 1;
                    $storageUomCount = $uploadedItem->storage_uom_count ?? 1;
                } else {
                    $storageUomConversion = $uploadedItem->storage_uom_conversion ?? 1;
                    $storageUomCount = 1;
                }
            }

            if (!empty($uploadedItem->attributes)) {
                $attributes = json_decode($uploadedItem->attributes, true);
                $this->service->validateItemAttributes($attributes, $errors);
            }
            if (!empty($uploadedItem->specifications)) {
                $specifications = json_decode($uploadedItem->specifications, true);
                $this->service->validateItemSpecifications($specifications, $errors);
            }
            if (!empty($uploadedItem->alternate_uoms)) {
                $alternateUoms = json_decode($uploadedItem->alternate_uoms, true);
                $this->service->validateAlternateUoms($alternateUoms, $errors);
            }
            try {
                $item = new Item([
                    'type' => $uploadedItem->type ?? null,
                    'subcategory_id' => $subCategory->id ?? null,
                    'item_name' => $uploadedItem->item_name ?? null,
                    'item_code' => $uploadedItem->item_code ?? null,
                    'item_code_type' => $uploadedItem->item_code_type ?? null,
                    'hsn_id' => $hsnCodeId ?? null,
                    'uom_id' => $uomId ?? null,
                    'cost_price_currency_id' => $costPriceCurrencyId ?? null,
                    'sell_price_currency_id' => $sellPriceCurrencyId ?? null,
                    'storage_uom_id' => $uomId ?? null,
                    'storage_uom_conversion' => 1,
                    'storage_uom_count' =>1,
                    'created_by'=> $user->auth_user_id ?? null,
                    'group_id' => $uploadedItem->group_id ?? null,
                    'company_id' => $uploadedItem->company_id ?? null,
                    'organization_id' => null,
                    'cost_price' => $uploadedItem->cost_price ?? null,
                    'sell_price' => $uploadedItem->sell_price ?? null,
                    'min_stocking_level' => $uploadedItem->min_stocking_level ?? null,
                    'max_stocking_level' => $uploadedItem->max_stocking_level ?? null,
                    'reorder_level' => $uploadedItem->reorder_level ?? null,
                    'minimum_order_qty' => $uploadedItem->min_order_qty  ?? null,
                    'lead_days' => $uploadedItem->lead_days ?? null,
                    'safety_days' => $uploadedItem->safety_days ?? null,
                    'shelf_life_days' => $uploadedItem->shelf_life_days ?? null,
                    'po_positive_tolerance' => $uploadedItem->po_positive_tolerance ?? null,
                    'po_negative_tolerance' => $uploadedItem->po_negative_tolerance ?? null,
                    'so_positive_tolerance' => $uploadedItem->so_positive_tolerance ?? null,
                    'so_negative_tolerance' => $uploadedItem->so_negative_tolerance ?? null,
                    'is_serial_no' => $uploadedItem->is_serial_no ?? 0,
                    'is_batch_no' => $uploadedItem->is_batch_no ?? 0,
                    'is_expiry' => $uploadedItem->is_expiry ?? 0,
                    'is_inspection' => $uploadedItem->is_inspection ?? 0,
                    'inspection_checklist_id' => $inspectionChecklistId, 
                    'storage_uom_id' => $storageUomId,
                    'storage_uom_conversion' => $storageUomConversion,
                    'storage_uom_count' => $storageUomCount,
                    'storage_weight' => $uploadedItem->storage_weight ?? null,
                    'storage_volume' => $uploadedItem->storage_volume ?? null,
                    'item_remarks' => $uploadedItem->remarks ?? null,
                    'is_traded_item' => $subTypeData['is_traded_item'] ?? 0,
                    'is_asset'       => $subTypeData['is_asset'] ?? 0,
                    'is_scrap'       => $subTypeData['is_scrap'] ?? 0,
                    'asset_category_id' => $assetCategoryId,
                    'expected_life' => $expectedLife,
                    'maintenance_schedule' => $maintenanceSchedule,
                    'brand_name' => $uploadedItem->brand_name,
                    'model_no' => $uploadedItem->model_no,
                ]);

                $item->book_id = $bookId;
                $rules = [
                    'type' => 'required|string|in:Goods,Service',
                    'hsn_id' => 'required|exists:erp_hsns,id',
                    'subcategory_id' => 'required|exists:erp_categories,id',
                    'cost_price_currency_id' => 'nullable|exists:mysql_master.currency,id',
                    'sell_price_currency_id' => 'nullable|exists:mysql_master.currency,id',
                    'group_id' => 'nullable',
                    'company_id' => 'nullable',
                    'organization_id' => 'nullable',
                    'service_type' => 'nullable',
                    'sub_types.*' => 'integer|exists:mysql_master.erp_sub_types,id',
                     'item_code' => [
                        'required',
                        'max:255',
                        Rule::unique('erp_items', 'item_code')
                            ->where(function ($query) use ($uploadedItem) {
                                if ($uploadedItem->group_id !== null) {
                                    $query->where('group_id', $uploadedItem->group_id);
                                }
                                if ($uploadedItem->company_id !== null) {
                                    $query->where(function ($q) use ($uploadedItem) {
                                        $q->where('company_id', $uploadedItem->company_id)
                                            ->orWhereNull('company_id');
                                    });
                                }
                                if ($uploadedItem->organization_id !== null) {
                                    $query->where(function ($q) use ($uploadedItem) {
                                        $q->where('organization_id', $uploadedItem->organization_id)
                                            ->orWhereNull('organization_id');
                                    });
                                }
                                $query->whereNull('deleted_at');
                            }),
                    ],
                   'item_name' => [
                        'required',
                        'string',
                        'max:200',
                        Rule::unique('erp_items', 'item_name')
                            ->where(function ($query) use ($uploadedItem) {
                                if ($uploadedItem->group_id !== null) {
                                    $query->where('group_id', $uploadedItem->group_id);
                                }
                                if ($uploadedItem->company_id !== null) {
                                    $query->where(function ($q) use ($uploadedItem) {
                                        $q->where('company_id', $uploadedItem->company_id)
                                            ->orWhereNull('company_id');
                                    });
                                }
                                if ($uploadedItem->organization_id !== null) {
                                    $query->where(function ($q) use ($uploadedItem) {
                                        $q->where('organization_id', $uploadedItem->organization_id)
                                            ->orWhereNull('organization_id');
                                    });
                                }
                                $query->whereNull('deleted_at');
                            }),
                    ],
                    'uom_id' => 'required|max:255',
                    'item_remark' => 'nullable|string',
                    'cost_price' => 'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
                    'sell_price' => 'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
                    'status' => 'nullable|string',
                    'min_stocking_level' => 'nullable|numeric|min:0',
                    'max_stocking_level' => 'nullable|numeric|min:0',
                    'reorder_level' => 'nullable|numeric|min:0',
                    'minimum_order_qty' => 'nullable|numeric|min:0',
                    'lead_days' => 'nullable|numeric|min:0',
                    'safety_days' => 'nullable|numeric|min:0',
                    'shelf_life_days' => 'nullable|numeric|min:0',
                ];
                $customMessages = [
                    'required' => 'The :attribute field is required.',
                    'string' => 'The :attribute must be a string.',
                    'max' => 'The :attribute may not be greater than :max characters.',
                    'in' => 'The :attribute must be one of the following values: :values.',
                    'exists' => 'The selected :attribute is invalid.',
                    'unique' => 'The :attribute has already been taken.',
                    'regex' => 'The :attribute format is invalid.',
                    'min' => 'The :attribute must be at least :min.',
                    'nullable' => 'The :attribute field may be null.',
                    'array' => 'The :attribute must be an array.',
                    'integer' => 'The :attribute must be an integer.',
                    'subcategory_id.required' => 'The group field is required.',
                ];

                $validator = Validator::make($item->toArray(), $rules, $customMessages);
                $validationMessages = $validator->errors()->all();
                if (!empty($validationMessages) || !empty($errors)) {
                    $errors = array_merge($errors, $validationMessages);
                    $uploadedItem->update([
                        'status' => 'Failed',
                        'remarks' => implode(', ', $errors),
                    ]);
                    $this->onFailure($uploadedItem);
                    continue;
                }
                $item->document_status = ConstantHelper::DRAFT;
                $item->status = ConstantHelper::DRAFT;
                $item->save();

                $docId = $item->id;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $document_status = $approveDocument['approvalStatus'];
                $item->document_status = $document_status;
                if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                    $item->status = ConstantHelper::ACTIVE;
                } else {
                    $item->status = $document_status;
                }
                $item->save();
                $this->service->createItemAttributes($item, $attributes);
                $this->service->createItemSpecifications($item, $specifications);
                $this->service->createAlternateUoms($item, $alternateUoms);
                if (!empty($subTypeId)) {
                    ItemSubType::create([
                        'item_id' => $item->id,
                        'sub_type_id' => $subTypeId,
                    ]);
                }
                $uploadedItem->update([
                    'status' => 'Success',
                    'remarks' => 'Successfully imported item.',
                ]);
                $this->onSuccess($uploadedItem);
            } catch (Exception $e) {
                Log::error("Error fetching category: " . $e->getMessage(), ['error' => $e]);
                $errors[] = "Error fetching category: " . $e->getMessage();
                $uploadedItem->update([
                    'status' => 'Failed',
                    'remarks' => implode(', ', $errors),
                ]);
                Log::info("Updated uploaded item status to Failed. Item code: " . $uploadedItem->item_code . ".  Remarks: " . $uploadedItem->remarks . ". Status: " . $uploadedItem->status); //Check the status here
                $this->onFailure($uploadedItem);
                Log::info("Called onFailure for item code: " . $uploadedItem->item_code);
                continue;
            }
        }
    }
}

