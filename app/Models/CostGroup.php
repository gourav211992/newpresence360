<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;

class CostGroup extends Model
{
    protected $table = 'erp_cost_groups';

    use HasFactory,DefaultGroupCompanyOrg;

    protected $fillable = [
        'name',
        'parent_cost_group_id',
        'status',
        'group_id',
        'company_id',
        'organization_id'
    ];

    public function parent()
    {
        return $this->belongsTo(CostGroup::class, 'parent_cost_group_id');
    }

    public function costCenters()
    {
        return $this->hasMany(CostCenter::class, 'cost_group_id', 'id');
    }
}
