<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class ErpLogisticsMultiFixedLocation extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;

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



}
