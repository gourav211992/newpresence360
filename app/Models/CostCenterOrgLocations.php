<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;


class CostCenterOrgLocations extends Model
{
    use HasFactory;

    protected $table = 'erp_cost_center_org_locations';

    protected $fillable = [
        'cost_center_id',
        'location_id',
        'organization_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
}
