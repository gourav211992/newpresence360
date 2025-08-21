<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'multi_point.*.source_route_id' => ['required', 'exists:erp_logistics_route_masters,id'],
            'multi_point.*.free_point' => ['required', 'numeric', 'min:0'],
            'multi_point.*.amount' => ['required', 'numeric', 'min:0'],
            'multi_point.*.customer_id' => ['nullable', 'exists:erp_customers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'multi_point.required' => 'At least one freight charge entry is required.',
            'multi_point.*.source_route_id.required' => 'The source location is required.',
            'multi_point.*.source_route_id.exists' => 'The selected source location is invalid.',
            'multi_point.*.free_point.required' => 'The free point is required.',
            'multi_point.*.free_point.numeric' => 'Free point must be a number.',
            'multi_point.*.free_point.min' => 'Free point must be at least 0.',
            'multi_point.*.amount.required' => 'The rate amount is required.',
            'multi_point.*.amount.numeric' => 'The rate amount must be a valid number.',
            'multi_point.*.amount.min' => 'The rate amount must be at least 0.',
            'multi_point.*.customer_id.exists' => 'The selected customer is invalid.',
        ];
    }
  public function withValidator($validator): void
{
    $validator->after(function (Validator $validator) {
        $seenKeys = []; // Tracks request-level duplicates
        $checkedDbKeys = []; // Tracks keys already checked in DB

        foreach ($this->multi_point as $index => $entry) {
            $id = $entry['id'] ?? null;
            $source = $entry['source_route_id'] ?? null;
            $customer = $entry['customer_id'] ?? null;

            if (!$source) {
                continue;
            }

            $key = $source . '-' . ($customer ?? 'null');
            if (in_array($key, $seenKeys)) {
                $validator->errors()->add("multi_point.$index.customer_id", 'Duplicate multi-point pricing entry in request.');
                continue;
            }

            $seenKeys[] = $key;
            if (!in_array($key, $checkedDbKeys)) {
                $query = \DB::table('erp_logistics_mp_pricing')
                    ->where('source_route_id', $source)->whereNull('deleted_at');

                if (is_null($customer)) {
                    $query->whereNull('customer_id');
                } else {
                    $query->where('customer_id', $customer);
                }

                if ($id) {
                    $query->where('id', '!=', $id);
                }

                if ($query->exists()) {
                    $validator->errors()->add("multi_point.$index.customer_id", 'Duplicate multi-point pricing entry exists in database.');
                }

                $checkedDbKeys[] = $key;
            }
        }
    });
}



}
