<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Hsn;
use App\Models\Ledger;
use App\Helpers\Helper;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\ExpenseMaster;
use App\Helpers\ConstantHelper;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExpenseMasterRequest;

class ExpenseMasterController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;

        if ($request->ajax()) {
            $expenseMasters = ExpenseMaster::with([
                'hsn',
                'expenseLedger',
                'serviceProviderLedger',
                'expenseLedgerGroup',
                'serviceProviderLedgerGroup'
            ])
                ->orderBy('id', 'desc');

            return DataTables::of($expenseMasters)
                ->addIndexColumn()
                ->editColumn('name', function ($expense) {
                    return $expense->name ?? 'N/A';
                })
                ->editColumn('alias', function ($expense) {
                    return $expense->alias ?? 'N/A';
                })
                // ->editColumn('percentage', function ($expense) {
                //     return number_format($expense->percentage, 2) ?? '0.00';
                // })
                ->editColumn('expense_ledger_id', function ($expense) {
                    return $expense->expenseLedger->name ?? 'N/A';
                })
                // ->editColumn('service_provider_ledger_id', function ($expense) {
                //     return $expense->serviceProviderLedger->name ?? 'N/A';
                // })
                ->editColumn('is_purchase', function ($expense) {
                    return $expense->is_purchase ? '1' : '0';
                })
                ->editColumn('is_sale', function ($expense) {
                    return $expense->is_sale ? '1' : '0';
                })
                ->editColumn('status', function ($expense) {
                    $statusClass = 'badge-light-secondary';
                    if ($expense->status == 'active') {
                        $statusClass = 'badge-light-success';
                    } elseif ($expense->status == 'inactive') {
                        $statusClass = 'badge-light-danger';
                    } elseif ($expense->status == 'draft') {
                        $statusClass = 'badge-light-warning';
                    }
                    return '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($expense->status ?? 'Unknown') . '</span>';
                })
                ->addColumn('actions', function ($expense) {

                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item text-warning edit-btn"
                                    data-id="' . $expense->id . '"
                                    data-name="' . $expense->name . '"
                                    data-alias="' . $expense->alias . '"
                                    data-hsn_id="' . optional($expense->hsn)->id . '"
                                    data-hsn_name="' . optional($expense->hsn)->code . '"
                                    data-expense_ledger_id="' . optional($expense->expenseLedger)->id . '"
                                    data-expense_ledger_name="' . optional($expense->expenseLedger)->name . '"
                                    data-expense_ledger_group_id="' . optional($expense->expenseLedgerGroup)->id . '"
                                    data-expense_ledger_group_name="' . optional($expense->expenseLedgerGroup)->name . '"
                                    data-service_provider_ledger_id="' . optional($expense->serviceProviderLedger)->id . '"
                                    data-service_provider_ledger_name="' . optional($expense->serviceProviderLedger)->name . '"
                                    data-service_provider_ledger_group_id="' . optional($expense->serviceProviderLedgerGroup)->id . '"
                                    data-service_provider_ledger_group_name="' . optional($expense->serviceProviderLedgerGroup)->name . '"
                                    data-percentage="' . $expense->percentage . '"
                                    data-is_purchase="' . $expense->is_purchase . '"
                                    data-is_sale="' . $expense->is_sale . '"
                                    data-status="' . $expense->status . '">
                                    <i data-feather="edit" class="me-50"></i> Edit
                                </a>
                                <a href="#" class="dropdown-item text-danger delete-btn"
                                   data-url="' . route('expense-masters.destroy', $expense->id) . '"
                                   data-message="Are you sure you want to delete this Expense Master?">
                                    <i data-feather="trash-2" class="me-50"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }
        $hsns = Hsn::where('status', 'active')
            ->get();
        $ledgers = Ledger::where('status', 1)
            ->get();
        $status = ConstantHelper::STATUS;
        $sac = ConstantHelper::SAC;

        return view('procurement.expense-master.index', compact('ledgers', 'status', 'hsns', 'sac'));
    }

    public function store(ExpenseMasterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();
            $parentUrl = ConstantHelper::EXPENSE_MASTER_SERVICE_ALIAS;
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
            $expenseMaster = ExpenseMaster::create($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $expenseMaster,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the Expense Master: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(ExpenseMasterRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validated = $request->validated();
            $expenseMaster = ExpenseMaster::findOrFail($id);
            $parentUrl = ConstantHelper::EXPENSE_MASTER_SERVICE_ALIAS;
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
            $expenseMaster->update($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $expenseMaster,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the Expense Master: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $expenseMaster = ExpenseMaster::findOrFail($id);
            $result = $expenseMaster->deleteWithReferences();
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
                'message' => 'An error occurred while deleting the Expense Master: ' . $e->getMessage()
            ], 500);
        }
    }
}
