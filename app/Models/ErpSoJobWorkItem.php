<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoJobWorkItem extends Model
{
    use HasFactory;
    protected $table = 'erp_so_job_work_items';
    protected $fillable = [
        'sale_order_id',
        'so_item_id',
        'jo_id',
        'bom_detail_id',
        'station_id',
        'rm_type',
        'item_id',
        'item_code',
        'uom_id',
        'qty',
        'grn_qty',
        'consumed_qty',
        'rate',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function header()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'sale_order_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpSoJobWorkItemAttribute::class,'job_work_item_id')->with(['headerAttribute', 'headerAttributeValue']);
    }

    public function so_item()
    {
        return $this->belongsTo(ErpSoItem::class, 'so_item_id');
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
            $existingAttribute = ErpSoJobWorkItemAttribute::where('job_work_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = [];
            if ($attribute -> all_checked) {
                $attribute_ids = Attribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
            } else {
                $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            }
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute -> group ?-> short_name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = Attribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpSoJobWorkItemAttribute::where('job_work_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }

            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'short_name' ,'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id'],'short_name' => $attribute['short_name']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}
