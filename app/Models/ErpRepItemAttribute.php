<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpRepItemAttribute extends Model
{
    protected $table = 'erp_rep_item_attributes';

    protected $fillable = [
        'repair_order_id',
        'rep_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value',
    ];

     public function item()
    {
        return $this->belongsTo(ErpRepItem::class, 'rep_item_id');
    }

    public function repairOrder()
    {
        return $this->belongsTo(ErpRepairOrder::class, 'repair_order_id');
    }
}
