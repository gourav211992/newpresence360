<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class StationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected $organization_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }

    public function rules(): array
    {
        $stationId = $this->route('id'); 

        return [
            'parent_id' => 'nullable|exists:erp_stations,id',
            // 'station_group_id' => [
            // 'required',
            // 'exists:erp_station_groups,id',
            // ],
           'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_stations')
                    ->ignore($stationId)
                    ->where('group_id', $this->group_id) 
                    ->whereNull('parent_id')
                    ->whereNull('deleted_at'), 
            ],
            'alias' => [
                'max:50',
            ],
            // 'is_consumption' => [
            //     'required',
            //     'string',
            //     Rule::in(['yes', 'no']), 
            // ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive']), 
            ],
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable',
            'organization_id' => 'nullable|exists:organizations,id', 
            'substations.*.id' => 'nullable|exists:erp_stations,id',
            'substations.*.name' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.exists' => 'The selected parent station is invalid.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 100 characters.',
            'name.unique' => 'The  name has already been taken.',
            'alias.max' => 'The alias may not be greater than 50 characters.',
            'alias.unique' => 'The alias has already been taken.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of the following: active, inactive.',
            'group_id.exists' => 'The selected group is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'substations.*.name.string' => ' names must be strings.',
            'substations.*.name.max' => 'names may not be greater than 100 characters.',
        ];
    }
}
