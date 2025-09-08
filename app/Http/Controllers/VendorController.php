<?php

namespace App\Http\Controllers;
use App\Exceptions\ApiGenericException;
use App\Helpers\ASN\Constants as ASNConstants;
use App\Helpers\GstStatusChecker;
use App\Helpers\StoreHelper;
use App\Models\ErpStore;
use App\Models\VendorLocation;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;
use App\Models\VendorHistory;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Compliance;
use App\Models\Currency;
use App\Models\Country;
use App\Models\State;
use App\Models\Item;
use App\Models\Unit;
use App\Models\City;
use App\Models\BankInfo;
use App\Models\VendorAddress;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\OrganizationType;
use App\Models\Ledger;
use Illuminate\Http\Request;
use App\Http\Requests\VendorRequest;
use Illuminate\Support\Facades\Validator;
use App\Services\CommonService;
use App\Helpers\ConstantHelper;
use App\Helpers\FileUploadHelper; 
use App\Helpers\Helper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\ServiceParametersHelper;
use App\Models\Organization;
use App\Models\VendorItem;
use App\Models\Group;
use App\Models\Contact;
use App\Models\User;
use App\Models\VendorPortalBook;
use App\Models\VendorPortalUser;
use App\Models\Book;
use App\Models\UploadVendorMaster;
use App\Models\PincodeMaster;
use App\Models\ItemDetail;
use App\Imports\VendorImport;
use App\Services\ItemImportExportService;
use App\Exports\VendorsExport;
use App\Exports\FailedVendorsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\ImportComplete;
use App\Models\AuthUser;
use Carbon\Carbon;
use stdClass;
use Auth;
use Exception;
use Log;
use Illuminate\Support\Facades\Hash;


class VendorController extends Controller
{

    protected $commonService;
    protected $fileUploadHelper;
    protected $itemImportExportService;

    public function __construct(CommonService $commonService, FileUploadHelper $fileUploadHelper,ItemImportExportService $itemImportExportService)
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
                $query = Vendor::with(relations: ['erpOrganizationType', 'category', 'subcategory','auth_user'])
                ->withDraftListingLogic() 
                ->orderBy('id', 'desc');
        
                if ($request->has('vendor_type') && !empty($request->vendor_type)) {
                    $query->where('vendor_type', $request->vendor_type);
                }
        
                if ($categoryId = request('category_id')) {
                    $query->where('category_id', $categoryId);
                }
        
                if ($subcategoryId = request('subcategory_id')) {
                    $query->where('subcategory_id', $subcategoryId);
                }

