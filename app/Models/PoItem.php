<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    use HasFactory,DateFormatTrait;

    protected $table = 'erp_po_items';

    protected $fillable = [
        'purchase_order_id',
        'so_id',
        'pi_item_id',
        'po_item_id',
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
        'igst_value',
        'inter_org_so_bal_qty'
    ];

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
    public function header()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function pi_item()
    {
        return $this->belongsTo(PiItem::class, 'pi_item_id');
    }

    public function po_item()
    {
        return $this->belongsTo(PoItem::class, 'po_item_id');
    }

    public function si_item()
    {
        return $this->hasOne(PoItem::class, 'po_item_id');
    }

    public function mrn_details()
    {
        return $this->hasMany(MrnDetail::class,'purchase_order_item_id');
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

    public function items()
    {
        return $this->hasOne(Item::class, 'item_id');
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
        return $this->hasMany(PoItemAttribute::class,'po_item_id')->with(['headerAttribute', 'headerAttributeValue']);
    }

    public function teds()
    {
        return $this->hasMany(PurchaseOrderTed::class,'po_item_id');
    }

    public function ted_tax()
    {
        return $this->hasOne(PurchaseOrderTed::class,'po_item_id')->where('ted_type','Tax')->latest();
    }

    public function itemDelivery()
    {
        return $this->hasMany(PoItemDelivery::class,'po_item_id');
    }

    /*Detail level*/
    public function itemDiscount()
    {
        return $this->hasMany(PurchaseOrderTed::class,'po_item_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }
    public function discount_ted()
    {
        return $this->hasMany(PurchaseOrderTed::class,'po_item_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PurchaseOrderTed::class)->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(PurchaseOrderTed::class,'po_item_id')->where('ted_type','Tax');
    }
    public function tax_ted()
    {
        return $this->hasMany(PurchaseOrderTed::class,'po_item_id')->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = PurchaseOrderTed::where('po_item_id', $this->id)
            ->where('purchase_order_id', $this->purchase_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = PurchaseOrderTed::with(['taxDetail'])
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
        $tedRecords = PurchaseOrderTed::where('po_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = PurchaseOrderTed::with(['taxDetail'])
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
        $tedRecords = PurchaseOrderTed::where('po_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = PurchaseOrderTed::with(['taxDetail'])
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
        $maxQty = max($this->invoice_quantity,$this->grn_qty);
        // $maxQty = max((float) $this->invoice_quantity, (float) $this->grn_qty) - (float) $this->short_close_qty;
        $balance = max(($this->order_qty - $maxQty - $this->short_close_qty),0);
        return $balance;
    }

    public function supplierPoItems()
    {
        return $this->hasMany(PoItem::class, 'po_item_id');
    }

    public function pi_item_mappings()
    {
        return $this->hasMany(PiPoMapping::class,'po_item_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = PoItemAttribute::where('po_item_id', $this->getAttribute('id'))
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

    public function getInterOrgSoBalQtyAttribute()
    {
        return ($this->order_qty - $this->inter_org_so_qty);
    }

    public function getPendingPoAttribute()
    {
        $itemId       = $this->item_id;
        $selectedAttr = $this->attributes;
        $uomId        = $this->uom_id;
        $storeId      = $this?->po?->store_id;
        return InventoryHelper::getPendingPo($itemId, $uomId, $selectedAttr, $storeId);
    }

    public function asnItems()
    {
        return $this->hasMany(VendorAsnItem::class, 'po_item_id', 'id');
    }

    public function geItems()
    {
        return $this->hasMany(GateEntryDetail::class, 'purchase_order_item_id', 'id');
    }
}
