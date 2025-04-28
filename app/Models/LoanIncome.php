<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanIncomeIndividualDetail;

class LoanIncome extends Model
{
    protected $table = 'erp_loan_incomes';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function loanIncomeIndividualDetails()
    {
        return $this->hasMany(LoanIncomeIndividualDetail::class, 'loan_income_id');
    }

    public static function createUpdateLoanIncome($request, $edit_loanId, $homeLoan){
        $loan_individual = $request->input('LoanIncIdividual', []);
        if(isset($loan_individual['common_data'])){
            $loan_income = static::updateOrCreate([
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
            ], [
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                'gross_monthly_income' => $loan_individual['common_data']['gross_monthly_income'] ?? null,
                'net_monthly_income' => $loan_individual['common_data']['net_monthly_income'] ?? null,
                'encumbered' => $loan_individual['common_data']['encumbered'] ?? null,
                'plot_land' => $loan_individual['common_data']['plot_land'] ?? null,
                'agriculture_land' => $loan_individual['common_data']['agriculture_land'] ?? null,
                'house_godowns' => $loan_individual['common_data']['house_godowns'] ?? null,
                'others' => $loan_individual['common_data']['others'] ?? null,
                'estimated_value' => $loan_individual['common_data']['estimated_value'] ?? null
            ]);

            if($loan_income){
                LoanIncomeIndividualDetail::where('loan_income_id', $loan_income->id)->delete();
                foreach ($loan_individual['source'] as $index => $individual_loan) {
                    if($index == 0){
                        continue;
                    }
                    LoanIncomeIndividualDetail::create([
                        'loan_income_id' => $loan_income->id,
                        'source' => $loan_individual['source'][$index] ?? null,
                        'purpose' => $loan_individual['purpose'][$index] ?? null,
                        'sanction_date' => $loan_individual['sanction_date'][$index] ?? null,
                        'amount' => $loan_individual['amount'][$index] ?? null,
                        'outstanding' => $loan_individual['outstanding'][$index] ?? null,
                        'emi' => $loan_individual['emi'][$index] ?? null,
                        'overdue_amount' => $loan_individual['overdue_amount'][$index] ?? null,
                        'overdue_since' => $loan_individual['overdue_since'][$index] ?? null
                    ]);
                }
            }
        }
    }
}
