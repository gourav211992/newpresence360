<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class CreateLeaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->status == 'draft') {
            return [
                'document_no' => 'required',
                'document_date' => 'required|date',
                'lease_time' => 'required|numeric|min:0',
                'sub_total_amount' => 'required|decimal:2|min:0',
                'lease_start_date' => 'required',
                'lease_end_date' => 'required',
                'repayment_period_type' => 'required',
                'repayment_period' => 'required|numeric|min:0',
                'deposit_refundable' => 'nullable|boolean',
                'total_amount' => 'required|decimal:2|min:0',

                'plot_details.*.land_parcel_id' => 'required',
                'plot_details.*.land_plot_id' => 'required',
                'plot_details.*.plot_document_no' => 'required',
                'plot_details.*.plot_area' => 'required',
                'plot_details.*.dimension' => 'nullable',
                'plot_details.*.address' => 'required',
                'plot_details.*.land_property_type' => 'required',
                'plot_details.*.land_lease_amount' => 'required',
                'plot_details.*.land_total_amount' => 'required',
                
                
            ];
        }

        if ($this->status == 'submitted') {
            //dd($this->all());
            return [
                'document_no' => 'required',
                'document_date' => 'required|date',
                'customer_id' => 'required|numeric|min:0',
                'currency_id' => 'required|numeric|min:0',
                'exchage_rate' => 'nullable|numeric|min:0',
                'agreement_no' => 'required|string|max:255',
                'lease_time' => 'required|numeric|min:0',
                'lease_start_date' => 'required',
                'lease_end_date' => 'required',
                'repayment_period_type' => 'required',
                'repayment_period' => 'required|numeric|min:0',
                'deposit_refundable' => 'nullable|boolean',
                'sub_total_amount' => 'required|decimal:2|min:0',
                'total_amount' => 'required|decimal:2|min:0',

                'plot_details.*.land_parcel_id' => 'required',
                'plot_details.*.land_plot_id' => 'required',
                'plot_details.*.plot_document_no' => 'required',
                'plot_details.*.plot_area' => 'required',
                'plot_details.*.dimension' => 'nullable',
                'plot_details.*.address' => 'required',
                'plot_details.*.land_property_type' => 'required',
                'plot_details.*.land_lease_amount' => 'required',
                'plot_details.*.land_total_amount' => 'required',
                'addresses' => 'nullable|array',
                'addresses.*.country_id' => 'nullable|exists:countries,id',
                'addresses.*.state_id' => 'nullable|exists:states,id',
                'addresses.*.city_id' => 'nullable|exists:cities,id',
                'addresses.*.pincode' => 'nullable|string|max:10',
                'addresses.*.address' => 'nullable|string|max:255',

            ];
        }


        return [];
    }

    public function messages(): array
{
    return [
        'document_no.required' => 'The document number is required.',
        'document_date.required' => 'The document date is required.',
        'document_date.date' => 'The document date must be a valid date.',

        'customer_id.required' => 'The customer ID is required.',
        'customer_id.numeric' => 'The customer ID must be a number.',
        'customer_id.min' => 'The customer ID must be at least 0.',

        'currency_id.required' => 'The currency ID is required.',
        'currency_id.numeric' => 'The currency ID must be a number.',
        'currency_id.min' => 'The currency ID must be at least 0.',

        'exchage_rate.numeric' => 'The exchange rate must be a number.',
        'exchage_rate.min' => 'The exchange rate must be at least 0.',

        'agreement_no.required' => 'The agreement number is required.',
        'agreement_no.string' => 'The agreement number must be a string.',
        'agreement_no.max' => 'The agreement number may not be greater than 255 characters.',

        'lease_time.required' => 'The lease time is required.',
        'lease_time.numeric' => 'The lease time must be a number.',
        'lease_time.min' => 'The lease time must be at least 0.',

        'lease_start_date.required' => 'The lease start date is required.',
        'lease_end_date.required' => 'The lease end date is required.',

        'repayment_period_type.required' => 'The repayment period type is required.',
        'repayment_period.required' => 'The repayment period is required.',
        'repayment_period.numeric' => 'The repayment period must be a number.',
        'repayment_period.min' => 'The repayment period must be at least 0.',

        'deposit_refundable.boolean' => 'The deposit refundable field must be true or false.',

        'sub_total_amount.required' => 'The sub-total amount is required.',
        'sub_total_amount.decimal' => 'The sub-total amount must be a decimal with 2 decimal places.',
        'sub_total_amount.min' => 'The sub-total amount must be at least 0.',

        'total_amount.required' => 'The total amount is required.',
        'total_amount.decimal' => 'The total amount must be a decimal with 2 decimal places.',
        'total_amount.min' => 'The total amount must be at least 0.',

        'plot_details.*.land_parcel_id.required' => 'The land parcel ID in plot details is required.',
        'plot_details.*.land_plot_id.required' => 'The land plot ID in plot details is required.',
        'plot_details.*.plot_document_no.required' => 'The plot document number in plot details is required.',
        'plot_details.*.khasara_no.required' => 'The khasara number in plot details is required.',
        'plot_details.*.plot_area.required' => 'The plot area in plot details is required.',
        'plot_details.*.dimension.nullable' => 'The dimension in plot details is optional.',
        'plot_details.*.address.required' => 'The address in plot details is required.',
        'plot_details.*.land_property_type.required' => 'The land property type in plot details is required.',
        'plot_details.*.land_lease_amount.required' => 'The land lease amount in plot details is required.',
        'plot_details.*.land_total_amount.required' => 'The land total amount in plot details is required.',

        'addresses.array' => 'The addresses field must be an array.',
        'addresses.*.country_id.exists' => 'The selected country ID is invalid.',
        'addresses.*.state_id.exists' => 'The selected state ID is invalid.',
        'addresses.*.city_id.exists' => 'The selected city ID is invalid.',
        'addresses.*.pincode.string' => 'The pincode must be a string.',
        'addresses.*.pincode.max' => 'The pincode may not be greater than 10 characters.',
        'addresses.*.address.string' => 'The address must be a string.',
        'addresses.*.address.max' => 'The address may not be greater than 255 characters.',
    ];
}

}
