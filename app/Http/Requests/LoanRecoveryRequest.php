<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;

class LoanRecoveryRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change this if authorization logic is needed
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'settled_principal' => Helper::removeCommas($this->settled_principal),
            'settled_interest' => Helper::removeCommas($this->settled_interest),
            'balance_amount' => Helper::removeCommas($this->balance_amount),
            'settled_blnc_amnt' => Helper::removeCommas($this->settled_blnc_amnt) ?? 0,
            'settled_amnt' => Helper::removeCommas($this->settled_amnt) ?? 0,
            'settled_rec_amnt' => Helper::removeCommas($this->settled_rec_amnt) ?? 0,
            'dis_amount' => Helper::removeCommas($this->dis_amount),
            'recovery_amnnt' => Helper::removeCommas($this->recovery_amnnt),
            'dis_id' => json_encode($this->dis_id),
        ]);
    }

    public function rules()
    {
        return [
            'book_id' => 'required|integer',
            'settle_status' => 'nullable|integer|in:0,1',
            'document_no' => 'required|string|max:255|unique:erp_recovery_loans,document_no',
            'doc_number_type' => 'required|string|max:50',
            'doc_reset_pattern' => 'nullable|string|max:50',
            'doc_prefix' => 'nullable|string|max:50',
            'doc_suffix' => 'nullable|string|max:50',
            'doc_no' => 'nullable|string|max:255',
            'settled_principal' => 'nullable|numeric|min:0',
            'settled_interest' => 'nullable|numeric|min:0',
            'balance_amount' => 'nullable|numeric|min:0',
            'account_number' => 'nullable|string|max:20',
            'settled_blnc_amnt' => 'nullable|numeric|min:0',
            'settled_amnt' => 'nullable|numeric|min:0',
            'settled_rec_amnt' => 'nullable|numeric|min:0',
            'dis_amount' => 'nullable|numeric|min:0',
            'dis_id' => 'nullable|json',
            'dis_id.*' => 'integer',
            'application_no' => 'nullable|string|max:100',
            'recovery_amnnt' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:50',
            'ref_no' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}
