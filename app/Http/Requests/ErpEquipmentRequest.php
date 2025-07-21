<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErpEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name'            => 'required|string|max:255',
            'alias'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'upload_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'final_remarks'   => 'nullable|string',
        ];
    }
}
