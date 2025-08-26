<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwoBomMapping extends Model
{
    use HasFactory;
    protected $table = 'erp_pwo_bom_mapping';
    protected $fillable = [
        'pwo_id',
        'pwo_mapping_id',
        'bom_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'item_code',
        'attributes',
        'uom_id',
        'bom_qty',
        'qty',
        'rate',
        'station_id',
        'section_id',
        'sub_section_id',
        'so_id'
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function bomDetail()
    {
        return $this->belongsTo(BomDetail::class, 'bom_detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class,'uom_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class,'so_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $attributesArray = array();
            $attribute_ids = $attribute -> attribute_id ? ($attribute -> attribute_id) : [];
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $mappingAttributes = $this->getAttribute('attributes');
                        $isSelected = array_filter($mappingAttributes, function($itemAttr) use($attribute, $attributeValueData) {
                            return ($itemAttr['attribute_id'] == $attribute -> id && $itemAttr['attribute_value'] == $attributeValueData -> value);
                        });
                        $attributeValueData -> selected = count($isSelected) ? true : false;
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
}
