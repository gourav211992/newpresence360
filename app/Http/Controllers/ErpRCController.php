<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\EInvoiceHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\SaleModuleHelper;
use App\Http\Requests\ErpRateContractRequest;
use App\Models\AuthUser;
use App\Helpers\DynamicFieldHelper;
use App\Models\Customer;
use App\Models\ErpRcDynamicField;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ErpInvoiceItem;
use App\Models\ErpRateContract;
use App\Models\ErpRateContractHistory;
use App\Models\ErpRateContractItem;
use App\Models\ErpRateContractItemAttribute;
use App\Models\ErpRcOrganizationMapping;
use App\Models\EwayBillMaster;
use App\Models\Item;
use App\Models\Organization;
use App\Models\PaymentTerm;
use App\Models\TermsAndCondition;
use App\Models\Unit;
use App\Models\Vendor;
use DB;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ErpRCController extends Controller
{
    //
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $orderType = "Rate Contract";
        $redirectUrl = route('rate.contract.index');
        $createRoute = route('rate.contract.create');

        $typeName = "Rate Contract";

        if ($request->ajax()) {
            $returns = ErpRateContract::withDefaultGroupCompanyOrg()
                ->withDraftListingLogic()
                ->orderByDesc('id');
            return DataTables::of($returns)  
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) use ($orderType) {
                    $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                    $displayStatus = '';
                    $row->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED ? $displayStatus = 'Approved' : $displayStatus = $row->display_status;
                    $editRoute = route('rate.contract.edit', ['id' => $row->id]);
                    return "
                    <div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                        <div class='dropdown' style='display:inline;'>
                            <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                <i data-feather='more-vertical'></i>
                            </button>
                            <div class='dropdown-menu dropdown-menu-end'>
                                <a class='dropdown-item' href='" . $editRoute . "'>
                                    <i data-feather='edit-3' class='me-50'></i>
                                    <span>View/ Edit Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                ";
                })
                ->addColumn('document_type', function ($row) {
                    return 'Rate Contract';
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book_code ? $row->book_code : 'N/A';
                })
                ->addColumn('curr_name', function ($row) {
                    return $row->currency_code ?? $row->currency ? ($row->currency?->short_name ?? $row->currency?->name) : 'N/A';

                })
                ->editColumn('document_date', function ($row) {
                    return $row->getFormattedDate('document_date') ?? 'N/A';
                })
                ->editColumn('revision_number', function ($row) {
                    return strval($row->revision_number);
                })
                ->addColumn('vendor_name', function ($row) {
                    return $row->vendor_code?? $row->vendor?->company_name ?? ($row->customer?->company_name ?? 'NA');
                })
                ->addColumn('items_count', function ($row) {
                    return $row->items->count();
                })
                ->addColumn('app_org',function($row) {
                    $orgs = json_decode($row->applicable_organizations);
                    $orgNames = [];
                    if (isset($orgs)) {
                        foreach ($orgs as $org) {
                            $organization = Organization::find($org);
                            if (isset($organization)) {
                                array_push($orgNames, $organization->name);
                            }
                        }
                    }
                    return implode(', ', $orgNames);
                })
                ->addColumn('start_date', function ($row) {
                    return $row->getFormattedDate('start_date') ?? '';
                })
                ->addColumn('end_date', function ($row) {
                    return $row->getFormattedDate('end_date') ?? '';
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }

        return view('rate-contract.index', [
            'typeName' => $typeName,
            'redirect_url' => $redirectUrl,
            'create_route' => $createRoute
        ]);
    }


    public function create(Request $request)
    {
        $parentURL = request()->segments()[0];
        $redirectUrl = route('rate.contract.index');
        $user = Helper::getAuthenticatedUser();
        $users = AuthUser::where('organization_id', $user -> organization_id) -> where('status', ConstantHelper::ACTIVE) -> get();

        $type = ConstantHelper::RC_SERVICE_ALIAS;
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL,"",$user);
        $termsAndConditions = TermsAndCondition::where('status',ConstantHelper::ACTIVE)->get();
        $firstService = $servicesBooks['services'][0];
        $bookType = $type;
        $typeName = "Rate Contract";
        // $stores = ErpStore::withDefaultGroupCompanyOrg()->where('store_location_type', ConstantHelper::STOCKK)->get();
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        // $organization = Organization::where('id', $user->organization_id)->first();
        $userOrgs = $user->organizations->pluck('id')->toArray();
        array_push($userOrgs,$user->organization_id);
        $organizations = Organization::whereIn('id',$userOrgs)->where('status', ConstantHelper::ACTIVE)->get();
        $vendors = Vendor::where('organization_id', $user->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $books = Helper::getBookSeries($bookType)->get();
        $countries = Country::select('id AS value', 'name AS label')->where('status', ConstantHelper::ACTIVE)->get();
        $transportationModes = EwayBillMaster::where('status', 'active')
            ->where('type', '=', 'transportation-mode')
            ->orderBy('id', 'ASC')
            ->get();
        $data = [
            'user' => $user,
            'users' => $users,
            'stores' => $stores,
            'vendors' => $vendors,
            'all_orgs' => $organizations,
            // 'departments' => $departments,
            'services' => $servicesBooks['services'],
            'selectedService' => $firstService?->id ?? null,
            'series' => $books,
            'countries' => $countries,
            'type' => $type,
            'typeName' => $typeName,
            'termsAndConditions' => $termsAndConditions,
            'redirect_url' => $redirectUrl,
            'transportationModes' => $transportationModes,
            'einvoice' => null

        ];
        return view('rate-contract.create_edit', $data);
    }
    public function edit(Request $request, string $id)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $users = AuthUser::where('organization_id', $user -> organization_id) -> where('status', ConstantHelper::ACTIVE) -> get();
            if (isset($request->revisionNumber)) {
                $order = ErpRateContractHistory::with([ 'media_files'])->with('items', function ($query) {
                    $query->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom']);
                        }
                    ]);
                })
                    ->where('revision_number',$request->revisionNumber)
                    ->where('source_id', $id)->first();
                $ogReturn = ErpRateContract::find($id);
            } else {
                $order = ErpRateContract::with([ 'media_files'])->with('items', function ($query) {
                    $query->with(['item_attributes'])->with([
                        'item' => function ($itemQuery) {
                            $itemQuery->with(['specifications', 'alternateUoms.uom', 'uom','group']);
                        }
                    ]);
                })->find($id);
                $ogReturn = $order;
            }
            $parentURL = request()->segments()[0];
            $termsAndConditions = TermsAndCondition::where('status',ConstantHelper::ACTIVE)->get();
            $redirectUrl = route('rate.contract.index');
            if (isset($order)) {
                $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL, $order?->book?->service?->alias,$user);
                $firstService = $servicesBooks['services'][0];
                foreach ($order->items as &$siItem) {
                        $siItem->max_attribute = 999999;
                        $siItem->is_editable = true;
                    if($order->document_status != ConstantHelper::DRAFT){
                        $siItem->is_editable = false;
                    }
                }
            }
            $revision_number = $order->revision_number??null;
            $userType = Helper::userCheck();
            $totalValue = 0;
            $userOrgs = $user->organizations->pluck('id')->toArray();
            array_push($userOrgs,$user->organization_id);
            $organizations = Organization::whereIn('id',$userOrgs)->where('status', ConstantHelper::ACTIVE)->get();
        
            $buttons = Helper::actionButtonDisplay($order->book_id, $order->document_status, $order->id, $totalValue, $order->approval_level, $order->created_by ?? 0, $userType['type'], $revision_number);
            $type = ConstantHelper::RC_SERVICE_ALIAS;
            $books = Helper::getBookSeries($type)->get();
            $countries = Country::select('id AS value', 'name AS label')->where('status', ConstantHelper::ACTIVE)->get();
            $revNo = $order->revision_number;
            if ($request->has('revisionNumber')) {
                $revNo = intval($request->revisionNumber);
            } else {
                $revNo = $order->revision_number;
            }
            $docValue = $order->total_amount ?? 0;
            $approvalHistory = Helper::getApprovalHistory($order->book_id, $ogReturn->id, $revNo, $docValue);
            $order->document_status == 'approval_not_required' ? $display_status = 'Apporved' : $display_status = $order->display_status;
            $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$order->document_status] ?? '';
            $typeName = "Rate Contract";
            $bookType = $type;
            $stores = InventoryHelper::getAccessibleLocations();
            $organization = Organization::where('id', $user->organization_id)->first();
            // $departments = Department::where('organization_id', $organization->id)
            //     ->where('status', ConstantHelper::ACTIVE)
            //     ->get();
            $payment_term = PaymentTerm::find($order->payment_term_id);
            $dynamicFieldsUI = $order -> dynamicfieldsUi();
            $data = [
                'user' => $user,
                'users' => $users,
                'payment_term' => $payment_term,
                'services' => $servicesBooks['services'],
                'all_orgs' => $organizations,
                'stores' => $stores,
                // 'departments' => $departments,
                'selectedService' => $firstService?->id ?? null,
                'series' => $books,
                'order' => $order,
                'countries' => $countries,
                'buttons' => $buttons,                
                'termsAndConditions' => $termsAndConditions,
                'dynamicFieldsUi' => $dynamicFieldsUI,
                'approvalHistory' => $approvalHistory,
                'type' => $type,
                'revision_number' => $revision_number,
                'docStatusClass' => $docStatusClass,
                'typeName' => $typeName,
                'display_status' => $display_status,
                'redirect_url' => $redirectUrl,
            ];
            return view('rate-contract.create_edit', $data);

        } catch (Exception $ex) {
            dd($ex);
        }
    }
    public function store(ErpRateContractRequest $request)
    {
        try {
            $party = null;
            if ($request->filled("party_type") && $request->party_type == 'vendor') {
                $party = Vendor::find($request->party_id);
            }
            else{
                $party = Customer::find($request->party_id);
            }
            //Reindex
            $request -> from_item_qty =  array_values($request -> from_item_qty);
            $request -> to_item_qty =  array_values($request -> to_item_qty);
            $request -> item_remarks =  array_values($request -> item_remarks ?? []);
            $request -> uom_id =  array_values($request -> uom_id);
            $request -> item_rate =  array_values($request -> item_rate);
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            //Auth credentials
            $organization = Organization::find($user -> organization_id);
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;
            $itemAttributeIds = [];
            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($organization -> currency -> id, $request -> document_date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message']
                ], 422); 
            }
            if (!$request -> rate_contract_id) {
                $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $request -> document_no;
                $regeneratedDocExist = ErpRateContract::withDefaultGroupCompanyOrg() -> where('book_id',$request->book_id)
                    ->where('document_number',$document_number)->first();
                    //Again check regenerated doc no
                    if (isset($regeneratedDocExist)) {
                        return response()->json([
                            'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                            'error' => "",
                        ], 422);
                    }
            }
            $rateContract = null;
            if ($request -> rate_contract_id) { //Update
                // dd($request->all());
                $rateContract = ErpRateContract::find($request -> rate_contract_id);
                $rateContract -> document_date = $request -> document_date ?? $rateContract -> document_date;
                $rateContract -> start_date = $request -> start_date ?? $rateContract -> start_date;
                $rateContract -> end_date = $request -> end_date ?? $rateContract -> end_date;
                $rateContract ->tnc = $request -> tnc ?? $rateContract -> tnc;
                $rateContract ->tnc_id = $request -> tnc_id ?? $rateContract -> tnc_id;
                $rateContract -> payment_term_id = isset($request -> payment_terms_id) ? $request->payment_terms_id : $rateContract -> payment_term_id;
                if($request->party_type == "customer")
                {
                    $rateContract -> customer_id = $request -> customer_id ?? $rateContract -> customer_id;
                }
                else
                {
                    $rateContract -> vendor_id = $request -> vendor_id ?? $rateContract -> vendor_id;
                }


                // $rateContract -> reference_number = $request -> reference_no;
                //Store and department keys
                // $rateContract -> store_id = $request -> return_location ?? null;
                // $rateContract -> store_code = $fromStore ?-> store_name ?? null;
                // $rateContract -> to_store_id = $request -> store_from_id ?? null;
                // $rateContract -> to_store_code = $toStore ?-> store_name ?? null;
                $rateContract -> remarks = $request -> final_remarks;
                $actionType = $request -> action_type ?? '';
                //Amend backup

                if(($rateContract -> document_status == ConstantHelper::APPROVED || $rateContract -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                {
                    $revisionData = [
                        ['model_type' => 'header', 'model_name' => 'ErpRateContract', 'relation_column' => ''],
                        ['model_type' => 'detail', 'model_name' => 'ErpRateContractItem', 'relation_column' => 'rate_contract_id'],
                        ['model_type' => 'detail', 'model_name' => 'ErpRcOrganizationMapping', 'relation_column' => 'rate_contract_id'],
                        ['model_type' => 'sub_detail', 'model_name' => 'ErpRateContractItemAttribute', 'relation_column' => 'rate_contract_item_id'],
                    ];
                    $a = Helper::documentAmendment($revisionData, $rateContract->id);
                }
                $keys = ['deletedSiItemIds', 'deletedAttachmentIds'];
                $deletedData = [];

                foreach ($keys as $key) {
                    $deletedData[$key] = json_decode($request->input($key, '[]'), true);
                }
                if (count($deletedData['deletedSiItemIds'])) {
                    $rcItems = ErpRateContractItem::whereIn('id',$deletedData['deletedSiItemIds'])->get();
                    # all ted remove item level
                    foreach($rcItems as $rcItem) {
                        # all attr remove
                        $rcItem->item_attributes()->delete();
                        $rcItem->delete();
                    }
                }
            } else { //Create
                $party = null;
                
                if ($request->filled("party_type") && $request->party_type == 'vendor') {
                    $party = Vendor::find($request->vendor_id);
                }
                else{
                    $party = Customer::find($request->customer_id);
                }
                $currency = Currency::find($request->currency_id);
                $rateContract = ErpRateContract::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $request->book_id,
                    'book_code' => $request->book_code,
                    'start_date' => $request -> start_date,
                    'end_date' => $request -> end_date, 
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $request->document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'currency_id' => $request->currency_id,
                    'currency_code' => isset($request -> currency_id) ? $currency->short_name : null,
                    'vendor_id' =>  $request->party_type == 'vendor' ? $party?->id : null,
                    'vendor_code' =>  $request->party_type == 'vendor' ? $party?->company_name : null,
                    'customer_id' =>  $request->party_type == 'customer' ? $party?->id : null,
                    'customer_code' =>  $request->party_type == 'customer' ? $party?->company_name : null,
                    'document_status' => $request->document_status ?? ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'applicable_organizations' => json_encode($request->organization_id),
                    'payment_term_id' => isset($request->payment_terms_id) ? $request->payment_terms_id : null,
                    'tnc' => isset($request->tnc) ? $request->tnc : null,
                    'tnc_id' => isset($request->term_id) ? $request->term_id : null,
                    'remarks' => $request->final_remarks,
                ]);
            }
               
                $rateContract -> save();
                //Seperate array to store each item calculation
                $itemsData = array();
                if ($request -> item_id && count($request -> item_id) > 0) {
                    //Items
                    foreach ($request -> item_id as $itemKey => $itemId) {
                        $item = Item::find($itemId);
                        if (isset($item))
                        {
                            
                            $inventoryUomQty = isset($request -> from_item_qty[$itemKey]) ? $request -> from_item_qty[$itemKey] : 0;
                            $requestUomId = isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null;
                            if($requestUomId != $item->uom_id) {
                                $alUom = $item->alternateUOMs()->where('uom_id',$requestUomId)->first();
                                if($alUom) {
                                    $inventoryUomQty= intval(isset($request -> from_item_qty[$itemKey]) ? $request -> from_item_qty[$itemKey] : 0) * $alUom->conversion_to_inventory;
                                }
                            }
                            $uom = Unit::find($request -> uom_id[$itemKey] ?? null);
                            $rcItem = ErpRateContractItem::find($request ?-> mi_item_id[$itemKey] ?? NULL); 
                            $user = AuthUser::find($request ?-> user_id[$itemKey] ?? NULL);
                            if(isset($request->item_currency_id[$itemKey])) {
                                $currency = Currency::find($request->item_currency_id[$itemKey]);
                            } 
                            array_push($itemsData, [
                                'rate_contract_id' => $rateContract -> id,
                                'item_id' => $item -> id,
                                'mi_item_id' => isset($request -> mi_item_id[$itemKey]) ? $request -> mi_item_id[$itemKey] : null,
                                'item_code' => $item -> item_code,
                                'item_name' => $item -> item_name,
                                'hsn_id' => $item -> hsn_id,
                                'hsn_code' => $item -> hsn ?-> code,
                                'uom_id' => isset($request -> uom_id[$itemKey]) ? $request -> uom_id[$itemKey] : null,
                                'uom_code' => isset($uom) ? $uom -> name : null,
                                'moq' => isset($request->MOQ[$itemKey]) ? $request->MOQ[$itemKey] : 0,
                                'from_qty' => isset($request -> from_item_qty[$itemKey]) ? $request -> from_item_qty[$itemKey] : 0,
                                'to_qty' => isset($request -> to_item_qty[$itemKey]) ? $request -> to_item_qty[$itemKey] : 0,
                                'rate' => isset($request -> item_rate[$itemKey]) ? $request -> item_rate[$itemKey] : 0,
                                'lead' => isset($request -> item_lead[$itemKey]) ? $request -> item_lead[$itemKey] : 0,
                                'currency_id' => isset($request -> item_currency_id[$itemKey]) ? $request -> item_currency_id[$itemKey] : null,
                                'currency_code' => isset($request -> item_currency_id[$itemKey]) ? $currency->short_name : null,
                                'from_date' => isset($request -> effective_from[$itemKey]) ? $request -> effective_from[$itemKey] : null,
                                'to_date' => isset($request -> effective_to[$itemKey]) ? $request -> effective_to[$itemKey] : null,
                                'remarks' => isset($request -> item_remarks[$itemKey]) ? $request -> item_remarks[$itemKey] : null,
                            ]);   
                        }
                    }
                    foreach ($itemsData as $itemDataKey => $itemDataValue) {
                        //Update or create
                        $itemRowData = [
                            'rate_contract_id' => $rateContract -> id,
                            'item_id' => $itemDataValue['item_id'],
                            'item_code' => $itemDataValue['item_code'],
                            'item_name' => $itemDataValue['item_name'],
                            'hsn_id' => $itemDataValue['hsn_id'],
                            'hsn_code' => $itemDataValue['hsn_code'],
                            'uom_id' => $itemDataValue['uom_id'],
                            'uom_code' => $itemDataValue['uom_code'],
                            'from_qty' => $itemDataValue['from_qty'],
                            'to_qty' => $itemDataValue['to_qty'],
                            'moq' => $itemDataValue['moq'],
                            'currency_id' => $itemDataValue['currency_id'],
                            'currency_code' => $itemDataValue['currency_code'],
                            'from_date' => $itemDataValue['from_date'],
                            'to_date' => $itemDataValue['to_date'],
                            'rate' => $itemDataValue['rate'],
                            'lead_time' => $itemDataValue['lead'],
                            'remarks' => $itemDataValue['remarks'],
                        ];
                        
                        if (isset($request -> rc_item_id[$itemDataKey])) {
                            $oldRcItem = ErpRateContractItem::find($request -> rc_item_id[$itemDataKey]);
                            $rcItem = ErpRateContractItem::updateOrCreate(['id' => $request -> rc_item_id[$itemDataKey]], $itemRowData);
                        } else {
                            $rcItem = ErpRateContractItem::create($itemRowData);
                        }
                        //Item Attributes
                        if (isset($request -> item_attributes[$itemDataKey])) {
                            $attributesArray = json_decode($request -> item_attributes[$itemDataKey], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($attributesArray)) {
                                foreach ($attributesArray as $attributeKey => $attribute) {
                                    $attributeVal = "";
                                    $attributeValId = null;
                                    foreach ($attribute['values_data'] as $valData) {
                                        if ($valData['selected']) {
                                            $attributeVal = $valData['value'];
                                            $attributeValId = $valData['id'];
                                            break;
                                        }
                                    }
                                    if(isset($attributeVal) && $attributeValId){

                                        $itemAttribute = ErpRateContractItemAttribute::updateOrCreate(
                                            [
                                                'rate_contract_id' => $rateContract -> id,
                                                'rate_contract_item_id' => $rcItem -> id,
                                                'item_attribute_id' => $attribute['id'],
                                            ],
                                            [
                                                'item_code' => $rcItem -> item_code,
                                                'attribute_name' => $attribute['group_name'],
                                                'attr_name' => $attribute['attribute_group_id'],
                                                'attribute_value' => $attributeVal,
                                                'attr_value' => $attributeValId,
                                                ]
                                            );
                                            array_push($itemAttributeIds, $itemAttribute -> id);
                                    }
                                }
                            } else {
                                return response() -> json([
                                    'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid attributes',
                                    'error' => ''
                                ], 422);
                            }
                        }
                        // rate contract organization mapping
                        foreach($request->organization_id as $orgKey => $orgId) {
                            $rcOrgMapping = ErpRcOrganizationMapping::updateOrCreate(
                                [
                                    'rate_contract_id' => $rateContract->id,
                                    'organization_id' => $orgId,
                                ],
                                [
                                    'rate_contract_id' => $rateContract->id,
                                    'organization_id' => $orgId,
                                ]
                            );
                        }
                        ErpRcOrganizationMapping::where('rate_contract_id', $rateContract->id)
                            ->whereNotIn('organization_id', $request->organization_id)
                            ->delete();
                        $rcOrgMapping->save();
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Please select Items',
                        'error' => "",
                    ], 422);
                }
                ErpRateContractItemAttribute::where([
                    'rate_contract_id' => $rateContract -> id,
                    'rate_contract_item_id' => $rcItem -> id,
                ]) -> whereNotIn('id', $itemAttributeIds) -> delete();
                //Approval check
                if ($request -> rate_contract_id) { //Update condition
                    $bookId = $rateContract->book_id; 
                    $docId = $rateContract->id;
                    $amendRemarks = $request->amend_remarks ?? null;
                    $remarks = $rateContract->remarks;
                    $amendAttachments = $request->file('amend_attachments');
                    $attachments = $request->file('attachment');
                    $currentLevel = $rateContract->approval_level;
                    $modelName = get_class($rateContract);
                    $actionType = $request -> action_type ?? "";
                    $currency = Currency::find($request->currency_id);
                    $rateContract->vendor_id = $request->vendor_id;
                    $rateContract->vendor_code = $party?->company_name;
                    $rateContract->applicable_organizations = isset($request->applicable_organization) ? json_encode($request->applicable_organization) : $rateContract->applicable_organizations;
                    $rateContract->payment_term_id = isset($request->payment_terms_id) ? $request->payment_terms_id : ($rateContract->payment_term_id??null);
                    // $rateContract->currency_id = $request->currency_id;
                    // $rateContract->currency_code = isset($request -> currency_id) ? $currency->short_name : null;
                    if(($rateContract -> document_status == ConstantHelper::APPROVED || $rateContract -> document_status == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment')
                    {
                        //*amendmemnt document log*/
                        $revisionNumber = $rateContract->revision_number + 1;
                        $actionType = 'amendment';
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $amendRemarks, $amendAttachments, $currentLevel, $actionType, 0, $modelName);
                        $rateContract->revision_number = $revisionNumber;
                        $rateContract->approval_level = 1;
                        $rateContract->revision_date = now();
                        $amendAfterStatus = $rateContract->document_status;
                        // $checkAmendment = Helper::checkAfterAmendApprovalRequired($request->book_id);
                        // if(isset($checkAmendment->approval_required) && $checkAmendment->approval_required) {
                        //     $totalValue = $rateContract->grand_total_amount ?? 0;
                        //     $amendAfterStatus = Helper::checkApprovalRequired($request->book_id,$totalValue);
                        // } else {
                        //     $actionType = 'approve';
                        //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        // }
                        // if ($amendAfterStatus == ConstantHelper::SUBMITTED) {
                        //     $actionType = 'submit';
                        //     $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                        // }
                        $rateContract->document_status = $amendAfterStatus;
                        $rateContract->save();

                    } else {
                        if ($request->document_status == ConstantHelper::SUBMITTED) {
                            $revisionNumber = $rateContract->revision_number ?? 0;
                            $actionType = 'submit';
                            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);

                            $totalValue = $rateContract->grand_total_amount ?? 0;
                            $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                            $rateContract->document_status = $document_status;
                        } else {
                            $rateContract->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                        }
                    }
                } else { //Create condition
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $bookId = $rateContract->book_id;
                        $docId = $rateContract->id;
                        $remarks = $rateContract->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $rateContract->approval_level;
                        $revisionNumber = $rateContract->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($rateContract);
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
                    }

                    // if ($request->document_status == 'submitted') {
                    //     $totalValue = $rateContract->total_amount ?? 0;
                    //     $document_status = Helper::checkApprovalRequired($request->book_id,$totalValue);
                    //     $rateContract->document_status = $document_status;
                    // } else {
                    //     $rateContract->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    // }
                    $rateContract -> save();
                }
                $rateContract -> save();
                //Media
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $singleFile) {
                        $mediaFiles = $rateContract->uploadDocuments($singleFile, 'rate_contract', false);
                    }
                }
                // $status = self::maintainStockLedger($rateContract);
                // if (!$status) {     
                //     DB::rollBack();
                //     return response() -> json([
                //         'message' => 'Stock not available'
                //     ], 422);
                // }
                //Dynamic Fields
                $status = DynamicFieldHelper::saveDynamicFields(ErpRcDynamicField::class, $rateContract -> id, $request -> dynamic_field ?? []);
                if ($status && !$status['status'] ) {
                    DB::rollBack();
                    return response() -> json([
                        'message' => $status['message'],
                        'error' => ''
                    ], 422);
                }
                DB::commit();
                $module = ConstantHelper::RC_SERVICE_ALIAS;
                return response() -> json([
                    'message' => "Rate Contract created successfully",
                    'redirect_url' => route('rate.contract.index')
                ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $ex->getMessage() . ' at ' . $ex -> getLine() . ' in ' . $ex -> getFile(),
            ], 500);
        }
    }
    public function amend()
    {
        
    }
    public function revoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $saleDocument = ErpRateContract::find($request->id);
            if (isset($saleDocument)) {
                $revoke = Helper::approveDocument($saleDocument->book_id, $saleDocument->id, $saleDocument->revision_number, $request->remarks??" ", [], 0, ConstantHelper::REVOKE, 0, get_class($saleDocument));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $saleDocument->document_status = $revoke['approvalStatus'];
                    $saleDocument->save();

                }
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Revoked succesfully',
                ]);

            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex->getMessage());
        }
    
    }

    public function checkExistingRateContract(Request $request)
    {
        if (empty($request->start_date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Start date is required.',
            ]);
        }

        $partyId = $request->vendor_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Determine which party column to use based on request type
        $partyColumn = $request->type === 'customer' ? 'customer_id' : 'vendor_id';
        $partyId = $request->vendor_id;

        $rateContract = ErpRateContract::where($partyColumn, $partyId)
            ->where('document_status', ConstantHelper::APPROVED)
            ->where(function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate) {
                // Contract open-ended
                $sub->whereNull('end_date')
                    ->where(function ($cond) use ($startDate) {
                    $cond->whereNull('start_date')
                        ->orWhere('start_date', '<=', $startDate);
                    });
                })
                ->orWhere(function ($sub) use ($startDate, $endDate) {
                // Request open-ended
                $sub->whereNull($endDate ? DB::raw('1 = 0') : 'end_date')
                    ->where('start_date', '<=', $endDate ?? now());
                })
                ->orWhere(function ($sub) use ($startDate, $endDate) {
                // Standard overlap
                $sub->whereNotNull('start_date')
                    ->where(function ($overlap) use ($startDate, $endDate) {
                    $overlap->where('start_date', '<=', $endDate ?? now())
                        ->where(function ($c) use ($startDate) {
                            $c->whereNull('end_date')->orWhere('end_date', '>=', $startDate);
                        });
                    });
                });
            });
            })
            ->first();

        if ($rateContract) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rate Contract already exists for this vendor in the selected date range.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'No existing Rate Contract found for this vendor in the selected date range.',
        ]);
    }


}
