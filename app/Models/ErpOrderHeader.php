<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpOrderHeader extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class,'customer_code','customer_code');
    }
}
