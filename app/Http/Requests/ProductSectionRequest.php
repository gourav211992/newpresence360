<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class ProductSectionRequest extends FormRequest
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
        $productSectionId = $this->route('id');
         $uniqueRule = Rule::unique('erp_product_sections', 'name')
            ->ignore($productSectionId)
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
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                'string', 
            ],
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'details' => 'nullable|array',
            'details.*.name' => 'nullable|string|max:255',
            'details.*.description' => 'nullable|string',
            'details.*.station_id' => 'nullable|exists:erp_stations,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The product section name is required.',
            'name.string' => 'The product section name must be a string.',
            'name.max' => 'The product section name may not exceed 100 characters.',
            'name.unique' => 'The product section name has already been taken.',
            'description.string' => 'The product section description must be a string.',
            'description.max' => 'The product section description may not exceed 255 characters.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'group_id.exists' => 'The selected group is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'details.array' => 'The details must be an array.',
            'details.*.name.string' => 'The detail name must be a string.',
            'details.*.name.max' => 'The detail name may not exceed 255 characters.',
            'details.*.description.string' => 'The detail description must be a string.',
            'details.*.station_id.exists' => 'The selected station ID is invalid.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $details = collect($this->input('details'));
            foreach ($details as $index => $item) {
                if (!isset($item['name']) || trim($item['name']) === '') {
                    $validator->errors()->add("details.$index.name", "The name field is required.");
                }
            }
            $names = $details
                ->pluck('name')
                ->filter()
                ->map(fn($v) => strtolower(trim($v)));

            $duplicates = $names->duplicates();

            if ($duplicates->isNotEmpty()) {
                foreach ($names as $index => $value) {
                    if ($duplicates->contains($value)) {
                        $validator->errors()->add("details.$index.name", "The name '$value' is duplicated.");
                    }
                }
            }
        });
    }
}
