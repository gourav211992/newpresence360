<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanGuarApplicantInsurancePolicy;
use App\Models\LoanGuarApplicantLegalHeir;
use App\Models\LoanGuarApplicantMoveableAsset;
use App\Models\LoanGuarApplicantTermDeposit;

class LoanGuarApplicant extends Model
{
    protected $table = 'erp_loan_guar_applicants';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function loanGuarApplicantInsurancePolicies()
    {
        return $this->hasMany(LoanGuarApplicantInsurancePolicy::class, 'loan_guar_applicant_id');
    }

    public function loanGuarApplicantLegalHeirs()
    {
        return $this->hasMany(LoanGuarApplicantLegalHeir::class, 'loan_guar_applicant_id');
    }

    public function loanGuarApplicantMoveableAssets()
    {
        return $this->hasMany(LoanGuarApplicantMoveableAsset::class, 'loan_guar_applicant_id');
    }

    public function loanGuarApplicantTermDeposits()
    {
        return $this->hasMany(LoanGuarApplicantTermDeposit::class, 'loan_guar_applicant_id');
    }

    public static function createUpdateGuar($request, $edit_loanId, $homeLoan){
        $guarantor_co_appli = $request->input('GuarantorData', []);
        if(isset($guarantor_co_appli['common_data'])){
            $image_customer = null;
            if ($request->has('guarntr_image')) {
                $path = $request->file('guarntr_image')->store('loan_images', 'public');
                $image_customer = $path;
            }elseif($request->has('stored_guarntr_image')){
                $image_customer = $request->stored_guarntr_image;
            }else{
                $image_customer = null;
            }
            $loan_guar_applicant_id = LoanGuarApplicant::updateOrCreate([
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
            ],[
                'home_loan_id' => $homeLoan->id,
                'name' => $guarantor_co_appli['common_data']['guarntr_name'] ?? null,
                'fm_name' => $guarantor_co_appli['common_data']['guarntr_fm_name'] ?? null,
                'encumbered' => $guarantor_co_appli['common_data']['guarntr_encumbered'] ?? null,
                'land_plot' => $guarantor_co_appli['common_data']['guarntr_land_plot'] ?? null,
                'agriculture_land' => $guarantor_co_appli['common_data']['guarntr_agriculture_land'] ?? null,
                'h_godowns' => $guarantor_co_appli['common_data']['guarntr_h_godowns'] ?? null,
                'other' => $guarantor_co_appli['common_data']['guarntr_other'] ?? null,
                'est_val' => $guarantor_co_appli['common_data']['guarntr_est_val'] ?? null,
                'oth_liability' => $guarantor_co_appli['common_data']['guarntr_oth_liability'] ?? null,
                'bank_name' => $guarantor_co_appli['common_data']['guarntr_bank_name'] ?? null,
                'purpose' => $guarantor_co_appli['common_data']['guarntr_purpose'] ?? null,
                'loan_amount' => $guarantor_co_appli['common_data']['guarntr_loan_amount'] ?? null,
                'overdue' => $guarantor_co_appli['common_data']['guarntr_overdue'] ?? null,
                'personal_guarantee' => $guarantor_co_appli['common_data']['guarntr_personal_guarantee'] ?? null,
                'person_behalf' => $guarantor_co_appli['common_data']['guarntr_person_behalf'] ?? null,
                'commitment_amnt' => $guarantor_co_appli['common_data']['guarntr_commitment_amnt'] ?? null,
                'guarntr_image' => $image_customer
            ]);

            if($loan_guar_applicant_id){ 
                LoanGuarApplicantInsurancePolicy::where('loan_guar_applicant_id', $loan_guar_applicant_id->id)->delete();
                foreach ($guarantor_co_appli['guarntr_lip_policy_no'] as $index => $lip_policy_no) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarApplicantInsurancePolicy::create([
                        'loan_guar_applicant_id' => $loan_guar_applicant_id->id,
                        'policy_no' => $guarantor_co_appli['guarntr_lip_policy_no'][$index] ?? null,
                        'maturity_date' => $guarantor_co_appli['guarntr_lip_maturity_date'][$index] ?? null,
                        'sum_insured' => $guarantor_co_appli['guarntr_lip_sum_insured'][$index] ?? null,
                        'co_branch' => $guarantor_co_appli['guarntr_lip_co_branch'][$index] ?? null,
                        'last_premium' => $guarantor_co_appli['guarntr_lip_last_premium'][$index] ?? null,
                        'surrender_value' => $guarantor_co_appli['guarntr_lip_surrender_value'][$index] ?? null
                    ]);
                }

                LoanGuarApplicantTermDeposit::where('loan_guar_applicant_id', $loan_guar_applicant_id->id)->delete();
                foreach ($guarantor_co_appli['guarntr_market_val'] as $index => $market_val) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarApplicantTermDeposit::create([
                        'loan_guar_applicant_id' => $loan_guar_applicant_id->id,
                        'description' => $guarantor_co_appli['guarntr_description'][$index] ?? null,
                        'face_value' => $guarantor_co_appli['guarntr_face_value'][$index] ?? null,
                        'units' => $guarantor_co_appli['guarntr_units'][$index] ?? null,
                        'market_val' => $guarantor_co_appli['guarntr_market_val'][$index] ?? null
                    ]);
                }

                LoanGuarApplicantMoveableAsset::where('loan_guar_applicant_id', $loan_guar_applicant_id->id)->delete();
                foreach ($guarantor_co_appli['guarntr_valuation_date'] as $index => $valuation_date) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarApplicantMoveableAsset::create([
                        'loan_guar_applicant_id' => $loan_guar_applicant_id->id,
                        'description' => $guarantor_co_appli['guarntr_description_moveable'][$index] ?? null,
                        'purchase_price' => $guarantor_co_appli['guarntr_purchase_price'][$index] ?? null,
                        'market_val' => $guarantor_co_appli['guarntr_market_val_moveable'][$index] ?? null,
                        'valuation_date' => $guarantor_co_appli['guarntr_valuation_date'][$index] ?? null
                    ]);
                }

                LoanGuarApplicantLegalHeir::where('loan_guar_applicant_id', $loan_guar_applicant_id->id)->delete();
                foreach ($guarantor_co_appli['guarntr_present_addr'] as $index => $present_addr) {
                    if($index == 0){
                        continue;
                    }
                    LoanGuarApplicantLegalHeir::create([
                        'loan_guar_applicant_id' => $loan_guar_applicant_id->id,
                        'name' => $guarantor_co_appli['guarntr_name'][$index] ?? null,
                        'relation' => $guarantor_co_appli['guarntr_relation'][$index] ?? null,
                        'age' => $guarantor_co_appli['guarntr_age'][$index] ?? null,
                        'present_addr' => $guarantor_co_appli['guarntr_present_addr'][$index] ?? null
                    ]);
                }
            }
        }
    }
}
