<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLoanAppraisalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_appraisal_id',
        'document_type',
        'document',
    ];

    public function loanAppraisal()
    {
        return $this->belongsTo(ErpLoanAppraisal::class, 'loan_appraisal_id');
    }
}
