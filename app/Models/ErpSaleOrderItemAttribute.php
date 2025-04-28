<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleOrderItemAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'sale_order_item_id',
        'item_code',
        'item_name',
        'attribute_name',
        'attribute_value',
    ];
}
