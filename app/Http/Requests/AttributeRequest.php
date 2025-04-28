<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class AttributeRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    protected $organization_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }

    public function rules(): array
    {
        $attributeId = $this->route('id');
    
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_attribute_groups', 'name')
                     ->where('group_id', $this->group_id) 
                    ->ignore($attributeId) 
                    ->whereNull('deleted_at'), 
            ],
            'short_name' => [
                'required',
                'string',
                'max:100'
            ],
            'subattribute.*.value' => 'nullable|string|max:100',
            'subattribute.*.id' => 'nullable|exists:erp_attributes,id',
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
            'short_name.required' => 'Attribute short name is required.',
            'short_name.string' => 'Attribute short name must be a string.',
            'short_name.max' => 'Attribute short name may not exceed 100 characters.',
            'status.string' => 'The status must be a string.',
            'subattribute.*.value.required' => 'The attribute value is required.',
            'subattribute.*.value.string' => 'The attribute value must be a string.',
            'subattribute.*.value.max' => 'The attribute value may not be greater than 100 characters.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
                $subattributes = collect($this->input('subattribute'));
                $values = collect($this->input('subattribute'))
                    ->pluck('value')
                    ->filter()
                    ->map(fn($v) => strtolower(trim($v)));

                $duplicates = $values->duplicates();

                if ($duplicates->isNotEmpty()) {
                    foreach ($duplicates as $index => $value) {
                        $validator->errors()->add("subattribute.$index.value", "The value '$value' is duplicated in this request.");
                    }
                }
                foreach ($subattributes as $index => $item) {
                    if (!isset($item['value']) || trim($item['value']) === '') {
                        $validator->errors()->add("subattribute.$index.value", "The value field is required.");
                    }
                }
        });
    }
    
}
