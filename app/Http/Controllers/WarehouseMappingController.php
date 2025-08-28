<?php

namespace App\Http\Controllers;

use Str;
use App\Helpers\Helper;
use App\Models\WhLevel;
use App\Models\ErpStore;
use App\Models\WhDetail;
use App\Models\ErpSubStore;
use App\Models\WhStructure;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Models\ErpSubStoreParent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\WhMappingRequest;
use Yajra\DataTables\Facades\DataTables;

class WarehouseMappingController extends Controller
{

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        if ($request->ajax()) {
            $records = WhDetail::whereHas('store')->with('whLevel')
                ->groupBy('sub_store_id', 'wh_level_id');

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
                        } else {
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
                    $editMultipleUrl = route('warehouse-multiple-mapping.edit', $pr->store_id) . '?sub_store=' . $pr->sub_store_id . '&wh_level=' . $pr->wh_level_id;
                    $editUrl = route('warehouse-mapping.edit', $pr->store_id) . '?sub_store=' . $pr->sub_store_id . '&wh_level=' . $pr->wh_level_id;
                    // $deleteUrl = route('warehouse-mapping.delete', $pr->id);
                    return '<div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="' . $editUrl . '">
                                        <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="dropdown-item" href="' . $editMultipleUrl . '">
                                        <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit Multiple</span>
                                    </a>
                                </div>
                            </div>';
                })
                ->rawColumns(['names', 'status', 'action'])
                ->make(true);
        }

        return view('procurement.warehouse-structure.mapping.index');
    }

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::withDefaultGroupCompanyOrg()
            ->get();
        return view('procurement.warehouse-structure.mapping.create', [
            'user' => $user,
            'status' => $status,
            'stores' => $stores,
        ]);
    }

    public function store(WhMappingRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $whLevel = WhLevel::find($request->all()['level_id']);
            // dd($request->details);
            // Detail Save
            if (isset($request->details)) {
                $previousLevel = null;
                foreach ($request->details as $l_key => $detail) {
                    $storagePoint = (isset($detail['storage_point']) && ($detail['storage_point'] == 'on')) ? 1 : 0;

                    $whDetail = new WhDetail();
                    $whDetail->wh_level_id = $whLevel->id;
                    $whDetail->store_id = $whLevel->store_id;
                    $whDetail->sub_store_id = $whLevel->sub_store_id;
                    $whDetail->name = $detail['name'] ?? null;
                    $whDetail->is_storage_point = $storagePoint;
                    $whDetail->parent_id = $detail['parent_id'] ?? null;
                    $whDetail->is_first_level = $detail['is_first_level'] ?? null;
                    $whDetail->is_last_level = $detail['is_last_level'] ?? null;
                    $whDetail->max_weight = $detail['max_weight'] ?? null;
                    $whDetail->max_volume = $detail['max_volume'] ?? null;
                    $whDetail->status = 'active';
                    $whDetail->save();

                    if ($whDetail->is_storage_point == 1) {
                        $randomNumber = strtoupper(Str::random(rand(6, 8)));
                        $storageNumber = strtoupper(str_replace(' ', '-', $whDetail?->name)) . '-' . $randomNumber;
                        $whDetail->storage_number = $storageNumber;
                        $whDetail->save();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $whLevel
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

        $whDetails = WhDetail::whereHas('store')->with(['whLevel', 'parent'])->where('store_id', $id)
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

        if ($level->parent) {
            $parentDetails = self::getParentDetails($level?->parent);
        }

        return view('procurement.warehouse-structure.mapping.edit', [
            'level' => $level,
            'status' => $status,
            'whDetails' => $whDetails,
            'isLastLevel' => $isLastLevel,
            'parentDetails' => $parentDetails,
        ]);
    }

    public function update(WhMappingRequest $request, $id)
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
                    $whDetailId = $detail['detail_id'] ?? null;
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

                    $updatedDetailIds[] = $whDetail->id;
                    if (!$whDetail->storage_number && ($whDetail->is_storage_point == 1)) {
                        $randomNumber = strtoupper(Str::random(rand(6, 8)));
                        $storageNumber = strtoupper(str_replace(' ', '-', $whDetail?->name)) . '-' . $randomNumber;
                        $whDetail->storage_number = $storageNumber;
                        $whDetail->save();
                    }
                }

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

    // Get Store Levels
    public function getLevels(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $levels = WhLevel::where('sub_store_id', $request->store_id)
            ->get();

        return response()->json(['data' => $levels, 'status' => 200, 'message' => 'fetched.']);
    }

    // Get Store Level Parents
    public function getLevelParents(Request $request)
    {
        $isLastLevel = 0;
        $isFirstLevel = 1;
        $parentDetails = array();
        $parentHierarchy = array();
        $user = Helper::getAuthenticatedUser();
        $level = WhLevel::find($request->level_id);
        if ($level->parent) {
            $isFirstLevel = 0;
            $parentDetails = self::getParentDetails($level?->parent);
            $parentHierarchy = self::getParentNames($level?->parent);
        }
        $cheLastLevel = $level->children()->doesntExist();
        if ($cheLastLevel) {
            $isLastLevel = 1;
        }

        return response()->json(
            [
                'status' => 200,
                'message' => 'fetched.',
                'is_last_level' => $isLastLevel,
                'is_first_level' => $isFirstLevel,
                'parentDetails' => $parentDetails,
                'parentHierarchy' => $parentHierarchy,
            ]
        );
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

    // Get Store Based Levels
    public function getSubLevels(Request $request, String $subStoreId)
    {
        try {
            $user = Helper::getAuthenticatedUser();

            $term = $request->get('term');
            $levels = WhLevel::where('sub_store_id', $subStoreId)->select('id AS value', 'name AS label')
                ->when($term, function ($query, $term) {
                    return $query->where('name', 'LIKE', "%$term%");
                })
                ->get();
            return response()->json([
                'data' => array(
                    'levels' => $levels
                )
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ]);
        }
    }

    // Get Store Parent Parents
    public function getParents(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $level = WhDetail::find($request->parent_id);
        $parentHierarchy = self::getParentNames($level);
        // dd($parentHierarchy);

        return response()->json(['data' => $parentHierarchy, 'status' => 200, 'message' => 'fetched.']);
    }

    private static function getAncestors($level)
    {
        $ancestors = collect();
        while ($level) {
            $ancestors->push($level);
            $level = $level->parent;
        }
        // Ensure details are fetched correctly for each ancestor
        $ancestors = $ancestors->map(function ($ancestor) {
            $parentDetails = WhDetail::where('wh_level_id', $ancestor['id'])
                ->where('is_storage_point', 0)
                ->get();
            return [
                'id' => $ancestor['id'],
                'name' => $ancestor['name'],
                'level' => $ancestor['level'],
                'parent_id' => $ancestor['parent_id'],
                'parentDetails' => $parentDetails, // Correctly map the details
            ];
        });

        return $ancestors;
    }

    private static function getParentDetails($parent)
    {
        $parentDetails = WhDetail::where('wh_level_id', $parent->id)
            ->where('is_storage_point', 0)
            ->get();

        return $parentDetails;
    }

    private static function getParentNames($level)
    {
        $colors = [
            'badge-light-primary',
            'badge-light-success',
            'badge-light-warning',
            'badge-light-danger',
            'badge-light-info',
            'badge-light-dark',
        ];

        $badges = '';
        $parent = WhDetail::where('id', $level->parent_id)
            ->where('is_storage_point', 0)
            ->first();

        $index = 0;

        while ($parent) {
            $colorClass = $colors[$index % count($colors)]; // Cycle through colors
            $badges .= '<span class="badge rounded-pill ' . $colorClass . ' badgeborder-radius" style="margin-right: 5px;">'
                . $parent->name .
                '</span>';

            $parent = $parent->parent;
            $index++;
        }

        return $badges;
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

        // // Check if any children remain
        // $remaining = WhDetail::where('wh_level_id', $levelId)->count();

        // if ($remaining === 0) {
        //     // Delete the parent record
        //     WhDetail::where('id', $parentId)->delete(); // Replace with actual parent model
        //     return response()->json(['status' => 'success', 'redirect' => route('warehouse-structure.mapping.index')]);
        // }

        return response()->json(['status' => 'success']);
    }

    # WM Print Labels
    public function getBarcodes(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;

        $level = WhLevel::where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('id', $request->wh_level)
            ->first();

        $whDetails = WhDetail::whereHas('store')->with(
            [
                'whLevel', 
                'parent', 
                'store', 
                'sub_store'
            ]
        )
        ->where('store_id', $id)
        ->where('sub_store_id', $request->sub_store)
        ->where('wh_level_id', $request->wh_level)
        ->where('is_storage_point', 1)
        ->get();

        return view('procurement.warehouse-structure.mapping.get-barcodes', [
            'level' => $level,
            'status' => $status,
            'whDetails' => $whDetails,
        ]);
    }

    # WM Print Labels
    public function printBarcodes(Request $request)
    {
        
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;

        $whDetails = WhDetail::whereHas('store')
        ->with(
            [
                'whLevel', 
                'parent', 
                'store', 
                'sub_store'
            ]
        )
        ->where('sub_store_id', $request->sub_store)
        ->where('wh_level_id', operator: $request->wh_level)
        ->whereIn('id', (array) $request->ids ?? [])
        ->get();

        $html = view('procurement.warehouse-structure.mapping.print-barcodes', compact('whDetails'))->render();

        return response()->json([
            'status' => 200,
            'html' => $html
        ]);
    }
}