                if ($request->has('gst_status') && !empty($request->gst_status)) {
                    $query->where('gst_status', $request->gst_status);
                }
        
                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->status);
                }
        
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('vendor_code', function ($row) {
                        return $row->vendor_code ?? 'N/A';
                    })
                    ->editColumn('company_name', function ($row) {
                        return $row->company_name ?? 'N/A';
                    })
                    ->editColumn('vendor_type', function ($row) {
                        return $row->vendor_type ?? 'N/A';
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
                        return $row->created_at ? Carbon::parse($row->created_at)->setTimezone('Asia/Kolkata')->format('d-m-Y H:i:s') : 'N/A';
                    })
                    
                    ->editColumn('created_by', function ($row) {
                        $createdBy = optional($row->auth_user)->name ?? 'N/A'; 
                        return $createdBy;
                    })
                    
                    ->editColumn('updated_at', function ($row) {
                        return $row->updated_at ? Carbon::parse($row->updated_at)->setTimezone('Asia/Kolkata')->format('d-m-Y H:i:s') : 'N/A';
                    })
    
                    ->editColumn('status', function ($row) {
                        $statusKey = strtolower($row->getRawOriginal('status') ?? ConstantHelper::DRAFT); 
                        $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$statusKey] ?? 'badge-light-secondary';
                        
                        $statusLabel = ucfirst(str_replace('_', ' ', $row->getRawOriginal('status') ?? 'N/A'));
                        $editRoute = route('vendor.edit', ['id' => $row->id]);

                        return "
                            <div style='text-align:right;'>
                                <span class='badge rounded-pill {$statusClass} badgeborder-radius'>{$statusLabel}</span>
                                <div class='dropdown' style='display:inline;'>
                                    <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                        <i data-feather='more-vertical'></i>
                                    </button>
                                    <div class='dropdown-menu dropdown-menu-end'>
                                        <a class='dropdown-item' href='{$editRoute}'>
                                            <i data-feather='edit-3' class='me-50'></i>
                                            <span>View/ Edit Detail</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ";
                    })
                    ->rawColumns(['gst_status','status'])
                    ->make(true);
            }
        
            $categories = Category::where('type', 'Vendor')
                ->doesntHave('subCategories')
                ->where('status', ConstantHelper::ACTIVE)
                ->get();
        
            return view('procurement.vendor.index', compact('categories'));
        }

        public function generateItemCode(Request $request)
        {
            $vendorName = $request->input('vendor_name');
            $vendorId = $request->input('vendor_id');
            $vendorType = $request->input('vendor_type');
            $vendorInitials = $request->input('vendor_initials');
            $prefix = $request->input('prefix', ''); 
            $baseCode =  $prefix .$vendorType . $vendorInitials;

            $authUser = Helper::getAuthenticatedUser();
            
            if ($vendorId) {
                $existingVendor = Vendor::find($vendorId);
                if ($existingVendor) {
                    $existingVendorCode = $existingVendor->vendor_code;
                    $currentBaseCode = substr($existingVendorCode, 0, strlen($baseCode));
                    if ($currentBaseCode === $baseCode) {
                        return response()->json(['vendor_code' => $existingVendorCode]);
                    }
                }
            }

            $lastSimilarVendor = Vendor::where('vendor_code', 'like', "{$baseCode}%")
            ->orderBy('vendor_code', 'desc')
            ->first();
    
            $nextSuffix = '001';
            if ($lastSimilarVendor) {
                $lastSuffix = intval(substr($lastSimilarVendor->vendor_code, -3));
                $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);
            }
            $finalVendorCode = $baseCode . $nextSuffix;

            return response()->json(['vendor_code' => $finalVendorCode]);
        }


        public function create()
        {
            $urlSegmentAlias = request()->segments()[0];
            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($urlSegmentAlias);
            if (count($servicesBooks['services']) == 0) {
                return redirect()->route('/');
            }
            $organizationTypes = OrganizationType::where('status', ConstantHelper::ACTIVE)->get();
            $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')->get();
            $currencies = Currency::where('status', ConstantHelper::ACTIVE)->get();
            $paymentTerms = PaymentTerm::where('status', ConstantHelper::ACTIVE)->get();
            $titles = ConstantHelper::TITLES;
            $status = ConstantHelper::STATUS;
            $options = ConstantHelper::STOP_OPTIONS;
            $vendorTypes = ConstantHelper::VENDOR_TYPES;
            $vendorSubTypes = ConstantHelper::VENDOR_SUB_TYPES;
            $addressTypes = ConstantHelper::ADDRESS_TYPES;
            $countries = Country::where('status', 'active')->get();
            $serviceAlias = ASNConstants::SERVICE_ALIAS;
            $user = Helper::getAuthenticatedUser();
            $supplierUsers = AuthUser::where('organization_id', $user?->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->where('user_type',ConstantHelper::IAM_VENDOR_USER)
            ->get();
            $books = Helper::getBookSeries($serviceAlias)->get();
            $parentUrl = ConstantHelper::VENDOR_SERVICE_ALIAS;
            $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            $organization = $user->organization;
            $groupId = $organization->group_id;
            $groupOrganizations = Organization::where('status', 'active')
            ->where('group_id', $groupId)
            ->where('id', '!=', $organization->id)
            ->get();
            $stores = StoreHelper::getAvailableStoresForVendor();
            $vendorCodeType = 'Manual';
            if ($services && $services['current_book']) {
                if (isset($services['current_book'])) {
                    $book=$services['current_book'];
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
            }
            if (count($services['services']) == 0) {
               return redirect() -> route('/');
            }
            return view('procurement.vendor.create', [
                'organizationTypes' => $organizationTypes,
                'categories' => $categories,
                'titles' => $titles,
                'currencies' => $currencies,
                'paymentTerms' => $paymentTerms,
                'status'=>$status,
                'options'=>$options,
                'vendorTypes'=>$vendorTypes,
                'vendorSubTypes'=>$vendorSubTypes,
                'countries'=>$countries,
                'addressTypes'=>$addressTypes,
                'supplierUsers' => $supplierUsers,
                'books' => $books,
                'vendorCodeType' => $vendorCodeType, 
                'organization'=>$organization,
                'groupOrganizations'=>$groupOrganizations,
                'stores' => $stores,
            ]);
        }

        public function store(VendorRequest $request)
        {
          
            DB::beginTransaction();
          try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validatedData = $request->validated();
            $validatedData['created_by'] = $user->auth_user_id; 
            $validatedData['related_party'] = isset($validatedData['related_party']) ? 'Yes' : 'No';
            // $validatedData['on_account_required'] = isset($validatedData['on_account_required']) ? '1' : '0';
            $parentUrl = ConstantHelper::VENDOR_SERVICE_ALIAS;
            $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
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
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
                // Insert Book ID (if current_book exists)
                if (isset($services['current_book'])) { 
                    $book = $services['current_book'];
                    if ($book) {
                        $validatedData['book_id'] = $book->id;
                    } else {
                        $validatedData['book_id'] = null;
                    }
                } else {
                    $validatedData['book_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
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
                $gstValidation = EInvoiceHelper::validateGstinName($gstnNo);
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
            $vendor = Vendor::create($validatedData);
            $vendor ->updated_at = null;
            if ($request->document_status === 'submitted') {
                $bookId = $vendor->book_id;
                $docId = $vendor->id;
                $remarks = $request->remarks ?? '';
                $attachments = $request->file('attachment');
                $currentLevel = $vendor->approval_level ?? 1;
                $revisionNumber = $vendor->revision_number ?? 0;
                $actionType = 'submit';
                $modelName = get_class($vendor);
                $totalValue = 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $document_status = $approveDocument['approvalStatus'] ?? $vendor->document_status;
                $vendor->document_status = $document_status;
            
                $submittedStatus = $validatedData['status'] ?? ConstantHelper::ACTIVE;

                if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                     if ($revisionNumber == 0) {
                         $vendor->status = ConstantHelper::ACTIVE;
                      }
                       // ** START: Call createPartyLedger if conditions are met **
                        $shouldCreateLedger = isset($validatedData['create_ledger']) && $validatedData['create_ledger'] == 1;
                        $hiddenLedgerVendorName = $validatedData['hidden_ledger_vendor_name'];
                         
                        $hiddenLedgerVendorCode = $validatedData['hidden_ledger_vendor_code'];
                      
                        $ledgerId = $validatedData['ledger_id'] ?? null; 
                        $ledgerGroupId = $request->ledger_group_id?? null; 
                          
                        if ($shouldCreateLedger && !empty($hiddenLedgerVendorName) && !empty($hiddenLedgerVendorCode) && !empty($ledgerGroupId)) {
                            try {
                                $result = Helper::createPartyLedger(
                                    'vendor',
                                    $hiddenLedgerVendorName,
                                    $hiddenLedgerVendorCode,
                                    $ledgerGroupId 
                                );
                              
                                if (!$result['success']) {
                                    Log::error('Error creating party ledger: ' . $result['message']);
                                    DB::rollBack();
                                    return response()->json([
                                        'status' => false,
                                        'message' => $result['message'], 
                                        'data' => $vendor,
                                    ], 500);
                                }
                                $ledgerId = $result['data']['ledger_id'] ?? null;
                          
                                $ledgerGroupId = $result['data']['ledger_group_id'] ?? null;
                                $vendor->ledger_id = $ledgerId;
                                $vendor->ledger_group_id = $ledgerGroupId;
                                $vendor->create_ledger = 0;
                            } catch (Exception $e) {
                                Log::error('Exception creating party ledger: ' . $e->getMessage(), [
                                    'trace' => $e->getTraceAsString()
                                ]);
                                DB::rollBack();
                                return response()->json([
                                    'status' => false,
                                    'message' =>  $e->getMessage(), 
                                    'data' => $vendor,
                                ], 500);
                            }
                        } 
                        // ** END: Call createPartyLedger if conditions are met **

                } else {
                    $vendor->status = $document_status;
                }
            
            } else {
                $document_status = $request->document_status ?? ConstantHelper::DRAFT;
                $vendor->document_status = $document_status;
                $vendor->status = $document_status;
            }
            $vendor->save();

            $fileConfigs = [
                'pan_attachment' => ['folder' => 'pan_attachments', 'clear_existing' => true],
                'tin_attachment' => ['folder' => 'tin_attachments', 'clear_existing' => true],
                'aadhar_attachment' => ['folder' => 'aadhar_attachments', 'clear_existing' => true],
                'other_documents' => ['folder' => 'other_documents', 'clear_existing' => true],
            ];
            
            $this->fileUploadHelper->handleFileUploads($request, $vendor, $fileConfigs);
            
            
            $bankInfoData = $validatedData['bank_info'] ?? [];
            if (!empty($bankInfoData)) {
                $this->commonService->createBankInfo($bankInfoData, $vendor);
            }
            // Handle notes
            $notesData = $validatedData['notes'] ?? [];
            if (!empty($notesData)) {
                $this->commonService->createNote($notesData, $vendor, $user);
            }
            
            $contacts = $validatedData['contacts'] ?? [];
            if (!empty($contacts)) {
                $this->commonService->createContact($contacts, $vendor);
            }
        

            $addresses = $validatedData['addresses'] ?? [];
            if (!empty($addresses)) {
                $this->commonService->createAddress($addresses, $vendor);
            }

            $compliance = $validatedData['compliance'] ?? [];
            if (!empty($compliance)) {
                $this->commonService->createCompliance($compliance, $vendor);
            }

            if ($request->has('vendor_item')) {
                foreach ($request->input('vendor_item') as $vendorItemData) {
                    if (!empty($vendorItemData['item_code']) && !empty($vendorItemData['item_name'])) {
                        $vendor->approvedItems()->create([
                            'item_id' => $vendorItemData['item_id'],
                            'item_code' => $vendorItemData['item_code'] ?? null, 
                            'cost_price' => $vendorItemData['cost_price'] ?? null, 
                            'uom_id' => $vendorItemData['uom_id']?? null,
                            'organization_id' => $validatedData['organization_id']?? null,
                            'group_id' => $validatedData['group_id']?? null,
                            'company_id' => $validatedData['company_id']?? null,
                        ]);
                    }
                }
            }
            
              // Step 7: Synchronize Vendor Books
            $bookIds = $request->book_id ?? [];
            if (!empty($bookIds)) {
                VendorPortalBook::where('vendor_id', $vendor->id)
                    ->whereNotIn('book_id', $bookIds)
                    ->delete();
                foreach ($bookIds as $bookId) {
                    $book = Book::find($bookId);
                    if ($book) {
                        VendorPortalBook::updateOrCreate(
                            ['vendor_id' => $vendor->id, 'book_id' => $bookId],
                            ['service_id' => $book->service_id]
                        );
                    }
                }
            } else {
                VendorPortalBook::where('vendor_id', $vendor->id)->delete();
            }

            // Step 8: Synchronize Vendor Users
            $userIds = $request->user_id ?? [];
            if (!empty($userIds)) {
                VendorPortalUser::where('vendor_id', $vendor->id)
                    ->whereNotIn('user_id', $userIds)
                    ->delete();
                foreach ($userIds as $userId) {
                    VendorPortalUser::updateOrCreate(
                        ['vendor_id' => $vendor->id, 'user_id' => $userId]
                    );
                }
            } else {
                VendorPortalUser::where('vendor_id', $vendor->id)->delete();
            }

            //Sync Vendor Locations
            $storeIds = isset($request -> vendor_store) && is_array($request -> vendor_store) ? $request -> vendor_store : [];
            $locationSyncStatus = $vendor -> syncLocations($storeIds);
            if (!$locationSyncStatus['status']) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => $locationSyncStatus['message'],
                    'error' => '',
                ], 422);
            }
            DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $vendor,
        ]);
            } catch (Exception $e) {
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
        //
        }

        public function edit(Request $request,$id)
        {
            $urlSegmentAlias = request()->segments()[0];
            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($urlSegmentAlias);
            if (count($servicesBooks['services']) == 0) {
                return redirect()->route('/');
            }
            if ($request->has('revisionNumber')) {
                $vendor = VendorHistory::where('source_id', $id)
                    ->where('revision_number', $request->revisionNumber)
                    ->firstOrFail();
                 $ogVendor = Vendor::findOrFail($id);
            } else {
                $vendor = Vendor::findOrFail($id);
                $ogVendor = Vendor::findOrFail($id);    
            }
            $gstStateId = $vendor->gst_state_id;
            // Fetch State and Country details
            $state = $gstStateId ? State::find($gstStateId) : null;
            $country = $state ? Country::find($state->country_id) : null;
            $organizationTypes = OrganizationType::where('status', ConstantHelper::ACTIVE)->get();
            $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')->get();
            $subcategories = Category::where('status', ConstantHelper::ACTIVE)->whereNotNull('parent_id')->get();
            $currencies = Currency::where('status', ConstantHelper::ACTIVE)->get();
            $paymentTerms = PaymentTerm::where('status', ConstantHelper::ACTIVE)->get();
            $titles = ConstantHelper::TITLES;
            $notificationData = $vendor? $vendor->notification : [];
            $notifications = is_array($notificationData) ? $notificationData : json_decode($notificationData, true);
            $notifications = $notifications ?? [];
            $status = ConstantHelper::STATUS;
            $options = ConstantHelper::STOP_OPTIONS;
            $vendorTypes = ConstantHelper::VENDOR_TYPES;
            $vendorSubTypes = ConstantHelper::VENDOR_SUB_TYPES;
            $addressTypes = ConstantHelper::ADDRESS_TYPES;
            $countries = Country::where('status', 'active')->get();
            $serviceAlias = ASNConstants::SERVICE_ALIAS;
            $user = Helper::getAuthenticatedUser();
            $supplierUsers = AuthUser::where('organization_id', $user?->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->where('user_type',ConstantHelper::IAM_VENDOR_USER)
            ->get();
            $books = Helper::getBookSeries($serviceAlias)->get();
            $ledgerGroups = collect();
            $ledgerId = $vendor->ledger_id ?? null;
            $createLedger = $request->input('create_ledger');
            $isLedgerEditable = true;
            if ($ledgerId) {
                $ledger = Ledger::find($ledgerId);
                if ($ledger) {
                    $ledgerGroups = $ledger->groups();
                    $ledgerGroupId = $vendor->ledger_group_id ?? null;
                    $existsInItems = ItemDetail::where('ledger_id', $ledgerId)
                    ->where('ledger_parent_id', $ledgerGroupId)
                    ->exists();
                    if ($existsInItems) {
                        $isLedgerEditable = false;
                    }
                }
            }
            if ($ledgerGroups->isEmpty() && $createLedger == 1) {
                $ledgerGroups = Group::where('status', 1)->get(); 
            }

            if ($ledgerGroups->isEmpty()) {
                $defaultGroup = Group::where('name', 'Account Payable')->first();

                if ($defaultGroup) {
                    $lastLevelGroupIds = $defaultGroup->getAllLastLevelGroupIds();
                    $ledgerGroups = Group::whereIn('id', $lastLevelGroupIds)->get();
                }
            }
            $parentUrl = ConstantHelper::VENDOR_SERVICE_ALIAS;
            $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            $organization = $user->organization;
            $groupId = $organization->group_id;
            $groupOrganizations = Organization::where('status', 'active')
            ->where('group_id', $groupId)
            ->where('id', '!=', $organization->id)
            ->get();
            $selectedStoreIds = $vendor ?-> locations() -> pluck('store_id') -> toArray();
            $stores = $vendor -> locations;
            $vendorCodeType = 'Manual';
            if ($services && $services['current_book']) {
                if (isset($services['current_book'])) {
                    $book=$services['current_book'];
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
            }
            $revision_number = $vendor->revision_number;
            $userType = Helper::userCheck();
            $buttons = Helper::actionButtonDisplay($vendor->book_id, $vendor->document_status, $vendor->id, 1, $vendor->approval_level, $vendor->created_by ?? 0, $userType['type'], $revision_number);
            $revNo = $vendor->revision_number;
            if ($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $vendor->revision_number;
            }

            $docValue = 1;
            $approvalHistory = Helper::getApprovalHistory($ogVendor->book_id, $ogVendor->id, $revNo, $docValue, $ogVendor->created_by);
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$vendor->document_status] ?? '';

            return view('procurement.vendor.edit', [
                'vendor' => $vendor,
                'organizationTypes' => $organizationTypes,
                'categories' => $categories,
                'subcategories' => $subcategories,
                'titles' => $titles,
                'currencies' => $currencies,
                'paymentTerms' => $paymentTerms,
                'notifications' => $notifications,
                'status'=>$status,
                'options'=>$options,
                'vendorTypes'=>$vendorTypes,
                'vendorSubTypes'=>$vendorSubTypes,
                'countries'=>$countries,
                'addressTypes'=>$addressTypes,
                'supplierUsers' => $supplierUsers,
                'ledgerGroups' => $ledgerGroups,
                'books' => $books,
                'vendorStores' => $stores,
                'selectedStoreIds' => $selectedStoreIds,
                'vendorCodeType'=>$vendorCodeType,
                'organization'=>$organization,
                'groupOrganizations'=>$groupOrganizations,
                'gstState' => $state,
                'gstCountry' => $country,
                'revision_number'=>$revision_number,
                'buttons' => $buttons,
                'approvalHistory' => $approvalHistory,
                'docStatusClass' => $docStatusClass,
                'isLedgerEditable'=>$isLedgerEditable
            ]);
        }


        public function update(VendorRequest $request, $id)
        {
           
            DB::beginTransaction();

            try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validatedData = $request->validated();
            $validatedData['related_party'] = isset($validatedData['related_party']) ? 'Yes' : 'No';
            // $validatedData['on_account_required'] = isset($validatedData['on_account_required']) ? '1' : '0';
            $parentUrl = ConstantHelper::VENDOR_SERVICE_ALIAS;
            $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
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
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
                // Insert Book ID (if current_book exists)
                if (isset($services['current_book'])) {
                    $book = $services['current_book'];
                    $validatedData['book_id'] = $book ? $book->id : null;  
                } else {
                    $validatedData['book_id'] = null;
                }
                
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
            $companyName = $validatedData['company_name'] ?? ''; 
            $validatedData['display_name'] = $companyName;
            $vendor = Vendor::findOrFail($id);
            $validatedData['created_by'] = $vendor->created_by ?? $user->auth_user_id;
            if ($request->input('document_status') === 'submitted') {
                $validatedData['status'] =  $validatedData['status'] ?? ConstantHelper::ACTIVE;
            } else {
                $validatedData['status'] = ConstantHelper::DRAFT;
            }
            $gstnNo = $validatedData['compliance']['gstin_no'] ?? '';
            if (!$gstnNo) {
                $validatedData['deregistration_date'] = null;
                $validatedData['taxpayer_type'] = null;
                $validatedData['gst_status'] = null;
                $validatedData['block_status'] = null;
                $validatedData['legal_name'] = null;
                $validatedData['gst_state_id'] = null;
            } else {
                $gstValidation = EInvoiceHelper::validateGstinName($gstnNo);
                if ($gstValidation['Status'] === 1) {
                    $gstData = json_decode($gstValidation['checkGstIn'], true);
                    $validatedData['deregistration_date'] = $gstData['DtDReg'] ?? '';
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

            $currentStatus = $vendor->document_status;
            $actionType = $request->action_type ?? 'submit';
            $amendRemarks = $request->amend_remarks ?? null;
            
            if (($vendor->document_status == ConstantHelper::APPROVED || $vendor->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED)
                && $actionType == 'amendment') {

                $revisionData = [
                    ['model_type' => 'header', 'model_name' => 'Vendor', 'relation_column' => ''],
                    ['model_type' => 'detail', 'model_name' => 'VendorItem', 'relation_column' => 'vendor_id'],
                    ['model_type' => 'detail', 'model_name' => 'VendorPortalBook', 'relation_column' => 'vendor_id'],
                    ['model_type' => 'detail', 'model_name' => 'VendorPortalUser', 'relation_column' => 'vendor_id'],
                    ['model_type' => 'detail', 'model_name' => 'VendorLocation', 'relation_column' => 'vendor_id'],
                ];

                Helper::documentAmendment($revisionData, $vendor->id);
            }
             $vendor->update($validatedData);
           // Document approval logic for vendor
            if ($request->input('current_status') === ConstantHelper::SUBMITTED) {
                $bookId = $vendor->book_id;
                $docId = $vendor->id;
                $remarks = $request->remarks;
                $amendAttachments = $request->file('amend_attachments');
                $attachments = $request->file('attachment');
                $currentLevel = $vendor->approval_level ?? 1;
                $modelName = get_class($vendor);
                $submittedStatus = $request->input('status') ?? ConstantHelper::ACTIVE;
                
                if (($currentStatus == ConstantHelper::APPROVED || $currentStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                    $revisionNumber = $vendor->revision_number + 1;
                    $approve = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                    $vendor->revision_number = $revisionNumber;
                    $vendor->approval_level = 1;
                    $vendor->revision_date = now();
                    $statusAfterApproval = $approve['approvalStatus'] ?? $vendor->document_status;
                    $vendor->document_status = $statusAfterApproval;
                    if (in_array($statusAfterApproval, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                        if ($submittedStatus === ConstantHelper::INACTIVE) {
                            $vendor->status = ConstantHelper::INACTIVE;
                        } else {
                            $vendor->status = ConstantHelper::ACTIVE;
                        }
                        // ** START: Call createPartyLedger if conditions are met **
                        $shouldCreateLedger = isset($validatedData['create_ledger']) && $validatedData['create_ledger'] == 1;
                        $hiddenLedgerVendorName = $validatedData['hidden_ledger_vendor_name'];
                        $hiddenLedgerVendorCode = $validatedData['hidden_ledger_vendor_code'];
                        $ledgerId = $validatedData['ledger_id'] ?? null; 
                        $ledgerGroupId =$request->ledger_group_id ?? null; 
                        if ($shouldCreateLedger && !empty($hiddenLedgerVendorName) && !empty($hiddenLedgerVendorCode ) && !empty($ledgerGroupId )) {
                            try {
                                $result = Helper::createPartyLedger(
                                    'vendor',
                                    $hiddenLedgerVendorName,
                                    $hiddenLedgerVendorCode,
                                    $ledgerGroupId
                                );
                                if (!$result['success']) {
                                    Log::error('Error creating party ledger: ' . $result['message']);
                                    DB::rollBack();
                                    return response()->json([
                                        'status' => false,
                                        'message' => $result['message'], 
                                        'data' => $vendor,
                                    ], 500);
                                }
                                $ledgerId = $result['data']['ledger_id'] ?? null;
                                $ledgerGroupId = $result['data']['ledger_group_id'] ?? null;

                                $vendor->ledger_id = $ledgerId;
                                $vendor->ledger_group_id = $ledgerGroupId;
                                $vendor->create_ledger = 0;
                            } catch (Exception $e) {
                                Log::error('Exception creating party ledger: ' . $e->getMessage(), [
                                    'trace' => $e->getTraceAsString()
                                ]);
                                DB::rollBack();
                                return response()->json([
                                    'status' => false,
                                    'message' =>  $e->getMessage(), 
                                    'data' => $vendor,
                                ], 500);
                            }
                        } 
                        // ** END: Call createPartyLedger if conditions are met **
                    } else {
                        $vendor->status = $statusAfterApproval;
                    }

                } else {
                    $revisionNumber = $vendor->revision_number ?? 0;
                    $approve = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    $document_status = $approve['approvalStatus'] ;
                    $vendor->document_status = $document_status;
                    if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                         if ($revisionNumber == 0) {
                            $vendor->status = ConstantHelper::ACTIVE;
                         }
                           // ** START: Call createPartyLedger if conditions are met **
                        $shouldCreateLedger = isset($validatedData['create_ledger']) && $validatedData['create_ledger'] == 1;
                        $hiddenLedgerVendorName = $validatedData['hidden_ledger_vendor_name'];
                        $hiddenLedgerVendorCode = $validatedData['hidden_ledger_vendor_code'];
                        $ledgerId = $validatedData['ledger_id'] ?? null; 
                        $ledgerGroupId = $validatedData['ledger_group_id'] ?? null; 
                        if ($shouldCreateLedger && !empty($hiddenLedgerVendorName) && !empty($hiddenLedgerVendorCode)) {
                            try {
                                $result = Helper::createPartyLedger(
                                    'vendor',
                                    $hiddenLedgerVendorName,
                                    $hiddenLedgerVendorCode
                                );
                          
                                if (!$result['success']) {
                                    Log::error('Error creating party ledger: ' . $result['message']);
                                    DB::rollBack();
                                    return response()->json([
                                        'status' => false,
                                        'message' => $result['message'], 
                                        'data' => $vendor,
                                    ], 500);
                                }
                                $ledgerId = $result['data']['id'] ?? null;
                                $ledgerGroupId = $result['data']['ledger_group_id'] ?? null;

                                $vendor->ledger_id = $ledgerId;
                                $vendor->ledger_group_id = $ledgerGroupId;
                                $vendor->create_ledger = 0;
                            } catch (Exception $e) {
                                Log::error('Exception creating party ledger: ' . $e->getMessage(), [
                                    'trace' => $e->getTraceAsString()
                                ]);
                                DB::rollBack();
                                return response()->json([
                                    'status' => false,
                                    'message' =>  $e->getMessage(), 
                                    'data' => $vendor,
                                ], 500);
                            }
                        } 
                        // ** END: Call createPartyLedger if conditions are met **
                    } else {
                        $vendor->status = $document_status;
                    }
                }

            } else {
                $document_status = $request->current_status ?? ConstantHelper::DRAFT;
                $vendor->document_status = $document_status;
                $vendor->status = $document_status;
            }
            

            $vendor->save();

            $fileConfigs = [
                'pan_attachment' => ['folder' => 'pan_attachments', 'clear_existing' => true],
                'tin_attachment' => ['folder' => 'tin_attachments', 'clear_existing' => true],
                'aadhar_attachment' => ['folder' => 'aadhar_attachments', 'clear_existing' => true],
                'other_documents' => ['folder' => 'other_documents', 'clear_existing' => true],
            ];

            $this->fileUploadHelper->handleFileUploads($request, $vendor, $fileConfigs);

            $bankInfoData = $validatedData['bank_info'] ?? [];
            if (!empty($bankInfoData)) {
             $this->commonService->updateBankInfo($bankInfoData, $vendor);
            }
            
            $notesData = $validatedData['notes'] ?? [];
            if (!empty($notesData)) {
                $this->commonService->createNote($notesData,$vendor,$user); 
            }

            $contacts = $validatedData['contacts'] ?? [];
            if (!empty($contacts)) {
                $this->commonService->updateContact($contacts, $vendor);
            }

            $addresses = $validatedData['addresses'] ?? [];
            if (!empty($addresses)) {
                $this->commonService->updateAddress($addresses, $vendor);
            }

            $compliance = $validatedData['compliance'] ?? [];
            if (!empty($compliance)) {
                $this->commonService->updateCompliance($compliance, $vendor);
            }

            if ($request->has('vendor_item')) {
                $existingVendorItems = $vendor->approvedItems()->pluck('id')->toArray();
                $newItems = [];
                foreach ($request->input('vendor_item') as $vendorItemData) {
                    if (!empty($vendorItemData['item_code']) && !empty($vendorItemData['item_name'])) {
                        if (isset($vendorItemData['id']) && !empty($vendorItemData['id'])) {
                            $existingItem = $vendor->approvedItems()->where('id', $vendorItemData['id'])->first();
                            if ($existingItem) {
                                $updateData = [
                                    'item_id' => $vendorItemData['item_id'] ?? null,
                                    'item_code' => $vendorItemData['item_code'] ?? null,
                                    'cost_price' => $vendorItemData['cost_price'] ?? null, 
                                    'uom_id' => $vendorItemData['uom_id']?? null,
                                    'organization_id' => $validatedData['organization_id']?? null,
                                    'group_id' => $validatedData['group_id']?? null,
                                    'company_id' => $validatedData['company_id']?? null,
                                ];
                                if (isset($vendorItemData['item_id']) && !empty($vendorItemData['item_id'])) {
                                    $updateData['item_id'] = $vendorItemData['item_id'];
                                }
                                $existingItem->update($updateData);
                                $newItems[] = $existingItem->id;
                            }
                        } else {
                            $newItem = $vendor->approvedItems()->create([
                                'item_id' => $vendorItemData['item_id'] ?? null,
                                'item_code' => $vendorItemData['item_code'],
                                'cost_price' => $vendorItemData['cost_price'] ?? null, 
                                'uom_id' => $vendorItemData['uom_id']?? null,
                                'organization_id' => $organization->id ?? null, 
                                'group_id' => $organization->group_id ?? null, 
                                'company_id' => $organization->company_id ?? null, 
                            ]);
                            $newItems[] = $newItem->id;
                        }
                    } 
                }
                $itemsToDelete = array_diff($existingVendorItems, $newItems);
                if ($itemsToDelete) {
                    $vendor->approvedItems()->whereIn('id', $itemsToDelete)->delete();
                }
            } else {
                $vendor->approvedItems()->delete();
            }

              // Step 7: Synchronize Vendor Books
              $bookIds = $request->book_id ?? [];
              if (!empty($bookIds)) {
                  VendorPortalBook::where('vendor_id', $vendor->id)
                      ->whereNotIn('book_id', $bookIds)
                      ->delete();
                  foreach ($bookIds as $bookId) {
                      $book = Book::find($bookId);
                      if ($book) {
                          VendorPortalBook::updateOrCreate(
                              ['vendor_id' => $vendor->id, 'book_id' => $bookId],
                              ['service_id' => $book->service_id]
                          );
                      }
                  }
              } else {
                  VendorPortalBook::where('vendor_id', $vendor->id)->delete();
              }
  
              // Step 8: Synchronize Vendor Users
              $userIds = $request->user_id ?? [];
              if (!empty($userIds)) {
                  VendorPortalUser::where('vendor_id', $vendor->id)
                      ->whereNotIn('user_id', $userIds)
                      ->delete();
                  foreach ($userIds as $userId) {
                      VendorPortalUser::updateOrCreate(
                          ['vendor_id' => $vendor->id, 'user_id' => $userId]
                      );
                  }
              } else {
                  VendorPortalUser::where('vendor_id', $vendor->id)->delete();
              }

            //Sync Vendor Locations
            $storeIds = isset($request -> vendor_store) && is_array($request -> vendor_store) ? $request -> vendor_store : [];
            $locationSyncStatus = $vendor -> syncLocations($storeIds);
            if (!$locationSyncStatus['status']) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => $locationSyncStatus['message'],
                    'error' => '',
                ], 422);
            }  
              DB::commit();

              return response()->json([
                  'status' => true,
                  'message' => 'Record updated successfully',
                  'data' => $vendor,
              ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update record',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        public function revoke(Request $request)
        {
            DB::beginTransaction();
            try {
                $vendor = Vendor::find($request->id);
                if (isset($vendor)) {
                    $revoke = Helper::approveDocument($vendor->book_id, $vendor->id, $vendor->revision_number, '', [], 0, ConstantHelper::REVOKE, 0, get_class($vendor));
                    if ($revoke['message']) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => $revoke['message'],
                        ]);
                    } else {
                        $vendor->document_status = $revoke['approvalStatus'];
                        $vendor->status = $revoke['approvalStatus'];
                        $vendor->save();
                        DB::commit();
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Revoked successfully',
                        ]);
                    }
                } else {
                    DB::rollBack();
                    throw new ApiGenericException("No Document found");
                }
            } catch(Exception $ex) {
                DB::rollBack();
                throw new ApiGenericException($ex->getMessage());
            }
        }
    
        public function showImportForm()
        {
            $urlSegmentAlias = request()->segments()[0];
            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($urlSegmentAlias);
            if (count($servicesBooks['services']) == 0) {
                return redirect()->route('/');
            }
            return view('procurement.vendor.import'); 
        }

        public function import(Request $request)
        {
            $user = Helper::getAuthenticatedUser();
            
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
            
                UploadVendorMaster::where('user_id', $user->auth_user_id)->delete();
            
                $import = new VendorImport($this->itemImportExportService); 
                Excel::import($import, $file);
            
                $successfulVendors = $import->getSuccessfulVendors();
                $failedVendors = $import->getFailedVendors();
                $mailData = [
                    'modelName' => 'Vendor',
                    'successful_items' => $successfulVendors,
                    'failed_items' => $failedVendors,
                    'export_successful_url' => route('vendors.export.successful'), 
                    'export_failed_url' => route('vendors.export.failed'), 
                ];
            
                if (count($failedVendors) > 0) {
                    $message = 'Record import failed. Some records were not imported.';
                    $status = 'failure';
                } else {
                    $message = 'Record import successfully.';
                    $status = 'success';
                }

                if ($user->email) {
                    try {
                        Mail::to($user->email)->send(new ImportComplete( $mailData)); 
                    } catch (Exception $e) {
                        $message .= " However, there was an error sending the email notification.";
                    }
                }
            
                return response()->json([
                    'status' => $status,
                    'message' => $message,
                    'successful_vendors' => $successfulVendors,
                    'failed_vendors' => $failedVendors,
                ], 200);
            
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 30MB.',
                ], 400);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to import vendors: ' . $e->getMessage(),
                ], 500);
            }
        }

        public function exportSuccessfulVendors()
        {
            $user = Helper::getAuthenticatedUser();
            $uploadVendors = UploadVendorMaster::where('status', 'Success')->where('user_id', $user->id)->get();
            $vendors = Vendor::with(['category', 'subcategory', 'currency', 'paymentTerms', 'erpOrganizationType', 'addresses', 'compliances', 'ledgerGroup', 'ledger'])
                ->whereIn('company_name', $uploadVendors->pluck('company_name'))
                ->get();
        
            return Excel::download(new VendorsExport($vendors, $this->itemImportExportService), "successful-vendors.xlsx");
        }
        
        public function exportFailedVendors()
        {
            $user = Helper::getAuthenticatedUser();
            $failedVendors = UploadVendorMaster::where('status', 'Failed')->where('user_id', $user->id)->get();
            return Excel::download(new FailedVendorsExport($failedVendors), "failed-vendors.xlsx");
        }
        public function checkGst()
        {
            try {
                GstStatusChecker::checkInvalidGst();
                return response()->json(['message' => 'GST number(s) verified successfully!']);
            } catch (Exception $e) {
                \Log::error('Error while verifying GST: ' . $e->getMessage());
        
                return response()->json([
                    'message' => 'Something went wrong while verifying GST.',
                    'error' => $e->getMessage()
                ], 500);
            }
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
            } catch (Exception $e) {
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
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }
        }

        public function deleteVendorItem($id)
        {
            DB::beginTransaction();
            try {
                $vendorItem = VendorItem::find($id);

                if ($vendorItem) {
                    $result = $vendorItem->deleteWithReferences();

                    if (!$result['status']) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => $result['message']], 400);
                    }

                    DB::commit();
                    return response()->json(['status' => true, 'message' => 'Record deleted successfully']);
                }

                return response()->json(['status' => false, 'message' => 'Vendor item not found'], 404);
            } catch (Exception $e) {
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
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }
        }

        public function destroy($id)
        {
            try {
                $vendor = Vendor::findOrFail($id);
        
                $referenceTables = [
                    'erp_addresses' => ['addressable_id'],
                    'erp_contacts' => ['contactable_id'],
                    'erp_bank_infos' => ['morphable_id'],
                    'erp_notes' => ['noteable_id'],
                    'erp_vendor_items' => ['vendor_id'],
                    'erp_compliances' => ['morphable_id'],
                ];
        
                $result = $vendor->deleteWithReferences($referenceTables);
        
                if (!$result['status']) {
                    return response()->json([
                        'status' => false,
                        'message' => $result['message'],
                        'referenced_tables' => $result['referenced_tables'] ?? []
                    ], 400);
                }
        
                return response()->json([
                    'status' => true,
                    'message' => 'Record deleted successfully.',
                ], 200);
        
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while deleting the vendor: ' . $e->getMessage()
                ], 500);
            }
        }
        
      
        public function getVendor(Request $request)
        {
            $searchTerm = $request->input('q', '');
            $vendors = Vendor::where(function ($query) use ($searchTerm) {
                    $query->where('company_name', 'like', "%{$searchTerm}%")
                        ->orWhere('vendor_code', 'like', "%{$searchTerm}%");  
                })
                ->where('status', ConstantHelper::ACTIVE)
                ->limit(10)
                ->get(['id', 'company_name','vendor_code']);
            if ($vendors->isEmpty()) {
                $vendors = Vendor::where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'company_name','vendor_code']);
            }
        
            return response()->json($vendors);
        } 
        
        public function users(Request $req)
        {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first(); 
            $organizationId = $organization?->id ?? null;
            $companyId = $organization?->company_id ?? null;
            $type = 'IAM-SUPPLIER';
            $email = $req->input('email');
            $name = $req->input('name');

            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $name = htmlspecialchars($name);

            if (!$email || !$name) {
                return response()->json(['error' => 'Email and name are required'], 400);
            }

            $user = User::firstOrNew([
                'email' => $email,
                'user_type' => $type
            ]);
        
            if (!isset($user->id)) {
                $user->password = Hash::make('Admin@123');
            }
            $user->name = $name;
            $user->user_type = $type;
            $user->organization_id = $organizationId;
            $user->save();

            return response()->json([
                'message' => 'User saved successfully',
                'user' => $user
            ], 200);
        }

        public function getUOM(Request $request)
        {
            $itemId = $request->get('item_id');
            $item = Item::find($itemId);
        
            if (!$item) {
                return response()->json(['error' => 'Item not found'], 404);
            }
            $itemUOM = $item->uom_id;
            $alternateUOMs = $item->alternateUOMs;
            $uoms = collect([$itemUOM])->merge($alternateUOMs->pluck('uom_id'))->unique();
            $uomDetails = Unit::whereIn('id', $uoms)->get();
            $response = [
                'uom_id' => $itemUOM,
                'uom_name' => $item->uom->name,
                'alternate_uoms' => $uomDetails->map(function($uom) {
                    return [
                        'id' => $uom->id,
                        'name' => $uom->name,
                    ];
                }),
            ];
        
            return response()->json($response);
        }

       public function getLedgerGroupsByType(Request $request, $ledgerId = null)
        {
          $type = strtolower($request->query('type'));

           if (!in_array($type, ['vendor', 'customer'])) {
                return response()->json(['error' => 'Invalid type provided.'], 400);
            }

            if ($ledgerId) {
                $ledger = Ledger::find($ledgerId);
                if ($ledger) {
                    $groups = $ledger->group();

                    if ($groups instanceof \Illuminate\Database\Eloquent\Collection) {
                        $groupItems = $groups->map(fn($group) => [
                            'id' => $group->id,
                            'name' => $group->name
                        ]);
                    } elseif ($groups) {
                        $groupItems = collect([
                            ['id' => $groups->id, 'name' => $groups->name]
                        ]);
                    } else {
                        $groupItems = collect(); 
                    }

                    return response()->json($groupItems->values());
                }
            }

            $defaultGroupName = $type === 'vendor' ? 'Account Payable' : 'Account Receivable';

            $parentGroup = Group::with(['children'])
              ->where('status', 'active')
              ->where('name', $defaultGroupName)
              ->first();

            if (!$parentGroup) {
                return response()->json(['error' => 'Group not found.'], 404);
            }
            $lastLevelGroupIds = $parentGroup->getAllLastLevelGroupIds();

             $groups = Group::whereIn('id', $lastLevelGroupIds)->get();

            $groupItems = $groups->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name
            ]);

            return response()->json($groupItems->values());
        }

}
