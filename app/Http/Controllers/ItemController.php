<?php

namespace App\Http\Controllers;
use App\Models\ItemSubType;
use Yajra\DataTables\DataTables;
use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Models\SubType;
use App\Models\Hsn;
use App\Models\Unit;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Models\Attribute;
use App\Models\Currency;
use App\Models\AttributeGroup;
use App\Models\AlternateUOM;
use App\Models\ProductSpecification;
use App\Models\CustomerItem;
use App\Models\VendorItem;
use App\Models\ItemAttribute;
use App\Models\AlternateItem;
use App\Helpers\Helper; 
use App\Imports\ItemImport;
use App\Services\CommonService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Helpers\ItemHelper;
use App\Helpers\ServiceParametersHelper;
use App\Exports\ItemsExport;
use App\Exports\FailedItemsExport;
use App\Models\UploadItemMaster;
use App\Services\ItemImportExportService;
use Carbon\Carbon;
use App\Mail\ImportComplete;
use Illuminate\Support\Facades\Mail;
use Auth;
use stdClass;


class ItemController extends Controller
{
    protected $commonService;
    protected $itemImportExportService;

    public function __construct(CommonService $commonService, ItemImportExportService $itemImportExportService)
    {
        $this->commonService = $commonService;
        $this->itemImportExportService = $itemImportExportService;
        
    }
  
    public function index()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
    
        if (request()->ajax()) {
            $query = Item::WithDefaultGroupCompanyOrg()
                ->with(['uom', 'hsn', 'category', 'subcategory', 'subTypes','auth_user'])
                ->orderBy('id', 'desc');
                
            if ($status = request(key: 'status')) {
                $query->where('status', $status);
            }
    
            if ($hsnId = request(key: 'hsn_id')) {
                $query->where('hsn_id', $hsnId);
            }
            if ($subtypeId = request('sub_type_id')) {
                $query->whereHas('subTypes', function ($query) use ($subtypeId) {
                    $query->where('sub_type_id', $subtypeId);
                });
            }
            if ($categoryId = request('category_id')) {
                $query->where('category_id', $categoryId);
            }
    
            if ($subcategoryId = request('subcategory_id')) {
                $query->where('subcategory_id', $subcategoryId);
            }
    
            if ($type = request('type')) {
                $query->where('type', $type);
            }

           return DataTables::of($query) 
                ->addIndexColumn()
                ->addColumn('subtypes', function($row) {
                    $subTypes = '';
                    foreach ($row->subTypes as $subTypeIndex => $subTypeVal) {
                        $subTypes .= (($subTypeIndex == 0 ? '' : ',') . $subTypeVal -> subType ?-> name);
                    }
                    return $row->subTypes->isEmpty() ? 'No Subtypes' : $subTypes;
                })
                ->editColumn('uom', function ($item) {
                    return $item->uom ? $item->uom->name : 'N/A';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : 'N/A';
                })
                
                ->editColumn('created_by', function ($row) {
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
    
                    $action = '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="' . route('item.edit', $row->id) . '">
                                    <i data-feather="edit-3" class="me-50"></i>
                                    <span>Edit</span>
                                </a>
                            </div>
                        </div>';
                    return '<div class="d-flex align-items-center justify-content-end">' . $status . $action . '</div>';
                })
                ->rawColumns(['status_action'])
                ->make(true);

        }
        $subtypes = SubType::where('status', 'active')->get();
        $hsns = Hsn::withDefaultGroupCompanyOrg()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
    
        $categories = Category::withDefaultGroupCompanyOrg()
            ->where('status', ConstantHelper::ACTIVE)
            ->whereNull('parent_id')
            ->get();
    
        $types = ConstantHelper::ITEM_TYPES;
    
