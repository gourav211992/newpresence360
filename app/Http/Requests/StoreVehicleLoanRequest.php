<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Age;
use App\Rules\ValidAppliNo;

class StoreVehicleLoanRequest extends FormRequest
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
            return [
                'series' => 'required',
                'doc_number_type' => 'required',
                'appli_no' => ['required'],
                'ref_no' => 'required|numeric|min:0',
                'loan_amount' => 'required|numeric|min:0',
                'f_name' => 'required',
                'l_name' => 'required',
                'constitution' => 'required',
                've_email' => 'required|email',
            ];
        }
        if($this->status_val == 'submitted'){
            return [
                'series' => 'required',
                'appli_no' => ['required'],
                'ref_no' => 'required|numeric|min:0',
                'loan_amount' => 'required|numeric|min:0',
                'f_name' => 'required',
                'l_name' => 'required',
                'constitution' => 'required',
                've_email' => 'required|email',
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
            'doc_number_type.required' => 'The Doc Number Type Number is required.',
            'ref_no.required' => 'The Reference Number is required.',
            'loan_amount.required' => 'The Loan Amount is required.',
            'f_name.required' => 'The First name is required.',
            'l_name.required' => 'The Last name is required.',
            'constitution.required' => 'The Constitution is required.',
            've_email.required' => 'The Email Address is required.',
            've_email.email' => 'The Email Address must be a valid email address.',
        ];
    }
}
