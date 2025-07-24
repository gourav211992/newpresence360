<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpEquipmentRequest extends FormRequest
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
    public function rules()
    {
        return [
            'organization_id' => 'required|integer',
            'location_id' => 'required|integer',
            'category_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'upload_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'final_remarks' => 'nullable|string',
            'status' => 'required|in:draft,submitted',
            'doc_number_type' => 'nullable|string',
            'doc_prefix' => 'nullable|string',
            'doc_suffix' => 'nullable|string',
            'doc_no' => 'nullable|string',
            'book_id' => 'nullable|string',
            'document_number' => 'nullable|string',

            // Maintenance details validation
            'maintenance' => 'nullable|array',
            'maintenance.*.type' => 'required_with:maintenance|integer|exists:erp_maintenance_types,id',
            'maintenance.*.frequency' => 'required_with:maintenance|string',
            'maintenance.*.time' => 'nullable|string',
            'maintenance.*.checklists' => 'nullable|array',

            // Spare parts validation
            'spareparts' => 'nullable|array',
            // 'spareparts.*.item_code'  => 'required_with:spareparts|string',
            // 'spareparts.*.item_name'  => 'required_with:spareparts|string',
            // 'spareparts.*.attributes' => 'nullable|array',
            // 'spareparts.*.uom'        => 'required_with:spareparts|string',
            // 'spareparts.*.qty'        => 'required_with:spareparts|numeric|min:0',
        ];
    }
}
