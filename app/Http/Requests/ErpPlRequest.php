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
           'book_code' => 'required|string',
           'document_no' => 'required|string',
           'document_date' => 'required|date',
           'store_id' => 'required|numeric|integer|exists:erp_stores,id',
           'main_sub_store_id' => 'required|numeric|integer|exists:erp_sub_stores,id',
           'staging_sub_store_id' => 'required|numeric|integer|exists:erp_sub_stores,id',
           'remarks' => 'nullable|string|max:255',
       ];
   }
   
}
