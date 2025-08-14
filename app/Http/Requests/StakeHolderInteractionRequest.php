<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StakeHolderInteractionRequest extends FormRequest
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
            'userable_id' => 'required',
//            'userable_type' => 'required',
            'user_type_id' => 'required|exists:erp_stakeholder_user_types,id',
            'interaction_type_id' => 'required|exists:erp_interaction_types,id',
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'followup_actions' => 'nullable|string|max:1000',
            'db_name' => 'sometimes|string|max:255',
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
//            'user_id.required' => 'The stakeholder is required.',
//            'user_id.exists' => 'The selected stakeholder does not exist.',
            'user_type_id.required' => 'The user type is required.',
            'user_type_id.exists' => 'The selected user type does not exist.',
            'interaction_type_id.required' => 'The interaction type is required.',
            'interaction_type_id.exists' => 'The selected interaction type does not exist.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date.',
            'db_name.required' => 'The database name is required.',
            'book_code.string' => 'The book code must be a valid string.',
            'doc_number_type.string' => 'The document number type must be a valid string.',
            'doc_reset_pattern.string' => 'The document reset pattern must be a valid string.',
            'doc_prefix.string' => 'The document prefix must be a valid string.',
            'doc_suffix.string' => 'The document suffix must be a valid string.',
            'doc_no.string' => 'The document number must be a valid string.',
        ];
    }
}
