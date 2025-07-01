<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RouteMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'route_master'                     => 'required|array|min:1',
            'route_master.*.name'          => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9\s\.\-]+$/'            // letters, numbers, spaces, dots, hyphens
            ],
            'route_master.*.country_id'        => [
                'required',
                'integer',
                'exists:mysql_master.countries,id'
            ],
            'route_master.*.state_id'          => [
                'required',
                'integer',
                'exists:mysql_master.states,id'
            ],
            'route_master.*.city_id'           => [
                'required',
                'integer',
                'exists:mysql_master.cities,id'
            ],
            'route_master.*.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'route_master.required'                    => 'You must add at least one route.',
            'route_master.array'                       => 'Invalid route data submitted.',

            'route_master.*.name.required'         => 'Location is required.',
            'route_master.*.name.string'           => 'Location must be text.',
            'route_master.*.name.max'              => 'Location may not exceed 100 characters.',
            'route_master.*.name.regex'            => 'Location may only contain letters, numbers, spaces, dots, and hyphens.',

            'route_master.*.country_id.required'       => 'Country is required.',
            'route_master.*.country_id.integer'        => 'Country selection is invalid.',
            'route_master.*.country_id.exists'         => 'Selected country does not exist.',

            'route_master.*.state_id.required'         => 'State is required.',
            'route_master.*.state_id.integer'          => 'State selection is invalid.',
            'route_master.*.state_id.exists'           => 'Selected state does not exist.',

            'route_master.*.city_id.required'         => 'City is required.',
            'route_master.*.city_id.integer'           => 'City selection is invalid.',
            'route_master.*.city_id.exists'            => 'Selected city does not exist.',

            'route_master.*.status.required'           => 'Status is required.',
            'route_master.*.status.in'                 => 'Status must be either Active or Inactive.',
        ];
    }
}
