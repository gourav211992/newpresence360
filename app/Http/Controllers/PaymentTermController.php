<?php

namespace App\Http\Controllers;
use Yajra\DataTables\DataTables;
use App\Models\PaymentTerm;
use App\Models\PaymentTermDetail;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Http\Requests\PaymentTermRequest;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper; 
use Auth;

class PaymentTermController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
    
        if ($request->ajax()) {
         $paymentTerms = PaymentTerm::orderBy('id', 'desc')->get();
            return DataTables::of($paymentTerms)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill badge-light-' . ($row->status === 'active' ? 'success' : 'danger') . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('payment-terms.edit', $row->id);
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
    
        return view('procurement.payment-term.index');
    }
    

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $triggerTypes = ConstantHelper::TRIGGER_TYPES;
        return view('procurement.payment-term.create', compact('status', 'triggerTypes'));
    }

    public function store(PaymentTermRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $parentUrl = ConstantHelper::PAYMENT_TERM_SERVICE_ALIAS;
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
    
        $paymentTerm = PaymentTerm::create($validated);
    
        if ($request->has('term_details')) {
            $termDetails = $request->input('term_details');
            foreach ($termDetails as $detail) {
                if (!empty($detail['installation_no'])) {
                    $paymentTerm->details()->create($detail);
                }
            }
        }
       
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $paymentTerm,
        ]);
    }

    public function show(PaymentTerm $paymentTerm)
    {
        // Implement this method if needed
    }

    public function edit($id)
    {
        $paymentTerm = PaymentTerm::findOrFail($id);
        $status = ConstantHelper::STATUS;
        $triggerTypes = ConstantHelper::TRIGGER_TYPES; 
        return view('procurement.payment-term.edit', compact('paymentTerm', 'status', 'triggerTypes'));
    }

    public function update(PaymentTermRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $paymentTerm = PaymentTerm::findOrFail($id);
        $parentUrl = ConstantHelper::PAYMENT_TERM_SERVICE_ALIAS;
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
        $paymentTerm->update($validated);
        if ($request->has('term_details')) {
            $termDetails = $request->input('term_details');
            $newDetailIds = [];

            foreach ($termDetails as $detail) {
                $detailId = $detail['id'] ?? null;

                if ($detailId) {
                    $existingDetail = $paymentTerm->details()->find($detailId);
                    if ($existingDetail) {
                        $existingDetail->update($detail);
                        $newDetailIds[] = $detailId; 
                    }
                } else {
                    $newDetail = $paymentTerm->details()->create($detail);
                    $newDetailIds[] = $newDetail->id; 
                }
            }
            $paymentTerm->details()->whereNotIn('id', $newDetailIds)->delete();
        } else {
            $paymentTerm->details()->delete();
        }
        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $paymentTerm,
        ]);
    }

    public function deletePaymentTermDetail($id)
    {
        try {
            $paymentTermDetail = PaymentTermDetail::findOrFail($id);
            $result = $paymentTermDetail->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.',
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $paymentTerm = PaymentTerm::findOrFail($id);
            $referenceTables = [
                'erp_payment_term_details' => ['payment_term_id'], 
            ];
            $result = $paymentTerm->deleteWithReferences($referenceTables);
            
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record',
            ], 500);
        }
    }
    
}
