<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLoanAppraisalRecovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_appraisal_id',
        'year',
        'start_amount',
        'interest_amount',
        'repayment_amount',
        'end_amount',
    ];

    public function loanAppraisal()
    {
        return $this->belongsTo(ErpLoanAppraisal::class, 'loan_appraisal_id');
    }
}
