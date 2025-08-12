<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\ErpVehicleType;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_type' => 'required|array|min:1',
            'vehicle_type.*.name' => [
            'required',
            'string',
            'max:100',
            'regex:/^[A-Za-z\s\.\-]+$/'
        ],


           'vehicle_type.*.capacity' => [
                'required',
                'numeric',
                'min:0.01', 
                'max:999999.99',
                'regex:/^\d{1,6}(\.\d{1,2})?$/', 
            ],

            'vehicle_type.*.uom_id' => 'required|integer|exists:erp_units,id',
            'vehicle_type.*.description' => 'nullable|string|max:500|regex:/^[A-Za-z0-9\s\.\,\-\(\)]+$/',
            'vehicle_type.*.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type.required' => 'At least one vehicle type entry is required.',

            'vehicle_type.*.name.required' => 'Vehicle type name is required.',
            'vehicle_type.*.name.regex' => 'The vehicle type name may only contain letters, spaces, dots, and hyphens.',
            'vehicle_type.*.name.max' => 'Vehicle type name may not be greater than 255 characters.',

            'vehicle_type.*.capacity.required' => 'Capacity is required.',
            'vehicle_type.*.capacity.regex' => 'Capacity must be a valid number with up to 2 decimal places (max 999999.99).',
            'vehicle_type.*.capacity.max' => 'Capacity may not exceed the allowed limit.',

            'vehicle_type.*.uom_id.required' => 'UOM is required.',
            'vehicle_type.*.uom_id.integer' => 'UOM must be a valid selection.',
            'vehicle_type.*.uom_id.exists' => 'Selected UOM is invalid.',

            'vehicle_type.*.description.string' => 'Description must be a valid string.',

            'vehicle_type.*.status.required' => 'Status is required.',
            'vehicle_type.*.status.in' => 'Status must be either Active or Inactive.',
        ];
    }

   
public function withValidator(Validator $validator)
{
    $validator->after(function ($validator) {
        $vehicleTypes = $this->input('vehicle_type', []);
        $nameOccurrences = [];

        foreach ($vehicleTypes as $index => $vehicleType) {
            $rawName = $vehicleType['name'] ?? '';
            $name = strtolower(trim($rawName));

            if ($name === '') {
                continue;
            }

            // Group name occurrences for duplicate detection in form
            if (!isset($nameOccurrences[$name])) {
                $nameOccurrences[$name] = [];
            }

            $nameOccurrences[$name][] = $index;
        }

        foreach ($nameOccurrences as $name => $indexes) {
            if (count($indexes) > 1) {
                foreach (array_slice($indexes, 1) as $duplicateIndex) {
                    $validator->errors()->add(
                        "vehicle_type.{$duplicateIndex}.name",
                        "The vehicle type name '{$vehicleTypes[$duplicateIndex]['name']}' is already exists in the database."
                    );
                }
            }

            $firstIndex = $indexes[0];
            $rowId = $vehicleTypes[$firstIndex]['id'] ?? null;

            $existsInDb = ErpVehicleType::whereRaw('LOWER(name) = ?', [$name])
                ->whereNull('deleted_at')
                ->when($rowId, fn($q) => $q->where('id', '!=', $rowId)) 
                ->exists();

            if ($existsInDb) {
                $validator->errors()->add(
                    "vehicle_type.{$firstIndex}.name",
                    "The vehicle type name '{$vehicleTypes[$firstIndex]['name']}' already exists in the database."
                );
            }
        }
    });
}



}
