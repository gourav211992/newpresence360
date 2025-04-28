<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Station;
use App\Models\StationGroup;
use Illuminate\Http\Request;
use App\Http\Requests\StationRequest; 
use App\Helpers\ConstantHelper;
use Illuminate\Support\Str;
use App\Helpers\Helper;
use App\Models\Organization;
use Auth;

class StationController extends Controller
{
    public function index(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization = Organization::where('id', $user->organization_id)->first(); 
    $organizationId = $organization?->id ?? null;
    $companyId = $organization?->company_id ?? null;

    if ($request->ajax()) {
        $stations = Station::WithDefaultGroupCompanyOrg()
            ->whereNull('parent_id')
            ->orderBy('id', 'desc');

        return DataTables::of($stations)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . '">
                            ' . ucfirst($row->status) . '
                        </span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('stations.edit', $row->id);
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

    return view('procurement.station.index');
}

    

    public function create()
    {
        $stations = Station::whereNull('parent_id')->WithDefaultGroupCompanyOrg()->get();
        $stationGroups = StationGroup::where('status', 'active')->get();
        $status = ConstantHelper::STATUS;
        return view('procurement.station.create', compact('stations','stationGroups', 'status'));
    }

    public function store(StationRequest $request)
    {
        $isConsumption = isset($request->is_consumption) && $request->is_consumption ? 'yes' : 'no'; 
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $validatedData['is_consumption'] = $isConsumption;
        $parentUrl = ConstantHelper::STATION_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = null;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = null;
            $validatedData['organization_id'] = null;
        }

        DB::beginTransaction();
        try {
            $station = Station::create($validatedData);

            $subStations = $validatedData['substations'] ?? [];
            foreach ($subStations as $subStation) {
                if (!empty($subStation['name'])) {
                    Station::create([
                        'name' => $subStation['name'],
                        'parent_id' => $station->id,
                        'alias' => $subStation['alias'] ?? null,
                        'status' => $subStation['status'] ?? $station->status,
                        'organization_id' => $validatedData['organization_id'],
                        'group_id' => $validatedData['group_id'],
                        'company_id' => $validatedData['company_id'],
                    ]);
                }
            }

         DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $station,
        ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Station $station)
    {
        // Implement as needed
    }

    public function edit($id)
    {
        $station = Station::with('subStations')->findOrFail($id);
        $stationGroups = StationGroup::where('status', 'active')->get();
        $stations = Station::whereNull('parent_id')->WithDefaultGroupCompanyOrg()->get();
        $status = ConstantHelper::STATUS;
        return view('procurement.station.edit', compact('station', 'stations','stationGroups', 'status'));
    }

    public function update(StationRequest $request, $id)
    {
        $isConsumption = isset($request->is_consumption) && $request->is_consumption ? 'yes' : 'no'; 
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $validatedData['is_consumption'] = $isConsumption;
        $parentUrl = ConstantHelper::STATION_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = null;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = null;
            $validatedData['organization_id'] = null;
        }

        DB::beginTransaction();

        try {
            $station = Station::findOrFail($id);
            $station->update([
                'name' => $validatedData['name'],
                'alias' => $validatedData['alias'],
                'status' => $validatedData['status'],
                'is_consumption' => $validatedData['is_consumption']
            ]);

            $subStations = $validatedData['substations'] ?? [];
            $newStationIds = [];

            foreach ($subStations as $subStation) {
                $subStationId = $subStation['id'] ?? null;
                
                if ($subStationId) {
                    $sub = Station::find($subStationId);
                    if ($sub) {
                        $sub->update([
                            'name' => $subStation['name'],
                            'alias' => $subStation['alias'] ?? null,
                            'status' => $subStation['status'] ?? $station->status,
                            'parent_id' => $station->id,
                            'organization_id' => $validatedData['organization_id'],
                            'group_id' => $validatedData['group_id'],
                            'company_id' => $validatedData['company_id'],
                        ]);
                        $newStationIds[] = $sub->id;
                    }
                } else {
                    $sub = Station::create([
                        'name' => $subStation['name'],
                        'alias' => $subStation['alias'] ?? null,
                        'status' => $subStation['status'] ?? $station->status,
                        'parent_id' => $station->id,
                        'organization_id' => $validatedData['organization_id'],
                        'group_id' => $validatedData['group_id'],
                        'company_id' => $validatedData['company_id'],
                    ]);
                    $newStationIds[] = $sub->id;
                }
            }

            Station::where('parent_id', $station->id)
                ->whereNotIn('id', $newStationIds)
                ->delete();

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Record updated successfully',
                    'data' => $station,
                ]);
        
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while updating the record',
                    'error' => $e->getMessage(),
                ], 500);
            }
    }

    public function deleteSubstation($id)
    {
        DB::beginTransaction();
         try {
            $substation = Station::findOrFail($id);
            $referenceTables = [
                'erp_stations' => ['parent_id'],
            ];
            $result = $substation->deleteWithReferences($referenceTables);
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? [],
                ], 400);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.',
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction(); 
        try {
            $station = Station::findOrFail($id);
            $referenceTables = [
                'erp_stations' => ['parent_id'],
            ];
            $result = $station->deleteWithReferences($referenceTables);
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully',
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the station: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSubstations($stationId)
    {
        $substations = Station::where('parent_id', $stationId)->WithDefaultGroupCompanyOrg()->get();
        return response()->json($substations);
    }
}
