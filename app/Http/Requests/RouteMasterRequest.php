<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\ErpRouteMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

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
public function withValidator(Validator $validator)
{
    $validator->after(function ($validator) {
        $routeMasters = $this->input('route_master', []);
        $nameMap = [];

        foreach ($routeMasters as $index => $route) {
            $originalName = $route['name'] ?? '';
            $normalized = strtolower(trim($originalName));

            if ($normalized === '') {
                continue;
            }

            // Group same names together by their normalized value
            $nameMap[$normalized][] = $index;
        }

        foreach ($nameMap as $normalizedName => $indexes) {
            $firstIndex = $indexes[0];
            $firstRoute = $routeMasters[$firstIndex];
            $firstName = $firstRoute['name'];
            $rowId = $firstRoute['id'] ?? null;

            $existsInDb = ErpRouteMaster::whereRaw('LOWER(name) = ?', [$normalizedName])
                ->whereNull('deleted_at')
                ->when($rowId, fn($q) => $q->where('id', '!=', $rowId)) // ignore current row when updating
                ->exists();

            if ($existsInDb) {
                $validator->errors()->add(
                    "route_master.{$firstIndex}.name",
                    "The route master  '{$firstName}' name already exists in the database."
                );
            }

            // ✅ Check for duplicate in the same request payload
            if (count($indexes) > 1) {
                foreach (array_slice($indexes, 1) as $dupIndex) {
                    $dupName = $routeMasters[$dupIndex]['name'];
                    $validator->errors()->add(
                        "route_master.{$dupIndex}.name",
                        "The route master  '{$dupName}' name is already exists in the database."
                    );
                }
            }
        }
    });
}





}
