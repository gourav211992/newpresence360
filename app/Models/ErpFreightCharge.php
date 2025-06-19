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
        'source_state_id',
        'source_city_id',
        'destination_state_id',
        'destination_city_id',
        'distance',
        'vehicle_type_id',
        'amount',
        'customer_id',
        'status'
    ];

    public function sourceState()
    {
        return $this->belongsTo(State::class, 'source_state_id');
    }

    public function destinationState()
    {
        return $this->belongsTo(State::class, 'destination_state_id');
    }

    public function sourceCity()
    {
        return $this->belongsTo(City::class, 'source_city_id');
    }

    public function destinationCity()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
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

