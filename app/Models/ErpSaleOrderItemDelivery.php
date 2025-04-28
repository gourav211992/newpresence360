<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleOrderItemDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'sale_order_item_id',
        'delivery_date',
        'quantity',
        'invoice_quantity',
    ];
}
