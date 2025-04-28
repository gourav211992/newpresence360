<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\ServiceParametersHelper;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationService extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_organization_services';

    protected $fillable = [
        'organization_id',
        'name',
        'alias',
        'company_id',
        'group_id',
        'service_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function bookTypes()
    {
        return $this->hasMany(BookType::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function organization()
    {
        return $this -> belongsTo(Organization::class);
    }
    public function company()
    {
        return $this -> belongsTo(OrganizationCompany::class);
    }
    public function parameters()
    {
        return $this -> hasMany(OrganizationServiceParameter::class, 'service_id', 'service_id') -> withDefaultGroupCompanyOrg();
    }
    public function common_parameters()
    {
        return $this -> hasMany(OrganizationServiceParameter::class, 'service_id', 'service_id') -> where('type', ServiceParametersHelper::COMMON_PARAMETERS) -> withDefaultGroupCompanyOrg();
    }
    public function gl_parameters()
    {
        return $this -> hasMany(OrganizationServiceParameter::class, 'service_id', 'service_id') -> where('type', ServiceParametersHelper::GL_PARAMETERS) -> withDefaultGroupCompanyOrg();
    }
}