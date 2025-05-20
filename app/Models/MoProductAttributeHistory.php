<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProductAttributeHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_product_attributes_history';
    protected $fillable = [
        'source_id',
        'mo_id',
        'mo_product_id',
        'item_attribute_id',
        'item_code',
        'attribute_group_id',
        'attribute_name',
        'attribute_value'
    ];
}
