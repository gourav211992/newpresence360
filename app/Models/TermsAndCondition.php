<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class TermsAndCondition extends Model
{
    use HasFactory, SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_terms_and_conditions';

    protected $fillable = [
        'organization_id',
        'company_id',
        'group_id',
        'term_name',
        'term_detail',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
   
    protected $hidden = ['deleted_at'];
    
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

    public static function boot()
    {
        parent::boot();
        if(\Auth::check()) {

            static::creating(function ($model) {
                $user = \Auth::user();
                $model->created_by = $user->auth_user_id;
            });

            static::updating(function ($model) {
                $user = \Auth::user();
                $model->updated_by = $user->auth_user_id;
            });

            static::deleting(function ($model) {
                $user = \Auth::user();
                $model->deleted_by = $user->auth_user_id;
            });
        }   
    }
}
