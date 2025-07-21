<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class CurrencyExchangeRateRequest extends FormRequest
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

        $exchangeId = $this->route('exchangeId');
        
        $uniqueRateRule = Rule::unique('erp_currency_exchanges')
            ->ignore($exchangeId)
            ->whereNull('deleted_at')
            ->where('from_currency_id', $this->from_currency_id)
            ->where('upto_currency_id', $this->upto_currency_id)
            ->where('from_date', $this->from_date);

        if ($this->group_id !== null) {
            $uniqueRateRule->where('group_id', $this->group_id);
        }

        if ($this->company_id !== null) {
            $uniqueRateRule->where(function ($query) {
                $query->where('company_id', $this->company_id)
                    ->orWhereNull('company_id');
            });
        }

        if ($this->organization_id !== null) {
            $uniqueRateRule->where(function ($query) {
                $query->where('organization_id', $this->organization_id)
                    ->orWhereNull('organization_id');
            });
        }

        return [
            'organization_id' => 'nullable|exists:organizations,id',
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id',
            'from_currency_id' => 'required|exists:mysql_master.currency,id',
            'upto_currency_id' => [
                'required',
                'exists:mysql_master.currency,id',
                'different:from_currency_id',
                $uniqueRateRule
            ],
            'from_date' => 'required|date',
            'exchange_rate' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.exists' => 'The selected organization is invalid.',
            'group_id.exists' => 'The selected group is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'from_currency_id.required' => 'The from currency ID is required.',
            'from_currency_id.exists' => 'The selected from currency ID is invalid.',
            'upto_currency_id.required' => 'The to currency ID is required.',
            'upto_currency_id.exists' => 'The selected to currency ID is invalid.',
            'upto_currency_id.different' => 'The upto currency must be different from the from currency.',
            'upto_currency_id.unique' => 'The combination of from currency, upto currency, and date already exists.',
            'from_date.required' => 'The from date is required.',
            'from_date.date' => 'The from date must be a valid date.',
            'exchange_rate.required' => 'The exchange rate is required.',
            'exchange_rate.numeric' => 'The exchange rate must be a valid number.',
            'exchange_rate.min' => 'The exchange rate must be at least 0.',
            'exchange_rate.regex' => 'The exchange rate must be a valid number with up to two decimal places.',
        ];
    }
}
