<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoJobWorkItemHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_so_job_work_items_history';
    protected $fillable = [
        'sale_order_id',
        'so_item_id',
        'bom_detail_id',
        'station_id',
        'rm_type',
        'item_id',
        'item_code',
        'uom_id',
        'qty',
        'consumed_qty',
        'rate',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty'
    ];
}
