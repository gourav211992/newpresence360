<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'term_id',
        'term_code',
        'remarks',
    ];
}
