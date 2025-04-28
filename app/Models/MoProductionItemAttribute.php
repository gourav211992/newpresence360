<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProductionItemAttribute extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_production_item_attributes';
    protected $fillable = [
        'mo_id',
        'item_id',
        'item_code',
        'uom_id',
        'required_qty',
        'produced_qty'
    ];
}
