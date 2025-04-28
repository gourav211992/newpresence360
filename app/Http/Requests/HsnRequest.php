<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;

class HsnRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected $organization_id;
    protected $group_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }

    public function rules()
    {
        $hsnId = $this->route('id');
        
        return [
            'type' => 'required|string|max:255',
            'code' => [
                    'required',
                    'string',
                    'max:255',
                    $hsnId 
                        ? Rule::unique('erp_hsns', 'code')
                            ->where('group_id', $this->group_id)
                            ->whereNull('deleted_at')
                            ->ignore($hsnId)  
                        : Rule::unique('erp_hsns', 'code')
                            ->where('group_id', $this->group_id)
                            ->whereNull('deleted_at'),
                ],
            'description' => 'nullable|string',
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable|exists:organizations,id', 
            'status' => 'required|in:active,inactive',
            'tax_patterns.*.id' => 'nullable|integer|exists:erp_hsn_tax_patterns,id',
            'tax_patterns.*.from_price' => 'required|numeric|min:0',
            'tax_patterns.*.upto_price' => 'required|numeric|gte:tax_patterns.*.from_price',
            'tax_patterns.*.from_date' => 'required|date',
            'tax_patterns.*.tax_group_id' => [
                'required',
                'exists:erp_taxes,id',
            ],
        ];
        
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $taxPatterns = $this->input('tax_patterns');
            $groups = [];
    
            foreach ($taxPatterns as $index => $pattern) {
                $taxGroupId = $pattern['tax_group_id'] ?? null;
    
                if (isset($pattern['from_price']) && isset($pattern['upto_price']) && $taxGroupId) {
                    $from = $pattern['from_price'];
                    $upto = $pattern['upto_price'];
                    if (!isset($groups[$taxGroupId])) {
                        $groups[$taxGroupId] = [];
                    }
    
                    foreach ($groups[$taxGroupId] as $range) {
                        if ($from <= $range['upto'] && $upto >= $range['from']) {
                            $validator->errors()->add("tax_patterns.$index.tax_group_id", 'The price ranges overlap within the same tax group.');
                        }
                    }
    
                    $groups[$taxGroupId][] = ['from' => $from, 'upto' => $upto];
                }
            }
        });
    }
    
    public function messages()
    {
        return [
            'type.required' => 'The HSN type is required.',
            'type.string' => 'The HSN type must be a valid string.',
            'type.max' => 'The HSN type may not be longer than 255 characters.',
            'code.required' => 'The HSN code is required.',
            'code.string' => 'The HSN code must be a valid string.',
            'code.max' => 'The HSN code may not be longer than 255 characters.',
            'code.unique' => 'The HSN code has already been taken for this organization.',
            'description.string' => 'The description must be a valid string.',
            'group_id.exists' => 'The selected group is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'tax_patterns.*.from_price.required' => 'The From Price field is required for each tax pattern.',
            'tax_patterns.*.from_price.numeric' => 'The From Price must be a numeric value.',
            'tax_patterns.*.from_price.min' => 'The From Price must be at least 0.',
            'tax_patterns.*.upto_price.required' => 'The Upto Price field is required for each tax pattern.',
            'tax_patterns.*.upto_price.numeric' => 'The Upto Price must be a numeric value.',
            'tax_patterns.*.upto_price.gte' => 'The Upto Price must be greater than or equal to the "From Price".',
            'tax_patterns.*.tax_group_id.required' => 'The Tax Group field is required for each tax pattern.',
            'tax_patterns.*.tax_group_id.exists' => 'The selected Tax Group is invalid.',
            'tax_patterns.*.tax_group_id' => 'The price ranges overlap within the same tax group.',
           
        ];
    }
}
