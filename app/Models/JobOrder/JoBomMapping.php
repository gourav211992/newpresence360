<?php

namespace App\Models\JobOrder;

use App\Models\ErpMiItem;
use App\Models\StockLedger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoBomMapping extends Model
{
    use HasFactory;
    protected $table = 'erp_jo_bom_mapping';
    protected $fillable = [
        'jo_id',
        'jo_product_id',
        'so_id',
        'bom_id',
        'bom_detail_id',
        'item_id',
        'item_code',
        'uom_id',
        'attributes',
        'rm_type',
        'bom_qty',
        'qty',
        'station_id',
        'section_id',
        'sub_section_id'
    ];
    protected $casts = ['attributes' => 'array'];

    public function jo()
    {
        return $this->belongsTo(JobOrder::class,'jo_id');
    }

    public function joProduct()
    {
        return $this->belongsTo(JoProduct::class,'jo_product_id');
    }

    public function stockMappings()
    {
        return $this->hasMany(StockLedger::class, 'item_id', 'item_id');
    }

}
