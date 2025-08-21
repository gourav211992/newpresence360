<?php

namespace App\Http\Controllers;

use Str;
use App\Helpers\Helper;
use App\Models\WhLevel;
use App\Models\ErpStore;
use App\Models\WhDetail;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\WhMappingRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\WhMultiMappingRequest;

class WarehouseMultiMappingController extends Controller
{

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::withDefaultGroupCompanyOrg()->get();

        return view('procurement.warehouse-structure.mapping.multiple.create', [
            'user' => $user,
            'status' => $status,
            'stores' => $stores,
        ]);
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
                return $item->name;
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

        return view('procurement.warehouse-structure.mapping.multiple.edit', [
            'level' => $level,
            'status' => $status,
            'whDetails' => $whDetails,
            'isLastLevel' => $isLastLevel,
            'parentDetails' => $parentDetails,
        ]);
    }

    public function store(WhMultiMappingRequest $request)
    {
        $whLevel = WhLevel::find($request->level_id);
        DB::beginTransaction();

        try {
            foreach ($request->input('details') as $detail) {
                $parentIds = $detail['parent_id'] ?? [null];

                foreach ($parentIds as $parentId) {
                    if ($parentId) {
                        $parentWh = WhDetail::find($parentId);

                        $parentWhDetails = WhDetail::where([
                            ['store_id', $whLevel->store_id],
                            ['sub_store_id', $whLevel->sub_store_id],
                            ['wh_level_id', $parentWh->wh_level_id ?? null],
                            ['name', $parentWh->name ?? null],
                            ['status', ConstantHelper::ACTIVE]
                        ])->get();

                        foreach ($parentWhDetails as $parent) {
                            $this->saveWhDetail($whLevel, $detail, $parent->id, $parent->heirarchy_name ?? null);
                        }
                    } else {
                        // No parent – save without parent linkage
                        $this->saveWhDetail($whLevel, $detail, null, null);
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

    public function update(WhMultiMappingRequest $request, $id)
    {
        $updatedDetailIds = [];
        $whLevel = WhLevel::where('store_id', $id)
            ->where('sub_store_id', $request->sub_store)
            ->where('id', $request->wh_level)
            ->first();

        $existingDetailIds = WhDetail::where([
            ['store_id', $id],
            ['sub_store_id', $request->sub_store],
            ['wh_level_id', $request->wh_level]
        ])->pluck('id')->toArray();

        DB::beginTransaction();
        try {
            foreach ($request->input('details') as $detail) {
                $parentIds = $detail['parent_id'] ?? [null];

                foreach ($parentIds as $parentId) {
                    if ($parentId) {
                        $parentWh = WhDetail::find($parentId);

                        $parentWhDetails = WhDetail::where([
                            ['store_id', $whLevel->store_id],
                            ['sub_store_id', $whLevel->sub_store_id],
                            ['wh_level_id', $parentWh->wh_level_id ?? null],
                            ['name', $parentWh->name ?? null],
                            ['status', ConstantHelper::ACTIVE]
                        ])->get();

                        foreach ($parentWhDetails as $parent) {
                            $whDetail = WhDetail::firstOrNew([
                                'store_id' => $id,
                                'sub_store_id' => $request->sub_store,
                                'wh_level_id' => $request->wh_level,
                                'name' => $detail['name'],
                                'parent_id' => $parent->id,
                            ]);

                            $this->fillWhDetail($whDetail, $whLevel, $detail, $parent->id, $parent->heirarchy_name ?? null);
                            $whDetail->save();

                            $updatedDetailIds[] = $whDetail->id;
                        }
                    } else {
                        // No parent case
                        $whDetail = WhDetail::firstOrNew([
                            'store_id' => $id,
                            'sub_store_id' => $request->sub_store,
                            'wh_level_id' => $request->wh_level,
                            'name' => $detail['name'],
                            'parent_id' => null,
                        ]);

                        $this->fillWhDetail($whDetail, $whLevel, $detail, null, null);
                        $whDetail->save();

                        $updatedDetailIds[] = $whDetail->id;
                    }
                }
            }

            // Delete removed details
            $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
            WhDetail::whereIn('id', $detailsToDelete)->forceDelete();

            DB::commit();
            return response()->json([
                'message' => 'Warehouse mapping updated successfully.',
                'data' => $whLevel,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while updating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function saveWhDetail($whLevel, $detail, $parentId = null, $parentHeirarchy = null)
    {
        $whDetail = new WhDetail();
        $this->fillWhDetail($whDetail, $whLevel, $detail, $parentId, $parentHeirarchy);
        $whDetail->save();

        if (!$whDetail->storage_number && ($whDetail->is_storage_point == 1)) {
            $prefix = strtoupper(str_replace(' ', '-', $whDetail->name));
            $suffix = strtoupper(Str::random(rand(6, 8)));
            $whDetail->storage_number = "{$prefix}-{$suffix}";
            $whDetail->save();
        }
    }

    private function fillWhDetail(&$whDetail, $whLevel, $detail, $parentId = null, $parentHeirarchy = null)
    {
        $heirarchyName = $parentHeirarchy && $detail['name'] ? "{$parentHeirarchy}-{$detail['name']}" : ($parentHeirarchy ?? $detail['name']);

        $whDetail->wh_level_id = $whLevel->id;
        $whDetail->store_id = $whLevel->store_id;
        $whDetail->sub_store_id = $whLevel->sub_store_id;
        $whDetail->name = $detail['name'];
        $whDetail->parent_id = $parentId;
        $whDetail->heirarchy_name = $heirarchyName;
        $whDetail->is_storage_point = !empty($detail['storage_point']) ? 1 : 0;
        $whDetail->is_first_level = $detail['is_first_level'] ?? 0;
        $whDetail->is_last_level = $detail['is_last_level'] ?? 0;
        $whDetail->max_weight = $detail['max_weight'] ?? null;
        $whDetail->max_volume = $detail['max_volume'] ?? null;
        $whDetail->status = ConstantHelper::ACTIVE;

        if (!$whDetail->storage_number && ($whDetail->is_storage_point == 1)) {
            $prefix = strtoupper(str_replace(' ', '-', $whDetail->name));
            $suffix = strtoupper(Str::random(rand(6, 8)));
            $whDetail->storage_number = "{$prefix}-{$suffix}";
        }
    }

    private function getParentDetails($parent)
    {
        $parentDetails = WhDetail::where('wh_level_id', $parent->id)
            ->where('is_storage_point', 0)
            ->groupBy('name')
            ->get();

        return $parentDetails;
    }

    public function getLevelParents(Request $request)
    {
        $isLastLevel = 0;
        $isFirstLevel = 1;
        $parentDetails = array();
        $parentHierarchy = array();
        $level = WhLevel::find($request->level_id);
        if ($level->parent) {
            $isFirstLevel = 0;
            $parentDetails = self::getParentDetails($level?->parent);
            $parentHierarchy = self::getParentHeirarchy($level?->parent);
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

    public function getLevels(Request $request)
    {
        $levels = WhLevel::where('sub_store_id', $request->store_id)
            ->get();

        return response()->json(['data' => $levels, 'status' => 200, 'message' => 'fetched.']);
    }

    private static function getParentHeirarchy($level)
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
}
