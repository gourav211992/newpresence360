<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryLoanDoc extends Model
{
    protected $table = 'erp_recovery_loan_docs';

    use HasFactory;
    protected $guarded = ['id'];

    public function recoveryLoan()
    {
        return $this->belongsTo(RecoveryLoan::class, 'recovery_loan_id');
    }
}
