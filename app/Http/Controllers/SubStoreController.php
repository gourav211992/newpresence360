<?php

namespace App\Http\Controllers;
use App\Exceptions\ApiGenericException;
use App\Helpers\InventoryHelper;
use App\Http\Requests\SubStoreRequest;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;
use Exception;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ErpStore;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB; 

class SubStoreController extends Controller
{

    
    public function index(Request $request)
    {    
        $subStores = ErpSubStore::whereHas('parents', function ($subQuery) {
            $subQuery -> withDefaultGroupCompanyOrg();
        })->get();    
        if ($request->ajax()) {
            return DataTables::of($subStores)
                ->addIndexColumn()
                ->addColumn('organization', function ($subStore) {
                    return $subStore->erp_store?->organization->name ?? 'AS';
                })
                ->addColumn('store_name', function ($subStore) {
                    return $subStore->store_names();
                })
                ->addColumn('status', function ($subStore) {
                    return '<span class="badge rounded-pill badge-light-' . ($subStore->status == 'active' ? 'success' : 'danger') . '">'
                        . ucfirst($subStore->status) . '</span>';
                })
                ->addColumn('action', function ($subStore) {
                    $editUrl = route('subStore.edit', $subStore->id);
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
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    
        return view('procurement.subStore.index');
    }
    
    
    public function create()
    {
        $stores=InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $status = ConstantHelper::STATUS;
        $storeLocationType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES; 
        return view('procurement.subStore.create', compact('status','storeLocationType', 'stores'));
    }

    public function store(SubStoreRequest $request)
    {
        $validatedData = $request->validated();
        try {
            $erpSubStore =new ErpSubStore();
            $data= [
                'code' => $validatedData['code'], 
                'name' => $validatedData['name'], 
                'type'=>$validatedData['store_location_type'],
                'station_wise_consumption'=>isset($request -> station_wise_consumption) ? 'yes' : 'no',
                'is_warehouse_required'=>isset($request -> is_warehouse_required) ? 1 : 0,
                'status'=>$validatedData['status'],
            ];
            $erpSubStore->fill($data);
            $erpSubStore->save();
            foreach ($validatedData['store_id'] as $storeId) {
                $store = ErpStore::find($storeId);
                ErpSubStoreParent::create([
                    'group_id' => $store -> group_id,
                    'company_id' => $store -> company_id,
                    'organization_id' => $store -> organization_id,
                    'sub_store_id' => $erpSubStore -> id,
                    'store_id' => $storeId,
                ]);  
            }
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error managing sub store: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $subStore = ErpSubStore::find($id);
        if (!$subStore) {
            return redirect()->back()->with('error', 'Sub Store not found.');
        }
        $status = ConstantHelper::STATUS;
        $storeLocationType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES;
        $referencedCheck = $subStore->isReferenced(['erp_sub_store_parents']); 
        $isSubStoreReferenced = !$referencedCheck['status'];
        $stores=InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK, $subStore -> store_id);
        $selectedStoreIds=$subStore->parents->pluck('store_id')->toArray();
        return view('procurement.subStore.edit', [
            'subStore' => $subStore,
            'stores' => $stores,
            'status' => $status,
            'storeLocationType'=>$storeLocationType,
            'isSubStoreReferenced' => $isSubStoreReferenced,
            'selectedStoreIds' => $selectedStoreIds
        ]);
    }
    

    public function update(SubStoreRequest $request, $id)
    {
        $validatedData = $request->validated();
        DB::beginTransaction();
        try {
            $subStore = ErpSubStore::where('id', $id)->first();
            if (!$subStore) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sub Store not found',
                ], 404);
            }
            $subStore->update([
                'code' => $validatedData['code'],
                'name' => $validatedData['name'],
                'type'=>$validatedData['store_location_type'],
                'station_wise_consumption'=>isset($request -> station_wise_consumption) ? 'yes' : 'no',
                'is_warehouse_required'=>isset($request -> is_warehouse_required) ? 1 : 0,
                'status' => $validatedData['status'],
            ]);
            $newSelectedStoreIds = [];
            foreach ($validatedData['store_id'] as $storeId) {
                $store = ErpStore::find($storeId);
                ErpSubStoreParent::updateOrCreate(
                    ['store_id' => $storeId, 'sub_store_id' => $subStore -> id],
                    [
                        'store_id' => $storeId, 'sub_store_id' => $subStore -> id,
                        'group_id' => $store -> group_id,
                        'company_id' => $store -> company_id,
                        'organization_id' => $store -> organization_id
                    ],
                );
                array_push($newSelectedStoreIds, $storeId);
            }
            ErpSubStoreParent::whereNotIn('store_id', $newSelectedStoreIds) 
            -> where('sub_store_id', $subStore -> id) -> delete();
            
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating sub store: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $subStore = ErpSubStore::findOrFail($id);
            $referenceTables = [
                'erp_racks' => ['erp_store_id'], 
                'erp_shelfs' => ['erp_store_id'],
            ];
            ErpSubStoreParent::where('sub_store_id', $subStore -> id) -> delete();
            $result = $subStore->deleteWithReferences($referenceTables, ['erp_sub_store_parents']);
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the sub store: ' . $e->getMessage()
            ], 500);
        }
    }

    function getSubStoresOfStore(Request $request)
    {
        try {
            $storeId = $request -> store_id ?? 0;
            $itemId = $request -> item_id ?? null;
            $type = isset($request -> types) ? $request -> types : ConstantHelper::STOCKK;
            $subStores = InventoryHelper::getAccesibleSubLocations($storeId, $itemId, $type);
            return response() -> json([
                'status' => 200,
                'message' => 'Records retrieved successfully',
                'data' => $subStores
            ], 200);
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
}
