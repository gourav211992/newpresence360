<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;

class ErpRepItemDefectLog extends Model
{
    use FileUploadTrait;
    protected $table = 'erp_rep_item_defect_logs';

    protected $fillable = [
        'repair_order_id',
        'rep_item_id',
        'defect_severity',
        'defect_type',
        'damage_nature',
        'remarks',
    ];

     public function item()
    {
        return $this->belongsTo(ErpRepItem::class, 'rep_item_id');
    }

    public function repairOrder()
    {
        return $this->belongsTo(ErpRepairOrder::class, 'repair_order_id');
    }
    public function media()
    {
        return $this->morphMany(ErpRepMedia::class, 'model');
    }
}
