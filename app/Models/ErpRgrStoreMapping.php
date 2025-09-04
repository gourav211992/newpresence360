<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserStampTrait;
use App\Traits\Deletable;

class ErpRgrStoreMapping extends Model
{
    use SoftDeletes,Deletable,UserStampTrait;

    protected $table = 'erp_rgr_store_mappings';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'category_id',
        'store_id',
        'sub_store_id',
        'qc_sub_store_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

      public $referencingRelationships = [
        'category' => 'category_id',
        'store' => 'store_id',
        'subStore' => 'sub_store_id',
        'qcSubStore' => 'qc_sub_store_id'
    ];

    
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function qcSubStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'qc_sub_store_id');
    }

      public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
}
