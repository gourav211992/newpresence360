<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanOtherGuarantor;

class LoanOtherDetail extends Model
{
    protected $table = 'erp_loan_other_details';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public static function createUpdateOtherDetail($request, $edit_loanId, $homeLoan){
        $other_detail = $request->input('OtherDetail', []);
        if(isset($other_detail['common_data'])){
            $co_type = isset($other_detail['common_data']['co_type']) ? $other_detail['common_data']['co_type'] : null;
            $guar_type = isset($other_detail['common_data']['guar_type']) ? $other_detail['common_data']['guar_type'] : null;
            if(!empty($co_type)){
                $other_detail_data = static::updateOrCreate([
                    'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                ], [
                    'home_loan_id' => $homeLoan->id,
                    'type' => $other_detail['common_data']['co_type'] ?? null,
                    'name' => $other_detail['common_data']['co_name'] ?? null,
                    'dob' => $other_detail['common_data']['co_dob'] ?? null,
                    'fm_name' => $other_detail['common_data']['co_fm_name'] ?? null,
                    'applicant_relation' => $other_detail['common_data']['co_applicant_relation'] ?? null,
                    'address' => $other_detail['common_data']['co_address'] ?? null,
                    'city' => $other_detail['common_data']['co_city'] ?? null,
                    'state' => $other_detail['common_data']['co_state'] ?? null,
                    'pin_code' => $other_detail['common_data']['co_pin_code'] ?? null,
                    'occupation' => $other_detail['common_data']['co_occupation'] ?? null,
                    'phn_fax' => $other_detail['common_data']['co_phn_fax'] ?? null,
                    'email' => $other_detail['common_data']['co_email'] ?? null,
                    'pan_gir_no' => $other_detail['common_data']['co_pan_gir_no'] ?? null
                ]);
            }else{
                $other_detail_data = static::updateOrCreate([
                    'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                ], [
                    'home_loan_id' => $homeLoan->id,
                    'type' => $other_detail['common_data']['co_type'] ?? null
                ]);
            }

            if(!empty($guar_type)){
                $other_detail_guar = LoanOtherGuarantor::updateOrCreate([
                    'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                ], [
                    'home_loan_id' => $homeLoan->id,
                    'type' => $other_detail['common_data']['guar_type'] ?? null,
                    'name' => $other_detail['common_data']['guar_name'] ?? null,
                    'dob' => $other_detail['common_data']['guar_dob'] ?? null,
                    'fm_name' => $other_detail['common_data']['guar_fm_name'] ?? null,
                    'applicant_relation' => $other_detail['common_data']['guar_applicant_relation'] ?? null,
                    'address' => $other_detail['common_data']['guar_address'] ?? null,
                    'city' => $other_detail['common_data']['guar_city'] ?? null,
                    'state' => $other_detail['common_data']['guar_state'] ?? null,
                    'pin_code' => $other_detail['common_data']['guar_pin_code'] ?? null,
                    'occupation' => $other_detail['common_data']['guar_occupation'] ?? null,
                    'phn_fax' => $other_detail['common_data']['guar_phn_fax'] ?? null,
                    'email' => $other_detail['common_data']['guar_email'] ?? null,
                    'pan_gir_no' => $other_detail['common_data']['guar_pan_gir_no'] ?? null,
                    'net_annu_income' => $other_detail['common_data']['guar_net_annu_income'] ?? null, 
                ]);
            }else{
                $other_detail_guar = LoanOtherGuarantor::updateOrCreate([
                    'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                ], [
                    'home_loan_id' => $homeLoan->id,
                    'type' => $other_detail['common_data']['guar_type'] ?? null
                ]);
            }
        }
    }
}
