<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSettlementDoc extends Model
{
    protected $table = 'erp_loan_settlement_docs';

    use HasFactory;
    protected $guarded = ['id'];

    public function loanSettlement()
    {
        return $this->belongsTo(LoanSettlement::class, 'loan_settlement_id');
    }
}
