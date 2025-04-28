<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;

class LoanDisbursement extends Model
{
    protected $table = 'erp_loan_disbursements';

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
    protected $casts = [
        'dis_milestone' => 'json',
    ];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function loanDisbursementDoc()
    {
        return $this->hasMany(LoanDisbursementDoc::class, 'loan_disbursement_id');
    }
    public function payment(){
        return $this->belongsTo(BankDetail::class, 'bank_details_id');

    }
    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }
}
