<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDisbursementDoc extends Model
{
    protected $table = 'erp_loan_disbursement_docs';

    use HasFactory;
    protected $guarded = ['id'];

    public function loanDisbursement()
    {
        return $this->belongsTo(LoanDisbursement::class, 'loan_disbursement_id');
    }
}
