<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConstantHelper;
use App\Traits\Deletable;

class ErpAddress extends Model
{
    use HasFactory,Deletable;
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'country_id',
        'state_id',
        'city_id',
        'pincode_master_id',
        'address',
        'type',
        'pincode',
        'phone',
        'fax_number',
        'is_billing',
        'is_shipping'
    ];

    protected $appends = ['display_address'];

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

    public function erpAddressable()
    {
        return $this->morphTo();
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

}
