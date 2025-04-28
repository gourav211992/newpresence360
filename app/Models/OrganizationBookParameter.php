<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationBookParameter extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_book_parameters';

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'book_id',
        'org_service_id',
        'service_param_id',
        'parameter_name',
        'parameter_value',
        'type',
        'status',
    ];

    protected $casts = [
        'parameter_value' => 'array'
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

    public function org_param()
    {
        return $this -> belongsTo(OrganizationServiceParameter::class, 'service_param_id');
    }

    public function book()
    {
        return $this -> belongsTo(Book::class);
    }

    public function org_service()
    {
        return $this -> belongsTo(OrganizationService::class, 'org_service_id');
    }
}
