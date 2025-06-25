<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class DriverRequest extends FormRequest
{
    protected $organization_id;
    protected $group_id;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->merge([
            'organization_id' => $organization?->id,
            'group_id' => $organization?->group_id,
        ]);
    }

public function rules()
{
    $id = $this->route('id'); 

    return [
        'user_id'        => 'nullable|exists:employees,id',
        'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\.\-]+$/'], 
        'experience_years' => [
            'required',
            'integer',
            'min:0',
            'max:99',
            'regex:/^\d{1,2}$/', 
        ],
        'email'          => 'nullable|email|max:100|unique:erp_drivers,email,' . $id,
      'mobile_no' => [
            'required',
            'string',
            'regex:/^\d{10}$/',
            Rule::unique('erp_drivers', 'mobile_no')->ignore($id),
        ],

        'license_no' => [
            'required',
            'string',
            'max:100',
            'regex:/^[A-Za-z0-9\-]+$/',  
            Rule::unique('erp_drivers', 'license_no')->ignore($id),
        ],

        'license_expiry_date' => 'required|date|after:today',


        'license_front' => [
        $id ? 'nullable' : 'required',
        'file',
        'mimes:jpg,jpeg,png,pdf',
        'min:10',    
        'max:2048', 
    ],
    'license_back' => [
        $id ? 'nullable' : 'required',
        'file',
        'mimes:jpg,jpeg,png,pdf',
        'min:10',
        'max:2048',
    ],
    'id_proof_front' => [
        $id ? 'nullable' : 'required',
        'file',
        'mimes:jpg,jpeg,png,pdf',
        'min:10',
        'max:2048',
    ],
    'id_proof_back' => [
        $id ? 'nullable' : 'required',
        'file',
        'mimes:jpg,jpeg,png,pdf',
        'min:10',
        'max:2048',
    ],

  ];
}


   public function messages()
{
    return [
        'user_id.nullable' => 'Employee is required.',
        'user_id.exists' => 'Selected employee does not exist.',
        
        'name.required' => 'Driver name is required.',
        'name.regex' => 'The name may only contain letters, spaces, dots, and hyphens.',
        
        'email.email' => 'Enter a valid email address.',
        'email.unique' => 'This email is already used.',
        
        'mobile_no.required' => 'Mobile number is required.',
        'mobile_no.regex' => 'Mobile number must be exactly 10 digits and contain only numbers.',
        'mobile_no.unique' => 'This mobile number is already used.',

        'experience_years.required' => 'Experience is required.',
        'experience_years.integer' => 'Experience must be an integer.',
        'experience_years.min' => 'Experience cannot be negative.',
        'experience_years.max' => 'Experience cannot exceed 99 years.',
        'experience_years.regex' => 'Experience must be a 1 or 2-digit number only.',

        'license_no.required' => 'License number is required.',
        'license_no.regex' => 'License number may only contain letters, numbers, and hyphens.',
        'license_no.unique' => 'This license number is already used.',
        
        'license_expiry_date.required' => 'License expiry date is required.',
        'license_expiry_date.after' => 'License expiry must be a future date.',

        'license_front.required' => 'License front media is required.',
        'license_front.min' => 'License front must be at least 10 KB.',
        'license_front.max' => 'License front must not exceed 2 MB.',

        'license_back.required' => 'License back media is required.',
        'license_back.min' => 'License back must be at least 10 KB.',
        'license_back.max' => 'License back must not exceed 2 MB.',

        'id_proof_front.required' => 'ID proof front is required.',
        'id_proof_front.min' => 'ID proof front must be at least 10 KB.',
        'id_proof_front.max' => 'ID proof front must not exceed 2 MB.',

        'id_proof_back.required' => 'ID proof back is required.',
        'id_proof_back.min' => 'ID proof back must be at least 10 KB.',
        'id_proof_back.max' => 'ID proof back must not exceed 2 MB.',
    ];
}


}
