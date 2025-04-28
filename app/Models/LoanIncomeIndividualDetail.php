<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanIncomeIndividualDetail extends Model
{
    protected $table = 'erp_loan_income_individual_details';

    use HasFactory;
    protected $guarded = ['id'];

    public function loanIncome()
    {
        return $this->belongsTo(LoanIncome::class, 'loan_income_id');
    }
}
