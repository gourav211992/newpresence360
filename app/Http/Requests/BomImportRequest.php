<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Rules\ValidExcelFile;
use Illuminate\Foundation\Http\FormRequest;

class BomImportRequest extends FormRequest
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
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date') ?? date('Y-m-d'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        $rules = [
            'book_id' => 'required',
            'document_date' => 'required|date',
            'document_number' => 'required',
            'attachment'      => ['required', 'file', new ValidExcelFile(), 'max:5120'],
            // 'attachment' => 'required|file|mimes:xlsx,xls,csv,docx|max:2048',
        ];
        $today = now()->toDateString();
        $isPast = false;
        $isFeature = false;
        $futureAllowed = isset($parameters['future_date_allowed']) && is_array($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['future_date_allowed']));
        $backAllowed = isset($parameters['back_date_allowed']) && is_array($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['back_date_allowed']));

        if (!$futureAllowed && !$backAllowed) {
            $rules['document_date'] = "required|date|in:$today";
        } else {
            if ($futureAllowed) {
                $rules['document_date'] = "required|date|after_or_equal:$today";
                $isFeature = true;
            } else {
                $rules['document_date'] = "required|date|before_or_equal:$today";
                $isFeature = false;
            }
            if ($backAllowed) {
                $rules['document_date'] = "required|date|before_or_equal:$today";
                $isPast = true;
            } else {
                $rules['document_date'] = "required|date|after_or_equal:$today";
                $isPast = false;
            }
        }
        if($isFeature && $isPast) {
            $rules['document_date'] = "required|date";
        }
        return $rules;
    }
    
    public function messages(): array
    {
        return [
            'book_id.required' => 'The series is required.',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
        ];
    }
}
