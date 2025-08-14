<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoItemHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_so_items_history';

    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'so_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id', 'id');
    }

    public function item_attributes()
    {
        return $this -> belongsTo(ErpSoItemAttributeHistory::class, 'so_item_id');
    }

    public function itemAttributes()
    {
        return $this -> belongsTo(ErpSoItemAttributeHistory::class, 'so_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpSoItemAttributeHistory::class, 'so_item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
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
            $existingAttribute = ErpSoItemAttributeHistory::where('so_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = [];
            if ($attribute -> all_checked) {
                $attribute_ids = Attribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
            } else {
                $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            }
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute -> group ?-> short_name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = Attribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpSoItemAttributeHistory::where('so_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
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

    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleOrderTedHistory::class, 'so_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Discount');
    }
    public function tax_ted()
    {
        return $this -> hasMany(ErpSaleOrderTedHistory::class, 'so_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Tax');
    }
    public function item_deliveries()
    {
        return $this -> hasMany(ErpSoItemDeliveryHistory::class, 'so_item_id', 'id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpSaleOrderHistory::class, 'sale_order_id');
    }
    public function quotation()
    {
        return $this -> belongsTo(ErpSaleOrder::class, 'order_quotation_id');
    }
    public function getBalanceQtyAttribute()
    {
        $totalQty = $this -> getAttribute('order_qty');
        $usedQty = $this -> getAttribute('invoice_qty');
        $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        return $balanceQty;
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = ErpSaleOrderTedHistory::where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = ErpSaleOrderTedHistory::with(['taxDetail'])
            ->where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getSgstValueAttribute()
    {
        $tedRecords = ErpSaleOrderTedHistory::where('so_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleOrderTedHistory::with(['taxDetail'])
            ->where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getIgstValueAttribute()
    {
        $tedRecords = ErpSaleOrderTedHistory::where('so_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleOrderTedHistory::with(['taxDetail'])
            ->where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getStockBalanceQty()
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
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,null,null,null,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return $stockBalanceQty;
    }

    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }
    public function custom_bom_details()
    {
        return $this -> hasMany(ErpSoItemBomHistory::class,'so_item_id');
    }
}
