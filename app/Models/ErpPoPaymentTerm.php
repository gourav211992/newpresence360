<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPoPaymentTerm extends Model
{
    use HasFactory;

    protected $table = 'erp_po_payment_terms';
    
    protected $fillable = [
        'po_header_id',
        'payment_term_id',
        'payment_term_detail_id',
        'credit_days',
        'percent',
        'trigger_type'
    ];
}
