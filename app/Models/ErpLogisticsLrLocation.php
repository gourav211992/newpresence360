<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLogisticsLrLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_logistics_lr_locations';

    protected $fillable = [
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
