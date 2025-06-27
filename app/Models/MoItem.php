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
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = MoItemAttribute::where('mo_item_id', $this->getAttribute('id'))
        ->select(['item_attribute_id as attribute_id', 'attribute_value as attribute_value_id'])
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
                'short_name' => $attribute->short_name
            ];
        }
        return collect($processedData);
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
        // return min($stockBalanceQty, $this -> qty);
        return $stockBalanceQty;
    }
}
