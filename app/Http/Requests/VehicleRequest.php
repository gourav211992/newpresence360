<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? null;

        return [
            'transporter_id' => 'required',

            'lorry_no' => [
                'required',
                'string',
                'regex:/^[A-Z]{2}\d{2}[A-Z]{1,2}\d{4}$/',
                Rule::unique('erp_vehicles', 'lorry_no')->ignore($id),
            ],

            'vehicle_type_id' => 'required|integer|exists:erp_vehicle_types,id',

            'chassis_no' => [
                'required',
                'string',
                Rule::unique('erp_vehicles', 'chassis_no')->ignore($id),
            ],

            'engine_no' => [
                'required',
                'string',
                Rule::unique('erp_vehicles', 'engine_no')->ignore($id),
            ],

            'rc_no' => [
                'nullable',
                'string',
                Rule::unique('erp_vehicles', 'rc_no')->ignore($id),
            ],

            'rto_no'         => 'nullable|string',
            'company_name'   => 'nullable|string',
            'model_name'     => 'nullable|string',
            'capacity_kg'    => 'nullable|numeric',
            'driver_id'      => 'nullable|exists:erp_drivers,id',
            'fuel_type'      => 'nullable|string',
            'purchase_date'  => 'nullable|date',
            'ownership'      => 'nullable|string',

            // Media Files
            'vehicle_attachment' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'vehicle_video'      => 'nullable|file|mimetypes:video/mp4,video/x-msvideo,video/quicktime|max:51200',
            'rc_attachment'      => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',

            // Fitness
            'fitness_no'             => 'nullable|string',
            'fitness_date'           => 'nullable|date',
            'fitness_expiry_date'    => 'nullable|date|after_or_equal:fitness_date',
            'fitness_amount'         => 'nullable|numeric',
            'fitness_attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Insurance
            'policy_no'              => 'nullable|string',
            'insurance_company'      => 'nullable|string',
            'insurance_date'         => 'nullable|date',
            'insurance_expiry_date'  => 'nullable|date|after_or_equal:insurance_date',
            'insurance_amount'       => 'nullable|numeric',
            'insurance_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Permit
            'type'                   => 'nullable|string',
            'permit_no'              => 'nullable|string',
            'permit_date'            => 'nullable|date',
            'permit_expiry_date'     => 'nullable|date|after_or_equal:permit_date',
            'permit_amount'          => 'nullable|numeric',
            'permit_attachment'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Pollution
            'pollution_no'           => 'nullable|string',
            'pollution_date'         => 'nullable|date',
            'pollution_expiry_date'  => 'nullable|date|after_or_equal:pollution_date',
            'pollution_amount'       => 'nullable|numeric',
            'pollution_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Road Tax
            'road_tax_from'          => 'nullable|date',
            'road_tax_to'            => 'nullable|date|after_or_equal:road_tax_from',
            'road_paid_on'           => 'nullable|date',
            'road_tax_amount'        => 'nullable|numeric',
            'road_tax_attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'transporter_id.required' => 'Transporter is required.',
            'transporter_id.exists'   => 'Selected transporter does not exist.',

            'lorry_no.required'       => 'Vehicle number is required.',
            'lorry_no.regex'          => 'Invalid vehicle number format. Example: MH12AB1234',
            'lorry_no.unique'         => 'This Vehicle number already exists.',

            'vehicle_type_id.required' => 'Vehicle type is required.',
            'vehicle_type_id.exists'   => 'Selected Vehicle does not exist.',

            'chassis_no.required'     => 'Chassis number is required.',
            'chassis_no.unique'       => 'This chassis number already exists.',

            'engine_no.required'      => 'Engine number is required.',
            'engine_no.unique'        => 'This engine number already exists.',

            'rc_no.unique'            => 'This RC number already exists.',
            'capacity_kg.numeric'     => 'Capacity must be a valid number.',
            'driver_id.exists'        => 'Selected driver does not exist.',
            'purchase_date.date'      => 'Purchase date must be a valid date.',

            // Media
            'vehicle_attachment.file'     => 'Vehicle image must be a valid file.',
            'vehicle_attachment.mimes'    => 'Vehicle image must be of type: jpg, jpeg, png, svg.',
            'vehicle_video.file'          => 'Vehicle video must be a valid file.',
            'vehicle_video.mimetypes'     => 'Vehicle video must be of type: mp4, avi, mov.',

            // Fitness
            'fitness_expiry_date.after_or_equal' => 'Fitness expiry date must be after or equal to fitness date.',

            'insurance_expiry_date.after_or_equal' => 'Insurance expiry must be after or equal to insurance date.',

            'permit_expiry_date.after_or_equal' => 'Permit expiry must be after or equal to permit date.',

            'pollution_expiry_date.after_or_equal' => 'Pollution expiry must be after or equal to pollution date.',

            'road_tax_to.after_or_equal' => 'Road tax to date must be after or equal to road tax from.',
        ];
    }
}
