<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class ErpLogisticsMultiFixedPricing extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_logistics_mf_pricing';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'source_route_id',
        'destination_route_id',
        'vehicle_type_id',
        'customer_id',
        'status',
         'created_by',
        'updated_by',
    ];

     public function sourceRoute()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'source_route_id');
    }

    public function destinationRoute()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'destination_route_id');
    }

     public function vehicleType()
    {
        return $this->belongsTo(ErpVehicleType::class, 'vehicle_type_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

      public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

    public function locations() 
    { 
        return $this->hasMany(ErpLogisticsMultiFixedLocation::class, 'multi_fixed_pricing_id');
    }

}
