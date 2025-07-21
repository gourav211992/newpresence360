<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMaintenanceType extends Model
{
    use HasFactory,DefaultGroupCompanyOrg, Deletable,softDeletes;

    protected $table = 'erp_maintenance_types';

    // protected $fillable = ['name', 'description', 'status'];

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'name',
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
