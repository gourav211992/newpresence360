<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaMaster extends Model
{
    protected $table = 'erp_area_masters';

    protected $fillable = [
        'organization_id',
        'name',
        'employee_type',
        'parent_id',
        'sublocation_id',
        'location_id',
        'status',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function subLocations()
    {
        return $this->hasMany(SubLocation::class, 'organization_id', 'organization_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'organization_id', 'organization_id');
    }
}
