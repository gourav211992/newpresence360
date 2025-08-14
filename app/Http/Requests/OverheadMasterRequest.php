<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;

class OverheadMasterRequest extends FormRequest
{
    protected $organization_id;
    public function authorize(): bool
    {
        return true;
    }
    // protected function prepareForValidation()
    // {
        // $user = Helper::getAuthenticatedUser();
        // $organization = $user?->organization;
        // $organizationId = $organization?->id ?? null;
        // $groupId = $organization?->group_id ?? null;
        // $companyId = $organization?->company_id ?? null;
    // }
    public function rules(): array
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user?->organization;
        $organizationId = $organization?->id ?? null;
        $groupId = $organization?->group_id ?? null;
        $companyId = $organization?->company_id ?? null;
        $discountId = $this->route('id');
        return [
           'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                Rule::unique('erp_overheads', 'name')
                    ->ignore($discountId)
                    ->where('group_id', $groupId)
                    ->whereNull('deleted_at'), 
            ],
            'alias' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                Rule::unique('erp_overheads', 'alias')
                    ->ignore($discountId)
                    ->where('group_id', $groupId)
                    ->whereNull('deleted_at'), 
            ],
            'perc' => [
                // 'required',
                // 'numeric',
                'nullable',
                'min:0', 
                'max:100',
            ],
            'ledger_id' => [
                'nullable',
                'exists:erp_ledgers,id',
            ],
            'ledger_group_id' => [
                'nullable',
                'exists:erp_groups,id',
            ],
            'status' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The overhead name should not exceed 100 characters.',
            'name.regex' => 'The overhead name should only contain letters and spaces.',
            'alias.required' => 'The alias name is required.',
            'alias.string' => 'The alias must be a valid string.',
            'alias.max' => 'The alias should not exceed 100 characters.',
            'alias.regex' => 'The alias should contain only alphanumeric characters and spaces.',
            'alias.unique' => 'This alias already exists.',
            'perc.numeric' => 'The overhead percentage must be a valid number.',
            'perc.min' => 'The overhead percentage cannot be negative.',
            'perc.max' => 'The overhead percentage cannot exceed 100.',
            'perc.required' => 'The percentage is required.',
            'ledger_id.exists' => 'The selected ledger ID is invalid.'
        ];
    }
}