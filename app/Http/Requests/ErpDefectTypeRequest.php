<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpDefectTypeRequest extends FormRequest
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
        return [
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.priority' => ['required', 'in:High,Medium,Low'],
            'rows.*.estimated_time' => ['required', 'integer', 'min:1'],
            'rows.*.description' => ['nullable', 'string', 'max:255'],
            'rows.*.status' => ['required', 'in:Active,Inactive'],
        ];
    }

    public function messages()
    {
        return [
            'rows.*.name.required' => 'Type Name is required for all records.',
            'rows.*.priority.required' => 'Priority is required.',
            'rows.*.priority.in' => 'Priority must be High, Medium, or Low.',
            'rows.*.estimated_time.required' => 'Estimated Time is required.',
            'rows.*.estimated_time.integer' => 'Estimated Time must be an integer.',
            'rows.*.estimated_time.min' => 'Estimated Time must be at least 1 day.',
            'rows.*.status.required' => 'Status is required.',
            'rows.*.status.in' => 'Status must be Active or Inactive.',
        ];
    }
}
