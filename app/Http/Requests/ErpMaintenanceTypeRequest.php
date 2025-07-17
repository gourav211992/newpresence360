<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpMaintenanceTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        // Validating each row in the "rows" array
        return [
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.description' => ['nullable', 'string', 'max:255'],
            'rows.*.status' => ['required', 'in:Active,Inactive'],
        ];
    }

    public function messages()
    {
        return [
            'rows.required' => 'At least one maintenance type should be added.',
            'rows.*.name.required' => 'Type Name is required for all records.',
            'rows.*.status.required' => 'Status is required.',
            'rows.*.status.in' => 'Status must be either Active or Inactive.',
        ];
    }
}
