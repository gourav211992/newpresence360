<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSrItemLotDetail extends Model
{
    use HasFactory;

    protected $table = 'erp_sr_item_lot_details';
    protected $fillable = [
        'sr_item_id',
        'lot_number',
        'lot_qty',
        'total_lot_qty',
        'original_receipt_date',
        'inventory_uom_qty',
    ];

    public function detail()
    {
        return $this->belongsTo(ErpSaleReturnItem::class, 'sr_item_id', 'id');
    }

}
