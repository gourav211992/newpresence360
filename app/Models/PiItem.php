<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiItem extends Model
{
    use HasFactory;

    protected $table = 'erp_pi_items';

    protected $fillable = [
        'pi_id',
        'so_id',
        'item_id',
        'item_code',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'mi_qty',
        'indent_qty',
        'rfq_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'vendor_id',
        'vendor_code',
        'vendor_name',
        'remarks',
        'adjusted_qty',
        'required_qty'
    ];

    protected $appends = [
        'mi_balance_qty',
        'rfq_balance_qty',
        'qty'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id',
        'vendor' => 'vendor_id',
    ];
    
    public function pi()
    {
        return $this->belongsTo(PurchaseIndent::class, 'pi_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function header()
    {
        return $this->belongsTo(PurchaseIndent::class, 'pi_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
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

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function attributes()
    {
        return $this->hasMany(PiItemAttribute::class,'pi_item_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = PiItemAttribute::where('pi_item_id', $this->getAttribute('id'))
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
            ];
        }
        return collect($processedData);
    }
    
    public function po_item()
    {
        return $this->hasOne(PoItem::class,'pi_item_id','id');
    }

    public function po_items()
    {
        return $this->hasMany(PoItem::class,'pi_item_id');
    }
    
    public function getBalenceQtyAttribute()
    {
        return $this->indent_qty - ($this->order_qty ?? 0);
    }

    public function so_pi_mapping_item()
    {
        return $this->hasMany(PiSoMappingItem::class,'pi_item_id');
    }
    
    public function getMiBalanceQtyAttribute()
    {
        return max(($this->indent_qty) - $this->mi_qty, 0);
    }
    public function getRfqBalanceQtyAttribute()
    {
        return max(($this->indent_qty) - $this->rfq_qty, 0);
    }

    # Use For MI
    public function getAvlStock($storeId, $subStoreId = null, $stationId = null)
    {
        $selectedAttributeIds = [];
        $itemAttributes = $this->item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($this->item_id, $selectedAttributeIds, $this->uom_id, $storeId, $subStoreId, NULL, $stationId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return $stockBalanceQty;
    }

    public function getAvlStockForPi($storeId = null)
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
        $storeId = $storeId ? $storeId : $this->pi->store_id;
        $stocks = InventoryHelper::totalInventoryAndStock($this->item_id, $selectedAttributeIds, $this->uom_id, $storeId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return $stockBalanceQty;
    }

    public function getQtyAttribute()
    {
        return $this -> indent_qty;
    }

    public function getPendingPoAttribute()
    {   
        $itemId       = $this->item_id;
        $selectedAttr = $this->attributes()->get();
        $uomId        = $this->uom_id;
        $storeId      = $this?->pi?->store_id;
        return InventoryHelper::getPendingPo($itemId, $uomId, $selectedAttr, $storeId);
    }

}
