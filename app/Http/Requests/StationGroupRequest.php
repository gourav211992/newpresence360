<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class StationGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    protected $organization_id;
    protected $group_id;
    protected $company_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null;
        $this->company_id = $organization ? $organization->company_id : null;
    }

    public function rules(): array
    {
        $stationGroupId = $this->route('id'); 
        
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                $stationGroupId
                    ? Rule::unique('erp_station_groups', 'name')
                        ->where('group_id', $this->group_id)
                        ->whereNull('deleted_at')
                        ->ignore($stationGroupId)
                    : Rule::unique('erp_station_groups', 'name')
                        ->where('group_id', $this->group_id)
                        ->whereNull('deleted_at'),
            ],
            'alias' => [
                'required',
                'string',
                'max:50',
                $stationGroupId
                    ? Rule::unique('erp_station_groups', 'alias')
                        ->where('group_id', $this->group_id)
                        ->where('company_id', $this->company_id)
                        ->where('organization_id', $this->organization_id)
                        ->whereNull('deleted_at')
                        ->ignore($stationGroupId)
                    : Rule::unique('erp_station_groups', 'alias')
                        ->where('group_id', $this->group_id)
                        ->where('company_id', $this->company_id)
                        ->where('organization_id', $this->organization_id)
                        ->whereNull('deleted_at'),
            ],
            'status' => 'nullable|in:active,inactive', 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 50 characters.',
            'alias.required' => 'The alias field is required.',
            'alias.string' => 'The alias must be a string.',
            'alias.max' => 'The alias may not be greater than 50 characters.',
            'status.in' => 'The status must be either "active" or "inactive".',
        ];
    }
}
