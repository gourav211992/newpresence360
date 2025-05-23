<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;

class PwoStationConsumption extends Model
{
    use HasFactory,DefaultGroupCompanyOrg;

    protected $table = 'erp_pwo_station_consumptions';

    protected $fillable = [
        'mo_id',
        'pwo_mapping_id',
        'station_id',
        'mo_product_qty',
        'level'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class,'station_id');
    }
}
