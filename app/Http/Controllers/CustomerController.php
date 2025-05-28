<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Compliance;
use App\Models\Currency;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\BankInfo;
use App\Models\PaymentTerm;
use App\Models\OrganizationType;
use App\Models\CustomerAddress;
use App\Models\ErpAddress;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest; 
use Illuminate\Support\Facades\Validator;
use App\Services\CommonService;
use App\Helpers\ConstantHelper;
use App\Helpers\FileUploadHelper; 
use App\Helpers\ServiceParametersHelper;
use App\Helpers\Helper; 
use App\Helpers\EInvoiceHelper; 
use App\Models\Organization;
use App\Models\CustomerItem;
use App\Models\Contact;
use App\Models\UploadCustomerMaster;
use App\Imports\CustomerImport;
use App\Services\ItemImportExportService;
use App\Exports\CustomersExport;
use App\Exports\FailedCustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\ImportComplete;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use stdClass;
use Auth;

class CustomerController extends Controller
{
    protected $commonService;
    protected $fileUploadHelper;
    protected $itemImportExportService;

    public function __construct(CommonService $commonService,FileUploadHelper $fileUploadHelper,ItemImportExportService $itemImportExportService)
    {
        $this->commonService = $commonService;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->itemImportExportService = $itemImportExportService;
    }

        public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $query = Customer::with(['salesPerson', 'erpOrganizationType', 'category', 'subcategory', 'sales_person','auth_user'])
                ->withDefaultGroupCompanyOrg()
                ->orderBy('id', 'desc');

            if ($request->filled('customer_type')) {
                $query->where('customer_type', $request->customer_type);
            }

            if ($categoryId = request('category_id')) {
                $query->where('category_id', $categoryId);
            }

            if ($subcategoryId = request('subcategory_id')) {
                $query->where('subcategory_id', $subcategoryId);
            }

