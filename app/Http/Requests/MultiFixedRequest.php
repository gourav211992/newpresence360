<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
class MultiFixedRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true; // Make sure it's not false
    }

   
    public function rules(): array
    {
        return [
            'source_route_id' => ['required', 'exists:erp_logistics_route_masters,id'],
            'destination_route_id'   => 'required|integer|exists:erp_logistics_route_masters,id',
            'vehicle_type_id'        => 'required|array|min:1',
            'vehicle_type_id.*'      => 'required|integer|exists:erp_vehicle_types,id',
            'customer_id'            => 'nullable|integer|exists:erp_customers,id',
            
            'multi_fixed_pricing'                          => 'required|array|min:1',
            'multi_fixed_pricing.*.location_route_id'      => 'required|integer|exists:erp_logistics_route_masters,id',
            'multi_fixed_pricing.*.amount'                 => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'source_route_id.required' => 'The source Location is required.',
            'source_route_id.exists' => 'The selected source location is invalid.',
            'destination_route_id.required'   => 'Destination location is required.',
            'destination_route_id.exists'     => 'Selected destination location is invalid.',
            'vehicle_type_id.required'        => 'At least one vehicle type is required.',
            'vehicle_type_id.array'           => 'Vehicle type must be an array.',
            'vehicle_type_id.*.required'      => 'Each vehicle type is required.',
            'vehicle_type_id.*.exists'        => 'One or more vehicle types are invalid.',
            'customer_id.exists'              => 'Selected customer is invalid.',

            'multi_fixed_pricing.required'                       => 'At least one location pricing is required.',
            'multi_fixed_pricing.array'                          => 'Invalid format for location pricing.',
            'multi_fixed_pricing.*.location_route_id.required' => 'Please select a location for each row.',
            'multi_fixed_pricing.*.location_route_id.exists'   => 'One or more selected locations are invalid.',
            'multi_fixed_pricing.*.amount.required'             => 'Amount is required for each location.',
            'multi_fixed_pricing.*.amount.numeric'              => 'Amount must be a number.',
          
        ];
    }

     public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (
                $this->input('source_route_id') &&
                $this->input('destination_route_id') &&
                $this->input('source_route_id') == $this->input('destination_route_id')
            ) {
                $validator->errors()->add('destination_route_id', 'Source and destination location cannot be the same.');
            }
        });
    }
}
