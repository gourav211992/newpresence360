<?php
namespace App\Models;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionRouteParentDetail extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait;
    protected $table = 'erp_pr_parent_details';
    protected $fillable = [
        'production_route_id', 
        'production_level_id', 
        'parent_id', 
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
                $model->created_by = $user->id;
            }
        });

        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });

        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->id;
            }
        });
    }

    public function productionRoute()
    {
        return $this->belongsTo(ProductionRoute::class,'production_route_id');
    }

    public function productionLevel()
    {
        return $this->belongsTo(ProductionLevel::class,'production_level_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class,'station_id');
    }

    public function details()
    {
        return $this->hasMany(ProductionRouteDetail::class,'pr_parent_id');
    }

}
