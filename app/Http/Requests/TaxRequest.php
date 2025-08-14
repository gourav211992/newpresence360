<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class TaxRequest extends FormRequest
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
        $taxGroupId = $this->route('id'); 
        $taxCategory = $this->input('tax_category'); 
          // Conditional Unique Rule like in AttributeRequest
        $uniqueRule = Rule::unique('erp_taxes', 'tax_group')
            ->ignore($taxGroupId)
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
           'tax_group' => [
            'required',
            'string',
            'max:10',
            $uniqueRule,
            ],
            'description' => 'nullable|string',
            'tax_category' => 'required|string', 
            'tax_type' => $taxCategory  === 'GST' ? 'nullable' : 'required',
            'status' => 'required|string',
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable',
            'organization_id' => 'nullable|exists:organizations,id',
            'tax_details' => 'required|array',
            'tax_details.*.id' => 'nullable|integer|exists:erp_tax_details,id',
            'tax_details.*.tax_type' => 'required',
            'tax_details.*.tax_percentage' => 'required|numeric|min:0|max:100',
            'tax_details.*.place_of_supply' => $taxCategory === 'GST' ? 'required|in:Intrastate,Interstate,Overseas' : 'nullable', 
            'tax_details.*.is_purchase' => 'nullable|boolean',
            'tax_details.*.is_sale' => 'nullable|boolean',
            'tax_details.*.applicability_type' => 'required|string',
            'tax_details.*.status' => 'nullable|in:active,inactive',
            'tax_details.*.ledger_id' => 'nullable|exists:erp_ledgers,id',
            'tax_details.*.ledger_group_id' => 'nullable|exists:erp_groups,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $taxDetails = $this->input('tax_details', []);
            $taxTypePlacePairs = [];
            
            foreach ($taxDetails as $index => $taxDetail) {
                $taxType = $taxDetail['tax_type'] ?? null;
                $placeOfSupply = $taxDetail['place_of_supply'] ?? null;
                if (!empty($taxType) && !empty($placeOfSupply)) {
                if ($taxType && $placeOfSupply && $taxType === $placeOfSupply) {
                    $validator->errors()->add("tax_details.{$index}.tax_type", 'The tax type and place of supply must be different.');
                    $validator->errors()->add("tax_details.{$index}.place_of_supply", 'The tax type and place of supply must be different.');
                }

                $pair = $taxType . '|' . $placeOfSupply;
                if (isset($taxTypePlacePairs[$pair])) {
                    $validator->errors()->add("tax_details.{$index}.tax_type", 'The combination of tax type and place of supply must be unique.');
                    $validator->errors()->add("tax_details.{$index}.place_of_supply", 'The combination of tax type and place of supply must be unique.');
                } else {
                    $taxTypePlacePairs[$pair] = true;
                }
            }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tax_group.required' => 'The tax group field is required.',
            'tax_group.string' => 'The tax group must be a string.',
            'tax_group.max' => 'The tax group may not be greater than 10 characters.',
            'tax_type.required' => 'The tax type field is required.',
            'tax_category.required' => 'The tax category field is required.',
            'description.string' => 'The description must be a string.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'applicability_type.required' => 'The applicability type field is required.',
            'applicability_type.string' => 'The applicability type must be a string.',
            'group_id.exists' => 'The selected group does not exist.',
            'organization_id.exists' => 'The selected organization does not exist.',
            'tax_details.required' => 'At least one tax detail is required.',
            'tax_details.array' => 'The tax details must be an array.',
            'tax_details.*.tax_type.in' => 'The selected tax type is invalid.',
            'tax_details.*.tax_type.required' => 'The tax type is required.',
            'tax_details.*.tax_percentage.required' => 'The tax percentage field is required.',
            'tax_details.*.tax_percentage.numeric' => 'The tax percentage must be a number.',
            'tax_details.*.tax_percentage.min' => 'The tax percentage must be at least 0.',
            'tax_details.*.tax_percentage.max' => 'The tax percentage may not be greater than 100.',
            'tax_details.*.place_of_supply.required' => 'The place of supply is required.',
            'tax_details.*.place_of_supply.in' => 'The selected place of supply is invalid.',
            'tax_details.*.is_purchase.boolean' => 'The is purchase field must be a boolean.',
            'tax_details.*.is_sale.boolean' => 'The is sale field must be a boolean.',
            'tax_details.*.status.in' => 'The selected status is invalid.',
            'tax_details.*.ledger_id.exists' => 'The selected ledger does not exist.',
        ];
    }
}
