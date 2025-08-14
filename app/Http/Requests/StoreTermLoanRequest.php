<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Age;
use App\Rules\ValidAppliNo;

class StoreTermLoanRequest extends FormRequest
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
        if ($this->status_val == 'draft') {
            return [
                'series' => 'required',
                'appli_no' => ['required'],
                'ref_no' => 'required|numeric|min:0',
                'loan_amount' => 'required|numeric|min:0',
                'f_name' => 'required',
                'l_name' => 'required',
                'f_name_pro' => 'required',
                'l_name_pro' => 'required',
                'scheme_for' => 'required',
            ];
        }
        if ($this->status_val == 'submitted') {
            return [
                'series' => 'required',
                'appli_no' => ['required'],
                'ref_no' => 'required|numeric|min:0',
                'loan_amount' => 'required|numeric|min:0',
                'f_name' => 'required',
                'l_name' => 'required',
                'f_name_pro' => 'required',
                'l_name_pro' => 'required',
                'scheme_for' => 'required',
                'Address.pin_code' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
                'Address.registered_offc_tele' => 'nullable|min:0|max:10',
                'Address.registered_offc_mobile' => 'nullable|min:0|max:10',
                'Address.registered_offc_email_id' => 'nullable|email',
                'Address.registered_offc_fax_num' => 'nullable|min:0|max:10',
                'Address.factory_tele' => 'nullable|min:0|max:10',
                'Address.factory_mobile' => 'nullable|min:0|max:10',
                'Address.factory_email_id' => 'nullable|email',
                'Address.factory_fax_num' => 'nullable|min:0|max:10',
                'TermNetWorth.common_data.nw_unit_phone' => 'nullable|min:0|max:10',
                'TermNetWorth.common_data.nw_resi_mobile' => 'nullable|min:0|max:10',
                'TermNetWorth.common_data.nw_resi_phone' => 'nullable|min:0|max:10',
                'tr_email' => 'required|email'
            ];
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
            'appli_no.required' => 'The Applicant number is required.',
            'ref_no.required' => 'The Reference Number is required.',
            'loan_amount.required' => 'The Loan Amount is required.',
            'f_name.required' => 'The Concern First name is required.',
            'l_name.required' => 'The Concern Last name is required.',
            'f_name_pro.required' => 'The Promoter First name is required.',
            'l_name_pro.required' => 'The Promoter Last name is required.',
            'scheme_for.required' => 'The Scheme For is required.',
            'Address.pin_code.regex' => 'The PIN code must be a 6-digit number starting with a non-zero digit.',
            'Address.registered_offc_tele.required' => 'The registered office telephone number is required.',
            'Address.registered_offc_tele.min' => 'The registered office telephone number must be at least 0 characters.',
            'Address.registered_offc_tele.max' => 'The registered office telephone number may not be greater than 10 characters.',
            'Address.registered_offc_mobile.required' => 'The registered office mobile number is required.',
            'Address.registered_offc_mobile.min' => 'The registered office mobile number must be at least 0 characters.',
            'Address.registered_offc_mobile.max' => 'The registered office mobile number may not be greater than 10 characters.',
            'Address.registered_offc_email_id.required' => 'The registered office email ID is required.',
            'Address.registered_offc_email_id.email' => 'The registered office email ID must be a valid email address.',
            'Address.registered_offc_fax_num.required' => 'The registered office fax number is required.',
            'Address.registered_offc_fax_num.min' => 'The registered office fax number must be at least 0 characters.',
            'Address.registered_offc_fax_num.max' => 'The registered office fax number may not be greater than 10 characters.',
            'Address.factory_tele.required' => 'The factory telephone number is required.',
            'Address.factory_tele.min' => 'The factory telephone number must be at least 0 characters.',
            'Address.factory_tele.max' => 'The factory telephone number may not be greater than 10 characters.',
            'Address.factory_mobile.required' => 'The factory mobile number is required.',
            'Address.factory_mobile.min' => 'The factory mobile number must be at least 0 characters.',
            'Address.factory_mobile.max' => 'The factory mobile number may not be greater than 10 characters.',
            'Address.factory_email_id.required' => 'The factory email ID is required.',
            'Address.factory_email_id.email' => 'The factory email ID must be a valid email address.',
            'Address.factory_fax_num.required' => 'The factory fax number is required.',
            'Address.factory_fax_num.min' => 'The factory fax number must be at least 0 characters.',
            'Address.factory_fax_num.max' => 'The factory fax number may not be greater than 10 characters.',
            'TermNetWorth.common_data.nw_unit_phone' => 'The Phone No. may not be greater than 10 characters.',
            'TermNetWorth.common_data.nw_resi_mobile' => 'The Mobile No. may not be greater than 10 characters.',
            'TermNetWorth.common_data.nw_resi_phone' => 'The Phone No. may not be greater than 10 characters.',
            'tr_email.required' => 'The Email Address is required.',
            'tr_email.email' => 'The Email Address must be a valid email address.',
        ];
    }
}
