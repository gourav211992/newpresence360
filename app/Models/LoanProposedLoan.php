<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanProposedInsurancePolicy;
use App\Models\LoanProposedTermDeposit;
use App\Models\LoanProposedMoveableAsset;

class LoanProposedLoan extends Model
{
    protected $table = 'erp_loan_proposed_loans';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function loanProposedInsurancePolicy()
    {
        return $this->hasMany(LoanProposedInsurancePolicy::class, 'loan_proposed_loan_id');
    }

    public function loanProposedTermDeposit()
    {
        return $this->hasMany(LoanProposedTermDeposit::class, 'loan_proposed_loan_id');
    }

    public function loanProposedMoveableAsset()
    {
        return $this->hasMany(LoanProposedMoveableAsset::class, 'loan_proposed_loan_id');
    }

    public static function createUpdateProposed($request, $edit_loanId, $homeLoan){
        $proposed_loan = $request->input('ProposedLoan', []);
        if(isset($proposed_loan['common_data'])){
            $loan_proposed = static::updateOrCreate([
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
            ],[
                'home_loan_id' => $homeLoan->id,
                'outside_borrowing' => $proposed_loan['common_data']['outside_borrowing'] ?? null,
                'loan_amount_request' => $proposed_loan['common_data']['loan_amount_request'] ?? null,
                'interest_rate' => $proposed_loan['common_data']['interest_rate'] ?? null,
                'floating_fixed' => $proposed_loan['common_data']['floating_fixed'] ?? null,
                'margin' => $proposed_loan['common_data']['margin'] ?? null,
                'bank_name' => $proposed_loan['common_data']['bank_name'] ?? null,
                'loan_credit' => $proposed_loan['common_data']['loan_credit'] ?? null,
                'security_schedule' => $proposed_loan['common_data']['security_schedule'] ?? null,
                'present_outstanding' => $proposed_loan['common_data']['present_outstanding'] ?? null,
                'liabilities' => $proposed_loan['common_data']['liabilities'] ?? null
            ]);

            if($loan_proposed){
                LoanProposedInsurancePolicy::where('loan_proposed_loan_id', $loan_proposed->id)->delete();
                foreach ($proposed_loan['policy_no'] as $index => $policy_no) {
                    if($index == 0){
                        continue;
                    }
                    LoanProposedInsurancePolicy::create([
                        'loan_proposed_loan_id' => $loan_proposed->id,
                        'policy_no' => $proposed_loan['policy_no'][$index] ?? null,
                        'issuance_date' => $proposed_loan['issuance_date'][$index] ?? null,
                        'sum_insured' => $proposed_loan['sum_insured'][$index] ?? null,
                        'co_branch' => $proposed_loan['co_branch'][$index] ?? null,
                        'annual_premium' => $proposed_loan['annual_premium'][$index] ?? null,
                        'premium_paid' => $proposed_loan['premium_paid'][$index] ?? null
                    ]);
                }

                LoanProposedTermDeposit::where('loan_proposed_loan_id', $loan_proposed->id)->delete();
                foreach ($proposed_loan['post_office'] as $index => $post_office) {
                    if($index == 0){
                        continue;
                    }
                    LoanProposedTermDeposit::create([
                        'loan_proposed_loan_id' => $loan_proposed->id,
                        'post_office' => $proposed_loan['post_office'][$index] ?? null,
                        'instrument_date' => $proposed_loan['instrument_date'][$index] ?? null,
                        'face_value' => $proposed_loan['face_value'][$index] ?? null,
                        'resent_value' => $proposed_loan['resent_value'][$index] ?? null,
                        'due_date' => $proposed_loan['due_date'][$index] ?? null,
                        'whether_encumbered' => $proposed_loan['whether_encumbered'][$index] ?? null
                    ]);
                }

                LoanProposedMoveableAsset::where('loan_proposed_loan_id', $loan_proposed->id)->delete();
                foreach ($proposed_loan['description'] as $index => $description) {
                    if($index == 0){
                        continue;
                    }
                    LoanProposedMoveableAsset::create([
                        'loan_proposed_loan_id' => $loan_proposed->id,
                        'description' => $proposed_loan['description'][$index] ?? null,
                        'acquiring_year' => $proposed_loan['acquiring_year'][$index] ?? null,
                        'purchase_price' => $proposed_loan['purchase_price'][$index] ?? null,
                        'present_market_val' => $proposed_loan['present_market_val'][$index] ?? null,
                        'valuation_date' => $proposed_loan['valuation_date'][$index] ?? null
                    ]);
                }
            }
        }
    }
}
