<?php

namespace App\Http\Controllers;

use App\Models\Overhead;
use App\Models\Ledger;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Yajra\DataTables\DataTables;
use App\Http\Requests\OverheadMasterRequest;
use Illuminate\Support\Facades\DB;

class OverheadMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $overheads = Overhead::with(['erpLedger','ledgerGroup'])
                ->withDefaultGroupCompanyOrg()
                ->get();
            return DataTables::of($overheads)
                ->addIndexColumn()
                ->editColumn('name', fn($overhead) => $overhead->name ?? 'N/A')
                ->editColumn('perc', fn($overhead) => number_format($overhead->perc, 2) ?? '0.00')
                ->editColumn('ledger_id', fn($overhead) => optional($overhead->erpLedger)->name ?? 'N/A')
                ->editColumn('ledger_group_id', fn($overhead) => optional($overhead->ledgerGroup)->name ?? 'N/A')
                ->editColumn('status', function ($overhead) {
                    $statusClass = match($overhead->status) {
                        'active' => 'badge-light-success',
                        'inactive' => 'badge-light-danger',
                        'draft' => 'badge-light-warning',
                        default => 'badge-light-secondary'
                    };

                    return '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($overhead->status ?? 'Unknown') . '</span>';
                })
                ->addColumn('actions', function ($overhead) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item text-warning edit-btn"
                                    data-id="' . $overhead->id . '"
                                    data-name="' . $overhead->name . '"
                                    data-alias="' . $overhead->alias . '"
                                    data-ledger_id="' . optional($overhead->erpLedger)->id . '" 
                                    data-ledger_name="' . optional($overhead->erpLedger)->name . '" 
                                    data-ledger_group_id="' . optional($overhead->ledgerGroup)->id . '" 
                                    data-ledger_group_name="' . optional($overhead->ledgerGroup)->name . '" 
                                    data-perc="' . $overhead->perc . '"
                                    data-is_waste="' . $overhead->is_waste . '"
                                    data-status="' . $overhead->status . '">
                                    <i data-feather="edit" class="me-50"></i> Edit
                                </a>
                                <a href="#" class="dropdown-item text-danger delete-btn" 
                                   data-url="' . route('overhead-masters.destroy', $overhead->id) . '" 
                                   data-message="Are you sure you want to delete this Overhead?">
                                    <i data-feather="trash-2" class="me-50"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where('status', 1)->get();
        $status = ConstantHelper::STATUS;

        return view('procurement.overhead-master.index', compact('ledgers', 'status'));
    }

    public function store(OverheadMasterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();
            $parentUrl = ConstantHelper::OVERHEAD_MASTER_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

            if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                $policyData = Helper::getPolicyByServiceId($services['services']->first()->service_id);
                $policyLevelData = $policyData['policyLevelData'] ?? null;

                $validated['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                $validated['company_id'] = $policyLevelData['company_id'] ?? null;
                $validated['organization_id'] = $policyLevelData['organization_id'] ?? null;
            } else {
                $validated['group_id'] = $organization->group_id;
                $validated['company_id'] = null;
                $validated['organization_id'] = null;
            }
            $validated['is_waste'] = isset($request->is_waste) && $request->is_waste == 'yes' ? 1 : 0;
            $overhead = Overhead::create($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $overhead
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating Overhead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(OverheadMasterRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();

            $overhead = Overhead::findOrFail($id);

            $parentUrl = ConstantHelper::OVERHEAD_MASTER_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                $policyData = Helper::getPolicyByServiceId($services['services']->first()->service_id);
                $policyLevelData = $policyData['policyLevelData'] ?? null;

                $validated['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                $validated['company_id'] = $policyLevelData['company_id'] ?? null;
                $validated['organization_id'] = $policyLevelData['organization_id'] ?? null;
            } else {
                $validated['group_id'] = $organization->group_id;
                $validated['company_id'] = null;
                $validated['organization_id'] = null;
            }
            $validated['is_waste'] = isset($request->is_waste) && $request->is_waste == 'yes' ? 1 : 0;
            $overhead->update($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $overhead
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating Overhead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $overhead = Overhead::findOrFail($id);

            $overhead->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting Overhead: ' . $e->getMessage()
            ], 500);
        }
    }
}
