<?php
namespace App\Models;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhLevel extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait, Deletable;
    protected $table = 'erp_wh_levels';
    protected $fillable = [
        'name',
        'level', 
        'parent_id', 
        'store_id',
        'sub_store_id',
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

    public function details()
    {
        return $this->hasMany(WhDetail::class,'wh_level_id');
    }
    
    public function storagePointDetails()
    {
        return $this->hasMany(WhDetail::class,'wh_level_id');
    }

    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }

    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function sub_store_parent()
    {
        return $this -> belongsTo(ErpSubStoreParent::class, 'sub_store_id');
    }

    public function parent()
    {
        return $this->belongsTo(WhLevel::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WhLevel::class, 'parent_id');
    }

}
