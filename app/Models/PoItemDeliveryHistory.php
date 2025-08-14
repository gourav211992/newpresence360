<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoItemDeliveryHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_po_item_delivery_history';

    protected $fillable = [
        'purchase_order_id',
        'source_id',
        'po_item_id',
        'qty',
        'grn_qty',
        'delivery_date'
    ];
}
