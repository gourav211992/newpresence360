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
use Maatwebsite\Excel\Concerns\ToModel;
use Exception;
use stdClass;

class ItemImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;

    public function chunkSize(): int
    {
        return 500; 
    }

    public function __construct(ItemImportExportService $service)
    {
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
        $this->failedItems[] = [
            'item_code' => $uploadedItem->item_code,
            'item_name' => $uploadedItem->item_name,
            'uom' => $uploadedItem->uom ? $uploadedItem->uom : 'N/A',
            'hsn' => $uploadedItem->hsn ? $uploadedItem->hsn : 'N/A',
            'type' => $uploadedItem->type,
            'sub_type' => $uploadedItem->sub_type,
            'status' => 'failed',
            'remarks' => $uploadedItem->remarks,
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
    public function model(array $row)
    {
        if (collect($row)->filter()->isEmpty()) {
            return null;
        }
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $batchNo = $this->service->generateBatchNo($organization->id, $organization->group_id, $organization->company_id, $user->id);
        $uploadedItem = null;
        $validatedData = [];
        $errors = [];
        try {
            $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            $itemCodeType = 'Manual'; 
            $itemCode = null;
    
            if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
                $firstService = $services['services']->first();
                $serviceId = $firstService->service_id;
                $policyData = Helper::getPolicyByServiceId($serviceId);
                if ($policyData && isset($policyData['policyLevelData'])) {
                    $policyLevelData = $policyData['policyLevelData'];
                    $validatedData['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                    $validatedData['company_id'] = $policyLevelData['company_id'] ?? null;
                    $validatedData['organization_id'] = $policyLevelData['organization_id'] ?? null;
                } else {
                    $validatedData['group_id'] = $organization->group_id;
                    $validatedData['company_id'] = null;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = null;
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
            $attributes = [];
            for ($i = 1; $i <= 10; $i++) {
                if (isset($row["attribute_{$i}_name"])) {
                    $attributeName = $row["attribute_{$i}_name"];
                    $attributeValue = $row["attribute_{$i}_value"];
                    $requiredBom = $row["attribute_{$i}_bom_required"] ?? null;
                    $allChecked = ($row["attribute_{$i}_all_checked"] ?? 'N') === 'Y' ? 1 : 0;
                    if ($attributeName) {
                        $attributes[] = [
                            'name' =>$attributeName,
                            'value' =>$attributeValue,
                            'required_bom' =>$requiredBom,
                            'all_checked' => $allChecked,
                        ];
                    }
                }
            }
    
            $specifications = [];
            $specificationGroupName = $row['product_specification_group'] ?? 'Specification';
            $specifications[] = [
                'group_name' => $specificationGroupName,
                'specifications' => []
            ];
    
            for ($i = 1; $i <= 10; $i++) {
                if (isset($row["specification_{$i}_name"]) && isset($row["specification_{$i}_value"])) {
                    $specifications[0]['specifications'][] = [
                        'name' => $row["specification_{$i}_name"],
                        'value' => $row["specification_{$i}_value"]
                    ];
                }
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

            $categoryInitials = '';
            $subCategoryInitials = '';
            $itemName = $row['item_name'] ?? '';
            $itemInitials = strtoupper(substr($itemName, 0, 3));
            $subType = $row['sub_type'] ?? null;

            if ($itemCodeType === 'Manual') {
                $itemCode = isset($row['item_code']) && !empty($row['item_code']) ? $row['item_code'] : null;
            } elseif ($itemCodeType === 'Auto') {
                try {
                    $category = $this->service->getCategory($row['category']);
                    if ($category) {
                        $categoryInitials = $category->cat_initials ?? null;
            
                        try {
                            $subCategory = $this->service->getSubCategory($row['sub_category'], $category); 
                            $subCategoryInitials = $subCategory->sub_cat_initials ?? null;
                        } catch (Exception $e) {
                            Log::error("Error fetching sub-category: " . $e->getMessage());
                        }
                    } else {
                        Log::error("Error fetching category: " . $row['category']);
                    }
                } catch (Exception $e) {
                    Log::error("Error fetching category: " . $e->getMessage());
                }
            
                if (!empty($subCategoryInitials) && !empty($subType) && !empty($itemInitials)) {
                    $itemCode = $this->service->generateItemCode($subType, $subCategoryInitials, $itemInitials);
                }
            }

            $uploadedItem = UploadItemMaster::create([
                'item_name' => $row['item_name'] ?? null,
                'item_code' => $itemCode,
                'item_code_type' => $itemCodeType, 
                'category' => $row['category'] ?? null,
                'subcategory' => $row['sub_category'] ?? null,
                'hsn' => $row['hsnsac'] ?? null,
                'uom' => $row['inventory_uom'] ?? null,
                'cost_price' =>$row['cost_price']?? null,
                'cost_price_currency' => $row['cost_price_currency'] ?? null,
                'sell_price' => $row['sale_price']?? null,
                'sell_price_currency' => $row['sell_price_currency'] ?? null,
                'type' => ($row['type'] === 'G') ? 'Goods' : (($row['type'] === 'S') ? 'Service' : 'Goods'),
                'status' => 'Processed',
                'group_id' => $validatedData['group_id'], 
                'company_id' => $validatedData['company_id'], 
                'organization_id' => $validatedData['organization_id'], 
                'sub_type' => $row['sub_type'] ?? null,
                'remarks' => "Processing item upload",
                'batch_no' => $batchNo,
                'user_id' => $user->id,
                'min_stocking_level' => $row['min_stocking_level'] ?? null,
                'max_stocking_level' =>$row['max_stocking_level'] ?? null,
                'reorder_level' => $row['reorder_level'] ?? null,
                'minimum_order_qty' => $row['minimum_order_qty'] ?? null,
                'lead_days' => $row['lead_days'] ?? null,
                'safety_days' => $row['safety_days'] ?? null,
                'shelf_life_days' => $row['shelf_life_days'] ?? null,
                'attributes' => json_encode($attributes),
                'specifications' => json_encode($specifications),
                'alternate_uoms' => json_encode($alternateUoms),
            ]);

            if ($uploadedItem) {
                $this->processItemFromUpload($uploadedItem);
            } else {
                throw new Exception("Failed to create item in the database.");
            }
            return $uploadedItem;
    
        } catch (Exception $e) {
            Log::error("Error importing item: " . $e->getMessage(), [
                'error' => $e,
                'row' => $row
            ]);
            if (isset($uploadedItem)) {
                $uploadedItem->update([
                    'status' => 'Failed',
                    'remarks' => "Error importing item: " . $e->getMessage(),
                ]);
            }
    
            $this->onFailure($uploadedItem);
            throw new Exception("Error importing item: " . $e->getMessage());
        }
    }
    

    private function processItemFromUpload(UploadItemMaster $uploadedItem)
    {
        $user = Helper::getAuthenticatedUser();
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
        try {
        
            $category = $this->service->getCategory($uploadedItem->category);
            if ($category) {
                try {
                    $subCategory = $this->service->getSubCategory($uploadedItem->subcategory, $category);
                } catch (Exception $e) {
                    $errors[] = "Error fetching sub-category: " . $e->getMessage();
                }
            } else {
                $errors[] = "Category not found: " . $uploadedItem->category;
            }
        } catch (Exception $e) {
            $errors[] = "Error fetching category: " . $e->getMessage();
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
            $subTypeId = $this->service->getSubTypeId($uploadedItem->sub_type);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
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
                'category_id' => $category->id ?? null,
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
                'status' => 'active',
                'created_by'=> $user->auth_user_id ?? null,
                'group_id' => $uploadedItem->group_id ?? null,
                'company_id' => $uploadedItem->company_id ?? null,
                'organization_id' => $uploadedItem->organization_id ?? null,
                'cost_price' => $uploadedItem->cost_price ?? null,
                'sell_price' => $uploadedItem->sell_price ?? null,
                'min_stocking_level' => $uploadedItem->min_stocking_level ?? null,
                'max_stocking_level' => $uploadedItem->max_stocking_level ?? null,
                'reorder_level' => $uploadedItem->reorder_level ?? null,
                'minimum_order_qty' => $uploadedItem->minimum_order_qty ?? null,
                'lead_days' => $uploadedItem->lead_days ?? null,
                'safety_days' => $uploadedItem->safety_days ?? null,
                'shelf_life_days' => $uploadedItem->shelf_life_days ?? null,
                'item_remarks' => $uploadedItem->remarks ?? null,
            ]);

    
            $rules = [
                'type' => 'required|string|in:Goods,Service',
                'hsn_id' => 'required|exists:erp_hsns,id',
                'category_id' => 'required|exists:erp_categories,id',
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
                    ->where('group_id', $uploadedItem->group_id) 
                    ->whereNull('deleted_at')
                ],
                'item_name' => [
                    'required',
                    'string',
                    'max:200',
                    Rule::unique('erp_items', 'item_name')
                    ->where('group_id', $uploadedItem->group_id) 
                    ->whereNull('deleted_at')
                ],
                'uom_id' => 'required|max:255',
                'item_remark' => 'nullable|string',
                'cost_price' => 'required|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
                'sell_price' => 'required|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
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
            ];
        
            $validator = Validator::make($item->toArray(), $rules, $customMessages);
         
            if ($validator->fails()) {
                $errors[] = 'Validation errors: ' . implode(', ', $validator->errors()->all());
            
                $uploadedItem->update([
                    'status' => 'Failed',
                    'remarks' => implode(', ', $errors),
                ]);
            
                $this->onFailure($uploadedItem);
                return; 
            }

            $item->save();

            $this->service->createItemAttributes($item, $attributes);
            $this->service->createItemSpecifications($item, $specifications);
            $this->service->createAlternateUoms($item, $alternateUoms);
    
            // if ($subTypeId) {
            //     $item->subTypes()->attach($subTypeId);
            // }
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
            return;  
        }
    }
}
