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
                'regex:/^[A-Z0-9\-]{6,20}$/i', 
                Rule::unique('erp_vehicles', 'chassis_no')->ignore($id),
            ],

            'engine_no' => [
                'required',
                'string',
                'max:17',
                'regex:/^[A-Z0-9]{6,17}$/i', 
                Rule::unique('erp_vehicles', 'engine_no')->ignore($id),
            ],

            'rc_no' => [
                'nullable',
                'string',
                'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/i', 
                Rule::unique('erp_vehicles', 'rc_no')->ignore($id),
            ],

            'rto_no' => [
                'nullable',
                'string',
                'regex:/^[A-Z]{2}[0-9]{2}$/i', 
            ],

            'company_name'   => 'nullable|string',
            'model_name'     => 'nullable|string',
            'capacity_kg'    => 'nullable|numeric|min:0',
            'driver_id'      => 'nullable|exists:erp_drivers,id',
            'fuel_type'      => 'nullable|string',
            'purchase_date'  => 'nullable|date',
            'ownership'      => 'nullable|string',

            // Media Files
            'vehicle_attachment' => 'nullable|file|mimes:jpg,jpeg,png,svg|min:10|max:2048',
            'vehicle_video'      => 'nullable|file|mimetypes:video/mp4,video/x-msvideo,video/quicktime|min:100|max:20480',
            'rc_attachment'      => 'nullable|file|mimes:jpg,jpeg,png,svg|min:10|max:2048',

            // Fitness
            'fitness_no'             => 'nullable|string',
            'fitness_date'           => 'nullable|date',
            'fitness_expiry_date'    => 'nullable|date|after_or_equal:fitness_date',
            'fitness_amount'         => 'nullable|numeric|min:0',
            'fitness_attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|min:10|max:2048',

            // Insurance
            'policy_no'              => 'nullable|string',
            'insurance_company'      => 'nullable|string',
            'insurance_date'         => 'nullable|date',
            'insurance_expiry_date'  => 'nullable|date|after_or_equal:insurance_date',
            'insurance_amount'       => 'nullable|numeric|min:0',
            'insurance_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|min:10|max:2048',

            // Permit
            'type'                   => 'nullable|string',
            'permit_no'              => 'nullable|string',
            'permit_date'            => 'nullable|date',
            'permit_expiry_date'     => 'nullable|date|after_or_equal:permit_date',
            'permit_amount'          => 'nullable|numeric|min:0',
            'permit_attachment'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|min:10|max:2048',

            // Pollution
            'pollution_no'           => 'nullable|string',
            'pollution_date'         => 'nullable|date',
            'pollution_expiry_date'  => 'nullable|date|after_or_equal:pollution_date',
            'pollution_amount'       => 'nullable|numeric|min:0',
            'pollution_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|min:10|max:2048',

            // Road Tax
            'road_tax_from'   => 'nullable|date',
            'road_tax_to'     => 'nullable|date|after_or_equal:road_tax_from',
            'road_paid_on'    => 'nullable|required_with:road_tax_from,road_tax_to|date',

            'road_tax_amount'        => 'nullable|numeric|min:0',
            'road_tax_attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|min:10|max:2048',
        ];
    }

  public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $from = $this->input('road_tax_from');
        $to = $this->input('road_tax_to');
        $paid = $this->input('road_paid_on');

        if ($from && $to && $paid) {
            try {
                $toDate = \Carbon\Carbon::parse($to);
                $paidDate = \Carbon\Carbon::parse($paid);

                if ($paidDate->lte($toDate)) {
                    $validator->errors()->add(
                        'road_paid_on',
                        'Road tax paid date must be after the road tax end date.'
                    );
                }
            } catch (\Exception $e) {
                $validator->errors()->add(
                    'road_paid_on',
                    'Invalid date format in road tax fields.'
                );
            }
        }
    });
}



    public function messages(): array
    {
        return [
            'transporter_id.required' => 'Organization is required.',
            'transporter_id.exists'   => 'Selected organization does not exist.',

            'lorry_no.required'       => 'Vehicle number is required.',
            'lorry_no.regex'          => 'Invalid vehicle number format. Example: MH12AB1234',
            'lorry_no.unique'         => 'This Vehicle number already exists.',

            'vehicle_type_id.required' => 'Vehicle type is required.',
            'vehicle_type_id.exists'   => 'Selected Vehicle does not exist.',

            'chassis_no.required'     => 'Chassis number is required.',
            'chassis_no.unique'       => 'This chassis number already exists.',

            'engine_no.required'      => 'Engine number is required.',
            'engine_no.max'           => 'Engine number cannot exceed 17 characters.',
            'engine_no.unique'        => 'This engine number already exists.',

            'rc_no.unique'            => 'This RC number already exists.',

            'chassis_no.regex'        => 'Chassis number must be alphanumeric and 6–20 characters.',
            'engine_no.regex'         => 'Engine number must be alphanumeric and up to 17 characters.',
            'rc_no.regex'             => 'RC number format is invalid. Example: UP14CA1234.',
            'rto_no.regex'            => 'RTO number format is invalid. Example: UP14.',

            'capacity_kg.numeric'     => 'Capacity must be a valid number.',
            'capacity_kg.min'         => 'Capacity must be a positive number.',
            'driver_id.exists'        => 'Selected driver does not exist.',
            'purchase_date.date'      => 'Purchase date must be a valid date.',

            // Media
            'vehicle_attachment.min'     => 'Vehicle image must be at least 10KB.',
            'vehicle_attachment.max'     => 'Vehicle image must not exceed 2MB.',
            'vehicle_attachment.mimes'   => 'Vehicle image must be jpg, jpeg, png, or svg.',
            'vehicle_video.min'          => 'Vehicle video must be at least 100KB.',
            'vehicle_video.max'          => 'Vehicle video must not exceed 20MB.',
            'vehicle_video.mimetypes'    => 'Vehicle video must be of type: mp4, avi, mov.',
            'rc_attachment.min'          => 'RC image must be at least 10KB.',
            'rc_attachment.max'          => 'RC image must not exceed 2MB.',

            // Attachments
            '*.attachment.min'           => 'Attachment file must be at least 10KB.',
            '*.attachment.max'           => 'Attachment file must not exceed 2MB.',
            '*.amount.min'               => 'Amount must be a positive number.',

            'fitness_amount.min' => 'Fitness amount must be greater than or equal to 0.',
            'permit_amount.min'   => 'Permit amount must be greater than or equal to 0.',
            'insurance_amount.min' => 'Insurance amount must be greater than or equal to 0.',
            'pollution_amount.min' => 'Pollution amount must be greater than or equal to 0.',
            'road_tax_amount.min' => 'Road tax amount must be greater than or equal to 0.',
            'capacity_kg.min' => 'Capacity must be greater than or equal to 0.',


            // Date Validations
            'road_tax_from.date'    => 'Start date must be a valid date.',
            'road_tax_to.date'      => 'End date must be a valid date.',
            'road_tax_to.after_or_equal' => 'End date must be after or equal to start date.',
            'road_paid_on.required_with' => 'Road tax paid date is required when tax period is given.',
            'road_paid_on.date'     => 'Paid date must be a valid date.',

            'fitness_expiry_date.after_or_equal'    => 'Fitness expiry date must be after or equal to fitness date.',
            'insurance_expiry_date.after_or_equal'  => 'Insurance expiry date must be after or equal to insurance date.',
            'permit_expiry_date.after_or_equal'     => 'Permit expiry date must be after or equal to permit date.',
            'pollution_expiry_date.after_or_equal'  => 'Pollution expiry date must be after or equal to pollution date.',
            'road_tax_to.after_or_equal'            => 'Road tax end date must be after or equal to road tax start date.',
        ];
    }
}
