<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Auth;

class ProductSpecificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected $organization_id;
    protected $company_id;
    protected $group_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
        $this->company_id = $organization ? $organization->company_id : null;
    }

    public function rules()
    {
        $productSpecificationId =$this->route('id');

        $uniqueRule = Rule::unique('erp_product_specifications', 'name')
            ->ignore($productSpecificationId)
            ->whereNull('deleted_at');

        if ($this->group_id !== null) {
            $uniqueRule->where('group_id', $this->group_id);
        }

        if ($this->company_id !== null) {
            $companyId = $this->company_id;
            $uniqueRule->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                      ->orWhereNull('company_id');
            });
        }

        if ($this->organization_id !== null) {
            $orgId = $this->organization_id;
            $uniqueRule->where(function ($query) use ($orgId) {
                $query->where('organization_id', $orgId)
                      ->orWhereNull('organization_id');
            });
        }
        return [
           'name' => [
                'required',
                'string',
                'max:100',
                $uniqueRule,
            ],

            'alias' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
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
            'specification_details' => [
                'nullable',
                'array',
            ],
            'specification_details.*.id' => 'nullable|exists:erp_product_specification_details,id',
            'specification_details.*.name' => 'nullable|string|max:100',
            'specification_details.*.description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The product specification name is required.',
            'name.string' => 'The product specification name must be a string.',
            'name.max' => 'The product specification name may not exceed 100 characters.',
            'name.unique' => 'The product specification name has already been taken.',
            'alias.string' => 'The alias must be a string.',
            'alias.max' => 'The alias may not exceed 50 characters.',
            'description.string' => 'The description must be a string.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'group_id.exists' => 'The selected group is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'specification_details.array' => 'The specification details must be an array.',
            'specification_details.*.name.string' => 'Each specification name must be a string.',
            'specification_details.*.name.max' => 'Each specification name may not exceed 100 characters.',
            'specification_details.*.description.string' => 'Each specification description must be a string.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $details = collect($this->input('specification_details'));
            foreach ($details as $index => $item) {
                if (!isset($item['name']) || trim($item['name']) === '') {
                    $validator->errors()->add("specification_details.$index.name", "The name field is required.");
                }
            }

            $names = $details ->pluck('name') ->filter()
            ->map(fn($v) => strtolower(trim($v)));

            $duplicates = $names->duplicates();

            if ($duplicates->isNotEmpty()) {
                foreach ($names as $index => $value) {
                    if ($duplicates->contains($value)) {
                        $validator->errors()->add("specification_details.$index.name", "The name '$value' is duplicated.");
                    }
                }
            }
        });
    }
}
