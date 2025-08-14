<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoItemDelivery extends Model
{
    use HasFactory;

    protected $table = 'erp_so_item_delivery';

    protected $fillable = [
        'sale_order_id',
        'so_item_id',
        'ledger_id',
        'qty',
        'invoice_qty',
        'delivery_date',
    ];
    public function item()
    {
        return $this->belongsTo(ErpSoItem::class, 'so_item_id');
    }
}
