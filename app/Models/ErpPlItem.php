<?php

namespace App\Models;

use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ErpPlItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'pl_header_id',
        'item_id',
        'item_code',
        'item_name',
        'attributes',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpPlHeader::class, 'pl_header_id');
    }

    public function uniqueCodes()
    {
        return $this->morphMany(ErpItemUniqueCode::class, 'morphable');
    }

    public function stockReservation()
    {
        return $this->hasMany(StockLedgerReservation::class, 'issue_detail_id','id');
    }
}
