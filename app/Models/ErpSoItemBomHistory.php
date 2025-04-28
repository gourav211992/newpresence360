<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoItemBomHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_so_item_bom_history';

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

    public function getItemNameAttribute()
    {
        return $this -> item ?-> item_name;
    }

    public function getUomNameAttribute()
    {
        return $this -> uom ?-> uom_name;
    }
}
