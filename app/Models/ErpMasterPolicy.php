<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMasterPolicy extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_master_policies'; 
    
    protected $fillable = [
        'service_id',
        'policy_level',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    
    public static function boot()
    {
        parent::boot();
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

    public function service()
    {
        return $this->belongsTo(ErpService::class, 'service_id');
    }
}
