<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpBundleItemDetailsHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_bundle_item_details_history';

    protected $fillable = [
        'source_id',
        'bundle_id',
        'item_id',
        'item_code',
        'item_name',
        'uom_id',
        'qty',
        'group_id',
        'company_id',
        'organization_id',
    ];
}