<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\Book;
use App\Models\PaymentTerm;
use App\Services\PurchaseOrderService;
use App\Models\Currency;
use App\Models\Item;
use App\Models\Hsn;
use App\Models\Unit;
use App\Models\ErpAddress;
use App\Models\Address;
use App\Models\Tax;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;


class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }
    public function get_purchase_order_no($book_id)
    {
        $data = Helper::generateVoucherNumber($book_id);
        return response()->json($data);
    }
    
   
    public function index(Request $request)
    {
        $pos = PurchaseOrder::with('vendor')->get();
        return view('procurement.po.index', compact('pos'));
    }
    
    public function create()
    {
        $user=Auth::user();
        $books = Helper::getSeriesCode('Po')->get();
        $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        $paymentTerms = PaymentTerm::where('status', ConstantHelper::ACTIVE)->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->get();
        $items = Item::where('status', ConstantHelper::ACTIVE)->get();
        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)->get();
        $units = Unit::where('status', ConstantHelper::ACTIVE)->get();
        $discountTypes = ConstantHelper::DISCOUNT_TYPES;

        return view('procurement.po.create', [
            'vendors' => $vendors,
            'paymentTerms' => $paymentTerms,
            'currencies' => $currencies,
            'items' => $items,
            'hsns' => $hsns,
            'units' => $units,
            'books'=>$books,
            'discountTypes'=>$discountTypes
        ]); 
    }
    
    public function store(PurchaseOrderRequest $request)
    {
        try {
            $purchaseOrder = $this->purchaseOrderService->createPurchaseOrder($request);

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order created successfully',
                'data' => $purchaseOrder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Purchase Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
 
    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        $user = Auth::user();

        $purchaseOrder = PurchaseOrder::with(['vendor', 'currency', 'items'])
            ->findOrFail($id);

        if (is_string($purchaseOrder->po_date)) {
            $purchaseOrder->po_date = \Carbon\Carbon::parse($purchaseOrder->po_date);
        }

        $vendor = $purchaseOrder->vendor;
        $vendorPaymentTerms = $vendor ? $vendor->vendorDetail->paymentTerms()->where('status', ConstantHelper::ACTIVE)->get() : [];
        $vendorCurrencies = $vendor ? $vendor->vendorDetail->currency()->where('status', ConstantHelper::ACTIVE)->get() : [];
        $addresses = $vendor ? $vendor->addresses()->get() : [];

        $shippingAddresses = [];
        $billingAddresses = [];
    
        foreach ($addresses as $address) {
            if ($address->type === 'shipping' || $address->type === 'both') {
                $shippingAddresses[$address->id] = $address;
            }
            if ($address->type === 'billing' || $address->type === 'both') {
                $billingAddresses[$address->id] = $address;
            }
        }
        $items = Item::with('hsn','hsn.tax','uom')->where('status', ConstantHelper::ACTIVE)
            ->where('vendor_id', $purchaseOrder->vendor_id)->get();

        $allUnitIds = $items->pluck('uom_id')->merge($items->pluck('alternate_uom_id'))->unique();
        $units = Unit::whereIn('id', $allUnitIds)->get()->keyBy('id');
            
        $gstDetails = json_decode($purchaseOrder->gst_details, true) ?? [];
        $purchaseOrder->sgst_amount = $gstDetails['sgst_amount'] ?? '0.00';
        $purchaseOrder->cgst_amount = $gstDetails['cgst_amount'] ?? '0.00';
        $purchaseOrder->igst_amount = $gstDetails['igst_amount'] ?? '0.00';
    
        $books = Book::where('status', 'Active')
            ->where('organization_id', $user->organization_id)->get();
        $addresses = ErpAddress::all();
        $discountTypes = ConstantHelper::DISCOUNT_TYPES;

        return view('procurement.po.edit', [
            'purchaseOrder' => $purchaseOrder,
            'paymentTerms' => $vendorPaymentTerms,
            'currencies' => $vendorCurrencies,
            'items' => $items,
            'units'=>$units,
            'addresses' => $addresses,
            'books' => $books,
            'discountTypes' => $discountTypes,
            'shippingAddresses' => $shippingAddresses,
            'billingAddresses' => $billingAddresses,
        ]);
    }
    
    
    public function update(PurchaseOrderRequest $request, $id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            $updatedOrder = $this->purchaseOrderService->updatePurchaseOrder($id, $request);
    
            return response()->json([
                'success' => true,
                'message' => 'Purchase Order updated successfully',
                'data' => $updatedOrder
            ], 200);
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Purchase Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    public function destroy(string $id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->delete();
            if ($purchaseOrder->hasMedia('documents')) {
                $purchaseOrder->media()->where('collection_name', 'documents')->each(function ($media) {
                    $media->delete(); 
                });
            }

            $purchaseOrder->delete();

            return response()->json([
                'status' => true,
                'message' => 'Purchase Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Purchase Order',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getDropdownData(Request $request)
    {
        $type = $request->input('type');
        $searchTerm = $request->input('q', '');
        $vendorId = $request->input('vendor_id');
        $page = (int) $request->input('page', 1);
        $perPage = 30;
            $queries = [
                'item' => function() use ($searchTerm, $page, $perPage, $vendorId) {
                    $query = Item::query();
                    if(strlen($searchTerm) >= 3){
                        $query->where('item_name', 'like', "%{$searchTerm}%")->take($perPage);
                    }
                    else{
                        $query->take(10);
                    }
                    
                    if ($vendorId) {
                        $query->whereHas('vendor', function($q) use ($vendorId) {
                            $q->where('id', $vendorId);
                        });
                    }
    
                    $totalItems = $query->count();
                    $items = $query->skip(($page - 1) * $perPage)->get();
                    $allUnitIds = $items->pluck('uom_id')->merge($items->pluck('alternate_uom_id'))->unique();
                    $units = Unit::whereIn('id', $allUnitIds)->get()->keyBy('id');
                    return [
                        'items' => $items->map(fn($item) => [
                            'id' => $item->id,
                            'text' => $item->item_name,
                            'hsn_code' => $item->hsn->code ?? null,
                            'uom' => $units ?? '',
                            'tax' => $item->hsn->tax->value ?? 0
                        ]),
                        'total_count' => $totalItems
           
                    ];
                },
                'vendor' => function() use ($searchTerm, $page, $perPage) {
                    $searchTerm=$searchTerm;
                    $query = Vendor::with([
                            'addresses',
                            'vendorDetail.paymentTerms', 
                            'vendorDetail.currency',     
                            'items.hsn',
                            'items.uom'
                        ]);

                    if(strlen($searchTerm) >= 3){
                        $query->where('company_name', 'like', "%{$searchTerm}%")->take($perPage);
                    }
                    else{
                        $query->take(10);
                    }
                
                    $totalVendors = $query->count();

                    $vendors = $query->skip(($page - 1) * $perPage)->get();
    
                    return [
                        'items' => $vendors->map(function($vendor) {
                            $addresses = $vendor->addresses->groupBy('type');
    
                            $billingAddresses = $addresses->filter(function($addresses, $type) {
                                return in_array($type, ['billing', 'both']);
                            })->flatten()->map(function($address) {
                                return [
                                    'address_id' => $address->id,
                                    'address' => $address->address,
                                    'city' => $address->city_id,
                                    'state' => $address->state_id,
                                    'zip' => $address->pincode,
                                    'type' => $address->type
                                ];
                            });
    
                            $shippingAddresses = $addresses->filter(function($addresses, $type) {
                                return in_array($type, ['shipping', 'both']);
                            })->flatten()->map(function($address) {
                                return [
                                    'address_id' => $address->id,
                                    'address' => $address->address,
                                    'city' => $address->city_id,
                                    'state' => $address->state_id,
                                    'zip' => $address->pincode,
                                    'type' => $address->type
                                ];
                            });
    
                            $paymentTerm = $vendor->vendorDetail->paymentTerms;
                            $currency = $vendor->vendorDetail->currency;
    
                            return [
                                'id' => $vendor->id,
                                'text' => $vendor->company_name,
                                'billing_addresses' => $billingAddresses,
                                'shipping_addresses' => $shippingAddresses,
                                'payment_terms' => $paymentTerm ? [
                                    'id' => $paymentTerm->id,
                                    'name' => $paymentTerm->name
                                ] : null,
                                'currency' => $currency ? [
                                    'id' => $currency->id,
                                    'code' => $currency->short_name,
                                    'symbol' => $currency->symbol
                                ] : null,
                                'items' => $vendor->items->map(function($item) {
                                    return [
                                        'id' => $item->id,
                                        'item_code' => $item->item_code,
                                        'item_name' => $item->item_name,
                                        'hsn_code' => $item->hsn ? $item->hsn->code : null,
                                    ];
                                })
                            ];
                        }),
                        'total_count' => $totalVendors
                    ];
                }
            ];
    
            if (isset($queries[$type])) {
                $result = $queries[$type]();
                return response()->json($result);
            }
       
        return response()->json(['items' => [], 'total_count' => 0]);
    }
    

    
}
