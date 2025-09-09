<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;

class ErpRepItem extends Model
{
    use FileUploadTrait;
    protected $table = 'erp_rep_items';

    protected $fillable = [
        'repair_order_id',
        'rgr_item_id',
        'rgr_job_detail_id',
        'item_id',
        'item_code',
        'item_name',
        'item_uid',
        'uom_id',
        'uom_code',
        'qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'service_item_id',
        'service_item_code',
        'service_item_name',
        'rgr_sub_store_id',
        'rgr_sub_store_name',
        'qc_sub_store_id',
        'qc_sub_store_name',
        'rejuvenate_item_id',
        'rejuvenate_item_code',
        'rejuvenate_item_name',
        'rejuvenate_item_attributes',
        'repair_remarks',
    ];

     // Relations

      public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function subStore()
    {
        return $this->belongsTo(ErpRgrStoreMapping::class, 'rgr_sub_store_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function repairOrder()
    {
        return $this->belongsTo(ErpRepairOrder::class, 'repair_order_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpRepItemAttribute::class, 'rep_item_id');
    }

    public function defectLogs()
    {
        return $this->hasMany(ErpRepItemDefectLog::class, 'rep_item_id');
    }

     public function media()
    {
        return $this->morphMany(ErpRepMedia::class, 'model');
    }

    public function rgrSegregations()
    {
        return $this->hasMany(ErpRgrItemSegregation::class, 'rgr_item_id', 'rgr_item_id');
    }
}
