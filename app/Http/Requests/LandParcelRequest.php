<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandParcelRequest extends FormRequest
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
    public function rules(): array
    {
        if ($this->status_val == 'draft') {
            return[
                'document_no' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'dimension' => 'sometimes', // Validates if filled, ensures it's numeric and greater than 0
                'plot_area' => 'sometimes|nullable|numeric|min:0.01', // Validates if filled, ensures it's numeric and greater than 0
                'land_valuation' => 'sometimes|nullable|numeric|min:0.01' // Validates if filled, ensures it's numeric and greater than 0

                ];
        }
        if ($this->status_val == 'submitted') {

                return[
                    'document_no' => 'required|string|max:255',
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string|max:255',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
                    'surveyno' => 'nullable|string|max:255',
                    'status' => 'required|boolean',
                    'khasara_no' => 'nullable|string|max:255',
                    'area_unit' => 'required|string|max:255',
                    'address' => 'required|nullable|string|max:255',
                    'district' => 'required|nullable|string|max:255',
                    'state' => 'required|nullable|string|max:255',
                    'country' => 'required|nullable|string|max:255',
                    'pincode' => 'required|nullable|string|max:10',
                    'remarks' => 'nullable|string',
                    'handoverdate' => 'required|nullable|date',
                    'geofence' => 'nullable|file|mimes:csv,txt',
                    'dimension' => 'required', // Validates if filled, ensures it's numeric and greater than 0
                    'plot_area' => 'required|numeric|min:0.01', // Validates if filled, ensures it's numeric and greater than 0
                    'land_valuation' => 'nullable|numeric|min:0.01', // Validates if filled, ensures it's numeric and greater than 0

                    ];


        }
        return[];

    }
    public function messages()
{
    return [
        'document_no.required' => 'The document number field is required.',
        'document_no.string' => 'The document number must be a string.',
        'document_no.max' => 'The document number cannot exceed 255 characters.',

        'name.required' => 'The name field is required.',
        'name.string' => 'The name must be a string.',
        'name.max' => 'The name cannot exceed 255 characters.',

        'description.string' => 'The description must be a string.',
        'description.max' => 'The description cannot exceed 255 characters.',

        'latitude.numeric' => 'The latitude must be a number.',
        'longitude.numeric' => 'The longitude must be a number.',

        'surveyno.string' => 'The survey number must be a string.',
        'surveyno.max' => 'The survey number cannot exceed 255 characters.',

        'status.required' => 'The status field is required.',
        'status.boolean' => 'The status must be either true or false.',

        'khasara_no.string' => 'The khasara number must be a string.',
        'khasara_no.max' => 'The khasara number cannot exceed 255 characters.',

        'area_unit.required' => 'The area unit field is required.',
        'area_unit.string' => 'The area unit must be a string.',
        'area_unit.max' => 'The area unit cannot exceed 255 characters.',

        'address.required' => 'The address field is required.',
        'address.string' => 'The address must be a string.',
        'address.max' => 'The address cannot exceed 255 characters.',

        'district.required' => 'The district field is required.',
        'district.string' => 'The district must be a string.',
        'district.max' => 'The district cannot exceed 255 characters.',

        'state.required' => 'The state field is required.',
        'state.string' => 'The state must be a string.',
        'state.max' => 'The state cannot exceed 255 characters.',

        'country.required' => 'The country field is required.',
        'country.string' => 'The country must be a string.',
        'country.max' => 'The country cannot exceed 255 characters.',

        'pincode.required' => 'The pincode field is required.',
        'pincode.string' => 'The pincode must be a string.',
        'pincode.max' => 'The pincode cannot be longer than 10 characters.',

        'remarks.string' => 'The remarks must be a string.',

        'handoverdate.required' => 'The handover date field is required.',
        'handoverdate.date' => 'The handover date must be a valid date.',

        'geofence.file' => 'The geofence must be a file.',
        'geofence.mimes' => 'The geofence file must be a CSV or TXT file.',

        'dimension.required' => 'The dimension field is required.',
        // 'dimension.numeric' => 'The dimension must be a number.',
        // 'dimension.min' => 'The dimension must be at least 0.01.',

        'plot_area.required' => 'The plot area is required.',
        'plot_area.numeric' => 'The plot area must be a number.',
        'plot_area.min' => 'The plot area must be at least 0.01.',

        'land_valuation.numeric' => 'The land valuation must be a number.',
        'land_valuation.min' => 'The land valuation must be at least 0.01.',
    ];
}

}
