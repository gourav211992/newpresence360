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

class ProductionRoute extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait,DefaultGroupCompanyOrg, Deletable;
    protected $table = 'erp_production_routes';
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'name',
        'description',
        'safety_buffer_perc',
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

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function levels()
    {
        return $this->hasMany(ProductionLevel::class,'production_route_id');
    }

    public function machines()
    {
        return $this->hasMany(ErpMachine::class,'production_route_id');
    }

    public function details()
    {
        return $this->hasMany(ProductionRouteDetail::class,'production_route_id');
    }

}
