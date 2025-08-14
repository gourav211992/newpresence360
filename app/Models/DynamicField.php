<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class DynamicField extends Model
{
    use SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_dynamic_fields';
    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'name',
        'description',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(DynamicFieldDetail::class, 'header_id');
    }
    public function items()
    {
        return $this -> hasMany(DynamicFieldDetail::class, 'header_id');
    }
}