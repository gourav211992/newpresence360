<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'erp_land_locations';

    protected $fillable = [
        'land_parcel_id', // Foreign key
        'name',
        'latitude',
        'longitude',
    ];

    /**
     * Get the land parcel associated with the location.
     */
    public function landParcel()
    {
        return $this->belongsTo(LandParcel::class, 'land_parcel_id');
    }
}


