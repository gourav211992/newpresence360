<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LanParcelRequest extends FormRequest
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
                'series' => 'required|string|max:255',
                'document_no' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'plot_area' => 'required|numeric',

                ];
        }
        if ($this->status_val == 'submitted') {
            return[
            'series' => 'required|string|max:255',
            'document_no' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'surveyno' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'khasara_no' => 'nullable|string|max:255',
            'plot_area' => 'required|numeric',
            'area_unit' => 'nullable|string|max:255',
            'dimension' => 'required|nullable|string|max:255',
            'land_valuation' => 'nullable|numeric',
            'address' => 'required|nullable|string|max:255',
            'district' => 'required|nullable|string|max:255',
            'state' => 'required|nullable|string|max:255',
            'country' => 'required|nullable|string|max:255',
            'pincode' => 'required|nullable|string|max:10',
            'remarks' => 'nullable|string',
            'handoverdate' => 'required|nullable|date',
            'geofence' => 'nullable|file|mimes:csv,txt'
            ];
        }


    }

}
