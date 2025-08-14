<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomProductionItem extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_production_items';
    protected $fillable = [
        'bom_id',
        'station_id',
        'item_id',
        'item_code',
        'attributes',
        'uom_id',
        'qty'
    ];

    protected $casts = ['attributes' => 'array'];

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }
}
