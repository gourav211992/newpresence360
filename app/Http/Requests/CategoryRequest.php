<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;
use App\Models\Category; 
use Illuminate\Support\Facades\DB; 

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected $organization_id;
    protected $group_id;
    protected $company_id;

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
              
            ],
            'type' => 'required|string',
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'cat_initials' => 'required|string|max:100', 
            'inspection_checklist_id' => 'nullable|exists:erp_inspection_checklists,id', 
            'status' => 'required', 
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable|exists:organizations,id',

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

            $categoryId = $this->route('id'); 
            $categoryName = $this->input('name');
            $parent_id = $this->input('parent_id');
            $category = null; 
            if ($categoryId) {
                $category = Category::find($categoryId); 
            }
            if ($category && $category->parent_id) {
                $parentCategory = Category::find($category->parent_id); 

                if ($parentCategory && $parentCategory->name === $categoryName) {
                    return; 
                }
            }

            $existing = Category::where('name', $categoryName)
                ->whereNull('deleted_at')
                 ->when($this->group_id !== null, function ($query) {
                    return $query->where('group_id', $this->group_id);
                })
                ->when($this->company_id !== null, function ($query) {
                    return $query->where(function($q) {
                        $q->where('company_id', $this->company_id)
                        ->orWhereNull('company_id');
                    });
                })
                ->when($this->organization_id !== null, function ($query) {
                    return $query->where(function($q) {
                        $q->where('organization_id', $this->organization_id)
                        ->orWhereNull('organization_id');
                    });
                })
                ->when($categoryId, function ($query) use ($categoryId) {
                    return $query->where('id', '!=', $categoryId);
                })
                ->exists(); 

            if ($existing) {
                $validator->errors()->add('name', "The category name has already been taken.");
            }

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
