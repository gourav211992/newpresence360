<?php

namespace App\Models;

use App\Models\PO\PoHeader;
use App\Models\PO\PoDetail;
use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_mrn_details';
    protected $fillable = [
        'mrn_header_id',
        'purchase_order_item_id',
        'gate_entry_detail_id',
        'so_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'store_location',
        'rack',
        'shelf',
        'bin',
        'uom_id',
        'uom_code',
        'order_qty',
        'receipt_qty',
        'accepted_qty',
        'purchase_bill_qty',
        'pr_qty',
        'rejected_qty',
        'pr_rejected_qty',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_qty',
        'inventory_uom_code',
        'rate',
        'basic_value',
        'discount_percentage',
        'discount_amount',
        'header_discount_percentage',
        'header_discount_amount',
        'net_value',
        'sgst_percentage',
        'cgst_percentage',
        'igst_percentage',
        'tax_value',
        'taxable_amount',
        'sub_total',
        'item_exp_amount',
        'header_exp_amount',
        'company_currency',
        'exchange_rate_to_company_currency',
        'group_currency',
        'exchange_rate_to_group_currency',
        'selected_item',
        'remark'
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

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function header()
    {
        return $this->belongsTo(MrnHeader::class, 'mrn_header_id');
    }

    public function attributes()
    {
        return $this->hasMany(MrnAttribute::class);
    }

    public function attributeHistories()
    {
        return $this->hasMany(MrnAttributeHistory::class, 'mrn_detail_id');
    }

    public function mrnItemLocations()
    {
        return $this->hasMany(MrnItemLocation::class, 'mrn_detail_id');
    }

    public function mrnItemLocationHistories()
    {
        return $this->hasMany(MrnItemLocationHistory::class, 'mrn_detail_id');
    }

    public function extraAmounts()
    {
        return $this->hasMany(MrnExtraAmount::class, 'mrn_detail_id');
    }

    public function extraAmountHistories()
    {
        return $this->hasMany(MrnExtraAmountHistory::class, 'mrn_detail_id');
    }

    public function geItem()
    {
        return $this->belongsTo(GateEntryDetail::class, 'gate_entry_detail_id');
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
        return $this->belongsTo(MrnItemLocation::class, 'mrn_detail_id');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }
    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function itemDiscount()
    {
        return $this->hasMany(MrnExtraAmount::class)->where('ted_level', 'D')->where('ted_type','Discount');
    }
    public function itemDiscountHistory()
    {
        return $this->hasMany(MrnExtraAmountHistory::class)->where('ted_level', 'D')->where('ted_type','Discount');
    }
    public function stockLedger()
    {
        return $this->hasOne(StockLedger::class, 'document_detail_id');
    }
    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(MrnExtraAmount::class)->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function headerDiscountHistory()
    {
        return $this->hasMany(MrnExtraAmountHistory::class)->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(MrnExtraAmount::class)->where('ted_type','Tax');
    }

    public function taxHistories()
    {
        return $this->hasMany(MrnExtraAmountHistory::class)->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = MrnExtraAmount::where('mrn_detail_id', $this->id)
            ->where('mrn_header_id', $this->mrn_header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = MrnExtraAmount::with(['taxDetail'])
            ->where('mrn_detail_id', $this->id)
            ->where('mrn_header_id', $this->mrn_header_id)
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
        $tedRecords = MrnExtraAmount::where('mrn_detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = MrnExtraAmount::with(['taxDetail'])
            ->where('mrn_detail_id', $this->id)
            ->where('mrn_header_id', $this->mrn_header_id)
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
        $tedRecords = MrnExtraAmount::where('mrn_detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = MrnExtraAmount::with(['taxDetail'])
            ->where('mrn_detail_id', $this->id)
            ->where('mrn_header_id', $this->mrn_header_id)
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
        return $this->hasOne(MrnExtraAmount::class,'mrn_detail_id')->where('ted_type','Tax')->latest();
    }

    // public function getAvailableStockAttribute()
    // {
    //     $availableStocks = StockLedger::where('document_detail_id', $this->id)
    //         ->where('mrn_header_id', $this->mrn_header_id)
    //         ->where('book_type', '=', ConstantHelper::MRN_SERVICE_ALIAS)
    //         ->where('transaction_type', '=', 'receipt')
    //         ->whereNull('utilized_id')
    //         ->sum('receipt_qty');


    //     return $availableStocks;
    // }
    public function asset()
    {
        return $this->hasOne(FixedAssetRegistration::class,'mrn_detail_id')->latest();
    }
}

