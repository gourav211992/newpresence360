<?php

namespace App\Http\Controllers;

use Yajra\DataTables\DataTables;
use App\Models\DiscountMaster;
use App\Models\Ledger;
use App\Http\Requests\DiscountMasterRequest;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Auth;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountMasterController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;
        if ($request->ajax()) {
            $discountMasters = DiscountMaster::with(['erpLedger'])
                ->orderBy('id', 'desc');
    
                return DataTables::of($discountMasters)
                ->addIndexColumn()
                ->editColumn('name', function ($discount) {
                    return $discount->name ?? 'N/A';
                })
                ->editColumn('alias', function ($discount) {
                    return $discount->alias ?? 'N/A';
                })
                ->editColumn('discount_ledger_id', function ($discount) {
                    return optional($discount->erpLedger)->name ?? 'N/A';
                })
                ->editColumn('discount_ledger_group_id', function ($discount) {
                    return optional($discount->ledgerGroup)->name ?? 'N/A';
                })
                ->editColumn('percentage', function ($discount) {
                    return number_format($discount->percentage, 2) ?? '0.00';
                })
                ->editColumn('is_purchase', function ($discount) {
                    return $discount->is_purchase ? '1' : '0';
                })
                ->editColumn('is_sale', function ($discount) {
                    return $discount->is_sale ? '1' : '0';
                })
                ->editColumn('status', function ($discount) {
                    $statusClass = 'badge-light-secondary';
            
                    if ($discount->status == 'active') {
                        $statusClass = 'badge-light-success';
                    } elseif ($discount->status == 'inactive') {
                        $statusClass = 'badge-light-danger';
                    } elseif ($discount->status == 'draft') {
                        $statusClass = 'badge-light-warning';
                    }
            
                    return '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($discount->status ?? 'Unknown') . '</span>';
                })
                ->addColumn('actions', function ($discount) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item text-warning edit-btn"
                                    data-id="' . $discount->id . '"
                                    data-name="' . $discount->name . '"
                                    data-alias="' . $discount->alias . '"
                                    data-discount_ledger_id="' . optional($discount->erpLedger)->id . '" 
                                    data-discount_ledger_name="' . optional($discount->erpLedger)->name . '" 
                                    data-discount_ledger_group_id="' . optional($discount->ledgerGroup)->id . '" 
                                    data-discount_ledger_group_name="' . optional($discount->ledgerGroup)->name . '" 
                                    data-percentage="' . $discount->percentage . '"
                                    data-is_purchase="' . $discount->is_purchase . '"
                                    data-is_sale="' . $discount->is_sale . '"
                                    data-status="' . $discount->status . '">
                                    <i data-feather="edit" class="me-50"></i> Edit
                                </a>
                                <a href="#" class="dropdown-item text-danger delete-btn" 
                                   data-url="' . route('discount-masters.destroy', $discount->id) . '" 
                                   data-message="Are you sure you want to delete this Discount Master?">
                                    <i data-feather="trash-2" class="me-50"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions']) 
                ->make(true);
        }
        $ledgers = Ledger::where('status', 1) 
            ->get();
         $status = ConstantHelper::STATUS;
    
        return view('procurement.discount-master.index', compact('ledgers','status'));
    }
    
    public function store(DiscountMasterRequest $request)
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

            $discountMaster = DiscountMaster::create($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $discountMaster,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the Discount Master: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(DiscountMasterRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();

            $discountMaster = DiscountMaster::findOrFail($id);

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

            $discountMaster->update($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $discountMaster,
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
            $discountMaster = DiscountMaster::findOrFail($id);

            $result = $discountMaster->deleteWithReferences();

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
                'message' => 'An error occurred while deleting the Discount Master: ' . $e->getMessage()
            ], 500);
        }
    }
}
