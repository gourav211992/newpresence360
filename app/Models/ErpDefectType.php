<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;


class ErpDefectType extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;
    protected $table = 'erp_defect_types';


    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'name',
        'priority',
        'estimated_time',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

}
