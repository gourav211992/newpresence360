<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ErpRgrStoreMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'store_mappings' => 'required|array|min:1',
            'store_mappings.*.id'              => 'nullable|integer|exists:erp_rgr_store_mappings,id',
            'store_mappings.*.category_id'     => 'required|integer|exists:erp_categories,id',
            'store_mappings.*.store_id'        => 'required|integer|exists:erp_stores,id',
            'store_mappings.*.sub_store_id'    => 'required|integer|exists:erp_sub_stores,id',
            'store_mappings.*.qc_sub_store_id' => 'nullable|integer|exists:erp_sub_stores,id',

            'store_mappings.*.organization_id' => 'nullable|integer|exists:organizations,id',
            'store_mappings.*.group_id'        => 'nullable|integer|exists:organization_groups,id',
            'store_mappings.*.company_id'      => 'nullable|integer|exists:organization_companies,id',

            // Damage Mappings
            'damage_mappings' => 'nullable|array',
            'damage_mappings.*.id'           => 'nullable|integer|exists:erp_rgr_damage_mappings,id',
            'damage_mappings.*.damage_type'  => 'required|string|max:255',
            'damage_mappings.*.store_id'     => 'nullable|integer|exists:erp_stores,id',
            'damage_mappings.*.sub_store_id' => 'nullable|integer|exists:erp_sub_stores,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $combinations = [];
            if ($this->has('store_mappings')) {
                foreach ($this->store_mappings as $index => $mapping) {
                    $key = $mapping['category_id'] . '-' . $mapping['store_id'];
                    if (in_array($key, $combinations)) {
                        $validator->errors()->add(
                            "store_mappings.$index.store_id",
                            "The combination of category and location must be unique."
                        );
                    } else {
                        $combinations[] = $key;
                    }
                }
            }
        });
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            // Category
            'store_mappings.*.category_id.required' => 'Category is required.',
            'store_mappings.*.category_id.integer'  => 'Category ID must be a valid number.',
            'store_mappings.*.category_id.exists'   => 'The selected category does not exist.',

            // Store
            'store_mappings.*.store_id.required' => 'Location is required.',
            'store_mappings.*.store_id.integer'  => 'Location ID must be a valid number.',
            'store_mappings.*.store_id.exists'   => 'The selected store does not exist.',

            // Sub Store
            'store_mappings.*.sub_store_id.required' => 'Rgr-store is required.',
            'store_mappings.*.sub_store_id.integer'  => 'Rgr-store ID must be a valid number.',
            'store_mappings.*.sub_store_id.exists'   => 'The selected Rgr-store does not exist.',

            // QC Sub Store
            'store_mappings.*.qc_sub_store_id.integer' => 'QC-store ID must be a valid number.',
            'store_mappings.*.qc_sub_store_id.exists'  => 'The selected QC-store does not exist.',

              // Damage Mappings
            'damage_mappings.*.damage_type.required'  => 'Damage Type is required.',
            'damage_mappings.*.damage_type.string'    => 'Damage Type must be a string.',
            'damage_mappings.*.damage_type.max'       => 'Damage Type may not be greater than 255 characters.',
            'damage_mappings.*.store_id.required'     => 'Location is required.',
            'damage_mappings.*.store_id.integer'      => 'Location ID must be a valid number.',
            'damage_mappings.*.store_id.exists'       => 'Selected Damage Location does not exist.',
            'damage_mappings.*.sub_store_id.required' => 'Store is required.',
            'damage_mappings.*.sub_store_id.integer'  => 'Store ID must be a valid number.',
            'damage_mappings.*.sub_store_id.exists'   => 'Selected Store does not exist.',
        ];
    }
}
