<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostGroup extends Model
{
    protected $table = 'erp_cost_groups';

    use HasFactory;

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
}
