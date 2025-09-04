<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErpRgrStoreMappingRequest;
use App\Models\ErpRgrStoreMapping;
use App\Helpers\InventoryHelper;
use App\Models\Category;
use App\Models\Item;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpRgrDamageMapping; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

class ErpRgrStoreMappingController extends Controller
{
  public function index(Request $request)
    {
        $storeMappings = ErpRgrStoreMapping::with(['category','store','subStore','qcSubStore'])
            ->orderBy('id', 'desc')
            ->get();

        $damageMappings = ErpRgrDamageMapping::with(['store','subStore'])
            ->orderBy('id', 'desc')
            ->get();

        $damageNatures = ConstantHelper::DAMAGE_TYPE;

        return view('rgr-store-mapping.index', [
            'storeMappings' => $storeMappings,
            'damageMappings' => $damageMappings,
            'damageNatures' => $damageNatures,
        ]);
    }

   public function store(ErpRgrStoreMappingRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;

            $parentUrl = ConstantHelper::STORE_MAPPING_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

            $defaultData = [];
            if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                $firstService = $services['services']->first();
                $serviceId = $firstService->service_id;
                $policyData = Helper::getPolicyByServiceId($serviceId);

                if ($policyData && isset($policyData['policyLevelData'])) {
                    $policyLevelData = $policyData['policyLevelData'];
                    $defaultData['group_id'] = $policyLevelData['group_id'];
                    $defaultData['company_id'] = $policyLevelData['company_id'];
                    $defaultData['organization_id'] = $policyLevelData['organization_id'];
                } else {
                    $defaultData['group_id'] = $organization->group_id;
                    $defaultData['company_id'] = $organization->company_id;
                    $defaultData['organization_id'] = null;
                }
            } else {
                $defaultData['group_id'] = $organization->group_id;
                $defaultData['company_id'] = $organization->company_id;
                $defaultData['organization_id'] = null;
            }

            if (!empty($validated['store_mappings'])) {
                foreach ($validated['store_mappings'] as $mapData) {
                    if (!empty($mapData['id'])) {
                        $existing = ErpRgrStoreMapping::find($mapData['id']);
                        if ($existing) {
                            $existing->update([
                                'category_id'      => $mapData['category_id'] ?? null,
                                'store_id'         => $mapData['store_id'] ?? null,
                                'sub_store_id'     => $mapData['sub_store_id'] ?? null,
                                'qc_sub_store_id'  => $mapData['qc_sub_store_id'] ?? null,
                                'updated_by'       => $user->id,
                                'group_id'         => $defaultData['group_id'],
                                'company_id'       => $defaultData['company_id'],
                                'organization_id'  => $defaultData['organization_id'],
                            ]);
                            continue; 
                        }
                    }

                    ErpRgrStoreMapping::create([
                        'category_id'      => $mapData['category_id'] ?? null,
                        'store_id'         => $mapData['store_id'] ?? null,
                        'sub_store_id'     => $mapData['sub_store_id'] ?? null,
                        'qc_sub_store_id'  => $mapData['qc_sub_store_id'] ?? null,
                        'created_by'       => $user->id,
                        'group_id'         => $defaultData['group_id'],
                        'company_id'       => $defaultData['company_id'],
                        'organization_id'  => $defaultData['organization_id'],
                    ]);
                }
            }

            if (!empty($validated['damage_mappings'])) {
                foreach ($validated['damage_mappings'] as $damageData) {

                    if (empty($damageData['store_id']) || empty($damageData['sub_store_id'])) {
                        continue;
                    }

                    if (!empty($damageData['id'])) {
                        $existing = ErpRgrDamageMapping::find($damageData['id']);
                        if ($existing) {
                            $existing->update([
                                'damage_type'     => $damageData['damage_type'] ?? null,
                                'store_id'        => $damageData['store_id'],
                                'sub_store_id'    => $damageData['sub_store_id'],
                                'group_id'        => $defaultData['group_id'],
                                'company_id'      => $defaultData['company_id'],
                                'organization_id' => $defaultData['organization_id'],
                            ]);
                        }
                    } else {
                        ErpRgrDamageMapping::create([
                            'damage_type'     => $damageData['damage_type'] ?? null,
                            'store_id'        => $damageData['store_id'],
                            'sub_store_id'    => $damageData['sub_store_id'],
                            'group_id'        => $defaultData['group_id'],
                            'company_id'      => $defaultData['company_id'],
                            'organization_id' => $defaultData['organization_id'],
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record processed successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
   // Category from Item (subcategory_id)
    public function categories(Request $request)
    {
        $term = $request->get('term');

        $query = Item::where('status', 'active')->with('subCategory');

        if (!empty($term)) {
            $query->whereHas('subCategory', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }

        $items = $query->get()->pluck('subCategory')->unique('id')->take(10);

        if ($items->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No category found.',
                'data' => []
            ]);
        }

        $data = $items->map(function ($cat) {
            return [
                'label' => $cat->name,
                'value' => $cat->id
            ];
        })->values();

        return response()->json(['status' => true, 'data' => $data]);
    }

    // Store from ErpStore (Location)
   public function stores(Request $request)
    {
        $term = $request->get('term');

        $accessibleStoreIds = InventoryHelper::getAccessibleLocations()
            ->where('status', 'active')
            ->pluck('id');

        $stores = ErpStore::whereIn('id', $accessibleStoreIds)
            ->where('status', 'active')
            ->when($term, function ($query, $term) {
                $query->where('store_name', 'LIKE', '%' . $term . '%');
            })
            ->take(10)
            ->get();

        if ($stores->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No Record found.',
                'data' => []
            ]);
        }

        $data = $stores->map(function ($store) {
            return [
                'label' => $store->store_name, 
                'value' => $store->id,      
            ];
        })->values();

        return response()->json(['status' => true, 'data' => $data]);
    }
    // SubStores (RGR & QC Store)
    public function substores(Request $request)
    {
        $term = $request->get('term');
        $storeId = $request->get('store_id');

        if (empty($storeId)) {
            return response()->json([
                'status' => false,
                'message' => 'Store ID is required.',
                'data' => []
            ]);
        }

        $query = ErpSubStore::query()->where('status', 'active');

        $query->whereHas('parents', function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        });

        if (!empty($term)) {
            $query->where('name', 'like', "%{$term}%");
        }

        $subStores = $query->limit(10)->get();

        if ($subStores->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No sub-store found.',
                'data' => []
            ]);
        }

        $data = $subStores->map(function ($sub) {
            return [
                'label' => $sub->name,
                'value' => $sub->id
            ];
        })->values();

        return response()->json(['status' => true, 'data' => $data]);
   }

   public function destroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No IDs provided.'
                ], 400);
            }

            $mappings = ErpRgrStoreMapping::whereIn('id', $ids)->get();

            foreach ($mappings as $mapping) {
                $result = $mapping->deleteWithReferences();

                if (!$result['status']) {
                    return response()->json([
                        'status' => false,
                        'message' => $result['message'],
                        'referenced_tables' => $result['referenced_tables'] ?? []
                    ], 400);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting: ' . $e->getMessage(),
            ], 500);
        }
    }  
}
