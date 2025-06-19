<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FreightChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'freight_charges' => ['required', 'array', 'min:1'],
            'freight_charges.*.source_state_id' => ['required', 'exists:mysql_master.states,id'],
            'freight_charges.*.source_city_id' => ['required', 'exists:mysql_master.cities,id'],
            'freight_charges.*.destination_state_id' => ['required', 'exists:mysql_master.states,id'],
            'freight_charges.*.destination_city_id' => ['required', 'exists:mysql_master.cities,id'],
            'freight_charges.*.distance' => ['required', 'numeric', 'min:0'],
            'freight_charges.*.vehicle_type_id' => ['required', 'exists:erp_vehicle_types,id'],
            'freight_charges.*.amount' => ['required', 'numeric', 'min:0'],
            'freight_charges.*.customer_id' => ['nullable', 'exists:erp_customers,id'],
            
        ];
    }

    public function messages(): array
    {
        return [
            'freight_charges.required' => 'At least one freight charge entry is required.',
            'freight_charges.*.source_state_id.required' => 'The source state is required.',
            'freight_charges.*.source_state_id.exists' => 'The selected source state is invalid.',
            'freight_charges.*.source_city_id.required' => 'The source city is required.',
            'freight_charges.*.source_city_id.exists' => 'The selected source city is invalid.',
            'freight_charges.*.destination_state_id.required' => 'The destination state is required.',
            'freight_charges.*.destination_state_id.exists' => 'The selected destination state is invalid.',
            'freight_charges.*.destination_city_id.required' => 'The destination city is required.',
            'freight_charges.*.destination_city_id.exists' => 'The selected destination city is invalid.',
            'freight_charges.*.distance.required' => 'The distance is required.',
            'freight_charges.*.distance.numeric' => 'Distance must be a number.',
            'freight_charges.*.vehicle_type_id.required' => 'The vehicle type is required.',
            'freight_charges.*.vehicle_type_id.exists' => 'The selected vehicle type is invalid.',
            'freight_charges.*.amount.required' => 'The amount is required.',
            'freight_charges.*.amount.numeric' => 'The amount must be a valid number.',
            'freight_charges.*.customer_id.exists' => 'The selected customer is invalid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            $rows = $this->input('freight_charges', []);

            foreach ($rows as $index => $row) {
               if ($row['source_city_id'] == $row['destination_city_id']) {
                    $validator->errors()->add("freight_charges.$index.destination_city_id", 'Source and destination cities must be different.');
                }

            }
        });
    }
}
