<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpSaleOrder extends FormRequest
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
            'sale_order_id' => 'numeric|integer',
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => 'required|unique:erp_sale_orders,document_number' . (isset(request() -> sale_order_id) ? 'expect,' . request() -> sale_order_id : ''),
            'document_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'customer_id' => 'required|numeric|integer|exists:erp_customers,id',
            'currency_id' => 'required|numeric|integer|exists:erp_currency,id',
            'payment_terms_id' => 'required|numeric|integer|exists:erp_payment_terms,id',
            'billing_address' => 'required|numeric|integer',
            'shipping_address' => 'required|numeric|integer',
            'item_id' => 'required|array|min:1',
            'item_id.*' => 'required|numeric|integer',
            'item_qty' => 'required|array|same_size',
        ];
    }
}
