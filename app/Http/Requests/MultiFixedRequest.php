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
            'source_state_id'        => 'required|integer|exists:mysql_master.states,id',
            'source_city_id'         => 'required|integer|exists:mysql_master.cities,id',
            'destination_state_id'   => 'required|integer|exists:mysql_master.states,id',
            'destination_city_id'    => 'required|integer|exists:mysql_master.cities,id',
            'vehicle_type_id'        => 'required|array|min:1',
            'vehicle_type_id.*'      => 'required|integer|exists:erp_vehicle_types,id',
            'customer_id'            => 'nullable|integer|exists:erp_customers,id',
            
            'multi_fixed_pricing'                          => 'required|array|min:1',
            'multi_fixed_pricing.*.location_state_id'      => 'required|integer|exists:mysql_master.states,id',
            'multi_fixed_pricing.*.location_city_id'       => 'required|integer|exists:mysql_master.cities,id',
            'multi_fixed_pricing.*.amount'                 => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'source_state_id.required'        => 'Source state is required.',
            'source_state_id.exists'          => 'Selected source state is invalid.',
            'source_city_id.required'         => 'Source city is required.',
            'source_city_id.exists'           => 'Selected source city is invalid.',
            'destination_state_id.required'   => 'Destination state is required.',
            'destination_state_id.exists'     => 'Selected destination state is invalid.',
            'destination_city_id.required'    => 'Destination city is required.',
            'destination_city_id.exists'      => 'Selected destination city is invalid.',
            'vehicle_type_id.required'        => 'At least one vehicle type is required.',
            'vehicle_type_id.array'           => 'Vehicle type must be an array.',
            'vehicle_type_id.*.required'      => 'Each vehicle type is required.',
            'vehicle_type_id.*.exists'        => 'One or more vehicle types are invalid.',
            'customer_id.exists'              => 'Selected customer is invalid.',

            'multi_fixed_pricing.required'                       => 'At least one location pricing is required.',
            'multi_fixed_pricing.array'                          => 'Invalid format for location pricing.',
            'multi_fixed_pricing.*.location_state_id.required'  => 'State is required for each location.',
            'multi_fixed_pricing.*.location_state_id.exists'    => 'Invalid state selected in locations.',
            'multi_fixed_pricing.*.location_city_id.required'   => 'City is required for each location.',
            'multi_fixed_pricing.*.location_city_id.exists'     => 'Invalid city selected in locations.',
            'multi_fixed_pricing.*.amount.required'             => 'Amount is required for each location.',
            'multi_fixed_pricing.*.amount.numeric'              => 'Amount must be a number.',
          
        ];
    }

     public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (
                $this->input('source_city_id') &&
                $this->input('destination_city_id') &&
                $this->input('source_city_id') == $this->input('destination_city_id')
            ) {
                $validator->errors()->add('destination_city_id', 'Source and destination city cannot be the same.');
            }
        });
    }
}
