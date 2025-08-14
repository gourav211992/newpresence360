<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'item_id',
        'hsn_code',
        'transaction_uom_id',
        'inventory_uom_id',
        'quantity',
        'inventory_quantity',
        'invoiced_quantity',
        'rate',
        'basic_value',
        'discount_percentage',
        'discount_amount',
        'header_discount_percentage',
        'header_discount_amount',
        'expense_percentage',
        'expense_amount',
        'header_expense_percentage',
        'header_expense_amount',
        'net_value',
        'sgst_percentage',
        'cgst_percentage',
        'igst_percentage',
        'tax_value',
        'taxable_amount',
        'sub_total',
    ];

    public function item()
    {
        return $this -> belongsTo(ErpItem::class, 'item_id', 'id');
    }

    public function item_attributes()
    {
        return $this -> belongsTo(ErpSaleOrderItemAttribute::class, 'sale_order_item_id');
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
            foreach ($attribute_ids as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                if (isset($attributeValueData))
                {
                    $attributeValueData -> selected = false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
           $attribute -> values_data = $attributesArray;
           $attribute -> only(['id','group_name', 'values_data']);
        }
        return $itemAttributes;
    }

    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleOrderMrnTed::class, 'sale_order_item_id', 'id') -> where('level', 'Detail') -> where('type', 'Discount');
    }
    public function item_deliveries()
    {
        return $this -> hasMany(ErpSaleOrderItemDelivery::class, 'sale_order_item_id', 'id');
    }
}
