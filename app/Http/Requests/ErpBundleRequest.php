<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpBundleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $itemId = $this->route('id'); 

        $uniqueScope = function ($query) {
            if ($this->group_id !== null) {
                $query->where('group_id', $this->group_id);
            }

            if ($this->company_id !== null) {
                $query->where(function ($q) {
                    $q->where('company_id', $this->company_id)
                      ->orWhereNull('company_id'); 
                });
            }

            if ($this->organization_id !== null) {
                $query->where(function ($q) {
                    $q->where('organization_id', $this->organization_id)
                      ->orWhereNull('organization_id'); 
                });
            }
        };

        return [
            'sku_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_item_bundles', 'sku_code')
                    ->where($uniqueScope)
                    ->ignore($itemId),
            ],
            'sku_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_item_bundles', 'sku_name')
                    ->where($uniqueScope)
                    ->ignore($itemId),
            ],
            'sku_initial' => 'required|string|max:3',
            'front_sku_code' => 'required|string|max:255',
            'code_type' => 'required|string|max:255',
            'book_id' => 'required|integer|exists:erp_books,id',
            'category_id' => 'required|integer|exists:erp_categories,id',
            'group_id' => 'nullable|integer|exists:erp_groups,id',
            'company_id' => 'nullable|integer|exists:erp_companies,id',
            'organization_id' => 'nullable|integer|exists:erp_organizations,id',
            'status' => 'required|string|max:255',
            'document_status' => 'required|string|max:255',
            'doc_no' => 'required|string|max:255',
            'approver_level' => 'required|integer',
            'revision_number' => 'required|string|max:255',
            'revision_date' => 'required|date',
            'upload_document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',  
            'final_remarks' => 'nullable|string',

            'created_by' => 'required|integer|exists:users,id',
            'updated_by' => 'nullable|integer|exists:users,id',
            'deleted_by' => 'nullable|integer|exists:users,id',

            'bundle_item_details.*.item_id' => 'nullable|integer|exists:erp_items,id',
            'bundle_item_details.*.item_name' => 'required|string|max:255',
            'bundle_item_details.*.item_code' => 'required|string|max:255',
            'bundle_item_details.*.uom_id' => 'required|integer|exists:erp_units,id',
            'bundle_item_details.*.qty' => 'required|numeric|min:0',
            'bundle_item_details.*.hsn_id' => 'nullable',
            'bundle_item_details.*.attributes' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            // Unique validations
            'sku_code.required' => 'SKU code is required.',
            'sku_code.unique' => 'SKU code must be unique within the selected group, company, and organization.',
            'sku_name.required' => 'SKU name is required.',
            'sku_name.unique' => 'SKU name must be unique within the selected group, company, and organization.',

            // General field validations
            'sku_code.string' => 'SKU code must be a valid string.',
            'sku_code.max' => 'SKU code cannot exceed 255 characters.',
            'sku_name.string' => 'SKU name must be a valid string.',
            'sku_name.max' => 'SKU name cannot exceed 255 characters.',
            'sku_initial.required' => 'SKU initial is required.',
            'sku_initial.string' => 'SKU initial must be a valid string.',
            'sku_initial.max' => 'SKU initial cannot exceed 3 characters.',
            'front_sku_code.required' => 'Front SKU code is required.',
            'front_sku_code.string' => 'Front SKU code must be a valid string.',
            'front_sku_code.max' => 'Front SKU code cannot exceed 255 characters.',
            'code_type.required' => 'Code type is required.',
            'book_id.required' => 'Book ID is required.',
            'book_id.exists' => 'Selected book does not exist.',
            'category_id.required' => 'Group is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'group_id.exists' => 'Selected group does not exist.',
            'company_id.exists' => 'Selected company does not exist.',
            'organization_id.exists' => 'Selected organization does not exist.',
            'status.required' => 'Status is required.',
            'document_status.required' => 'Document status is required.',
            'doc_no.required' => 'Document number is required.',
            'approver_level.required' => 'Approver level is required.',
            'revision_number.required' => 'Revision number is required.',
            'revision_date.required' => 'Revision date is required.',

            // Bundle details
            'bundle_item_details.*.item_name.required' => 'Item name is required.',
            'bundle_item_details.*.item_code.required' => 'Item code is required.',
            'bundle_item_details.*.uom_id.required' => 'UOM is required.',
            'bundle_item_details.*.qty.required' => 'Quantity is required.',
        ];
    }
}
