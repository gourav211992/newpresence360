<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ErpPlItemDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'pl_header_id',
        'order_id',
        'order_item_id',
        'order_item_delivery_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'picked_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'delivery_date',
        'rate',
        'total_amount',
        'remarks',
    ];
    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function item_attributes()
    {
        return $this->hasMany(ErpPlItemAttribute::class, 'pl_item_id');
    }
    public function attributes()
    {
        return $this->hasMany(ErpPlItemAttribute::class, 'pl_item_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this->item_id)->get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = ErpPlItemAttribute::where('pl_item_id', $this->getAttribute('id'))
                ->where('item_attribute_id', $attribute->id)
                ->first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = [];
            $attribute_ids = [];
            if ($attribute->all_checked) {
                $attribute_ids = ErpAttribute::where('attribute_group_id', $attribute->attribute_group_id)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            } else {
                $attribute_ids = $attribute->attribute_id ? json_decode($attribute->attribute_id) : [];
            }
            $attribute->group_name = $attribute->group?->name;
            $attribute->short_name = $attribute->group?->short_name;
            foreach ($attribute_ids as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue)
                    ->select('id', 'value')
                    ->where('status', 'active')
                    ->first();
                if (isset($attributeValueData)) {
                    $isSelected = ErpPlItemAttribute::where('pl_item_id', $this->getAttribute('id'))
                        ->where('item_attribute_id', $attribute->id)
                        ->where('attribute_value', $attributeValueData->value)
                        ->first();
                    $attributeValueData->selected = $isSelected ? true : false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
            $attribute->values_data = $attributesArray;
            $attribute = $attribute->only(['id', 'group_name', 'short_name', 'values_data', 'attribute_group_id']);
            array_push($processedData, [
                'id' => $attribute['id'],
                'group_name' => $attribute['group_name'],
                'values_data' => $attributesArray,
                'attribute_group_id' => $attribute['attribute_group_id'],
                'short_name' => $attribute['short_name'],
            ]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }
    public function soItem()
    {
        return $this->belongsTo(ErpSoItem::class,'order_item_id');
    }
    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'order_id');
    }
    public function sale_order()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'order_id');
    }
    public function header(){
        return $this->belongsTo(ErpPlHeader::class, 'pl_header_id');
    }
    public function uom(){
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function getAvailableStocks($storeId, $subStoreId)
    {
        $itemId = $this -> getAttribute('item_id');
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds, $this -> getAttribute('uom_id'), $storeId, $subStoreId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return $stockBalanceQty;
    }

    public function getStockBalanceQty($storeId = null, $subStoreId = null)
    {
        $itemId = $this -> getAttribute('item_id');
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,null,$storeId, $subStoreId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        $stockBalanceQty = ItemHelper::convertToAltUom($this -> getAttribute(('item_id')), $this -> getAttribute('uom_id'), (float)$stockBalanceQty);
        return $stockBalanceQty;
    }
}
