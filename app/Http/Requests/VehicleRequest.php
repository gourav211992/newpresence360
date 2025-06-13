<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'transporter_id'     => 'required',
            'lorry_no'           => 'required|string|unique:erp_vehicles,lorry_no,' . $id,
            'vehicle_type'       => 'required|string',
            'chassis_no'          => 'required|string|unique:erp_vehicles,chassis_no,' . $id,
            'engine_no'          => 'required|string|unique:erp_vehicles,engine_no,' . $id,
            'rc_no'              => 'required|string|unique:erp_vehicles,rc_no,' . $id,
            'rto_no'             => 'required|string',
            'company_name'       => 'required|string',
            'model_name'         => 'required|string',
            'capacity_kg'        => 'required|numeric',
            'driver_id'          => 'required|exists:erp_drivers,id',
            'fuel_type'          => 'required|string',
            'purchase_date'      => 'required|date',
            'ownership'          => 'required|string',

            // Media Files (conditional based on create or update)
            'vehicle_attachment' => $id ? 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048' : 'required|file|mimes:jpg,jpeg,png,svg|max:2048',
            'vehicle_video'      => $id ? 'nullable|file|mimetypes:video/mp4,video/x-msvideo,video/quicktime|max:51200' : 'required|file|mimetypes:video/mp4,video/x-msvideo,video/quicktime|max:51200',
            'rc_attachment'      => $id ? 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048' : 'required|file|mimes:jpg,jpeg,png,svg|max:2048',

            // Fitness
            'fitness_no'             => 'required|string',
            'fitness_date'           => 'required|date',
            'fitness_expiry_date'    => 'required|date|after_or_equal:fitness_date',
            'fitness_amount'         => 'nullable|numeric',
            'fitness_attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Insurance
            'policy_no'              => 'required|string',
            'insurance_company'      => 'required|string',
            'insurance_date'         => 'required|date',
            'insurance_expiry_date'  => 'required|date|after_or_equal:insurance_date',
            'insurance_amount'       => 'nullable|numeric',
            'insurance_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Permit
            'type'                   => 'required|string',
            'permit_no'              => 'required|string',
            'permit_date'            => 'required|date',
            'permit_expiry_date'     => 'required|date|after_or_equal:permit_date',
            'permit_amount'          => 'nullable|numeric',
            'permit_attachment'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Pollution
            'pollution_no'           => 'required|string',
            'pollution_date'         => 'required|date',
            'pollution_expiry_date'  => 'required|date|after_or_equal:pollution_date',
            'pollution_amount'       => 'nullable|numeric',
            'pollution_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Road Tax
            'road_tax_from'          => 'required|date',
            'road_tax_to'            => 'required|date|after_or_equal:road_tax_from',
            'road_paid_on'           => 'required|date',
            'road_tax_amount'        => 'nullable|numeric',
            'road_tax_attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'transporter_id.required' => 'Transporter is required.',
            'transporter_id.exists'   => 'Selected transporter does not exist.',

            'lorry_no.required'       => 'Lorry number is required.',
            'lorry_no.unique'         => 'This lorry number already exists.',

            'vehicle_type.required'   => 'Vehicle type is required.',

            'chassis_no.required'      => 'Chassis number is required.',
            'chassis_no.unique'        => 'This chassis number already exists.',

            'engine_no.required'      => 'Engine number is required.',
            'engine_no.unique'        => 'This engine number already exists.',

            'rc_no.required'          => 'RC number is required.',
            'rc_no.unique'            => 'This RC number already exists.',

            'rto_no.required'         => 'RTO number is required.',
            'company_name.required'   => 'Company name is required.',
            'model_name.required'     => 'Model name is required.',
            'capacity_kg.required'    => 'Capacity in kilograms is required.',
            'capacity_kg.numeric'     => 'Capacity must be a valid number.',

            'driver_id.required'      => 'Driver is required.',
            'driver_id.exists'        => 'Selected driver does not exist.',

            'fuel_type.required'      => 'Fuel type is required.',
            'purchase_date.required'  => 'Purchase date is required.',
            'purchase_date.date'      => 'Purchase date must be a valid date.',
            'ownership.required'      => 'Ownership type is required.',

            'vehicle_attachment.required' => 'Vehicle image is required.',
            'vehicle_attachment.file'     => 'Vehicle image must be a valid file.',
            'vehicle_attachment.mimes'    => 'Vehicle image must be of type: jpg, jpeg, png, svg.',

            'vehicle_video.required'      => 'Vehicle video is required.',
            'vehicle_video.file'          => 'Vehicle video must be a valid file.',
            'vehicle_video.mimetypes'     => 'Vehicle video must be of type: mp4, avi, mov.',

            'fitness_no.required'         => 'Fitness certificate number is required.',
            'fitness_date.required'       => 'Fitness date is required.',
            'fitness_expiry_date.required'=> 'Fitness expiry date is required.',

            'policy_no.required'          => 'Policy number is required.',
            'insurance_company.required'  => 'Insurance company name is required.',
            'insurance_date.required'     => 'Insurance start date is required.',
            'insurance_expiry_date.required' => 'Insurance expiry date is required.',
             
            'type.required'               => 'Type is required.',
            'type.exists'                 => 'Selected type does not exist.',

            'permit_no.required'          => 'Permit number is required.',
            'permit_date.required'        => 'Permit start date is required.',
            'permit_expiry_date.required' => 'Permit expiry date is required.',

            'pollution_no.required'       => 'Pollution certificate number is required.',
            'pollution_date.required'     => 'Pollution issue date is required.',
            'pollution_expiry_date.required' => 'Pollution expiry date is required.',

            'road_tax_from.required'      => 'Road tax start date is required.',
            'road_tax_to.required'        => 'Road tax end date is required.',
            'road_paid_on.required'       => 'Road tax paid date is required.',
        ];
    }
}
