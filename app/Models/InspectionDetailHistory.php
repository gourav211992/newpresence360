<?php
namespace App\Models;

use App\Models\PO\PoHeader;
use App\Models\PO\PoDetail;
use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionDetailHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_insp_details_history';
    protected $fillable = [
        'source_id',
        'header_id', 
        'item_id', 
        'mrn_detail_id', 
        'item_code', 
        'item_name', 
        'hsn_id', 
        'hsn_code', 
        'uom_id', 
        'uom_code', 
        'store_id', 
        'store_code', 
        'sub_store_id', 
        'sub_store_code', 
        'receipt_qty', 
        'accepted_qty', 
        'rejected_qty', 
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

    public function header()
    {
        return $this->belongsTo(InspectionHeaderHistory::class, 'header_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function attributes()
    {
        return $this->hasMany(InspectionItemAttributeHistory::class, 'detail_id');
    }

    public function inspectionTed()
    {
        return $this->hasMany(InspectionTedHistory::class, 'detail_id');
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

    public function itemDiscount()
    {
        return $this->hasMany(InspectionTedHistory::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(InspectionTedHistory::class, 'detail_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(InspectionTedHistory::class, 'detail_id')->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = InspectionTedHistory::where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = InspectionTedHistory::with(['taxDetail'])
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
        $tedRecords = InspectionTedHistory::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = InspectionTedHistory::with(['taxDetail'])
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
        $tedRecords = InspectionTedHistory::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = InspectionTedHistory::with(['taxDetail'])
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
        return $this->hasOne(InspectionTedHistory::class,'detail_id')->where('ted_type','Tax')->latest();
    }
}
