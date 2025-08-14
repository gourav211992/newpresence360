<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwoSoMappingItem extends Model
{
    use HasFactory;
    protected $table = 'erp_pwo_so_mapping_items';
    public $timestamps = false;
    protected $fillable = [
        'pwo_so_mapping_id',
        'pwo_item_id',
        'qty'
    ];

    public function pwo_item()
    {
        return $this->belongsTo(PiItem::class,'pwo_item_id');
    }

    public function pwo_so_mapping()
    {
        return $this->belongsTo(PiSoMapping::class,'pwp_so_mapping_id');
    }
}
