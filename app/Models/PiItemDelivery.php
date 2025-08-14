<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiItemDelivery extends Model
{
    use HasFactory;

    protected $table = 'erp_pi_item_delivery';

    protected $fillable = [
        'pi_id',
        'pi_item_id',
        'qty',
        'grn_qty',
        'delivery_date'
    ];
}