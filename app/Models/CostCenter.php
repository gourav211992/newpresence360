<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $table = 'erp_cost_centers';

    use HasFactory, DefaultGroupCompanyOrg;

    protected $fillable = [
        'name',
        'cost_group_id',
        'status',
        'group_id',
        'organizations',
        'locations',
        'company_id',
        'organization_id'
    ];
    protected $casts = [
        'organizations' => 'array',
        'locations' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(CostGroup::class, 'cost_group_id');
    }
    public function itemDetail(){
        return $this->hasOne(ItemDetail::class, 'cost_center_id');
    }
    public function organizations()
{
    return Organization::whereIn('id', $this->organizations ?? []);
}
public function orgLocationMap()
{
    return $this->hasMany(CostCenterOrgLocations::class, 'cost_center_id');
}
public function getOrganizationNamesAttribute()
{
    return Organization::whereIn('id', $this->organizations ?? [])->pluck('name');
}

    public function getLocationNamesAttribute()
    {
        return ErpStore::whereIn('id', $this->locations ?? [])->pluck('store_name');
    }

public function locations()
{
    return ErpStore::whereIn('id', $this->locations ?? []);
}
}
