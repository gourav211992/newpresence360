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

class WarehouseItemMappingController extends Controller
{

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();


        $records  = WhItemMapping::get()->toArray();
        $stores = ErpStore::withDefaultGroupCompanyOrg()
            ->get();
        $status = ConstantHelper::STATUS;

        if ($request->ajax()) {
            $records = WhDetail::with('whLevel')
                ->groupBy('sub_store_id', 'wh_level_id');

            // Log the query for debugging
            DB::enableQueryLog();
            $records = $records->get();
            Log::info('Query Log:', DB::getQueryLog());
            DB::disableQueryLog();

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('category', function ($row) {
                    $catIds = json_decode($row->category_id, true) ?? [];
                    $categories = Category::whereIn('id', $catIds)
                        ->whereHas('itemSub', function ($query) {
                            $query->where('status', ConstantHelper::ACTIVE);
                        })
                        ->pluck('name')
                        ->toArray();
                    return implode(', ', $categories);
                })
                ->addColumn('subcategory', function ($row) {
                    $subCatIds = json_decode($row->sub_category_id, true) ?? [];
                    $subCategories = Category::whereIn('id', $subCatIds)
                        ->whereNotNull('parent_id')
                        ->pluck('name')
                        ->toArray();
                    return implode(', ', $subCategories);
                })
                ->addColumn('item', function ($row) {
                    $itemIds = json_decode($row->item_id, true) ?? [];
                    $items = Item::whereIn('id', $itemIds)
                        ->pluck('name')
                        ->toArray();
                    return implode(', ', $items);
                })
                ->addColumn('structure_details', function ($row) {
                    $structure = json_decode(json_encode($row->structure_details), true) ?? [];

                    $structureNames = [];

                    foreach ($structure as $key => $ids) {
                        if (!empty($ids)) {
                            $names = WhDetail::whereIn('id', $ids)
                                ->pluck('name')
                                ->toArray();

                            $structureNames[] = ucfirst($key) . ': ' . implode(', ', $names);
                        }
                    }

                    return implode(' | ', $structureNames);
                })
                ->rawColumns(['category', 'subcategory', 'item', 'structure_details'])
                ->make(true);
        }

