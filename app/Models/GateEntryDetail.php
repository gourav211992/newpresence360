<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_details';

    protected $fillable = [
        'header_id',
        'purchase_order_item_id',
        'so_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'store_id',
        'order_qty',
        'receipt_qty',
        'accepted_qty',
        'mrn_qty',
        'rejected_qty',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'basic_value',
        'discount_percentage',
        'discount_amount',
        'header_discount_amount',
        'net_value',
        'tax_value',
        'taxable_amount',
        'item_exp_amount',
        'header_exp_amount',
        'total_item_amount',
        'remark',
        'store_code',
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

    public function gateEntryHeader()
    {
        return $this->belongsTo(GateEntryHeader::class, 'header_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function attributes()
    {
        return $this->hasMany(GateEntryAttribute::class, 'detail_id');
    }

    public function attributeHistories()
    {
        return $this->hasMany(GateEntryAttributeHistory::class, 'detail_id');
    }

    public function gateEntryItemLocations()
    {
        return $this->hasMany(GateEntryItemLocation::class, 'detail_id');
    }

    public function gateEntryItemLocationHistories()
    {
        return $this->hasMany(GateEntryItemLocationHistory::class, 'detail_id');
    }

    public function extraAmounts()
    {
        return $this->hasMany(GateEntryTed::class, 'detail_id');
    }

    public function extraAmountHistories()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'purchase_order_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function getAssessmentAmountTotalAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount - $this->header_discount_amount);
    }

    public function getAssessmentAmountItemAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount);
    }

    // After item discount
    public function getAssessmentAmountHeaderAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount);
    }

    public function getTotalItemValueAttribute()
    {
        return ($this->accepted_qty * $this->rate);
    }

    public function getTotalDiscValueAttribute()
    {
        return ($this->discount_amount + $this->header_discount_amount);
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function storeLocations()
    {
        return $this->belongsTo(GateEntryItemLocation::class, 'detail_id');
    }

    public function itemDiscount()
    {
        return $this->hasMany(GateEntryTed::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    public function itemDiscountHistory()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(GateEntryTed::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function headerDiscountHistory()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(GateEntryTed::class, 'detail_id')->where('ted_type','Tax');
    }

    public function taxHistories()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id')->where('ted_type','Tax');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = GateEntryTed::where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = GateEntryTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getSgstValueAttribute()
    {
        $tedRecords = GateEntryTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = GateEntryTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getIgstValueAttribute()
    {
        $tedRecords = GateEntryTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = GateEntryTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function ted_tax()
    {
        return $this->hasOne(GateEntryTed::class,'detail_id')->where('ted_type','Tax')->latest();
    }
}

