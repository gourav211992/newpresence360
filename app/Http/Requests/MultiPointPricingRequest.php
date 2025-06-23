<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MultiPointPricingRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'multi_point' => ['required', 'array', 'min:1'],
            'multi_point.*.source_state_id' => ['required', 'exists:mysql_master.states,id'],
            'multi_point.*.source_city_id' => ['required', 'exists:mysql_master.cities,id'],
            'multi_point.*.free_point' => ['required', 'numeric', 'min:0'],
            'multi_point.*.amount' => ['required', 'numeric', 'min:0'],
            'multi_point.*.customer_id' => ['nullable', 'exists:erp_customers,id'],
            
        ];
    }

    public function messages(): array
    {
        return [
            'multi_point.required' => 'At least one multi-point pricing entry is required.',
            'multi_point.*.source_state_id.required' => 'The source state is required.',
            'multi_point.*.source_state_id.exists' => 'The selected source state is invalid.',
            'multi_point.*.source_city_id.required' => 'The source city is required.',
            'multi_point.*.source_city_id.exists' => 'The selected source city is invalid.',
            'multi_point.*.free_point.required' => 'The free point is required.',
            'multi_point.*.free_point.numeric' => 'Free point must be a number.',
            'multi_point.*.free_point.min' => 'Free point must be at least 0.',
            'multi_point.*.amount.required' => 'The rate amount is required.',
            'multi_point.*.amount.numeric' => 'The rate amount must be a valid number.',
            'multi_point.*.amount.min' => 'The rate amount must be at least 0.',
            'multi_point.*.customer_id.exists' => 'The selected customer is invalid.',
        ];
    }

}
