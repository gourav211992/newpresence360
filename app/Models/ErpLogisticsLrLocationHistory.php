<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLogisticsLrLocationHistory extends Model
{
    use HasFactory;

     protected $table = 'erp_logistics_lr_locations_history';

    protected $fillable = [
        'source_id',
        'lorry_receipt_id',
        'location_id',
        'type',
        'no_of_articles',
        'weight',
        'amount',
    ];



    public function route()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'location_id');
    }
}
