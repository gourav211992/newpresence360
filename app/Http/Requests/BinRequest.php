<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BinRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'group_id' => 'nullable|integer|exists:groups,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'store_id' => 'nullable|integer|exists:erp_stores,id',
            'rack_id' => 'nullable|integer|exists:erp_racks,id',
            'shelf_id' => 'nullable|integer|exists:erp_shelfs,id',
            'bin_code' => 'nullable|string|max:151',
            'bin_name' => 'nullable|string|max:191',
            'status' => 'nullable|string|max:99',
        ];
    }

    public function messages()
    {
        return [
            'organization_id.integer' => 'The organization ID must be an integer.',
            'organization_id.exists' => 'The selected organization ID is invalid.',
            'group_id.integer' => 'The group ID must be an integer.',
            'group_id.exists' => 'The selected group ID is invalid.',
            'company_id.integer' => 'The company ID must be an integer.',
            'company_id.exists' => 'The selected company ID is invalid.',
            'store_id.integer' => 'The store ID must be an integer.',
            'store_id.exists' => 'The selected store ID is invalid.',
            'rack_id.integer' => 'The rack ID must be an integer.',
            'rack_id.exists' => 'The selected rack ID is invalid.',
            'shelf_id.integer' => 'The shelf ID must be an integer.',
            'shelf_id.exists' => 'The selected shelf ID is invalid.',
            'bin_code.string' => 'The bin code must be a string.',
            'bin_code.max' => 'The bin code may not be greater than 151 characters.',
            'bin_name.string' => 'The bin name must be a string.',
            'bin_name.max' => 'The bin name may not be greater than 191 characters.',
            'status.string' => 'The status must be a string.',
            'status.max' => 'The status may not be greater than 99 characters.',
        ];
    }
}
