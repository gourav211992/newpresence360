<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class Station extends Model
{
    use HasFactory, SoftDeletes, Deletable,DefaultGroupCompanyOrg;
    protected $table = 'erp_stations';

    protected $fillable = [
        'parent_id',
        'station_group_id',
        'name',
        'alias',
        'status',
        'group_id',
        'company_id',
        'organization_id',
        'is_consumption'
    ];

    public function stationGroup()
    {
        return $this->hasMany(StationGroup::class, 'station_group_id');
    }

    public function parent()
    {
        return $this->belongsTo(Station::class);
    }

    public function subStations()
    {
        return $this->hasMany(Station::class, 'parent_id');
    }

    public function lines()
    {
        return $this->hasMany(StationLine::class, 'station_id');
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

}
