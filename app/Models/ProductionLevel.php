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

class ProductionLevel extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait, Deletable;
    protected $table = 'erp_production_levels';
    protected $fillable = [
        'production_route_id',
        'name',
        'description',
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

    public function productionRoute()
    {
        return $this->belongsTo(ProductionRoute::class,'production_route_id');
    }

    public function parentDetails()
    {
        return $this->hasMany(ProductionRouteParentDetail::class,'production_level_id');
    }

    public function details()
    {
        return $this->hasMany(ProductionRouteDetail::class,'production_level_id');
    }

}
