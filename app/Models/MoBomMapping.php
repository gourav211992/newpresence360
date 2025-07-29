<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\returnCallback;

class MoBomMapping extends Model
{
    use HasFactory;

    protected $table = 'erp_mo_bom_mapping';

    protected $fillable = [
        'mo_id',
        'mo_product_id',
        'old_mo_product_id',
        'so',
        'bom_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'uom_id',
        'attributes',
        'rm_type',
        'bom_qty',
        'consumption_qty',
        'station_id',
        'section_id',
        'sub_section_id'
    ];
    
    protected $casts = ['attributes' => 'array'];

    public function getRequiredQtyAttribute()
    {
        $a = $this->mo_product->qty - $this->mo_product->pslip_qty-$this->mo_product->short_closed_qty;
        return $this->bom_qty*$a;
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function mo()
    {
        return $this->belongsTo(MfgOrder::class, 'mo_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function mo_product()
    {
        return $this->belongsTo(MoProduct::class, 'mo_product_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function bom_detail()
    {
        return $this->belongsTo(BomDetail::class, 'bom_detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
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
        $storeId = $this->mo_product->mo->store_id ?? null;
        $subStoreId = $this->mo_product->mo->sub_store_id ?? null;
        $stationId = $this->mo_product->mo->station_id ?? null;
        $rm_type = 'R';
        $itemWipStationId = null;
        if($this->rm_type =='sf') {
            $rm_type = 'W';
            $itemWipStationId = $this->station_id;
        }
        $soItemId = $this?->mo_product?->so_item_id;
        $stocks = InventoryHelper::totalInventoryAndStock($this->item_id, $selectedAttributeIds, $this->uom_id, $storeId,$subStoreId,$soItemId,$stationId, $rm_type, $itemWipStationId);
        $stockBalanceQty = 0;
        if (isset($stocks)) {
            $stockBalanceQty = $stocks['confirmedStocks'] - $stocks['reservedStocks'];
        }

        return $stockBalanceQty;
    }

}
