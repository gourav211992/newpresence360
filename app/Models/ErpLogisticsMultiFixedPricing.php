<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpLogisticsMultiFixedPricing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_logistics_multi_fixed_pricing';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'source_state_id',
        'source_city_id',
        'destination_state_id',
        'destination_city_id',
        'vehicle_type_id',
        'customer_id',
        'status',
    ];
}
