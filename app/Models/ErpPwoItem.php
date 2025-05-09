<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPwoItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'pwo_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'manf_order_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'so_id'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id',
    ];
    protected $appends = [
        'mi_balance_qty',
        'qty'
    ];

    public function header()
    {
        return $this->belongsTo(ErpProductionWorkOrder::class, 'pwo_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
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
            $existingAttribute = ErpPwoItemAttribute::where('pwo_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
            $attribute -> short_name = $attribute -> group ?-> short_name;
            $attributesArray = array();
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach (isset($attribute_ids) ? $attribute_ids : [] as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                if (isset($attributeValueData))
                {
                    $isSelected = ErpPwoItemAttribute::where('pwo_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
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
    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function order_item()
    {
        return $this->belongsTo(ErpSoItem::class, 'so_item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }
    public function attributes()
    {
        return $this->hasMany(ErpPwoItemAttribute::class,'pwo_item_id');
    }

    public function mapping()
    {
        return $this->hasMany(PwoSoMapping::class,'pwo_item_id');
    }

    public function mappedids()
    {
        return $this->mapping() // Select only needed columns
        ->get()
        ->pluck('so_item_id') // Extract only so_item_id values
        ->unique() // Remove duplicate values
        ->values()->toArray();
    }
    public function getMiBalanceQtyAttribute()
    {
        return max($this -> order_qty - $this -> mi_qty, 0);
    }
    public function getQtyAttribute()
    {
        return ($this -> order_qty);
    }
    public function getAvlStock($storeId, $subStoreId = null, $stationId = null)
    {
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId,$subStoreId,NULL, $stationId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return min($stockBalanceQty, $this -> qty);
    }
}   
