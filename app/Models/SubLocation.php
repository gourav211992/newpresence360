<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubLocation extends Model
{
    protected $table = 'erp_sub_locations';

    protected $fillable = [
        'organization_id',
        'parent_id',
        'location_id',
        'name',
        'code',
        'master',
        'geo_address',
        'geo_latitude',
        'geo_longitude',
        'geo_boundaries',
        'status',
        'radius_in_meter',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function areaMaster()
    {
        return $this->belongsTo(AreaMaster::class, 'organization_id', 'organization_id');
    }
}
