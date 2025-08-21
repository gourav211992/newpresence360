<?php

namespace App\Http\Controllers\Kaizen;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Kaizen\ErpKaizenImprovement;
use App\Http\Requests\Kaizen\ErpKaizenImprovementRequest;


class ImprovementController extends Controller
{
     public function index(Request $request)
    {
        
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;
        if ($request->ajax()) {
            $ErpKaizenImprovements = ErpKaizenImprovement::
            // where('organization_id', $organizationId)->
                orderBy('id', 'desc');
    
                return DataTables::of($ErpKaizenImprovements)
                ->addIndexColumn()
                ->editColumn('type', function ($imp) {
                    return $imp->type ?? 'N/A';
                })
                ->editColumn('description', function ($imp) {
                    return $imp->description ?? 'N/A';
                })
                ->editColumn('marks', function ($imp) {
                    return $imp->marks ?? 0;
                })
                ->editColumn('status', function ($imp) {
                    $statusClass = 'badge-light-secondary';
            
                    if ($imp->status == 'active') {
                        $statusClass = 'badge-light-success';
                    } elseif ($imp->status == 'inactive') {
                        $statusClass = 'badge-light-danger';
                    } elseif ($imp->status == 'draft') {
                        $statusClass = 'badge-light-warning';
                    }
                    return '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($imp->status ?? 'Unknown') . '</span>';
                })
                ->addColumn('actions', function ($imp) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item text-warning edit-btn"
                                    data-id="' . $imp->id . '"
                                    data-type="' . $imp->type . '"
                                    data-description="' . $imp->description . '"
                                    data-marks="' . $imp->marks . '" 
                                    data-status="' . $imp->status . '">
                                    <i data-feather="edit" class="me-50"></i> Edit
                                </a>
                                <a href="#" class="dropdown-item text-danger delete-btn" 
                                   data-url="' . route('improvement-masters.destroy', $imp->id) . '" 
                                   data-message="Are you sure you want to delete this Improvement Master?">
                                    <i data-feather="trash-2" class="me-50"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions']) 
                ->make(true);
        }
    
         $status = ConstantHelper::STATUS;

        return view('kaizen.evaluation.index', compact('status'));
    }
    
    public function store(ErpKaizenImprovementRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();
            $parentUrl = ConstantHelper::DISCOUNT_MASTER_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
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

            $ErpKaizenImprovement = ErpKaizenImprovement::create($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $ErpKaizenImprovement,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the Discount Master: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(ErpKaizenImprovementRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();

            $ErpKaizenImprovement = ErpKaizenImprovement::findOrFail($id);

            $parentUrl = ConstantHelper::DISCOUNT_MASTER_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
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

            $ErpKaizenImprovement->update($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $ErpKaizenImprovement,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the Discount Master: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            ErpKaizenImprovement::whereId($id)->delete();


            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Improvement Master: ' . $e->getMessage()
            ], 500);
        }
    }
}
