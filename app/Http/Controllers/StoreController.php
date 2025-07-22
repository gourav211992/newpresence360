<?php

namespace App\Http\Controllers;
use App\Exceptions\ApiGenericException;
use Exception;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ErpStore;
use App\Models\ErpRack;
use App\Models\ErpShelf; 
use App\Models\ErpBin;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\AuthUser;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRequest;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Traits\Deletable; 

class StoreController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $stores = ErpStore::query()
            ->orderBy('id', 'desc'); 
    
            return DataTables::of($stores)
                ->addIndexColumn()
                ->addColumn('company', function ($store) {
                    return $store->organization->name ?? '';
                })
                ->addColumn('racks', function ($store) {
                    return $store->racks ? count($store->racks) : 0;
                })
                ->addColumn('shelfs', function ($store) {
                    return $store->Shelfs ? count($store->Shelfs) : 0;
                })
                ->addColumn('status', function ($store) {
                    return '<span class="badge rounded-pill badge-light-' . ($store->status == 'active' ? 'success' : 'danger') . '">'
                        . ucfirst($store->status) . '</span>';
                })
                ->addColumn('action', function ($store) {
                    $editUrl = route('store.edit', $store->id);
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
                ->rawColumns(['status','action'])
                ->make(true);
        }
    
        return view('procurement.store.index');
    }
    
    
    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER');
       if ($isSuperAdmin) {
            $allOrganizations = Organization::where('group_id',$groupId)
            ->where('status', 'active')
                ->with('addresses')
                ->get();
        } else {
            $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
            $allOrganizations = Organization::whereIn('id', $orgIds)
                ->with('addresses')
                ->where('status', 'active')
                ->get();
        }
    
        $status = ConstantHelper::STATUS;
        $storeLocationType = ConstantHelper::ERP_STORE_LOCATION_TYPES_LABEL_VAL; 
        return view('procurement.store.create', compact('status','storeLocationType','allOrganizations'));
    }

    public function store(StoreRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $organizations = Organization::where('id', $validatedData['organization_id'])->first();
            $erpStore =new ErpStore();
            $data= [
                    'store_code' => $validatedData['store_code'], 
                    'store_name'=>$validatedData['store_name'],
                    'contact_person'=>$validatedData['contact_person'],
                    'contact_phone_no'=>$validatedData['contact_phone_no'],
                    'contact_email'=>$validatedData['contact_email'],
                    'store_location_type'=>ConstantHelper::STOCKK,
                    'status'=>$validatedData['status'],
                    'organization_id' => $validatedData['organization_id'],
                    'company_id'=> $organizations->company_id,
                    'group_id'=>$organizations->group_id 
                ];

                $erpStore->fill($data);
                $erpStore->save();
                $erpStore->address()->create([
                    'country_id' => $validatedData['country_id'],
                    'state_id' => $validatedData['state_id'],
                    'city_id' => $validatedData['city_id'],
                    'address' => $validatedData['address'],
                    'pincode_master_id' => $validatedData['pincode_master_id'],
                    'pincode' => $validatedData['pincode']
                ]);
           
            return response()->json([
                'status' => true,
                'store_id' => $erpStore->id,
                'message' => 'Record created successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error managing store: ' . $e->getMessage(),
            ]);
        }
    }

    public function rackStore(Request $request)
    {
        $storeId = $request->store_id;
        $store = ErpStore::findOrFail($storeId);
        $organizationData = $store->organization;
        $validatedData = $request->validate([
            'racks' => 'required|array',
            'racks.*.rack_code' => 'required|string|max:151',
            'deleted_racks' => 'array',
        ]);
    
        $messages = []; 
    
        foreach ($validatedData['racks'] as $rack) {
            if (ErpRack::where('rack_code', $rack['rack_code'])
                ->where('erp_store_id', $storeId)
                ->exists()) {
                $messages[] = "Rack code {$rack['rack_code']} already exists.";
                continue; 
            }
    
            ErpRack::create([
                'rack_code' => $rack['rack_code'],
                'organization_id' => $organizationData->id,
                'company_id' => $organizationData->company_id,
                'group_id' => $organizationData->group_id,
            ]);
        }
        return response()->json([
            'message' => 'Racks created successfully',
            'duplicateMessages' => $messages,
        ]);
    }
    
    public function shelfStore(Request $request)
    {
        $storeId = $request->store_id;
        $store = ErpStore::findOrFail($storeId);
        $organizationData = $store->organization;
    
        $validatedData = $request->validate([
            'shelfs' => 'required|array',
            'shelfs.*.shelf_code' => 'required|string|max:151',
            'deleted_shelfs' => 'array',
        ]);
    
        $messages = []; 
    
        foreach ($validatedData['shelfs'] as $shelf) {
            if (ErpShelf::where('shelf_code', $shelf['shelf_code'])
                ->where('erp_store_id', $storeId)
                ->exists()) {
                $messages[] = "Shelf code {$shelf['shelf_code']} already exists.";
                continue;
            }
    
            ErpShelf::create([
                'shelf_code' => $shelf['shelf_code'],
                'organization_id' => $organizationData->id,
                'company_id' => $organizationData->company_id,
                'group_id' => $organizationData->group_id,
            ]);
        }

        return response()->json([
            'message' => 'Shelf created successfully',
            'duplicateMessages' => $messages,
        ]);
    }
    
    public function binStore(Request $request)
    {
        $storeId = $request->store_id;
        $store = ErpStore::findOrFail($storeId);
        $organizationData = $store->organization;
    
        $validatedData = $request->validate([
            'bins' => 'required|array',
            'bins.*.bin_code' => 'required|string|max:151',
            'deleted_bins' => 'array',
        ]);
    
        $messages = []; 
    
        foreach ($validatedData['bins'] as $bin) {
         
            if (ErpBin::where('bin_code', $bin['bin_code'])
                ->where('erp_store_id', $storeId)
                ->exists()) {
                $messages[] = "Bin code {$bin['bin_code']} already exists.";
                continue; 
            }
    
            ErpBin::create([
                'bin_code' => $bin['bin_code'],
                'organization_id' => $organizationData->id,
                'company_id' => $organizationData->company_id,
                'group_id' => $organizationData->group_id,
            ]);
        }
        return response()->json([
            'message' => 'Bins created successfully',
            'duplicateMessages' => $messages,
        ]);
    }
 
    private function mapRackShelf(array $rackShelfData, $storeId)
    {
        $currentRackIds = ErpRack::where('erp_store_id', $storeId)->pluck('id')->toArray();
        $currentShelfIds = ErpShelf::where('erp_store_id', $storeId)->pluck('id')->toArray();
        foreach ($rackShelfData as $data) {
            $rackId = $data['rack_id'] ?? null;
            $shelfIds = $data['shelf_ids'] ?? [];
            if ($rackId) {
                ErpRack::where('id', $rackId)->update(['erp_store_id' => $storeId]);
                if (!empty($shelfIds)) {
                    $currentShelfIdsForRack = ErpShelf::where('erp_rack_id', $rackId)->pluck('id')->toArray();
                    ErpShelf::whereIn('id', $shelfIds)->update([
                        'erp_rack_id' => $rackId,
                        'erp_store_id' => $storeId
                    ]);
                    $shelvesToUnlink = array_diff($currentShelfIdsForRack, $shelfIds);
                    if (!empty($shelvesToUnlink)) {
                        ErpShelf::whereIn('id', $shelvesToUnlink)->update(['erp_rack_id' => null, 'erp_store_id' => null]);
                    }
                }
            }
        }
    }

    private function unlinkRacksFromShelves(array $rackShelfData, $storeId)
    {
        $currentRackIds = ErpRack::where('erp_store_id', $storeId)->pluck('id')->toArray();
        $linkedRackIds = array_column($rackShelfData, 'rack_id');
        $racksToUnlink = array_diff($currentRackIds, $linkedRackIds);
        if (!empty($racksToUnlink)) {
            ErpShelf::whereIn('erp_rack_id', $racksToUnlink)->update([
                'erp_rack_id' => null,
                'erp_store_id' => null 
            ]);
        }
        if (!empty($racksToUnlink)) {
            ErpRack::whereIn('id', $racksToUnlink)->update(['erp_store_id' => null]);
        }
    }

    private function mapBins(array $binData, $storeId)
    {
        if (isset($binData['bin_ids']) && is_array($binData['bin_ids'])) {
            $newBinIds = $binData['bin_ids'];
            $currentAssociatedBins = ErpBin::where('erp_store_id', $storeId)->pluck('id')->toArray();
            foreach ($newBinIds as $binId) {
                ErpBin::where('id', $binId)->update(['erp_store_id' => $storeId]);
            }
            $binsToUnlink = array_diff($currentAssociatedBins, $newBinIds);
            if (!empty($binsToUnlink)) {
                ErpBin::whereIn('id', $binsToUnlink)->update(['erp_store_id' => null]);
            }
        } else {
            throw new \Exception('Bin IDs not found or invalid format');
        }
    }
    
    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER');
        if ($isSuperAdmin) {
              $allOrganizations = Organization::where('group_id',  $groupId)
               ->where('status', 'active')
                ->with('addresses')
                ->get();
        } else {
            $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
            $allOrganizations = Organization::whereIn('id', $orgIds)
                ->with('addresses')
                ->where('status', 'active')
                ->get();
        }
        $states = [];
        $cities = [];
       if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
            ->where('group_id', $groupId)
            ->find($id);
        } else {
            $store = ErpStore::find($id);
        }
        if (!$store) {
            return redirect()->back()->with('error', 'Store not found.');
        }
        $status = ConstantHelper::STATUS;
        $storeLocationType = ConstantHelper::ERP_STORE_LOCATION_TYPES_LABEL_VAL; 
        $organizations = Organization::where('status', 'active')->get();
        $shelfRackMappings = ErpRack::with(['shelfs', 'store'])
        ->where('erp_store_id', $id) 
        ->get();
        $referencedCheck = $store->isReferenced(); 
        $isStoreReferenced = !$referencedCheck['status'];
        $countries = Country::all(); 
        if ($store->address && $store->address->country_id) {
            $states = State::where('country_id', $store->address->country_id)->get();
        }
        
        if ($store->address && $store->address->state_id) {
            $cities = City::where('state_id', $store->address->state_id)->get();
        }
        
        return view('procurement.store.edit', [
            'store' => $store,
            'status' => $status,
            'storeLocationType'=>$storeLocationType,
            'allOrganizations' => $allOrganizations,
            'shelfRackMappings' => $shelfRackMappings,
            'isStoreReferenced' => $isStoreReferenced,
            'countries'=>$countries,
            'states'=>$states,
            'cities'=>$cities,
        ]);
    }
    

    public function update(StoreRequest $request, $id)
    {
        $validatedData = $request->validated();
        $organizations = Organization::where('id', $validatedData['organization_id'])->first();
        DB::beginTransaction();
    
        try {
            $user = Helper::getAuthenticatedUser(); 
            $useRole = AuthUser::where('id', $user->auth_user_id)->first();
            $isSuperAdmin = ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER');
            $organization = $user->organization;
            $groupId = $organization?->group_id;
            if ($isSuperAdmin) {
                $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->find($id);
            } else {
                $store = ErpStore::find($id);
            }
    
            if (!$store) {
                return response()->json([
                    'status' => false,
                    'message' => 'Store not found',
                ], 404);
            }
    
            $store->update([
                'store_code' => $validatedData['store_code'], 
                'store_name' => $validatedData['store_name'],
                'contact_person' => $validatedData['contact_person'],
                'contact_phone_no' => $validatedData['contact_phone_no'],
                'contact_email' => $validatedData['contact_email'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'],
                'company_id' => $organizations->company_id,
                'group_id' => $organizations->group_id,
            ]);
    
            $storeId = $store->id;
            $this->mapRackShelf($validatedData['rackshelfmapping'] ?? [], $storeId, $organizations);
            $this->unlinkRacksFromShelves($validatedData['rackshelfmapping'] ?? [], $storeId);
            if (isset($validatedData['storebinmapping']['bin_ids'])) {
                $this->mapBins($validatedData['storebinmapping'], $storeId);
            } else {
            }

            $address = $store->address()->first(); 
       

            if ($address) {
                $address->update([
                    'country_id' => $validatedData['country_id'],
                    'state_id' => $validatedData['state_id'],
                    'city_id' => $validatedData['city_id'],
                    'address' => $validatedData['address'],
                    'pincode' => $validatedData['pincode'],
                ]);
            } else {
                $store->address()->create([
                    'country_id' => $validatedData['country_id'],
                    'state_id' => $validatedData['state_id'],
                    'city_id' => $validatedData['city_id'],
                    'address' => $validatedData['address'],
                    'pincode' => $validatedData['pincode'],
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating store: ' . $e->getMessage(),
            ]);
        }
    }
    
    
    public function getMappedRacks(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');
        $storeId = $request->input('store_id');
        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->findOrFail($storeId);
        } else {
            $store = ErpStore::findOrFail($storeId);
        }
        $mappedRacks = ErpRack::whereNotNull('erp_store_id')->pluck('id')->toArray();
        $racks = ErpRack::where('organization_id', $store->organization_id)
            ->whereNotIn('id', $mappedRacks)
            ->get();

        return response()->json($racks);
    }

    public function getMappedShelves(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;

        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');
        $storeId = $request->input('store_id');
        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->findOrFail($storeId);
        } else {
            $store = ErpStore::findOrFail($storeId);
        }
        $mappedShelves = ErpShelf::whereNotNull('erp_store_id')->pluck('id')->toArray();
        $shelves = ErpShelf::where('organization_id', $store->organization_id)
            ->whereNotIn('id', $mappedShelves)
            ->get();
    
        return response()->json($shelves);
    }
    
    public function getMappedBins(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;

        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');
        $storeId = $request->input('store_id');
        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->findOrFail($storeId);
        } else {
            $store = ErpStore::findOrFail($storeId);
        }
        $mappedBins = ErpBin::whereNotNull('erp_store_id')->pluck('id')->toArray();
        $bins = ErpBin::where('organization_id', $store->organization_id)
            ->whereNotIn('id', $mappedBins)
            ->get();

        return response()->json($bins);
    }


    public function getRacks(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;

        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER');
        $storeId = $request->input('store_id');
        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->findOrFail($storeId);
        } else {
            $store = ErpStore::findOrFail($storeId);
        }
        $racks = ErpRack::where('organization_id', $store->organization_id)
            ->get();

        return response()->json($racks);
    }

    public function getShelves(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;

        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER');
        $storeId = $request->input('store_id');
       if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->findOrFail($storeId);
        } else {
            $store = ErpStore::findOrFail($storeId);
        }
        $shelves = ErpShelf::where('organization_id', $store->organization_id)
            ->get();
    
        return response()->json($shelves);
    }
    
    public function getBins(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;

        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER');
        $storeId = $request->input('store_id');
        if ($isSuperAdmin) {
          $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
            ->where('group_id', $groupId)
            ->findOrFail($storeId);
        } else {
            $store = ErpStore::findOrFail($storeId);
        }
        $bins = ErpBin::where('organization_id', $store->organization_id)
            ->get();

        return response()->json($bins);
    }

    public function searchRacks(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');
        $query = $request->input('query');
        $storeId = $request->store_id;
        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->find($storeId);
        } else {
            $store = ErpStore::find($storeId);
        }
        if ($store) {
            $racks = ErpRack::where('rack_code', 'LIKE', '%' . $query . '%')
                           ->where('organization_id', $store->organization_id)
                           ->get(['id', 'rack_code']); 
    
            return response()->json($racks); 
        }
    
        return response()->json([]); 
    }
    

    public function searchShelves(Request $request)
    {
        $query = $request->input('query');
        $storeId = $request->store_id;
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');

        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->find($storeId);
        } else {
            $store = ErpStore::find($storeId);
        }
        if ($store) {
            $shelves = ErpShelf::where('shelf_code', 'LIKE', '%' . $query . '%')
                            ->where('organization_id', $store->organization_id) 
                            ->get(['id','shelf_code']);
            return response()->json($shelves);
        }

        return response()->json([]);
    }


    public function searchBins(Request $request)
    {
        $query = $request->input('query');
        $storeId = $request->store_id;
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization?->group_id;

        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
        $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');
        if ($isSuperAdmin) {
            $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('group_id', $groupId)
                ->find($storeId);
        } else {
            $store = ErpStore::find($storeId);
        }
        if ($store) {
            $bins = ErpBin::where('bin_code', 'LIKE', '%' . $query . '%')
                        ->where('organization_id', $store->organization_id)
                        ->get(['id','bin_code']);
            return response()->json($bins);
        }

        return response()->json([]);
    }

    public function destroy($id)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $groupId = $organization?->group_id;

            $useRole = AuthUser::find($user->auth_user_id);
            $isSuperAdmin = ($useRole && $useRole->user_type === 'IAM-SUPER');
            if ($isSuperAdmin) {
                $store = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                    ->where('group_id', $groupId)
                    ->findOrFail($id);
            } else {
                $store = ErpStore::findOrFail($id);
            }
            $referenceTables = [
                'erp_racks' => ['erp_store_id'], 
                'erp_shelfs' => ['erp_store_id'],
                'erp_bins' => ['erp_store_id'], 
            ];
            $result = $store->deleteWithReferences($referenceTables);
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the store: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroyBin($id)
    {
        try {
            $bin = ErpBin::findOrFail($id);

            $result = $bin->deleteWithReferences();
    
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Bin deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the bin: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyShelf($id)
    {
        try {
            $shelf = ErpShelf::findOrFail($id);
            $result = $shelf->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Shelf deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the shelf: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroyRack($id)
    {
        try {
            $rack = ErpRack::findOrFail($id);
    
            $referenceTables = [
                'erp_shelfs' => ['erp_rack_id'], 
            ];
    
            $result = $rack->deleteWithReferences($referenceTables);
    
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Rack deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the rack: ' . $e->getMessage()
            ], 500);
        }
    }

    # Get Location
    public function getLocation(Request $request)
    {
        try {
            $locationId = $request->location_id ?? null; // store_id
            $locations = InventoryHelper::getAccessibleLocations('stock', $locationId);
            return response()->json([
                'data' => ['locations' => $locations],
                'status' => 200,
                'message' => 'Fetched successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching locations:', ['error' => $e->getMessage()]);
            return response()->json([
                'data' => [],
                'status' => 500,
                'message' => 'An error occurred while fetching locations.',
            ], 500);
        }
    }

    public function getStoreRacksAndBins(Request $request)
    {
        try {
            $store = ErpStore::with(['racks', 'shelfs']) -> find($request -> store_id);
            return array(
                'message' => 'Data found',
                'data' => array(
                    'racks' => $store ?-> store_racks,
                    'bins' => $store ?-> store_bins
                )
            );
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
    public function getRackShelfs(Request $request)
    {
        try {
            $rack = ErpRack::find($request -> rack_id);
            return array(
                'message' => 'Data found',
                'data' => array(
                    'shelfs' => $rack ?-> rack_shelfs,
                )
            );
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
    # Get Sub Store
    public function getSubStore(Request $request)
    {
        try {
            $storeId = $request->store_id;
            $subStores = InventoryHelper::getAccesibleSubLocations($storeId ?? 0, null, [ConstantHelper::SHOP_FLOOR]);
            return response()->json(['data' => $subStores, 'status' => 200, 'message' => "fetched!"]);
        } catch (Exception $e) {
            \Log::error('Error fetching sub stores:', ['error' => $e->getMessage()]);
            return response()->json(['data' => [], 'status' => 500, 'message' => "An error occurred while fetching sub stores."]);
        }
    }

}
