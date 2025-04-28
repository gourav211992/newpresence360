<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
use App\Traits\DefaultGroupCompanyOrg;

class AttributeGroup extends Model
{
    protected $table = 'erp_attribute_groups';
    use HasFactory, SoftDeletes,Deletable,DefaultGroupCompanyOrg;
    protected $fillable = [
        'id',
        'short_name',
        'name',
        'organization_id',
        'group_id',
        'company_id',
        'status',
    ];
  
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }


}
