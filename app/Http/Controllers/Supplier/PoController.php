<?php

namespace App\Http\Controllers\Supplier;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\Helper;
use App\Helpers\NumberHelper;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\TaxHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PoRequest;
use App\Models\Address;
use App\Models\AttributeGroup;
use App\Models\Currency;
use App\Models\ErpAddress;
use App\Models\Hsn;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\PaymentTerm;
use App\Models\PiItem;
use App\Models\PoItem;
use App\Models\PoItemAttribute;
use App\Models\PoItemDelivery;
use App\Models\PoTerm;
use App\Models\PurchaseIndent;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderMedia;
use App\Models\PurchaseOrderTed;
use App\Models\TermsAndCondition;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\Department;
use App\Models\ErpStore;
use Auth;
use DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;

class PoController extends Controller
{
    protected $type;

    public function __construct(Request $request)
    {
        // view()->share('type', request()->route('tyspe'));
        if($request->route('type') == 'purchase-order')
        {
            $this->type = 'po';
        } else {
            $this->type = 'supplier-invoice';
        }
    }

    # Po List
    public function index(Request $request)
    {
        $type = 'po';
        if (request()->ajax()) {
            $user = Helper::getAuthenticatedUser();
            $vendor_id = $request->cookie('vendor_id');
            $pos = PurchaseOrder::ofType($type)
                    ->withDefaultGroupCompanyOrg()
                    ->where('vendor_id',$vendor_id)
                    ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                    ->latest()
                    ->with('vendor')
                    ->get();
            return DataTables::of($pos)
            ->addIndexColumn()
            ->editColumn('document_status', function ($row) {
                $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status];
                $route = route('supplier.po.edit', ['type' => request()->route('type'), 'id' => $row->id]);
                $displayStatus = $row->display_status;
                return "<div style='text-align:right;'>
                    <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                    <div class='dropdown' style='display:inline;'>
                        <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                            <i data-feather='more-vertical'></i>
                        </button>
                        <div class='dropdown-menu dropdown-menu-end'>
                            <a class='dropdown-item' href='" . $route . "'>
                                <i data-feather='edit-3' class='me-50'></i>
                                <span>View/ Edit Detail</span>
                            </a>
                        </div>
                    </div>
                </div>";
            })
            ->addColumn('book_name', function ($row) {
                return $row->book ? $row->book?->book_code : 'N/A';
            })
            ->addColumn('department', function ($row) {
                return $row->department ? $row->department?->name : 'N/A';
            })
            ->addColumn('store_location', function ($row) {
                return $row->store_location ? $row->store_location?->store_code : 'N/A';
            })
            ->addColumn('curr_name', function ($row) {
                return $row->currency ? ($row->currency?->short_name ?? $row->currency?->name) : 'N/A';
            })
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
            })
            ->editColumn('revision_number', function ($row) {
                return strval($row->revision_number);
            })
            ->addColumn('vendor_name', function ($row) {
                return $row->vendor?->company_name ?? 'NA';
            })
            ->addColumn('components', function ($row) {
                return $row->po_items->count();
            })
            ->addColumn('company_name', function ($row) {
                return $row?->organization?->name ?? 'NA';
            })
            ->editColumn('total_item_value', function ($row) {
                return number_format($row->total_item_value,2);
            })
            ->editColumn('total_discount_value', function ($row) {
                return number_format($row->total_discount_value,2);
            })
            ->editColumn('total_tax_value', function ($row) {
                return number_format($row->total_tax_value,2);
            })
            ->editColumn('total_expense_value', function ($row) {
                return number_format($row->total_expense_value,2);
            })
            ->editColumn('grand_total_amount', function ($row) {
                return number_format($row->grand_total_amount,2);
            })
            ->rawColumns(['document_status'])
            ->make(true);
        }
        return view('supplier.po.index');
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $isPi = intval($request->isPi) ?? 0;
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $poItemId = $request->po_item_id ?? null;
        $itemAttIds = [];
        if($poItemId) {
            $poItem = PoItem::find($poItemId);
            if($poItem) {
                $itemAttIds = $poItem->attributes()->pluck('item_attribute_id')->toArray();
            }
        }
        $itemAttributes = collect();
        if(count($itemAttIds)) {
            $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
            if(count($itemAttributes) < 1) {
                $itemAttributes = $item?->itemAttributes;
            }
        } else {
            $itemAttributes = $item?->itemAttributes;
        }
        $html = view('supplier.po.partials.comp-attribute',compact('item','rowCount','selectedAttr','isPi','itemAttributes'))->render();
        $hiddenHtml = '';
        if($item) {
            foreach ($itemAttributes as $attribute) {
                    $selected = '';
                    foreach ($attribute->attributes() as $value) {
                        if (in_array($value->id, $selectedAttr)) {
                            $selected = $value->id;
                        }
                    }
                $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
            }
        }
        return response()->json(['data' => ['attr' => $item?->itemAttributes->count() ?? 0,'html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
    }
    
    # get tax calcualte
    public function taxCalculation(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $location = ErpStore::find($request->location_id ?? null);
        $organization = $user->organization;
        $firstAddress = $location->address->first();
        if ($firstAddress) {
            $companyCountryId = $firstAddress->country_id;
            $companyStateId = $firstAddress->state_id;
        } else {
            return response()->json(['error' => 'No address found for the organization.'], 404);
        }
        $price = $request->input('price', 6000);
        $hsnId = null;
        $item = Item::find($request -> item_id);
        if (isset($item)) {
            $hsnId = $item -> hsn_id;
        } else {
            return response()->json(['error' => 'Invalid Item'], 500);
        }
        $transactionType = $request->input('transaction_type', 'sale');
        if ($transactionType === "sale") {
            $fromCountry = $companyCountryId;
            $fromState = $companyStateId;
            $upToCountry = $request->input('party_country_id', $companyCountryId) ?? 0;
            $upToState = $request->input('party_state_id', $companyStateId) ?? 0;
        } else {
            $fromCountry = $request->input('party_country_id', $companyCountryId) ?? 0;
            $fromState = $request->input('party_state_id', $companyStateId) ?? 0;
            $upToCountry = $companyCountryId;
            $upToState = $companyStateId;
        }
        try {
            $taxDetails = TaxHelper::calculateTax( $hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType);
            $rowCount = intval($request->rowCount) ?? 1;
            $itemPrice = floatval($request->price) ?? 0;
            // dd($hsnId,$price,$fromCountry,$fromState,$upToCountry,$upToState,$transactionType);
            $html = view('supplier.po.partials.item-tax',compact('taxDetails','rowCount','itemPrice'))->render();
            return response()->json(['data' => ['html' => $html, 'rowCount' => $rowCount], 'message' => 'fetched', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $delivery = json_decode($request->delivery,200) ?? [];
        $item = Item::find($request->item_id ?? null);
        $uomId = $request->uom_id ?? null;
        $qty = floatval($request->qty) ?? 0;
        // $uomName = '';
        $uomName = $item?->uom?->name ?? 'NA';
        if($item?->uom_id == $uomId) {
        } else {
            $alUom = $item?->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
            // $uomName = $alUom->uom->name ?? 'NA';
        }

        $specifications = $item?->specifications()->whereNotNull('value')->get() ?? [];
        $remark = $request->remark ?? null;
        $delivery = isset($delivery) ? $delivery  : null;
        $html = view('supplier.po.partials.comp-item-detail',compact('item','selectedAttr','remark','uomName','qty','delivery','specifications'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # Edit Po
    public function edit(Request $request, $id)
    {
        $title = '';
        $menu = 'Home';
        $menu_url = url('/');
        $sub_menu = 'Edit';
        $short_title = ''; 
        $reference_from_title = '';
        $user = Helper::getAuthenticatedUser();
        $serviceAlias = ConstantHelper::PO_SERVICE_ALIAS;
        $title = 'Purchase Order';
        $short_title = 'PO';
        $reference_from_title = 'Purchase Indent'; 

        $po = PurchaseOrder::where('id',$id)->first();
        if (!$po) {
            abort(404);
        }
        $createdBy = $po?->created_by;
        $revision_number = $po->revision_number;
        $totalValue = $po->grand_total_amount ?? 0;
        $creatorType = Helper::userCheck()['type'];
        $buttons = Helper::actionButtonDisplay($po->book_id,$po->document_status , $po->id, $totalValue, $po->approval_level, $po->created_by ?? 0, $creatorType, $revision_number);

        $revNo = $po->revision_number;
        if($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $po->revision_number;
        }

        $approvalHistory = Helper::getApprovalHistory($po->book_id, $po->id, $revNo, $totalValue,$createdBy);
        $termsAndConditions = TermsAndCondition::withDefaultGroupCompanyOrg()
                            ->where('status',ConstantHelper::ACTIVE)->get();
        $view = 'supplier.po.view';

        if($request->has('revisionNumber') && $request->revisionNumber != $po->revision_number) {
            $po = $po->source()->where('revision_number', $request->revisionNumber)->first();
            $view = 'supplier.po.view';
        } 
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';

        $organization = Organization::where('id', $user->organization_id)->first();
        $departments = Department::where('organization_id', $organization->id)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->get();

        $selectedDepartmentId = null;
        $userCheck = Auth::guard('web2')->user();
        if($userCheck) {
            $selectedDepartmentId = $user?->department_id;
        }
        $locations = InventoryHelper::getAccessibleLocations('stock', $po->store_id);
        return view($view, [
            'po' => $po,
            'buttons' => $buttons,
            'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'termsAndConditions' => $termsAndConditions,
            'revision_number' => $revision_number,
            'title' => $title,
            'menu' => $menu,
            'menu_url' => $menu_url,
            'sub_menu' => $sub_menu,
            'short_title' => $short_title,
            'reference_from_title' => $reference_from_title,
            'selectedDepartmentId' => $selectedDepartmentId,
            'departments' => $departments,
            'locations' => $locations
        ]);
    }

    // genrate pdf
    public function generatePdf(Request $request, $type, $id)
    {
        $user = Helper::getAuthenticatedUser();

        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $po = PurchaseOrder::with(['vendor', 'currency', 'po_items', 'book', 'headerExpenses', 'TermsCondition'])
            ->findOrFail($id);

        $totalItemValue = $po->po_items()
                ->selectRaw('SUM(order_qty * rate) as total')
                ->value('total') ?? 0.00;
        $totalItemDiscount = $po->po_items()->sum('item_discount_amount') ?? 0.00;
        $totalHeaderDiscount = $po->po_items()->sum('header_discount_amount') ?? 0.00;
        $totalTaxes = $po->po_items()->sum('tax_amount') ?? 0.00;
        $totalTaxableValue = ($totalItemValue - ($totalItemDiscount + $totalHeaderDiscount));
        $totalAfterTax = ($totalTaxableValue + $totalTaxes);
        $totalAmount = ($totalAfterTax + $po->total_expense_value ?? 0.00);
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        // Path to your image (ensure the file exists and is accessible)
        $imagePath = public_path('assets/css/midc-logo.jpg'); // Store the image in the public directory
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$po->document_status] ?? '';
        $fileName = '';
        if ($type == 'supplier-invoice') {
            // $path = 'pdf.supplier-invoice';
            $path = 'pdf.supplier-invoice2';
            $fileName = 'Supplier-Invoice-' . date('Y-m-d') . '.pdf';
        } else {
            // $path = 'pdf.po';
            $path = 'pdf.po2';
            $fileName = 'Purchase-Order-' . date('Y-m-d') . '.pdf';
        }

        $taxes = PurchaseOrderTed::where('purchase_order_id', $po->id)
        ->where('ted_type', 'Tax')
        ->select('ted_type','ted_id','ted_name', 'ted_perc', DB::raw('SUM(ted_amount) as total_amount'),DB::raw('SUM(assessment_amount) as total_assessment_amount'))
        ->groupBy('ted_name', 'ted_perc')
        ->get();
        $sellerShippingAddress = $po->latestShippingAddress();
        $buyerAddress = $po?->store_location?->address;
        $pdf = PDF::loadView(
            // return view(
            $path,
            [
                'user' => $user,
                'po'=> $po,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress,
                'totalItemValue' => $totalItemValue,
                'totalItemDiscount' =>$totalItemDiscount,
                'totalHeaderDiscount' => $totalHeaderDiscount,
                'totalTaxes' =>$totalTaxes,
                'totalTaxableValue' =>$totalTaxableValue,
                'totalAfterTax' =>$totalAfterTax,
                'totalAmount'=>$totalAmount,
                'amountInWords'=>$amountInWords,
                'imagePath' => $imagePath,
                'docStatusClass' => $docStatusClass,
                'taxes' => $taxes,
                'sellerShippingAddress' => $sellerShippingAddress,
                'buyerAddress' => $buyerAddress
            ]
        );
        return $pdf->stream($fileName);
    }
}