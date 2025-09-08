<?php

namespace App\Imports;

use App\Models\Vendor;
use App\Models\UploadVendorMaster;
use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ItemImportExportService;
use Illuminate\Validation\Rule;
use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
use Illuminate\Support\Facades\DB;
use App\Helpers\EInvoiceHelper;
use Exception;
use stdClass;
use Illuminate\Support\Collection;

class VendorImport implements ToCollection, WithHeadingRow, WithChunkReading
{
  protected $successfulVendors = [];
    protected $failedVendors = [];
    protected $service;

    public function chunkSize(): int
    {
        return 500; 
    }

    public function __construct(ItemImportExportService $service)
    {
        $this->service = $service;
    }

    public function onSuccess(Vendor $vendor)
    {
        $this->successfulVendors[] = [
            'vendor_code' => $vendor->vendor_code, 
            'company_name' => $vendor->company_name,
            'vendor_type' => $vendor->vendor_type,
            'status' => 'success',
            'vendor_remark' => 'Successfully uploaded',
        ];
    }
    
    public function onFailure($uploadedVendor)
    {
        $this->failedVendors[] = [
            'vendor_code' => $uploadedVendor->vendor_code,
            'company_name' => $uploadedVendor->company_name,
            'vendor_type' => $uploadedVendor->vendor_type,
            'status' => 'failed',
            'remarks' => $uploadedVendor->remarks,
        ];
    }

    public function getSuccessfulVendors()
    {
        return $this->successfulVendors;
    }

    public function getFailedVendors()
    {
        return $this->failedVendors;
    }
   protected function getServiceData($organization, $services)
    {
        $validatedData = [];
        $vendorCodeType = 'Manual';

        if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
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
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        if ($services && isset($services['current_book'])) {
            $book = $services['current_book'];
            if ($book) {
                $parameters = new stdClass();
                foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                    $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                    $parameters->{$paramName} = $param;
                }
                if (isset($parameters->vendor_code_type) && is_array($parameters->vendor_code_type)) {
                    $vendorCodeType = $parameters->vendor_code_type[0] ?? null;
                }
            }
        }

