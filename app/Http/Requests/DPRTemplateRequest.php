<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DPRTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected $organization_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
    }

    public function rules(): array
    {
        $attributeId = $this->route('id');
    
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_dpr_template_masters', 'template_name')
                    ->where('organization_id', $this->organization_id)
                    ->ignore($attributeId) 
                    ->whereNull('deleted_at'), 
            ],
            'organization_id' => 'nullable|exists:organizations,id',
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'nullable'
        ];
    }
    

    public function messages(): array
    {
        return [
            'name.required' => 'The attribute name is required.',
            'name.string' => 'The attribute name must be a string.',
            'name.max' => 'The attribute name may not exceed 100 characters.',
            'status.string' => 'The status must be a string.',
            'subattribute.*.value.required' => 'The attribute value is required.',
            'subattribute.*.value.string' => 'The attribute value must be a string.',
            'subattribute.*.value.max' => 'The attribute value may not be greater than 100 characters.',
        ];
    }
}
