<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintBOMRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isEdit = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'book_code' => $isEdit ? 'nullable|string' : 'required|string',
            'doc_number_type' => $isEdit ? 'nullable|string' : 'required|string',
            'doc_prefix' => 'nullable|string',
            'doc_suffix' => 'nullable|string',
            'doc_no' => $isEdit ? 'nullable|integer' : 'required|integer',
            'document_status' => 'required|string',
            'book_id' => $isEdit ? 'nullable|integer' : 'required|integer',
            'document_number' => $isEdit ? 'nullable|string' : 'required|string',
            'document_date' => $isEdit ? 'nullable|date' : 'required|date',
            'bom_name' => 'required|string',
        ];

    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'book_code' => 'Book Code',
            'doc_number_type' => 'Document Number Type',
            'doc_prefix' => 'Document Prefix',
            'doc_suffix' => 'Document Suffix',
            'doc_no' => 'Document Number',
            'document_status' => 'Document Status',
            'book_id' => 'Book ID',
            'document_number' => 'Document Number',
            'document_date' => 'Document Date',
            'bom_name' => 'Bom Name',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'book_code.required' => 'The Book Code field is required.',
            'book_code.string' => 'The Book Code must be a valid string.',
            'doc_number_type.required' => 'The Document Number Type field is required.',
            'doc_number_type.string' => 'The Document Number Type must be a valid string.',
            'doc_prefix.string' => 'The Document Prefix must be a valid string.',
            'doc_suffix.string' => 'The Document Suffix must be a valid string.',
            'doc_no.required' => 'The Document Number field is required.',
            'doc_no.integer' => 'The Document Number must be an integer.',
            'document_status.required' => 'The Document Status field is required.',
            'document_status.string' => 'The Document Status must be a valid string.',
            'book_id.required' => 'The Book ID field is required.',
            'book_id.integer' => 'The Book ID must be an integer.',
            'document_number.required' => 'The Document Number field is required.',
            'document_number.string' => 'The Document Number must be a valid string.',
            'document_date.required' => 'The Document Date field is required.',
            'document_date.date' => 'The Document Date must be a valid date.',
            'bom_name.string' => 'The Asset Name must be a valid string.',
            'bom_name.unique' => 'The Asset Name has already been taken.',
        ];
    }
}
