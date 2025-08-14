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

class WhStructure extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait,DefaultGroupCompanyOrg, Deletable;
    protected $table = 'erp_wh_structures';
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'store_id',
        'sub_store_id',
        'name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
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
        return $this->hasMany(WhLevel::class,'wh_structure_id');
    }

    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }

    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }

}
