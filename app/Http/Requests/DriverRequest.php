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
    $id = $this->route('id'); // Will be null for create, set for update

    return [
        'user_id'        => 'required|exists:employees,id',
        'name'           => 'required|string|max:255',
        'email'          => 'nullable|email|max:255|unique:erp_drivers,email,' . $id,
        'mobile_no'      => 'required|string|max:20|unique:erp_drivers,mobile_no,' . $id,
        'experience_years' => 'required|integer|min:0',
        'license_no'     => 'required|string|max:100|unique:erp_drivers,license_no,' . $id,
        'license_expiry_date' => 'required|date|after:today',

        // File validation only required if creating
        'license_front'     => $id ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'license_back'      => $id ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'id_proof_front'    => $id ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'id_proof_back'     => $id ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ];
}


    public function messages()
    {
        return [
            'user_id.required' => 'Employee is required.',
            'user_id.exists' => 'Selected employee does not exist.',
            'name.required' => 'Driver name is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already used.',
            'mobile_no.required' => 'Mobile number is required.',
            'mobile_no.unique' => 'This mobile number is already used.',
            'experience_years.required' => 'Experience is required.',
            'experience_years.integer' => 'Experience must be an integer.',
            'license_no.required' => 'License number is required.',
            'license_no.unique' => 'This license number is already used.',
            'license_expiry_date.required' => 'License expiry date is required.',
            'license_expiry_date.after' => 'License expiry must be a future date.',
            'license_front.required' => 'License front media is required.',
            'license_front.exists' => 'License front file not found.',
            'license_back.required' => 'License back media is required.',
            'license_back.exists' => 'License back file not found.',
            'id_proof_front.required' => 'ID proof front is required.',
            'id_proof_front.exists' => 'ID proof front file not found.',
            'id_proof_back.required' => 'ID proof back is required.',
            'id_proof_back.exists' => 'ID proof back file not found.',
        ];
    }

}
