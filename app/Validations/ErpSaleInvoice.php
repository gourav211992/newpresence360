<?php

namespace App\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class ErpSaleInvoice
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        
    }
    public function store(): ValidationValidator
    {
        $validator = Validator::make($this->request->all(), [
                'book_id' => [
                    'required',
                    'numeric',
                    'integer'
                ],
                'document_no' => [
                    'required',
                    'string'
                ],
                'document_date' => [
                    'required',
                    'date'
                ],
                'reference_no' => [
                    'nullable',
                    'string'
                ],
                'customer_id' => [
                    'required',
                    'numeric',
                    'integer'
                ],
                'currency_id' => [
                    'required',
                    'numeric',
                    'integer'
                ],
                'payment_terms_id' => [
                    'required',
                    'numeric',
                    'integer'
                ],
                'billing_address' => [
                    'required',
                    'numeric',
                    'integer'
                ],
                'shipping_address' => [
                    'required',
                    'numeric',
                    'integer'
                ],
            ] 
        );
        return $validator;
    }
}
