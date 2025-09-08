<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\StockLedgerResource;
use App\Models\ErpAttribute;
use App\Models\ErpAttributeGroup;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\StockLedger;
use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;

class StockLookoutController extends Controller
{

    public function index(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => 'required|integer',
        ],[
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $itemId = $request->input('item_id');
        $storeId = $request->input('store_id');
        $subStoreId = $request->input('sub_store_id');
        $search = $request->search;

        $selectFields = [
            'id',
            'group_id',
            'company_id',
            'organization_id',
            'store_id',
            'sub_store_id',
            'item_id',
        ];

        // Conditionally include 'item_attributes'
        if ($request->is_attribute == 1) {
            $selectFields[] = 'item_attributes';
        }
        

        $query = StockLedger::with(['item' => function($q){
                $q->select('id','item_name','item_code','uom_id');
            }, 'location' =>  function($q){
                $q->select('id', 'store_name', 'store_code');
            },'item.uom' => function($q){
                $q->select('id','name');
            }])
            ->when($request->is_sub_store == 1, function($q) {
                $q->with(['store' => function($q){
                    $q->select('id','name');
                }]);
            })
            ->when($storeId, function($q) use($storeId){
                $q->where('store_id', $storeId);
            })
            ->when($subStoreId, function($q) use($subStoreId){
                $q->where('sub_store_id', $subStoreId);
            })
            ->when($itemId, function($query) use($itemId){
                $query->where('item_id', $itemId);
            })
            ->when($search, function($q) use($search){
                $q->whereHas('item', function($q) use ($search) {
                        $q->where('item_name', 'like', '%'.$search.'%')
                        ->orWhere('item_code','like', '%'.$search.'%');
                    });
            })
            ->when(!empty($request->input('attributes')), function($q) use ($request) {
                foreach ($request->input('attributes') as $attrName => $attrValue) {
                    $q->whereJsonContains('item_attributes', [
                        'attr_name' => (string) $attrName,
                        'attr_value' => (string) $attrValue
                    ]);
                }
            })
            ->withDefaultGroupCompanyOrg()
            ->whereNull('utilized_id')
            ->where('transaction_type', 'receipt');

        $query->select($selectFields)
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN (receipt_qty - reserved_qty) ELSE 0 END) as confirmed_stock',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status NOT IN (?, ?, ?) THEN receipt_qty ELSE 0 END) as unconfirmed_stock',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN putaway_pending_qty ELSE 0 END) as putaway_pending_qty',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN reserved_qty ELSE 0 END) as reserved_qty',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN org_currency_cost ELSE 0 END) as confirmed_stock_value',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status NOT IN (?, ?, ?) THEN org_currency_cost ELSE 0 END) as unconfirmed_stock_value',
                ['approved', 'approval_not_required', 'posted']
            );

        if ($storeId) { 
            $query->groupBy(['store_id']); 
        }

        if ($request->is_sub_store == 1) { 
            $query->groupBy(['sub_store_id']); 
        }

        if ($subStoreId) { 
            $query->groupBy(['sub_store_id']); 
        }

        if($request->is_attribute == 1) {
            $query->groupBy('item_attributes');
        }

        $query->groupBy(['item_id']); 

        $inventory_reports = $query->paginate(50);
        return [
            "data" => [
                'records' =>  StockLedgerResource::collection($inventory_reports),
                'pagination' => [
                    'current_page' => $inventory_reports->currentPage(),
                    'last_page' => $inventory_reports->lastPage(),
                    'per_page' => $inventory_reports->perPage(),
                    'total' => $inventory_reports->total(),
                    'from' => $inventory_reports->firstItem(),
                    'to' => $inventory_reports->lastItem(),
                ],
            ],
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

    public function applyFilter(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => 'required|integer',
        ],[
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $subStoreId = $request->sub_store_id ? ($request->sub_store_id == 0 ? NULL : $request->sub_store_id) : NULL;
        $filters = $request->input('warehouse', []);
        $levelKeys = array_keys($filters);
        $deepestLevelKey = end($levelKeys);  // gets the deepest level (e.g., "8")
        $lastLevelValues = $filters[$deepestLevelKey] ?? [];

        $storagePointIds = $this->getStoragePointIdsFromFilter([
            $deepestLevelKey => $lastLevelValues
        ]);

        $selectFields = [
            'store_id',
            'sub_store_id',
            'item_id',
            'item_name',
            'item_code',
            'storage_point_id',
            \DB::raw('COUNT(*) as total_quantity')
        ];

        // Conditionally include 'item_attributes'
        if ($request->is_attribute == 1) {
            $selectFields[] = 'item_attributes';
        }
        
        $items = ErpItemUniqueCode::query()
            ->with(['item.uom' => function($q){
                $q->select('id','name');
            },'item' => function($q){
                $q->select('id','item_name','item_code','uom_id');
            }])
            ->select($selectFields)
            ->when($request->is_sub_store == 1, function($q) {
                $q->with(['subStore' => function($q){
                    $q->select('id','name');
                }]);
            })
            ->when(!empty($storagePointIds), function($q) use ($storagePointIds) {
                $q->whereIn('storage_point_id', $storagePointIds);
            })
            ->when($request->store_id,function($q) use($request){
                $q->where('store_id',$request->store_id);
            })
            ->when($subStoreId,function($q) use($subStoreId){
                $q->where('sub_store_id',$subStoreId);
            })
            ->when(!empty($request->input('attributes')), function($q) use ($request) {
                foreach ($request->input('attributes') as $attrName => $attrValue) {
                    $q->whereJsonContains('item_attributes', [
                        'attr_name' => (string) $attrName,
                        'attr_value' => (string) $attrValue
                    ]);
                }
            })
            ->where('status', CommonHelper::SCANNED)
            ->groupBy('storage_point_id','item_id')
            ->get();

        $filteredArray = [];

        if($request->store_id){
            $store = ErpStore::select('id','store_name')->find($request->store_id);
            $filteredArray['store'] = $store ? $store->store_name : null;
            $filteredArray['store_id'] = $request->store_id;
        }

        if($request->sub_store_id){
            $subStore = ErpSubStore::select('id','name')->find($request->sub_store_id);
            $filteredArray['sub_store'] = $subStore ? $subStore->name : null;
            $filteredArray['sub_store_id'] = $request->sub_store_id;
        }

        if($filters){
            foreach($filters as $levelId => $storageIds){
                $level = DB::table('erp_wh_levels')->select('name')->where('id',$levelId)->first();

                if (!$level) {
                    continue; // skip if level not found
                }
                
                foreach ($storageIds as $id) {
                    // Check if this id is already a storage point
                    $whDetail = DB::table('erp_wh_details')
                                ->select('name')
                                ->where('id', $id)
                                ->first();

                    if ($whDetail) {
                        $filteredArray['warehouse'][] = [
                            'label_id' => $levelId,
                            'label' => $level->name,
                            'value_id' => $id,
                            'value' => $whDetail->name
                        ];
                    }
                }

            }
        }

        if($request->input('attributes')){
            foreach ($request->input('attributes') as $attrGroupId => $attrId) {
                $attributeGroup = ErpAttributeGroup::select('name')->where('id',$attrGroupId)->first();
                $attribute = ErpAttribute::select('value')->where('id',$attrId)->first();

                if ($attributeGroup && $attribute) {
                    $filteredArray['attributes'][] = [
                        'label_id' => $attrGroupId,
                        'label' => $attributeGroup->name,
                        'value_id' => $attrId,
                        'value' => $attribute->value,
                    ];
                }
            }
        }
        
        return [
            "data" => [
                'items' => $items,
                'filter_request' => $filteredArray
            ]
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