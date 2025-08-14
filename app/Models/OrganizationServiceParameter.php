<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationServiceParameter extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_organization_service_parameters';
    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'service_id',
        'service_param_id',
        'parameter_name',
        'parameter_value',
        'status',
    ];
    protected $casts = [
        'parameter_value' => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        if(\Auth::check()) {

            static::creating(function ($model) {
                $user = Helper::getAuthenticatedUser();
                $model->created_by = $user->auth_user_id;
            });

            static::updating(function ($model) {
                $user = Helper::getAuthenticatedUser();
                $model->updated_by = $user->auth_user_id;
            });

            static::deleting(function ($model) {
                $user = Helper::getAuthenticatedUser();
                $model->deleted_by = $user->auth_user_id;
            });
        }   
    }

    public function service_parameter()
    {
        return $this -> belongsTo(ServiceParameter::class, 'service_param_id');
    }
}
