<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanGuarApplicantInsurancePolicy extends Model
{
    protected $table = 'erp_loan_guar_applicant_insurance_policies';

    use HasFactory;
    protected $guarded = ['id'];

    public function loanGuarApplicant()
    {
        return $this->belongsTo(LoanGuarApplicant::class, 'loan_guar_applicant_id');
    }
}
