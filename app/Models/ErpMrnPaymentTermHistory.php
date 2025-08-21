<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMrnPaymentTermHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_mrn_payment_terms_history';
    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'source_id',
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
