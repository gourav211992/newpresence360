<?php

namespace App\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class Auth
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
                    'required|numeric|integer'
                ],
                'document_no' => [
                    'required|string'
                ],
                'document_date' => [
                    'required|date'
                ],
                'reference_no' => [
                    'nullable|string'
                ],
            ] 
        );
        return $validator;
    }
}
