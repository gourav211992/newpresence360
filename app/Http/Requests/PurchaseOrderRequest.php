<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purchaseOrderId = $this->route('id');

        $rules = [
            'series_id' => 'required|string|max:255',
            'purchase_order_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_purchase_orders')->ignore($purchaseOrderId),
            ],
            'po_date' => 'nullable|date',
            'organization_id' => 'nullable|exists:organizations,id',
            'group_id' => 'nullable|exists:groups,id',
            'company_id' => 'nullable|exists:companies,id', 
            'vendor_id' => 'nullable|exists:erp_vendors,id',
            'billing_to' => 'nullable',
            'ship_to' => 'nullable',
            'reference_number' => 'nullable|string|max:255',
            'payment_terms_id' => 'nullable|exists:erp_payment_terms,id',
            'currency_id' => 'nullable|exists:erp_currency,id',
            'billing_address' => 'nullable|json', 
            'shipping_address' => 'nullable|json', 
            'item_remark' => 'nullable|string',
            'sub_total' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'gst' => 'nullable|numeric|min:0',
            'gst_details' => 'nullable',
            'tax_value'=>'nullable',
            'taxable_amount' => 'nullable|numeric|min:0', 
            'other_expenses' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'final_remarks' => 'nullable|string',
            'status' => 'nullable|string|in:open,closed', 

            'items' => 'required|array',
            'items.*.item_id' => 'nullable|exists:erp_items,id',
            'items.*.hsn_code' => 'nullable',
            'items.*.uom_id' => 'nullable', 
            'items.*.expected_delivery_date' => 'nullable|date',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.basic_value' => 'nullable|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.sgst_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.cgst_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.igst_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_value' => 'nullable|numeric|min:0', 
            'items.*.net_value' => 'nullable|numeric|min:0',
            'items.*.sub_total' => 'nullable|numeric|min:0',
            'items.*.selected_item' => 'nullable|boolean',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['purchase_order_no'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('erp_purchase_orders', 'purchase_order_no')->ignore($purchaseOrderId),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'status.in' => 'The selected status is invalid.',
            'items.required' => 'At least one item is required.',
            'items.array' => 'The items field must be an array.',
            'items.*.item_id.exists' => 'The selected item ID is invalid.',
            'items.*.hsn_code.exists' => 'The selected HSN code is invalid.',
            'items.*.uom_id.exists' => 'The selected UOM ID is invalid.',
            'items.*.quantity.numeric' => 'The quantity must be a number.',
            'items.*.rate.numeric' => 'The rate must be a number.',
            'items.*.basic_value.numeric' => 'The basic value must be a number.',
            'items.*.discount_percentage.numeric' => 'The discount percentage must be a number.',
            'items.*.discount_amount.numeric' => 'The discount amount must be a number.',
            'items.*.sgst_percentage.numeric' => 'The SGST percentage must be a number.',
            'items.*.cgst_percentage.numeric' => 'The CGST percentage must be a number.',
            'items.*.igst_percentage.numeric' => 'The IGST percentage must be a number.',
            'billing_address.json' => 'The billing address must be a valid JSON.',
            'shipping_address.json' => 'The shipping address must be a valid JSON.',
            'gst_details.json' => 'The GST details must be a valid JSON.',
            'sub_total.numeric' => 'The sub total must be a number.',
            'gst.numeric' => 'The GST must be a number.',
            'discount.numeric' => 'The discount must be a number.',
            'other_expenses.numeric' => 'The other expenses must be a number.',
            'taxable_amount.numeric' => 'The taxable amount must be a number.',
        ];
    }
}
