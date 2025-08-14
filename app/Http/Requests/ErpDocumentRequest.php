<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpDocumentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('erp_documents')->where(function ($query) {
                    return $query->where('service', $this->input('service'));
                })->ignore($unitId),
            ],

            'service' => [
                'required',
                'string',
                'max:100',
            ],
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable',
            'organization_id' => 'nullable|exists:organizations,id',
            'status' => 'nullable',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 50 characters.',
            'service.required' => 'The service field is required.',
            'service.string' => 'The service must be a string.',
            'service.max' => 'The service may not be greater than 100 characters.',
            'group_id.exists' => 'The selected group does not exist.',
            'organization_id.exists' => 'The selected organization does not exist.',
            'status.required' => 'The status field is required.',
        ];
    }
}
