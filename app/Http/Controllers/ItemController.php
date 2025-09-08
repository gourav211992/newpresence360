<?php

namespace App\Http\Controllers;
use App\Exceptions\ApiGenericException;
use App\Models\ItemSubType;
use Yajra\DataTables\DataTables;
use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\SubType;
use App\Models\Hsn;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
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
use App\Models\FixedAssetSetup;
use App\Models\OrganizationGroup;
use App\Traits\DataTableExportable;
use Auth;
use stdClass;
use Exception;


class ItemController extends Controller
{
    use DataTableExportable;
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
            $query = Item::with(['uom', 'hsn','subCategory', 'subTypes','auth_user','group'])
                ->orderBy('id', 'desc');
            $search = request('search_value');


            if ($status = request(key: 'status')) {
                $query->where('status', $status);
            }

            if ($hsnId = request(key: 'hsn_id')) {
                $query->where('hsn_id', $hsnId);
            }

           if ($subtypeId = request('sub_type_id')) {
                if ($subtypeId === 'traded_item') {
                    $query->where('is_traded_item', 1);
                } elseif ($subtypeId === 'asset') {
                    $query->where('is_asset', 1);
                }elseif ($subtypeId === 'scrap') {
                    $query->where('is_scrap', 1);
                }  else {
                    $query->whereHas('subTypes', function ($query) use ($subtypeId) {
                        $query->whereHas('subType', function ($q) use ($subtypeId) {
                            $q->where('id', $subtypeId);
                        });
                    });
                }
            }
            if ($categoryId = request('subcategory_id')) {
                $query->where('subcategory_id', $categoryId);
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
                    // Asset
                    if ($row->is_asset == '1') {
                        $subTypes .= ($subTypes ? ', ' : '') . 'Asset';
                    }

                    // Traded Item
                    if ($row->is_traded_item == '1') {
                        $subTypes .= ($subTypes ? ', ' : '') . 'Traded Item';
                    }
                     // Scrap
                    if ($row->is_scrap == '1') {
                        $subTypes .= ($subTypes ? ', ' : '') . 'Scrap';
                    }

                    if ($row->subTypes->isEmpty() && $row->is_asset != '1' && $row->is_traded_item != '1'  && $row->is_scrap != '1') {
                         $subTypes = 'No Subtypes';
                     }

                     return $subTypes;
                })
                ->editColumn('uom', function ($item) {
                    return $item->uom ? $item->uom->name : 'N/A';
                })
                ->addColumn('subCategoryName', function($row) {
                    return  $row->subCategory?->name;
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
                $editRoute = route('item.edit', ['id' => $row->id]);

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
           ->filter(function ($q) use ($search) {
                if ($search) {
                    $q->where(function ($q) use ($search) {
                        $q->where('item_code', 'LIKE', "%{$search}%")
                            ->orWhere('item_name', 'LIKE', "%{$search}%")
                            ->orWhere('type', 'LIKE', "%{$search}%")
                            ->orWhereHas('uom', fn($subQ) => $subQ->where('name', 'LIKE', "%{$search}%"))
                            ->orWhereHas('hsn', fn($subQ) => $subQ->where('code', 'LIKE', "%{$search}%"))
                            ->orWhereHas('subCategory', fn($subQ) => $subQ->where('name', 'LIKE', "%{$search}%"))
                            ->orWhereHas('subTypes', function ($subQ) use ($search) {
                                $subQ->whereHas('subType', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
                            });

                        $searchLower = strtolower($search);
                        if (str_contains($searchLower, 'traded')) {
                            $q->orWhere('is_traded_item', 1);
                        }
                        if (str_contains($searchLower, 'asset')) {
                            $q->orWhere('is_asset', 1);
                        }
                    });
                }
            })
            ->rawColumns(['status'])
            ->make(true);
        }
        $subtypes = SubType::where('status', 'active')->get();
        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)
            ->get();

        $categories = Category::where('type', 'Product')
            ->doesntHave('subCategories')
            ->where('status', ConstantHelper::ACTIVE)
            ->get();

        $types = ConstantHelper::ITEM_TYPES;

        return view('procurement.item.index', compact('hsns', 'categories', 'types','subtypes'));
    }

    public function export(Request $request)
    {
        $search = $request->search_value ?? '';
        $query = Item::with([
            'uom', 'hsn', 'category', 'subCategory', 'auth_user', 'group', 'company', 'organization',
            'itemAttributes.attributeGroup', 'specifications.group', 'alternateUOMs.uom',
            'costCurrency', 'sellCurrency'
        ]);
        // Apply existing filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('hsn_id')) {
            $query->where('hsn_id', $request->input('hsn_id'));
        }
        if ($subtypeId = request('sub_type_id')) {
            if ($subtypeId === 'traded_item') {
                $query->where('is_traded_item', 1);
            } elseif ($subtypeId === 'asset') {
                $query->where('is_asset', 1);
            } else {
                $query->whereHas('subTypes', function ($query) use ($subtypeId) {
                    $query->whereHas('subType', function ($q) use ($subtypeId) {
                        $q->where('id', $subtypeId);
                    });
                });
            }
        }
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'LIKE', "%{$search}%")
                    ->orWhere('item_name', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%")
                    ->orWhereHas('uom', function($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('hsn', function($subQ) use ($search) {
                        $subQ->where('code', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('subCategory', function($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('subTypes', function($subQ) use ($search) {
                        $subQ->whereHas('subType', function($subTypeQ) use ($search) {
                            $subTypeQ->where('name', 'LIKE', "%{$search}%");
                        });
                    });
                $searchLower = strtolower($search);

                if (strpos($searchLower, 'traded item') !== false && strpos($searchLower, 'asset') !== false) {
                    $q->orWhere(function ($inner) {
                        $inner->where('is_traded_item', 1)
                            ->orWhere('is_asset', 1);
                    });
                } elseif (strpos($searchLower, 'traded item') !== false) {
                    $q->orWhere('is_traded_item', 1);
                } elseif (strpos($searchLower, 'asset') !== false) {
                    $q->orWhere('is_asset', 1);
                }
                elseif (strpos($searchLower, 'scrap') !== false) {
                    $q->orWhere('is_scrap', 1);
                }
            });
        }

        $exportType = $request->input('export_type', 'excel');
        $title = 'Items Export';

        return $this->exportDataTable($request, $query, new Item(), $exportType, $title);
    }


    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $urlSegmentAlias = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($urlSegmentAlias, '', $user);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $organization = Organization::where('id', $user->organization_id)->first();
        $currencies = Currency::where('status', operator: ConstantHelper::ACTIVE)->get();
        $subTypes = SubType::where('status', ConstantHelper::ACTIVE)->get();
        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)->get();
        $units = Unit::where('status', ConstantHelper::ACTIVE)->get();
        $organizations = Organization::where('status', ConstantHelper::ACTIVE)->get();
        $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $customers = Customer::where('status', ConstantHelper::ACTIVE)->get();
        $attributeGroups = AttributeGroup::where('status', ConstantHelper::ACTIVE)->get();
        $allItems = Item::where('status', ConstantHelper::ACTIVE)->get();
        $types = ConstantHelper::ITEM_TYPES;
        $storageTypes = ConstantHelper::STORAGE_TYPES;
        $status = ConstantHelper::STATUS;
        $service = ConstantHelper::IS_SERVICE;
        $options = ConstantHelper::STOP_OPTIONS;
        $specificationGroups = ProductSpecification::where('status', ConstantHelper::ACTIVE)->get();
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);
        $fixedAssetCategories = FixedAssetSetup::with('assetCategory')->where('status', ConstantHelper::ACTIVE)->select('id', 'asset_category_id')->get();
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

        if (count($services['services']) == 0) {
            return redirect() -> route('/');
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
            'fixedAssetCategories'=>$fixedAssetCategories,
        ]);
    }

    public function generateItemCode(Request $request)
    {
        $itemName = $request->input('item_name');
        $itemId = $request->input('item_id');
        $subType = $request->input('sub_type');
        $subCategoryInitials = $request->input('cat_initials');
        $itemInitials = $request->input('item_initials');
        $prefix = $request->input('prefix', '');
        $baseCode =  $prefix .$subType . $subCategoryInitials . $itemInitials;

        $authUser = Helper::getAuthenticatedUser();
        $organizationId = $authUser->organization_id;
        if ($itemId) {
            $existingItem = Item::find($itemId);
            if ($existingItem) {
                $existingItemCode = $existingItem->item_code;
                $currentBaseCode = substr($existingItemCode, 0, strlen($baseCode));
                if ($currentBaseCode === $baseCode) {
                    return response()->json(['item_code' => $existingItemCode]);
                }
            }
        }
        $lastSimilarItem = Item::where('item_code', 'like', "{$baseCode}%")
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

        $validatedData['is_serial_no']     = $request->input('is_serial_no') === '1' ? 1 : 0;
        $validatedData['is_batch_no']      = $request->input('is_batch_no') === '1' ? 1 : 0;
        $validatedData['is_expiry']        = $request->input('is_expiry') === '1' ? 1 : 0;
        $validatedData['is_inspection']    = $request->input('is_inspection') === '1' ? 1 : 0;
        $validatedData['is_traded_item']   = $request->input('is_traded_item') === '1' ? 1 : 0;
        $validatedData['is_asset']         = $request->input('is_asset') === '1' ? 1 : 0;
        $validatedData['is_scrap']         = $request->input('is_scrap') === '1' ? 1 : 0;

        if ($validatedData['uom_id'] == $validatedData['storage_uom_id']) {
            $validatedData['storage_uom_conversion'] = 1;
        }
        $validatedData['created_by'] = $user->auth_user_id;

        $orgGroup = OrganizationGroup::find($organization->group_id);
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);
        if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId, $user);
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
            $validatedData['company_id'] =$organization->company_id;
            $validatedData['organization_id'] = null;
        }

        $item = Item::create($validatedData);
        $item ->updated_at = null;
        if ($request->document_status === ConstantHelper::SUBMITTED) {
            $bookId = $item->book_id;
            $docId = $item->id;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $item->approval_level ?? 1;
            $revisionNumber = $item->revision_number ?? 0;
            $actionType = 'submit';
            $modelName = get_class($item);
            $totalValue = 0;

            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
            $document_status = $approveDocument['approvalStatus'];
            $item->document_status = $document_status;
            $submittedStatus = $request->input('status') ?? ConstantHelper::ACTIVE;
            if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                  if ($revisionNumber == 0) {
                        $item->status = ConstantHelper::ACTIVE;
                  }
            } else {
                $item->status = $document_status;
            }

        } else {
            $document_status = $request->document_status ?? ConstantHelper::DRAFT;
            $item->document_status = $document_status;
            $item->status = $document_status;
        }

         $item->save();

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
                        'alt_item_id' => $alternateItemData['alt_item_id'],
                        'item_code' => $alternateItemData['item_code'],
                        'item_name' => $alternateItemData['item_name'],
                    ]);

                    $altItem = Item::find($alternateItemData['alt_item_id']);
                    if ($altItem) {
                        $altItem->alternateItems()->create([
                            'alt_item_id' => $item->id,
                            'item_code' => $item->item_code,
                            'item_name' => $item->name ?? $item->item_name,
                        ]);
                    }
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
        } catch (Exception $e) {
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
        $urlSegmentAlias = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($urlSegmentAlias);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
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
            $deleteQuery = UploadItemMaster::where('user_id', $user->auth_user_id);
            $deleteQuery->delete();

            $import = new ItemImport($this->itemImportExportService, $user);
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
                } catch (Exception $e) {
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
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import items: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportSuccessfulItems()
    {
        $user = Helper::getAuthenticatedUser();
        $uploadItems = UploadItemMaster::where('status', 'Success')->where('user_id', $user->id)->get();
        $items = Item::with(['category', 'subTypes', 'subcategory', 'hsn', 'uom', 'itemAttributes', 'specifications', 'alternateUOMs'])->whereIn('item_code', $uploadItems->pluck('item_code'))->get();
        return Excel::download(new ItemsExport($items, $this->itemImportExportService), "successful-items.xlsx");
    }


    public function exportFailedItems()
    {
        $user = Helper::getAuthenticatedUser();
        $failedItems = UploadItemMaster::where('status', 'Failed')->where('user_id', $user->id)->get();
        return Excel::download(new FailedItemsExport($failedItems), "failed-items.xlsx");
    }

    public function edit(Request $request,$id)
    {
        $user = Helper::getAuthenticatedUser();
        $urlSegmentAlias = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($urlSegmentAlias, '', $user);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        if ($request->has('revisionNumber')) {
            $item = ItemHistory::with(['subTypes.subType'])
                ->where('source_id', $id)
                ->where('revision_number', $request->revisionNumber)
                ->firstOrFail();
            $ogItem = Item::with(['subTypes.subType'])
            ->findOrFail($id);
        } else {
            $item = Item::with(['subTypes.subType'])
                ->findOrFail($id);
            $ogItem = $item;
        }
        $organization = Organization::where('id', $user->organization_id)->first();
        $currencies = Currency::where('status', operator: ConstantHelper::ACTIVE)->get();
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

        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)->get();
        $units = Unit::where('status', ConstantHelper::ACTIVE)->get();
        $categories = Category::where('status', ConstantHelper::ACTIVE)->whereNull('parent_id')  ->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $customers = Customer::where('status', ConstantHelper::ACTIVE)->get();
        $types = ConstantHelper::ITEM_TYPES;
        $storageTypes = ConstantHelper::STORAGE_TYPES;
        $status = ConstantHelper::STATUS;
        $options = ConstantHelper::STOP_OPTIONS;
        $service = ConstantHelper::IS_SERVICE;
        $organizations = Organization::where('status', ConstantHelper::ACTIVE)->get();
        $subTypes = SubType::where('status', ConstantHelper::ACTIVE)->get();
        $attributeGroups = AttributeGroup::with('attributes')->get();
        $allItems = Item::where('status', ConstantHelper::ACTIVE)->get();
        $specificationGroups = ProductSpecification::where('status', ConstantHelper::ACTIVE)->get();
        $fixedAssetCategories = FixedAssetSetup::with('assetCategory')->where('status', ConstantHelper::ACTIVE)->select('id', 'asset_category_id')->get();
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);
        $bomCheckResult = ItemHelper::checkBomForItem($item->id);
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
        $revision_number = $item->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($item->book_id,$item->document_status , $item->id, 1, $item->approval_level, $item -> created_by ?? 0, $userType['type'], $revision_number);
        $revNo = $item->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $item->revision_number;
        }
        $docValue =1;
        $approvalHistory = Helper::getApprovalHistory($ogItem->book_id, $ogItem->id, $revNo, $docValue, $ogItem -> created_by);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$item->document_status] ?? '';
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
            'isBomExists'=>$isBomExists,
            'fixedAssetCategories'=>$fixedAssetCategories,
            'revision_number'=>$revision_number,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
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

        $validatedData['is_serial_no']     = $request->input('is_serial_no') === '1' ? 1 : 0;
        $validatedData['is_batch_no']      = $request->input('is_batch_no') === '1' ? 1 : 0;
        $validatedData['is_expiry']        = $request->input('is_expiry') === '1' ? 1 : 0;
        $validatedData['is_inspection']    = $request->input('is_inspection') === '1' ? 1 : 0;
        $validatedData['is_traded_item']   = $request->input('is_traded_item') === '1' ? 1 : 0;
        $validatedData['is_asset']         = $request->input('is_asset') === '1' ? 1 : 0;
        $validatedData['is_scrap']         = $request->input('is_scrap') === '1' ? 1 : 0;

        if ($validatedData['uom_id'] == $validatedData['storage_uom_id']) {
            $validatedData['storage_uom_conversion'] = 1;
        }
        $validatedData['created_by'] = $item->created_by ?? $user->auth_user_id;
        $parentUrl = ConstantHelper::ITEM_SERVICE_ALIAS;

        $orgGroup = OrganizationGroup::find($organization->group_id);

        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);
        if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId, $user);
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

        $currentStatus = $item->document_status;
        $actionType = $request->action_type ?? 'submit';
        $amendRemarks = $request->amend_remarks ?? null;

        if (($item->document_status == ConstantHelper::APPROVED || $item->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED)
            && $actionType == 'amendment') {

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'Item', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ItemSubType', 'relation_column' => 'item_id'],
                ['model_type' => 'detail', 'model_name' => 'AlternateUOM', 'relation_column' => 'item_id'],
                ['model_type' => 'detail', 'model_name' => 'AlternateItem', 'relation_column' => 'item_id'],
                ['model_type' => 'detail', 'model_name' => 'ItemAttribute', 'relation_column' => 'item_id'],
                ['model_type' => 'detail', 'model_name' => 'ItemSpecification', 'relation_column' => 'item_id'],
                ['model_type' => 'detail', 'model_name' => 'VendorItem', 'relation_column' => 'item_id'],
                ['model_type' => 'detail', 'model_name' => 'CustomerItem', 'relation_column' => 'item_id'],

            ];

            Helper::documentAmendment($revisionData, $item->id);
        }
        $item->fill($validatedData);
        $item->save();
        // Document Approval Logic
        if ($request->input('current_status') === ConstantHelper::SUBMITTED ) {
            $bookId = $item->book_id;
            $docId = $item->id;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $amendAttachments = $request->file('amend_attachments');
            $currentLevel = $item->approval_level ?? 1;
            $modelName = get_class($item);
            $submittedStatus = $request->input('status');

            if (($currentStatus == ConstantHelper::APPROVED || $currentStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
                $revisionNumber = $item->revision_number + 1;
                $totalValue = 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
                $item->revision_number = $revisionNumber;
                $item->approval_level = 1;
                $item->revision_date = now();

                $statusAfterApproval = $approveDocument['approvalStatus'] ?? $item->document_status;

                $item->document_status = $statusAfterApproval;

                if (in_array($statusAfterApproval, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                    if ($submittedStatus === ConstantHelper::INACTIVE) {
                        $item->status = ConstantHelper::INACTIVE;
                    } else {
                        $item->status = ConstantHelper::ACTIVE;
                    }
                } else {
                    $item->status = $statusAfterApproval;
                }
            } else {
                $revisionNumber = $item->revision_number ?? 0;
                $totalValue = 0;
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                $document_status = $approveDocument['approvalStatus'];
                $item->document_status = $document_status;
               if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                    if ($revisionNumber == 0) {
                        $item->status = ConstantHelper::ACTIVE;
                    }
                } else {
                    $item->status = $document_status;
                }
            }

        } else {
            $document_status = $request->current_status ?? ConstantHelper::DRAFT;
            $item->document_status = $document_status;
            $item->status = $document_status;
        }

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
            $newAlternateItemIds = [];

            foreach ($request->input('alternateItems') as $altItemData) {
                if (
                    isset($altItemData['item_code']) && !empty($altItemData['item_code']) &&
                    isset($altItemData['item_name']) && !empty($altItemData['item_name'])
                ) {

                    if (isset($altItemData['id']) && in_array($altItemData['id'], $existingAlternateItems)) {
                        $item->alternateItems()->where('id', $altItemData['id'])->update([
                            'alt_item_id' => $altItemData['alt_item_id'],
                            'item_code' => $altItemData['item_code'],
                            'item_name' => $altItemData['item_name'],
                        ]);
                        $newAlternateItemIds[] = $altItemData['id'];
                    } else {
                        $newAlt = $item->alternateItems()->create([
                            'alt_item_id' => $altItemData['alt_item_id'],
                            'item_code' => $altItemData['item_code'],
                            'item_name' => $altItemData['item_name'],
                        ]);
                        $newAlternateItemIds[] = $newAlt->id;
                    }

                    // --- Reverse mapping: B → A ---
                    $altItem = Item::find($altItemData['alt_item_id']);
                    if ($altItem) {
                        $reverse = $altItem->alternateItems()->where('alt_item_id', $item->id)->first();

                        if ($reverse) {
                            $reverse->update([
                                'item_code' => $item->item_code,
                                'item_name' => $item->item_name ?? $item->name,
                            ]);
                        } else {
                            $altItem->alternateItems()->create([
                                'alt_item_id' => $item->id,
                                'item_code' => $item->item_code,
                                'item_name' => $item->item_name ?? $item->name,
                            ]);
                        }
                    }
                }
            }

            $toBeDeleted = $item->alternateItems()->whereNotIn('id', $newAlternateItemIds)->get();
            foreach ($toBeDeleted as $alt) {
                $altItem = Item::find($alt->alt_item_id);
                if ($altItem) {
                    $altItem->alternateItems()->where('alt_item_id', $item->id)->delete();
                }
                $alt->delete();
            }

        } else {
            foreach ($item->alternateItems as $alt) {
                $altItem = Item::find($alt->alt_item_id);
                if ($altItem) {
                    $altItem->alternateItems()->where('alt_item_id', $item->id)->delete();
                }
                $alt->delete();
            }
        }

        if ($request->has('item_specifications')) {
            $specifications = $request->input('item_specifications');
            if (!is_array($specifications)) {
                $specifications = [];
            } elseif (isset($specifications['specification_name'])) {
                $specifications = [$specifications];
            }

            $existingIds = [];

            foreach ($specifications as $specificationData) {
                if (!is_array($specificationData)) {
                    continue;
                }

                if (!empty($specificationData['id'])) {
                    $spec = $item->specifications()->where('id', $specificationData['id'])->first();

                    if ($spec) {
                        $spec->update([
                            'group_id' => $specificationData['group_id'] ?? null,
                            'specification_id' => $specificationData['specification_id'] ?? null,
                            'specification_name' => $specificationData['specification_name'] ?? null,
                            'value' => $specificationData['value'] ?? null,
                        ]);
                        $existingIds[] = $spec->id;
                        continue;
                    }
                }

                if (!empty($specificationData['specification_name'])) {
                    $newSpec = $item->specifications()->create([
                        'group_id' => $specificationData['group_id'] ?? null,
                        'specification_id' => $specificationData['specification_id'] ?? null,
                        'specification_name' => $specificationData['specification_name'],
                        'value' => $specificationData['value'] ?? null,
                    ]);
                    $existingIds[] = $newSpec->id;
                }
            }
            $item->specifications()->whereNotIn('id', $existingIds)->delete();
        } else {
            $item->specifications()->delete();
        }

        DB::commit();
        return response()->json(['message' => 'Record updated successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function revoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $item = Item::find($request -> id);
            if (isset($item)) {
                $revoke = Helper::approveDocument($item -> book_id, $item -> id, $item -> revision_number, '', [], 0, ConstantHelper::REVOKE, $item -> cost_price, get_class($item));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response() -> json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $item -> document_status = $revoke['approvalStatus'];
                    $item->status = $revoke['approvalStatus'];
                    $item -> save();
                    DB::commit();
                    return response() -> json([
                        'status' => 'success',
                        'message' => 'Revoked succesfully',
                    ]);
                }
            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch(Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex -> getMessage());
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAlternateItem($id)
        {
            DB::beginTransaction();

            try {
                $alternateItem = AlternateItem::find($id);

                if (!$alternateItem) {
                    return response()->json(['success' => false, 'message' => 'Alternate item not found'], 404);
                }

                $itemId = $alternateItem->item_id;
                $altItemId = $alternateItem->alt_item_id;

                $reverseItem = AlternateItem::where('item_id', $altItemId)
                    ->where('alt_item_id', $itemId)
                    ->first();

                if ($reverseItem) {
                    $result = $reverseItem->deleteWithReferences();

                    if (!$result['status']) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => $result['message']], 400);
                    }
                }

                $mainResult = $alternateItem->deleteWithReferences();
                if (!$mainResult['status']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $mainResult['message']], 400);
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Record deleted successfully']);

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

        } catch (Exception $e) {
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
        $excludeId = $request->input('exclude_id');

        $query = Item::where('status', ConstantHelper::ACTIVE);

        if ($searchTerm) {
            $query->where('item_name', 'like', "%{$searchTerm}%");
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $items = $query->limit(10)->get(['id', 'item_name', 'item_code']);

        if ($items->isEmpty()) {
            $query = Item::where('status', ConstantHelper::ACTIVE);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $items = $query->limit(10)->get(['id', 'item_name', 'item_code']);
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

    public function getAssetDataForCategory($categoryId)
    {
        $data = FixedAssetSetup::where('status', 'ACTIVE')
            ->where('asset_category_id', $categoryId)
            ->select('expected_life_years', 'maintenance_schedule')
            ->first();

        if ($data) {
            return response()->json([
                'expected_life_years' => $data->expected_life_years,
                'maintenance_schedule' => $data->maintenance_schedule
            ]);
        } else {
            return response()->json([
                'expected_life_years' => '',
                'maintenance_schedule' => ''
            ]);
        }
    }
}
