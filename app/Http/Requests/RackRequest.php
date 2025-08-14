<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RackRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Update as per your authorization logic
    }

    public function rules()
    {
        return [
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'group_id' => 'nullable|integer|exists:groups,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'store_id' => 'nullable|integer|exists:erp_stores,id',
            'rack_code' => 'nullable|string|max:151',
            'rack_name' => 'nullable|string|max:191',
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
            'rack_code.string' => 'The rack code must be a string.',
            'rack_code.max' => 'The rack code may not be greater than 151 characters.',
            'rack_name.string' => 'The rack name must be a string.',
            'rack_name.max' => 'The rack name may not be greater than 191 characters.',
            'status.string' => 'The status must be a string.',
            'status.max' => 'The status may not be greater than 99 characters.',
        ];
    }
}
