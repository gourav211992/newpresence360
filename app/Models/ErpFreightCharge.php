<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpFreightCharge extends Model
{

    use HasFactory,DefaultGroupCompanyOrg;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'source_route_id',
        'destination_route_id',
        'distance',
        'vehicle_type_id',
        'no_bundle',
        'amount',
        'per_bundle',
        'customer_id',
        'status'
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
}

