<?php

namespace App\Http\Requests;

use App\Models\ErpItemAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Str;

class ErpRateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rate_contract_id' => 'nullable|numeric|integer',
            
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => [
                'required',
                Rule::unique('erp_rate_contracts', 'document_number')
                    ->ignore($this->rate_contract_id, 'id')
                    ->where('book_id', $this->input('book_id')), // Fixed direct variable access
            ],
            
            'document_date' => 'required|date',
            'reference_no' => 'nullable|string',

            // Either vendor or customer pair is required
            'vendor_id' => 'nullable|numeric|integer|exists:erp_vendors,id',
            'vendor_code' => 'required_with:vendor_id|nullable|string|max:50',

            'customer_id' => 'nullable|numeric|integer|exists:erp_customers,id',
            'customer_code' => 'required_with:customer_id|nullable|string|max:50',

            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            'item_id' => 'required|array',
            'item_id.*' => 'required|numeric|integer',

            'MOQ'=> 'array',
            'MOQ.*' => 'numeric',
            
            'from_item_qty' => 'required|array',
            'from_item_qty.*' => 'required|numeric',
    
            'to_item_qty' => 'required|array',
            'to_item_qty.*' => 'required|numeric',

            'item_rate' => 'required|array',
            'item_rate.*' => 'required|numeric|min:0.01',

            'item_lead' => 'array',
            'item_lead.*' => 'numeric',
            
            'tnc' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    // Remove HTML tags
                    $plainText = strip_tags($value);

                    // Decode HTML entities (&nbsp;, &amp;, etc.)
                    $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    // Remove extra whitespace/newlines
                    $plainText = preg_replace('/\s+/', ' ', trim($plainText));

                    // Count visible characters
                    if (Str::length($plainText) > 1000) {
                        $fail('The ' . $attribute . ' may not exceed 1000 visible characters.');
                    }
                },
            ],


            'effective_from' => 'required|array',
            'effective_from.*' => 'required|date',
            
            'effective_to' => 'nullable|array',
            'effective_to.*' => 'nullable|date|after_or_equal:effective_from.*',

            'currency_id' => 'required|numeric|integer',

            'item_currency_id' => 'required|array',
            'item_currency_id.*' => 'required|numeric|integer',

            'organization_id' => 'required|array',
            'organization_id.*' => 'required|numeric|integer',

            'final_remarks' => 'nullable|string|max:255'
        ];
    }
    
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $itemIds = $this->input('item_id', []);
            $uomIds = $this->input('uom_id', []);
            $fromItemQty = $this->input('from_item_qty', []);
            $toItemQty = $this->input('to_item_qty', []);
            $effectiveFrom = $this->input('effective_from', []);
            $effectiveTo = $this->input('effective_to', []);
            $moqs = $this->input('MOQ', []);
            $start_date = $this->input('start_date');
            $end_date = $this->input('end_date');
            $itemAttributesCombination = [];
            $vendorId   = $this->input('vendor_id');
            $customerId = $this->input('customer_id');
            if (empty($vendorId) && empty($customerId)) {
                $validator->errors()->add('vendor_customer', 'Please select either a vendor or a customer.');
            }
            foreach ($itemIds as $itemKey => $itemId) {
                $attributes = [];
                $requestAttributesForHelper = json_decode($this->item_attributes[$itemKey], true);
                if ($requestAttributesForHelper) {
                    foreach ($requestAttributesForHelper as $attribute) {
                        $selectedAttributeValue = null;
                        foreach ($attribute['values_data'] as $valData) {
                            if ($valData['selected'] == 'true') {
                                $selectedAttributeValue = $valData['id'];
                                break;
                            }
                        }
                        array_push($attributes, [
                            'attribute_id' => $attribute['id'],
                            'attribute_value' => $selectedAttributeValue
                        ]);
                    }
                }

                foreach ($requestAttributesForHelper as $attributeIndex => $attribute) {
                    foreach ($attribute['values_data'] as $valData) {
                        if ($valData['selected'] == 'true') {
                            $itemAttributesCombination[$itemKey]['attributes'] = ($attributeIndex == 0 ? '' : ',') . $attribute['id'] . ":" . $valData['id'];
                            $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                            $itemAttributesCombination[$itemKey]['uom_id'] = $uomIds[$itemKey] ?? null;
                            $itemAttributesCombination[$itemKey]['from_item_qty'] = $fromItemQty[$itemKey] ?? null;
                            $itemAttributesCombination[$itemKey]['to_item_qty'] = $toItemQty[$itemKey] ?? null;
                            $itemAttributesCombination[$itemKey]['effective_from'] = $effectiveFrom[$itemKey] ?? null;
                            $itemAttributesCombination[$itemKey]['effective_to'] = $effectiveTo[$itemKey] ?? null;
                            $itemAttributesCombination[$itemKey]['MOQ'] = $moqs[$itemKey] ?? null;
                        }
                    }
                }

                $itemAttributesCombination[$itemKey]['attributes'] ??= '';
                $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                $itemAttributesCombination[$itemKey]['uom_id'] = $uomIds[$itemKey] ?? null;
                $itemAttributesCombination[$itemKey]['from_item_qty'] = $fromItemQty[$itemKey] ?? null;
                $itemAttributesCombination[$itemKey]['to_item_qty'] = $toItemQty[$itemKey] ?? null;
                $itemAttributesCombination[$itemKey]['effective_from'] = $effectiveFrom[$itemKey] ?? null;
                $itemAttributesCombination[$itemKey]['effective_to'] = $effectiveTo[$itemKey] ?? null;
                $itemAttributesCombination[$itemKey]['MOQ'] = $moqs[$itemKey] ?? null;

                if ($moqs[$itemKey] > $toItemQty[$itemKey] && $toItemQty[$itemKey] > 0) {
                    $validator->errors()->add("to_item_qty.{$itemKey}", "MOQ > To quantity.");
                }

                if ($effectiveFrom[$itemKey] < $start_date || ($end_date && $effectiveFrom[$itemKey] > $end_date)) {
                    $validator->errors()->add("effective_from.{$itemKey}", "date must be within start date and end date.");
                }

                if ($effectiveTo[$itemKey] && ($effectiveTo[$itemKey] < $start_date || ($end_date && $effectiveTo[$itemKey] > $end_date))) {
                    $validator->errors()->add("effective_to.{$itemKey}", "date must be within start date and end date.");
                }

                foreach ($itemAttributesCombination as $otherKey => $otherItem) {
                    if ($itemKey !== $otherKey &&
                        $itemId == $otherItem['item_id'] &&
                        $itemAttributesCombination[$itemKey]['attributes'] == $otherItem['attributes'] &&
                        $uomIds[$itemKey] == $otherItem['uom_id']
                    ) {
                        if ($moqs[$itemKey] != $otherItem['MOQ']) {
                            $validator->errors()->add("MOQ.{$itemKey}", "MOQ must be same.");
                        }
                        // if ($moqs[$itemKey] != $otherItem['MOQ']) {
                        //     $validator->errors()->add("MOQ.{$itemKey}", "MOQ must be same.");
                        // }

                        $currentFromQty = $fromItemQty[$itemKey];
                        $currentToQty = $toItemQty[$itemKey];
                        $otherFromQty = $otherItem['from_item_qty'];
                        $otherToQty = $otherItem['to_item_qty'];

                        $currentEffectiveFrom = $effectiveFrom[$itemKey];
                        $currentEffectiveTo = $effectiveTo[$itemKey];
                        $otherEffectiveFrom = $otherItem['effective_from'];
                        $otherEffectiveTo = $otherItem['effective_to'];

                        // Check if quantity ranges overlap
                        if (
                            ($currentFromQty >= $otherFromQty && $currentFromQty <= $otherToQty) ||
                            ($currentToQty >= $otherFromQty && $currentToQty <= $otherToQty) ||
                            ($otherFromQty >= $currentFromQty && $otherFromQty <= $currentToQty) ||
                            ($otherToQty >= $currentFromQty && $otherToQty <= $currentToQty)
                        ) {
                            // If quantity ranges overlap, ensure effective dates do not overlap
                            if (
                                ($currentEffectiveFrom <= $otherEffectiveTo && $currentEffectiveFrom >= $otherEffectiveFrom) ||
                                ($currentEffectiveTo <= $otherEffectiveTo && $currentEffectiveTo >= $otherEffectiveFrom) ||
                                ($otherEffectiveFrom <= $currentEffectiveTo && $otherEffectiveFrom >= $currentEffectiveFrom) ||
                                ($otherEffectiveTo <= $currentEffectiveTo && $otherEffectiveTo >= $currentEffectiveFrom)
                            ) {
                                if (
                                    ($currentFromQty > $otherFromQty && $currentFromQty < $otherToQty) ||
                                    ($otherFromQty > $currentFromQty && $otherFromQty < $currentToQty)
                                ) {
                                    $validator->errors()->add("from_item_qty.{$itemKey}", "Overlapping quantity.");
                                }
                                if (
                                    ($currentToQty >= $otherFromQty && $currentToQty <= $otherToQty) ||
                                    ($otherToQty >= $currentFromQty && $otherToQty <= $currentToQty)
                                ) {
                                    $validator->errors()->add("to_item_qty.{$itemKey}", "Overlapping  quantity.");
                                }
                                if (
                                    ($currentEffectiveFrom <= $otherEffectiveTo && $currentEffectiveFrom >= $otherEffectiveFrom) ||
                                    ($currentEffectiveTo <= $otherEffectiveTo && $currentEffectiveTo >= $otherEffectiveFrom)
                                ) {
                                    $validator->errors()->add("effective_from.{$itemKey}", "Overlapping date.");
                                }
                            }
                        } else {
                            // Check if only effective dates overlap
                            if (
                                ($currentEffectiveFrom <= $otherEffectiveTo && $currentEffectiveFrom >= $otherEffectiveFrom) ||
                                ($currentEffectiveTo <= $otherEffectiveTo && $currentEffectiveTo >= $otherEffectiveFrom) ||
                                ($otherEffectiveFrom <= $currentEffectiveTo && $otherEffectiveFrom >= $currentEffectiveFrom) ||
                                ($otherEffectiveTo <= $currentEffectiveTo && $otherEffectiveTo >= $currentEffectiveFrom)
                            ) {
                                if (
                                    ($currentEffectiveFrom <= $otherEffectiveTo && $currentEffectiveFrom >= $otherEffectiveFrom)
                                ) {
                                    $validator->errors()->add("effective_from.{$itemKey}", "Overlapping date.");
                                }
                                if (
                                    ($currentEffectiveTo <= $otherEffectiveTo && $currentEffectiveTo >= $otherEffectiveFrom)
                                ) {
                                    $validator->errors()->add("effective_to.{$itemKey}", "Overlapping date.");
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}
