<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoItemHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_po_items_history';

    protected $fillable = [
        'purchase_order_id',
        'so_item_id',
        'pi_item_id',
        'source_id',
        'item_id',
        'item_code',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'grn_qty',
        'ge_qty',
        'asn_qty',
        'short_close_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'item_discount_amount',
        'header_discount_amount',
        'tax_amount',
        'expense_amount',
        'company_currency_id',
        'company_currency_exchange_rate',
        'group_currency_id',
        'group_currency_exchange_rate',
        'remarks',
        'delivery_date'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];
    
    protected $appends = [
        'cgst_value',
        'sgst_value',
        'igst_value'
    ];

    protected $casts = ['so_item_id' => 'array'];

    public function po()
    {
        return $this->belongsTo(PurchaseOrderHistory::class, 'purchase_order_id');
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

    public function mrn_details()
    {
        return $this->hasMany(MrnDetailHistory::class,'purchase_order_item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    // After item discount and header discount
    public function getAssessmentAmountTotalAttribute()
    {
        return ($this->order_qty * $this->rate) - ($this->item_discount_amount - $this->header_discount_amount);
    }

    public function getAssessmentAmountItemAttribute()
    {
        return ($this->order_qty * $this->rate) - ($this->item_discount_amount);
    }

    // After item discount
    public function getAssessmentAmountHeaderAttribute()
    {
        return ($this->order_qty * $this->rate) - ($this->item_discount_amount);
    }

    public function getTotalItemValueAttribute()
    {
        return ($this->order_qty * $this->rate);
    }

    public function getTotalDiscValueAttribute()
    {
        return ($this->item_discount_amount + $this->header_discount_amount);
    }

    public function attributes()
    {
        return $this->hasMany(PoItemAttributeHistory::class,'po_item_id');
    }

        public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = PoItemAttributeHistory::where('po_item_id', $this->getAttribute('id'))
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

    public function itemDelivery()
    {
        return $this->hasMany(PoItemDeliveryHistory::class,'po_item_id');
    }

    /*Detail level*/
    public function itemDiscount()
    {
        return $this->hasMany(PurchaseOrderTedHistory::class,'po_item_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PurchaseOrderTedHistory::class)->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(PurchaseOrderTedHistory::class,'po_item_id')->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = PurchaseOrderTedHistory::where('po_item_id', $this->id)
            ->where('purchase_order_id', $this->purchase_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = PurchaseOrderTedHistory::with(['taxDetail'])
            ->where('po_item_id', $this->id)
            ->where('purchase_order_id', $this->purchase_order_id)
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
        $tedRecords = PurchaseOrderTedHistory::where('po_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = PurchaseOrderTedHistory::with(['taxDetail'])
            ->where('po_item_id', $this->id)
            ->where('purchase_order_id', $this->purchase_order_id)
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
        $tedRecords = PurchaseOrderTedHistory::where('po_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = PurchaseOrderTedHistory::with(['taxDetail'])
            ->where('po_item_id', $this->id)
            ->where('purchase_order_id', $this->purchase_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getShortBalQtyAttribute()
    {
        $maxQty = max((float) $this->invoice_quantity, (float) $this->grn_qty);
        $balance = (float) $this->order_qty - $maxQty;
        return $balance; 
    }

    public function supplierPoItems()
    {
        return $this->hasMany(PoItemHistory::class, 'po_item_id');
    }
}
