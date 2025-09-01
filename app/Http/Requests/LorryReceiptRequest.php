<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\Helper;

class LorryReceiptRequest extends FormRequest
{
    protected $organization_id;
    protected $group_id;

    public function authorize(): bool
    {
        return true;
    }

     protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->merge([
            'organization_id' => $organization?->id,
            'group_id' => $organization?->group_id,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('id'); // For update

        return [
            'book_id'            => 'required|exists:erp_books,id',
            'document_number'    => 'required|string|max:255',
            'document_date'      => 'required|date|before_or_equal:today',
            'location'           => 'required|exists:erp_stores,id',
            'cost_center_id'     => 'nullable|exists:erp_cost_centers,id',

            'source_id'        => 'required|numeric|exists:erp_logistics_route_masters,id',
            'destination_id'   => 'required|numeric|exists:erp_logistics_route_masters,id',
            'customer_id'      => 'required|numeric|exists:erp_customers,id',
            'consignee_id'     => 'required|numeric|exists:erp_customers,id',
            // 'vehicle_number'   => 'required',
            'vehicle_number_id'=> 'required|numeric|exists:erp_vehicles,id',
            'distances'        => 'required','numeric', 'regex:/^\d{1,4}(\.\d{1,2})?$/',
            'freight_charge'   => 'required|numeric|min:0',
            'driver_id'        => 'required|numeric|exists:erp_drivers,id',
            'driver_cash'      => 'nullable|numeric|min:0',
            'fuel_price'       => 'nullable|numeric|min:0',
            'invoice_no'       => 'nullable|string|max:255',
            'invoice_value'    => 'nullable|numeric|min:0',
            'no_of_bundles'    => 'required|numeric|min:1',
            'weight'           => 'required', 'numeric', 'regex:/^\d{1,4}(\.\d{1,2})?$/',
            'ewaybill_no'      => 'required|string|max:255',
            'gst_paid_by'      => 'required|in:Consignor,Consignee,Transporter',
            'lr_type'          => 'required',
            'billing_type'     => 'required',
            'load_type'        => 'nullable|in:FTL,Bulk,CEP,FCL,LCP,LTL',
            'lr_charges'       => 'nullable|numeric|min:0',
            

            // Dynamic locations
          'locations'                  => 'required|array|min:1',
'locations.*.location_id'    => 'nullable|numeric|exists:erp_logistics_route_masters,id',

'locations.*.type' => [
    function ($attribute, $value, $fail) {
        $index = explode('.', $attribute)[1]; // index निकाल लो (0,1,...)
        $locationId = request()->input("locations.$index.location_id");

        if ($locationId) {
            if (!$value) {
                $fail("The type field is required when location is selected.");
            } elseif (!in_array($value, ['Pick Up', 'Drop Off'])) {
                $fail("The type must be Pick Up or Drop Off.");
            }
        }
    },
],
'locations.*.no_of_articles' => 'nullable|required_with:locations.*.location_id|integer|min:1',
'locations.*.weight'         => 'nullable|required_with:locations.*.location_id|numeric|min:0.01',
'locations.*.freight'        => 'nullable|required_with:locations.*.location_id|numeric|min:0',



        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'The series field is required.',
            'book_id.exists' => 'The selected series is invalid.',

            'document_number.required' => 'The document number is required.',
            'document_number.max' => 'The document number may not be greater than 255 characters.',

            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please provide a valid document date.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',

            'location.required' => 'The location is required.',
            'location.exists' => 'The selected location is invalid.',

            'cost_center_id.required' => 'The cost center is required.',
            'cost_center_id.exists' => 'The selected cost center is invalid.',

            'source_id.required' => 'The source is required.',
            'source_id.exists' => 'The selected source is invalid.',
            'destination_id.required' => 'The destination is required.',
            'destination_id.exists' => 'The selected destination is invalid.',

            'customer_id.required' => 'The consignor is required.',
            'customer_id.exists' => 'The selected consignor is invalid.',
            
            'consignee_id.required' => 'The consignee is required.',
            'consignee_id.exists' => 'The selected consignee is invalid.',

            'vehicle_number_id.required' => 'The vehicle number is required.',
            'vehicle_number_id.exists' => 'The selected vehicle number is invalid.',

            'distances.required' => 'The distance is required.',
            'distances.numeric' => 'The distance must be a number.',
            'distances.min' => 'The distance must be at least 1 km.',
            'distances.regex' => 'Distance must be a number with up to 4 digits and up to 2 decimal places.',

            'freight_charge.required' => 'The freight charges are required.',
            'freight_charge.numeric' => 'The freight charges must be a number.',
            'freight_charge.min' => 'The freight charges must be at least 0.',

            'driver_id.required' => 'The driver is required.',
            'driver_id.exists' => 'The selected driver is invalid.',

            'driver_cash.numeric' => 'Driver cash must be a number.',
            'driver_cash.min' => 'Driver cash must be at least 0.',

            'fuel_price.numeric' => 'Fuel price must be a number.',
            'fuel_price.min' => 'Fuel price must be at least 0.',

            'invoice_no.max' => 'The invoice number may not be greater than 255 characters.',
            'invoice_value.numeric' => 'Invoice value must be a number.',
            'invoice_value.min' => 'Invoice value must be at least 0.',

            'no_of_bundles.required' => 'The number of article/bundles is required.',
            'no_of_bundles.numeric' => 'The number of article/bundles must be a number.',
            'no_of_bundles.min' => 'The number of article/bundles must be at least 1.',

            'weight.required' => 'The weight is required.',
            'weight.numeric' => 'The weight must be a number.',
            'weight.min' => 'The weight must be at least 1 kg.',
            'weight.regex' => 'Weight must be a number with up to 4 digits and up to 2 decimal places.',


            'ewaybill_no.required' => 'The E-Waybill number is required.',
            'ewaybill_no.max' => 'The E-Waybill number may not be greater than 255 characters.',

            'gst_paid_by.required' => 'Please select who paid the GST.',
            'gst_paid_by.in' => 'Please select a valid GST paid by value.',

            'lr_type.required' => 'The LR type is required.',
            'billing_type.required' => 'The billing type is required.',
            'load_type.in' => 'Please select a valid load type.',

            'lr_charges.numeric' => 'LR charges must be a number.',
            'lr_charges.min' => 'LR charges must be at least 0.',

            'locations.required' => 'At least one location entry is required.',

            'locations.*.location_id.numeric' => 'Each location must be a valid number.',
            'locations.*.location_id.exists' => 'The selected location is invalid.',

            'locations.*.type.required_with' => 'The type (Pick Up/Drop Off) is required when location is selected.',
            'locations.*.type.in' => 'The type must be Pick Up or Drop Off.',

            'locations.*.no_of_articles.required_with' => 'Article count is required when location is selected.',
            'locations.*.no_of_articles.numeric' => 'Articles must be a number.',
            'locations.*.no_of_articles.min' => 'Articles must be at least 1.',

            'locations.*.weight.required_with' => 'Weight is required when location is selected.',
            'locations.*.weight.numeric' => 'Weight must be a number.',
            'locations.*.weight.min' => 'Weight must be greater than 0.',

            'locations.*.freight.required_with' => 'Freight amount is required when location is selected.',
            'locations.*.freight.numeric' => 'Freight must be a number.',
            'locations.*.freight.min' => 'Freight must be at least 0.',


            'lorry_file.file' => 'The uploaded file must be a valid file.',
            'lorry_file.mimes' => 'File must be a type: jpg, jpeg, png, pdf.',
            'lorry_file.max' => 'File may not be greater than 2 MB.',
        ];
    }

public function withValidator(Validator $validator)
{
    $validator->after(function ($validator) {
        $sourceId = $this->input('source_id');
        $destinationId = $this->input('destination_id');
        $customerId = $this->input('customer_id');
        $consigneeId = $this->input('consignee_id');

        if ($sourceId && $destinationId && $sourceId == $destinationId) {
            $validator->errors()->add('destination_id', 'Source and destination location cannot be the same.');
        }

        if ($customerId && $consigneeId && $customerId == $consigneeId) {
            $validator->errors()->add('consignee_id', 'Consignor and Consignee cannot be the same.');
        }

        $locationInputs = collect($this->input('locations', []));
        $locationIds = $locationInputs->pluck('location_id')->filter();

        // Detect duplicate location IDs
        $duplicateIds = $locationIds->duplicates()->all();

        foreach ($locationInputs as $index => $location) {
            $locId = $location['location_id'] ?? null;

            // Source match
            if ($sourceId && $locId == $sourceId) {
                $validator->errors()->add("locations.$index.location_id", 'Pickup/Drop-off location must be different from the source location.');
            }

            // Destination match
            if ($destinationId && $locId == $destinationId) {
                $validator->errors()->add("locations.$index.location_id", 'Pickup/Drop-off location must be different from the destination location.');
            }

            // Duplicate location ID
            if (in_array($locId, $duplicateIds)) {
                $validator->errors()->add("locations.$index.location_id", 'Duplicate locations are not allowed.');
            }
        }
    });
}


}
