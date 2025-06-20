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

            'vehicle_type.*.description.string' => 'Description must be a valid string.',

            'vehicle_type.*.status.required' => 'Status is required.',
            'vehicle_type.*.status.in' => 'Status must be either Active or Inactive.',
        ];
    }
}
