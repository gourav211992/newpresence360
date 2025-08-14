<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'params' => 'array',
            'params.*' => 'required|array',
            'params.*.*' => 'required|alpha_num',
            'param_names' => 'array',
            'params_names.*' => 'required|string',
        ];
        // if ($this -> input('params')) {
        //     if (count($this -> input('params')) !== count($this ->))
        // }
    }
}
