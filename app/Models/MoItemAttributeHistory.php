<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoItemAttributeHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_item_attributes_history';
    protected $fillable = [
        'source_id',
        'mo_id',
        'mo_item_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attribute_name',
        'attribute_value'
    ];
}
