<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpPlRequest extends FormRequest
{
    /* Determine if the user is authorized to make this request.
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
           'book_id' => 'required|numeric|integer|exists:erp_books,id',
           'book_code' => 'required|string|max:10',
           'document_no' => 'required|string|max:50',
           'document_date' => 'required|date',
           'store_id' => 'required|numeric|integer|exists:erp_stores,id',
           'remarks' => 'nullable|string|max:255',
       ];
   }
   
}
