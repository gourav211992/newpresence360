<?php

namespace App\Http\Requests;

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
            'vehicle_type.*.name' => 'required|string|max:255',
            'vehicle_type.*.capacity' => 'required|numeric|max:999999.99',
            'vehicle_type.*.uom_id' => 'required|integer|exists:erp_units,id',
            'vehicle_type.*.description' => 'nullable|string',
            'vehicle_type.*.status' => 'required|in:Active,Inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type.required' => 'At least one vehicle type entry is required.',

            'vehicle_type.*.name.required' => 'Vehicle type name is required.',
            'vehicle_type.*.name.string' => 'Vehicle type name must be a string.',
            'vehicle_type.*.name.max' => 'Vehicle type name may not be greater than 255 characters.',

            'vehicle_type.*.capacity.required' => 'Capacity is required.',
            'vehicle_type.*.capacity.numeric' => 'Capacity must be a valid number.',
            'vehicle_type.*.capacity.max' => 'Capacity may not exceed the allowed limit.',

            'vehicle_type.*.uom_id.required' => 'UOM is required.',
            'vehicle_type.*.uom_id.integer' => 'UOM must be a valid selection.',
            'vehicle_type.*.uom_id.exists' => 'Selected UOM is invalid.',

            'vehicle_type.*.description.string' => 'Description must be a valid string.',

            'vehicle_type.*.status.required' => 'Status is required.',
            'vehicle_type.*.status.in' => 'Status must be either Active or Inactive.',
        ];
    }
}
