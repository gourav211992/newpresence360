<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoItemBom extends Model
{
    use HasFactory;

    protected $table = 'erp_so_item_bom';
    protected $fillable = [
        'sale_order_id',
        'so_item_id',
        'bom_id',
        'bom_detail_id',
        'uom_id',
        'item_id',
        'item_code',
        'item_attributes',
        'qty',
        'station_id',
        'station_name',
        'remark'
    ];

    protected $casts = [
        'item_attributes' => 'array'
    ];

    protected $appends = [
        'item_name',
        'uom_name'
    ];

    public function uom()
    {
        return $this -> belongsTo(Unit::class);
    }
    public function item()
    {
        return $this -> belongsTo(Item::class);
    }
    public function bom()
    {
        return $this -> belongsTo(Bom::class,'bom_id');
    }
    public function bomDetail()
    {
        return $this -> belongsTo(BomDetail::class,'bom_detail_id');
    }
    public function getItemNameAttribute()
    {
        return $this -> item ?-> item_name;
    }

    public function getUomNameAttribute()
    {
        return $this -> uom ?-> uom_name;
    }
}
