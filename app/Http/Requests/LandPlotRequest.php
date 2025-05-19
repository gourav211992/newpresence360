<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandPlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Set to true if no specific authorization is needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'series' => 'required|string|max:255',
            'document_no' => 'required|string|max:255',
            'doc_number_type' => 'required|in:Auto,Manually',
            'doc_reset_pattern' => 'nullable|in:Never,Yearly,Quarterly,Monthly',
            'doc_prefix' => 'nullable|string|max:255',
            'doc_suffix' => 'nullable|string|max:255',
            'doc_no' => 'nullable|integer|min:1',
            'plot_name' => 'nullable|string|max:255',
            'attachments' => 'nullable|array', // Ensuring valid JSON format
            'land_id' => 'required|integer|min:1',
            'land_size' => 'required|string',
            'land_location' => 'required|string|max:255',
            'status' => 'required|boolean',
            'khasara_no' => 'nullable|string|max:255',
            'plot_area' => 'required|numeric|min:0.01',
            'area_unit' => 'required|string|max:255',
            'dimension' => 'nullable|string|max:255',
            'plot_valuation' => 'nullable|numeric|min:0.01',
            'address' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|digits:6', // Ensuring pincode has exactly 6 digits
            'type_of_usage' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geofence_file' => 'nullable|string|max:255',
        ];
    
        if ($this->input('page') == 'edit') {
            if ($this->input('status_val') !== 'draft') {
                $rules = array_merge($rules, [
                    'series' => 'nullable|string|max:255',
                    'document_no' => 'nullable|string|max:255',
                    'doc_number_type' => 'nullable|in:Auto,Manually',
                    'area_unit' => 'required',
                    'address' => 'required',
                    'pincode' => 'required|digits:6',
                    'status' => 'required',
                    'type_of_usage' => 'required',
                ]);
            }
        } else {
            if ($this->input('status_val') !== 'draft') {
                $rules = array_merge($rules, [
                    'area_unit' => 'required',
                    'address' => 'required',
                    'pincode' => 'required|digits:6',
                    'status' => 'required',
                    'type_of_usage' => 'required',
                ]);
            }
        }
    
        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'series.required' => 'The series field is required.',
            'series.string' => 'The series must be a valid string.',
            'series.max' => 'The series may not be greater than 255 characters.',

            'document_no.required' => 'The document number is required.',
            'document_no.string' => 'The document number must be a valid string.',
            'document_no.max' => 'The document number may not be greater than 255 characters.',

            'doc_number_type.required' => 'The document number type is required.',
            'doc_number_type.in' => 'The document number type must be either Auto or Manually.',

            'doc_reset_pattern.in' => 'The document reset pattern must be one of Never, Yearly, Quarterly, or Monthly.',

            'doc_no.integer' => 'The document number must be an integer.',
            'doc_no.min' => 'The document number must be at least 1.',

            'land_id.required' => 'The land ID is required.',
            'land_id.integer' => 'The land ID must be an integer.',
            'land_id.min' => 'The land ID must be a positive number.',

            'land_size.required' => 'The land size is required.',
            'land_size.numeric' => 'The land size must be a valid number.',
            'land_size.min' => 'The land size must be greater than 0.',

            'land_location.required' => 'The land location is required.',
            'land_location.string' => 'The land location must be a valid string.',
            'land_location.max' => 'The land location may not be greater than 255 characters.',

            'status.required' => 'The status field is required.',
            'status.boolean' => 'The status must be a boolean value.',

            'plot_area.required' => 'The plot area is required.',
            'plot_area.numeric' => 'The plot area must be a valid number.',
            'plot_area.min' => 'The plot area must be greater than 0.',

            'area_unit.required' => 'The area unit is required.',
            'area_unit.string' => 'The area unit must be a valid string.',

            'pincode.required' => 'The pincode is required.',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',

            'type_of_usage.required' => 'The type of usage field is required.',
            'type_of_usage.string' => 'The type of usage must be a valid string.',

            'latitude.numeric' => 'The latitude must be a valid number.',
            'latitude.between' => 'The latitude must be between -90 and 90.',

            'longitude.numeric' => 'The longitude must be a valid number.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
        ];
    }
}
