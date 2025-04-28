<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Age;
use App\Rules\ValidAppliNo;
use App\Rules\ImageDimensions;

class StoreHomeLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Remove commas from the loan_amount field
        if ($this->has('loan_amount')) {
            $this->merge([
                'loan_amount' => str_replace(',', '', $this->loan_amount),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if($this->status_val == 'draft'){
            $rules = [
                'series' => 'required',
                'appli_no' => 'required',
                'ref_no' => 'required|numeric|min:0',
                'loan_amount' => 'required|numeric|min:0',
                'scheme_for' => 'required',
                'f_name' => 'required',
                'l_name' => 'required',
                'gir_no' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
                'email' => 'required|email',
                'mobile' => 'required|min:0|max:10',
                'no_of_depends' => 'required',
                'earning_member' => 'required',
            ];
            return $rules;
        }
        if($this->status_val == 'submitted'){
            $widthInPixels = (25 / 25.4) * 96;
            $heightInPixels = (35 / 25.4) * 96;
            $rules = [
                'series' => 'required',
                'appli_no' => ['required'],
                'ref_no' => 'required|numeric|min:0',
                'loan_amount' => 'required|numeric|min:0',
                'scheme_for' => 'required',
                'f_name' => 'required',
                'l_name' => 'required',
                'gir_no' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
                'email' => 'required|email',
                'mobile' => 'required|min:0|max:10',
                'no_of_depends' => 'required',
                'earning_member' => 'required',
                'dob' => ['nullable', new Age],
                'Address.pin_code' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
                'Address.p_pin' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
                'OtherDetail.common_data.guar_pin_code' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
                'OtherDetail.common_data.co_pin_code' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
                'EmployerDetail.pin_code' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
                'Address.residence_phn' => ['nullable', 'numeric', 'digits_between:1,10'],
                'Address.p_resi_code' => ['nullable', 'numeric', 'digits_between:1,10'],
                'Address.office_phn' => ['nullable', 'numeric', 'digits_between:1,10'],
                'Address.fax_num' => ['nullable', 'numeric', 'digits_between:1,10'],
                'EmployerDetail.phn_no' => ['nullable', 'numeric', 'digits_between:1,10'],
                'EmployerDetail.fax_num' => ['nullable', 'numeric', 'digits_between:1,10'],
                'OtherDetail.common_data.co_phn_fax' => ['nullable', 'numeric', 'digits_between:1,10'],
                'OtherDetail.common_data.co_pan_gir_no' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
                'EmployerDetail.company_email' => 'nullable|email',
                'LoanIncIdividual.common_data.gross_monthly_income' => 'required|numeric|min:0',
                'LoanIncIdividual.common_data.net_monthly_income' => 'required|numeric|min:0',
                'OtherDetail.common_data.co_dob' => ['nullable', new Age],
                // 'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024', new ImageDimensions($widthInPixels, $heightInPixels)]
            ];

            if (isset($this->OtherDetail['common_data']['guar_type']) && $this->OtherDetail['common_data']['guar_type'] == 1) {
                $guarantorRules = [
                    'OtherDetail.common_data.guar_name' => 'required',
                    'OtherDetail.common_data.guar_dob' => ['required', new Age],
                    'OtherDetail.common_data.guar_address' => 'required',
                    'OtherDetail.common_data.guar_phn_fax' => ['required', 'numeric', 'digits_between:1,10'],
                    'OtherDetail.common_data.guar_pan_gir_no' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/']
                ];

                $rules = array_merge($rules, $guarantorRules);
            }

            return $rules;
        }

        return [];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ref_no.required' => 'The Reference Number is required.',
            'loan_amount.required' => 'The Loan Amount is required.',
            'scheme_for.required' => 'The Scheme for is required.',
            'f_name.required' => 'The First name is required.',
            'l_name.required' => 'The Last name is required.',
            'gir_no.required' => 'The PAN/GIR NO is required.',
            'gir_no.regex' => 'The PAN/GIR NO must be in the format XXXXX0000X.',
            'email.required' => 'The Email Address is required.',
            'email.email' => 'The Email Address must be a valid email address.',
            'mobile.required' => 'The Mobile Number is required.',
            'no_of_depends.required' => 'The no. of depends is required.',
            'dob.nullable' => 'The Date of Birth is not required but must be valid if provided.',
            'appli_no.required' => 'The Applicant number is required.',
            'Address.pin_code.regex' => 'The PIN code must be a 6-digit number starting with a non-zero digit.',
            'Address.p_pin.regex' => 'The PIN code must be a 6-digit number starting with a non-zero digit.',
            'OtherDetail.common_data.guar_pin_code.regex' => 'The PIN code must be a 6-digit number starting with a non-zero digit.',
            'OtherDetail.common_data.co_pin_code.regex' => 'The PIN code must be a 6-digit number starting with a non-zero digit.',
            'EmployerDetail.pin_code.regex' => 'The PIN code must be a 6-digit number starting with a non-zero digit.',
            'Address.residence_phn.numeric' => 'The residence phone number must be a valid numeric value.',
            'Address.residence_phn.digits_between' => 'The residence phone number must be between 1 and 10 digits.',
            'Address.p_resi_code.numeric' => 'The residence phone number must be a valid numeric value.',
            'Address.p_resi_code.digits_between' => 'The residence phone number must be between 1 and 10 digits.',
            'Address.office_phn.numeric' => 'The office phone number must be a valid numeric value.',
            'Address.office_phn.digits_between' => 'The office phone number must be between 1 and 10 digits.',
            'Address.fax_num.numeric' => 'The fax number must be a valid numeric value.',
            'Address.fax_num.digits_between' => 'The fax number must be between 1 and 10 digits.',
            'EmployerDetail.phn_no.numeric' => 'The employer phone number must be a valid numeric value.',
            'EmployerDetail.phn_no.digits_between' => 'The employer phone number must be between 1 and 10 digits.',
            'EmployerDetail.fax_num.numeric' => 'The employer fax number must be a valid numeric value.',
            'EmployerDetail.fax_num.digits_between' => 'The employer fax number must be between 1 and 10 digits.',
            'OtherDetail.common_data.guar_phn_fax.numeric' => 'The guarantor phone or fax number must be a valid numeric value.',
            'OtherDetail.common_data.guar_phn_fax.digits_between' => 'The guarantor phone or fax number must be between 1 and 10 digits.',
            'OtherDetail.common_data.co_phn_fax.numeric' => 'The Co-Applicant phone or fax number must be a valid numeric value.',
            'OtherDetail.common_data.co_phn_fax.digits_between' => 'The Co-Applicant phone or fax number must be between 1 and 10 digits.',
            'OtherDetail.common_data.guar_pan_gir_no.regex' => 'The Guarantor PAN/GIR NO must be in the format XXXXX0000X',
            'OtherDetail.common_data.co_pan_gir_no.regex' => 'The Company PAN/GIR NO must be in the format XXXXX0000X',
            'EmployerDetail.company_email' => 'The company email field must be a valid email address.',
            'OtherDetail.common_data.guar_email' => 'The email field must be a valid email address.',
            'OtherDetail.common_data.co_email' => 'The email field must be a valid email address.',
            'LoanIncIdividual.common_data.gross_monthly_income' => 'The Gross Monthly Income is required.',
            'LoanIncIdividual.common_data.net_monthly_income' => 'The Net Monthly Income is required.',
            'OtherDetail.common_data.guar_name' => 'The Guarantor name is required.',
            'OtherDetail.common_data.guar_dob' => 'Date of Birth is required and must be at least 18 years.',
            'OtherDetail.common_data.guar_address' => 'The Guarantor Address is required.',
            'OtherDetail.common_data.guar_phn_fax' => 'The Guarantor Phone/Fax is required.',
            'OtherDetail.common_data.guar_pan_gir_no' => 'The Guarantor PAN/GIR No. is required.'
        ];
    }
}
