<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLoanAppraisal extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'application_no',
        'unit_name',
        'proprietor_name',
        'address',
        'project_cost',
        'term_loan',
        'promotor_contribution',
        'cibil_score',
        'interest_rate',
        'loan_period',
        'repayment_type',
        'no_of_installments',
        'repayment_start_after',
        'repayment_start_period',
        'status',
        'group_id',
        'company_id',
        'organization_id',
    ];

    public function loan()
    {
        return $this->belongsTo(HomeLoan::class, 'loan_id');
    }

    public function dpr()
    {
        return $this->hasMany(ErpLoanAppraisalDpr::class, 'loan_appraisal_id')->with('dpr');
    }

    public function disbursal()
    {
        return $this->hasMany(ErpLoanAppraisalDisbursal::class, 'loan_appraisal_id');
    }

    public function recovery()
    {
        return $this->hasMany(ErpLoanAppraisalRecovery::class, 'loan_appraisal_id');
    }

    public function document()
    {
        return $this->hasMany(ErpLoanAppraisalDocument::class, 'loan_appraisal_id');
    }
}
