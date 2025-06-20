<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpVehicleType extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_logistics_vehicle_types';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'name',
        'description',
        'status',
        'created_at',
        'updated_at'
    ];
}
