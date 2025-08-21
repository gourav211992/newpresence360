<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PslipBomConsumption extends Model
{
    use HasFactory;

    protected $table = 'erp_pslip_bom_consumptions';

    protected $fillable = [
        'pslip_id',
        'pslip_item_id',
        'bom_id',
        'bom_detail_id',
        'so_id',
        'so_item_id',
        'item_id',
        'item_code',
        'attributes',
        'uom_id',
        'qty',
        'consumption_qty',
        'inventory_uom_qty',
        'station_id',
        'section_id',
        'sub_section_id',
        'rate',
        'rm_type'
    ];

    protected $casts = ['attributes' => 'array'];

    public function getItemValueAttribute()
    {
        return $this->consumption_qty * $this->rate;
    }

    public function pslip()
    {
        return $this->belongsTo(ErpProductionSlip::class,'pslip_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class,'so_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class,'uom_id');
    }

    public function pslip_item()
    {
        return $this->belongsTo(ErpPslipItem::class,'pslip_item_id');
    }

    

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = $this->getAttribute('attributes') ?? [];
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
                            $itemAttr['attribute_value'] == $attributeValueData->id;
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

    public function getAvlStockAttribute()
    {
        $selectedAttributeIds = [];
        $itemAttributes = $this->getAttribute('attributes');
        foreach ($itemAttributes as $itemAttr) {
            $selectedAttributeIds[] = $itemAttr['attribute_value'];
        }
        // ($itemId, $selectedAttr=null, $uomId=null, $storeId=null, $subStoreId=null, $orderId=null, $stationId = null)
        $storeId = $this->pslip_item->mo_product->mo->store_id ?? null;
        $subStoreId = $this->pslip_item->mo_product->mo->sub_store_id ?? null;
        $stationId = $this->pslip_item->mo_product->mo->station_id ?? null;
        // dd($this->pslip_item);
        $rm_type = 'R';
        $itemWipStationId = null;
        if($this->rm_type =='sf') {
            $rm_type = 'W';
            $itemWipStationId = $this->station_id;
        }
        $stocks = InventoryHelper::totalInventoryAndStock($this->item_id, $selectedAttributeIds, $this->uom_id, $storeId,$subStoreId,null,$stationId,$rm_type, $itemWipStationId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }

        return $stockBalanceQty;
    }
}
