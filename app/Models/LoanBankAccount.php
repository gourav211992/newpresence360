<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanBankAccount extends Model
{
    protected $table = 'erp_loan_bank_accounts';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public static function createUpdateBankAccount($request, $edit_loanId, $homeLoan){
        $bank_accounts = $request->input('BankAcc', []);
        if(count($bank_accounts) > 0){
            static::where('home_loan_id', $edit_loanId)->delete();
            foreach ($bank_accounts['bank_name'] as $index => $bank_name) {
                if($index == 0){
                    continue;
                }
                static::create([
                    'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                    'bank_name' => $bank_accounts['bank_name'][$index] ?? null,
                    'branch' => $bank_accounts['branch'][$index] ?? null,
                    'ac_held' => $bank_accounts['ac_held'][$index] ?? null,
                    'ac_type' => $bank_accounts['ac_type'][$index] ?? null,
                    'ac_no' => $bank_accounts['ac_no'][$index] ?? null,
                    'ac_balance' => $bank_accounts['ac_balance'][$index] ?? null,
                    'date' => $bank_accounts['date'][$index] ?? null
                ]);
            }
        }
    }
}
