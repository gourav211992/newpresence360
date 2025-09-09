<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FixedAssetRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Check if it's an update (edit page)
        if ($this->input('page') == "edit") {
            // Get the asset ID to exclude it from the uniqueness check
            $assetId = $this->route('finance.fixed-asset.registration.edit'); // Assuming the asset ID is passed via route parameters

            // Validation rules for update
            if ($this->input('document_status') != "draft") {

                return [
                    'document_status' => 'required|string',
                    'reference_no' => 'nullable|string',
                    'status' => 'required|string',
                    'category_id' => 'required|integer',
                    'asset_name' => 'required|string',
                    'quantity' => 'required|numeric',
                    'ledger_id' => 'nullable|integer',
                    'ledger_group_id' => 'nullable|integer',
                    'capitalize_date' => 'nullable|date',
                    'maintenance_schedule' => 'nullable|string',
                    'useful_life' => 'nullable|integer',
                    'salvage_value' => 'nullable|numeric',
                    'current_value' => 'nullable|numeric',
                    'vendor_id' => 'nullable|integer',
                    'currency_id' => 'nullable|integer',
                    'supplier_invoice_no' => 'nullable|string',
                    'supplier_invoice_date' => 'nullable|date',
                    'sub_total' => 'nullable|numeric',
                    'tax' => 'nullable|numeric',
                    'purchase_amount' => 'nullable|numeric',
                    'book_date' => 'nullable|date',
                ];
            } else {
                return [
                    'document_status' => 'required|string',
                    'asset_name' => 'required|string',
                    'status' => 'required|string',
                ];
            }
        } else {
            // Validation for creation (not an edit page)
            if ($this->input('document_status') != "draft") {
                return [
                    'book_code' => 'required|string',
                    'doc_number_type' => 'required|string',
                    'doc_prefix' => 'nullable|string',
                    'doc_suffix' => 'nullable|string',
                    'doc_no' => 'required|integer',
                    'document_status' => 'required|string',
                    'book_id' => 'required|integer',
                    'document_number' => 'required|string',
                    'document_date' => 'required|date',
                    'reference_no' => 'nullable|string',
                    'status' => 'required|string',
                    'category_id' => 'required|integer',
                    'asset_name' => 'required|string',
                    'asset_code' => 'required|string', // Unique rule without exclusion
                    'quantity' => 'required|numeric',
                    'ledger_id' => 'nullable|integer',
                    'ledger_group_id' => 'nullable|integer',
                    'capitalize_date' => 'nullable|date',
                    'maintenance_schedule' => 'nullable|string',
                    'useful_life' => 'nullable|integer',
                    'salvage_value' => 'nullable|numeric',
                    'current_value' => 'nullable|numeric',
                    'vendor_id' => 'nullable|integer',
                    'currency_id' => 'nullable|integer',
                    'supplier_invoice_no' => 'nullable|string',
                    'supplier_invoice_date' => 'nullable|date',
                    'sub_total' => 'nullable|numeric',
                    'tax' => 'nullable|numeric',
                    'purchase_amount' => 'nullable|numeric',
                    'book_date' => 'nullable|date',
                ];
            } else {
                return [
                    'book_code' => 'required|string',
                    'doc_number_type' => 'required|string',
                    'doc_prefix' => 'nullable|string',
                    'doc_suffix' => 'nullable|string',
                    'doc_no' => 'required|integer',
                    'document_status' => 'required|string',
                    'book_id' => 'required|integer',
                    'document_number' => 'required|string',
                    'document_date' => 'required|date',
                    'asset_name' => 'required|string',
                    'asset_code' => 'required|string', // Unique rule without exclusion
                    'status' => 'required|string',
                    'vendor_id' => 'nullable|integer',
                ];
            }
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'book_code' => 'Book Code',
            'doc_number_type' => 'Document Number Type',
            'doc_prefix' => 'Document Prefix',
            'doc_suffix' => 'Document Suffix',
            'doc_no' => 'Document Number',
            'document_status' => 'Document Status',
            'book_id' => 'Book ID',
            'document_number' => 'Document Number',
            'document_date' => 'Document Date',
            'reference_no' => 'Reference Number',
            'status' => 'Status',
            'category_id' => 'Category',
            'asset_name' => 'Asset Name',
            'asset_code' => 'Asset Code',
            'quantity' => 'Quantity',
            'ledger_id' => 'Ledger ID',
            'ledger_group_id' => 'Ledger Group ID',
            'capitalize_date' => 'Capitalize Date',
            'maintenance_schedule' => 'Maintenance Schedule',
            'useful_life' => 'Useful Life',
            'salvage_value' => 'Salvage Value',
            'current_value' => 'Current Value',
            'vendor_id' => 'Vendor ID',
            'currency_id' => 'Currency ID',
            'supplier_invoice_no' => 'Supplier Invoice Number',
            'supplier_invoice_date' => 'Supplier Invoice Date',
            'sub_total' => 'Sub Total',
            'tax' => 'Tax',
            'purchase_amount' => 'Purchase Amount',
            'book_date' => 'Book Date',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'book_code.required' => 'The Book Code field is required.',
            'book_code.string' => 'The Book Code must be a valid string.',
            'doc_number_type.required' => 'The Document Number Type field is required.',
            'doc_number_type.string' => 'The Document Number Type must be a valid string.',
            'doc_prefix.string' => 'The Document Prefix must be a valid string.',
            'doc_suffix.string' => 'The Document Suffix must be a valid string.',
            'doc_no.required' => 'The Document Number field is required.',
            'doc_no.integer' => 'The Document Number must be an integer.',
            'document_status.required' => 'The Document Status field is required.',
            'document_status.string' => 'The Document Status must be a valid string.',
            'book_id.required' => 'The Book ID field is required.',
            'book_id.integer' => 'The Book ID must be an integer.',
            'document_number.required' => 'The Document Number field is required.',
            'document_number.string' => 'The Document Number must be a valid string.',
            'document_date.required' => 'The Document Date field is required.',
            'document_date.date' => 'The Document Date must be a valid date.',
            'reference_no.required' => 'The Reference Number field is required.',
            'reference_no.string' => 'The Reference Number must be a valid string.',
            'status.required' => 'The Status field is required.',
            'status.string' => 'The Status must be a valid string.',
            'category_id.required' => 'The Category field is required.',
            'category_id.integer' => 'The Category must be an integer.',
            'asset_name.required' => 'The Asset Name field is required.',
            'asset_name.string' => 'The Asset Name must be a valid string.',
            'asset_name.unique' => 'The Asset Name has already been taken.',
            'asset_code.required' => 'The Asset Code field is required.',
            'asset_code.string' => 'The Asset Code must be a valid string.',
            'asset_code.unique' => 'The Asset Code has already been taken.',
            'quantity.required' => 'The Quantity field is required.',
            'quantity.numeric' => 'The Quantity must be numeric.',
            'ledger_id.integer' => 'The Ledger ID must be an integer.',
            'ledger_group_id.integer' => 'The Ledger Group ID must be an integer.',
            'capitalize_date.date' => 'The Capitalize Date must be a valid date.',
            'maintenance_schedule.string' => 'The Maintenance Schedule must be a valid string.',
            'useful_life.integer' => 'The Useful Life must be an integer.',
            'salvage_value.numeric' => 'The Salvage Value must be numeric.',
            'current_value.numeric' => 'The Current Value must be numeric.',
            'vendor_id.integer' => 'The Vendor ID must be an integer.',
            'currency_id.integer' => 'The Currency ID must be an integer.',
            'supplier_invoice_no.string' => 'The Supplier Invoice Number must be a valid string.',
            'supplier_invoice_date.date' => 'The Supplier Invoice Date must be a valid date.',
            'sub_total.numeric' => 'The Sub Total must be numeric.',
            'tax.numeric' => 'The Tax must be numeric.',
            'purchase_amount.numeric' => 'The Purchase Amount must be numeric.',
            'book_date.date' => 'The Book Date must be a valid date.',
        ];
    }
}
