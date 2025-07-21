<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;


class BankRequest extends FormRequest
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
        $bankId = $this->route('id');
        $uniqueBankCodeRule = Rule::unique('erp_banks', 'bank_code')
        ->ignore($bankId)
        ->whereNull('deleted_at');

        if ($this->group_id !== null) {
            $uniqueBankCodeRule->where('group_id', $this->group_id);
        }

        if ($this->company_id !== null) {
            $uniqueBankCodeRule->where(function ($query) {
                $query->where('company_id', $this->company_id)
                    ->orWhereNull('company_id');
            });
        }

        if ($this->organization_id !== null) {
            $uniqueBankCodeRule->where(function ($query) {
                $query->where('organization_id', $this->organization_id)
                    ->orWhereNull('organization_id');
            });
        }
        return [
            'bank_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/', 
            ],
            'bank_code' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Z0-9]+$/',
                $uniqueBankCodeRule,
            ],
            'ledger_id' => 'nullable|exists:erp_ledgers,id',
            'ledger_group_id' => 'nullable|exists:erp_groups,id',

            'company_id' => 'nullable',
            'organization_id' => 'nullable|exists:organizations,id',
            'group_id' => 'nullable|exists:groups,id',
            'status' => 'nullable|in:active,inactive', 
    
            'bank_details' => 'required|array',
            'bank_details.*.id' => 'nullable|integer|exists:erp_bank_details,id',
            'bank_details.*.account_number' => [
                'required',
                'string',
                'regex:/^[\d\s-]{1,255}$/',
            ],
            'bank_details.*.branch_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
            ],
           'bank_details.*.branch_address' => 'required|string|max:255',
            'bank_details.*.ifsc_code' => [
                'required',
                'string',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            ],
            'bank_details.*.ledger_id' => 'nullable|exists:erp_ledgers,id',
            'bank_details.*.ledger_group_id' => 'nullable|exists:erp_groups,id',
            
        ];
    }
    

    public function messages(): array
    {
        return [
            // Bank Name
            'bank_name.required' => 'Please enter the bank name.',
            'bank_name.string' => 'The bank name must consist of letters and spaces only.',
            'bank_name.max' => 'The bank name should not exceed 100 characters.',
            'bank_name.regex' => 'The bank name should contain only letters and spaces.',

            // Bank Code
            'bank_code.string' => 'The bank code must be a valid string.',
            'bank_code.max' => 'The bank code cannot exceed 255 characters.',
            'bank_code.regex' => 'The bank code must contain only uppercase letters and numbers.',
            'bank_code.unique' => 'This bank code already exists.',

            // Company, Organization, and Group
            'company_id.exists' => 'The selected company is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'group_id.exists' => 'The selected group is invalid.',

            // Status
            'status.in' => 'The status must be either "active" or "inactive".',

            // Bank Details
            'bank_details.required' => 'Please provide bank details.',

            // Account Number
            'bank_details.*.account_number.required' => 'The account number is required.',
            'bank_details.*.account_number.string' => 'The account number must be a valid string.',
            'bank_details.*.account_number.regex' => 'The account number should only include numbers, spaces, and hyphens.',

            // Branch Name
            'bank_details.*.branch_name.string' => 'The branch name must be a valid string.',
            'bank_details.*.branch_name.max' => 'The branch name should not exceed 255 characters.',
            'bank_details.*.branch_name.regex' => 'The branch name should contain only letters and spaces.',

            // Branch Address
            'bank_details.*.branch_address.required' => 'The branch address is required.',
            'bank_details.*.branch_address.string' => 'The branch address must be a valid string.',
            'bank_details.*.branch_address.max' => 'The branch address cannot exceed 255 characters.',

            // IFSC Code
            'bank_details.*.ifsc_code.required' => 'The IFSC code is required.',
            'bank_details.*.ifsc_code.string' => 'The IFSC code must be a valid string.',
            'bank_details.*.ifsc_code.regex' => 'The IFSC code must be in the format XXXX0XXXXXX.',
        ];
    }
}
