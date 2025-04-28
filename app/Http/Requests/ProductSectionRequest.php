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

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }


    public function rules()
    {
        $productSectionId = $this->route('id');

        return [
           'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_product_sections')
                    ->ignore($productSectionId)
                    ->where('group_id', $this->group_id) 
                    ->whereNull('deleted_at'),
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
