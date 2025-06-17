<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ProcessesComponentJson;

class PoBulkRequest extends FormRequest
{
    use ProcessesComponentJson;
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
    protected function prepareForValidation(): void
    {
        $this->processComponentJson('components_json');
    }

     public function rules(): array
     {
         $parameters = [];
         $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
         if ($response['status'] === 200) {
             $parameters = json_decode(json_encode($response['data']['parameters']), true);
         }
         $vendor = auth()->user()?->vendor_portal;
         $storeRequired = true;
         if($vendor) {
             $storeRequired = false;
         }
         $rules = [
             'book_id' => 'required',
             'document_date' => 'required|date',
            //  'document_number' => 'required',
            //  'vendor_id' => $vendor ? 'nullable' : 'required',
            //  'currency_id' => $vendor ? 'nullable' : 'required',
            //  'payment_term_id' => $vendor ? 'nullable' : 'required',
         ];
         // if($departmentRequired) {
         //     $rules['department_id'] = 'required';
         // }
         if($storeRequired) {
             $rules['store_id'] = 'required';
         }
         $today = now()->toDateString();
         $isPast = false;
         $isFeature = false;
         $futureAllowed = isset($parameters['future_date_allowed']) && is_array($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['future_date_allowed']));
         $backAllowed = isset($parameters['back_date_allowed']) && is_array($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['back_date_allowed']));
 
         if (!$futureAllowed && !$backAllowed) {
             $rules['document_date'] = "required|date|in:$today";
         } else {
             if ($futureAllowed) {
                 $rules['document_date'] = "after_or_equal:$today";
                 $isFeature = true;
             } else {
                 $rules['document_date'] = "before_or_equal:$today";
                 $isFeature = false;
             }
             if ($backAllowed) {
                 $rules['document_date'] = "before_or_equal:$today";
                 $isPast = true;
             } else {
                 $rules['document_date'] = "after_or_equal:$today";
                 $isPast = false;
             }
         }
         if($isFeature && $isPast) {
             $rules['document_date'] = "required|date";
         }
        //  if ($this->filled('book_id')) {
        //      $user = Helper::getAuthenticatedUser();
        //      $numPattern = NumberPattern::where('organization_id', $user->organization_id)
        //                  ->where('book_id', $this->book_id)
        //                  ->orderBy('id', 'DESC')
        //                  ->first();
        //      if ($numPattern && $numPattern->series_numbering == 'Manually') {
        //          if($poId) {
        //              $rules['document_number'] = 'required|unique:erp_purchase_orders,document_number,' . $poId;
        //          } else {
        //              $rules['document_number'] = 'required|unique:erp_purchase_orders,document_number';
        //          }
        //      }
        //  }
    
 
         foreach ($this->input('components', []) as $index => $component) {
            if (!empty($component['is_pi_item_id'])) {
                $rules["components.$index.qty"] = 'required|numeric|min:0.000001';
                $rules["components.$index.rate"] = 'required|numeric|min:0.01';
                $rules["components.$index.vendor_id"] = 'required';
                $rules["components.$index.delivery_date"] = 'required';
            }
         }
         return $rules;
     }
 
     public function messages(): array
     {
         return [
             'book_id.required' => 'The series is required.',
             'component_item_name.*.required' => 'Required',
             'components.*.qty.required' => 'Required',
             'components.*.rate.required' => 'Required',
             'components.*.vendor_id.required' => 'Required',
             'components.*.qty.required' => 'Required',
             'document_date.in' => 'The document date must be today.',
             'document_date.required' => 'The document date is required.',
             'document_date.date' => 'Please enter a valid date for the document date.',
             'document_date.after_or_equal' => 'The document date cannot be in the past.',
             'document_date.before_or_equal' => 'The document date cannot be in the future.',
         ];
  
     }
}
