<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class TermsAndConditionRequest extends FormRequest
{
    public function authorize()
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

    public function rules()
    {
         $termId = $this->route('id');
         $uniqueRule = Rule::unique('erp_terms_and_conditions', 'term_name')
            ->ignore($termId)
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
           'term_name' => [
                'required',
                'string',
                'max:255',
                $uniqueRule
            ],
            'term_detail' => 'required|string|min:10', 
            'status' => 'required|in:active,inactive', 
        ];
    }

    public function messages()
    {
        return [
            'term_name.required' => 'Term Name is required.',
            'term_name.string' => 'Term Name must be a string.',
            'term_name.max' => 'Term Name may not be greater than 255 characters.',
            'term_name.unique' => 'The Term Name has already been taken.',

            'term_detail.required' => 'Term Detail is required.',
            'term_detail.string' => 'Term Detail must be a string.',
            'term_detail.min' => 'Term Detail must be at least 10 characters.', 

            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
