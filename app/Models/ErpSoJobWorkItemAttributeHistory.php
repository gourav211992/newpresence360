<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoJobWorkItemAttributeHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_so_job_work_item_attributes_history';
    protected $fillable = [
        'sale_order_id',
        'so_item_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value'
    ];
}
