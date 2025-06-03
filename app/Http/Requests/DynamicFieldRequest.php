<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;

class DynamicFieldRequest extends FormRequest
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

    public function rules()
    {
        $dynamicFieldId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_dynamic_fields')
                    ->ignore($dynamicFieldId)
                    ->where('group_id', $this->group_id)
                    ->whereNull('deleted_at'),
            ],
            'description' => [
                'nullable',
                'string',
            ],

            'data_type' => [
                'nullable',
                'string',
            ],
            
            'status' => [
                'required',
                Rule::in(ConstantHelper::STATUS),
            ],
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'field_details' => [
                'nullable',
                'array',
            ],
            'field_details.*.id' => 'nullable|exists:erp_dynamic_field_details,id',
            'field_details.*.name' => 'nullable|string|max:255',
            'field_details.*.value' => 'nullable|string|max:255',
            'field_details.*.description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not exceed 255 characters.',
            'name.unique' => 'The name has already been taken.',
            'description.string' => 'The description must be a string.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'group_id.exists' => 'The selected group is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'field_details.array' => 'The field details must be an array.',
            'field_details.*.name.string' => 'Each field detail name must be a string.',
            'field_details.*.name.max' => 'Each field detail name may not exceed 255 characters.',
            'field_details.*.description.string' => 'Each field detail description must be a string.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $details = collect($this->input('field_details'));
            foreach ($details as $index => $item) {
                if (!isset($item['name']) || trim($item['name']) === '') {
                    $validator->errors()->add("field_details.$index.name", "The name field is required.");
                }
            }

            $names = $details->pluck('name')->filter()
                ->map(fn($v) => strtolower(trim($v)));

            $duplicates = $names->duplicates();

            if ($duplicates->isNotEmpty()) {
                foreach ($names as $index => $value) {
                    if ($duplicates->contains($value)) {
                        $validator->errors()->add("field_details.$index.name", "The name '$value' is duplicated.");
                    }
                }
            }
        });
    }
}
