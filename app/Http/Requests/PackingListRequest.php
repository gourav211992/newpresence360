<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackingListRequest extends FormRequest
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
            'packing_list_id' => 'numeric|integer',
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => ['required'],
            'document_date' => 'required|date',
            'store_id' => 'required|numeric|integer',
            'sub_store_id' => 'required|numeric|integer',
            'items.*.packet_name' => 'required|string',
            'items.*.sale_order_id' => 'required|numeric',
            // 'items.*.so_items' => 'required|array|min:1',
            // 'items.*.so_items.qty' => 'required|numeric|min:0.000001',
            // 'items.*.so_items.item_id' => 'required|numeric',
            'final_remarks' => 'nullable|string|max:255',
        ];
    }

    protected function withValidator($validator)
    {
        
    }
}
