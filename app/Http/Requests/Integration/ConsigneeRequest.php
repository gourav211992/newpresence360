<?php

namespace App\Http\Requests\Integration;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ConsigneeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organizationId = $this->input('organization_id');

        return [
            'organization_id' => 'required|integer|exists:organizations,id',
            'consignees'       => 'required|array|min:1',

            // Customer fields validation
            'consignees.*.consignee_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_consignees', 'consignee_code')
                    ->whereNull('deleted_at')
                    ->where(fn ($q) => $q->where('organization_id', $organizationId))
            ],
            'consignees.*.consignee_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_consignees', 'consignee_name')
                    ->where(fn ($q) => $q->where('organization_id', $organizationId))
                    ->whereNull('deleted_at')
            ],
            'consignees.*.country_id'    => 'required|integer|exists:mysql_master.countries,id',
            'consignees.*.state_id'      => 'required|integer|exists:mysql_master.states,id',
            'consignees.*.city_id'       => 'required|integer|exists:mysql_master.cities,id',
            'consignees.*.address'       => 'nullable|string|max:500',
            'consignees.*.pincode'       => 'nullable|string|max:6',
            'consignees.*.email'         => 'nullable|email|max:150',
            'consignees.*.phone'         => 'nullable|string|max:20',
            'consignees.*.mobile'        => 'required|string|max:20',
        ];
    }

    /**
     * Friendly attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'organization_id'              => 'organization',
            'consignees.*.consignee_code'  => 'consignee code',
            'consignees.*.consignee_name'  => 'consignee name',
            'consignees.*.country_id'      => 'country',
            'consignees.*.state_id'        => 'state',
            'consignees.*.city_id'         => 'city',
            'consignees.*.address'         => 'address',
            'consignees.*.pincode'         => 'pincode',
            'consignees.*.email'           => 'email',
            'consignees.*.phone'           => 'phone',
            'consignees.*.mobile'          => 'mobile',
        ];
    }
}
