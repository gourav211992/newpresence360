<?php
namespace App\Http\Requests;

use Auth;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductionRouteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected $organization_id;
    protected $group_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('erp_production_routes', 'name')->where('group_id',  $this->group_id)->where('organization_id',  $this->organization_id)->whereNull('deleted_at')->ignore($this->route('id'))],
            'description' => ['nullable', 'string', 'max:500'],
            'safety_buffer_perc' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string',
            'levels' => 'nullable|array',
            'levels.*.level' => 'required|integer',
            'levels.*.name' => 'required|string|max:100',
            'levels.*.details' => 'required|array',
            'levels.*.details.*.consumption' => 'nullable|in:yes,no',
            'levels.*.details.*.qa' => 'nullable|in:yes,no',
            'levels.*.details.*.hidden_station_id' => 'required|integer|exists:erp_stations,id',
        ];
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('erp_production_routes', 'name')->whereNull('deleted_at')->ignore($this->route('id')),
            ];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name may not be greater than 100 characters.',
            'description.max' => 'Description may not be greater than 500 characters.',
            'safety_buffer_perc.max' => 'Safety Buffer Percentage may not be greater than 100.',
            'levels.*.details.required' => 'Station for each level is required.',
            // 'levels.required' => 'Levels for this production route required.',
            // 'levels.*.details.*.consumption.required' => 'Consumption is required.',
            'levels.*.details.*.hidden_station_id.required' => 'Station is required.',
        ];
    }
}
