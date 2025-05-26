<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ItemSpecification;
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
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\PaymentTerm;
use App\Models\SubType;
use App\Models\UploadItemMaster;
use App\Models\OrganizationType;
use App\Helpers\EInvoiceHelper;
use App\Helpers\GstnHelper;
use Illuminate\Support\Facades\Log;
use Exception;

class ItemImportExportService
{
    public function generateItemCode($subType, $subCategoryInitials, $itemInitials)
    {
        $baseCode = $subType . $subCategoryInitials . $itemInitials;
        $lastSimilarItem = Item::where('item_code', 'like', "{$baseCode}%")
            ->withDefaultGroupCompanyOrg()
            ->orderBy('item_code', 'desc')
            ->first();

        $nextSuffix = '001';
        if ($lastSimilarItem) {
            $lastSuffix = intval(substr($lastSimilarItem->item_code, -3));
            $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);
        }

        return $baseCode . $nextSuffix;
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
        ->first();

        if (!$category) {
            throw new Exception('Category not found');
        }

        return $category;
    }

    public function getSubCategory($subCategoryName, $category)
    {
        $subCategory = Category::withDefaultGroupCompanyOrg()
            ->where('name', $subCategoryName)
            ->where('parent_id', $category->id)
            ->first();

        if (!$subCategory) {
            throw new Exception('Subcategory not found under this category');
        }

        return $subCategory;
    }


    public function getSalesPersonId($salesPerson)
    {
    
        $salesPerson = Employee::where('name', $salesPerson)
        ->first();

        if (!$salesPerson) {
            throw new Exception('Sales Person not found');
        }

        return $salesPerson->id;  
    }

    public function getHSNCode($hsnCode)
    {
        $hsn = Hsn::withDefaultGroupCompanyOrg()
              ->where('code', $hsnCode)
              ->first();
        if (!$hsn) {
            throw new Exception("HSN Code not found: {$hsnCode}");
        }
        return $hsn->id;
    }

    public function getUomId($uomName)
    {
        $uom = Unit::withDefaultGroupCompanyOrg()
               ->where('name', $uomName)
               ->first();
        if (!$uom) {
            throw new Exception("UOM not found: {$uomName}");
        }
        return $uom->id;
    }

    public function getCurrencyId($currencyName)
    {
        $currency = Currency::where('short_name', $currencyName)->first();
        if (!$currency) {
            throw new Exception("Currency not found: {$currencyName}");
        }
        return $currency->id;
    }

    public function getPaymentTermId($paymentTermName)
    {
        $paymentTerm = PaymentTerm::withDefaultGroupCompanyOrg()
                              ->where('name', $paymentTermName)
                              ->first();
        if (!$paymentTerm) {
            throw new Exception("Payment term not found: {$paymentTermName}");
        }
        return $paymentTerm->id;
    }


    public function getLedgerAndGroupIds($ledgerCode, $ledgerGroupName)
    {
        try {
            $ledger = Ledger::withDefaultGroupCompanyOrg()
                        ->where('code', $ledgerCode)
                        ->first();
            if (!$ledger) {
                throw new Exception('Ledger not found for the given ledger code.');
            }
            $ledgerGroup = Group::where('name', $ledgerGroupName)
            ->first();
            if (!$ledgerGroup) {
                throw new Exception('Ledger Group not found for the given ledger group name.');
            }

            $ledgerId = $ledger->id;
            $ledgerGroupId = $ledgerGroup->id;
            $groupIds = json_decode($ledger->ledger_group_id, true); 

            $groupExists = false;

            if (is_array($groupIds)) {
                $groupExists = in_array($ledgerGroupId, $groupIds); 
            } elseif (is_numeric($ledger->ledger_group_id)) {
                $groupExists = ($ledgerGroupId == $ledger->ledger_group_id);  
            }

            if (!$groupExists) {
                throw new Exception('Ledger Group not found in the associated Ledger.');
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

    public function getSubTypeId($subTypeCode)
    {
        $subTypeMapping = [
            'FG' => 'Finished Goods',
            'SF' => 'WIP/Semi Finished',
            'RM' => 'Raw Material',
            'TI' => 'Traded Item',
            'A'  => 'Asset',
            'E'  => 'Expense'
        ];

        if (isset($subTypeMapping[$subTypeCode])) {
            $subTypeName = $subTypeMapping[$subTypeCode];
            $subType = SubType::where('name', $subTypeName)->first();
            if (!$subType) {
                throw new Exception("SubType not found: {$subTypeName}");
            }
            return $subType->id;
        }

        throw new Exception("Invalid SubType code: {$subTypeCode}");
    }


    public function getOrganizationTypeId($orgTypeCode)
    {

        if (isset($orgTypeCode)) {
            $normalizedCode = ucwords(strtolower($orgTypeCode));
            $orgType = OrganizationType::whereRaw('LOWER(name) = ?', [strtolower($normalizedCode)])->first();
            if (!$orgType) {
                throw new Exception("Organization Type not found: {$orgTypeCode}");
            }
            return $orgType->id;
        }
    
    }

    public function validateItemAttributes($attributes, &$errors)
    {
        if ($attributes) {
            foreach ($attributes as $attribute) {
                if (empty($attribute['value'])) {
                    continue;
                }  
                $attributeGroup = $this->getAttributeGroupByName($attribute['name'], $errors); 
                if ($attributeGroup) {
                    $attributeValues = explode(',', $attribute['value']);
                    foreach ($attributeValues as $value) {
                        $attributeValue = $this->getAttributeByName($value, $attributeGroup, $errors); 
                        if (!$attributeValue) {
                            $errors[] = "Attribute value {$value} for group {$attributeGroup->name} is invalid.";
                        }
                    }
                } else {
                    $errors[] = "Attribute group not found for {$attribute['name']}";
                }
            }
        }
    }

    public function validateItemSpecifications($specifications, &$errors)
    {
        if ($specifications) {
            foreach ($specifications as $specGroup) {
                if (isset($specGroup['specifications']) && is_array($specGroup['specifications'])) {
                    foreach ($specGroup['specifications'] as $spec) {
                        $productSpecification = $this->getProductSpecificationByName($spec['name'], $errors);
                        if (!$productSpecification) {
                            $errors[] = "Specification {$spec['name']} not found.";
                        }
                        $productSpecificationGroup = $this->getProductSpecificationGroupByName($specGroup['group_name'], $errors);
                        if (!$productSpecificationGroup) {
                            $errors[] = "Specification group {$specGroup['group_name']} not found.";
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
                $uom = $this->getUomId($uomData['uom']);
                if (!$uom) {
                    $errors[] = "UOM not found for {$uomData['uom']}";
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
        $allChecked = empty($attributeValues) ? 1 : 0;

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
            ->first();
            if (!$attributeGroup) {
                throw new Exception("AttributeGroup not found: {$attributeName}");
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
            ->first();
            
            if (!$productSpecificationGroup) {
                $errorMessage = "ProductSpecificationGroup not found for group name: {$groupName}";
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
            $uom = $this->getUomId($uomData['uom'], $errors);
            
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

     public function getLocationIds($countryName, $stateName, $cityName)
    {
        $countryId = null;
        $stateId = null;
        $cityId = null;

        if ($countryName) {
            $country = Country::where('name', $countryName)->first();
            $countryId = $country ? $country->id : null;
        }

        if ($stateName && $countryId) {
            $state = State::where('name', $stateName)->where('country_id', $countryId)->first();
            $stateId = $state ? $state->id : null;
        }

        if ($cityName && $stateId) {
            $city = City::where('name', $cityName)->where('state_id', $stateId)->first();
            $cityId = $city ? $city->id : null;
        }

        return [
            'country_id' => $countryId,
            'state_id' => $stateId,
            'city_id' => $cityId
        ];
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
            if (empty($address['city_id'])) {
                $errors["addresses.{$index}.city_id"] = 'City is required.';
            }
            if (empty($address['state_id'])) {
                $errors["addresses.{$index}.state_id"] = 'State is required.';
            }
            if (empty($address['country_id'])) {
                $errors["addresses.{$index}.country_id"] = 'Country is required.';
            }
            if (empty($address['pincode'])) {
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
        if ($shippingCount === 0) {
            $errors['addresses'] = 'At least one shipping address is required.';
        }
    
        // GST Validation
        $gstinNo = $data['compliance']['gstin_no'] ?? null;
        $companyName = $data['company_name'] ?? null;
        $gstinRegistrationDate = $data['compliance']['gstin_registration_date'] ?? null;
        $gstinLegalName = $data['compliance']['gst_registered_name'] ?? null;
    
        if ($gstinNo) {
            $gstValidation = EInvoiceHelper::validateGstNumber($gstinNo);
            if ($gstValidation['Status'] == 1) {
                $gstData = json_decode($gstValidation['checkGstIn'], true);
    
                if ($companyName && $companyName !== ($gstData['TradeName'] ?? '')) {
                    $errors['company_name'] = 'Company name does not match GSTIN record.';
                }
    
                if ($gstinLegalName && $gstinLegalName !== ($gstData['LegalName'] ?? '')) {
                    $errors['compliance.gst_registered_name'] = 'Legal name does not match GSTIN record.';
                }
    
                if (($gstData['DtReg'] ?? null) && $gstinRegistrationDate !== $gstData['DtReg']) {
                    $errors['compliance.gstin_registration_date'] = 'GSTIN registration date does not match GSTIN records.';
                }
    
                $this->addAddressValidationErrors($addresses, $gstData, $errors);
            } else {
                $errors['compliance.gstin_no'] = 'The provided GSTIN number is invalid. Please verify and try again.';
            }
        }
    
        return $errors;
    }

    private function addAddressValidationErrors($addresses, $gstData, &$errors)
    {
        $gstnHelper = new GstnHelper();
        foreach ($addresses as $index => $address) {
            if (!empty($address['state_id'])) {
                $stateValidation = $gstnHelper->validateStateCode(
                    $address['state_id'],
                    $gstData['StateCode'] ?? null
                );
                if (!$stateValidation['valid']) {
                    $errors["addresses.{$index}.state_id"] = $stateValidation['message'] ?? 'State does not match GSTIN records';
                }
            }
        }
    }
    

}
