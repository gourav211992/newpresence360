<?php

namespace App\Http\Requests;

use App\Rules\UniqueSubStoreCode;
use App\Rules\UniqueSubStoreName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Helpers\SubStore\Constants as SubStoreConstants;
use Auth;

class SubStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected $organization_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }

    public function rules(): array
    {
        $storeId = $this->route('id');

        return [
            'store_id' => 'required|array',
            'store_id.*' => 'required|numeric|integer|exists:erp_stores,id',
            'code' => [
                'required',
                'string',
                'max:30',
                new UniqueSubStoreCode($storeId, $this -> group_id), 
            ],
           'name' => [
                'required',
                'string',
                'max:100',
                new UniqueSubStoreName($storeId, $this -> group_id), 
            ],
            'status' => 'nullable|string|max:99',
            'store_location_type' => [
                    'required', 
                    'string',  
                    Rule::in(ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES), 
                ],
            'stock_store_types' => [
                    'required',
                    'string',  
                    Rule::in(SubStoreConstants::STOCK_STORE_TYPES_VALUES), 
                ],
            'description' => 'nullable|string|max:255'
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'organization_id.required' => 'The organization ID is required.',
    //         'organization_id.exists' => 'The selected organization ID is invalid.',
    //         'group_id.required' => 'The group ID is required.',
    //         'group_id.exists' => 'The selected group ID is invalid.',
    //         'company_id.required' => 'The company ID is required.',
    //         'company_id.exists' => 'The selected company ID is invalid.',
    //         'store_code.required' => 'The store code is required.',
    //         'store_code.unique' => 'This store code already exists. Please choose a different one.',
    //         'store_code.max' => 'The store code may not be greater than 100 characters.',
    //         'store_name.required' => 'The store name is required .',
    //         'store_name.max' => 'The store name may not be greater than 100 characters.',
    //         'status.max' => 'The status may not be greater than 99 characters.',
    //         'contact_person.string' => 'The contact person ID must be an string.',
    //         'contact_phone_no.regex' => 'The contact phone number must be between 10 and 12 digits and may include an optional "+" prefix.',
    //         'contact_email.email' => 'The contact email must be a valid email address.',
    //         'contact_email.regex' => 'The contact email format is invalid.',
    //         'contact_email.max' => 'The contact email may not be greater than 255 characters.',

    //         'racks.*.rack_code.unique' => 'The rack code must be unique. This one is already taken.',
    //         'racks.*.rack_code.required' => 'The rack code is required.',

    //         'shelfs.*.shelf_code.unique' => 'The shelf code must be unique. This one is already taken.',
    //         'shelfs.*.shelf_code.required' => 'The shelf code is required.',

    //         'bins.*.bin_code.unique' => 'The bin code must be unique. This one is already taken.',
    //         'bins.*.bin_code.required' => 'The bin code is required.',

    //         'store_location_type.required' => 'The store location type is required.',
    //         'store_location_type.string' => 'The store location type must be a valid string.',
    //         'store_location_type.in' => 'The selected store location type is invalid. Please choose a valid option.',

    //         'country_id.required' => 'The country is required.',
    //         'country_id.exists' => 'The selected country/region is invalid.',

    //         'state_id.required' => 'The state is required.',
    //         'state_id.exists' => 'The selected state is invalid.',

    //         'city_id.required' => 'The city is required.',
    //         'city_id.exists' => 'The selected city is invalid.',

    //         'address.required' => 'The address is required.',
    //         'address.string' => 'The address must be a valid string.',
    //         'address.max' => 'The address may not be greater than 255 characters.',

    //         'pincode.regex' => 'The pincode must be a 6-digit number.',

    //     ];
    // }
}
