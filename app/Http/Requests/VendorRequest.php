<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\GstnHelper;
use Auth;

class VendorRequest extends FormRequest
{
    public function authorize(): bool
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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $vendorId = $this->route('id'); 

        $uniqueScope = function ($query) {
            if ($this->group_id !== null) {
                $query->where('group_id', $this->group_id);
            }

            if ($this->company_id !== null) {
                $query->where(function ($q) {
                    $q->where('company_id', $this->company_id)
                      ->orWhereNull('company_id');
                });
            }

            if ($this->organization_id !== null) {
                $query->where(function ($q) {
                    $q->where('organization_id', $this->organization_id)
                        ->orWhereNull('organization_id');
                });
            }
            
        };
        return [
        # vendor book and supplier user
        'user_id' => [
            'nullable',
            'array',
            function ($attribute, $value, $fail) {
                // Check if book_id is not empty but user_id is empty
                $bookIds = $this->book_id ?? [];
                if (!empty($bookIds) && empty($value)) {
                    $fail('The user field is required when books are selected.');
                }
            }
        ],
        'book_id' => [
            'nullable',
            'array',
            function ($attribute, $value, $fail) {
                // Check if user_id is not empty but book_id is empty
                $userIds = $this->user_id ?? [];
                if (!empty($userIds) && empty($value)) {
                    $fail('The book field is required when users are selected.');
                }
            }
        ],
         'vendor_code' => [
            'required',
            'string',
            'max:255',
            $isUpdate 
                ? Rule::unique('erp_vendors', 'vendor_code')
                    ->whereNull('deleted_at')
                    ->ignore($vendorId)
                     ->where($uniqueScope)
                : Rule::unique('erp_vendors', 'vendor_code')
                    ->whereNull('deleted_at')
                     ->where($uniqueScope),
          ],
            'vendor_initial'=>'required',
            'vendor_code_type'=>'nullable',
            'organization_type_id' => 'required|exists:mysql_master.erp_organization_types,id',
            'vendor_type' => 'required|string',
            'vendor_sub_type' => 'nullable|string',
            'display_name' => 'nullable|string|max:255',
            'deregistration_date' => 'nullable|string|max:255',
            'taxpayer_type' => 'nullable|string|max:255',
            'gst_status' => 'nullable|string|max:255',
            'block_status' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'company_name' => [
                    'required',
                    'string',
                    'max:255',
                    $isUpdate 
                        ? Rule::unique('erp_vendors', 'company_name')
                            ->whereNull('deleted_at')
                            ->ignore($vendorId)
                             ->where($uniqueScope)
                        : Rule::unique('erp_vendors', 'company_name')
                            ->where('company_id', $this->company_id)
                            ->whereNull('deleted_at')
                             ->where($uniqueScope),
                ],
            'category_id' => 'nullable|exists:erp_categories,id',
            'subcategory_id' => 'nullable|exists:erp_categories,id',
            'stop_billing' => 'nullable|string',
            'stop_purchasing' => 'nullable|string',
            'stop_payment' => 'nullable|string',
            'group_id' => 'nullable', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable', 
            'ledger_id' => 'nullable|exists:erp_ledgers,id', 
            'ledger_group_id' => 'nullable|exists:erp_groups,id', 
            'create_ledger' => 'nullable|boolean',
            'credit_days_editable'=>'nullable|boolean',
            'hidden_ledger_vendor_name' => 'nullable|string|max:255',
            'hidden_ledger_vendor_code' => 'nullable|string|max:20',
            'related_party' => 'nullable|string|max:255',
            'contra_ledger_id' => 'nullable|exists:erp_ledgers,id',
            'reld_vendor_id' => 'nullable|exists:erp_vendors,id',
            'enter_company_org_id' => [
                'nullable',
                'max:255',
                'required_if:related_party,on',
                function ($attribute, $value, $fail) use ($isUpdate, $vendorId) {
                    if ($value) {
                        $query = \App\Models\Vendor::where('enter_company_org_id', $value)
                            ->whereNull('deleted_at')
                            ->where('group_id', $this->group_id);
                        if ($isUpdate) {
                            $query->where('id', '!=', $vendorId);
                        }
                        if ($query->exists()) {
                            $fail('This Group Organization is already mapped to another Vendor.');
                        }
                    }
                },
            ],
           'email' => [
            'nullable',
            'email',
            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            $isUpdate
                ? Rule::unique('erp_vendors')
                    ->whereNull('deleted_at')
                    ->ignore($vendorId)
                    ->where($uniqueScope)
                : Rule::unique('erp_vendors')
                    ->whereNull('deleted_at')
                     ->where($uniqueScope),
            ],
            'phone' => 'nullable|string|regex:/^\d{10,12}$/',
            'mobile' => 'nullable|string|regex:/^\d{10}$/',
            'whatsapp_number' => 'nullable|string|regex:/^\d{10,12}$/',
            'whatsapp_same_as_mobile' => 'nullable|string',
            'notification' => 'nullable|array',
            'notification.*' => 'nullable|string',
            'pan_number' => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            'tin_number' => 'nullable|string|regex:/^\d{10}$/',
            'aadhar_number' => 'nullable|string|regex:/^\d{12}$/',
            'currency_id' => 'required|exists:mysql_master.currency,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'pricing_type' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0|max:365',
            'on_account_required' => 'nullable',
            'interest_percent' => 'nullable|numeric|min:0',
            'payment_terms_id' => 'required|exists:erp_payment_terms,id',
            'other_documents.*' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'aadhaar_attachment' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'pan_attachment' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'tin_attachment' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'status' => 'nullable',

            // Bank validation
            'bank_info' => 'nullable|array',
            'bank_info.*.id' => 'nullable|integer|exists:erp_bank_infos,id',
            'bank_info.*.bank_name' => 'nullable|string|max:255',
            'bank_info.*.beneficiary_name' => 'nullable|string|max:255|regex:/^[A-Za-z\s]+$/',
            'bank_info.*.account_number' => 'nullable|regex:/^\d{9,25}$/',
            'bank_info.*.re_enter_account_number' => 'nullable|same:bank_info.*.account_number',
            'bank_info.*.ifsc_code' => 'nullable|string',
            'bank_info.*.cancel_cheque.*' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'bank_info.*.primary' => 'nullable',

            // Notes validation
            'notes' => 'nullable|array',
            'notes.*.id' => 'nullable',
            'notes.remark' => 'nullable|string|max:255',

            'contacts' => 'nullable|array',
            'contacts.*.id' => 'nullable',
            'contacts.*' => 'nullable|array',
            'contacts.*.salutation' => 'nullable|string|max:50',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255', 
            'contacts.*.mobile' => 'nullable|string|regex:/^\d{10}$/',
            'contacts.*.phone' => 'nullable|string|regex:/^\d{10,12}$/',
            'contacts.*.primary' => 'nullable',

            // Vendor Item
            'vendor_item.item_code.*' => 'nullable',
            'vendor_item.id.*' => 'nullable',
            'vendor_item.item_name.*' => 'nullable|string|max:255',
            'vendor_item.part_number.*' => 'nullable|string|max:255', 
            'vendor_item.item_details.*' => 'nullable|string|max:255', 
            'vendor_item.*.cost_price' => 'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
            'vendor_item.*.item_id' => 'nullable', 
            'vendor_item.*.uom_id' => 'nullable|exists:erp_units,id|string|max:255', 
           
            // Address validation
            'addresses' => 'nullable|array',
            'addresses.*.id' => 'nullable',
            'addresses.*.country_id' => 'required|exists:mysql_master.countries,id',
            'addresses.*.state_id' => 'required|exists:mysql_master.states,id',
            'addresses.*.city_id' => 'required|exists:mysql_master.cities,id',
            'addresses.*.type' => 'nullable|string|max:255',
            'addresses.*.pincode' => 'required|string|max:10',
            'addresses.*.pincode_master_id' => 'nullable|exists:mysql_master.erp_pincode_masters,id',
            'addresses.*.phone' => 'nullable|string|regex:/^\d{10,12}$/',
            'addresses.*.fax_number' => 'nullable|numeric|min:0|max:999999999999999',
            'addresses.*.address' => 'nullable|string|max:255',
            'addresses.*.is_billing' => 'nullable',
            'addresses.*.is_shipping' => 'nullable',


            // Compliance
            'compliance' => 'nullable|array',
            'compliance.*.id' => 'nullable',
            'compliance.country_id' => 'nullable|exists:countries,id',
            'compliance.tds_applicable' => 'nullable',
            'compliance.wef_date' => 'nullable|date',
            'compliance.tds_certificate_no' => 'nullable|string|max:255',
            'compliance.tds_tax_percentage' => 'nullable|numeric|max:100',
            'compliance.tds_category' => 'nullable|string|max:255',
            'compliance.tds_value_cab' => 'nullable|numeric',
            'compliance.tan_number' => 'nullable|string|max:255',
            'compliance.gst_applicable' => 'nullable',
             'compliance.gstin_no' => [
                'nullable', 
                'string', 
                'size:15', 
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                // function ($attribute, $value, $fail) {
                //     if ($value) {
                //         $gstValidationResponse = $this->validateGstDetails();
                //         if ($gstValidationResponse !== true) {
                //             $fail($gstValidationResponse); 
                //         }
                //     }
                // },
                'required_if:compliance.gst_applicable,1'
            ],
            'compliance.gst_registered_name' => 'nullable|string|max:255',
            'compliance.gstin_registration_date' => 'nullable|date',
            'compliance.msme_registered' => 'nullable',
            'compliance.msme_no' => 'nullable|string|max:255',
            'compliance.msme_type' => 'nullable|string|max:255',
            'compliance.status' => 'nullable|string|max:255',
            'compliance.gst_certificate.*' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
            'compliance.msme_certificate.*' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_code.required' => 'The vendor code is required.',
            'vendor_code.string' => 'The vendor code must be a string.',
            'vendor_code.max' => 'The vendor code may not be greater than 255 characters.',
            'vendor_code.unique' => 'The vendor code has already been taken.',
            'vendor_initial.required' => 'The initial is required.',
            'credit_days.max' => 'The credit days must not exceed 365 days.',
            
            'organization_type_id.required' => 'The organization type is required.',
            'organization_type_id.exists' => 'The selected organization type is invalid.',
            
            'vendor_type.required' => 'The vendor type is required.',
            'vendor_type.string' => 'The vendor type must be a string.',
            
            'display_name.string' => 'The display name must be a string.',
            'display_name.max' => 'The display name may not be greater than 255 characters.',
            
            'company_name.required' => 'The vendor name is required.',
            'company_name.string' => 'The vendor name must be a string.',
            'company_name.max' => 'The vendor name may not be greater than 255 characters.',
            
            // Optional Fields
            'category_id.string' => 'The category ID must be a string.',
            'category_id.required' => 'The category  is required.',

            'subcategory_id.string' => 'The subcategory ID must be a string.',
            'subcategory_id.required' => 'The subcategory  is required.',

            'stop_billing.string' => 'The stop billing must be a string.',
            'stop_purchasing.string' => 'The stop purchasing must be a string.',
            'stop_payment.string' => 'The stop payment must be a string.',
            
            'group_id.exists' => 'The selected group is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'ledger_id.exists' => 'The selected ledger is invalid.',
            
            'related_party.string' => 'The related party must be a string.',
            'related_party.max' => 'The related party may not be greater than 255 characters.',
            
            // Email and Phone
            'email.email' => 'The email address must be valid.',
            'email.regex' => 'The email address format is invalid.',
            'email.unique' => 'The email has already been taken.',
            
            'phone.regex' => 'The phone number must be between 10 and 12 digits.',
            'mobile.regex' => 'The mobile number must be 10 digits.',
            'whatsapp_number.regex' => 'The WhatsApp number must be between 10 and 12 digits.',
            'whatsapp_same_as_mobile.string' => 'The WhatsApp same as mobile must be a string.',
            
            // Notifications
            'notification.array' => 'The notification must be an array.',
            'notification.*.string' => 'Each notification must be a string.',
            
            // Document Numbers
            'pan_number.regex' => 'The PAN number must be in the format: AAAAA9999A.',
            'tin_number.regex' => 'The TIN number must be in the format: 9999999999.',
            'aadhar_number.regex' => 'The Aadhaar number must be in the format: 999999999999.',
            
            'currency_id.exists' => 'The selected currency is invalid.',
            'currency_id.required' => 'The currency  is required.',

            'payment_terms_id.required' => 'The payment term is required.',
            'payment_terms_id.exists' => 'The selected payment term is invalid.',

            'financial.customer_id.exists' => 'The customer ID must exist.',
            'financial.leader_id.string' => 'The leader ID must be a string.',
            'financial.pricing_type.string' => 'The pricing type must be a string.',
            'financial.credit_limit.numeric' => 'The credit limit must be a number.',
            'financial.credit_days.min' => 'The credit days must be at least 0.',
            'financial.credit_days.max' => 'The credit days must not exceed 365 days.',

            'financial.interest_percent.numeric' => 'The interest percentage must be a number.',

            'bank_info.array' => 'The bank info field must be an array.',
            'bank_info.*.id.exists' => 'The bank info ID must exist.',
            'bank_info.*.bank_name.string' => 'The bank name must be a string.',
            'bank_info.*.beneficiary_name.string' => 'The beneficiary name must be a string.',
            'bank_info.*.beneficiary_name.regex' => 'The beneficiary name may only contain letters and spaces.',
            'bank_info.*.account_number.regex' => 'The account number must contain only digits and be between 9 to 25 digits long.',
            'bank_info.*.re_enter_account_number.same' => 'The re-entered account number does not match.',
            'bank_info.*.ifsc_code.regex' => 'Enter a valid IFSC code like SBIN0001234',
            'bank_info.*.cancel_cheque.mimes' => 'The cancel cheque must be a file of type: pdf, jpg, jpeg, png.',
            
            'compliance.array' => 'The compliance field must be an array.',
            'compliance.tds_applicable.string' => 'The TDS applicable field must be a string.',
            'compliance.wef_date.date' => 'The WEF date must be a valid date.',
            'compliance.tds_certificate_no.string' => 'The TDS certificate number must be a string.',
            'compliance.tds_tax_percentage.numeric' => 'The TDS tax percentage must be a number.',
            'compliance.tds_tax_percentage.max' => 'The TDS tax percentage must not be greater than 100%.',
            'compliance.tds_category.string' => 'The TDS category must be a string.',
            'compliance.tds_value_cab.numeric' => 'The TDS value CAB must be a number.',
            'compliance.tan_number.string' => 'The TAN number must be a string.',
            
            'compliance.gst_applicable.string' => 'The GST applicable field must be a string.',
            'compliance.gstin_no.string' => 'The GSTIN number must be a valid 15 character string.',
            'compliance.gstin_registration_date.date' => 'The GSTIN registration date must be a valid date.',
            'compliance.msme_registered.string' => 'The MSME registered field must be a string.',
            'compliance.msme_no.string' => 'The MSME number must be a string.',
            'compliance.msme_type.string' => 'The MSME type must be a string.',
            
            'compliance_certificates.*.name.string' => 'The certificate name must be a string.',
            'compliance_certificates.*.certificate_no.string' => 'The certificate number must be a string.',
            'compliance_certificates.*.expiry_date.date' => 'The certificate expiry date must be a valid date.',
            'compliance_certificates.*.document.mimes' => 'The certificate document must be a file of type: pdf, jpg, jpeg, png.',
            'compliance.gst_certificate.*.mimes' => 'The GST certificate must be a file of type: jpg, jpeg, png, pdf.',
            'compliance.msme_certificate.*.mimes' => 'The MSME certificate must be a file of type: jpg, jpeg, png, pdf.',
            'compliance.gstin_no.size' => 'The GSTIN number must be exactly 15 characters long.',
            'compliance.gstin_no.regex' => 'The GSTIN number must be in the format: 12ABCDE1234F1Z5.',
            
            'addresses.array' => 'The addresses field must be an array.',
            'addresses.*.country_id.exists' => 'The selected country is invalid.',
            'addresses.*.state_id.exists' => 'The selected state is invalid.',
            'addresses.*.city_id.exists' => 'The selected city is invalid.',
            'addresses.*.type.string' => 'The address type must be a string.',
            'addresses.*.pincode.string' => 'The pincode must be a string.',
            'addresses.*.pincode.max' => 'The pincode may not be greater than 10 characters.',
            'addresses.*.phone.regex' => 'The address phone number must be between 10 and 12 digits.',
            'addresses.*.fax_number.numeric' => 'The fax number must be a numeric value.',
            'addresses.*.fax_number.min' => 'The fax number must be at least 0.',
            'addresses.*.fax_number.max' => 'The fax number must not exceed 15 digits.',
            'addresses.*.address.string' => 'The address must be a string.',
            'addresses.*.address.max' => 'The address may not be greater than 255 characters.',


            'contacts.array' => 'The contacts field must be an array.',
            'contacts.*.array' => 'Each contact must be an array.',
            'contacts.*.salutation.string' => 'The salutation must be a string.',
            'contacts.*.salutation.max' => 'The salutation may not be greater than 50 characters.',
            'contacts.*.name.string' => 'The name must be a string.',
            'contacts.*.name.max' => 'The name may not be greater than 255 characters.',
            'contacts.*.email.email' => 'The email must be a valid email address.',
            'contacts.*.email.max' => 'The email may not be greater than 255 characters.',
            'contacts.*.mobile.string' => 'The mobile number must be a string.',
            'contacts.*.mobile.regex' => 'The mobile number must be 10 digits.',
            'contacts.*.phone.string' => 'The phone number must be a string.',
            'contacts.*.phone.regex' => 'The phone number must be between 10 to 12 digits.',

            'other_documents.*.mimes' => 'Other documents must be in one of the following formats: pdf, jpg, jpeg, png.',
            'aadhaar_attachment.mimes' => 'The Aadhaar attachment must be in one of the following formats: pdf, jpg, jpeg, png.',
            'pan_attachment.mimes' => 'The PAN attachment must be in one of the following formats: pdf, jpg, jpeg, png.',
            'tin_attachment.mimes' => 'The TIN attachment must be in one of the following formats: pdf, jpg, jpeg, png.',
            'bank_info.*.cancel_cheque.*.mimes' => 'The cancel cheque must be in one of the following formats: pdf, jpg, jpeg, png.',
            'other_documents.*.max' => 'The size of other documents must not exceed 2 MB.',
            'aadhaar_attachment.max' => 'The size of the Aadhaar attachment must not exceed 2 MB.',
            'pan_attachment.max' => 'The size of the PAN attachment must not exceed 2 MB.',
            'tin_attachment.max' => 'The size of the TIN attachment must not exceed 2 MB.',
            'bank_info.*.cancel_cheque.*.max' => 'The size of the cancel cheque must not exceed 2 MB.',
            'compliance.gst_certificate.*.max' => 'The size of the GST certificate must not exceed 2 MB.',
            'compliance.msme_certificate.*.max' => 'The size of the MSME certificate must not exceed 2 MB.',

        ];
    }

    protected function validateGstDetails()
    {
        $gstinNo = $this->input('compliance.gstin_no');
        if (empty($gstinNo)) {
            return true; 
        }
        $gstValidation = EInvoiceHelper::validateGstinName(gstNumber: $gstinNo);
        if ($gstValidation['Status'] ===0) {
            return $gstValidation['errorMsg'] ?? 'Invalid GST Number'; 
        }
        $gstData = json_decode($gstValidation['checkGstIn'], true);
        $deregistrationDate = $gstData['DtDReg'] ?? null;

        if ($deregistrationDate && $deregistrationDate !== '1900-01-01') {
            return 'The provided GSTIN is deregistered as of ' . $deregistrationDate . '. It is no longer valid for use.';
        }
        return true;
    }

     // Helper method to validate GST-related address details
     protected function addAddressValidationErrors($validator, $addresses, $gstData)
    {
        $gstnHelper = new GstnHelper();
        foreach ($addresses as $index => $address) {
            if (!empty($address['state_id'])) {
                $stateValidation = $gstnHelper->validateStateCode(
                    $address['state_id'],
                    $gstData['StateCode'] ?? null
                );
                if (!$stateValidation['valid']) {
                    $validator->errors()->add(
                        "addresses.{$index}.state_id", 
                        $stateValidation['message'] ?? 'State does not match GSTIN records'
                    );
                }
            }
        }
    }


    protected function validateBankInfo($validator)
    {
      
        $bankInfos = $this->input('bank_info', []);
        $hasPrimary = false;
        $hasIfscOrAccount = false;
        $lastIndex = count($bankInfos) > 0 ? count($bankInfos) - 1 : 0;
    
        foreach ($bankInfos as $index => $bank) {
            $accountNumber = $bank['account_number'] ?? null;
            $ifscCode = $bank['ifsc_code'] ?? null;
            $beneficiaryName = $bank['beneficiary_name'] ?? null;
            $reEnterAccountNumber = $bank['re_enter_account_number'] ?? null;
            $isPrimary = filter_var($bank['primary'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $chequeFiles = $this->file("bank_info.$index.cancel_cheque", []);
            $existingFile = $bank['existing_cancel_cheque'] ?? null;
            if ($isPrimary) {
                $hasPrimary = true;
            }
            if (!empty($ifscCode) || !empty($accountNumber)) {
                $hasIfscOrAccount = true;
    
                if (empty($accountNumber)) {
                    $validator->errors()->add("bank_info.$index.account_number", 'Account number is required when IFSC code is provided.');
                }
    
                if (empty($reEnterAccountNumber)) {
                    $validator->errors()->add("bank_info.$index.re_enter_account_number", 'Please re-enter account number.');
                } elseif ($accountNumber !== $reEnterAccountNumber) {
                    $validator->errors()->add("bank_info.$index.re_enter_account_number", 'Account numbers do not match.');
                }
    
                if (empty($beneficiaryName)) {
                    $validator->errors()->add("bank_info.$index.beneficiary_name", 'Beneficiary name is required.');
                }
    
                if (empty($ifscCode)) {
                    $validator->errors()->add("bank_info.$index.ifsc_code", 'IFSC code is required when account number is provided.');
                }
                // if ((!empty($accountNumber) || !empty($ifscCode)) && empty($chequeFiles) && empty($existingFile)) {
                //     $validator->errors()->add("bank_info.$index.cancel_cheque", 'Cancel cheque is required.');
                // }
            }
        }
    
        if ($hasIfscOrAccount && !$hasPrimary) {
            $validator->errors()->add("bank_info.$lastIndex.primary", 'At least one bank information must be marked as primary.');
        }
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBankInfo($validator);
            $userIds = $this->user_id ?? [];
            $bookIds = $this->book_id ?? [];

            // Check if book_id is not empty but user_id is empty
            if (!empty($bookIds) && empty($userIds)) {
                $validator->errors()->add('user_id', 'The user field is required when books are selected.');
            }

            // Check if user_id is not empty but book_id is empty
            if (!empty($userIds) && empty($bookIds)) {
                $validator->errors()->add('book_id', 'The book field is required when users are selected.');
            }

            $addresses = $this->input('addresses', []);
            $billingCount = 0;
            $shippingCount = 0;

            foreach ($addresses as $index => $address) {
                if (empty($address['address'])) {
                    $validator->errors()->add("addresses.{$index}.address", 'Address is required.');
                }
            }
            
            // 3. GST Validation
            $gstinNo = $this->input('compliance.gstin_no');
            $companyName = $this->input('company_name');
            $gstinRegistrationDate = $this->input('compliance.gstin_registration_date');
            $gstinLegalName = $this->input('compliance.gst_registered_name');
            if ($gstinNo) {
                $gstValidation = EInvoiceHelper::validateGstinName($gstinNo);
                if ($gstValidation['Status'] == 1) {
                    $gstData = json_decode($gstValidation['checkGstIn'], true);
                    $gstDataLegalName = trim($gstData['LegalName'] ?? ''); 
                    // if ($companyName && $companyName !== ($gstData['TradeName'] ?? '')) {
                    //     $validator->errors()->add(
                    //         'company_name', 
                    //         'Company name  does not match GSTIN record.'
                    //     );
                    // }
                    if ($gstinLegalName && strtolower($gstinLegalName) !== strtolower($gstDataLegalName)) {
                        $validator->errors()->add(
                            'compliance.gst_registered_name', 
                            'Legal name  does not match GSTIN record.'
                        );
                    }
                    // Validate GSTIN registration date
                    $gstRegistrationDate = $gstData['DtReg'] ?? null; 
                    if ($gstRegistrationDate && $gstinRegistrationDate && $gstinRegistrationDate !== $gstRegistrationDate) {
                        $validator->errors()->add(
                            'compliance.gstin_registration_date', 
                            'GSTIN registration date does not match GSTIN records. '
                        );
                    }
                    $this->addAddressValidationErrors($validator, $addresses, $gstData);
                } else {
                    $validator->errors()->add('compliance.gstin_no', 'The provided GSTIN number is invalid. Please verify and try again.');
                }
            }
        });
        
    }
}
