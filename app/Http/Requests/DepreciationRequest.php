<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepreciationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Change if authorization logic is needed
    }
//     protected function prepareForValidation()
// {
//     dd($this->all()); // Dumps all incoming request data
// }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'book_code' => 'required|string',
            'doc_no' => 'required|numeric|min:1',
            'book_id' => 'required|integer', // Assuming 'books' is the table name
            'document_number' => 'required|string',
            'document_date' => 'required|date|date_format:Y-m-d',
            'period' => 'required',
            'assets' => 'required|array|min:1',
            'assets.*' => 'integer|exists:erp_finance_fixed_asset_registration,id', // Assuming 'assets' is the table name
            'grand_total_current_value' => 'required|numeric|min:0',
            'grand_total_dep_amount' => 'required|numeric|min:0',
            'grand_total_after_dep_value' => 'required|numeric|min:0',
        ];
    }

    /**
     * Custom error messages (optional).
     */
    public function messages()
    {
        return [
            'book_code.required' => 'Book code is required.',
            'book_code.string' => 'Book code must be a string.',

            'doc_no.required' => 'Document number is required.',
            'doc_no.numeric' => 'Document number must be numeric.',
            'doc_no.min' => 'Document number must be at least 1.',

            'book_id.required' => 'Book ID is required.',
            'book_id.integer' => 'Book ID must be an integer.',

            'document_number.required' => 'Document number is required.',
            'document_number.string' => 'Document number must be a string.',

            'document_date.required' => 'Document date is required.',
            'document_date.date' => 'Document date must be a valid date.',
            'document_date.date_format' => 'Document date must be in YYYY-MM-DD format.',

            'period.required' => 'Period is required.',

            'assets.required' => 'At least one asset must be selected.',
            'assets.array' => 'Assets must be an array.',
            'assets.min' => 'At least one asset must be selected.',
            'assets.*.integer' => 'Each asset ID must be an integer.',
            'assets.*.exists' => 'One or more selected assets do not exist.',

            'grand_total_current_value.required' => 'Grand total current value is required.',
            'grand_total_current_value.numeric' => 'Grand total current value must be a number.',
            'grand_total_current_value.min' => 'Grand total current value cannot be negative.',

            'grand_total_dep_amount.required' => 'Grand total depreciation amount is required.',
            'grand_total_dep_amount.numeric' => 'Grand total depreciation amount must be a number.',
            'grand_total_dep_amount.min' => 'Grand total depreciation amount cannot be negative.',

            'grand_total_after_dep_value.required' => 'Grand total after depreciation value is required.',
            'grand_total_after_dep_value.numeric' => 'Grand total after depreciation value must be a number.',
            'grand_total_after_dep_value.min' => 'Grand total after depreciation value cannot be negative.',
        ];
    }
}
