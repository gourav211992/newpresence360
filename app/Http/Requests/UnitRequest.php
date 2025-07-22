<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('id'); 
        $uniqueRule = Rule::unique('erp_units', 'name')
        ->ignore($unitId)
        ->whereNull('deleted_at');

        if ($this->group_id !== null) {
            $uniqueRule->where('group_id', $this->group_id);
        }

        if ($this->company_id !== null) {
            $companyId = $this->company_id; 
            $uniqueRule->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhereNull('company_id');
            });
        }

        if ($this->organization_id !== null) {
            $orgId = $this->organization_id;
            $uniqueRule->where(function ($query) use ($orgId) {
                $query->where('organization_id', $orgId)
                    ->orWhereNull('organization_id');
            });
        }
        return [
           'name' => [
                'required',
                'string',
                'max:50',
               $uniqueRule,     
            ],
            'description' => [
                'required',
                'string',
                'max:100',
            ],
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable|exists:organizations,id', 
            'status' => 'nullable',
        ];
    }
  
    
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 50 characters.',
            'description.required' => 'The description field is required.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 100 characters.',
            'group_id.exists' => 'The selected group does not exist.',
            'organization_id.exists' => 'The selected organization does not exist.',
            'status.required' => 'The status field is required.',
        ];
    }
}
