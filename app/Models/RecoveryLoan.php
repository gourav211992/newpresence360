<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\DefaultGroupCompanyOrg;

class RecoveryLoan extends Model
{
    protected $table = 'erp_recovery_loans';

    use HasFactory,DefaultGroupCompanyOrg;
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->document_status = $model->approvalStatus;
            $model->approval_level = $model->approvalLevel;
        });
    }

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_name');
    }

    public function recoveryLoanDoc()
    {
        return $this->hasMany(RecoveryLoanDoc::class, 'recovery_loan_id');
    }
    public function getLoanDisbursementsAttribute()
    {
        return LoanDisbursement::whereIn('id', json_decode($this->dis_id))->get();
    }
    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }
}
