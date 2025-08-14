<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleReturnItemHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_sale_return_items_histories';
    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'si_item_id',
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
        return $this -> belongsTo(ErpSaleReturnItemAttributeHistory::class, 'sale_return_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpSaleReturnItemAttributeHistory::class, 'sale_return_item_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        foreach ($itemAttributes as $attribute) {
            $attributesArray = array();
            $attribute_ids = json_decode($attribute -> attribute_id);
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach (isset($attribute_ids) ? $attribute_ids : [] as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                if (isset($attributeValueData))
                {
                    $isSelected = ErpSaleReturnItemAttributeHistory::where('sale_return_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                    $attributeValueData -> selected = $isSelected ? true : false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
           $attribute -> values_data = $attributesArray;
           $attribute -> only(['id','group_name', 'values_data', 'attribute_group_id']);
        }
        return $itemAttributes;
    }

    public function discount_ted()
    {   
        return $this -> hasMany(ErpSaleReturnTedHistory::class, 'sale_return_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Discount');
    }
    public function tax_ted()
    {
        return $this -> hasMany(ErpSaleReturnTedHistory::class, 'sale_return_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Tax');
    }
    public function item_locations()
    {
        return $this -> hasMany(ErpSaleReturnItemLocationHistory::class, 'sale_return_item_id', 'id');
    }
    public function sale_invoice()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'sale_invoice_id');
    }
    public function return()
    {
        return $this -> belongsTo(ErpSaleReturn::class, 'source_id');
    }
    public function dn_cum_invoice()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'dn_id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpSaleReturnHistory::class, 'sale_return_id');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = ErpSaleReturnTedHistory::where('return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = ErpSaleReturnTedHistory::with(['taxDetail'])
            ->where('return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
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
        $tedRecords = ErpSalereturnTedHistory::where('return_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSalereturnTedHistory::with(['taxDetail'])
            ->where('return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
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
        $tedRecords = ErpSalereturnTedHistory::where('return_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSalereturnTedHistory::with(['taxDetail'])
            ->where('return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getBalanceQtyAttribute()
    {
        $totalQty = $this -> getAttribute('order_qty');
        $usedQty = $this -> getAttribute('invoice_qty');
        $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        return $balanceQty;
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

}