        return view('procurement.item.index', compact('hsns', 'categories', 'types','subtypes'));
    }
    

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $currencies = Currency::where('status', operator: ConstantHelper::ACTIVE)->get();
        $subTypes = SubType::where('status', ConstantHelper::ACTIVE)->get();
        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $units = Unit::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $organizations = Organization::where('status', ConstantHelper::ACTIVE)->get();
        $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')->WithDefaultGroupCompanyOrg()->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $customers = Customer::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $attributeGroups = AttributeGroup::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $allItems = Item::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $types = ConstantHelper::ITEM_TYPES;
        $storageTypes = ConstantHelper::STORAGE_TYPES;
        $status = ConstantHelper::STATUS;
        $service = ConstantHelper::IS_SERVICE;
        $options = ConstantHelper::STOP_OPTIONS;
        $specificationGroups = ProductSpecification::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $itemCodeType ='Manual';
        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book=$services['current_book'];
                if ($book) {
                    $parameters = new stdClass(); 
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->item_code_type) && is_array($parameters->item_code_type)) {
                        $itemCodeType = $parameters->item_code_type[0] ?? null;
                    }
                }
         }
        }
        
        return view('procurement.item.create', [
            'hsns' => $hsns,
            'units' => $units,
            'categories' => $categories,
            'vendors' => $vendors,
            'customers' => $customers,
            'types' => $types,
            'status' => $status,
            'service'=>$service,
            'options'=>$options,
            'organizations'=>$organizations,
            'organization'=>$organization,
            'subTypes'=>$subTypes,
            'storageTypes'=>$storageTypes,
            'attributeGroups'=>$attributeGroups,
            'allItems'=>$allItems,
            'specificationGroups'=>$specificationGroups,
            'itemCodeType' => $itemCodeType,
            'currencies'=>$currencies, 

        ]);
    }

    public function generateItemCode(Request $request)
    {
        $itemName = $request->input('item_name');
        $itemId = $request->input('item_id');
        $subType = $request->input('sub_type');
        $categoryInitials = $request->input('cat_initials');
        $subCategoryInitials = $request->input('sub_cat_initials');
        $itemInitials = $request->input('item_initials');
        $prefix = $request->input('prefix', ''); 
        $baseCode =  $prefix .$subType . $subCategoryInitials . $itemInitials;

        $authUser = Helper::getAuthenticatedUser();
        $organizationId = $authUser->organization_id;
        if ($itemId) {
            $existingItem = Item::withDefaultGroupCompanyOrg()->find($itemId);
            if ($existingItem) {
                $existingItemCode = $existingItem->item_code;
                $currentBaseCode = substr($existingItemCode, 0, strlen($baseCode));
                if ($currentBaseCode === $baseCode) {
                    return response()->json(['item_code' => $existingItemCode]);
                }
            }
        }
        $lastSimilarItem = Item::withDefaultGroupCompanyOrg()
           ->where('item_code', 'like', "{$baseCode}%")
            ->orderBy('item_code', 'desc')->first();
    
        $nextSuffix = '001';
        if ($lastSimilarItem) {
            $lastSuffix = intval(substr($lastSimilarItem->item_code, -3));
            $nextSuffix = str_pad($lastSuffix + 1, 3, '0', STR_PAD_LEFT);
        }
        $finalItemCode = $baseCode . $nextSuffix;
    
        return response()->json(['item_code' => $finalItemCode]);
    }
    

    public function store(ItemRequest $request)
    {
      DB::beginTransaction();
     try {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        if ($validatedData['uom_id'] == $validatedData['storage_uom_id']) {
            $validatedData['storage_uom_conversion'] = 1; 
        }
        $validatedData['created_by'] = $user->auth_user_id; 
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
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
        if ($request->document_status === 'submitted') {
            $validatedData['status'] = $validatedData['status'] ?? ConstantHelper::ACTIVE; 
        } else {
            $validatedData['status'] = ConstantHelper::DRAFT;
        }
    
        $item = Item::create($validatedData);
        if ($request->has('sub_types')) {
            // $item->subTypes()->attach($request->input('sub_types'));
            // $item->subTypesData()->attach($request->input('sub_types'));
            foreach ($request->sub_types as $subType) {
                ItemSubType::create([
                    'item_id' => $item -> id,
                    'sub_type_id' => $subType
                ]);
            }
        }
        if ($request->has('alternate_uoms')) {
            foreach ($request->input('alternate_uoms') as $uomData) {
                if (isset($uomData['uom_id']) && !empty($uomData['uom_id']) &&
                    isset($uomData['conversion_to_inventory']) && !empty($uomData['conversion_to_inventory'])) {
                    $item->alternateUOMs()->create([
                        'uom_id' => $uomData['uom_id'],
                        'conversion_to_inventory' => $uomData['conversion_to_inventory'],
                        'cost_price' => $uomData['cost_price'],
                        'sell_price' => $uomData['sell_price'],
                        'is_selling' => isset($uomData['is_selling']) && $uomData['is_selling'] == '1',
                        'is_purchasing' => isset($uomData['is_purchasing']) && $uomData['is_purchasing'] == '1',
                    ]);
                }
            }
        }
        
        if ($request->has('approved_customer')) {
           
            foreach ($request->input('approved_customer') as $approvedCustomerData) {
        
                if (isset($approvedCustomerData['customer_id']) && !empty($approvedCustomerData['customer_id'])) {
                    $item->approvedCustomers()->create([
                        'customer_id' => $approvedCustomerData['customer_id'],
                        'customer_code' => $approvedCustomerData['customer_code'] ?? null,
                        'item_code' => $approvedCustomerData['item_code'] ?? null, 
                        'item_name' => $approvedCustomerData['item_name'] ?? null, 
                        'item_details' => $approvedCustomerData['item_details'] ?? null,
                        'sell_price' => $approvedCustomerData['sell_price']?? null,
                        'uom_id' => $approvedCustomerData['uom_id']?? null,
                        'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                    ]);
                }
            }
        }
        
        if ($request->has('approved_vendor')) {
            $item->approvedVendors()->delete();
            foreach ($request->input('approved_vendor') as $approvedVendorData) {
                if (isset($approvedVendorData['vendor_id']) && !empty($approvedVendorData['vendor_id'])) {
                    $item->approvedVendors()->create([
                        'vendor_id' => $approvedVendorData['vendor_id'],
                        'vendor_code' => $approvedVendorData['vendor_code'] ?? null, 
                        'cost_price' => $approvedVendorData['cost_price'] ?? null, 
                        'uom_id' => $approvedVendorData['uom_id']?? null,
                        'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                    ]);
                }
            }
        }

        if ($request->has('attributes')) {
            foreach ($request->input('attributes') as $attributeGroupData) {
                $attributeGroupId = $attributeGroupData['attribute_group_id'] ?? null;
                $attributeIds = $attributeGroupData['attribute_id'] ?? [];
                $requiredBom = isset($attributeGroupData['required_bom']) ? (int) $attributeGroupData['required_bom'] : 0;
                $allChecked = isset($attributeGroupData['all_checked']) ? (int) $attributeGroupData['all_checked'] : 0;
                if ($attributeGroupId && ($attributeIds || $allChecked)) {
                    $item->itemAttributes()->create([
                        'attribute_group_id' => $attributeGroupId,
                        'attribute_id' => $attributeIds,
                        'required_bom' => $requiredBom, 
                        'all_checked' => $allChecked 
                    ]);
                }
            }
        }

        if ($request->has('alternateItems')) {
            foreach ($request->input('alternateItems') as $alternateItemData) {
                if (isset($alternateItemData['item_code']) && !empty($alternateItemData['item_code']) &&
                    isset($alternateItemData['item_name']) && !empty($alternateItemData['item_name'])) {
                    $item->alternateItems()->create([
                        'item_code' => $alternateItemData['item_code'],
                        'item_name' => $alternateItemData['item_name'],
                    ]);
                }
            }
        }

        if ($request->has('item_specifications')) {
            foreach ($request->input('item_specifications') as $specificationData) {
                if (isset($specificationData['specification_name']) && !empty($specificationData['specification_name'])) {
                    $item->specifications()->create([
                        'group_id' => $specificationData['group_id'] ?? null,
                        'specification_id' => $specificationData['specification_id'] ?? null,
                        'specification_name' => $specificationData['specification_name'],
                        'value' => $specificationData['value'] ?? null,
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Record created successfully',
            'data' => $item,
        ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show(Item $item)
    {
        // You can implement this if needed
    }
    public function showImportForm()
    {
        return view('procurement.item.import');
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
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(filename: $file);
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
            $deleteQuery = UploadItemMaster::where('user_id', $user->id);
            $deleteQuery->delete();
    
            $import = new ItemImport($this->itemImportExportService);
            Excel::import($import, $request->file('file'));
            
            $successfulItems = $import->getSuccessfulItems();
            $failedItems = $import->getFailedItems();
            $mailData = [
                'modelName' => 'Item',
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
                'export_successful_url' => route('items.export.successful'), 
                'export_failed_url' => route('items.export.failed'), 
            ];
            if (count($failedItems) > 0) {
                $message = 'Items import failed.';
                $status = 'failure';
            } else {
                $message = 'Items imported successfully.';
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
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 30MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import items: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function exportSuccessfulItems()
    {
        $uploadItems = UploadItemMaster::where('status','Success') 
        ->get();
        $items = Item::with(['category','subTypes', 'subcategory', 'hsn', 'uom', 'itemAttributes', 'specifications', 'alternateUOMs'])
            ->whereIn('item_code', $uploadItems->pluck('item_code'))
            ->get();
        return Excel::download(new ItemsExport($items, $this->itemImportExportService), "successful-items.xlsx");
    }

    public function exportFailedItems()
    {
        $failedItems = UploadItemMaster::where('status', operator: 'Failed')  
        ->get();
        return Excel::download(new FailedItemsExport($failedItems), "failed-items.xlsx");
    }

    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $currencies = Currency::where('status', operator: ConstantHelper::ACTIVE)->get();
        $item = Item::findOrFail($id);
        $subTypes = $item->subTypes; 
        $subtypeNames = $subTypes->map(function ($itemSubType) {
            return optional($itemSubType->subType)->name;
        })->filter()->toArray(); 

        $defaultItemTables = [ "erp_bom_details", "erp_rate_contract_items", "erp_pi_items", "erp_po_items", "erp_mo_items", "erp_pwo_items"];
        $itemTablesForFinishedAndSemiFinished = [ "erp_boms", "erp_bom_production_items", "erp_so_items", "erp_mo_production_items", "erp_mo_products"];

        if (!empty(array_intersect($subtypeNames, ['Finished Goods', 'WIP/Semi Finished']))) {
            $tablesToCheck = array_merge($defaultItemTables, $itemTablesForFinishedAndSemiFinished);
        } else {
            $tablesToCheck = $defaultItemTables;
        }

        $referenceColumns = ['erp_item_id', 'item_id'];
        $isModifyResult = $item->isModify($referenceColumns, $tablesToCheck);
        $isItemReferenced = $isModifyResult['status'];

        $defaultAttributeTables = ["erp_bom_attributes","erp_rate_contract_item_attributes", "erp_pi_item_attributes", "erp_po_item_attributes", "erp_mo_item_attributes", "erp_pwo_item_attributes"];
        $attributeTablesForFinishedAndSemiFinished = [
            "erp_so_item_attributes","erp_mo_production_item_attributes", "erp_mo_product_attributes",
        ];
        if (!empty(array_intersect($subtypeNames, ['Finished Goods', 'WIP/Semi Finished']))) {
            $attributeTablesToCheck = array_merge($defaultAttributeTables, $attributeTablesForFinishedAndSemiFinished);
        } else {
            $attributeTablesToCheck = $defaultAttributeTables;
        }
        
        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $units = Unit::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $categories = Category::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->whereNull('parent_id')  ->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $customers = Customer::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $types = ConstantHelper::ITEM_TYPES;
        $storageTypes = ConstantHelper::STORAGE_TYPES;
        $status = ConstantHelper::STATUS;
        $options = ConstantHelper::STOP_OPTIONS;
        $service = ConstantHelper::IS_SERVICE;
        $organizations = Organization::where('status', ConstantHelper::ACTIVE)->get();
        $subTypes = SubType::where('status', ConstantHelper::ACTIVE)->get();
        $attributeGroups = AttributeGroup::with('attributes')->WithDefaultGroupCompanyOrg()->get();
        $allItems = Item::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $specificationGroups = ProductSpecification::where('status', ConstantHelper::ACTIVE)->WithDefaultGroupCompanyOrg()->get();
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $bomCheckResult = ItemHelper::checkItemBomExists($item->id, [], 'bom', null);
        $isBomExists = $bomCheckResult['status'] === 'bom_exists';
        $itemCodeType ='Manual';
        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book=$services['current_book'];
                if ($book) {
                    $parameters = new stdClass(); 
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->item_code_type) && is_array($parameters->item_code_type)) {
                        $itemCodeType = $parameters->item_code_type[0] ?? null;
                    }
                }
         }
        }
        return view('procurement.item.edit', [
            'item' => $item,
            'hsns' => $hsns,
            'units' => $units,
            'categories' => $categories,
            'vendors' => $vendors,
            'customers' => $customers,
            'types' => $types,
            'status' => $status,
            'options'=>$options,
            'organizations'=>$organizations,
            'organization'=>$organization,
            'subTypes'=>$subTypes,
            'storageTypes'=>$storageTypes,
            'attributeGroups'=>$attributeGroups,
            'allItems'=>$allItems,
            'service'=>$service,
            'specificationGroups'=>$specificationGroups,
            'itemCodeType' => $itemCodeType, 
            'isItemReferenced' => $isItemReferenced,
            'tablesToCheck'=>$attributeTablesToCheck,
            'currencies'=>$currencies,
            'isBomExists'=>$isBomExists
        ]);
    }

    public function update(ItemRequest $request, $id = null)
    {
        DB::beginTransaction();
    try {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        $validatedData = $request->validated();
        if ($validatedData['uom_id'] == $validatedData['storage_uom_id']) {
            $validatedData['storage_uom_conversion'] = 1;
        }
        $validatedData['created_by'] = $item->created_by ?? $user->auth_user_id;
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
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
        if ($request->input('document_status') === 'submitted') {
            $validatedData['status'] = $validatedData['status'] ?? ConstantHelper::ACTIVE; 
        } else {
            $validatedData['status'] = ConstantHelper::DRAFT;
        }
    
        $item->fill($validatedData);
        $item->save();
        if ($request->type === 'Goods') {
            $previousSubTypes = $item -> subTypes -> pluck('sub_type_id') -> toArray();
            $requestSubTypes = $request->sub_types ? $request->sub_types : [];
            if (!(empty(array_diff($previousSubTypes, $requestSubTypes)) && empty(array_diff($requestSubTypes, $previousSubTypes)))) {
                ItemSubType::where('item_id', $item -> id) -> delete();
                if ($request->has('sub_types')) {
                    // $item->subTypesData()->sync($request->input('sub_types'));
                    foreach ($request->sub_types as $subType) {
                        ItemSubType::create([
                            'item_id' => $item -> id,
                            'sub_type_id' => $subType
                        ]);
                    }
                } else {
                    ItemSubType::where('item_id', $item -> id) -> delete();
                }
            }
        }
         else {
            ItemSubType::where('item_id', $item -> id) -> delete();
        }
    
        if ($request->has('alternate_uoms')) {
            $existingUOMs = $item->alternateUOMs()->pluck('id')->toArray();
            $newUOMs = [];
            foreach ($request->input('alternate_uoms') as $uomData) {
                if (isset($uomData['uom_id']) && !empty($uomData['uom_id']) && 
                isset($uomData['conversion_to_inventory']) && !empty($uomData['conversion_to_inventory'])) {
                if (isset($uomData['id']) && in_array($uomData['id'], $existingUOMs)) {
                    $item->alternateUOMs()->where('id', $uomData['id'])->update([
                        'uom_id' => $uomData['uom_id'],
                        'conversion_to_inventory' => $uomData['conversion_to_inventory'] ?? null,
                        'cost_price' => $uomData['cost_price']?? null,
                        'sell_price' => $uomData['sell_price']?? null,
                        'is_selling' => isset($uomData['is_selling']) && $uomData['is_selling'] == '1',
                        'is_purchasing' => isset($uomData['is_purchasing']) && $uomData['is_purchasing'] == '1',
                    ]);
                    $newUOMs[] = $uomData['id'];
                } else {
                    $newUOM = $item->alternateUOMs()->create([
                        'uom_id' => $uomData['uom_id'],
                        'conversion_to_inventory' => $uomData['conversion_to_inventory'] ?? null,
                        'cost_price' => $uomData['cost_price']?? null,
                        'sell_price' => $uomData['sell_price']?? null,
                        'is_selling' => isset($uomData['is_selling']) && $uomData['is_selling'] == '1',
                        'is_purchasing' => isset($uomData['is_purchasing']) && $uomData['is_purchasing'] == '1',
                    ]);
                  
                    $newUOMs[] = $newUOM->id;
                }
              }
            }
            $item->alternateUOMs()->whereNotIn('id', $newUOMs)->delete();
        }else {
            $item->alternateUOMs()->delete();
        }

        if ($request->has('approved_customer')) {
            $existingCustomers = $item->approvedCustomers()->pluck('id')->toArray();
            $newCustomers = [];
            foreach ($request->input('approved_customer') as $customerData) {
                if (isset($customerData['customer_id']) && !empty($customerData['customer_id'])) {
                if (isset($customerData['id']) && in_array($customerData['id'], $existingCustomers)) {
                    $item->approvedCustomers()->where('id', $customerData['id'])->update([
                        'customer_id' => $customerData['customer_id'],
                        'customer_code' => $customerData['customer_code'] ?? null,
                        'item_code' => $customerData['item_code'] ?? null,
                        'item_name' => $customerData['item_name'] ?? null,
                        'item_details' => $customerData['item_details'] ?? null,
                        'sell_price' => $customerData['sell_price']?? null,
                        'uom_id' => $customerData['uom_id']?? null,
                        'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                        
                    ]);
                    $newCustomers[] = $customerData['id'];
                } else {
                    $newCustomer = $item->approvedCustomers()->create([
                        'customer_id' => $customerData['customer_id'],
                        'customer_code' => $customerData['customer_code'] ?? null,
                        'item_code' => $customerData['item_code'] ?? null,
                        'item_name' => $customerData['item_name'] ?? null,
                        'item_details' => $customerData['item_details'] ?? null,
                        'sell_price' => $customerData['sell_price']?? null,
                        'uom_id' => $customerData['uom_id']?? null,
                        'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                    ]);
                    $newCustomers[] = $newCustomer->id;
                }
             }
            }
    
            $item->approvedCustomers()->whereNotIn('id', $newCustomers)->delete();
        }else {
            $item->approvedCustomers()->delete();
        }
    
        if ($request->has('approved_vendor')) {
            $existingVendors = $item->approvedVendors()->pluck('id')->toArray();
            $newVendors = [];
    
            foreach ($request->input('approved_vendor') as $vendorData) {
                if (isset($vendorData['vendor_id']) && !empty($vendorData['vendor_id'])) {
                if (isset($vendorData['id']) && in_array($vendorData['id'], $existingVendors)) {
                    $item->approvedVendors()->where('id', $vendorData['id'])->update([
                        'vendor_id' => $vendorData['vendor_id'],
                        'vendor_code' => $vendorData['vendor_code'] ?? null,
                        'cost_price' => $vendorData['cost_price']?? null,
                        'uom_id' => $vendorData['uom_id']?? null,
                        'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                        
                    ]);
                    $newVendors[] = $vendorData['id'];
                } else {
                    $newVendor = $item->approvedVendors()->create([
                        'vendor_id' => $vendorData['vendor_id'],
                        'vendor_code' => $vendorData['vendor_code'] ?? null,
                        'cost_price' => $vendorData['cost_price']?? null,
                        'uom_id' => $vendorData['uom_id']?? null,
                        'organization_id' => $validatedData['organization_id']?? null,
                        'group_id' => $validatedData['group_id']?? null,
                        'company_id' => $validatedData['company_id']?? null,
                    ]);
                    $newVendors[] = $newVendor->id;
                }
              }
            }
            $item->approvedVendors()->whereNotIn('id', $newVendors)->delete();
        }else {
            $item->approvedVendors()->delete();
        }
    
        if ($request->has('attributes')) {
            $existingAttributes = $item->itemAttributes()->pluck('id')->toArray();
            $newAttributes = [];
            foreach ($request->input('attributes') as $attributeData) {
                $attributeId = $attributeData['attribute_id'] ?? [];
                $attributeGroupId = $attributeData['attribute_group_id'] ?? null;
                $requiredBom = isset($attributeData['required_bom']) ? (int) $attributeData['required_bom'] : 0;
                $allChecked = isset($attributeData['all_checked']) ? (int) $attributeData['all_checked'] : 0;
                if ($attributeGroupId && ($attributeId || $allChecked)) {
                if (isset($attributeData['id'])) {
                    if ($attributeGroupId || $attributeId) {
                        $item->itemAttributes()->where('id', operator: $attributeData['id'])->update([
                            'attribute_id' => $attributeId,
                            'attribute_group_id' => $attributeGroupId,
                            'required_bom' => $requiredBom,
                            'all_checked' => $allChecked,
                        ]);
                        $newAttributes[] = $attributeData['id'];
                    } else {
                        return response()->json(['error' => 'Missing attribute_id or attribute_group_id for existing attribute.'], 400);
                    }
                } else {
                    if ($attributeGroupId || $attributeId) {
                        $newAttribute = $item->itemAttributes()->create([
                            'attribute_id' => $attributeId,
                            'attribute_group_id' => $attributeGroupId,
                            'required_bom' => $requiredBom,
                            'all_checked' => $allChecked,
                        ]);
                        $newAttributes[] = $newAttribute->id;
                    } else {
                        return response()->json(['error' => 'Missing attribute_id or attribute_group_id for new attribute.'], 400);
                    }
                }
                
             }
            }
            $item->itemAttributes()->whereNotIn('id', $newAttributes)->delete();
        }else {
            $item->itemAttributes()->delete();
        }
    
        if ($request->has('alternateItems')) {
            $existingAlternateItems = $item->alternateItems()->pluck('id')->toArray();
            $newAlternateItems = [];
      
            foreach ($request->input('alternateItems') as $altItemData) {
                if (isset($altItemData['item_code']) && !empty($altItemData['item_code']) &&
                isset($altItemData['item_name']) && !empty($altItemData['item_name'])) {
                if (isset($altItemData['id']) && in_array($altItemData['id'], $existingAlternateItems)) {
                    $item->alternateItems()->where('id', $altItemData['id'])->update([
                        'item_name' => $altItemData['item_name'], 
                        'item_code' => $altItemData['item_code'], 
                    ]);
                    $newAlternateItems[] = $altItemData['id'];
                } else {
                    $newAltItem = $item->alternateItems()->create([
                        'item_name' => $altItemData['item_name'],
                        'item_code' => $altItemData['item_code'], 
                    ]);
                    $newAlternateItems[] = $newAltItem->id;
                }
             }
            }
            $item->alternateItems()->whereNotIn('id', $newAlternateItems)->delete();
        }else {
            $item->alternateItems()->delete();
        }
    
        if ($request->has('item_specifications')) {
            $specifications = $request->input('item_specifications');
            $item->specifications()->delete();
            foreach ($specifications as $specificationData) {
                if (isset($specificationData['specification_name']) && !empty($specificationData['specification_name'])) {
                    $item->specifications()->create([
                        'group_id' => $specificationData['group_id'] ?? null,
                        'specification_id' => $specificationData['specification_id'] ?? null,
                        'specification_name' => $specificationData['specification_name'],
                        'value' => $specificationData['value'] ?? null,
                    ]);
                }
            }
        }else {
            $item->specifications()->delete();
        }
      
        DB::commit();
        return response()->json(['message' => 'Record updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAlternateUOM($id)
    {
        DB::beginTransaction();
        try {
            $uom = AlternateUOM::find($id);
            if ($uom) {
                $result = $uom->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'],
                        'referenced_tables' => $result['referenced_tables'] ?? []
                    ], 400);
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
            }
            return response()->json(['success' => false, 'message' => 'UOM not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    

    public function deleteApprovedCustomer($id)
    {
        DB::beginTransaction();
        try {
            $customer = CustomerItem::find($id);
            if ($customer) {
                $result = $customer->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $result['message']], 400);
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
            }
            return response()->json(['success' => false, 'message' => 'Approved customer not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteApprovedVendor($id)
    {
        DB::beginTransaction();
        try {
            $vendor = VendorItem::find($id);
            if ($vendor) {
                $result = $vendor->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $result['message']], 400);
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
            }
            return response()->json(['success' => false, 'message' => 'Approved vendor not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteAttribute($id)
    {
        DB::beginTransaction();
        try {
            $attribute = ItemAttribute::find($id);
            if ($attribute) {
                $result = $attribute->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $result['message']], 400);
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
            }
            return response()->json(['success' => false, 'message' => 'Attribute not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteAlternateItem($id)
    {
        DB::beginTransaction();
        try {
            $alternateItem = AlternateItem::find($id);
            if ($alternateItem) {
                $result = $alternateItem->deleteWithReferences();
                if (!$result['status']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $result['message']], 400);
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
            }
            return response()->json(['success' => false, 'message' => 'Alternate item not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $item = Item::findOrFail($id);
            $referenceTables = [
                'erp_item_attributes' => ['item_id'],
                'erp_item_specifications' => ['item_id'],
                'erp_item_subtypes' => ['item_id'],
                'erp_customer_items' => ['item_id'],
                'erp_vendor_items' => ['item_id'],
                'erp_alternate_items' => ['item_id'],
                'erp_alternate_uoms' => ['item_id'],
            ];
            $result = $item->deleteWithReferences($referenceTables);
            if (!$result['status']) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getItem(Request $request)
    {
        $searchTerm = $request->input('term', ''); 
        $items = Item::withDefaultGroupCompanyOrg() 
            ->where('item_name', 'like', "%{$searchTerm}%")
            ->where('status', ConstantHelper::ACTIVE)
            ->limit(10)
            ->get(['id', 'item_name', 'item_code']);
        if ($items->isEmpty()) {
            $items = Item::withDefaultGroupCompanyOrg()
                ->where('status', ConstantHelper::ACTIVE)
                ->limit(10)
                ->get(['id', 'item_name', 'item_code']);
        }
        $formattedItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'label' => $item->item_name,
                'value' => $item->item_name,
                'code' => $item->item_code,
            ];
        });
    
        return response()->json($formattedItems);
    }
    
    public function getUOM(Request $request)
    {
      
        $selectedUOMIds = $request->input('selectedUOMIds');
        $selectedUOMTypes = $request->input('selectedUOMTypes');
        return response()->json([
            'selectedUOMIds' => $selectedUOMIds,
            'selectedUOMTypes' => $selectedUOMTypes,
            'message' => 'UOM types received successfully',
        ]);
    }

    # Get item rate
    public function getItemCost(Request $request)
    {
        $itemId = $request->item_id;
        $attributes = $request->attr;
        $uomId = $request->uom_id;
        $currencyId = $request->currency_id;
        $transactionDate = $request->transaction_date ?? date('Y-m-d');
        $item_qty = $request->item_qty ?? 0;
        $vendorId = $request->vendor_id;
        $a = ItemHelper::getItemCostPrice($itemId, $attributes, $uomId, $currencyId, $transactionDate, $vendorId,$item_qty);
        return response()->json(['data' => ['cost' => $a], 'message' => 'get item cost', 'status' => 200]);
    }
}
