<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class Unit  extends Model
{
    use HasFactory,SoftDeletes,Deletable,DefaultGroupCompanyOrg;
    
    protected $table = 'erp_units';

    protected $fillable = [
        'name',
        'description',
        'group_id', 
        'company_id',
        'organization_id', 
        'status',
    ];

    protected $appends = ['alias'];

    public function alternateUOMs()
    {
        return $this->hasMany(AlternateUOM::class, 'uom_id');
    }
    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function getAliasAttribute()
    {
        // if ($this ->attributes['alias'] === null) {
        //     return $this -> attributes['name'];
        // } else {
        //     return $this -> attributes['alias'];
        // }
        if (isset($this ->attributes['name'])) {
            return $this -> attributes['name'];
        } else {
            return "";
        }
    }
}
