<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpTransporterRequestLocation extends Model
{
    use HasFactory;

    protected $fillable =[
        'transporter_request_id',
        'address_id',
        'location_id',
        'location_name',
        'location_type',
    ];
    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', '');
    }
    public function address()
    {
        return $this->belongsTo(ErpAddress::class,'address_id');
    }
}
