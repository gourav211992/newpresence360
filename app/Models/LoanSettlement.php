<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use App\Traits\DefaultGroupCompanyOrg;

class LoanSettlement extends Model
{
    protected $table = 'erp_loan_settlements';

    use HasFactory,DefaultGroupCompanyOrg;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function loanSettlementDoc()
    {
        return $this->belongsTo(LoanSettlementDoc::class, 'loan_settlement_id');
    }

    public function loanSettlementSchedule()
    {
        return $this->belongsTo(LoanSettlementSchedule::class, 'loan_settlement_id');
    }
    public function schedules()
    {
        return $this->hasMany(LoanSettlementSchedule::class, 'loan_settlement_id');
    }
    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }
}
