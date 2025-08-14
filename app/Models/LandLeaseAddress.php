<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class LandLeaseAddress extends Model
{
    use HasFactory,Deletable;


    protected $table = "land_lease_addresses";

    protected $fillable = [
        'lease_id',
        'customer_id',
        'country_id',
        'state_id',
        'city_id',
        'pincode',
        'address',
    ];

    public $referencingRelationships = [
        'lease' => 'lease_id',
        'customer' => 'customer_id',
        'country'=>'country_id',
        'state'=>'state_id',
        'city'=>'city_id'
    ];

    public $appends = ['display_address'];
    public static function createUpdateAddress($request, $lease, $edit_lease_id = null)
    {
    $id=$lease->id;
    if($edit_lease_id!=null){
        LandLeaseAddress::where('lease_id', $edit_lease_id)->delete();
        $id=$edit_lease_id;


    }

        $address = LandLeaseAddress::updateOrCreate([
            'lease_id' =>$id,
            'customer_id' => $request->customer_id,
            'country_id' => $request->addresses['country_id'],
            "state_id" => $request->addresses['state_id'],
            "city_id" => $request->addresses['city_id'],
            "pincode" => $request->addresses['pincode'],
            "address" => $request->addresses['address'],
        ]);

        return $address;
    }

    public function lease()
    {
        return $this->belongsTo(LandLease::class, 'lease_id');
    }
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function getDisplayAddressAttribute()
    {
        $addressParts = [
            $this->getAttribute('address'),
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
            $this->getAttribute('pincode') ? 'Pincode - ' . $this->getAttribute('pincode') : null,
        ];
        return implode(', ', array_filter($addressParts));
    }
}
