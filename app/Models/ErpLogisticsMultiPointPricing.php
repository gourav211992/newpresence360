<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class ErpLogisticsMultiPointPricing extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_logistics_mp_pricing';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'source_route_id',
        'free_point',
        'amount',
        'customer_id',
        'status',
    ];

     public function sourceRoute()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'source_route_id');
    }

    public function destinationRoute()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'destination_route_id');
    }

     public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
