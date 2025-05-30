<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\UploadCustomerMaster;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use App\Services\ItemImportExportService;
use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Exception;
use stdClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class CustomerImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $successfulCustomers = [];
    protected $failedCustomers = [];
    protected $service;

    public function chunkSize(): int
    {
        return 500; 
    }

    public function __construct(ItemImportExportService $service)
    {
        $this->service = $service;
    }

    public function onSuccess(Customer $customer)
    {
        $this->successfulCustomers[] = [
            'customer_code' => $customer->customer_code,
            'company_name' => $customer->company_name,  
            'customer_type' => $customer->customer_type,
            'status' => 'success',
            'customer_remark' => 'Successfully uploaded',
        ];
    }
    
    public function onFailure($uploadedCustomer)
    {
        $this->failedCustomers[] = [
            'customer_code' => $uploadedCustomer->customer_code,
            'company_name' => $uploadedCustomer->company_name,
            'customer_type' => $uploadedCustomer->customer_type,
            'status' => 'failed',
            'remarks' => $uploadedCustomer->remarks,
        ];
    }

    public function getSuccessfulCustomers()
    {
        return $this->successfulCustomers;
    }

    public function getFailedCustomers()
    {
        return $this->failedCustomers;
    }

    public function model(array $row)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $batchNo = $this->service->generateBatchNo($organization->id, $organization->group_id, $organization->company_id, $user->id);
        $uploadedCustomer = null;
        $validatedData = [];
        $errors = [];
    
        DB::beginTransaction();
    
        try {

            $parentUrl = ConstantHelper::CUSTOMER_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            $customerCodeType = 'Manual';
    
            if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                $firstService = $services['services']->first();
                $serviceId = $firstService->service_id;
                $policyData = Helper::getPolicyByServiceId($serviceId);
                if ($policyData && isset($policyData['policyLevelData'])) {
                    $policyLevelData = $policyData['policyLevelData'];
                    $validatedData['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                    $validatedData['company_id'] = $policyLevelData['company_id'] ?? null;
                    $validatedData['organization_id'] = $policyLevelData['organization_id'] ?? null;
                } else {
                    $validatedData['group_id'] = $organization->group_id;
                    $validatedData['company_id'] = null;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = null;
                $validatedData['organization_id'] = null;
            }

            if ($services && $services['current_book']) {
                if (isset($services['current_book'])) {
                    $book=$services['current_book'];
                    if ($book) {
                        $parameters = new stdClass(); 
                        foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                            $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                            $parameters->{$paramName} = $param;
                        }
                        if (isset($parameters->customer_code_type) && is_array($parameters->customer_code_type)) {
                            $customerCodeType = $parameters->customer_code_type[0] ?? null;
                        }
                    }
             }
            }
            $customerInitials = strtoupper(substr($row['customer_name'], 0, 3)); 

            $gstinRegDate = $row['gstin_reg_date'] ?? null;
            $gstinRegistrationDate = null;
            
            if ($gstinRegDate) {
                if (is_numeric($gstinRegDate)) {
                    $gstinRegistrationDate = \Carbon\Carbon::createFromFormat('Y-m-d', '1900-01-01')
                        ->addDays($gstinRegDate - 2) 
                        ->format('Y-m-d');
                } else {
                    $gstinRegistrationDate = $gstinRegDate; 
                    \Log::warning("Non-numeric GSTIN registration date encountered: " . $gstinRegDate);
                }
            }
            
            $tdsWefDate = $row['tds_wef_date'] ?? null;
            $tdsWefDatee = null;
            
            if ($tdsWefDate) {
                if (is_numeric($tdsWefDate)) {
                    $tdsWefDatee = \Carbon\Carbon::createFromFormat('Y-m-d', '1900-01-01')
                        ->addDays($tdsWefDate - 2) 
                        ->format('Y-m-d');
                } else {
                    $tdsWefDatee = $tdsWefDate; 
                    \Log::warning("Non-numeric TDS WEF date encountered: " . $tdsWefDate);
                }
            }
            
            $uploadedCustomer = UploadCustomerMaster::create([
                'company_name' => $row['customer_name'] ?? null,
                'customer_initial' => $customerInitials ?? null,
                'customer_code' => $row['customer_code'] ?? null,
                'customer_code_type' => $customerCodeType ?? null,
                'customer_type' =>$row['customer_type'] ?? null,
                'organization_type' => $row['organization_type'] ?? null,
                'category' => $row['category'] ?? null,
                'subcategory' => $row['sub_category'] ?? null,
                'sales_person' => $row['sales_person'] ?? null,
                'currency' => $row['currency'] ?? null,
                'payment_term' => $row['payment_term'] ?? null,
                'country' => $row['country'] ?? null,
                'state' => $row['state'] ?? null,
                'city' => $row['city'] ?? null,
                'address' => $row['address'] ?? null,
                'pin_code' => $row['pin_code'] ?? null,
                'email' => $row['email_id'] ?? null,
                'phone' => $row['phone_no'] ?? null,
                'mobile' => $row['mobile_no'] ?? null,
                'whatsapp_number' => $row['whatsapp_no'] ?? null,
                'notification_mode' => $row['notification_mode'] ?? null,
                'pan_number' => $row['pan_no'] ?? null,
                'tin_number' => $row['tin_no'] ?? null,
                'aadhar_number' => $row['adhaar_no'] ?? null,
                'ledger_code' => $row['ledger_code'] ?? null,
                'ledger_group' => $row['ledger_group'] ?? null,
                'credit_limit' => $row['credit_limit'] ?? null,
                'credit_days' => $row['credit_days'] ?? null,
                'gst_applicable' => ($row['gst_registered'] ?? 'N') === 'Y' ? 1 : 0,
                'gstin_no' => $row['gstin_no'] ?? null,
                'gst_registered_name' => $row['gst_registered_name'] ?? null,
                'gstin_registration_date' => $gstinRegistrationDate ?? null,
                'tds_applicable' => ($row['tds_applicable'] ?? 'N') === 'Y' ? 1 : 0, 
                'wef_date' =>  $tdsWefDatee ?? null,
                'tds_certificate_no' => $row['tds_certificate_no'] ?? null,
                'tds_tax_percentage' => $row['tds_tax'] ?? null,
                'tds_category' => $row['tds_category'] ?? null,
                'tds_value_cab' => $row['tds_value_cap'] ?? null,
                'tan_number' => $row['tan_no'] ?? null,
                'status' => 'Processed',
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
                'organization_id' => $validatedData['organization_id'],
                'remarks' => "Processing customer upload",
                'batch_no' => $batchNo,
                'user_id' => $user->id,
            ]);
    
            DB::commit();
    
            $this->processCustomerFromUpload($uploadedCustomer);
    
            return $uploadedCustomer;
        } catch (Exception $e) {
            DB::rollback();
    
            Log::error("Error importing customer: " . $e->getMessage(), [
                'error' => $e,
                'row' => $row
            ]);
    
            if (isset($uploadedCustomer)) {
                $uploadedCustomer->update([
                    'status' => 'Failed',
                    'remarks' => "Error importing customer: " . $e->getMessage(),
                ]);
            }
    
            $this->onFailure($uploadedCustomer);
            throw new Exception("Error importing customer: " . $e->getMessage());
        }
    }
    private function processCustomerFromUpload(UploadCustomerMaster $uploadedCustomer)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $errors = [];

        $customerType = $uploadedCustomer->customer_type === 'R' ? 'Regular' : ( $uploadedCustomer->customer_type === 'C' ? 'Cash' : 'Regular');
        $customerInitials =$uploadedCustomer->customer_initial; 
        $customerCodeType = $uploadedCustomer->customer_code_type ?? 'Manual';

        $customerCode = null;
        if ($customerCodeType === 'Manual') {
            $customerCode =$uploadedCustomer->customer_code ?? null;
        } elseif ($customerCodeType === 'Auto' && !empty($customerInitials) && !empty($customerType)) {
            $customerCode = $this->service->generateCustomerCode($customerInitials, $customerType);
        }

        if (!empty($uploadedCustomer->category)) {
            try {
                $category = $this->service->getCategory($uploadedCustomer->category);
                if ($category) {
                    try {
                        if (!empty($uploadedCustomer->subcategory)) {
                            $subCategory = $this->service->getSubCategory($uploadedCustomer->subcategory, $category);
                        } 
                    } catch (Exception $e) {
                        $errors[] = "Error fetching sub-category: " . $e->getMessage();
                    }
                } else {
                    $errors[] = "Category not found: " . $uploadedCustomer->category;
                }
            } catch (Exception $e) {
                $errors[] = "Error fetching category: " . $e->getMessage();
            }
        } 
        
        if (!empty($uploadedCustomer->currency)) {
            try {
                $currencyId = $this->service->getCurrencyId($uploadedCustomer->currency);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        } 

        if (!empty($uploadedCustomer->payment_term)) {
            try {
                $paymentTermId = $this->service->getPaymentTermId($uploadedCustomer->payment_term);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        } 

        if (!empty($uploadedCustomer->sales_person)) {
            try {
                $salesPersonId = $this->service->getSalesPersonId($uploadedCustomer->sales_person);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        } 
    
        $organizationType = $uploadedCustomer->organization_type ?? 'Private Ltd'; 
        try {
            $organizationTypeId = $this->service->getOrganizationTypeId($organizationType);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (!empty($uploadedCustomer->ledger_code) && !empty($uploadedCustomer->ledger_group)) {
            try {
                $result = $this->service->getLedgerAndGroupIds($uploadedCustomer->ledger_code, $uploadedCustomer->ledger_group);
                if (isset($result['error'])) {
                    $errors[] = $result['error'];
                } else {
                    $ledgerId = $result['ledger_id'];
                    $ledgerGroupId = $result['ledger_group_id'];
                }
            } catch (Exception $e) {
                $errors[] = 'Error while fetching Ledger and Ledger Group: ' . $e->getMessage();
            }
        } 

        if (!empty($uploadedCustomer->country) && !empty($uploadedCustomer->state) && !empty($uploadedCustomer->city)) {
        try {
                $locationIds = $this->service->getLocationIds($uploadedCustomer->country, $uploadedCustomer->state, $uploadedCustomer->city);
            } catch (Exception $e) {
                $errors[] = 'Error while fetching location IDs: ' . $e->getMessage();
            }
        } 
        try {
            $customerData = [
                'organization_type_id' => $organizationTypeId ?? null,
                'customer_code_type' => $uploadedCustomer->customer_code_type ?? null,
                'customer_code' => $customerCode ?? null,
                'customer_initial' => $uploadedCustomer->customer_initial ?? null,
                'company_name' => $uploadedCustomer->company_name ?? null,
                'customer_type' => $customerType ?? null,
                'category_id' => $category->id ?? null,
                'subcategory_id' => $subCategory->id ?? null,
                'currency_id' => $currencyId ?? null,
                'payment_terms_id' => $paymentTermId ?? null,
                'email' => $uploadedCustomer->email ?? null,
                'phone' => $uploadedCustomer->phone ?? null,
                'mobile' => $uploadedCustomer->mobile ?? null,
                'whatsapp_number' => $uploadedCustomer->whatsapp_number ?? null,
                'notification' => $uploadedCustomer->notification_mode ?? null,
                'pan_number' => $uploadedCustomer->pan_number ?? null,
                'tin_number' => $uploadedCustomer->tin_number ?? null,
                'aadhar_number' => $uploadedCustomer->aadhar_number ?? null,
                'ledger_id' => $ledgerId ?? null,
                'ledger_group_id' => $ledgerGroupId ?? null,
                'sales_person_id' => $salesPersonId ?? null,
                'credit_limit' => $uploadedCustomer->credit_limit ?? null,
                'credit_days' => $uploadedCustomer->credit_days ?? null,
                'status' => 'active',
                'created_by'=> $user->auth_user_id ?? null,
                'group_id' => $uploadedCustomer->group_id ?? null,
                'company_id' => $uploadedCustomer->company_id ?? null,
                'organization_id' => $uploadedCustomer->organization_id ?? null,
                'gst_applicable' => $uploadedCustomer->gst_applicable ?? 0,
                'gstin_no' => $uploadedCustomer->gstin_no ?? null,
                'gst_registered_name' => $uploadedCustomer->gst_registered_name ?? null,
                'gstin_registration_date' => $uploadedCustomer->gstin_registration_date ?? null,
                'tds_applicable' => $uploadedCustomer->tds_applicable ?? 0,
                'wef_date' => $uploadedCustomer->wef_date ?? null,
                'tds_certificate_no' => $uploadedCustomer->tds_certificate_no ?? null,
                'tds_tax_percentage' => $uploadedCustomer->tds_tax_percentage ?? null,
                'tds_category' => $uploadedCustomer->tds_category ?? null,
                'tds_value_cab' => $uploadedCustomer->tds_value_cab ?? null,
                'tan_number' => $uploadedCustomer->tan_number ?? null,
                'country_id' => $locationIds['country_id'] ?? null,
                'state_id' => $locationIds['state_id'] ?? null,
                'city_id' => $locationIds['city_id'] ?? null,
                'pincode' => $uploadedCustomer->pin_code,
                'address' => $uploadedCustomer->address,
            ];
        
            $customer = new Customer($customerData);

            $rules = [
                'organization_type_id' => 'nullable|exists:erp_organization_types,id',
                'customer_code' => [
                    'required_if:customer_code_type,Manual', 
                    'string',
                    'max:255', 
                    Rule::unique('erp_customers', 'customer_code')
                        ->where('group_id', $uploadedCustomer->group_id) 
                        ->whereNull('deleted_at')
                 ],
                 'customer_initial' => 'nullable|string|max:255',
                 'company_name' => [
                         'required',
                         'string',
                         'max:255',
                         Rule::unique('erp_customers', 'company_name')
                         ->where('group_id', $uploadedCustomer->group_id) 
                         ->whereNull('deleted_at')
                     ],
                 'country_id' => 'nullable|exists:countries,id',
                 'state_id' => 'nullable|exists:states,id',
                 'city_id' => 'nullable|exists:cities,id',
                 'pin_code' => 'nullable|regex:/^\d{6}$/',
                 'address' => 'nullable|string',
                 'customer_type' => 'required|string',
                 'customer_sub_type' => 'nullable|string',
                 'category_id' => 'nullable|exists:erp_categories,id',
                 'subcategory_id' => 'nullable|exists:erp_categories,id',
                 'currency_id' => 'required|exists:mysql_master.currency,id',
                 'payment_terms_id' => 'required|exists:erp_payment_terms,id',
                 'email' => [
                     'nullable',
                     'email',
                     'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                     ],
                 'phone' => 'nullable|regex:/^\d{10,12}$/',
                 'mobile' => 'nullable|regex:/^\d{10,12}$/',
                 'whatsapp_number' => 'nullable|regex:/^\d{10,12}$/',
                 'notification' => 'nullable',
                 'notification.*' => 'nullable',
                 'pan_number' => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
                 'tin_number' => 'nullable|regex:/^\d{10}$/',
                 'aadhar_number' => 'nullable|regex:/^\d{12}$/',
                 'ledger_id' => 'nullable|exists:erp_ledgers,id', 
                 'ledger_group_id' => 'nullable|exists:erp_groups,id', 
                 'credit_limit' => 'nullable|numeric|min:0',
                 'credit_days' => 'nullable|integer|min:0|max:365',
                 'gst_applicable' => 'nullable',
                 'gstin_no' => ['nullable', 'string', 'size:15', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
                 'gst_registered_name' => 'nullable|string|max:255',
                 'gstin_registration_date' => 'nullable|date',
                 'tds_applicable' => 'nullable',
                 'wef_date' => [
                     'nullable', 
                     'date', 
                     'required_if:tds_applicable,1'
                 ],
                 'tds_certificate_no' => [
                     'nullable', 
                     'string', 
                     'max:255', 
                     'required_if:tds_applicable,1'
                 ],
                 'tds_tax_percentage' => [
                     'nullable', 
                     'numeric', 
                     'max:100', 
                     'required_if:tds_applicable,1'
                 ],
                 'tds_category' => [
                     'nullable', 
                     'string', 
                     'max:255', 
                     'required_if:tds_applicable,1'
                 ],
                 'tds_value_cab' => [
                     'nullable', 
                     'numeric', 
                     'required_if:tds_applicable,1'
                 ],
                 'tan_number' => 'nullable|string|max:255',
                 'status' => 'nullable|string|max:255',
                 'group_id' => 'nullable',
                 'company_id' => 'nullable',
                 'organization_id' => 'nullable',
             ];
             
             $customMessages = [
                 'required' => 'The :attribute field is required.',
                 'string' => 'The :attribute must be a string.',
                 'max' => 'The :attribute may not be greater than :max characters.',
                 'in' => 'The :attribute must be one of the following values: :values.',
                 'exists' => 'The selected :attribute is invalid.',
                 'unique' => 'The :attribute has already been taken.',
                 'regex' => 'The :attribute format is invalid.',
                 'min' => 'The :attribute must be at least :min.',
                 'nullable' => 'The :attribute field may be null.',
                 'array' => 'The :attribute must be an array.',
                 'integer' => 'The :attribute must be an integer.',
                 'email' => 'The :attribute must be a valid email address.',
                 'size' => 'The :attribute must be :size characters.',
                 'numeric' => 'The :attribute must be a number.',
                 'date' => 'The :attribute must be a valid date.',
                 'mimes' => 'The :attribute must be a file of type: :values.',
                 
                 'customer_code.required' => 'Customer code is mandatory and cannot be empty.',
                 'customer_code.max' => 'Customer code should not exceed 255 characters.',
                 'customerr_code.unique' => 'The customer code you entered is already in use. Please choose a different one.',
                 
                 'company_name.required' => 'The customer name is required.',
                 'company_name.string' => 'Customer name must be a valid string.',
                 'company_name.max' => 'Customer name cannot exceed 255 characters.',
                 
                 'country_id.exists' => 'The country selected is invalid.',
                 'state_id.exists' => 'The state selected is invalid.',
                 'city_id.exists' => 'The city selected is invalid.',
                 
                 'pin_code.regex' => 'Pin code must be a 6-digit number.',
                 
                 'address.regex' => 'Address format is invalid. Please enter a valid address.',
                 
                 'customer_type.required' => 'Customer type is a required field.',
                 'customer_type.string' => 'Customer type must be a string.',
                 
                 'category_id.exists' => 'The category selected is invalid.',
                 
                 'subcategory_id.exists' => 'The subcategory selected is invalid.',
                 
                 'currency_id.required' => 'Currency is required and must exist.',
                 'currency_id.exists' => 'The currency selected is invalid.',
                 
                 'payment_terms_id.required' => 'Payment terms are required and must exist.',
                 'payment_terms_id.exists' => 'The payment term selected is invalid.',
                 
                 'email.email' => 'The email address is not valid.',
                 'email.regex' => 'Email format is invalid.',
                 
                 'phone.regex' => 'Phone number must be a valid 10-12 digit number.',
                 'mobile.regex' => 'Mobile number must be a valid 10-12 digit number.',
                 'whatsapp_number.regex' => 'WhatsApp number must be a valid 10-12 digit number.',
                 
                 'pan_number.regex' => 'PAN number format is invalid. Please use the correct format: AAAAA9999A.',
                 
                 'tin_number.regex' => 'TIN number must be a 10-digit number.',
                 
                 'aadhar_number.regex' => 'Aadhar number must be a 12-digit number.',
                 
                 'ledger_id.exists' => 'The ledger ID selected is invalid.',
                 'ledger_group_id.exists' => 'The ledger group ID selected is invalid.',
                 
                 'credit_limit.numeric' => 'Credit limit must be a valid number.',
                 'credit_limit.min' => 'Credit limit must be at least 0.',
                 
                 'credit_days.integer' => 'Credit days must be an integer.',
                 'credit_days.min' => 'Credit days must be at least 0.',
                 'credit_days.max' => 'Credit days cannot exceed 365.',
                 
                 'gstin_no.regex' => 'GSTIN number format is invalid.',
                 'gstin_no.size' => 'GSTIN number must be exactly 15 characters.',
                 
                 'gst_registered_name.string' => 'GST Registered Name must be a string.',
                 'gst_registered_name.max' => 'GST Registered Name should not exceed 255 characters.',
                 
                 'gstin_registration_date.date' => 'GST Registration date must be a valid date.',
                 
                 'tds_applicable' => 'TDS applicability must be specified.',
                 
                 'wef_date.required_if' => 'The "Date of Effect" is required if TDS is applicable.',
                 'wef_date.date' => 'Date of Effect must be a valid date.',
                 
                 'tds_certificate_no.required_if' => 'TDS Certificate Number is required if TDS is applicable.',
                 'tds_certificate_no.string' => 'TDS Certificate Number must be a string.',
                 'tds_certificate_no.max' => 'TDS Certificate Number cannot exceed 255 characters.',
                 
                 'tds_tax_percentage.required_if' => 'TDS Tax Percentage is required if TDS is applicable.',
                 'tds_tax_percentage.numeric' => 'TDS Tax Percentage must be a number.',
                 'tds_tax_percentage.max' => 'TDS Tax Percentage cannot exceed 100.',
                 
                 'tds_category.required_if' => 'TDS Category is required if TDS is applicable.',
                 'tds_category.string' => 'TDS Category must be a string.',
                 'tds_category.max' => 'TDS Category should not exceed 255 characters.',
                 
                 'tds_value_cab.required_if' => 'TDS Value Cap is required if TDS is applicable.',
                 'tds_value_cab.numeric' => 'TDS Value Cap must be a number.',
                 
                 'tan_number.string' => 'TAN number must be a string.',
                 'tan_number.max' => 'TAN number cannot exceed 255 characters.',
     
                 'status.string' => 'Status must be a string.',
                 'status.max' => 'Status cannot exceed 255 characters.',
                 
                 'group_id' => 'The group ID is not valid.',
                 'company_id' => 'The company ID is not valid.',
                 'organization_id' => 'The organization ID is not valid.',
             ];

            $validator = Validator::make($customerData, $rules, $customMessages);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
            }

            $addressData = [
                'country_id' => $locationIds['country_id'] ?? null,
                'state_id' => $locationIds['state_id'] ?? null,
                'city_id' => $locationIds['city_id'] ?? null,
                'pincode' => $uploadedCustomer->pin_code,
                'address' => $uploadedCustomer->address,
                'is_billing' => 1,
                'is_shipping' => 1,
            ];
    
            $gstAndAddressData = [
                'company_name' => $uploadedCustomer->company_name ?? null,
                'addresses' => [$addressData], 
                'compliance' => [
                    'gst_applicable' => $uploadedCustomer->gst_applicable ?? null,
                    'gstin_no' => $uploadedCustomer->gstin_no ?? null,
                    'gstin_registration_date' => $uploadedCustomer->gstin_registration_date ?? null,
                    'gst_registered_name' => $uploadedCustomer->gst_registered_name ?? null,
                ],
            ];
    
            $gstAddressErrors = $this->service->validateGstAndAddresses($gstAndAddressData);
    
            if (!empty($gstAddressErrors)) {
                $errors = array_merge($errors, $gstAddressErrors);
                  $uploadedCustomer->update([
                    'status' => 'Failed',
                    'remarks' => implode(', ', $errors),
                ]);
                $this->onFailure($uploadedCustomer);
                return; 
            }

            $customer->save();
            $customer->compliances()->create([
                'gst_applicable' =>$uploadedCustomer->gst_applicable ?? 0,
                'gstin_no' => $uploadedCustomer->gstin_no ?? null,
                'gst_registered_name' => $uploadedCustomer->gst_registered_name ?? null,
                'gstin_registration_date' => $uploadedCustomer->gstin_registration_date ?? null,
                'tds_applicable' =>$uploadedCustomer->tds_applicable ?? 0,
                'wef_date' => $uploadedCustomer->wef_date ?? null,
                'tds_certificate_no' => $uploadedCustomer->tds_certificate_no ?? null,
                'tds_tax_percentage' => $uploadedCustomer->tds_tax_percentage ?? null,
                'tds_category' => $uploadedCustomer->tds_category ?? null,
                'tds_value_cab' => $uploadedCustomer->tds_value_cab ?? null,
                'tan_number' => $uploadedCustomer->tan_number ?? null,
                'status' => 'active',
            ]);

            if (!empty($locationIds['country_id']) && !empty($locationIds['state_id']) && !empty($locationIds['city_id']) && !empty($uploadedCustomer->pin_code) && !empty($uploadedCustomer->address)) {
                $addressData = [
                    'country_id' => $locationIds['country_id'],
                    'state_id' => $locationIds['state_id'],
                    'city_id' => $locationIds['city_id'],
                    'pincode' => $uploadedCustomer->pin_code,
                    'address' => $uploadedCustomer->address,
                    'is_billing' => 1,
                    'is_shipping' => 1,
                ];
                $customer->addresses()->create($addressData);
            } 
            $uploadedCustomer->update([
                'status' => 'Success',
                'remarks' => 'Successfully imported customer.',
            ]);

            $this->onSuccess($customer);

    
        } catch (Exception $e) {
            $errors[] = "Error creating customer: " . $e->getMessage();
            $uploadedCustomer->update([
                'status' => 'Failed',
                'remarks' => implode(', ', $errors),
            ]);
            $this->onFailure($uploadedCustomer);
            Log::error("Error creating customer from upload: " . $e->getMessage(), ['error' => $e]);
            throw new Exception("Error creating customer from upload: " . $e->getMessage());
        }
    }

}
