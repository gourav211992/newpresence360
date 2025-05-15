<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMrItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'material_return_id',
        'mi_item_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'store_id',
        'store_code',
        'to_store_id',
        'to_store_code',
        'from_sub_store_id',
        'from_sub_store_code',
        'to_sub_store_id',
        'to_sub_store_code',
        'user_id',
        'user_name',
        'department_id',
        'department_code',
        'qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'item_discount_amount',
        'header_discount_amount',
        'item_expense_amount',
        'header_expense_amount',
        'tax_amount',
        'total_item_amount',
        'remarks',
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'mr_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $hidden = ['deleted_at'];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id', 'id');
    }

    public function issue_item()
    {
        return $this -> belongsTo(ErpMiItem::class, 'mi_item_id', 'id');
    }
    public function item_attributes()
    {
        return $this -> hasMany(ErpMrItemAttribute::class, 'mr_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpMrItemAttribute::class, 'mr_item_id');
    }
    
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = ErpMrItemAttribute::where('mr_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = [];
            if ($attribute -> all_checked) {
                $attribute_ids = ErpAttribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
            } else {
                $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            }
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute->group?->short_name;

            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpMrItemAttribute::where('mr_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }
                
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'short_name', 'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'short_name' => $attribute['short_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }
    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }
    public function item_locations()
    {
        return $this -> hasMany(ErpMrItemLocation::class, 'mr_item_id', 'id');
    }
    public function to_item_locations()
    {
        return $this -> hasMany(ErpMrItemLocation::class, 'mr_item_id', 'id') -> where('type', 'to');
    }
    public function from_item_locations()
    {
        return $this -> hasMany(ErpMrItemLocation::class, 'mr_item_id', 'id') -> where('type', 'from');
    }
    public function header()
    {
        return $this -> belongsTo(ErpMaterialReturnHeader::class, 'material_return_id');
    }

    public function erpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function toErpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'to_store_id');
    }
    public function erpSubstore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function toErpSubStore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'to_sub_store_id');
    }
    public function erpStation()
    {
        return $this -> belongsTo(Station::class, 'station_id');
    }
    public function toErpStation()
    {
        return $this -> belongsTo(Station::class, 'to_station_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
    public function erpMrItemLot()
    {
        return $this->hasMany(ErpMrItemLot::class, 'mr_item_id', 'id');
    }
    public function requester_name()
    {
        $firstItem = $this;

        if ($firstItem?->department_id) {
            $modelType = "Department";
        } elseif ($firstItem?->user_id) {
            $modelType = "User";
        } else {
            $modelType = "";
        }

        if (!$modelType) {
            return null;
        }

        // Map type to actual model class
        $modelMap = [
            'User' => \App\Models\AuthUser::class,
            'Department' => \App\Models\Department::class,
            // Add other mappings as needed
        ];

        $modelClass = $modelMap[$modelType] ?? "App\\Models\\$modelType";
        if (!class_exists($modelClass)) {
            return null;
        }

        $foreignKey = strtolower($modelType) . '_id';
        
        if (!isset($this->$foreignKey)) {
            return null;
        }
        return optional($modelClass::find($this->$foreignKey))->name;
    }
}
