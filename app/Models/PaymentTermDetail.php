<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class PaymentTermDetail extends Model
{
    use SoftDeletes, Deletable;

    protected $table = 'erp_payment_term_details';

    protected $fillable = [
        'erp_payment_term_id', 
        'installation_no',
        'percent',
        'term_days',
        'trigger_type'
    ];

  
    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

}
