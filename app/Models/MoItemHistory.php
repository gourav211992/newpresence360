<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class MoItemHistory extends Model
{
    use HasFactory,DateFormatTrait;

    protected $table = 'erp_mo_items_history';

    protected $fillable = [
        'source_id',
        'mo_id',
        'station_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'uom_id',
        'qty',
        'rate',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'so_id',
        'consumed_qty'
    ];

    
    // public $referencingRelationships = [
    //     'vendor' => 'vendor_id',
    // ];
    public function getQtnAttribute()
    {
        $formattedQty = sprintf("%.6f", (float) $this->attributes['qty']);
        return $formattedQty;
    }
    
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // public function bomDetail()
    // {
    //     return $this->belongsTo(BomDetail::class,'bom_detail_id');
    // }

    // public function station()
    // {
    //     return $this->belongsTo(Station::class,'station_id');
    // }

    public function attributes()
    {
        return $this->hasMany(MoItemAttributeHistory::class,'mo_item_id');
    }

    public function mo()
    {
        return $this->belongsTo(MfgOrderHistory::class,'mo_id');
    }
}
