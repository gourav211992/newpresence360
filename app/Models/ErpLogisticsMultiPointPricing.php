<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpLogisticsMultiPointPricing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_logistics_multi_point_pricing';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'source_state_id',
        'source_city_id',
        'free_point',
        'amount',
        'customer_id',
        'status',
    ];
}
