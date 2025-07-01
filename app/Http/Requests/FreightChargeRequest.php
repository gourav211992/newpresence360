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
            'freight_charges.*.source_route_id' => ['required', 'exists:erp_logistics_route_masters,id'],
            'freight_charges.*.destination_route_id' => ['required', 'exists:erp_logistics_route_masters,id'],
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
            'freight_charges.*.source_route_id.required' => 'The source Location is required.',
            'freight_charges.*.source_route_id.exists' => 'The selected source location is invalid.',
            'freight_charges.*.destination_route_id.required' => 'The destination location is required.',
            'freight_charges.*.destination_route_id.exists' => 'The selected destination location is invalid.',
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
                $source = $row['source_route_id'] ?? null;
                $destination = $row['destination_route_id'] ?? null;

                if ($source && $destination && $source == $destination) {
                    $validator->errors()->add("freight_charges.$index.destination_route_id", 'Source and destination location must be different.');
                }
            }
        });
    }

}
