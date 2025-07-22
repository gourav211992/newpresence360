<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class DiscountMasterRequest extends FormRequest
{
    protected $organization_id;
    protected $company_id;
    protected $group_id;

    public function authorize(): bool
    {
        return true;
    }

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

        $discountId = $this->route('id');
            $uniqueScope = function ($query) {
            if ($this->group_id !== null) {
                $query->where('group_id', $this->group_id);
            }

            if ($this->company_id !== null) {
                $query->where(function ($q) {
                    $q->where('company_id', $this->company_id)
                      ->orWhereNull('company_id');
                });
            }

            if ($this->organization_id !== null) {
                $query->where(function ($q) {
                    $q->where('organization_id', $this->organization_id)
                      ->orWhereNull('organization_id');
                });
            }
        };

        return [
           'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('erp_discount_master', 'name')
                    ->ignore($discountId)
                    ->whereNull('deleted_at')
                    ->where($uniqueScope),
            ],
            'alias' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('erp_discount_master', 'alias')
                    ->ignore($discountId)
                    ->whereNull('deleted_at')
                    ->where($uniqueScope),
            ],
            'percentage' => [
                'required',
                'numeric',
                'min:0', 
                'max:100',
            ],
            'discount_ledger_id' => [
                'nullable',
                'exists:erp_ledgers,id',
            ],
            'discount_ledger_group_id' => [
                'nullable',
                'exists:erp_groups,id',
            ],

            'is_purchase' => 'nullable|boolean',
            'is_sale' => 'nullable|boolean',
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable|exists:organizations,id', 
            'status' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The discount name should not exceed 100 characters.',
            'name.regex' => 'The discount name should only contain letters and spaces.',
            'alias.required' => 'The alias name is required.',
            'alias.string' => 'The alias must be a valid string.',
            'alias.max' => 'The alias should not exceed 100 characters.',
            'alias.regex' => 'The alias should contain only alphanumeric characters and spaces.',
            'alias.unique' => 'This alias already exists.',
            'percentage.numeric' => 'The discount percentage must be a valid number.',
            'percentage.min' => 'The discount percentage cannot be negative.',
            'percentage.max' => 'The discount percentage cannot exceed 100.',
            'percentage.required' => 'The percentage is required.',
            'discount_ledger_id.exists' => 'The selected ledger ID is invalid.',
            'is_purchase.boolean' => 'The purchase flag must be true or false.',
            'is_sale.boolean' => 'The sale flag must be true or false.',
            'organization_id.exists' => 'The selected organization is invalid.',
        ];
    }
}
