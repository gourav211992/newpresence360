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

class WhLevel extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait, DefaultGroupCompanyOrg, Deletable;
    protected $table = 'erp_wh_levels';
    protected $fillable = [
        'name', 
        'parent_id', 
        'wh_structure_id', 
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

    public function whStructure()
    {
        return $this->belongsTo(WhStructure::class, 'wh_structure_id');
    }

    public function details()
    {
        return $this->hasMany(WhDetail::class,'wh_level_id');
    }

}
