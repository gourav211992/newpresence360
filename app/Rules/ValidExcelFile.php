<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidExcelFile implements ValidationRule
{
    protected array $allowedExtensions = ['xlsx', 'xls', 'csv', 'docx'];
    protected array $allowedMimeTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel',                                          // .xls
        'text/csv',                                                          // .csv
        'application/msword',                                               // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        // 'application/kset'                                                  // fallback for Windows edge cases
    ];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!($value instanceof UploadedFile)) {
            $fail('The :attribute must be a valid uploaded file.');
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mime = $value->getMimeType(); // add log for this
        \Log::info("File uploaded: $mime");

        if (!in_array($extension, $this->allowedExtensions)) {
            $fail('The :attribute must be an Excel, CSV, or DOCX file.');
            return;
        }

        if (!in_array($mime, $this->allowedMimeTypes)) {
            $fail("The :attribute has an unsupported file type ($mime).");
            return;
        }
    }
}
