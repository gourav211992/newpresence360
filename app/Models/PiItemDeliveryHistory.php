<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiItemDeliveryHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_pi_item_delivery_history';

    protected $fillable = [
        'source_id',
        'pi_id',
        'pi_item_id',
        'qty',
        'grn_qty',
        'delivery_date'
    ];
}
