<?php
namespace App\Http\Controllers;

use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;


use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use App\Models\Organization;
use App\Models\UserOrganizationMapping;

use App\Models\WhLevel;
use App\Models\WhDetail;
use App\Models\WhStructure;
use App\Models\WhItemMapping;

use App\Models\Category;
use App\Models\Item;

use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\WhItemMappingRequest;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class WarehouseItemMapController extends Controller
{

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        if ($request->ajax()) {
            $records = WhItemMapping::with(
                [
                    'category',
                    'subCategory',
                    'item',
                    'whStructure',
                    'whDetail',
                    'whLevel',
                ]
            )
            ->groupBy('store_id', 'sub_store_id');
            
            // Log the query for debugging
            DB::enableQueryLog();
            $records = $records->get();
            Log::info('Query Log:', DB::getQueryLog());
            DB::disableQueryLog();
           
            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('store', function ($row) {
                    return $row->store ? $row->store?->store_name : 'N/A';
                })
                ->addColumn('sub_store', function ($row) {
                    return $row->sub_store ? $row->sub_store?->name : 'N/A';
                })
                ->addColumn('wh_level', function ($row) {
                    return $row->whLevel ? $row->whLevel?->name : 'N/A';
                })
                ->addColumn('names', function ($pr) {
                    if ($pr->level_names && !empty($pr->level_names)) {
                        if (is_string($pr->level_names)) {
                            return $pr->level_names; // Return the string directly
                        } elseif (is_iterable($pr->level_names)) {
                            $levelCount = count($pr->level_names);
                            $displayLevels = collect($pr->level_names)->map(function ($level) {
                                return '<span class="badge rounded-pill badge-light-secondary badgeborder-radius">' . $level . '</span>';
                            })->implode('');
                            return $displayLevels;
                        }else{
                            return '';
                        }
                    }
                    return '';
                })
                ->addColumn('status', function ($pr) {
                    return '<span class="badge rounded-pill badge-light-' . ($pr->status == 'active' ? 'success' : 'danger') . '">'
                        . ucfirst($pr->status) . '</span>';
                })
                ->addColumn('action', function ($pr) {
                    $editUrl = route('warehouse-items.edit', $pr->wh_structure_id);
                    $deleteUrl = route('warehouse-items.delete', $pr->wh_structure_id);
                    return '<div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . $editUrl . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>';
                })
                ->rawColumns(['names', 'status', 'action'])
                ->make(true);
        }

        return view('procurement.warehouse-items.index');
    }

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::withDefaultGroupCompanyOrg()
            ->get();
        return view('procurement.warehouse-items.create', [
            'user' => $user,
            'status' => $status,
            'stores' => $stores,
        ]);
    }

    public function store(WhItemMappingRequest $request)
    {
        // dd($request->all());
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            // Detail Save
            if ($request->has('details')) {
                // Get dynamic level keys
                $levelNames = WhLevel::orderByDesc('id') // Replace 'id' if another hierarchy column exists
                    ->pluck('name')
                    ->map(fn($name) => \Str::slug($name, '_')) // "Rack Name" => "rack_name"
                    ->toArray();
            
                foreach ($request->details as $detail) {
                    $lastLevelKey = null;
                    $lastLevelValues = [];
                    $lastLevelId = null;
            
                    // Find deepest level that exists
                    foreach ($levelNames as $key) {
                        if (!empty($detail[$key]) && is_array($detail[$key])) {
                            $lastLevelKey = $key;
                            $lastLevelValues = $detail[$key];
                    
                            // Get the wh_level_id for this key
                            $lastLevelId = WhLevel::whereRaw("LOWER(REPLACE(name, ' ', '_')) = ?", [$key])->value('id');
                            break;
                        }
                    }
            
                    if (empty($lastLevelValues)) {
                        continue; // No valid level data
                    }
            
                    // Handle insertion by item/sub-category/category
                    if (!empty($detail['item_id'])) {
                        foreach ($detail['item_id'] as $itemId) {
                            foreach ($lastLevelValues as $levelId) {
                                WhItemMapping::create([
                                    'store_id'        => $request->store_id,
                                    'sub_store_id'    => $request->sub_store_id,
                                    'item_id'         => $itemId,
                                    'wh_level_id'    => $lastLevelId,
                                    'wh_detail_id'    => $levelId,
                                    'status'          => 'active',
                                ]);
                            }
                        }
                    } elseif (!empty($detail['sub_category_id'])) {
                        foreach ($detail['sub_category_id'] as $subCatId) {
                            foreach ($lastLevelValues as $levelId) {
                                WhItemMapping::create([
                                    'store_id'         => $request->store_id,
                                    'sub_store_id'     => $request->sub_store_id,
                                    'sub_category_id'  => $subCatId,
                                    'wh_level_id'    => $lastLevelId,
                                    'wh_detail_id'     => $levelId,
                                    'status'           => 'active',
                                ]);
                            }
                        }
                    } elseif (!empty($detail['category_id'])) {
                        foreach ($detail['category_id'] as $catId) {
                            foreach ($lastLevelValues as $levelId) {
                                WhItemMapping::create([
                                    'store_id'      => $request->store_id,
                                    'sub_store_id'  => $request->sub_store_id,
                                    'category_id'   => $catId,
                                    'wh_level_id'    => $lastLevelId,
                                    'wh_detail_id'  => $levelId,
                                    'status'        => 'active',
                                ]);
                            }
                        }
                    }
                }
            }
            

            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => ''
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $parentDetails = array();
        $isLastLevel = 0;

        $whDetails = WhDetail::with(['whLevel', 'parent'])->where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('wh_level_id', $request->wh_level)
            ->get();
        $level = WhLevel::where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('id', $request->wh_level)
            ->first();
        
        $isLastLevel = $level->children()->doesntExist();

        if ($isLastLevel) {
            $isLastLevel = 1;
        }
        
        if($level->parent){
            $parentDetails = self::getParentDetails($level?->parent);
        }
        
        return view('procurement.warehouse-items.edit', [
            'level' => $level,
            'status' => $status,
            'whDetails' => $whDetails,
            'isLastLevel' => $isLastLevel,
            'parentDetails' => $parentDetails,
        ]);
    }

    public function update(WhItemMappingRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $level = WhLevel::where('store_id', $id)
                ->where('sub_store_id', $request->sub_store)
                ->where('id', $request->wh_level)
                ->first();
            // Detail Save
            if (isset($request->details)) {
                $previousLevel = null;
                $existingDetailIds = WhDetail::where('store_id', $id)
                    ->where('sub_store_id', $request->sub_store)
                    ->where('wh_level_id', $request->wh_level)
                    ->pluck('id')->toArray();
                $updatedDetailIds = [];
                foreach ($request->details as $l_key => $detail) {
                    $whDetailId = $detail->id ?? null;
                    $whDetail = WhDetail::find($whDetailId) ?? new WhDetail;
                    $storagePoint = (isset($detail['storage_point']) && ($detail['storage_point'] == 'on')) ? 1 : 0;
            
                    $whDetail->wh_level_id = $request->wh_level;
                    $whDetail->store_id = $id;
                    $whDetail->sub_store_id = $request->sub_store;
                    $whDetail->name = $detail['name'] ?? null;
                    $whDetail->is_storage_point = $storagePoint;
                    $whDetail->parent_id = $detail['parent_id'] ?? null;
                    $whDetail->is_first_level = $detail['is_first_level'] ?? null;
                    $whDetail->is_last_level = $detail['is_last_level'] ?? null;
                    $whDetail->max_weight = $detail['max_weight'] ?? null;
                    $whDetail->max_volume = $detail['max_volume'] ?? null;
                    $whDetail->status = 'active';
                    $whDetail->save();
                }

                 // Delete details that are no longer present in the request
                 $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
                 WhDetail::whereIn('id', $detailsToDelete)->forceDelete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $level
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete Store Mapping
    public function delete($id)
    {
        $pRoute = WhStructure::findOrFail($id);
        return redirect()->route("warehouse-structure.index")->with('success', 'Record deleted successfully.');
    }

    // Get Stores
    public function getSubStores(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $stores = ErpSubStoreParent::withDefaultGroupCompanyOrg()
            ->with(['sub_store' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get();

        return response()->json(['data' => $stores, 'status' => 200, 'message' => 'fetched.']);
    }

    // Get Sub Stores
    public function getStores(Request $request)
    {
        try{
            $user = Helper::getAuthenticatedUser();

            $term = $request->get('term'); 
            $stores = ErpSubStore::select('id AS value', 'name AS label')
            ->when($term, function($query, $term) {
                return $query->where('name', 'LIKE', "%$term%");  
            })
            ->whereHas('parents', function ($query) {
                $query->withDefaultGroupCompanyOrg();
            })
            ->where('status', 'active')
            ->get();


            return response() -> json([
                'data' => array(
                    'stores' => $stores
                )
            ]);
        } catch(\Exception $ex) {
            return response() -> json([
                'message' => $ex -> getMessage()
            ]);
        }
    }

    // Get Categories
    public function getDetails(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        
        $categories = array();
        $structures = array();
        
        $categories = Category::where('status', ConstantHelper::ACTIVE)
            ->withDefaultGroupCompanyOrg()
            ->whereNull('parent_id')
            ->get();
        
        $structures = WhLevel::with('storagePointDetails')
            ->where('store_id', $request->store_id)
            ->where('sub_store_id', $request->sub_store_id)
            ->get()->toArray();

        return response()->json(
            [
                'status' => 200, 
                'message' => 'fetched.',
                'categories' => $categories,
                'structures' => $structures,
            ]
        );
    }

    // Get Sub Categories
    public function getSubCategories(Request $request)
    {
        $parentIds = $request->input('parent_ids', []);
        if (empty($parentIds)) {
            return response()->json([
                'status' => 400,
                'message' => 'No valid parent IDs provided.',
            ]);
        }

        // Fetch subcategories that belong to any of the selected parent IDs
        $subCategories = Category::whereIn('parent_id', $parentIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Fetch items that belong to any of the selected parent IDs
        $items = Item::whereIn('category_id', $parentIds)
            ->select('id', 'item_code')
            ->orderBy('item_code')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'fetched.',
            'items' => $items,
            'data' => $subCategories,
        ]);
    }

    // Get Items
    public function getItems(Request $request)
    {
        $categoryIds = $request->input('category_ids', []);
        $subCategoryIds = $request->input('sub_category_ids', []);
        
        if (empty($categoryIds) && empty($subCategoryIds)) {
            return response()->json([
                'status' => 400,
                'message' => 'No valid IDs provided.',
            ]);
        }

        // Fetch items that belong to any of the selected parent IDs
        $items = Item::whereIn('category_id', $categoryIds)
            ->orWhereIn('subcategory_id', $subCategoryIds)
            ->select('id', 'item_code')
            ->orderBy('item_code')
            ->get();
        
        return response()->json([
            'status' => 200,
            'message' => 'fetched.',
            'items' => $items,
        ]);
    }

    // Get Structure Details
    public function getStructureDetails(Request $request)
    {
        $levelIds = $request->get('wh_level_ids', []);
        $details = WhDetail::whereIn('wh_level_id', $levelIds)->get(['id', 'name']);

        return response()->json([
            'status' => 200,
            'data' => $details
        ]);
    }


    // Get Structure Childs
    public function getChilds(Request $request)
    {
        $parentIds = $request->get('parent_ids', []);
        $parentDetails = WhDetail::whereIn('id', $parentIds)->get();

        $childData = collect();

        foreach ($parentDetails as $parentDetail) {
            $children = WhDetail::where('parent_id', $parentDetail->id)->get(['id', 'name']);
            $childData = $childData->merge($children);
        }

        return response()->json([
            'status' => 200,
            'data' => $childData->unique('id')->values()
        ]);
    }
    
    public function deleteDetails(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No IDs provided'], 400);
        }

        // Get the parent_id before deleting the children
        $level = WhDetail::whereIn('id', $ids)->first(); // Replace 'wh_id' with your actual parent key
        $levelId = $level->wh_level_id; 
        // Delete the children
        WhDetail::whereIn('id', $ids)->delete();

        return response()->json(['status' => 'success']);
    }

}
