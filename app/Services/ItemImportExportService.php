<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ItemSpecification;
use App\Models\InspectionChecklist;
use App\Models\AlternateUOM;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationDetail;
use App\Models\Category;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\Unit;
use App\Models\Hsn;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\PincodeMaster;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\PaymentTerm;
use App\Models\SubType;
use App\Models\UploadItemMaster;
use App\Models\FixedAssetSetup;
use App\Models\OrganizationType;
use App\Helpers\EInvoiceHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\GstnHelper;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;
use Exception;

class ItemImportExportService
{
   public function generateItemCode(array $subTypes, $subCategoryInitials, $itemInitials)
    {

        $authUser = Helper::getAuthenticatedUser();
        $subType = $this->getItemSubTypeCode($subTypes);
        $baseCode = $subType . $subCategoryInitials . $itemInitials;

        $lastInUpload = UploadItemMaster::where('item_code', 'like', "{$baseCode}%")
        ->withDefaultGroupCompanyOrg()
        ->where('status', '!=', 'failed')
        ->where('user_id', $authUser->auth_user_id) 
        ->orderBy('id', 'desc')
        ->first();

        if ($lastInUpload) {
            $lastSuffix = intval(substr($lastInUpload->item_code, -3));
        } 

        else {
            $lastInItem = Item::where('item_code', 'like', "{$baseCode}%")
                ->withDefaultGroupCompanyOrg()
                ->orderBy('id', 'desc')
                ->first();

            $lastSuffix = $lastInItem ? intval(substr($lastInItem->item_code, -3)) : 0;
        }

        $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);

