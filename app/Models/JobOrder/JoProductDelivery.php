<?php

namespace App\Models\JobOrder;

use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoProductDelivery extends Model
{
    use HasFactory,DateFormatTrait;

    protected $table = 'erp_jo_product_delivery';

    protected $fillable = [
        'jo_id',
        'jo_product_id',
        'qty',
        'grn_qty',
        'delivery_date'
    ];
}
