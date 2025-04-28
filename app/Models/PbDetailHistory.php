<?php
namespace App\Models;

use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;

class PbDetailHistory extends Model
{
    protected $table = 'erp_pb_detail_histories';
    protected $fillable = [
        'header_id', 
        'header_history_id', 
        'detail_id', 
        'mrn_detail_id', 
        'item_id', 
        'item_code', 
        'item_name', 
        'hsn_id', 
        'hsn_code', 
        'uom_id', 
        'uom_code', 
        'cost_center_id', 
        'cost_center_name', 
        'order_qty', 
        'accepted_qty', 
        'rejected_qty', 
        'inventory_uom', 
        'inventory_uom_id', 
        'inventory_uom_code', 
        'inventory_uom_qty', 
        'po_rate',
        'rate',
        'variance',
        'basic_value', 
        'discount_percentage', 
        'discount_amount', 
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

    protected $appends = [
        'cgst_value',
        'sgst_value',
        'igst_value'
    ];

    public function header()
    {
        return $this->belongsTo(PbHeader::class, 'header_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(PbHeaderHistory::class, 'header_history_id');
    }
    public function detail()
    {
        return $this->belongsTo(PbDetail::class, 'detail_id');
    }

    public function attributes()
    {
        return $this->hasMany(PbItemAttributeHistory::class, 'detail_history_id');
    }

    public function pbTed()
    {
        return $this->hasMany(PbTedHistory::class, 'detail_history_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class,'cost_center_id');
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

    public function itemDiscount()
    {
        return $this->hasMany(PbTedHistory::class, 'detail_history_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PbTedHistory::class, 'detail_history_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(PbTedHistory::class, 'detail_history_id')->where('ted_type','Tax');
    }
    
}
