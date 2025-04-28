<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermLoanNetWorthProperty extends Model
{
    protected $table = 'erp_term_loan_net_worth_properties';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function termLoanNetWorth()
    {
        return $this->belongsTo(TermLoanNetWorth::class, 'term_loan_net_worth_id');
    }
}
