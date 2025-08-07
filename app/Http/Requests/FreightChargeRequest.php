<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\DB;

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
        $seen = [];

        foreach ($rows as $index => $row) {
            $id = $row['id'] ?? null;
            $source = $row['source_route_id'] ?? null;
            $destination = $row['destination_route_id'] ?? null;
            $vehicleType = $row['vehicle_type_id'] ?? null;
            $customerId = $row['customer_id'] ?? null;

            // 1. Source and destination must be different
            if ($source && $destination && $source == $destination) {
                $validator->errors()->add("freight_charges.$index.destination_route_id", 'Source and destination must be different.');
            }

            // 2. Skip if any required field is missing
            if (!$source || !$destination || !$vehicleType) {
                continue;
            }

            // 3. Skip duplicate key check in form itself (as per your instruction)

            // 4. Check for duplicate in DB (excluding current record if updating)
            $query = DB::table('erp_freight_charges')
                ->where('source_route_id', $source)
                ->where('destination_route_id', $destination)
                ->where('vehicle_type_id', $vehicleType);

            if (is_null($customerId)) {
                $query->whereNull('customer_id');
            } else {
                $query->where('customer_id', $customerId);
            }

            if ($id) {
                $query->where('id', '!=', $id);
            }

            if ($query->exists()) {
                $validator->errors()->add("freight_charges.$index.customer_id", 'Duplicate freight charge entry.');
            }
        }
    });
}






}
