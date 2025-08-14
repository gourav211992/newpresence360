<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\StockLedgerResource;
use App\Models\StockLedger;
use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;

class StockLookoutController extends Controller
{

    public function index(Request $request){
        $itemId = $request->query('item_id');
        $storeId = $request->query('store_id');
        $isAttribute = $request->query('is_attribute');
        $isSubStore = $request->query('is_sub_store');
        $subStoreId = $request->query('sub_store_id');
        $attrGroup = $request->query('attribute_name');
        $attrValue = $request->query('attribute_value');

        $query = StockLedger::with(['item' => function($q){
                $q->select('id','item_name','item_code');
            }, 'location' =>  function($q){
                $q->select('id', 'store_name', 'store_code');
            }, 'store' => function($q){
                $q->select('id','name','code');
            }])
            ->when($storeId, function($q) use($storeId){
                $q->where('store_id', $storeId)->groupBy('store_id');
            })
            ->when($subStoreId, function($q) use($subStoreId){
                $q->where('sub_store_id', $subStoreId)->groupBy('sub_store_id');
            })
            ->when($itemId, function($query) use($itemId){
                $query->whereHas('item', function($q) use ($itemId) {
                     $q->where('id', $itemId);
                });
            })
            ->withDefaultGroupCompanyOrg()
            ->whereNull('utilized_id')
            ->where('transaction_type', 'receipt');
        
        // Attribute filtering
        if (!empty($attrGroup) && !empty($attrValue)) {
            foreach ($attrGroup as $key => $group) {
                if (!empty($attrValue[$key])) {
                    $query->where(function ($subQuery) use ($group, $attrValue, $key) {
                        $subQuery->whereJsonContains('item_attributes', [
                            'attr_name' => $group,
                            'attr_value' => $attrValue[$key]
                        ]);
                    });
                }
            }
        }

        $query->select('id',
                'group_id',
                'company_id',
                'organization_id',
                'store_id',
                'sub_store_id',
                'item_id',
                'reserved_qty',
                'hold_qty',
                'item_attributes'
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN receipt_qty ELSE 0 END) as confirmed_stock',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status NOT IN (?, ?, ?) THEN receipt_qty ELSE 0 END) as unconfirmed_stock',
            ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN org_currency_cost ELSE 0 END) as confirmed_stock_value',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status NOT IN (?, ?, ?) THEN org_currency_cost ELSE 0 END) as unconfirmed_stock_value',
            ['approved', 'approval_not_required', 'posted']
        );

        // Attributes Check
        $query->groupBy('item_id');

        $inventory_reports = $query->get();
        return [
            'data' => StockLedgerResource::collection($inventory_reports)
        ];

    }

    public function item(Request $request){
        $validator = Validator::make($request->all(),[
            'item_id' => ['required'],
            'store_id' => ['required'],
        ],[
            'item_id.required' => 'Item id is required',
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $subStoreId = $request->sub_store_id;
        $item = ErpItemUniqueCode::select('store_id','sub_store_id','item_id','item_name','item_code','item_attributes', \DB::raw('COUNT(*) as total_quantity'))
                ->where('store_id', $request->store_id)
                ->where('item_id', $request->item_id)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->where('status', CommonHelper::SCANNED)
                ->when($subStoreId, function($q) use($subStoreId){
                    $q->where('sub_store_id',$subStoreId);
                })
                ->whereNotNull('storage_point_id')
                ->whereNull('utilized_id')
                ->first();

        if($item){
            $storageData = ErpItemUniqueCode::select('storage_point_id', DB::raw('COUNT(*) as quantity'))
                ->where('item_id', $request->item_id)
                ->where('store_id', $request->store_id)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->when($subStoreId, function($q) use($subStoreId){
                    $q->where('sub_store_id',$subStoreId);
                })
                ->whereNull('utilized_id')
                ->whereNotNull('storage_point_id')
                ->groupBy('storage_point_id')
                ->get();

            $item->storage_points = $storageData->map(function ($record){
                $detailsResponse = StoragePointHelper::getStoragePointDetailById($record->storage_point_id);

                return [
                    'quantity' => $record->quantity,
                    'details' => $detailsResponse['data'] ?? null,
                ];
            });
        }
        
        return [
            "data" => $item
        ];

    }

    public function getFilteredItems(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => 'required|integer',
            'sub_store_id' => 'required|integer',
            'filter' => 'required|array',
        ],[
            'sub_store_id.required' => 'Sub store id is required',
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $filters = $request->input('filter', []);
        $levelKeys = array_keys($filters);
        $deepestLevelKey = end($levelKeys);  // gets the deepest level (e.g., "8")
        $lastLevelValues = $filters[$deepestLevelKey] ?? [];

        $storagePointIds = $this->getStoragePointIdsFromFilter([
            $deepestLevelKey => $lastLevelValues
        ]);
        
        $items = ErpItemUniqueCode::select('store_id','sub_store_id','item_id','item_name','item_code','item_attributes','storage_point_id', \DB::raw('COUNT(*) as total_quantity'))
            ->whereIn('storage_point_id', $storagePointIds)
            // ->where('store_id', $request->store_id)
            // ->where('sub_store_id', $request->sub_store_id)
            ->where('status', CommonHelper::SCANNED)
            ->groupBy('storage_point_id','item_id')
            ->get();
        
        return [
            "data" => $items
        ];

    }

    private function getStoragePointIdsFromFilter($filter)
    {
        $finalStoragePointIds = [];
        foreach ($filter as $levelId => $storageIds) {
            foreach ($storageIds as $id) {
                // Check if this id is already a storage point
                $whDetail = DB::table('erp_wh_details')
                            ->where('id', $id)
                            ->first();

                if (!$whDetail) continue;

                if ($whDetail->is_storage_point == 1) {
                    $finalStoragePointIds[] = $id;
                } else {
                    // Get all descendants which are storage points
                    $descendants = self::getAllStoragePoints($id);
                    $finalStoragePointIds = array_merge($finalStoragePointIds, $descendants);
                }
            }
        }

        $finalStoragePointIds = array_unique($finalStoragePointIds);
        return  $finalStoragePointIds;
    }

    private function getAllStoragePoints($parentId)
    {
        $storagePoints = [];

        $children = DB::table('erp_wh_details')
            ->where('parent_id', $parentId)
            ->where('status', 'active')
            ->get();

        foreach ($children as $child) {
            if ($child->is_storage_point == 1) {
                $storagePoints[] = $child->id;
            } else {
                // Recursively fetch from child
                $storagePoints = array_merge($storagePoints, $this->getAllStoragePoints($child->id));
            }
        }

        return $storagePoints;
    }

}