        return view('procurement.warehouse-items.index', [
            'user' => $user,
            'status' => $status,
            'stores' => $stores,
        ]);
    }

    public function store(WhItemMappingRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $whStructure = WhStructure::withDefaultGroupCompanyOrg()
                ->where('store_id', $request->store_id)
                ->where('sub_store_id', $request->sub_store_id)
                ->first();

            $existingIds = [];

            if ($request->has('details')) {
                // Get structure levels in order
                $levelNames = WhLevel::where('store_id', $request->store_id)
                    ->where('sub_store_id', $request->sub_store_id)
                    ->orderBy('level') // or appropriate sequence column
                    ->get(['id', 'name'])
                    ->mapWithKeys(function ($level) {
                        $slug = \Str::slug($level->name, '_');
                        return [$slug => ['id' => $level->id, 'name' => $level->name]];
                    })
                    ->toArray();

                foreach ($request->details as $detail) {
                    // Build structure details per level
                    $structureDetails = [];

                    foreach ($levelNames as $slug => $meta) {
                        if (!empty($detail[$slug])) {
                            $structureDetails[] = [
                                'level-id'     => $meta['id'],
                                'level-name'   => $meta['name'],
                                'level-values' => $detail[$slug],
                            ];
                        }
                    }

                    // Check if detail_id is provided → update, else create
                    if (!empty($detail['detail_id'])) {
                        $mapping = WhItemMapping::find($detail['detail_id']);

                        if ($mapping) {
                            $mapping->update([
                                'store_id'         => $request->store_id,
                                'sub_store_id'     => $request->sub_store_id,
                                'status'           => $request->status ?? 'active',
                                'category_id'      => $detail['category_id'] ?? [],
                                'sub_category_id'  => $detail['sub_category_id'] ?? [],
                                'item_id'          => $detail['item_id'] ?? [],
                                'structure_details' => $structureDetails,
                            ]);

                            $existingIds[] = $mapping->id;
                        }
                    } else {
                        $new = WhItemMapping::create([
                            'store_id'         => $request->store_id,
                            'sub_store_id'     => $request->sub_store_id,
                            'status'           => $request->status ?? 'active',
                            'category_id'      => $detail['category_id'] ?? [],
                            'sub_category_id'  => $detail['sub_category_id'] ?? [],
                            'item_id'          => $detail['item_id'] ?? [],
                            'structure_details' => $structureDetails,
                        ]);

                        $existingIds[] = $new->id;
                    }
                }

                // Optionally remove deleted records
                WhItemMapping::where('store_id', $request->store_id)
                    ->where('sub_store_id', $request->sub_store_id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            }

            // if ($request->has('details')) {
            //     // Get ordered structure levels (zone, bay, rack, etc.)
            //     $levelNames = WhLevel::where('store_id', $request->store_id)
            //         ->where('sub_store_id', $request->sub_store_id)
            //         ->orderBy('level') // or your actual sort column
            //         ->get(['id', 'name'])
            //         ->mapWithKeys(function ($level) {
            //             $slug = \Str::slug($level->name, '_'); // e.g., "Zone" => "zone"
            //             return [$slug => ['id' => $level->id, 'name' => $level->name]];
            //         })
            //         ->toArray();

            //     foreach ($request->details as $detail) {
            //         // Build structure_details associative array in the correct order
            //         $structureDetails = [];

            //         foreach ($levelNames as $slug => $meta) {
            //             if (!empty($detail[$slug])) {
            //                 $structureDetails[] = [
            //                     'level-id'     => $meta['id'],
            //                     'level-name'   => $meta['name'],
            //                     'level-values' => $detail[$slug],
            //                 ];
            //             }
            //         }

            //         // Create record
            //         WhItemMapping::create([
            //             'wh_structure_id'  => $whStructure?->id,
            //             'store_id'         => $request->store_id,
            //             'sub_store_id'     => $request->sub_store_id,
            //             'status'           => $request->status ?? 'active',
            //             'category_id'      => $detail['category_id'] ?? [],
            //             'sub_category_id'  => $detail['sub_category_id'] ?? [],
            //             'item_id'          => $detail['item_id'] ?? [],
            //             'structure_details'=> $structureDetails,
            //         ]);
            //     }
            // }

            DB::commit();
            return response()->json([
                'message' => 'Mappings saved successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error while saving mappings',
                'error'   => $e->getMessage(),
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
        try {
            $user = Helper::getAuthenticatedUser();

            $term = $request->get('term');
            $stores = ErpSubStore::select('id AS value', 'name AS label')
                ->when($term, function ($query, $term) {
                    return $query->where('name', 'LIKE', "%$term%");
                })
                ->whereHas('parents', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })
                ->where('status', 'active')
                ->get();


            return response()->json([
                'data' => array(
                    'stores' => $stores
                )
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
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
            ->whereHas('itemSub', function ($query) {
                $query->where('status', ConstantHelper::ACTIVE);
            })
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
        $items = Item::whereIn('subcategory_id', $parentIds)
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
        $items = Item::whereIn('subcategory_id', $subCategoryIds)
            ->select('id', 'item_code')
            ->orderBy('item_code')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'fetched.',
            'items' => $items,
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

        // Delete the children
        WhItemMapping::whereIn('id', $ids)->delete();

        return response()->json(['status' => 'success']);
    }

    // Mapping records based on store and sub store
    public function getMappingData(Request $request)
    {
        $storeId = $request->store_id;
        $subStoreId = $request->sub_store_id;
        $isExist = 0;

        $mappings = WhItemMapping::where('store_id', $storeId)
            ->where('sub_store_id', $subStoreId)
            ->get();

        if (!$mappings->isEmpty()) {
            $isExist = 1;
        }

        $allCategories = Category::whereHas('itemSub', function ($query) {
            $query->where('status', ConstantHelper::ACTIVE);
        })->get(); // Main categories
        $allSubCategories = Category::whereNotNull('parent_id')->get(); // All subcategories
        $allItems = Item::select('id', 'item_code as name', 'category_id', 'subcategory_id')->get();
        
        $mappingsData = [];

        foreach ($mappings as $mapping) {
            $categoryIds = $mapping->category_id ?? [];
            $itemIds = $mapping->item_id ?? [];
            $structureDetails = $mapping->structure_details ?? [];

            // Mark all categories, and selected
            $categories = $allCategories->map(function ($cat) use ($categoryIds) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'selected' => in_array($cat->id, $categoryIds)
                ];
            });

            // Filter items by selected category + subcategory
            $items = $allItems
                ->filter(function ($item) use ($categoryIds) {
                    return in_array($item->subcategory_id, $categoryIds);
                })
                ->map(function ($item) use ($itemIds) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'selected' => in_array($item->id, $itemIds)
                    ];
                })->values();

            // Process structures
            $structures = [];
            foreach ($structureDetails as $struct) {
                $levelName = $struct['level-name'];
                $levelIds = $struct['level-values'] ?? [];

                $allOptions = WhDetail::where('store_id', $storeId)
                    ->where('sub_store_id', $subStoreId)
                    ->whereHas('whLevel', function ($q) use ($levelName) {
                        $q->whereRaw('LOWER(name) = ?', [strtolower($levelName)]);
                    })
                    ->get()
                    ->map(function ($detail) use ($levelIds) {
                        return [
                            'id' => $detail->id,
                            'name' => $detail->name,
                            'selected' => in_array($detail->id, $levelIds)
                        ];
                    });

                $structures[] = [
                    'name' => $levelName,
                    'options' => $allOptions
                ];
            }

            $mappingsData[] = [
                'detail_id' => $mapping->id,
                'categories' => $categories,
                'items' => $items,
                'structures' => $structures
            ];

        }
        
        return response()->json([
            'status' => 200,
            'is_exist' => $isExist,
            'mappings' => $mappingsData
        ]);
    }
}
