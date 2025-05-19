<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class StationGroup extends Model
{
    use HasFactory, SoftDeletes, Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_station_groups'; 

    protected $fillable = [
        'name', 
        'alias', 
        'group_id',
        'company_id',
        'organization_id',
        'status', 
    ];


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
