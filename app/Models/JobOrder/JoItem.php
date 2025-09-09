<?php

namespace App\Models\JobOrder;

use App\Helpers\InventoryHelper;
use App\Models\ErpSaleOrder;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Attribute;
use App\Models\Unit;
use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoItem extends Model
{
    use HasFactory,DateFormatTrait;
    protected $table = 'erp_jo_items';
    protected $fillable = [
        'jo_id',
        'so_id',
        'bom_detail_id',
        'station_id',
        'rm_type',
        'item_id',
        'service_item_id',
        'item_code',
        'uom_id',
        'qty',
        'consumed_qty',
        'rate',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty'
    ];
    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'sow' => 'service_item_id',
        'inventoryUom' => 'inventory_uom_id'
    ];
    protected $appends = [
        // 'cgst_value',
        // 'sgst_value',
        // 'igst_value',
        'mi_balance_qty'
    ];
    public function jo() 
    {
        return $this->belongsTo(JobOrder::class, 'jo_id');
    }
    public function header() 
    {
        return $this->belongsTo(JobOrder::class, 'jo_id');
    }
    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
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
    public function sow()
    {
        return $this->belongsTo(Item::class, 'service_item_id');
    }
    public function attributes()
    {
        return $this->hasMany(JoItemAttribute::class,'jo_item_id')->with(['headerAttribute', 'headerAttributeValue']);
    }
    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = JoItemAttribute::where('jo_item_id', $this->getAttribute('id'))
        ->select(['item_attribute_id as attribute_id', 'attribute_value as attribute_value_id'])
        ->get()
        ->toArray();
        foreach ($itemAttributes as $attribute) {
            $attributeIds = is_array($attribute->attribute_id) ? $attribute->attribute_id : [$attribute->attribute_id];
            $attribute->group_name = $attribute->group?->name;
            $valuesData = [];
            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = Attribute::where('id', $attributeValueId)
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
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId, $subStoreId, null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        // return min($stockBalanceQty, $this -> qty);
        return $stockBalanceQty;
    }

    public function getMiBalanceQtyAttribute()
    {
        $currentQty = $this -> getAttribute('qty');
        $miQty = $this -> getAttribute('mi_qty');
        return $currentQty - $miQty;
    }
}
