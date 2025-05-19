<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPslipItemDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pslip_id',
        'pslip_item_id',
        'bundle_no',
        'bundle_type',
        'qty'
    ];
}
