<?php

namespace App\Http\Controllers;

use Str;
use App\Helpers\Helper;
use App\Models\WhLevel;
use App\Models\ErpStore;
use App\Models\WhDetail;
use App\Models\ErpSubStore;
use App\Models\WhStructure;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use Illuminate\Validation\Rule;
use App\Models\ErpSubStoreParent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\WhMappingRequest;
use App\Models\UserOrganizationMapping;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class WarehouseMappingController extends Controller
{

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        if ($request->ajax()) {
            $records = WhDetail::with('whLevel')
                ->groupBy('sub_store_id', 'wh_level_id');

            $records = $records->get();
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
                    return '<span class="badge rounded-pill badge-light-' . ($pr->status == ConstantHelper::ACTIVE ? 'success' : 'danger') . '">'
                        . ucfirst($pr->status) . '</span>';
                })
                ->addColumn('action', function ($pr) {
                    $editUrl = route('warehouse-mapping.edit', $pr->store_id) . '?sub_store=' . $pr->sub_store_id . '&wh_level=' . $pr->wh_level_id;
                    $deleteUrl = route('warehouse-mapping.delete', $pr->id);
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
        $baseRules = [
            'level_id' => 'required|exists:erp_wh_levels,id',
            'details' => 'required|array|min:1',
            'details.*.name' => 'required|string|max:255',
            'details.*.parent_id' => 'nullable|array',
            'details.*.parent_id.*' => 'nullable|exists:erp_wh_details,id',
            'details.*.is_first_level' => 'nullable|boolean',
            'details.*.is_last_level' => 'nullable|boolean',
            'details.*.max_weight' => 'nullable|numeric|min:0',
            'details.*.max_volume' => 'nullable|numeric|min:0',
        ];

        $whLevel = WhLevel::find($request->level_id);
        $validator = Validator::make($request->all(), $baseRules);

        $validator->after(function ($validator) use ($request, $whLevel) {
            if (!$whLevel || !$request->has('details')) return;
            foreach ($request->input('details') as $index => $detail) {
                $parentIds = $detail['parent_id'] ?? [null];

                foreach ($parentIds as $parentId) {
                    $parentName = optional(WhDetail::find($parentId))->name;
                    $heirarchyName = $detail['name'] . '-' . ($parentName ?? '');

                    $duplicateExists = WhDetail::where('wh_level_id', $whLevel->id)
                        ->where('store_id', $whLevel->store_id)
                        ->where('sub_store_id', $whLevel->sub_store_id)
                        ->where('heirarchy_name', $heirarchyName)
                        ->exists();

                    if ($duplicateExists) {
                        $validator->errors()->add("details.$index.name", "Duplicate hierarchy name: '{$heirarchyName}' already exists.");
                    }
                }
            }
        });

        $validator->validate();
        DB::beginTransaction();

        try {
            foreach ($request->input('details') as $detail) {
                $parentIds = $detail['parent_id'] ?? [null];

                foreach ($parentIds as $parentId) {
                    $parentName = optional(WhDetail::find($parentId))->name;
                    $heirarchyName = $detail['name'] . '-' . ($parentName ?? '');

                    $whDetail = new WhDetail();
                    $whDetail->wh_level_id = $whLevel->id;
                    $whDetail->store_id = $whLevel->store_id;
                    $whDetail->sub_store_id = $whLevel->sub_store_id;
                    $whDetail->name = $detail['name'];
                    $whDetail->heirarchy_name = $heirarchyName;
                    $whDetail->is_storage_point = isset($detail['storage_point']) && $detail['storage_point'] === 'on' ? 1 : 0;
                    $whDetail->parent_id = $parentId;
                    $whDetail->is_first_level = $detail['is_first_level'] ?? 0;
                    $whDetail->is_last_level = $detail['is_last_level'] ?? 0;
                    $whDetail->max_weight = $detail['max_weight'] ?? null;
                    $whDetail->max_volume = $detail['max_volume'] ?? null;
                    $whDetail->status = ConstantHelper::ACTIVE;
                    $whDetail->save();

                    if ($whDetail->is_storage_point) {
                        $randomSuffix = strtoupper(Str::random(rand(6, 8)));
                        $prefix = strtoupper(str_replace(' ', '-', $whDetail->name));
                        $whDetail->storage_number = "{$prefix}-{$randomSuffix}";
                        $whDetail->save();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Warehouse mapping saved successfully.',
                'level' => $whLevel->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving the warehouse mapping.',
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

        $whDetails = WhDetail::with(['whLevel', 'parent'])
            ->where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('wh_level_id', $request->wh_level)
            ->get()
            ->groupBy(function ($item) {
                return $item->name . '|' . $item->is_storage_point;
            });

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
        $level = WhLevel::where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('id', $request->wh_level)
            ->first();

        DB::beginTransaction();
        try {
            $existingDetailIds = WhDetail::where('store_id', $id)
                ->where('sub_store_id', $request->sub_store)
                ->where('wh_level_id', $request->wh_level)
                ->pluck('id')
                ->toArray();

            $updatedDetailIds = [];
            foreach ($request->input('details') as $detail) {
                $parentIds = $detail['parent_id'] ?? [null];
                foreach ($parentIds as $parentId) {
                    $matchedDetail = WhDetail::where('store_id', $id)
                        ->where('sub_store_id', $request->sub_store)
                        ->where('wh_level_id', $request->wh_level)
                        ->where('name', $detail['name'])
                        ->where('parent_id', $parentId)
                        ->first();

                    $whDetail = $matchedDetail ?? new WhDetail();

                    $whDetail->wh_level_id = $level->id;
                    $whDetail->store_id = $id;
                    $whDetail->sub_store_id = $request->sub_store;
                    $whDetail->name = $detail['name'];
                    $whDetail->parent_id = $parentId;
                    $whDetail->heirarchy_name = $detail['name'] . '-' . optional(WhDetail::find($parentId))->name;
                    $whDetail->is_storage_point = !empty($detail['storage_point']) ? 1 : 0;
                    $whDetail->is_first_level = $detail['is_first_level'] ?? 0;
                    $whDetail->is_last_level = $detail['is_last_level'] ?? 0;
                    $whDetail->max_weight = $detail['max_weight'] ?? null;
                    $whDetail->max_volume = $detail['max_volume'] ?? null;
                    $whDetail->status = ConstantHelper::ACTIVE;
                    $whDetail->save();

                    if (!$whDetail->storage_number && $whDetail->is_storage_point) {
                        $suffix = strtoupper(Str::random(rand(6, 8)));
                        $prefix = strtoupper(str_replace(' ', '-', $whDetail->name));
                        $whDetail->storage_number = "{$prefix}-{$suffix}";
                        $whDetail->save();
                    }

                    $updatedDetailIds[] = $whDetail->id;
                }
            }

            $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
            WhDetail::whereIn('id', $detailsToDelete)->forceDelete();

            DB::commit();
            return response()->json([
                'message' => 'Warehouse mapping updated successfully.',
                'data' => $level,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while updating the record.',
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
                $query->where('status', ConstantHelper::ACTIVE);
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
                ->where('status', ConstantHelper::ACTIVE)
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

        $whDetails = WhDetail::with(
            ['whLevel', 'parent', 'store', 'sub_store']
        )->where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('wh_level_id', $request->wh_level)
            ->get();

        return view('procurement.warehouse-structure.mapping.get-barcodes', [
            'level' => $level,
            'status' => $status,
            'whDetails' => $whDetails,
        ]);
    }

    # WM Print Labels
    public function printBarcodes(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;

        $whDetails = WhDetail::with(
            ['whLevel', 'parent', 'store', 'sub_store']
        )->where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('wh_level_id', $request->wh_level)
            ->get();

        $html = view('procurement.warehouse-structure.mapping.print-barcodes', compact('whDetails'))->render();

        return response()->json([
            'status' => 200,
            'html' => $html
        ]);
    }
}
