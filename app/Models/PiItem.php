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
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'vendor_id',
        'vendor_code',
        'vendor_name',
        'remarks'
    ];

    protected $appends = [
        'mi_balance_qty',
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
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = PiItemAttribute::where('pi_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
            $attributesArray = array();
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach (isset($attribute_ids) ? $attribute_ids : [] as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                if (isset($attributeValueData))
                {
                    $isSelected = PiItemAttribute::where('pi_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> id) -> first();
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

    public function po_item()
    {
        return $this->hasOne(PoItem::class,'pi_item_id','id');
    }

    public function po_items()
    {
        return $this->hasMany(PoItem::class,'pi_item_id');
    }

    public function itemDelivery()
    {
        return $this->hasMany(PiItemDelivery::class,'pi_item_id');
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
        return max(($this -> indent_qty) - $this -> mi_qty, 0);
    }

    public function getAvlStock($storeId = null)
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
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId,null,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return min($stockBalanceQty, $this -> indent_qty);
    }
    public function getQtyAttribute()
    {
        return $this -> indent_qty;
    }
}
