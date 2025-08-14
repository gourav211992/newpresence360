<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoBomMappingHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_bom_mapping';
    protected $fillable = [
        'source_id',
        'mo_id',
        'mo_product_id',
        'bom_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'item_code',
        'attributes',
        'uom_id',
        'qty',
        'station_id',
        'section_id',
        'sub_section_id',
        'so_id'
    ];
}
