<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermLoanNetWorth extends Model
{
    protected $table = 'erp_term_loan_net_worths';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'term_loan_id');
    }

    public function termLoanNetWorthExperience()
    {
        return $this->hasMany(TermLoanNetWorthExperience::class, 'term_loan_net_worth_id');
    }

    public function termLoanNetWorthProperty()
    {
        return $this->hasMany(TermLoanNetWorthProperty::class, 'term_loan_net_worth_id');
    }

    public function termLoanNetWorthLiability()
    {
        return $this->hasMany(TermLoanNetWorthLiability::class, 'term_loan_net_worth_id');
    }
}
