<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpMaintenanceRequest extends FormRequest
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
            'book_id'       => 'nullable|string',
            'category'      => 'required|integer|exists:erp_categories,id',
            'equipment'     => 'required|integer|exists:erp_equipment,id',
            'document_number'        => 'required|string|max:255',
            'doc_date'      => 'required|date',

            'checklist_answers' => 'nullable|array',
            'checklist_answers.*' => 'array',

            'defects' => 'nullable|array',
            'defects.*.' => 'array',
        ];
    }
}
