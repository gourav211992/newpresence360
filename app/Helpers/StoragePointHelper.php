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
use App\Models\MrnItemLocation;
use Illuminate\Support\Facades\Log;


class StoragePointHelper
{
    public function __construct()
    {
    
    }

    // Get Storage POints
    // public static function getStoragePoints($itemId, $qty=NULL, $locationId=NULL, $subLocationId=NULL)
    // {
    //     $user = Helper::getAuthenticatedUser();
    //     $data = array();
    //     try{
    //         // Step 1: Try to find mapping by item_id
    //         $query = \DB::table('erp_wh_item_mappings')
    //         ->where('store_id', $locationId);

    //         if($itemId){
    //             $query->whereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [(string)$itemId]);
    //         }

    //         if($subLocationId){
    //             $query->where('sub_store_id', $subLocationId);
    //         }
                
    //         $records = $query->get();
            
    //         // Step 2: If no records found → try sub_category_id, then category_id
    //         if ($records->isEmpty()) {
    //             // Get item's category and sub-category
    //             $item = \DB::table('erp_items')->where('id', $itemId)->first();
    //             if ($item) {
    //                 // Try sub_category_id
    //                 if ($item->subcategory_id) {
    //                     $records = \DB::table('erp_wh_item_mappings')
    //                         ->whereRaw("JSON_CONTAINS(sub_category_id, JSON_QUOTE(?))", [(string)$item->subcategory_id])
    //                         ->get();
    //                 }
                    
    //                 // If still empty, try category_id
    //                 if ($records->isEmpty() && $item->category_id) {
    //                     $records = \DB::table('erp_wh_item_mappings')
    //                     ->whereRaw("JSON_CONTAINS(category_id, JSON_QUOTE(?))", [(string)$item->category_id])
    //                     ->get();
    //                 }
    //             }
    //         }

    //         // Step 3: Parse structure_details
    //         $storagePointIds = [];

    //         foreach ($records as $record) {
    //             $structureDetails = json_decode($record->structure_details, true);

    //             foreach ($structureDetails as $level) {
    //                 if (!empty($level['level-values']) && is_array($level['level-values'])) {
    //                     $storagePointIds = array_merge($storagePointIds, $level['level-values']);
    //                 }
    //             }
    //         }   
    //         $storagePointIds = array_unique($storagePointIds);    
    //         // Step 4: Fetch matching storage points
    //         $results = self::getFinalStoragePoints($storagePointIds);
    //         // $results = \DB::table('erp_wh_details')
    //         //     ->where('is_storage_point', 1)
    //         //     ->whereIn('id', $storagePointIds)
    //         //     ->get();
    //         if(!empty($results)){
    //             $message = "Records successfuly fetched.";
    //             $data = self::successResponse($message, $results);
    //         } else{
    //             dd('no');
    //         }   
    //         return $data;
    //     } catch(\Exception $e){
    //         $data = self::errorResponse($e->getMessage());
    //         return $data;

