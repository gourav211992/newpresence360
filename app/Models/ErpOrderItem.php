<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpOrderItem extends Model
{
    use HasFactory;

    public function orderHeader()
    {
        return $this->belongsTo(ErpOrderHeader::class, 'order_number','order_number');
    }

}
