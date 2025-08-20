<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoPaymentTermHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_so_payment_terms_history';
    
    protected $fillable = [
        'source_id',
        'so_header_id',
        'payment_term_id',
        'payment_term_detail_id',
        'credit_days',
        'percent',
        'trigger_type'
    ];
}