    //     }
    // }
    public static function getStoragePoints($itemId, $qty=NULL, $locationId=NULL, $subLocationId=NULL)
    {
        $data = array();
        try{
            // Step 1: Try item-level mapping
            $records = \DB::table('erp_wh_item_mappings')
                ->when($locationId, fn($q) => $q->where('store_id', $locationId))
                ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                ->when($itemId, fn($q) => $q->whereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [$itemId]))
                ->get();
            
            // Step 2: If no records found → try sub_category_id, then category_id
            if ($records->isEmpty()) {
                // Get item's category and sub-category
                $item = \DB::table('erp_items')->find($itemId);
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

            // Step 2.5: If still no mapping, fallback to all available storage points in the given store
            if ($records->isEmpty() && $locationId) {
                $fallbackStoragePoints = \DB::table('erp_wh_details')
                    ->where('store_id', $locationId)
                    ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                    ->where('is_storage_point', 1)
                    ->get();
                
                $availablePoints = $fallbackStoragePoints->filter(function ($detail) {
                    return self::hasSpace($detail);
                });

                if ($availablePoints->isNotEmpty()) {
                    $data = self::successResponse('Fallback: Showing available storage points without mapping.', $availablePoints->map(function ($detail) {
                        $detail->parents = implode(' → ', self::getParentHierarchy($detail->parent_id));
                        return $detail;
                    })->values());
                    return $data;
                }
            }

            // Step 3: Parse structure_details
            $storagePointIds = [];

            foreach ($records as $record) {
                $structureDetails = json_decode($record->structure_details, true);
                if (!$structureDetails) continue;

                // Get the last level-values
                $lastLevel = end($structureDetails);
                $lastLevelValues = $lastLevel['level-values'] ?? [];

                // Get last-level storage points if defined
                if (!empty($lastLevelValues)) {
                    $details = \DB::table('erp_wh_details')
                    ->whereIn('id', $lastLevelValues)
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
                            $detail = \DB::table('erp_wh_details')->find($val);
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
        ->map(function ($detail) {
            $parents = self::getParentHierarchy($detail->parent_id);
            $detail->parents = implode(' → ', $parents);
            return $detail;
        })
        ->values(); // reset index
    }

    // Get Final Storage Points
    private static function getFinalStoragePoints(array $initialIds)
    {
        $finalIds = [];

        foreach ($initialIds as $id) {
            $detail = \DB::table('erp_wh_details')->where('id', $id)->first();

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
    // private static function findChildStoragePoints($parentId)
    // {
    //     $results = [];
    //     $children = \DB::table('erp_wh_details')
    //         ->where('parent_id', $parentId)
    //         ->get();

    //     foreach ($children as $child) {
    //         $hasSpace = (
    //             (is_null($child->max_weight) || is_null($child->current_weight) || $child->current_weight < $child->max_weight)
    //             ||
    //             (is_null($child->max_volume) || is_null($child->current_volume) || $child->current_volume < $child->max_volume)
    //         );

    //         if ($child->is_storage_point == 1 && $hasSpace) {
    //             $results[] = $child->id;
    //         } else {
    //             // Recursive call
    //             $results = array_merge($results, self::findChildStoragePoints($child->id));
    //         }
    //     }

    //     return $results;
    // }

    private static function findChildStoragePoints($parentId)
    {
        $results = [];

        $children = \DB::table('erp_wh_details')
            ->where('parent_id', $parentId)
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

    private static function hasSpace($detail)
    {
        return (
            (is_null($detail->max_weight) || is_null($detail->current_weight) || $detail->current_weight < $detail->max_weight)
            ||
            (is_null($detail->max_volume) || is_null($detail->current_volume) || $detail->current_volume < $detail->max_volume)
        );
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
            $mappings = \DB::table('erp_wh_item_mappings')
                ->where('store_id', $locationId)
                ->where(function ($q) use ($itemIds) {
                    foreach ($itemIds as $itemId) {
                        $q->orWhereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [(string)$itemId]);
                    }
                })
                ->when($subLocationId, fn($q) => $q->where('sub_store_id', $subLocationId))
                ->get();

            // Step 2: If not found, try subcategory/category fallback
            if ($mappings->isEmpty()) {
                $items = \DB::table('erp_items')->whereIn('id', $itemIds)->get();

                $subcategoryIds = $items->pluck('subcategory_id')->filter()->unique()->toArray();
                $categoryIds = $items->pluck('category_id')->filter()->unique()->toArray();

                if (!empty($subcategoryIds)) {
                    $mappings = \DB::table('erp_wh_item_mappings')
                        ->where(function ($q) use ($subcategoryIds) {
                            foreach ($subcategoryIds as $subId) {
                                $q->orWhereRaw("JSON_CONTAINS(sub_category_id, JSON_QUOTE(?))", [(string)$subId]);
                            }
                        })->get();
                }

                if ($mappings->isEmpty() && !empty($categoryIds)) {
                    $mappings = \DB::table('erp_wh_item_mappings')
                        ->where(function ($q) use ($categoryIds) {
                            foreach ($categoryIds as $catId) {
                                $q->orWhereRaw("JSON_CONTAINS(category_id, JSON_QUOTE(?))", [(string)$catId]);
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
            $storagePoint = \DB::table('erp_wh_details')->where('storage_number', $storageNumber)->first();

            if (!$storagePoint) {
                return self::errorResponse("Storage point not found.");
            }

            // Fetch parent hierarchy
            $parentHierarchy = self::getParentHierarchy($storagePoint->parent_id);
            $storagePoint->parents = implode(' → ', $parentHierarchy); // Optional formatting

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
            $storagePoint = \DB::table('erp_wh_details')
                ->where('id', $storagePointId)
                ->select('id','name','max_weight','max_volume','current_weight','current_volume','storage_number','parent_id')
                ->first();

            if (!$storagePoint) {
                return self::errorResponse("Storage point not found.");
            }

            // Fetch parent hierarchy
            $parentHierarchy = self::getParentHierarchy($storagePoint->parent_id);
            $storagePoint->parents = implode(' → ', $parentHierarchy);

            return self::successResponse("Storage point details fetched successfully.", $storagePoint);

        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage());
        }
    }


}