        return $baseCode . $nextSuffix;
    }
    
    private function getItemSubTypeCode(array $types): string
    {
        $types = array_unique(array_map('strtoupper', $types));
        sort($types);

       $combinations = [
            // Triple
            ['match' => ['A', 'RM', 'TI'], 'code' => 'RM'],
            ['match' => ['A', 'FG', 'TI'], 'code' => 'FG'],
            ['match' => ['A', 'SF', 'TI'], 'code' => 'SF'],
            ['match' => ['A', 'E',  'TI'], 'code' => 'EX'],

            // Double
            ['match' => ['A', 'TI'],       'code' => 'AS'],
            ['match' => ['RM', 'TI'],      'code' => 'RM'],
            ['match' => ['FG', 'TI'],      'code' => 'FG'],
            ['match' => ['SF', 'TI'],      'code' => 'SF'],
            ['match' => ['E', 'TI'],       'code' => 'EX'],
            ['match' => ['A', 'RM'],       'code' => 'RM'],
            ['match' => ['A', 'FG'],       'code' => 'FG'],
            ['match' => ['A', 'SF'],       'code' => 'SF'],
            ['match' => ['A', 'E'],        'code' => 'EX'],

            // Single
            ['match' => ['RM'],            'code' => 'RM'],
            ['match' => ['FG'],            'code' => 'FG'],
            ['match' => ['SF'],            'code' => 'SF'],
            ['match' => ['E'],             'code' => 'EX'],
            ['match' => ['TI'],            'code' => 'TR'],
            ['match' => ['A'],             'code' => 'AS'],
            ['match' => ['SC'],             'code' => 'SC'],
        ];
         foreach ($combinations as $combo) {
            $match = $combo['match'];
            sort($match);
            if ($types === $match) {
                return $combo['code'];
            }
        }

        throw new \InvalidArgumentException(
            "Invalid subtype combination provided: " . implode(', ', $types)
        );
    }

    public function generateCustomerCode($customerInitials, $customerType)
    {
        $prefix = '';
        if ($customerType === 'Regular') {
            $prefix = 'R';
        } elseif ($customerType === 'Cash') {
            $prefix = 'CA';
        }

        $baseCode = $prefix . $customerInitials;
        $lastSimilarCustomer = Customer::withDefaultGroupCompanyOrg()
            ->where('customer_code', 'like', "{$baseCode}%")
            ->orderBy('customer_code', 'desc')
            ->first();
        
        $nextSuffix = '001';

        if ($lastSimilarCustomer) {
            $lastSuffix = intval(substr($lastSimilarCustomer->customer_code, -3));
            $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);
        }
        
        $finalCustomerCode = $baseCode . $nextSuffix;
        return $finalCustomerCode;
    }

    public function generateVendorCode($vendorInitials, $vendorType)
    {
  
        $prefix = '';
        if ($vendorType === 'Regular') {
            $prefix = 'R';
        } elseif ($vendorType === 'Cash') {
            $prefix = 'CA';
        }

        $baseCode = $prefix . $vendorInitials;
        $lastSimilarVendor = Vendor::withDefaultGroupCompanyOrg()
            ->where('vendor_code', 'like', "{$baseCode}%")
            ->orderBy('vendor_code', 'desc')
            ->first();

        $nextSuffix = '001';

        if ($lastSimilarVendor) {
            $lastSuffix = intval(substr($lastSimilarVendor->vendor_code, -3));
            $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);
        }

        $finalVendorCode = $baseCode . $nextSuffix;

        return $finalVendorCode;
    }

    public function getCategory($categoryName)
    {
        $category = Category::withDefaultGroupCompanyOrg()
        ->where('name', $categoryName)
        ->where('status', ConstantHelper::ACTIVE) 
        ->first();

        if (!$category) {
            throw new Exception("Category not found or not active : {$categoryName}");
        }

        return $category;
    }

    public function getSubCategory($categoryName)
    {
        $category = Category::withDefaultGroupCompanyOrg()
            ->where('name', $categoryName)
            ->where('status', ConstantHelper::ACTIVE) 
            ->first();

        if (!$category) {
            throw new Exception("Group not found or not active: {$categoryName}");
        }

        if ($category->subCategories()->exists()) {
            $subCategories=$category->subCategories()->where('status', ConstantHelper::ACTIVE)->get();
            foreach ($subCategories as $subcat) {
                if ($subcat->name == $categoryName) {
                    if ($subcat->subCategories()->exists()) {
                        throw new Exception("There a child name that exists, please find last level. (" . $categoryName . ") is not a last-level group.");
                    } else {
                        return $subcat;
                    }
                }
            }
            throw new Exception("Group ".$categoryName." is not the last level.");

        } else {
            return $category;
        }
    }
    public function getSalesPersonId($salesPerson)
    {
        $salesPerson = Employee::where('name', $salesPerson)
        ->where('status', ConstantHelper::ACTIVE) 
        ->first();

        if (!$salesPerson) {
            throw new Exception("Sales Person not found or not active: {$salesPerson}");
        }

        return $salesPerson->id;  
    }

    public function getHSNCode($hsnCode)
    {
        $hsn = Hsn::withDefaultGroupCompanyOrg()
              ->where('code', $hsnCode)
              ->where('status', ConstantHelper::ACTIVE) 
              ->first();
        if (!$hsn) {
            throw new Exception("HSN Code not found or not active: {$hsnCode}");
        }
        return $hsn->id;
    }

    public function getUomId($uomName)
    {
        $uom = Unit::withDefaultGroupCompanyOrg()
               ->where('name', $uomName)
               ->where('status', ConstantHelper::ACTIVE) 
               ->first();
        if (!$uom) {
            throw new Exception("UOM not found or not active: {$uomName}");
        }
        return $uom->id;
    }

    public function getCurrencyId($currencyName)
    {
        $currency = Currency::where('short_name', $currencyName)
        ->where('status', ConstantHelper::ACTIVE) 
        ->first();
        if (!$currency) {
            throw new Exception("Currency not found or not active: {$currencyName}");
        }
        return $currency->id;
    }

    public function getPaymentTermId($paymentTermName)
    {
        $paymentTerm = PaymentTerm::withDefaultGroupCompanyOrg()
                              ->where('name', $paymentTermName)
                              ->where('status', ConstantHelper::ACTIVE) 
                              ->first();
        if (!$paymentTerm) {
            throw new Exception("Payment term not found or not active: {$paymentTermName}");
        }
        return $paymentTerm->id;
    }

    public function getInspectionByNameAndType($inspectionName)
    {
        $inspection = InspectionChecklist::where('name', $inspectionName)
            ->where('type', 'Item')
            ->where('status', ConstantHelper::ACTIVE) 
            ->first();

        if (!$inspection) {
            throw new \Exception("Inspection not found or not active: {$inspectionName}");
        }

        return $inspection->id;
    }

  public function getLedgerAndGroupIds($ledgerCode, $ledgerGroupName)
    {
        try {
            $ledger = Ledger::withDefaultGroupCompanyOrg()
                ->where('code', $ledgerCode)
                ->where('status', 1) 
                ->first();

            if (!$ledger) {
                throw new Exception('Ledger not found for the given ledger code.');
            }

             $ledgerGroup = Group::where('name', $ledgerGroupName)
                ->where('status', ConstantHelper::ACTIVE) 
                ->first();
            if (!$ledgerGroup) {
                throw new Exception('Ledger Group not found for the given ledger group name.');
            }

            $ledgerId = $ledger->id;
            $ledgerGroupId = $ledgerGroup->id;

            if (!$ledgerGroup->children->isEmpty()) {
                throw new Exception("The given group '{$ledgerGroupName}' is not a last-level group.");
            }

            $exists = Ledger::where('id', $ledgerId)
                ->where(function($q) use ($ledgerGroupId) {
                    $q->orWhereJsonContains('ledger_group_id', (string)$ledgerGroupId)
                    ->orWhereJsonContains('ledger_group_id', $ledgerGroupId);
                })
                ->exists();

            if (!$exists) {
                throw new Exception("Ledger is not linked with the given last-level group '{$ledgerGroupName}'.");
            }

            return [
                'ledger_id' => $ledgerId,
                'ledger_group_id' => $ledgerGroupId
            ];

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function getItemStatus($status)
    {
        return $status === 'submitted' ? 'Active' : ($status === 'failed' ? 'Failed' : 'Draft');
    }


    public function getSubTypeId(array $subTypeCodes): array
    {
        $subTypeMapping = [
            'FG' => 'Finished Goods',
            'SF' => 'WIP/Semi Finished',
            'RM' => 'Raw Material',
            'E'  => 'Expense',
        ];

        $subTypeCodes = array_unique(array_map('strtoupper', $subTypeCodes));
        sort($subTypeCodes);

        $validCombinations = [
             // Triple
            ['match' => ['A', 'RM', 'TI'], 'code' => 'RM'],
            ['match' => ['A', 'FG', 'TI'], 'code' => 'FG'],
            ['match' => ['A', 'SF', 'TI'], 'code' => 'SF'],
            ['match' => ['A', 'E',  'TI'], 'code' => 'E'],

            // Double
            ['match' => ['A', 'TI'],       'code' => null],
            ['match' => ['RM', 'TI'],      'code' => 'RM'],
            ['match' => ['FG', 'TI'],      'code' => 'FG'],
            ['match' => ['SF', 'TI'],      'code' => 'SF'],
            ['match' => ['E', 'TI'],       'code' => 'E'],
            ['match' => ['A', 'RM'],       'code' => 'RM'],
            ['match' => ['A', 'FG'],       'code' => 'FG'],
            ['match' => ['A', 'SF'],       'code' => 'SF'],
            ['match' => ['A', 'E'],        'code' => 'E'],

            // Single
            ['match' => ['RM'],            'code' => 'RM'],
            ['match' => ['FG'],            'code' => 'FG'],
            ['match' => ['SF'],            'code' => 'SF'],
            ['match' => ['E'],             'code' => 'E'],
            ['match' => ['TI'],            'code' => null],
            ['match' => ['A'],             'code' => null],
            ['match' => ['SC'],            'code' => null],
        ];

        $matched = false;
        $resolvedCode = null;

        foreach ($validCombinations as $combo) {
            $match = $combo['match'];
            sort($match);
            if ($subTypeCodes === $match) {
                $matched = true;
                $resolvedCode = $combo['code'];
                break;
            }
        }

        if (!$matched) {
            throw new \Exception("Invalid SubType combination: " . implode(', ', $subTypeCodes));
        }

        $result = [
            'sub_type_id'     => null,
            'is_traded_item'  => in_array('TI', $subTypeCodes) ? 1 : 0,
            'is_asset'        => in_array('A', $subTypeCodes) ? 1 : 0,
            'is_scrap'        => in_array('SC', $subTypeCodes) ? 1 : 0,
        ];

        if ($resolvedCode && isset($subTypeMapping[$resolvedCode])) {
            $subTypeName = $subTypeMapping[$resolvedCode];
            $subType = SubType::where('name', $subTypeName)->first();

            if (!$subType) {
                throw new \Exception("SubType not found for name: {$subTypeName}");
            }

            $result['sub_type_id'] = $subType->id;
        }

        return $result;
    }

    public function getOrganizationTypeId($orgTypeCode)
    {

        if (isset($orgTypeCode)) {
            $normalizedCode = ucwords(strtolower($orgTypeCode));
            $orgType = OrganizationType::whereRaw('LOWER(name) = ?', [strtolower($normalizedCode)])
            ->where('status', ConstantHelper::ACTIVE) 
            ->first();
            if (!$orgType) {
                throw new Exception("Organization Type not found or not active: {$orgTypeCode}");
            }
            return $orgType->id;
        }
    
    }

   public function validateItemAttributes($attributes, &$errors)
    {
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributeGroup = $this->getAttributeGroupByName($attribute['name'], $errors);
                if (!$attributeGroup) {
                    $errors[] = "Attribute group not found: {$attribute['name']}";
                    continue;
                }
               
               if (!empty($attribute['value']) && is_string($attribute['value'])) {
                    $attributeValues = array_filter(array_map('trim', explode(',', $attribute['value'])));

                    foreach ($attributeValues as $value) {
                        $attributeObj = $this->getAttributeByName($value, $attributeGroup, $errors);
                        if (!$attributeObj) {
                            $errors[] = "Attribute not found: {$value} in group {$attribute['name']}";
                        }
                    }
                }
            }
        }
    }
    
    public function validateItemSpecifications($specifications, &$errors)
    {
        if ($specifications) {
            foreach ($specifications as $specGroup) {
                if (!isset($specGroup['group_name']) || $specGroup['group_name'] === '') {
                    if (!in_array("Specification group name missing.", $errors)) {
                        $errors[] = "Specification group name missing.";
                    }
                    continue;
                }
                $specGroupObj = $this->getProductSpecificationGroupByName($specGroup['group_name'], $errors);
                if (!$specGroupObj) {
                    $msg = "Specification group not found: {$specGroup['group_name']}";
                    if (!in_array($msg, $errors)) {
                        $errors[] = $msg;
                    }
                }
                if (isset($specGroup['specifications']) && is_array($specGroup['specifications'])) {
                    foreach ($specGroup['specifications'] as $spec) {
                        if (!isset($spec['name']) || $spec['name'] === '') {
                            $msg = "Specification name missing in group: {$specGroup['group_name']}";
                            if (!in_array($msg, $errors)) {
                                $errors[] = $msg;
                            }
                            continue;
                        }
                        $specObj = $this->getProductSpecificationByName($spec['name'], $errors);
                        if (!$specObj) {
                            $msg = "Specification not found: {$spec['name']} in group {$specGroup['group_name']}";
                            if (!in_array($msg, $errors)) {
                                $errors[] = $msg;
                            }
                        }
                    }
                }
            }
        }
    }
    

    public function validateAlternateUoms($alternateUoms, &$errors)
    {
        if ($alternateUoms) {
            foreach ($alternateUoms as $uomData) {
                if (!isset($uomData['uom']) || $uomData['uom'] === '') {
                    $errors[] = "Alternate UOM value missing.";
                    continue;
                }
                try {
                    $uom = $this->getUomId($uomData['uom']);
                } catch (Exception $e) {
                    $errors[] = "UOM not found: {$uomData['uom']}";
                    continue;
                }
            }
        }
    }

    public function createItemAttributes($item, $attributes)
    {
        $errors = [];
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributeGroup = $this->getAttributeGroupByName($attribute['name'], $errors);
                if ($attributeGroup) {
                    $this->createItemAttribute($item, $attribute, $attributeGroup,$errors);
                }
            }
        }
    }

    private function createItemAttribute($item, $attribute, $attributeGroup, &$errors)
    {
        $attributeValues = explode(',', $attribute['value']);
        $attributeValues = array_filter($attributeValues);
        $attributeIds = []; 
        $allChecked = $attribute['all_checked'] ?? 0;

        if (!empty($attributeValues)) {
            foreach ($attributeValues as $value) {
                try {
                    $attributeValue = $this->getAttributeByName($value, $attributeGroup, $errors);
                    if ($attributeValue) {
                        $attributeIds[] = (string) $attributeValue->id;  
                    } else {
                        $errors[] = "Failed to create item attribute for value {$value}: Attribute not found";
                    }
                } catch (Exception $e) {
                    $errors[] = "Failed to create item attribute for value {$value}: " . $e->getMessage();
                }
            }
        }
    
        ItemAttribute::create([
            'item_id' => $item->id,
            'attribute_group_id' => $attributeGroup->id,
            'attribute_id' => $attributeIds,  
            'required_bom' => 0,
            'all_checked' => $allChecked, 
        ]);
    }
    
    
    public function getAttributeGroupByName($attributeName, &$errors)
    {
        
        try {
            $attributeGroup = AttributeGroup::withDefaultGroupCompanyOrg()
            ->where('name', $attributeName)
            ->where('status', ConstantHelper::ACTIVE)
            ->first();
            if (!$attributeGroup) {
                throw new Exception("AttributeGroup not found or not active: {$attributeName}");
            }
            return $attributeGroup;
        } catch (Exception $e) {
            $errorMessage = "Error fetching attribute group: " . $e->getMessage();
            $errors[] = $errorMessage;
            return null; 
        }
    }

    public function getAttributeByName($attributeName, $attributeGroup, &$errors)
    {
        $attributeValues = explode(',', $attributeName);
        foreach ($attributeValues as $value) {
            try {
                $value = trim($value);
                $attribute = Attribute::where('value', $value)
                    ->where('attribute_group_id', $attributeGroup->id)
                    ->first();
    
                if (!$attribute) {
                    $errorMessage = "Attribute not found: {$value} in group {$attributeGroup->name}";
                    $errors[] = $errorMessage;
                    return null; 
                }
            } catch (Exception $e) {
                $errorMessage = "Error fetching attribute value: {$value} from group {$attributeGroup->name}: " . $e->getMessage();
                $errors[] = $errorMessage;
            }
        }
        return $attribute; 
    }

    public function createItemSpecifications($item, $specifications)
    {
        if ($specifications) {
            foreach ($specifications as $specGroup) {
                if (isset($specGroup['specifications']) && is_array($specGroup['specifications'])) {
                    foreach ($specGroup['specifications'] as $spec) {
                        $this->createItemSpecificationAndGroup($item, $spec, $specGroup['group_name'], $errors);
                    }
                }
            }
        }
    }


    private function createItemSpecificationAndGroup($item, $spec, $groupName, &$errors)
    {
        try {
            $productSpecification = $this->getProductSpecificationByName($spec['name'], $errors);
            
            if ($productSpecification) {
                $productSpecificationGroup = $this->getProductSpecificationGroupByName($groupName, $errors);
                if ($productSpecificationGroup) {
                    ItemSpecification::create([
                        'item_id' => $item->id,
                        'specification_id' => $productSpecification->id,
                        'specification_name' => $productSpecification->name,
                        'value' => $spec['value'],
                        'group_id' => $productSpecificationGroup ? $productSpecificationGroup->id : null,
                    ]);
                } else {
                    $errors[] = "Failed to create item specification for {$spec['name']}: Specification group {$groupName} not found";
                }
            } else {
                $errors[] = "Failed to create item specification for {$spec['name']}: Specification not found";
            }
        } catch (Exception $e) {
            $errors[] = "Failed to create item specification: " . $e->getMessage();
        }
    }


    public function getProductSpecificationGroupByName($groupName, &$errors)
    {
        try {
            $productSpecificationGroup = ProductSpecification::withDefaultGroupCompanyOrg()
            ->where('name', $groupName)
            ->where('status', ConstantHelper::ACTIVE)
            ->first();
            
            if (!$productSpecificationGroup) {
                $errorMessage = "ProductSpecificationGroup not found or not active for group name: {$groupName}";
                $errors[] = $errorMessage;
                return null; 
            }
    
            return $productSpecificationGroup;
        } catch (Exception $e) {
            $errorMessage = "Error fetching product specification group: " . $e->getMessage();
            $errors[] = $errorMessage;
            return null; 
        }
    }

    public function getProductSpecificationByName($specName, &$errors)
    {
        try {
            $productSpecification = ProductSpecificationDetail::where('name', $specName)->first();
            
            if (!$productSpecification) {
                $errorMessage = "ProductSpecificationDetail not found: {$specName}";
                $errors[] = $errorMessage;
                return null;  
            }
    
            return $productSpecification;
        } catch (Exception $e) {
            $errorMessage = "Error fetching product specification: " . $e->getMessage();
            $errors[] = $errorMessage;
            return null; 
        }
    }

    public function createAlternateUoms($item, $alternateUoms)
    {
        if ($alternateUoms) {
            foreach ($alternateUoms as $uomData) {
                $this->createAlternateUomForItem($item, $uomData, $errors);
            }
        }
    }
    public function createAlternateUomForItem($item, $uomData, &$errors)
    {
        try {
            $uom = $this->getUomId($uomData['uom']);
            
            if (!$uom) {
                $errors[] = "UOM not found for item {$item->id} with UOM name {$uomData['uom']}. Skipping alternate UOM creation.";
                return; 
            }
            AlternateUOM::create([
                'item_id' => $item->id,
                'uom_id' => $uom,
                'conversion_to_inventory' => $uomData['conversion'],
                'cost_price' => $uomData['cost_price'] ?? null,
                'sell_price' => $uomData['sell_price'] ?? null,
                'is_selling' => (strpos($uomData['default'], 'S') !== false) ? 1 : 0,  
                'is_purchasing' => (strpos($uomData['default'], 'P') !== false) ? 1 : 0,  
            ]);
        } catch (Exception $e) {
            $errorMessage = "Failed to create alternate UOM for item {$item->id}: " . $e->getMessage();
            $errors[] = $errorMessage;
        }
    }

    public function generateBatchNo($organizationId, $groupId, $companyId, $userId)
    {
        $date = now()->format('Y-m-d'); 
        $lastBatch = UploadItemMaster::where('organization_id', $organizationId)
                                     ->where('group_id', $groupId)
                                     ->where('company_id', $companyId)
                                     ->where('user_id', $userId)
                                     ->where('batch_no', 'like', "{$organizationId}-{$groupId}-{$companyId}-{$userId}-{$date}%")
                                     ->orderBy('batch_no', 'desc')
                                     ->first();
 
         if ($lastBatch) {
             return $lastBatch->batch_no;
         }
         $nextSuffix = '001';
     
         return "{$organizationId}-{$groupId}-{$companyId}-{$userId}-{$date}-{$nextSuffix}";
     }

    public function getLocationIds($countryName, $stateName, $cityName, $pincode)
    {
        $countryId = null;
        $stateId = null;
        $cityId = null;
        $pincodeId = null;
        $pincodeVal = null;
        $errors = [];

        if (isset($countryName)) {
            if (trim($countryName) === '') {
                $errors['country'] = "Country name is empty.";
            } else {
                $country = Country::where('name', $countryName)->first();
                if (!$country) {
                    $errors['country'] = "Country '{$countryName}' not found.";
                } else {
                    $countryId = $country->id;
                }
            }
        }

        if (isset($stateName)) {
            if (trim($stateName) === '') {
                $errors['state'] = "State name is empty.";
            } else {
                $query = State::where('name', $stateName);
                if ($countryId) {
                    $query->where('country_id', $countryId);
                }
                $state = $query->first();
                if (!$state) {
                    $errors['state'] = "Invalid state name: '{$stateName}'" . ($countryName ? " for the selected country: '{$countryName}'." : ".");
                } else {
                    $stateId = $state->id;
                }
            }
        }

        if (isset($cityName)) {
            if (trim($cityName) === '') {
                $errors['city'] = "City name is empty.";
            } else {
                $query = City::where('name', $cityName);
                if ($stateId) {
                    $query->where('state_id', $stateId);
                }
                $city = $query->first();
                if (!$city) {
                    $errors['city'] = "Invalid city name: '{$cityName}'" . ($stateName ? " for the selected state: '{$stateName}'." : ".");
                } else {
                    $cityId = $city->id;
                }
            }
        }

        if (isset($pincode)) {
            if (trim($pincode) === '') {
                $errors['pincode'] = "Pincode is empty.";
            } else {
                $query = PincodeMaster::where('pincode', $pincode);
                if ($stateId) {
                    $query->where('state_id', $stateId);
                }
                $pincodeRecord = $query->first();
                if (!$pincodeRecord) {
                    $errors['pincode'] = "Invalid pincode: '{$pincode}'" . ($stateName ? " for the selected state: '{$stateName}'." : ".");
                } else {
                    $pincodeId = $pincodeRecord->id;
                    $pincodeVal = $pincodeRecord->pincode;
                }
            }
        }

        return [
            'country_id'  => $countryId,
            'state_id'    => $stateId,
            'city_id'     => $cityId,
            'pincode_id'  => $pincodeId,
            'pincode'     => $pincodeVal ?? $pincode,
            'errors'      => $errors,
        ];
    }


    public function getAssetCategoryDetailsByName($assetCategoryName)
    {
        try {
            $fixedAssetCategories = FixedAssetSetup::with('assetCategory')
                ->where('status', ConstantHelper::ACTIVE)
                ->select('id', 'asset_category_id', 'expected_life_years', 'maintenance_schedule')
                ->get();

            $matched = $fixedAssetCategories->first(function ($item) use ($assetCategoryName) {
                return strtolower(trim($item->assetCategory?->name)) === strtolower(trim($assetCategoryName));
            });

            if ($matched) {
                return [
                    'asset_category_id' => $matched->asset_category_id,
                    'expected_life_years' => $matched->expected_life_years,
                    'maintenance_schedule' => $matched->maintenance_schedule,
                ];
            }

            throw new \Exception("Asset Category '{$assetCategoryName}' not found or inactive.");
        } catch (\Exception $e) {
            Log::error("Error in getAssetCategoryDetailsByName: " . $e->getMessage());
            throw $e;
        }
    }

    public function validateGstAndAddresses($data)
    {
        $errors = [];
        $addresses = $data['addresses'] ?? [];
        $billingCount = 0;
        $shippingCount = 0;
        if (empty($addresses)) {
            $errors['addresses'] = 'At least one address is required.';
            return $errors; 
        }
        foreach ($addresses as $index => $address) {
            if (empty($address['address'])) {
                $errors["addresses.{$index}.address"] = 'Address is required.';
            }
           
            if (empty($address['state_id'])) {
                $errors["addresses.{$index}.state_id"] = 'State is required.';
            }
            if (empty($address['country_id'])) {
                $errors["addresses.{$index}.country_id"] = 'Country is required.';
            }
            if (empty($address['pincode_id'])) {
                $errors["addresses.{$index}.pincode"] = 'Pincode is required.';
            }
            if (!empty($address['is_billing'])) {
                $billingCount++;
            }
            if (!empty($address['is_shipping'])) {
                $shippingCount++;
            }
        }
    
        if ($billingCount === 0) {
            $errors['addresses'] = 'At least one billing address is required.';
        }
        // if ($shippingCount === 0) {
        //     $errors['addresses'] = 'At least one shipping address is required.';
        // }
    
        // GST Validation
        $gstinNo = $data['compliance']['gstin_no'] ?? null;
        $gstinApplicable = $data['compliance']['gst_applicable'] ?? null;
        $gstinRegistrationDate = $data['compliance']['gstin_registration_date'] ?? null;
        $gstinLegalName = $data['compliance']['gst_registered_name'] ?? null;
    
        if ($gstinNo and $gstinApplicable == 1) {
            $gstValidation = EInvoiceHelper::validateGstinName($gstinNo);
            if ($gstValidation['Status'] == 1) {
                $gstData = json_decode($gstValidation['checkGstIn'], true);
                $gstnHelper = new GstnHelper();
               foreach ($addresses as $index => $address) {
                if (!empty($address['state_id'])) {
                    $stateValidation = $gstnHelper->validateStateCode(
                        $address['state_id'],
                        $gstData['StateCode'] ?? null
                    );

                    if (!$stateValidation['valid'] && !empty($stateValidation['message'])) {
                        $errors["addresses.{$index}.state_id"] = $stateValidation['message'];
                    } else {
                        $state = State::find($address['state_id']);
                        if (!$state) {
                            $errors["addresses.{$index}.state_id"] = 'Invalid state selected.';
                        }
                        if (!empty($address['country_id']) && $state && $state->country_id != $address['country_id']) {
                            $errors["addresses.{$index}.country_id"] = 'Selected country does not belong to the selected state.';
                        }

                        if (!empty($address['city_id'])) {
                            $cityExists = City::where('id', $address['city_id'])
                                ->where('state_id', $address['state_id'])
                                ->exists();

                            if (!$cityExists) {
                                $errors["addresses.{$index}.city_id"] = 'Selected city does not belong to the selected state.';
                            }
                        }

                        if (!empty($address['pincode_id'])) {
                            $pincodeExists = PincodeMaster::where('id', $address['pincode_id'])
                                ->where('state_id', $address['state_id'])
                                ->exists();

                            if (!$pincodeExists) {
                                $errors["addresses.{$index}.pincode"] = 'Pincode does not match the selected state.';
                            }
                        }
                    }
                }
            }
            } else {
                $errors['compliance.gstin_no'] = 'The provided GSTIN number is invalid. Please verify and try again.';
            }
        }
    
        return $errors;
    }

}
