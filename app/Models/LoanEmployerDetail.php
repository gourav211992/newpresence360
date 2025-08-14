<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanEmployerDetail extends Model
{
    protected $table = 'erp_loan_employer_details';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public static function createUpdateEmployer($request, $edit_loanId, $homeLoan){
        $employer_details = $request->EmployerDetail ?? [];
        if(count($employer_details) > 0){
            static::updateOrCreate([
                'home_loan_id' => $edit_loanId
            ], [
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                'employer_name' => $employer_details['employer_name'] ?? null,
                'department' => $employer_details['department'] ?? null,
                'address' => $employer_details['address'] ?? null,
                'city' => $employer_details['city'] ?? null,
                'state' => $employer_details['state'] ?? null,
                'pin_code' => $employer_details['pin_code'] ?? null,
                'phn_no' => $employer_details['phn_no'] ?? null,
                'ext_no' => $employer_details['ext_no'] ?? null,
                'fax_num' => $employer_details['fax_num'] ?? null,
                'company_email' => $employer_details['company_email'] ?? null,
                'designation' => $employer_details['designation'] ?? null,
                'years_with_employers' => $employer_details['years_with_employers'] ?? null,
                'contact_person' => $employer_details['contact_person'] ?? null,
                'previous_employer' => $employer_details['previous_employer'] ?? null,
                'retirement_age' => $employer_details['retirement_age'] ?? null,
                'other_assets' => isset($employer_details['other_assets']) ? json_encode($employer_details['other_assets']) : '[]',
            ]);
        }
    }
}
