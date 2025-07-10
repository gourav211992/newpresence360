<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpLogisticsMultiFixedLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_logistics_mf_locations';

    protected $fillable = [
        'multi_fixed_pricing_id',
        'location_route_id',
        'amount',
    ];

    public function route()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'location_route_id');
    }


        public function fixedPricing()
    {
        return $this->belongsTo(ErpLogisticsMultiFixedPricing::class, 'multi_fixed_pricing_id');
    }


}
