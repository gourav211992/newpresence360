<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintManagementRequest extends FormRequest
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
        return [
//            'complaint_id' => 'required|string|max:255',
            'userable_id' => 'required',
//            'userable_type' => 'required',
            'user_type_id' => 'required|exists:erp_stakeholder_user_types,id',
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'description' => 'required|string|max:5000',
            'book_code' => 'sometimes',
            'doc_number_type' => 'sometimes|string|max:255',
            'doc_reset_pattern' => 'sometimes|string|max:255',
            'doc_prefix' => 'nullable|max:255',
            'doc_suffix' => 'nullable|max:255',
            'doc_no' => 'sometimes|string|max:255',
            'document_number' => 'sometimes',
            'book_id' => 'sometimes',
            'party_name' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'user_type_id.required' => 'The user type is required.',
            'user_type_id.exists' => 'The selected user type does not exist.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date.',
            'description.required' => 'The issue description is required.',
            'notes.string' => 'The notes must be a valid string.',
            'book_code.string' => 'The book code must be a valid string.',
            'doc_number_type.string' => 'The document number type must be a valid string.',
            'doc_reset_pattern.string' => 'The document reset pattern must be a valid string.',
            'doc_no.string' => 'The document number must be a valid string.',
        ];
    }
}
