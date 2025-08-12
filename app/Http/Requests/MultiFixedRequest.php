<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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
        $sourceId = $this->input('source_route_id');
        $destinationId = $this->input('destination_route_id');
        $vehicleTypeIds = array_map('intval', $this->input('vehicle_type_id', []));
        $customerId = $this->input('customer_id');
        $locations = collect($this->input('multi_fixed_pricing', []));
        $currentId = $this->route('id'); 

        // ❌ Source & Destination cannot be same
        if ($sourceId && $destinationId && $sourceId == $destinationId) {
            $validator->errors()->add('destination_route_id', 'Source and destination cannot be the same.');
        }

        // 🔍 Check for duplicate locations inside form
        $locationIds = $locations->pluck('location_route_id')->filter()->values();
        $duplicates = $locationIds->duplicates();

        foreach ($locations as $index => $item) {
            $locationId = $item['location_route_id'] ?? null;
            if (!$locationId) continue;

            if ($duplicates->contains($locationId)) {
                $validator->errors()->add("multi_fixed_pricing.$index.location_route_id", 'Duplicate location not allowed.');
            }

            if ($locationId == $sourceId) {
                $validator->errors()->add("multi_fixed_pricing.$index.location_route_id", 'Location cannot be the same as source.');
            }

            if ($locationId == $destinationId) {
                $validator->errors()->add("multi_fixed_pricing.$index.location_route_id", 'Location cannot be the same as destination.');
            }
        }

        // 📝 Check for duplicate combinations in the form itself
        $seenKeys = [];
        foreach ($vehicleTypeIds as $vIndex => $vehicleTypeId) {
            $key = "{$sourceId}-{$destinationId}-{$vehicleTypeId}-" . ($customerId ?: 'NULL');
            if (in_array($key, $seenKeys, true)) {
                $validator->errors()->add('customer_id', 'Duplicate multi-fixed pricing entry in form.');
                break;
            }
            $seenKeys[] = $key;
        }

        // 📦 Check for duplicates in DB
        foreach ($vehicleTypeIds as $vehicleTypeId) {
            $query = DB::table('erp_logistics_mf_pricing')
                ->where('source_route_id', $sourceId)
                ->where('destination_route_id', $destinationId)
                ->whereNull('deleted_at')
                ->whereRaw('JSON_CONTAINS(vehicle_type_id, ?)', [json_encode((string) $vehicleTypeId)])
                ->where(function ($q) use ($customerId) {
                    if (is_null($customerId)) {
                        $q->whereNull('customer_id');
                    } else {
                        $q->where('customer_id', $customerId);
                    }
                })
                ->when($currentId, fn($q) => $q->where('id', '!=', $currentId));

            if ($query->exists()) {
                $validator->errors()->add('customer_id', 'Duplicate multi-fixed pricing entry exists in database.');
                break;
            }
        }
    });
}





}
