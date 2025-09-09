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
use App\Models\ErpItem;
use App\Models\MrnDetail;
use App\Models\MrnItemLocation;
use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Support\Facades\Log;


class StoragePointHelper
{
    public function __construct()
    {
    
    }

    public static function getStoragePoints($itemId, $qty=NULL, $locationId=NULL, $subLocationId=NULL)
    {
        $data = array();
        try{
            // Step 1: Try item-level mapping
            $records = WhItemMapping::when($locationId, fn($q) => $q->where('store_id', $locationId))
                ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                ->when($itemId, fn($q) => $q->whereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [(string) $itemId]))
                ->get();
            
            // Step 2: If no records found → try sub_category_id, then category_id
            if ($records->isEmpty()) {
                // Get item's category and sub-category
                $item = ErpItem::find($itemId);
                if ($item) {
                    // Try sub_category_id
                    if ($item->subcategory_id) {
                        $records = WhItemMapping::when($locationId, fn($q) => $q->where('store_id', $locationId))
                            ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                            ->whereRaw("JSON_CONTAINS(category_id, JSON_QUOTE(?))", [(string)$item->subcategory_id])
                            ->get();
                    }
                    
                    
                }
            }

            // Step 2.5: If still no mapping, fallback to all available storage points in the given store
            if ($records->isEmpty() && $locationId) {
                $fallbackStoragePoints = WhDetail::where('store_id', $locationId)
                    ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                    ->where('is_storage_point', 1)
                    ->get();
                
                $availablePoints = $fallbackStoragePoints->filter(function ($detail) {
                    return self::hasSpace($detail);
                });

                if ($availablePoints->isNotEmpty()) {
                    $data = self::successResponse('Fallback: Showing available storage points without mapping.', $availablePoints->values());
                    return $data;
                }
            }

            // Step 3: Parse structure_details
            $storagePointIds = [];

            foreach ($records as $record) {
                // $structureDetails = json_decode($record->structure_details, true);
                $structureDetails = is_string($record->structure_details) ? json_decode($record->structure_details, true) : $record->structure_details;

                if (!$structureDetails) continue;

                // Get the last level-values
                $lastLevel = end($structureDetails);
                $lastLevelValues = $lastLevel['level-values'] ?? [];

                // Get last-level storage points if defined
                if (!empty($lastLevelValues)) {
                    $details = WhDetail::whereIn('id', $lastLevelValues)
                    ->get()
                    ->keyBy('id');

                    $hasLastLevel = $details->contains(fn($d) => $d->is_last_level == 1);
                    if ($hasLastLevel) {
                        $storagePointIds = array_merge($storagePointIds, array_keys($details->toArray()));
                        continue;
                    }

                }

                // Otherwise, find valid children recursively
                foreach ($structureDetails as $level) {
                    if (!empty($level['level-values']) && is_array($level['level-values'])) {
                        foreach ($level['level-values'] ?? [] as $val) {
                            $detail = WhDetail::find($val);
                            if ($detail && $detail->is_storage_point == 1 && self::hasSpace($detail)) {
                                $storagePointIds[] = $detail->id;
                            }

                            $childIds = self::findChildStoragePoints($val);
                            $storagePointIds = array_merge($storagePointIds, $childIds);
                        }
                    }
                }
            }

            $storagePointIds = array_unique($storagePointIds); 
            
            // Step 4: Fetch matching storage points
            $results = self::filterValidStoragePoints($storagePointIds);   

            if(!empty($results)){
                $message = "Records successfuly fetched.";
                $data = self::successResponse($message, $results);
            } else{
                $message = "No available storage points found.";
                $data = self::errorResponse($message);
            }   
            return $data;
        } catch(\Exception $e){
            $data = self::errorResponse($e->getMessage());
            return $data;

        }
    }

    private static function filterValidStoragePoints(array $ids)
    {
        $details = \DB::table('erp_wh_details')
            ->whereIn('id', array_unique($ids))
            ->get();

        return $details->filter(fn($detail) => $detail->is_storage_point == 1 && self::hasSpace($detail))
        ->values(); // reset index
    }

    // Get Final Storage Points
    private static function getFinalStoragePoints(array $initialIds)
    {
        $finalIds = [];

        foreach ($initialIds as $id) {
            $detail = WhDetail::find($id);

            if (!$detail) continue;

            // Check if storage point and has space (weight or volume)
            $hasSpace = (
                (is_null($detail->max_weight) || is_null($detail->current_weight) || $detail->current_weight < $detail->max_weight)
                ||
                (is_null($detail->max_volume) || is_null($detail->current_volume) || $detail->current_volume < $detail->max_volume)
            );


            if ($detail->is_storage_point == 1 && $hasSpace) {
                $finalIds[] = $detail->id;
            } else {
                // Recursively find child storage points
                $childStoragePoints = self::findChildStoragePoints($detail->id);
                $finalIds = array_merge($finalIds, $childStoragePoints);
            }
        }

        $finalIds = array_unique($finalIds);

        return WhDetail::whereIn('id', $finalIds)
            ->get();
    }

    private static function findChildStoragePoints($parentId)
    {
        $results = [];

        $children = WhDetail::where('parent_id', $parentId)
            ->get();

        foreach ($children as $child) {
            if ($child->is_storage_point == 1 && self::hasSpace($child)) {
                $results[] = $child->id;
            } else {
                $results = array_merge($results, self::findChildStoragePoints($child->id));
            }
        }

        return $results;
    }

    private static function hasSpace($detail)
    {
        return (
            (is_null($detail->max_weight) || is_null($detail->current_weight) || $detail->current_weight < $detail->max_weight)
            ||
            (is_null($detail->max_volume) || is_null($detail->current_volume) || $detail->current_volume < $detail->max_volume)
        );
    }

    // Save Storage Points
    public static function saveStoragePoints($documentHeader, $documentDetailId = NULL, $bookType, $documentStatus, $transactionType = NULL, $stockReservation = NULL, $subStoreId = NULL)
    {
        $data = array();
        try{
            if(empty($documentDetailId)){
                $message = "No storage points found.";
                $data = self::errorResponse($message);
                return $data;
            }

            // dd($documentHeader->id,$documentDetailId, $documentHeader->store_id, $documentHeader->sub_store_id,$bookType);
            $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeader->id)
                ->whereIn('document_detail_id',$documentDetailId)
                ->where('store_id',$documentHeader->store_id)
                ->where('sub_store_id',$subStoreId)
                ->where('book_type','=',$bookType)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->whereNull('utilized_id')
                ->get();
            
            if(empty($stockLedger)){
                $message = "Stock Ledger not found.";
                $data = self::errorResponse($message);
                return $data;
            }

            foreach($stockLedger as $val){
                $mrnDetail = MrnDetail::find($val->document_detail_id);

                $scannedPacketCount = ErpItemUniqueCode::where('morphable_id',$val->document_detail_id)
                        ->where('trns_type',$bookType)
                        ->where('status',CommonHelper::SCANNED)
                        ->whereNull('utilized_id')
                        ->count();

                $orderQty =  ItemHelper::convertToAltUom($mrnDetail->item_id, $mrnDetail->uom_id, $scannedPacketCount ?? 0);
                $mrnDetail->inventory_uom_qty = $scannedPacketCount;
                $mrnDetail->order_qty = $orderQty;
                $mrnDetail->save();

                $val->receipt_qty = $mrnDetail->inventory_uom_qty;
                $val->putaway_pending_qty = 0;
                $val->save();
                
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

    public static function getStoragePointsForMultipleItems(array $itemIds, $locationId = null, $subLocationId = null)
    {
        try {
            if (empty($itemIds)) {
                return self::errorResponse("Item Ids required.");
            }

            if (empty($locationId)) {
                return self::errorResponse("Location Id required.");
            }

            // Step 1: Fetch all item mappings
            $mappings = WhItemMapping::where('store_id', $locationId)
                ->where(function ($q) use ($itemIds) {
                    foreach ($itemIds as $itemId) {
                        $q->orWhereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [(string)$itemId]);
                    }
                })
                ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                ->get();

            // Step 2: If not found, try subcategory/category fallback
            if ($mappings->isEmpty()) {
                $items = ErpItem::whereIn('id', $itemIds)->get();

                $subcategoryIds = $items->pluck('subcategory_id')->filter()->unique()->toArray();
                $categoryIds = $items->pluck('category_id')->filter()->unique()->toArray();

                if (!empty($subcategoryIds)) {
                    $mappings = WhItemMapping::where(function ($q) use ($subcategoryIds) {
                            foreach ($subcategoryIds as $subId) {
                                $q->orWhereRaw("JSON_CONTAINS(category_id, JSON_QUOTE(?))", [(string)$subId]);
                            }
                        })->get();
                }
            }

            // Step 3: Extract structure point IDs
            $structurePointIds = collect($mappings)->flatMap(function ($record) {
                $details = json_decode($record->structure_details, true) ?? [];
                return collect($details)->pluck('level-values')->flatten()->all();
            })->unique()->values()->all();

            if (empty($structurePointIds)) {
                return self::successResponse("No storage points mapped.", []);
            }

            // Step 4: Get final storage points
            $storagePoints = self::getFinalStoragePoints($structurePointIds);

            return self::successResponse("Records successfully fetched.", $storagePoints);
        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage());
        }
    }


    // Get Specific Storage Point Detail
    public static function getStoragePointDetail($storageNumber)
    {
        try {
            if (!$storageNumber) {
                return self::errorResponse("Storage number is required.");
            }

            // Fetch the storage point
            $storagePoint = WhDetail::where('storage_number', $storageNumber)->first();

            if (!$storagePoint) {
                return self::errorResponse("Storage point not found.");
            }

            return self::successResponse("Storage point details fetched successfully.", $storagePoint);

        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage());
        }
    }

    public static function getStoragePointDetailById($storagePointId)
    {
        try {
            if (!$storagePointId) {
                return self::errorResponse("Storage point ID is required.");
            }

            // Fetch by ID
            $storagePoint = WhDetail::where('id', $storagePointId)
                ->select('id','heirarchy_name','name','max_weight','max_volume','current_weight','current_volume','storage_number','parent_id')
                ->first();

            if (!$storagePoint) {
                return self::errorResponse("Storage point not found.");
            }

            return self::successResponse("Storage point details fetched successfully.", $storagePoint);

        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage());
        }
    }



}
