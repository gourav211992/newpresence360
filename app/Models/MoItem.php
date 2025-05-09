<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class MoItem extends Model
{
    use HasFactory,DateFormatTrait;

    protected $table = 'erp_mo_items';

    protected $fillable = [
        'mo_id',
        'station_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'uom_id',
        'qty',
        'rate',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'so_id',
        'consumed_qty'
    ];

    protected $appends = [
        'mi_balance_qty',
        'value'
    ];

    public function getValueAttribute()
    {
        return $this->qty * $this->rate;
    }

    public function getQtnAttribute()
    {
        $formattedQty = sprintf("%.6f", (float) $this->attributes['qty']);
        return $formattedQty;
    }
    
    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function attributes()
    {
        return $this->hasMany(MoItemAttribute::class,'mo_item_id');
    }

    public function bomDetail()
    {
        return $this->belongsTo(BomDetail::class,'bom_detail_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class,'station_id');
    }

    public function mo()
    {
        return $this->belongsTo(MfgOrder::class,'mo_id');
    }
    
    public function header()
    {
        return $this->belongsTo(MfgOrder::class,'mo_id');
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
            $existingAttribute = MoItemAttribute::where('mo_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = $attribute -> attribute_id ? ($attribute -> attribute_id) : [];
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = MoItemAttribute::where('mo_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> id) -> first();
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

    public function getMiBalanceQtyAttribute()
    {
        return $this -> getAttribute('qty') - $this -> getAttribute('mi_qty');
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
        $stockType = $this -> getAttribute('rm_type') == 'sf' ? InventoryHelper::STOCK_TYPE_WIP : InventoryHelper::STOCK_TYPE_REGULAR; 
        $wipStationId = $stockType == InventoryHelper::STOCK_TYPE_WIP ? $this -> getAttribute('station_id') : null;
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId, $subStoreId, null, $stationId, $stockType, $wipStationId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return min($stockBalanceQty, $this -> qty);
    }
}
