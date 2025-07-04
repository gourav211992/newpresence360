<?php
namespace App\Helpers;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;

class StoreHelper
{
    public static function getAvailableStoresForVendor(array $selectedStoreIds = [], $vendorId = null) : DatabaseCollection
    {
        $storeQuery = ErpSubStore::select('id', 'name') -> whereHas('parents', function ($parentQuery) {
            $parentQuery -> withDefaultGroupCompanyOrg();
        }) -> 
        where('type', ConstantHelper::VENDOR_STORE);
        //Edit Case -> show inactive if selected
        if (count($selectedStoreIds) > 0) {
            $storeQuery -> where(function ($statusQuery) use($selectedStoreIds) {
                $statusQuery -> where('status', ConstantHelper::ACTIVE)
                -> orWhereIn('id', $selectedStoreIds);
            });
        } else { // Create case -> only active
            $storeQuery -> where('status', ConstantHelper::ACTIVE);
        }
        //Only show free (non used) vendor stores
        $stores = $storeQuery 
        // -> whereDoesntHave('vendor_stores', function ($vendorQuery) use($vendorId) {
        //     $vendorQuery -> when($vendorId, function ($subVendorQuery) use($vendorId) {
        //         $subVendorQuery -> whereNot('vendor_id', $vendorId);
        //     });
        // }) 
        -> get();
        return $stores;
    }
}
