<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use Illuminate\Foundation\Http\FormRequest;

class SaleOrderImportRequest extends FormRequest
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
            'book_id' => 'required|numeric|integer',
            'location_id' => 'required|numeric|integer',
            'attachment' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv|max:10240',
        ];
    }
    public function messages(): array
    {
        return [
            'book_id.required' => 'Series is required',
            'location_id.required' => 'Location is required',
            'attachment.required' => 'Import File is required',
            'attachment.file' => 'Import File should be a valid Excel File',
            'attachment.mimetypes' => 'Import File should be a valid Excel File',
        ];
    }
}
