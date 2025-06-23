<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLogisticsMultiFixedLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_logistics_multi_fixed_locations';

    protected $fillable = [
        'multi_fixed_pricing_id',
        'state_id',
        'city_id',
        'amount',
    ];
}
