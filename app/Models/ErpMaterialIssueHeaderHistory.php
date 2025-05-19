<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMaterialIssueHeaderHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait;

    protected $table = "erp_material_issue_header_history";

    protected $hidden = ['deleted_at'];

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

    public $referencingRelationships = [
        'book' => 'book_id',
        'vendor' => 'vendor_id',
        'from_store' => 'from_store_id',
        'to_store' => 'to_store_id',
        'department' => 'department_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
        'group_currency' => 'group_currency_id',
    ];


    public function media()
    {
        return $this->morphMany(ErpMaterialIssueMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpMaterialIssueMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function vendor()
    {
        return $this -> belongsTo(ErpVendor::class, 'vendor_id');
    }
    public function items()
    {
        return $this -> hasMany(ErpMiItemHistory::class, 'material_issue_id');
    }
    public function from_store()
    {
        return $this -> belongsTo(ErpStore::class, 'from_store_id');
    }
    public function to_store()
    {
        return $this -> belongsTo(ErpStore::class, 'to_store_id');
    }
    public function department()
    {
        return $this -> belongsTo(Department::class, 'department_id');
    }
    public function org_currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'org_currency_id');
    }
    public function comp_currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'comp_currency_id');
    }
    public function group_currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'group_currency_id');
    }
    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
}
