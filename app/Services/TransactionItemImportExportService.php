<?php
namespace App\Services;

use Exception;

use App\Models\Hsn;
use App\Models\Item;
use App\Models\Unit;
use App\Models\City;
use App\Models\Group;
use App\Models\State;
use App\Models\Ledger;
use App\Models\Vendor;
use App\Models\Country;
use App\Models\SubType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Attribute;
use App\Models\PaymentTerm;
use App\Models\ErpSubStore;
use App\Models\AlternateUOM;
use App\Models\ItemAttribute;
use App\Models\AttributeGroup;
use App\Models\OrganizationType;
use App\Models\UploadItemMaster;
use App\Models\ItemSpecification;
use Illuminate\Support\Facades\Log;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationDetail;

class TransactionItemImportExportService
{
    public function validateItem($itemCode)
    {
        $item = Item::withDefaultGroupCompanyOrg()
              ->where('item_code', $itemCode)
              ->first();
        if (!$item) {
            throw new Exception("Item Code not found: {$itemCode}");
        }
        return $item;
    }

    public function validateUom($uomName)
    {
        $uom = Unit::withDefaultGroupCompanyOrg()
               ->where('name', $uomName)
               ->first();
        if (!$uom) {
            throw new Exception("UOM not found: {$uomName}");
        }
        return $uom;
    }

    public function validateAttribute($item, $row, int $index): array
    {
        $attribute = null;
        $groupName = $row["attribute_name_{$index}"] ?? null;
        $valueName = $row["attribute_value_{$index}"] ?? null;
        if (!$groupName) return [];
        $group = AttributeGroup::withDefaultGroupCompanyOrg()->where('name', $groupName)->first();
        if (!$group) {
            return ['error' => "Attr {$index} group not found"];
        }
        $attr = Attribute::where('value', $valueName)->where('attribute_group_id', $group->id)->first();
        if (!$attr) {
            return ['error' => "Attr {$index} value not found"];
        }
        if ($item && $group) {
            $itemAttr = ItemAttribute::where('item_id', $item->id)->where('attribute_group_id', $group->id)->first();
            if (!$itemAttr) {
                return ['error' => "Attr {$index} not mapped to item"];
            }
            $attrIds = $itemAttr->all_checked
                ? Attribute::where('attribute_group_id', $group->id)->pluck('id')->toArray()
                : (array) $itemAttr->attribute_id;
            if (!in_array($attr->id, $attrIds)) {
                return ['error' => "Attr {$index} value not mapped with item"];
            }
            $attribute = [
                'item_attribute_id' => $itemAttr->id,
                'attribute_name_id' => $group->id,
                'attribute_value_id' => $attr->id,
            ];
        }
        return ['attribute' => $attribute];
    }

    public function validateItemAttributes($attributes, &$errors)
    {
        if ($attributes) {
            foreach ($attributes as $attribute) {
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

    public function getStore($locationId, $store)
    {
        $hsn = ErpSubStore::withDefaultGroupCompanyOrg()
              ->where('name', $store)
              ->first();
        if (!$hsn) {
            throw new Exception("Item Code not found: {$store}");
        }
        return $hsn->id;
    }

}
