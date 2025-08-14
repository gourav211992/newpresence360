<?php
namespace App\Models\BankReconciliation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FailedBankStatement extends Model
{
    use HasFactory;

    protected $table = 'erp_failed_bank_statements'; 

    protected $fillable = [
            'organization_id',
            'company_id',
            'group_id',
            'ledger_id',
            'ledger_group_id',
            'bank_id',
            'account_id',
            'account_number',
            'narration',
            'ref_no',
            'debit_amt',
            'credit_amt',
            'balance',
            'date',
            'errors',
            'uid',
            'created_by',
            'created_by_type',
    ];
}