<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConstantHelper;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'line_1',
        'line_2',
        'line_3',
        'city_id',
        'district',
        'state_id',
        'country_id',
        'postal_code',
        'mobile',
        'name',
        'email',
    ];

    protected $appends = ['display_address','full_address'];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function getDisplayAddressAttribute()
    {
        $addressParts = [
            ucFirst($this->getAttribute('line_1')),
            $this->getAttribute('line_2'),
            $this->getAttribute('line_3'),
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
            $this->getAttribute('postal_code') ? 'Pincode - ' . $this->getAttribute('postal_code') : null,
        ];
        return implode(', ', array_filter($addressParts));
        // $addressParts = [
        //     $this->getAttribute('address'),
        //     $this->city?->name,
        //     $this->state?->name,
        //     $this->country?->name,
        //     $this->getAttribute('pincode') ? 'Pincode - ' . $this->getAttribute('pincode') : null,
        // ];
        // return implode(', ', array_filter($addressParts));
    }

    public function getFullAddressAttribute()
    {
        $addressParts = [
            ucFirst($this->getAttribute('line_1')),
            $this->getAttribute('line_2'),
            $this->getAttribute('line_3'),
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
            $this->getAttribute('postal_code') ? 'Pincode - ' . $this->getAttribute('postal_code') : null,
        ];
        return implode(', ', array_filter($addressParts));
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
