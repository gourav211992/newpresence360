<?php
namespace App\Helpers;

use DB;
use Auth;

use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use App\Models\ErpAttribute;
use App\Models\ItemAttribute;

use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use App\Models\StockLedgerStoragePoint;

use App\Models\WhLevel;
use App\Models\WhDetail;
use App\Models\WhStructure;
use App\Models\WhItemMapping;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Models\MrnItemLocation;
use Illuminate\Support\Facades\Log;


class StoragePointHelper
{
    public function __construct()
    {
    
    }

    // Get Storage POints
    public static function getStoragePoints($itemId, $qty=NULL, $locationId=NULL, $subLocationId=NULL)
    {
        $user = Helper::getAuthenticatedUser();
        $data = array();
        try{
            // Step 1: Try to find mapping by item_id
            $records = \DB::table('erp_wh_item_mappings')
                ->whereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [$itemId])
                ->where('store_id', $locationId)
                ->where('sub_store_id', $subLocationId)
                ->get();
            // Step 2: If no records found → try sub_category_id, then category_id
            if ($records->isEmpty()) {
                // Get item's category and sub-category
                $item = \DB::table('erp_items')->where('id', $itemId)->first();
                if ($item) {
                    // Try sub_category_id
                    if ($item->subcategory_id) {
                        $records = \DB::table('erp_wh_item_mappings')
                            ->whereRaw("JSON_CONTAINS(sub_category_id, JSON_QUOTE(?))", [(string)$item->subcategory_id])
                            ->get();
                    }
                    
                    // If still empty, try category_id
                    if ($records->isEmpty() && $item->category_id) {
                        $records = \DB::table('erp_wh_item_mappings')
                        ->whereRaw("JSON_CONTAINS(category_id, JSON_QUOTE(?))", [(string)$item->category_id])
                        ->get();
                    }
                }
            }

            // Step 3: Parse structure_details
            $storagePointIds = [];

            foreach ($records as $record) {
                $structureDetails = json_decode($record->structure_details, true);

                foreach ($structureDetails as $level) {
                    if (!empty($level['level-values']) && is_array($level['level-values'])) {
                        $storagePointIds = array_merge($storagePointIds, $level['level-values']);
                    }
                }
            }

            $storagePointIds = array_unique($storagePointIds);

            // Step 4: Fetch matching storage points
            $results = self::getFinalStoragePoints($storagePointIds);
            // $results = \DB::table('erp_wh_details')
            //     ->where('is_storage_point', 1)
            //     ->whereIn('id', $storagePointIds)
            //     ->get();
            
            if(!empty($results)){
                $message = "Records successfuly fetched.";
                $data = self::successResponse($message, $results);
            } else{
                dd('no');
            }   
            return $data;
        } catch(\Exception $e){
            $data = self::errorResponse($e->getMessage());
            return $data;

        }
    }

    // Get Final Storage Points
    private static function getFinalStoragePoints(array $initialIds)
    {
        $finalIds = [];

        foreach ($initialIds as $id) {
            $detail = \DB::table('erp_wh_details')->where('id', $id)->first();

            if (!$detail) continue;

            if ($detail->is_storage_point == 1) {
                $finalIds[] = $detail->id;
            } else {
                // Recursively find child storage points
                $childStoragePoints = self::findChildStoragePoints($detail->id);
                $finalIds = array_merge($finalIds, $childStoragePoints);
            }
        }

        $finalIds = array_unique($finalIds);

        return \DB::table('erp_wh_details')
            ->whereIn('id', $finalIds)
            ->get()
            ->map(function ($detail) {
                $parents = self::getParentHierarchy($detail->parent_id);
                $detail->parents = implode(' → ', $parents); // Optional: format as "Zone → Bay → Rack"
                return $detail;
            });
    }

    // Find Child Storage Points
    private static function findChildStoragePoints($parentId)
    {
        $results = [];
        $children = \DB::table('erp_wh_details')
            ->where('parent_id', $parentId)
            ->get();

        foreach ($children as $child) {
            if ($child->is_storage_point == 1) {
                $results[] = $child->id;
            } else {
                // Recursive call
                $results = array_merge($results, self::findChildStoragePoints($child->id));
            }
        }

        return $results;
    }

    private static function getParentHierarchy($parentId)
    {
        $names = [];

        while ($parentId) {
            $parent = \DB::table('erp_wh_details')->where('id', $parentId)->first();
            if (!$parent) break;

            $names[] = $parent->name;
            $parentId = $parent->parent_id;
        }

        return $names; // returns array like ['Zone A', 'Bay 3']
    }

    // Save Storage Points
    public static function saveStoragePoints($documentHeader, $documentDetailId=NULL, $bookType, $documentStatus, $transactionType = NULL, $stockReservation = NULL)
    {
        $user = Helper::getAuthenticatedUser();
        $data = array();
        try{
            if(empty($documentDetailId)){
                $message = "No storage points found.";
                $data = self::errorResponse($message);
                return $data;
            }

            $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeader->id)
                ->whereIn('document_detail_id',$documentDetailId)
                ->where('store_id',$documentHeader->store_id)
                ->where('sub_store_id',$documentHeader->sub_store_id)
                ->where('book_type','=',$bookType)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->whereNull('utilized_id')
                ->get();

            if(empty($stockLedger)){
                $message = "Stock Ledger not found.";
                $data = self::errorResponse($message);
                return $data;
            }

            foreach($stockLedger as $val){
                $mrnItemLocations = MrnItemLocation::with(
                    [
                        'mrnHeader',
                        'mrnDetail',
                    ]
                )
                ->where('mrn_header_id', $val->document_header_id)
                ->where('mrn_detail_id', $val->document_detail_id)
                ->whereNotNull('storage_number')
                ->whereNotNull('packet_number')
                ->get();

                foreach($mrnItemLocations as $mrnItemLocation){
                    $stockLedgerStoragePoint = new StockLedgerStoragePoint();
                    $stockLedgerStoragePoint->stock_ledger_id = $val->id;
                    $stockLedgerStoragePoint->item_id = $val->item_id;
                    $stockLedgerStoragePoint->store_id = $val->store_id;
                    $stockLedgerStoragePoint->sub_store_id = $val->sub_store_id;
                    $stockLedgerStoragePoint->wh_detail_id = $mrnItemLocation->wh_detail_id;
                    $stockLedgerStoragePoint->quantity = $mrnItemLocation->inventory_uom_qty;
                    $stockLedgerStoragePoint->packet_number = $mrnItemLocation->packet_number;
                    $stockLedgerStoragePoint->storage_number = $mrnItemLocation->storage_number;
                    $stockLedgerStoragePoint->status = $documentStatus;
                    $stockLedgerStoragePoint->save();
                }
            }
            
            $message = "Storage points saved successfully.";
            $data = self::successResponse($message, $stockLedger);
            return $data;
        } catch(\Exception $e){
            $data = self::errorResponse($e->getMessage());
            return $data;
        }
    }

    // Error Response
    private static function errorResponse($message)
    {
        return [
            "status" => "error",
            "code" => "500",
            "message" => $message,
            "data" => null,
        ];
    }

    // Success Response
    private static function successResponse($response,$data)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response,
            "data" => $data
        ];
    }

}
