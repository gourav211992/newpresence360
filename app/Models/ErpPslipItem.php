<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPslipItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pslip_id',
        'item_id',
        'so_item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'store_id',
        'qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'customer_id',
        'order_id',
        'item_discount_amount',
        'header_discount_amount',
        'item_expense_amount',
        'header_expense_amount',
        'tax_amount',
        'total_item_amount',
        'remarks',
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'mi_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $hidden = ['deleted_at'];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id', 'id');
    }
    public function so_item()
    {
        return $this -> belongsTo(ErpSoItem::class, 'so_item_id', 'id');
    }
    public function customer()
    {
        return $this -> belongsTo(Customer::class, 'customer_id');
    }

    public function item_attributes()
    {
        return $this -> belongsTo(ErpPslipItemAttribute::class, 'pslip_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpPslipItemAttribute::class, 'pslip_item_id');
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
            $itemAttributes = ItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = ErpPslipItemAttribute::where('pslip_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
                        $isSelected = ErpPslipItemAttribute::where('pslip_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
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
    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }
    public function to_item_locations()
    {
        return $this -> hasMany(ErpPslipItemLocation::class, 'pslip_item_id', 'id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpProductionSlip::class, 'pslip_id');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function bundles()
    {
        return $this -> hasMany(ErpPslipItemDetail::class, 'pslip_item_id');
    }
    public function getStockBalanceQty($storeId = null)
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
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,$storeId,null,null,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        // $stockBalanceQty = $this -> getAttribute('inventory_uom_qty');
        $stockBalanceQty = ItemHelper::convertToAltUom($this -> getAttribute(('item_id')), $this -> getAttribute('uom_id'), $stockBalanceQty);
        return $stockBalanceQty;
        // return $this -> getAttribute('order_qty');
    }
}
