<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Auth;


class PaymentTermRequest extends FormRequest
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
        $paymentTermId = $this->route('id');

          $uniqueRule = Rule::unique('erp_payment_terms', 'name')
            ->ignore($paymentTermId)
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
                'max:100',
                $uniqueRule, 
            ],

            'alias' => [
                'nullable',
                'string',
                'max:50',
            ],
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'status' => 'nullable|in:active,inactive',
            'term_details' => 'required|array',
            'term_details.*.id' => 'nullable|integer|exists:erp_payment_term_details,id',
            'term_details.*.installation_no' => 'nullable|string|max:255',
            'term_details.*.percent' => [
                'required',
                'numeric',
                'between:0,100',
            ],
            'term_details.*.term_days' => 'nullable|integer|min:0|max:365',
            'term_details.*.trigger_type' => [
                'nullable',
                Rule::in(ConstantHelper::TRIGGER_TYPES),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The payment term name is required.',
            'name.string' => 'The payment term name must be a string.',
            'name.max' => 'The payment term name may not exceed 100 characters.',
            'name.unique' => 'The payment term name has already been taken.',
            'alias.string' => 'The alias must be a string.',
            'alias.max' => 'The alias may not exceed 50 characters.',
            'group_id.exists' => 'The selected group is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'term_details.required' => 'The term details are required.',
            'term_details.array' => 'The term details must be an array.',
            'term_details.*.installation_no.string' => 'The installation number must be a string.',
            'term_details.*.installation_no.max' => 'The installation number may not exceed 255 characters.',
            'term_details.*.percent.required' => 'The percent value is required.',
            'term_details.*.percent.numeric' => 'The percent value must be a number.',
            'term_details.*.percent.between' => 'The percent value must be between 0 and 100.',
            'term_details.*.term_days.integer' => 'The term days must be an integer.',
            'term_details.*.term_days.min' => 'The term days must be at least 0.',
            'term_details.*.trigger_type.in' => 'The selected trigger type is invalid.',
            'term_details.*.term_days.max' => 'The term days may not be greater than 365.',
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $termDetails = $this->input('term_details');
    
            if ($termDetails) {
                $totalPercent = 0;
    
                foreach ($termDetails as $termDetail) {
                    $percent = $termDetail['percent'] ?? 0;
                    if (is_numeric($percent) && $percent >= 0 && $percent <= 100) {
                        $totalPercent += $percent;
                    }
                }
                if (intval($totalPercent) !== 100) {
                    $validator->errors()->add('term_details.0.percent', 'The total percent must equal 100. Current total is ' . $totalPercent . '.');
                }
    
            } else {
                $validator->errors()->add('term_details', 'The term details are required.');
            }
        });
    }
    
}
