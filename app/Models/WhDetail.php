<?php
namespace App\Models;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhDetail extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait, DefaultGroupCompanyOrg, Deletable;
    protected $table = 'erp_wh_details';
    protected $fillable = [
        'name', 
        'wh_level_id', 
        'is_storage_point', 
        'status', 
        'created_by', 
        'updated_by',
        'deleted_by'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->created_by = $user->auth_user_id;
            }
        });

        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->auth_user_id;
            }
        });

        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->auth_user_id;
            }
        });
    }

    public function whLevel()
    {
        return $this->belongsTo(WhLevel::class, 'wh_level_id');
    }

}
