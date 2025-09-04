<?php 
namespace App\Services\Inspection;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

use App\Models\InspChecklist;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\InspBatchDetail;
use App\Models\InspectionItemAttribute;

use App\Models\InspBatchDetailHistory;
use App\Models\InspectionHeaderHistory;
use App\Models\InspectionDetailHistory;
use App\Models\InspectionItemLocation;
use App\Models\InspectionItemAttributeHistory;

use App\Models\Item;
use App\Models\MrnBatchDetail;


use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class UpdateAddressService
{
    // Store/Update Address
    public static function updateAddress($inspection)
    {
        try {

            $vendorBillingAddress = $inspection->billingAddress ?? null;
            $vendorShippingAddress = $inspection->shippingAddress ?? null;
            
            // Save Vendor Billing Address
            if ($vendorBillingAddress) {
                $billingAddress = $inspection->bill_address_details()->firstOrNew([
                    'type' => 'billing',
                ]);
                $billingAddress->fill([
                    'address' => $vendorBillingAddress->address,
                    'country_id' => $vendorBillingAddress->country_id,
                    'state_id' => $vendorBillingAddress->state_id,
                    'city_id' => $vendorBillingAddress->city_id,
                    'pincode' => $vendorBillingAddress->pincode,
                    'phone' => $vendorBillingAddress->phone,
                    'fax_number' => $vendorBillingAddress->fax_number,
                ]);
                $billingAddress->save();
            }

            // Save Vendor Shipping Address
            if ($vendorShippingAddress) {
                $shippingAddress = $inspection->ship_address_details()->firstOrNew([
                    'type' => 'shipping',
                ]);
                $shippingAddress->fill([
                    'address' => $vendorShippingAddress->address,
                    'country_id' => $vendorShippingAddress->country_id,
                    'state_id' => $vendorShippingAddress->state_id,
                    'city_id' => $vendorShippingAddress->city_id,
                    'pincode' => $vendorShippingAddress->pincode,
                    'phone' => $vendorShippingAddress->phone,
                    'fax_number' => $vendorShippingAddress->fax_number,
                ]);
                $shippingAddress->save();
            }

            # Store location address
            if($inspection?->erpStore)
            {
                $storeAddress  = $inspection?->erpStore->address;
                $storeLocation = $inspection->store_address()->firstOrNew();
                $storeLocation->fill([
                    'type' => 'location',
                    'address' => $storeAddress->address,
                    'country_id' => $storeAddress->country_id,
                    'state_id' => $storeAddress->state_id,
                    'city_id' => $storeAddress->city_id,
                    'pincode' => $storeAddress->pincode,
                    'phone' => $storeAddress->phone,
                    'fax_number' => $storeAddress->fax_number,
                ]);
                $storeLocation->save();
            }

            return self::successResponse('Address successfully saved.');
        } catch (\Throwable $e) {
            return self::errorResponse($e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    // Error Response
    private static function errorResponse(string $message): array
    {
        return [
            'status'  => 'error',
            'code'    => 500,
            'message' => $message,
            'data'    => null,
        ];
    }

    // Success Response
    private static function successResponse(string $message, $data = null): array
    {
        return [
            'status'  => 'success',
            'code'    => 200,
            'message' => $message,
            'data'    => $data,
        ];
    }

    
}