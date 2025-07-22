<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\StationGroup;
use Illuminate\Http\Request;
use App\Http\Requests\StationGroupRequest; 
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\Organization;

class StationGroupController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
    
        if ($request->ajax()) {
            $query = StationGroup::orderBy('id', 'ASC');
            $stationGroups = $query->get();
    
            return DataTables::of($stationGroups)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('station-groups.edit', $row->id);
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
    
        return view('procurement.station-group.index');
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        return view('procurement.station-group.create', compact('status'));
    }

    public function store(StationGroupRequest $request)
    {
        DB::beginTransaction(); 
        try {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $parentUrl = ConstantHelper::STATION_GROUP_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validated['group_id'] = $policyLevelData['group_id'];
                $validated['company_id'] = $policyLevelData['company_id'];
                $validated['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validated['group_id'] = $organization->group_id;
                $validated['company_id'] = $organization->company_id;
                $validated['organization_id'] = null;
            }
        } else {
            $validated['group_id'] = $organization->group_id;
            $validated['company_id'] = $organization->company_id;
            $validated['organization_id'] = null;
        }
        $stationGroup = StationGroup::create($validated);

        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $stationGroup,
        ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $stationGroup = StationGroup::findOrFail($id);
        $status = ConstantHelper::STATUS;
        return view('procurement.station-group.edit', compact('stationGroup', 'status'));
    }

    public function update(StationGroupRequest $request, $id)
    {  
        DB::beginTransaction(); 
        try {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $parentUrl = ConstantHelper::STATION_GROUP_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validated['group_id'] = $policyLevelData['group_id'];
                $validated['company_id'] = $policyLevelData['company_id'];
                $validated['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validated['group_id'] = $organization->group_id;
                $validated['company_id'] = $organization->company_id;
                $validated['organization_id'] = null;
            }
        } else {
            $validated['group_id'] = $organization->group_id;
            $validated['company_id'] = $organization->company_id;
            $validated['organization_id'] = null;
        }
        $stationGroup = StationGroup::findOrFail($id);
        $stationGroup->update($validated);
        DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $stationGroup,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction(); 

        try {
            $stationGroup = StationGroup::findOrFail($id);
            $result = $stationGroup->deleteWithReferences(); 
    
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
                'message' => 'An error occurred while deleting the station group: ' . $e->getMessage(),
            ], 500);
        }
    }
}
