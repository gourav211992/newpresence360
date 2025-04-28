<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanGuarantorCoApplicantInsurancePolicy;
use App\Models\LoanGuarantorCoApplicantTermDeposit;
use App\Models\LoanGuarantorCoApplicantMoveableAsset;
use App\Models\LoanGuarantorCoApplicantLegalHeir;

class LoanGuarantorCoApplicant extends Model
{
    protected $table = 'erp_loan_guarantor_co_applicants';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function loanGuarantorCoApplicantInsurancePolicy()
    {
        return $this->hasMany(LoanGuarantorCoApplicantInsurancePolicy::class, 'loan_guarantor_co_applicant_id');
    }

    public function loanGuarantorCoApplicantTermDeposit()
    {
        return $this->hasMany(LoanGuarantorCoApplicantTermDeposit::class, 'loan_guarantor_co_applicant_id');
    }

    public function loanGuarantorCoApplicantMoveableAsset()
    {
        return $this->hasMany(LoanGuarantorCoApplicantMoveableAsset::class, 'loan_guarantor_co_applicant_id');
    }

    public function loanGuarantorCoApplicantLegalHeir()
    {
        return $this->hasMany(LoanGuarantorCoApplicantLegalHeir::class, 'loan_guarantor_co_applicant_id');
    }

    public static function createUpdateGuarantor($request, $edit_loanId, $homeLoan){
        $guarantor_co_appli = $request->input('GuarantorCo', []);
        if(isset($guarantor_co_appli['common_data'])){
            $image_customer = null;
            if ($request->has('image_co')) {
                $path = $request->file('image_co')->store('loan_images', 'public');
                $image_customer = $path;
            }elseif($request->has('stored_image_co')){
                $image_customer = $request->stored_image_co;
            }else{
                $image_customer = null;
            }
            $loan_guarantor_co_applicant = LoanGuarantorCoApplicant::updateOrCreate([
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
            ],[
                'home_loan_id' => $homeLoan->id,
                'name' => $guarantor_co_appli['common_data']['name'] ?? null,
                'fm_name' => $guarantor_co_appli['common_data']['fm_name'] ?? null,
                'encumbered' => $guarantor_co_appli['common_data']['encumbered'] ?? null,
                'land_plot' => $guarantor_co_appli['common_data']['land_plot'] ?? null,
                'agriculture_land' => $guarantor_co_appli['common_data']['agriculture_land'] ?? null,
                'h_godowns' => $guarantor_co_appli['common_data']['h_godowns'] ?? null,
                'other' => $guarantor_co_appli['common_data']['other'] ?? null,
                'est_val' => $guarantor_co_appli['common_data']['est_val'] ?? null,
                'oth_liability' => $guarantor_co_appli['common_data']['oth_liability_co'] ?? null,
                'bank_name' => $guarantor_co_appli['common_data']['bank_name_co'] ?? null,
                'purpose' => $guarantor_co_appli['common_data']['purpose_co'] ?? null,
                'loan_amount' => $guarantor_co_appli['common_data']['loan_amount_co'] ?? null,
                'overdue' => $guarantor_co_appli['common_data']['overdue_co'] ?? null,
                'personal_guarantee' => $guarantor_co_appli['common_data']['personal_guarantee_co'] ?? null,
                'person_behalf' => $guarantor_co_appli['common_data']['person_behalf_co'] ?? null,
                'commitment_amnt' => $guarantor_co_appli['common_data']['commitment_amnt_co'] ?? null,
                'image_co' => $image_customer
            ]);

            if($loan_guarantor_co_applicant){ 
                LoanGuarantorCoApplicantInsurancePolicy::where('loan_guarantor_co_applicant_id', $loan_guarantor_co_applicant->id)->delete();
                foreach ($guarantor_co_appli['lip_policy_no'] as $index => $lip_policy_no) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarantorCoApplicantInsurancePolicy::create([
                        'loan_guarantor_co_applicant_id' => $loan_guarantor_co_applicant->id,
                        'policy_no' => $guarantor_co_appli['lip_policy_no'][$index] ?? null,
                        'maturity_date' => $guarantor_co_appli['lip_maturity_date'][$index] ?? null,
                        'sum_insured' => $guarantor_co_appli['lip_sum_insured'][$index] ?? null,
                        'co_branch' => $guarantor_co_appli['lip_co_branch'][$index] ?? null,
                        'last_premium' => $guarantor_co_appli['lip_last_premium'][$index] ?? null,
                        'surrender_value' => $guarantor_co_appli['lip_surrender_value'][$index] ?? null
                    ]);
                }

                LoanGuarantorCoApplicantTermDeposit::where('loan_guarantor_co_applicant_id', $loan_guarantor_co_applicant->id)->delete();
                foreach ($guarantor_co_appli['market_val'] as $index => $market_val) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarantorCoApplicantTermDeposit::create([
                        'loan_guarantor_co_applicant_id' => $loan_guarantor_co_applicant->id,
                        'description' => $guarantor_co_appli['description'][$index] ?? null,
                        'face_value' => $guarantor_co_appli['face_value'][$index] ?? null,
                        'units' => $guarantor_co_appli['units'][$index] ?? null,
                        'market_val' => $guarantor_co_appli['market_val'][$index] ?? null
                    ]);
                }

                LoanGuarantorCoApplicantMoveableAsset::where('loan_guarantor_co_applicant_id', $loan_guarantor_co_applicant->id)->delete();
                foreach ($guarantor_co_appli['valuation_date'] as $index => $valuation_date) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarantorCoApplicantMoveableAsset::create([
                        'loan_guarantor_co_applicant_id' => $loan_guarantor_co_applicant->id,
                        'description' => $guarantor_co_appli['description_moveable'][$index] ?? null,
                        'purchase_price' => $guarantor_co_appli['purchase_price'][$index] ?? null,
                        'market_val' => $guarantor_co_appli['market_val_moveable'][$index] ?? null,
                        'valuation_date' => $guarantor_co_appli['valuation_date'][$index] ?? null
                    ]);
                }

                LoanGuarantorCoApplicantLegalHeir::where('loan_guarantor_co_applicant_id', $loan_guarantor_co_applicant->id)->delete();
                foreach ($guarantor_co_appli['present_addr'] as $index => $present_addr) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarantorCoApplicantLegalHeir::create([
                        'loan_guarantor_co_applicant_id' => $loan_guarantor_co_applicant->id,
                        'name' => $guarantor_co_appli['name'][$index] ?? null,
                        'relation' => $guarantor_co_appli['relation'][$index] ?? null,
                        'age' => $guarantor_co_appli['age'][$index] ?? null,
                        'present_addr' => $guarantor_co_appli['present_addr'][$index] ?? null
                    ]);
                }
            }
        }
    }
}
