<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMrItemLot extends Model
{
    use HasFactory;
    protected $table = 'erp_mr_item_lot_details';
    protected $fillable = [
        'mr_item_id',
        'lot_number',
        'lot_qty',
        'so_lot_number',
        'total_lot_qty',
        'original_receipt_date',
        'inventory_uom_qty',
    ];

    public function detail()
    {
        return $this->belongsTo(ErpMrItem::class, 'mr_item_id', 'id');
    }
    
}
