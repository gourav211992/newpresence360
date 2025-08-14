<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EngagementTrackingRequest extends FormRequest
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
//            'feedback_id' => 'required|string|max:255',
            'userable_id' => 'required',
//            'userable_type' => 'required',
            'interaction_type_id' => 'required|exists:erp_interaction_types,id',
            'user_type_id' => 'required|exists:erp_stakeholder_user_types,id',
            'document_date' => 'required|date',
            'status' => 'required|string|max:1000',
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

            'interaction_type_id.required' => 'The interaction type is required.',
            'interaction_type_id.exists' => 'The selected interaction type does not exist.',

            'user_type_id.required' => 'The user type is required.',
            'user_type_id.exists' => 'The selected user type does not exist.',

            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date.',

            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description must not exceed 1000 characters.',

            'outcomes.required' => 'The outcomes is required.',
            'outcomes.string' => 'The outcomes must be a valid string.',
            'outcomes.max' => 'The outcomes must not exceed 5000 characters.',

            'book_code.string' => 'The book code must be a valid string.',
            'book_code.max' => 'The book code must not exceed 255 characters.',

            'doc_number_type.string' => 'The document number type must be a valid string.',
            'doc_number_type.max' => 'The document number type must not exceed 255 characters.',

            'doc_reset_pattern.string' => 'The document reset pattern must be a valid string.',
            'doc_reset_pattern.max' => 'The document reset pattern must not exceed 255 characters.',

            'doc_prefix.string' => 'The document prefix must be a valid string.',
            'doc_prefix.max' => 'The document prefix must not exceed 255 characters.',

            'doc_suffix.string' => 'The document suffix must be a valid string.',
            'doc_suffix.max' => 'The document suffix must not exceed 255 characters.',

            'doc_no.string' => 'The document number must be a valid string.',
            'doc_no.max' => 'The document number must not exceed 255 characters.',
        ];
    }
}
