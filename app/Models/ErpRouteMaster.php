<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRouteMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_logistics_route_master';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'name',
        'country_id',
        'state_id',
        'city_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
