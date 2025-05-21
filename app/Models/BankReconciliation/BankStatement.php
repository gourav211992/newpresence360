<?php
namespace App\Models\BankReconciliation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankStatement extends Model
{
    use HasFactory;

    protected $table = 'erp_bank_statements'; 

    protected $fillable = [
            'ledger_id',
            'ledger_group_id',
            'bank_id',
            'account_number',
            'narration',
            'ref_no',
            'debit_amt',
            'credit_amt',
            'balance',
            'date',
            'group_id',
            'company_id',
            'organization_id',
            'matched',
    ];
}