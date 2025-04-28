<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'nullable|exists:organizations,id',
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id',
            'from_currency_id' => 'required|exists:mysql_master.currency,id',
            'upto_currency_id' => [
                'required',
                'exists:mysql_master.currency,id',
                'different:from_currency_id',
                Rule::unique('erp_currency_exchanges')->where(function ($query) {
                    return $query->where('from_currency_id', $this->from_currency_id)
                                 ->where('upto_currency_id', $this->upto_currency_id)
                                 ->where('from_date', $this->from_date)
                                 ->where('organization_id', $this->organization_id)
                                 ->whereNull('deleted_at');
                })->ignore($this->route('exchangeId')), 
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
