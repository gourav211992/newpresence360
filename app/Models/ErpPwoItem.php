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
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = ErpPwoItemAttribute::where('pwo_item_id', $this->getAttribute('id'))
        ->select(['item_attribute_id as attribute_id', 'attribute_id as attribute_value_id'])
        ->get()
        ->toArray();
        foreach ($itemAttributes as $attribute) {
            $attributeIds = is_array($attribute->attribute_id) ? $attribute->attribute_id : [$attribute->attribute_id];
            $attribute->group_name = $attribute->group?->name;
            $valuesData = [];
            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = ErpAttribute::where('id', $attributeValueId)
                    ->where('status', 'active')
                    ->select('id', 'value')
                    ->first();
                if ($attributeValueData) {
                    $isSelected = collect($mappingAttributes)->contains(function ($itemAttr) use ($attribute, $attributeValueData) {
                        return $itemAttr['attribute_id'] == $attribute->id &&
                            $itemAttr['attribute_value_id'] == $attributeValueData->id;
                    });
                    $attributeValueData->selected = $isSelected;
                    $valuesData[] = $attributeValueData;
                }
            }
            $processedData[] = [
                'id' => $attribute->id,
                'group_name' => $attribute->group_name,
                'values_data' => $valuesData,
                'attribute_group_id' => $attribute->attribute_group_id,
            ];
        }
        return collect($processedData);
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