        return [
            'validatedData' => $validatedData,
            'vendorCodeType' => $vendorCodeType,
        ];
    }
    public function collection(Collection $rows)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $batchNo = $this->service->generateBatchNo($organization->id, $organization->group_id, $organization->company_id, $user->id);
        $uploadedVendors = collect();

        $parentUrl = ConstantHelper::VENDOR_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $serviceData = $this->getServiceData($organization, $services);
        $validatedData = $serviceData['validatedData'];
        $vendorCodeType = $serviceData['vendorCodeType'];

        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                
                $cleanedName = preg_replace('/[^a-zA-Z0-9\s]/', '', $row['vendor_name']);
                $words = array_values(array_filter(preg_split('/\s+/', trim($cleanedName))));

                if (count($words) === 1) {
                    $vendorInitials = strtoupper(substr($words[0], 0, 3));
                } elseif (count($words) === 2) {
                    $vendorInitials = strtoupper(substr($words[0], 0, 2) . substr($words[1], 0, 1));
                } elseif (count($words) >= 3) {
                    $vendorInitials = strtoupper($words[0][0] . $words[1][0] . $words[2][0]);
                } else {
                    $vendorInitials = '';
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
                $gstApplicable = ($row['gst_registered'] ?? 'N') === 'Y' ? 1 : 0;
                $gstinNo = $row['gstin_no'] ?? null;

                if ($gstApplicable === 0) {
                    $gstinNo = null;
                }
                $uploadedVendor = UploadVendorMaster::create([
                    'company_name' => $row['vendor_name'] ?? null,
                    'vendor_initial'=>  $vendorInitials ?? null,
                    'vendor_code' => $row['vendor_code'] ?? null,
                    'subcategory' => $row['group'] ?? null,
                    'currency' => $row['currency'] ?? null,
                    'payment_term' => $row['payment_term'] ?? null,
                    'vendor_type' => $row['vendor_type'],
                    'vendor_sub_type' =>$row['sub_type'] ,
                    'organization_type' => $row['organization_type'] ?? null,
                    'vendor_code_type' => $vendorCodeType ?? null,
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
                    'gst_applicable' => $gstApplicable,
                    'gstin_no' => $gstinNo,
                    'tds_applicable' => ($row['tds_applicable'] ?? 'N') === 'Y' ? 1 : 0,
                    'wef_date' =>  $tdsWefDatee,
                    'tds_certificate_no' => $row['tds_certificate_no'] ?? null,
                    'tds_tax_percentage' => $row['tds_tax'] ?? null,
                    'tds_category' => $row['tds_category'] ?? null,
                    'tds_value_cab' => $row['tds_value_cap'] ?? null,
                    'tan_number' => $row['tan_no'] ?? null,
                    'msme_registered' => ($row['msme_registered'] ?? 'N') === 'Y' ? 1 : 0,
                    'msme_no' => $row['msme_no'] ?? null,
                    'msme_type' =>$row['msme_type'],
                    'status' => 'Processed',
                    'group_id' => $validatedData['group_id'],
                    'company_id' => $validatedData['company_id'],
                    'organization_id' => $validatedData['organization_id'],
                    'remarks' => "Processing vendor upload",
                    'batch_no' => $batchNo,
                    'user_id' => $user->auth_user_id,
                ]);
                DB::commit();
                $uploadedVendors->push($uploadedVendor);
            } catch (Exception $e) {
                DB::rollback();
                Log::error("Error importing vendor: " . $e->getMessage(), [
                    'error' => $e,
                    'row' => $row
                ]);
                if (isset($uploadedVendor)) {
                    $uploadedVendor->update([
                        'status' => 'Failed',
                        'remarks' => "Error importing vendor: " . $e->getMessage(),
                    ]);
                }
                $this->onFailure($uploadedVendor ?? null);
            }
        }
        if ($uploadedVendors->isNotEmpty()) {
          $this->processVendorFromUpload($uploadedVendors);
        }
      
    }
  public function processVendorFromUpload(Collection $uploadedVendors)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $parentUrl = ConstantHelper::VENDOR_SERVICE_ALIAS; 
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $book = ($services && isset($services['current_book'])) ? $services['current_book'] : null;
        $uploadedVendors->each(function ($uploadedVendor) use ($user, $organization, $services, $book) {
            $errors = [];
            $subCategory = null;
            $currencyId = null;
            $paymentTermId = null;
            $organizationTypeId = null;
            $ledgerId = null;
            $ledgerGroupId = null;
            $locationIds = [];
            $countryId = null;
            $stateId = null;
            $cityId = null;
            $pincodeId = null;

            if (!empty($uploadedVendor->subcategory)) {
                try {
                    $subCategory = $this->service->getSubCategory($uploadedVendor->subcategory);
                } catch (Exception $e) {
                    $errors[] = "Error fetching subcategory: " . $e->getMessage();
                }
           }
            $vendorType = $uploadedVendor->vendor_type === 'R' ? 'Regular' : ($uploadedVendor->vendor_type === 'C' ? 'Cash' : 'Regular');
            $vendorCodeType = $uploadedVendor->vendor_code_type ?? 'Manual';
            
            $vendorInitials = $uploadedVendor->vendor_initial; 
            $vendorCode = null;

            if ($vendorCodeType === 'Manual') {
                $vendorCode = $uploadedVendor->vendor_code ?? null; 
            } elseif ($vendorCodeType === 'Auto' && !empty($vendorInitials) && !empty($vendorType)) {
                $vendorCode = $this->service->generateVendorCode($vendorInitials, $vendorType);
            }

            if (!empty($uploadedVendor->currency)) {
                try {
                    $currencyId = $this->service->getCurrencyId($uploadedVendor->currency);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            } 
        
            if (!empty($uploadedVendor->payment_term)) {
                try {
                    $paymentTermId = $this->service->getPaymentTermId($uploadedVendor->payment_term);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            } 

            $organizationType = $uploadedVendor->organization_type ?? 'Private Limited'; 
            try {
                $organizationTypeId = $this->service->getOrganizationTypeId($organizationType);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
     
            if (!empty($uploadedVendor->ledger_code) && !empty($uploadedVendor->ledger_group)) {
                try {
                    $result = $this->service->getLedgerAndGroupIds($uploadedVendor->ledger_code, $uploadedVendor->ledger_group);
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
        
            if (!empty($uploadedVendor->country) || !empty($uploadedVendor->state) || !empty($uploadedVendor->city)) {
                try {
                    $locationIds = $this->service->getLocationIds($uploadedVendor->country, $uploadedVendor->state, $uploadedVendor->city, $uploadedVendor->pin_code);

                    if (!empty($locationIds['country_id'])) {
                        $countryId = $locationIds['country_id'];
                    }

                    if (!empty($locationIds['state_id'])) {
                        $stateId = $locationIds['state_id'];
                    }

                    if (!empty($locationIds['city_id'])) {
                        $cityId = $locationIds['city_id'];
                    }

                    if (!empty($locationIds['pincode_id'])) {
                        $pincodeId = $locationIds['pincode_id'];
                    }

                    if (!empty($locationIds['errors'])) {
                        foreach ($locationIds['errors'] as $field => $message) {
                            $errors[] = $message;
                        }
                    }

                } catch (Exception $e) {
                    $errors[] = 'Error while fetching location: ' . $e->getMessage();
                }
            }

            $vendorSubType = $uploadedVendor->vendor_sub_type === 'T' ? 'Transporter' : 'Regular';
        
            $msmeType = null;
            if (!empty($uploadedVendor->msme_type)) {
                if ($uploadedVendor->msme_type === 'mi') {
                    $msmeType = 'Micro';
                } elseif ($uploadedVendor->msme_type === 'sm') {
                    $msmeType = 'Small';
                } elseif ($uploadedVendor->msme_type === 'me') {
                    $msmeType = 'Medium';
                }
            }
        
            try {
                $uploadedVendorData = [
                    'organization_type_id' => $organizationTypeId ?? null,
                    'vendor_code_type' => $uploadedVendor->vendor_code_type ?? null,
                    'vendor_code' => $vendorCode ?? null,
                    'vendor_initial' => $vendorInitials ?? null,
                    'company_name' => $uploadedVendor->company_name ?? null,
                    'vendor_type' => $vendorType,
                    'vendor_sub_type' => $vendorSubType,
                    'subcategory_id' => $subCategory->id ?? null,
                    'currency_id' => $currencyId ?? null,
                    'payment_terms_id' => $paymentTermId ?? null,
                    'email' => $uploadedVendor->email ?? null,
                    'phone' => $uploadedVendor->phone ?? null,
                    'mobile' => $uploadedVendor->mobile ?? null,
                    'whatsapp_number' => $uploadedVendor->whatsapp_number ?? null,
                    'notification' => $uploadedVendor->notification_mode ?? null,
                    'pan_number' => $uploadedVendor->pan_number ?? null,
                    'tin_number' => $uploadedVendor->tin_number ?? null,
                    'aadhar_number' => $uploadedVendor->aadhar_number ?? null,
                    'ledger_id' => $ledgerId ?? null,
                    'ledger_group_id' => $ledgerGroupId ?? null,
                    'credit_limit' => $uploadedVendor->credit_limit ?? null,
                    'credit_days' => $uploadedVendor->credit_days ?? null,
                    'created_by'=> $user->auth_user_id ?? null,
                    'group_id' => $uploadedVendor->group_id ?? null,
                    'company_id' => $uploadedVendor->company_id ?? null,
                    'organization_id' => null,
                    'gst_applicable' =>$uploadedVendor->gst_applicable ?? 0,
                    'gstin_no' => $uploadedVendor->gstin_no ?? null,
                    'gst_registered_name' => $uploadedVendor->gst_registered_name ?? null,
                    'gstin_registration_date' => $uploadedVendor->gstin_registration_date ?? null,
                    'tds_applicable' =>$uploadedVendor->tds_applicable ?? 0,
                    'wef_date' => $uploadedVendor->wef_date ?? null,
                    'tds_certificate_no' => $uploadedVendor->tds_certificate_no ?? null,
                    'tds_tax_percentage' => $uploadedVendor->tds_tax_percentage ?? null,
                    'tds_category' => $uploadedVendor->tds_category ?? null,
                    'tds_value_cab' => $uploadedVendor->tds_value_cab ?? null,
                    'tan_number' => $uploadedVendor->tan_number ?? null,
                    'msme_registered' =>$uploadedVendor->msme_registered ?? 0,
                    'msme_no' => $uploadedVendor->msme_no ?? null,
                    'msme_type' => $msmeType,
                    'country_id' => $locationIds['country_id'] ?? null,
                    'state_id' => $locationIds['state_id'] ?? null,
                    'city_id' => $locationIds['city_id'] ?? null,
                    'pincode_master_id' => $locationIds['pincode_id'] ?? null,
                    'pincode' => $locationIds['pincode'] ?? null,
                    'address' => $uploadedVendor->address,
                ];
                
                $rules = [
                   'organization_type_id' => 'nullable|exists:erp_organization_types,id',
                   'vendor_code' => [
                        'required_if:vendor_code_type,Manual', 
                        'max:255',
                      Rule::unique('erp_vendors', 'vendor_code')
                        ->where(function ($query) use ($uploadedVendor) {
                            if ($uploadedVendor->group_id !== null) {
                                $query->where('group_id', $uploadedVendor->group_id);
                            }
                            if ($uploadedVendor->company_id !== null) {
                                $query->where(function ($q) use ($uploadedVendor) {
                                    $q->where('company_id', $uploadedVendor->company_id)
                                    ->orWhereNull('company_id');
                                });
                            }
                            if ($uploadedVendor->organization_id !== null) {
                                $query->where(function ($q) use ($uploadedVendor) {
                                    $q->where('organization_id', $uploadedVendor->organization_id)
                                    ->orWhereNull('organization_id');
                                });
                            }
                            $query->whereNull('deleted_at');
                        }),
                    ],
                    'vendor_initial' => 'nullable|string|max:255',
                    'company_name' => [
                            'required',
                            'string',
                            'max:255',
                            Rule::unique('erp_vendors', 'company_name')
                            ->where(function ($query) use ($uploadedVendor) {
                                if ($uploadedVendor->group_id !== null) {
                                    $query->where('group_id', $uploadedVendor->group_id);
                                }
                                if ($uploadedVendor->company_id !== null) {
                                    $query->where(function ($q) use ($uploadedVendor) {
                                        $q->where('company_id', $uploadedVendor->company_id)
                                        ->orWhereNull('company_id');
                                    });
                                }
                                if ($uploadedVendor->organization_id !== null) {
                                    $query->where(function ($q) use ($uploadedVendor) {
                                        $q->where('organization_id', $uploadedVendor->organization_id)
                                        ->orWhereNull('organization_id');
                                    });
                                }
                                $query->whereNull('deleted_at');
                            }),
                        ],
                    'country_id' => 'nullable|exists:countries,id',
                    'state_id' => 'nullable|exists:states,id',
                    'city_id' => 'nullable|exists:cities,id',
                    'pin_code' => 'nullable|regex:/^\d{6}$/',
                    'address' => 'nullable|string',
                    'vendor_type' => 'required|string',
                    'vendor_sub_type' => 'nullable|string',
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
                    'msme_registered' => 'nullable',
                    'msme_no' => 'nullable|string|max:255',
                    'msme_type' => 'nullable|string|max:255',
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
                    
                    'vendor_code.required' => 'Vendor code is mandatory and cannot be empty.',
                    'vendor_code.string' => 'Vendor code must be a string.',
                    'vendor_code.max' => 'Vendor code should not exceed 255 characters.',
                    'vendor_code.unique' => 'The vendor code you entered is already in use. Please choose a different one.',
                    
                    'company_name.required' => 'The vendor name is required.',
                    'company_name.string' => 'Vendor name must be a valid string.',
                    'company_name.max' => 'Vendor name cannot exceed 255 characters.',
                    'company_name.unique' => 'Vendor name already exists. Please choose a different name.',

                    'country_id.exists' => 'The country selected is invalid.',
                    'state_id.exists' => 'The state selected is invalid.',
                    'city_id.exists' => 'The city selected is invalid.',
                    
                    'pin_code.regex' => 'Pin code must be a 6-digit number.',
                    
                    'address.regex' => 'Address format is invalid. Please enter a valid address.',
                    
                    'vendor_type.required' => 'Vendor type is a required field.',
                    'vendor_type.string' => 'Vendor type must be a string.',
                    
                    'subcategory_id.exists' => 'The group selected is invalid.',
                    'subcategory_id.required' => 'The group field is required.',
                    
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
                    
                    'msme_registered' => 'MSME Registration status must be specified.',
                    
                    'msme_no.string' => 'MSME Number must be a string.',
                    'msme_no.max' => 'MSME Number cannot exceed 255 characters.',
                    
                    'msme_type.string' => 'MSME Type must be a string.',
                    'msme_type.max' => 'MSME Type cannot exceed 255 characters.',
                    
                    'status.string' => 'Status must be a string.',
                    'status.max' => 'Status cannot exceed 255 characters.',
                    
                    'group_id' => 'The group ID is not valid.',
                    'company_id' => 'The company ID is not valid.',
                    'organization_id' => 'The organization ID is not valid.',
                ];

                $validator = Validator::make($uploadedVendorData, $rules, $customMessages);

                if ($validator->fails()) {
                    $errors = array_merge($errors, $validator->errors()->all());
                }

                $addressData = [
                    'country_id' => $locationIds['country_id'] ?? null,
                    'state_id' => $locationIds['state_id'] ?? null,
                    'city_id' => $locationIds['city_id'] ?? null,
                    'pincode_id' => $locationIds['pincode_id'] ?? null,
                    'address' => $uploadedVendor->address,
                    'is_billing' => 1,
                    'is_shipping' => 0,
                ];
                $gstAndAddressData = [
                    'company_name' => $uploadedVendor->company_name ?? null,
                    'addresses' => [$addressData], 
                    'compliance' => [
                        'gst_applicable' => $uploadedVendor->gst_applicable ?? null,
                        'gstin_no' => $uploadedVendor->gstin_no ?? null,
                    ],
                ];

                $gstAddressErrors = $this->service->validateGstAndAddresses($gstAndAddressData);
        
                if (!empty($gstAddressErrors)) {
                    $errors = array_merge($errors, $gstAddressErrors);
                }
                if (!empty($errors)){
                    if (isset($uploadedVendor)) {
                        $uploadedVendor->update([
                            'status' => 'Failed',
                            'remarks' => implode(', ', $errors),
                        ]);
                        $this->onFailure($uploadedVendor);
                    }
                    return; 
                 }


                $vendor = new Vendor($uploadedVendorData);
                $vendor->document_status = ConstantHelper::DRAFT;
                $vendor->status = ConstantHelper::DRAFT; 
               
                $vendor->book_id = $book ? $book->id : null;

                $vendor->save();

                $bookId = $vendor->book_id;
                $docId = $vendor->id;
                $remarks = null; 
                $attachments = null; 
                $currentLevel = $vendor->approval_level ?? 1;
                $revisionNumber = $vendor->revision_number ?? 0;
                $actionType = 'submit';
                $modelName = get_class($vendor);
                $totalValue = 0;

                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue,  $modelName);
                
                $document_status = $approveDocument['approvalStatus'];
                $vendor->document_status = $document_status;

                if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                    $vendor->status = ConstantHelper::ACTIVE;
                } else {
                    $vendor->status = $document_status;
                }

                $vendor->save();

               if (isset($uploadedVendor->gst_applicable) && $uploadedVendor->gst_applicable == 1) {
                    $gstValidation = EInvoiceHelper::validateGstinName($uploadedVendor->gstin_no);
                    if ($gstValidation['Status'] == 1) {
                        $gstDetails = json_decode($gstValidation['checkGstIn'], true);
                    } else {
                        $gstDetails = null;
                    }
                } else {
                   $gstDetails = null;
                }
               $compliancesData = [
                    'gst_applicable' =>$uploadedVendor->gst_applicable ?? 0,
                    'gstin_no' => $uploadedVendor->gstin_no ?? null,
                    'gstin_registration_date' => $gstDetails ? ($gstDetails['DtReg'] ?? null) : null,
                    'gst_registered_name' => $gstDetails ? ($gstDetails['LegalName'] ?? null) : null,
                    'tds_applicable' =>$uploadedVendor->tds_applicable ?? 0,
                    'wef_date' => $uploadedVendor->wef_date ?? null,
                    'tds_certificate_no' => $uploadedVendor->tds_certificate_no ?? null,
                    'tds_tax_percentage' => $uploadedVendor->tds_tax_percentage ?? null,
                    'tds_category' => $uploadedVendor->tds_category ?? null,
                    'tds_value_cab' => $uploadedVendor->tds_value_cab ?? null,
                    'tan_number' => $uploadedVendor->tan_number ?? null,
                    'msme_registered' =>$uploadedVendor->msme_registered ?? 0,
                    'msme_no' => $uploadedVendor->msme_no ?? null,
                    'msme_type' => $msmeType,
                    'status' => 'active',
                ];
           
                if (isset($uploadedVendor->gst_applicable)) {
                    $vendor->compliances()->create($compliancesData);
                }

                if (!empty($locationIds['country_id']) && !empty($locationIds['state_id']) && !empty($locationIds['city_id']) && !empty($locationIds['pincode_id']) && !empty($uploadedVendor->address)) {
                    $addressData = [
                        'country_id' => $locationIds['country_id'],
                        'state_id' => $locationIds['state_id'],
                        'city_id' => $locationIds['city_id'],
                        'pincode_master_id' => $locationIds['pincode_id'],
                        'pincode' => $locationIds['pincode'],
                        'address' => $uploadedVendor->address,
                        'is_billing' => 1,  
                        'is_shipping' => 0, 
                    ];
                    $vendor->addresses()->create($addressData);
                } 
                $uploadedVendor->update([
                    'status' => 'Success',
                    'remarks' => 'Successfully imported vendor.',
                ]);
                
                $this->onSuccess($vendor);
            } catch (Exception $e) {
                $errors[] = "Error creating vendor: " . $e->getMessage();
                $uploadedVendor->update([
                    'status' => 'Failed',
                    'remarks' => implode(', ', $errors),
                ]);
                $this->onFailure($uploadedVendor);
                Log::error("Error creating vendor from upload: " . $e->getMessage(), ['error' => $e]);
                throw new Exception("Error creating vendor from upload: " . $e->getMessage());
            }
        });
    }

}
