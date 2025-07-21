<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use Auth;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected $organization_id;
    protected $company_id;
    protected $group_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
        $this->company_id = $organization ? $organization->company_id : null;
    }

    public function rules(): array
    {
        $storeId = $this->route('id');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
         $uniqueScope = function ($query) {
            if ($this->group_id !== null) {
                $query->where('group_id', $this->group_id);
            }

            if ($this->company_id !== null) {
                $query->where(function ($q) {
                    $q->where('company_id', $this->company_id)->orWhereNull('company_id');
                });
            }

            if ($this->organization_id !== null) {
                $query->where(function ($q) {
                    $q->where('organization_id', $this->organization_id)->orWhereNull('organization_id');
                });
            }
        };
        return [
            'organization_id' => [
                'required', 
            ],
            'store_id' => 'nullable|integer|exists:erp_stores,id',
            'store_code' => [
                'required',
                'string',
                'max:100',
                $isUpdate
                    ? Rule::unique('erp_stores', 'store_code')->ignore($storeId)->whereNull('deleted_at')->where($uniqueScope)
                    : Rule::unique('erp_stores', 'store_code')->whereNull('deleted_at')->where($uniqueScope)
            ],

            'store_name' => [
                'required',
                'string',
                'max:100',
                $isUpdate
                    ? Rule::unique('erp_stores', 'store_name')->ignore($storeId)->whereNull('deleted_at')->where($uniqueScope)
                    : Rule::unique('erp_stores', 'store_name')->whereNull('deleted_at')->where($uniqueScope)
            ],

            'status' => 'nullable|string|max:99',
            'contact_person' => [
                'nullable', 
                'string',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            // 'store_location_type' => [
            //         'required', 
            //         'string',  
            //         Rule::in(ConstantHelper::ERP_STORE_LOCATION_TYPES), 
            //     ],
            'contact_phone_no' => [
                'string',
                'nullable',
                'regex:/^\+?[0-9]{10,12}$/',
            ],
            'contact_email' => [
                'email',
                'nullable',
                'max:255',
                'regex:/^[\w\.\-]+@[a-zA-Z\d\-]+(\.[a-zA-Z\d\-]+)*\.[a-zA-Z]{2,7}$/',
                $isUpdate
                    ? Rule::unique('erp_stores', 'contact_email')->ignore($storeId)->whereNull('deleted_at')->where($uniqueScope)
                    : Rule::unique('erp_stores', 'contact_email')->whereNull('deleted_at')->where($uniqueScope)
            ],

            'billing_address' => 'nullable',
            'country_id' => 'required|integer|exists:mysql_master.countries,id', 
            'state_id' => 'required|integer|exists:mysql_master.states,id',
            'city_id' => 'required|integer|exists:mysql_master.cities,id',
            'address' => 'required|string|max:255',
            'pincode_master_id' => [
                'nullable',
                'exists:mysql_master.erp_pincode_masters,id', 
            ],
            'pincode' => [
                'required',
            ],
            'rackshelfmapping' => 'nullable|array',
            'rackshelfmapping.*.rack_id' => 'nullable',
            'rackshelfmapping.*.shelf_ids' => 'nullable|array', 
            'rackshelfmapping.*.shelf_ids.*' => 'nullable',
            'storebinmapping' => 'nullable|array',
            'storebinmapping.bin_ids' => 'nullable|array',
            'storebinmapping.bin_ids.*' => 'nullable|integer|exists:erp_bins,id',
       
        ];
    }


    public function messages()
    {
        return [
            'organization_id.required' => 'The organization ID is required.',
            'organization_id.exists' => 'The selected organization ID is invalid.',
            'group_id.required' => 'The group ID is required.',
            'group_id.exists' => 'The selected group ID is invalid.',
            'company_id.required' => 'The company ID is required.',
            'company_id.exists' => 'The selected company ID is invalid.',
            'store_code.required' => 'The store code is required.',
            'store_code.unique' => 'This store code already exists.',
            'store_code.max' => 'The store code may not be greater than 100 characters.',
            'store_name.required' => 'The store name is required .',
            'store_name.max' => 'The store name may not be greater than 100 characters.',
            'status.max' => 'The status may not be greater than 99 characters.',
            'contact_person.string' => 'The contact person ID must be an string.',
            'contact_phone_no.regex' => 'The contact phone number must be between 10 and 12 digits and may include an optional "+" prefix.',
            'contact_email.email' => 'The contact email must be a valid email address.',
            'contact_email.regex' => 'The contact email format is invalid.',
            'contact_email.max' => 'The contact email may not be greater than 255 characters.',

            'racks.*.rack_code.unique' => 'The rack code must be unique. This one is already taken.',
            'racks.*.rack_code.required' => 'The rack code is required.',

            'shelfs.*.shelf_code.unique' => 'The shelf code must be unique. This one is already taken.',
            'shelfs.*.shelf_code.required' => 'The shelf code is required.',

            'bins.*.bin_code.unique' => 'The bin code must be unique. This one is already taken.',
            'bins.*.bin_code.required' => 'The bin code is required.',

            'store_location_type.required' => 'The store location type is required.',
            'store_location_type.string' => 'The store location type must be a valid string.',
            'store_location_type.in' => 'The selected store location type is invalid. Please choose a valid option.',

            'country_id.required' => 'The country is required.',
            'country_id.exists' => 'The selected country/region is invalid.',

            'state_id.required' => 'The state is required.',
            'state_id.exists' => 'The selected state is invalid.',

            'city_id.required' => 'The city is required.',
            'city_id.exists' => 'The selected city is invalid.',

            'address.required' => 'The address is required.',
            'address.string' => 'The address must be a valid string.',
            'address.max' => 'The address may not be greater than 255 characters.',

            'pincode.regex' => 'The pincode must be a 6-digit number.',

        ];
    }
}
