<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintWoRequest extends FormRequest
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

        $rules = [
            'book_id' => 'required',
            'book_code' => 'required|string|max:255',
            'document_number' => 'required|string|max:255',
            'document_date' => 'nullable|date',
            'doc_number_type' => 'nullable|string|max:255',
            'doc_reset_pattern' => 'nullable|string|max:255',
            'doc_prefix' => 'nullable|string|max:255',
            'doc_suffix' => 'nullable|string|max:255',
            'doc_no' => 'nullable|integer',
            'location_id' => 'nullable',
            'equipment_details' => 'nullable|json',
            'spare_parts' => 'nullable|json',
            'checklist_data' => 'nullable|json',
            'document_status' => 'nullable|string|in:Draft,Submitted,Approved,Rejected,Completed',
            'upload_file' => 'nullable|string|max:255',
            'final_remark' => 'nullable|string',
            'supporting_documents' => 'nullable|json',
            'completion_date' => 'nullable|date',
            'work_description' => 'nullable|string',
            'work_performed' => 'nullable|string',
        ];

        if ($isEdit) {
            $rules['document_number'] = 'required|string|unique:erp_plant_maint_wo,document_number,' . $this->route('maint-wo');
        }

        return $rules;
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
        ];
    }
}
