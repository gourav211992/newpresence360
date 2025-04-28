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

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }

    public function rules()
    {
        return [
           'term_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_terms_and_conditions', 'term_name')
                    ->ignore($this->route('id')) 
                    ->where('group_id', $this->group_id)
                    ->whereNull('deleted_at') 
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
