<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPickupItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'pickup_schedule_id',
        'item_id',
        'item_code',
        'item_name',
        'uom_id',
        'uom_code',
        'customer_id',
        'uid',
        'customer_name',
        'customer_email',
        'customer_phone',
        'type',
        'delivery_cancelled',
        'qty',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
