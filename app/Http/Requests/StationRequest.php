<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use App\Models\StationLine;
use Auth;

class StationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected $organization_id;
    protected $company_id;
    protected $group_id;

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
        $stationId = $this->route('id'); 
        $uniqueScope = Rule::unique('erp_stations')
            ->ignore($stationId)
            ->whereNull('deleted_at')
            ->whereNull('parent_id');
        if ($this->group_id !== null) {
            $uniqueScope->where('group_id', $this->group_id);
        }

        if ($this->company_id !== null) {
            $uniqueScope->where(function($query) {
                $query->where('company_id', $this->company_id)
                    ->orWhereNull('company_id');
            });
        }

        if ($this->organization_id !== null) {
            $uniqueScope->where(function($query) {
                $query->where('organization_id', $this->organization_id)
                    ->orWhereNull('organization_id');
            });
        }

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
                $uniqueScope
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
            
            'lines' => 'nullable|array',
            'lines.*.id' => 'nullable',
            'lines.*.name' => ['required', 'string', 'max:100'],
            'lines.*.supervisor_name' => ['nullable', 'string', 'max:100'],
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);
            $stationId = $this->route('id');
            $names = [];
            foreach ($lines as $index => $line) {
                $name = trim($line['name'] ?? '');
                // Check duplicate in request
                if (in_array($name, $names)) {
                    $validator->errors()->add("lines.$index.name", "Duplicate line name in the form.");
                } else {
                    $names[] = $name;
                }
                // Check existing in DB (for update)
                if ($stationId && $name) {
                    $existing = StationLine::where('station_id', $stationId)
                        ->where('name', $name)
                        ->when(isset($line['id']), function ($q) use ($line) {
                            $q->where('id', '!=', $line['id']);
                        })
                        ->exists();
                    if ($existing) {
                        $validator->errors()->add("lines.$index.name", "Line name already exists for this station.");
                    }
                }
            }
        });
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
            'lines.*.name.string' => ' names must be strings.',
            'lines.*.name.max' => 'names may not be greater than 100 characters.',
        ];
    }
}
