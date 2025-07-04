<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoJobWorkItemAttribute extends Model
{
    use HasFactory;

    protected $table = 'erp_so_job_work_item_attributes';
    protected $fillable = [
        'sale_order_id',
        'job_work_item_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value'
    ];
}
