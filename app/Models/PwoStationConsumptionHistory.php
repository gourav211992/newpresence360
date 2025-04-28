<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
class PwoStationConsumptionHistory extends Model
{
    use HasFactory,DefaultGroupCompanyOrg;

    protected $table = 'erp_pwo_station_consumptions_history';

    protected $fillable = [
        'mo_id',
        'pwo_mapping_id',
        'station_id',
        'mo_product_qty',
        'level'
    ];
}
