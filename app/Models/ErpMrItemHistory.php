<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMrItemHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "erp_mr_items_history";

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

    public function item_attributes()
    {
        return $this -> belongsTo(ErpMrItemAttributeHistory::class, 'mr_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpMrItemAttributeHistory::class, 'mr_item_id');
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
            $existingAttribute = ErpMrItemAttributeHistory::where('mr_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpMrItemAttributeHistory::where('mr_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }
                
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);
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
        return $this -> hasMany(ErpMrItemLocationHistory::class, 'mr_item_id', 'id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpMaterialReturnHeaderHistory::class, 'material_return_id');
    }

}
