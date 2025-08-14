<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleOrderMrnTed extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'level',
        'sale_order_id',
        'sale_order_item_id',
        'series_code',
        'document_no',
        'ted_code',
        'assessment_amount',
        'ted_percentage',
        'ted_amount',
        'applicability_type',
    ];
}
