<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class CategoryRequest extends FormRequest
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
        $categoryId = $this->route('id');

        return [
            'parent_id' => 'nullable|exists:erp_categories,id', 
            'hsn_id' => [
                function ($attribute, $value, $fail) {
                    if (request()->input('type') === 'Product' && empty($value)) {
                        return $fail('The hsn is required.');
                    }
                },
            ],
            'type' => 'required|string',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_categories')
                    ->where(function ($query) {
                        return $query->where('group_id', $this->group_id)
                                    ->whereNull('deleted_at')
                                    ->whereNull('parent_id');
                    })
                    ->ignore($categoryId),
            ],

            'cat_initials' => 'required|string|max:10', 
            'status' => 'required', 
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable|exists:organizations,id',
            'subcategories.*.id' => 'nullable|exists:erp_categories,id',
            'subcategories.*.name' => 'required|string|max:100', 
            'subcategories.*.sub_cat_initials' => 'required|string|max:10', 
        ];
    }

    public function messages()
    {
        return [
            'parent_id.exists' => 'The selected parent category is invalid.',
            'type.required' => 'The type field is required.',
            'type.string' => 'The type must be a valid string.',
            'hsn_id.required' => 'The HSN is required.',
            'hsn_id.exists' => 'The selected HSN is invalid.',
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a valid string.',
            'name.max' => 'The category name may not be greater than 100 characters.',
            'name.unique' => 'The category name has already been taken.',
            'cat_initials.required' => 'The category initials are required.',
            'cat_initials.string' => 'The category initials must be a valid string.',
            'cat_initials.max' => 'The category initials may not exceed 10 characters.',
            'status.required' => 'The status field is required.',
            'group_id.exists' => 'The selected group is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'subcategories.*.name.string' => 'Each sub-category name must be a valid string.',
            'subcategories.*.name.max' => 'Each sub-category name may not exceed 100 characters.',
            'subcategories.*.name.required' => 'The subcategory name is required.',
            'subcategories.*.sub_cat_initials.required' => 'The sub-category initials are required.',
            'subcategories.*.sub_cat_initials.string' => 'Each sub-category initials must be a valid string.',
            'subcategories.*.sub_cat_initials.max' => 'Each sub-category initials may not exceed 10 characters.',
        ];

        
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $subcategories = collect($this->input('subcategories'));

            $names = $subcategories
                ->pluck('name')
                ->filter()
                ->map(fn($v) => strtolower(trim($v)));

            $duplicates = $names->duplicates();

            if ($duplicates->isNotEmpty()) {
                foreach ($names as $index => $value) {
                    if ($duplicates->contains($value)) {
                        $validator->errors()->add("subcategories.$index.name", "The name '$value' is duplicated.");
                    }
                }
            }
        });
    }
}
