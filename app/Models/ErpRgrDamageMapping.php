<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserStampTrait;

class ErpRgrDamageMapping extends Model
{
    use HasFactory, SoftDeletes,UserStampTrait;

    protected $table = 'erp_rgr_damage_mappings';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'store_id',
        'sub_store_id',
        'damage_type',
        'created_by',
        'updated_by',
        'deleted_by',
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
      public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
}
