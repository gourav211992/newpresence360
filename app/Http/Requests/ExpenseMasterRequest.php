<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use App\Models\Ledger;
use Auth;

class ExpenseMasterRequest extends FormRequest
{
    protected $organization_id;

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
    }

    public function rules(): array
    {
        $expenseId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/', 
                Rule::unique('erp_expense_master', 'name')
                    ->ignore($expenseId)
                    ->where('group_id', $this->group_id)
                    ->whereNull('deleted_at'), 
            ],
            'alias' => [
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9\s]+$/', 
                Rule::unique('erp_expense_master', 'alias')
                    ->ignore($expenseId)
                    ->where('group_id', $this->group_id)
                    ->whereNull('deleted_at'), 
            ],
            'percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'expense_ledger_id' => [
                'nullable',
                'exists:erp_ledgers,id',
            ],
            'expense_ledger_group_id' => [
                'nullable',
                'exists:erp_groups,id',
            ],
          
            'service_provider_ledger_id' => [
                'nullable',
                'exists:erp_ledgers,id',
                function ($attribute, $value, $fail) {
                    $expenseLedgerId = $this->input('expense_ledger_id');
                    $expenseLedger = Ledger::find($expenseLedgerId);
                    $serviceProviderLedger = Ledger::find($value);
                    if ($expenseLedger && $serviceProviderLedger && $expenseLedger->name === $serviceProviderLedger->name) {
                        $fail('The ledger and service provider must have different names.');
                    }
                },
            ],
        
            'service_provider_ledger_group_id' => [
                'nullable',
                'exists:erp_groups,id',
            ],
            'is_purchase' => 'required|boolean',
            'is_sale' => 'required|boolean',
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable',
            'organization_id' => 'nullable|exists:organizations,id',
            'status' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name should not exceed 100 characters.',
            'name.regex' => 'The name should only contain alphanumeric and spaces.',
            'alias.required' => 'The alias name is required.',
            'alias.string' => 'The alias must be a valid string.',
            'alias.max' => 'The alias should not exceed 100 characters.',
            'alias.regex' => 'The alias should contain only alphanumeric characters and spaces.',
            'alias.unique' => 'This alias already exists within your organization.',
            'percentage.numeric' => 'The percentage must be a valid number.',
            'percentage.min' => 'The percentage cannot be negative.',
            'percentage.max' => 'The percentage cannot exceed 100.',
            'percentage.required' => 'The percentage is required.',
            'expense_ledger_id.exists' => 'The selected expense ledger is invalid.',
            'service_provider_ledger_id.exists' => 'The selected service provider ledger is invalid.',
            'is_purchase.boolean' => 'The purchase flag must be true or false.',
            'is_sale.boolean' => 'The sale flag must be true or false.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'group_id.exists' => 'The selected group is invalid.',
        ];
    }
}
