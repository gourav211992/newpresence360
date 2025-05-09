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

use App\Models\WhLevel;
use App\Models\WhDetail;
use App\Models\WhStructure;
use App\Models\WhItemMapping;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;

use Illuminate\Support\Facades\Log;


class InventoryHelperV2
{
    public function __construct()
    {
    
    }

    // Get Storage POints
    public static function getStoragePoints($itemId, $qty=NULL)
    {
        $user = Helper::getAuthenticatedUser();
        $data = array();
        try{
            // Step 1: Try to find mapping by item_id
            $records = \DB::table('erp_wh_item_mappings')
                ->whereRaw("JSON_CONTAINS(item_id, JSON_QUOTE(?))", [$itemId])
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
            ->get();
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
