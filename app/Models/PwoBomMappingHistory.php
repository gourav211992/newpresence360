<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwoBomMappingHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_pwo_bom_mapping_history';
    protected $fillable = [
        'pwo_id',
        'pwo_mapping_id',
        'bom_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'item_code',
        'attributes',
        'uom_id',
        'qty',
        'rate',
        'station_id',
        'section_id',
        'sub_section_id',
        'so_id'
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
