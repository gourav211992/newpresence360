<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMRNPaymentTerm extends Model
{
    use HasFactory;

    protected $table = 'erp_mrn_payment_terms';

    protected $fillable = [
        'mrn_header_id',
        'reference_id',
        'reference_type',
        'payment_term_id',
        'payment_term_detail_id',
        'credit_days',
        'percent',
        'trigger_type'
    ];
}