            if ($request->filled('sales_person')) {
                $query->whereHas('salesPerson', function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->sales_person}%");
                });
            }

            if ($request->has('gst_status') && !empty($request->gst_status)) {
                $query->where('gst_status', $request->gst_status);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('customer_code', function ($row) {
                    return $row->customer_code ?? 'N/A';
                })
                ->editColumn('company_name', function ($row) {
                    return $row->company_name ?? 'N/A';
                })
                ->editColumn('customer_type', function ($row) {
                    return $row->customer_type ?? 'N/A';
                })
                ->editColumn('phone', function ($row) {
                    return $row->phone ?? 'N/A';
                })
                ->editColumn('email', function ($row) {
                    return $row->email ?? 'N/A';
                })
                ->editColumn('gst_status', function ($row) {
                    $statusText = ($row->gst_status === 'ACT') ? 'Active' : (($row->gst_status === 'INACT') ? 'Inactive' : 'N/A');
                    $className = ($row->gst_status === 'ACT') ? 'text-success' : (($row->gst_status === 'INACT') ? 'text-danger' : '');

                    return $className ? '<span class="' . $className . '">' . $statusText . '</span>' : $statusText;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : 'N/A';
                })
                
                ->addColumn('created_by', function ($row) {
                    $createdBy = optional($row->auth_user)->name ?? 'N/A'; 
                    return $createdBy;
                })
                
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? Carbon::parse($row->updated_at)->format('d-m-Y') : 'N/A';
                })

                ->addColumn('status_action', function ($row) {
                    $statusClass = 'badge-light-secondary';
                    if ($row->status == 'active') {
                        $statusClass = 'badge-light-success';
                    } elseif ($row->status == 'inactive') {
                        $statusClass = 'badge-light-danger';
                    } elseif ($row->status == 'draft') {
                        $statusClass = 'badge-light-warning';
                    }
    
                    $status = '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($row->status ?? 'Unknown') . '</span>';
    
                    $editUrl = route('customer.edit', $row->id);
                    $action = '<div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="' . $editUrl . '">
                                        <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </div>';
    
                    return '<div class="d-flex align-items-center justify-content-end">' . $status . $action . '</div>';
                })
                ->rawColumns(['gst_status','status_action'])
                ->make(true);
        }

        $salesPersons = Employee::where('organization_id', $organizationId)->pluck('name', 'id');
        $categories = Category::withDefaultGroupCompanyOrg()
            ->where('status', ConstantHelper::ACTIVE)
            ->whereNull('parent_id') 
            ->get();

        return view('procurement.customer.index', compact('salesPersons', 'categories'));
    }


    public function updateOrganization(Request $request)
    {
        $user = Auth::guard('web')->user();
        $organizationId = $request->input('organization_id');
        $request->validate([
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $user->organization_id = $organizationId;
        $user->save();
        return redirect()->back()->with('success', 'Organization updated successfully!');
    }
    /**
     * Show the form for creating a new resource.
     */

     public function generateCustomerCode(Request $request)
    {
        $customerName = $request->input('customer_name');  
        $customerId = $request->input('customer_id');
        $customerType = $request->input('customer_type');
        $customerInitials = $request->input('customer_initials');
        $prefix = $request->input('prefix', ''); 
        $baseCode = $prefix . $customerType . $customerInitials;
        $authUser = Helper::getAuthenticatedUser();

        if ($customerId) {
            $existingCustomer = Customer::withDefaultGroupCompanyOrg()->find($customerId);
            if ($existingCustomer) {
                $existingCustomerCode = $existingCustomer->customer_code;
                $currentBaseCode = substr($existingCustomerCode, 0, strlen($baseCode));
                if ($currentBaseCode === $baseCode) {
                    return response()->json(['customer_code' => $existingCustomerCode]);
                }
            }
        }

        $lastSimilarCustomer = Customer::withDefaultGroupCompanyOrg() 
        ->where('customer_code', 'like', "{$baseCode}%")
        ->orderBy('customer_code', 'desc')
        ->first();

        $nextSuffix = '001';
        if ($lastSimilarCustomer) {
            $lastSuffix = intval(substr($lastSimilarCustomer->customer_code, -3));
            $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);
        }

        $finalCustomerCode = $baseCode . $nextSuffix;
        return response()->json(['customer_code' => $finalCustomerCode]);
    }

    public function create()
    {
        $organizationTypes = OrganizationType::where('status', ConstantHelper::ACTIVE)->get();
        $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')->withDefaultGroupCompanyOrg()->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->get();
        $paymentTerms = PaymentTerm::where('status', ConstantHelper::ACTIVE)->withDefaultGroupCompanyOrg()->get();
        $relatedCustomers = Customer::where('status', ConstantHelper::ACTIVE)->withDefaultGroupCompanyOrg()->get();
        $titles = ConstantHelper::TITLES;
        $status = ConstantHelper::STATUS;
        $options = ConstantHelper::STOP_OPTIONS;
        $customerTypes = ConstantHelper::CUSTOMER_TYPES;
        $addressTypes = ConstantHelper::ADDRESS_TYPES;
        $countries = Country::where('status', 'active')->get();
        $parentUrl = ConstantHelper::CUSTOMER_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupOrganizationIds = $user->groupOrganizationsIds(); 
        $groupOrganizations = Organization::where('status', 'active')
            ->where('id', '!=', $groupOrganizationIds)
            ->get();
        $customerCodeType = 'Manual';
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
        return view('procurement.customer.create', [
            'organizationTypes' => $organizationTypes,
            'categories' => $categories,
            'titles' => $titles,
            'currencies' => $currencies,
            'paymentTerms' => $paymentTerms,
            'status' => $status,
            'options' => $options,
            'customerTypes' => $customerTypes,
            'countries' => $countries,
            'addressTypes' => $addressTypes,
            'customerCodeType'=>$customerCodeType,
            'organization'=>$organization,
            'groupOrganizations'=>$groupOrganizations,
            'relatedCustomers'=>$relatedCustomers,
        ]);
    }

    public function store(CustomerRequest $request)
    {
        DB::beginTransaction();
    try {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $validatedData['created_by'] = $user->auth_user_id; 
        $validatedData['related_party'] = isset($validatedData['related_party']) ? 'Yes' : 'No';
        // $validatedData['on_account_required'] = isset($validatedData['on_account_required']) ? '1' : '0';
        $parentUrl = ConstantHelper::CUSTOMER_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
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
        $companyName = $validatedData['company_name'] ?? ''; 
        $validatedData['display_name'] = $companyName;
        if ($request->document_status === 'submitted') {
            $validatedData['status'] =  $validatedData['status'] ?? ConstantHelper::ACTIVE;
        } else {
            $validatedData['status'] = ConstantHelper::DRAFT;
        }
        $gstnNo = $validatedData['compliance']['gstin_no'] ?? '';
        if ($gstnNo) {
            $gstValidation = EInvoiceHelper::validateGstNumber($gstnNo);
            if ($gstValidation['Status'] === 1) {
                $gstData = json_decode($gstValidation['checkGstIn'], true);
                $validatedData['deregistration_date'] = $gstData['DtDReg'] ??''; 
                $validatedData['taxpayer_type'] = $gstData['TxpType'] ?? '';
                $validatedData['gst_status'] = $gstData['Status'] ?? '';
                $validatedData['block_status'] = $gstData['BlkStatus'] ?? '';
                $validatedData['legal_name'] = $gstData['LegalName'] ?? '';
                $addresses = $validatedData['addresses'] ?? [];
                if (!empty($addresses)) {
                    $firstAddress = $addresses[0];  
                    if (isset($firstAddress['state_id'])) {
                        $validatedData['gst_state_id'] = $firstAddress['state_id'];
                    }
                }
            }
        }
        $customer = Customer::create($validatedData);
        $fileConfigs = [
            'pan_attachment' => ['folder' => 'pan_attachments', 'clear_existing' => true],
            'tin_attachment' => ['folder' => 'tin_attachments', 'clear_existing' => true],
            'aadhar_attachment' => ['folder' => 'aadhar_attachments', 'clear_existing' => true],
            'other_documents' => ['folder' => 'other_documents', 'clear_existing' => true],
        ];
        
        $this->fileUploadHelper->handleFileUploads($request, $customer, $fileConfigs);

        $bankInfoData = $validatedData['bank_info'] ?? [];
        if (!empty($bankInfoData)) {
            $this->commonService->createBankInfo($bankInfoData, $customer);
        }
        // Handle notes
        $notesData = $validatedData['notes'] ?? [];
        if (!empty($notesData)) {
            $this->commonService->createNote($notesData, $customer, $user);
        }

        $contacts = $validatedData['contacts'] ?? [];
        if (!empty($contacts)) {
            $this->commonService->createContact($contacts, $customer);
        }

        $addresses = $validatedData['addresses'] ?? [];
        if (!empty($addresses)) {
            $this->commonService->createAddress($addresses, $customer);
        }

        $compliance = $validatedData['compliance'] ?? [];
         if (!empty($compliance)) {
             $this->commonService->createCompliance($compliance, $customer);
         }

        // Handling Customer Items

        if ($request->has('customer_item')) {
            foreach ($request->input('customer_item') as $customerItemData) {
                if (!empty($customerItemData['item_code']) && !empty($customerItemData['item_name'])) {
                    $customer->approvedItems()->create([
                        'item_id' => $customerItemData['item_id'],
                        'item_code' => $customerItemData['item_code'] ?? null, 
                        'item_name' => $customerItemData['item_name'] ?? null, 
                        'item_details' => $customerItemData['item_details'] ?? null, 
                        'sell_price' => $customerItemData['sell_price'] ?? null, 
                        'uom_id' => $customerItemData['uom_id']?? null,
                       'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                    ]);
                } 
            }
        }
        
        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $customer,
        ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function show($id)
    {
        // Implement the logic if needed
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $gstStateId = $customer->gst_state_id;
        $state = $gstStateId ? State::find($gstStateId) : null;
        $country = $state ? Country::find($state->country_id) : null;
        $organizationTypes = OrganizationType::where('status', ConstantHelper::ACTIVE)->get();
        $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')->withDefaultGroupCompanyOrg()->get();
        $subcategories = Category::where('status', ConstantHelper::ACTIVE)->whereNotNull('parent_id')->withDefaultGroupCompanyOrg()->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->get();
        $paymentTerms = PaymentTerm::where('status', ConstantHelper::ACTIVE)->withDefaultGroupCompanyOrg()->get();
        $relatedCustomers = Customer::where('status', ConstantHelper::ACTIVE)->withDefaultGroupCompanyOrg()->get();
        $titles = ConstantHelper::TITLES;
        $notificationData = $customer? $customer->notification : [];
        $notifications = is_array($notificationData) ? $notificationData : json_decode($notificationData, true);
        $notifications = $notifications ?? [];
        $status = ConstantHelper::STATUS;
        $options = ConstantHelper::STOP_OPTIONS;
        $customerTypes = ConstantHelper::CUSTOMER_TYPES;
        $addressTypes = ConstantHelper::ADDRESS_TYPES;
        $countries = Country::where('status', 'active')->get();
        $ledgerId = $customer->ledger_id;
        $ledger = Ledger::find($ledgerId);
        $ledgerGroups = $ledger ? $ledger->groups() : collect(); 
        $parentUrl = ConstantHelper::CUSTOMER_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupOrganizationIds = $user->groupOrganizationsIds(); 
        $groupOrganizations = Organization::where('status', 'active')
        ->where('id', '!=', $groupOrganizationIds)
        ->get();
        $customerCodeType ='Manual';
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
        return view('procurement.customer.edit', [
            'customer' => $customer,
            'organizationTypes' => $organizationTypes,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'titles' => $titles,
            'currencies' => $currencies,
            'paymentTerms' => $paymentTerms,
            'notifications' => $notifications,
            'status' => $status,
            'options' => $options,
            'customerTypes' => $customerTypes,
            'countries' => $countries,
            'addressTypes' => $addressTypes,
            'ledgerGroups' => $ledgerGroups,
            'customerCodeType'=>$customerCodeType,
            'organization'=>$organization,
            'groupOrganizations'=>$groupOrganizations,
            'gstState' => $state,
            'gstCountry' => $country,
            'relatedCustomers'=>$relatedCustomers,
        ]);
    }

    public function update(CustomerRequest $request, $id)
    {
        DB::beginTransaction();

    try {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $validatedData['related_party'] = isset($validatedData['related_party']) ? 'Yes' : 'No';
        // $validatedData['on_account_required'] = isset($validatedData['on_account_required']) ? '1' : '0';
        $parentUrl = ConstantHelper::CUSTOMER_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
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
        $companyName = $validatedData['company_name'] ?? ''; 
        $validatedData['display_name'] = $companyName;
        if ($request->input('document_status') === 'submitted') {
            $validatedData['status'] =  $validatedData['status'] ?? ConstantHelper::ACTIVE;
        } else {
            $validatedData['status'] = ConstantHelper::DRAFT;
        }
        $gstnNo = $validatedData['compliance']['gstin_no'] ?? '';
        if ($gstnNo) {
            $gstValidation = EInvoiceHelper::validateGstNumber($gstnNo);
            if ($gstValidation['Status'] === 1) {
                $gstData = json_decode($gstValidation['checkGstIn'], true);
                $validatedData['deregistration_date'] = $gstData['DtDReg'] ??''; 
                $validatedData['taxpayer_type'] = $gstData['TxpType'] ?? '';
                $validatedData['gst_status'] = $gstData['Status'] ?? '';
                $validatedData['block_status'] = $gstData['BlkStatus'] ?? '';
                $validatedData['legal_name'] = $gstData['LegalName'] ?? '';
                $addresses = $validatedData['addresses'] ?? [];
                if (!empty($addresses)) {
                    $firstAddress = $addresses[0];  
                    if (isset($firstAddress['state_id'])) {
                        $validatedData['gst_state_id'] = $firstAddress['state_id'];
                    }
                }
            }
        }
        $customer = Customer::findOrFail($id);
        $validatedData['created_by'] = $customer->created_by ?? $user->auth_user_id;
        $customer->update($validatedData);
       
         $fileConfigs = [
            'pan_attachment' => ['folder' => 'pan_attachments', 'clear_existing' => true],
            'tin_attachment' => ['folder' => 'tin_attachments', 'clear_existing' => true],
            'aadhar_attachment' => ['folder' => 'aadhar_attachments', 'clear_existing' => true],
            'other_documents' => ['folder' => 'other_documents', 'clear_existing' => true],
        ];

        $this->fileUploadHelper->handleFileUploads($request, $customer, $fileConfigs);

        
        $bankInfoData = $validatedData['bank_info'] ?? [];
        if (!empty($bankInfoData)) {
            $this->commonService->updateBankInfo($bankInfoData, $customer);
        }
        
        $notesData = $validatedData['notes'] ?? [];
        if (!empty($notesData['remark'])) {
            $this->commonService->createNote($notesData, $customer,$user);
        }

        $contacts = $validatedData['contacts'] ?? [];
        if (!empty($contacts)) {
            $this->commonService->updateContact($contacts, $customer);
        }

        $addresses = $validatedData['addresses'] ?? [];
        if (!empty($addresses)) {
            $this->commonService->updateAddress($addresses, $customer);
        }

        $compliance = $validatedData['compliance'] ?? [];
        if (!empty($compliance)) {
            $this->commonService->updateCompliance($compliance, $customer);
        }
        // for items
        if ($request->has('customer_item')) {
            $existingCustomerItems = $customer->approvedItems()->pluck('id')->toArray();
            $newItems = [];
            foreach ($request->input('customer_item') as $customerItemData) {
                if (!empty($customerItemData['item_code']) && !empty($customerItemData['item_name'])) {
                    if (isset($customerItemData['id']) && !empty($customerItemData['id'])) {
                        $existingItem = $customer->approvedItems()->where('id', $customerItemData['id'])->first();
                        if ($existingItem) {
                            $updateData = [
                                'item_code' => $customerItemData['item_code'],
                                'item_name' => $customerItemData['item_name'],
                                'item_details' => $customerItemData['item_details'] ?? null,
                                'sell_price' => $customerItemData['sell_price'] ?? null, 
                                'uom_id' => $customerItemData['uom_id']?? null,
                                'organization_id' => $validatedData['organization_id']?? null,
                                'group_id' => $validatedData['group_id']?? null,
                                'company_id' => $validatedData['company_id']?? null,
                            ];
                            if (isset($customerItemData['item_id']) && !empty($customerItemData['item_id'])) {
                                $updateData['item_id'] = $customerItemData['item_id'];
                            }
                            $existingItem->update($updateData);
                            $newItems[] = $existingItem->id;
                        }
                    } else {
                        $newItem = $customer->approvedItems()->create([
                            'item_id' => $customerItemData['item_id'] ?? null,
                            'item_code' => $customerItemData['item_code'],
                            'item_name' => $customerItemData['item_name'],
                            'item_details' => $customerItemData['item_details'] ?? null,
                            'sell_price' => $customerItemData['sell_price'] ?? null, 
                            'uom_id' => $customerItemData['uom_id']?? null,
                            'organization_id' => $validatedData['organization_id']?? null,
                            'group_id' => $validatedData['group_id']?? null,
                            'company_id' => $validatedData['company_id']?? null,
                        ]);
                        $newItems[] = $newItem->id;
                    }
                }
            }
        
            $itemsToDelete = array_diff($existingCustomerItems, $newItems);
            if ($itemsToDelete) {
                $customer->approvedItems()->whereIn('id', $itemsToDelete)->delete();
            }
        } else {
            $customer->approvedItems()->delete();
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $customer,
        ]);
    } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showImportForm()
    {
        return view('procurement.customer.import'); 
    }


    public function import(Request $request)
    {
        $user = Helper::getAuthenticatedUser ();
    
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:30720',
            ]);
            if (!$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }
    
            $file = $request->file('file');
    
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file format is incorrect or corrupted. Please upload a valid Excel file.',
                ], 400);
            }
    
            $sheet = $spreadsheet->getActiveSheet();
            $rowCount = $sheet->getHighestRow() - 1;
            if ($rowCount > 10000) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file contains more than 10000 items. Please upload a file with 10000 or fewer items.',
                ], 400);
            }
            if ($rowCount < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.',
                ], 400);
            }
    
            UploadCustomerMaster::where('user_id', $user->id)->delete();
    
            $import = new CustomerImport($this->itemImportExportService); 
            Excel::import($import, $file);
    
            $successfulCustomers = $import->getSuccessfulCustomers();
            $failedCustomers = $import->getFailedCustomers();
            $mailData = [
                'modelName' => 'Customer',
                'successful_items' => $successfulCustomers,
                'failed_items' => $failedCustomers,
                'export_successful_url' => route('customers.export.successful'), 
                'export_failed_url' => route('customers.export.failed'), 
            ];
            if (count($failedCustomers) > 0) {
                $message = 'Record import failed. Some records were not imported.';
                $status = 'failure';
            } else {
                $message = 'Record import successfully.';
                $status = 'success';
            }
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new ImportComplete( $mailData)); 
                } catch (\Exception $e) {
                    $message .= " However, there was an error sending the email notification.";
                }
            }
            return response()->json([
                'status' => $status,
                'message' => $message,
                'successful_customers' => $successfulCustomers,
                'failed_customers' => $failedCustomers,
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 30MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import customers: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportSuccessfulCustomers()
    {
        $uploadCustomers = UploadCustomerMaster::where('status', 'Success')->get();
        $customers = Customer::with(['category', 'subcategory', 'currency', 'paymentTerms'])
                   ->whereIn('company_name', $uploadCustomers->pluck('company_name'))
                            ->get();

         return Excel::download(new CustomersExport($customers, $this->itemImportExportService), "successful-customers.xlsx");
    }
    
    public function exportFailedCustomers()
    {
        $failedCustomers = UploadCustomerMaster::where('status', 'Failed')->get();
        return Excel::download(new FailedCustomersExport($failedCustomers), "failed-customers.xlsx");
    }


    public function deleteAddress($id)
    {
        DB::beginTransaction();
        try {
            $address = ErpAddress::find($id);

            if ($address) {
                $result = $address->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => $result['message']], 400);
                }
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Record deleted successfully']);
            }
            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deleteContact($id)
    {
        DB::beginTransaction();
        try {
            $contact = Contact::find($id);
            if ($contact) {
                $result = $contact->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => $result['message']], 400);
                }
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Record deleted successfully']);
            }

            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deleteBankInfo($id)
    {
        DB::beginTransaction();
        try {
            $bankInfo = BankInfo::find($id);
            if ($bankInfo) {
                $result = $bankInfo->deleteWithReferences();

                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => $result['message']], 400);
                }

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Record deleted successfully']);
            }

            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deleteCustomerItem($id)
    {
        DB::beginTransaction();
        try {
            $customerItem = CustomerItem::find($id);

            if ($customerItem) {
                $result = $customerItem->deleteWithReferences();

                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => $result['message']], 400);
                }

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Record deleted successfully']);
            }

            return response()->json(['status' => false, 'message' => 'Customer item not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);
    
            $referenceTables = [
                'erp_addresses' => ['addressable_id'],
                'erp_contacts' => ['contactable_id'],
                'erp_bank_infos' => ['morphable_id'],
                'erp_notes' => ['noteable_id'],
                'erp_customer_items' => ['customer_id'],
                'erp_compliances' => ['morphable_id'],
                
            ];
    
            $result = $customer->deleteWithReferences($referenceTables);
    
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' =>'Record deleted successfully.',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the customer: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getStates($country_id) 
    {
        $states = State::where('country_id', $country_id)->get();
        return response()->json($states);
    }

    public function getCities($state_id) 
    {
        $cities = City::where('state_id', $state_id)->get();
        return response()->json($cities);
    }

    public function getComplianceByCountry($customerId, $countryId)
    {
        $compliances = Compliance::where('customer_id', $customerId)
            ->where('country_id', $countryId)
            ->get();

        return response()->json([
            'compliances' => $compliances
        ]);
    }
    
    public function getComplianceById($id)
    {
        $compliance = Compliance::with('media')->find($id);
        
        if (!$compliance) {
            return response()->json(['error' => 'Compliance not found'], 404);
        }
    
        return response()->json($compliance);
    }

    public function getCustomer(Request $request)
    {
        $searchTerm = $request->input('q', '');
        $customers = Customer::withDefaultGroupCompanyOrg() 
            ->where(function ($query) use ($searchTerm) {
                $query->where('company_name', 'like', "%{$searchTerm}%")
                    ->orWhere('customer_code', 'like', "%{$searchTerm}%");
            })
            ->where('status', ConstantHelper::ACTIVE)
            ->get(['id', 'company_name','customer_code']);

        if ($customers->isEmpty()) {
            $customers = Customer::withDefaultGroupCompanyOrg()
                ->where('status', ConstantHelper::ACTIVE)
                ->limit(10)
                ->get(['id', 'company_name','customer_code']);
        }
        return response()->json($customers);
    } 
}
