<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMiItemLotDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'mi_item_id',
        'lot_number',
        'lot_qty',
        'total_lot_qty',
        'original_receipt_date',
        'inventory_uom_qty',
    ];

    public function detail()
    {
        return $this->belongsTo(ErpMrItem::class, 'mr_item_id', 'id');
    }
}
