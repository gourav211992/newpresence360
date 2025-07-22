<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TermsAndConditionRequest;
use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper; 
use Auth;
use App\Models\Organization;


class TermsAndConditionController extends Controller
{
    public function index(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization = Organization::where('id', $user->organization_id)->first(); 
    $organizationId = $organization?->id ?? null;
    $companyId = $organization?->company_id ?? null;

    if ($request->ajax()) {
        $terms = TermsAndCondition::query()
            ->orderBy('id', 'DESC')
            ->get();

        return DataTables::of($terms)
            ->addIndexColumn()
            ->addColumn('term_name', function ($row) {
                return $row->term_name; 
            })
            ->addColumn('term_detail', function ($row) {
                return \Str::limit($row->term_detail, 50); 
            })
            ->addColumn('status', function ($row) {
                return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . '">
                            ' . ucfirst($row->status) . '
                        </span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('terms.edit', $row->id);
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
            ->rawColumns(['term_name', 'term_detail', 'status', 'action'])
            ->make(true);
    }

    return view('termConditions.index'); 
}

    public function create()
    {
        $statuses = ConstantHelper::STATUS;

        return view('termConditions.create', compact('statuses'));
    }

    public function store(TermsAndConditionRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::TERMS_CONDITION_SERVICE_ALIAS;
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
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        try {
            $term = TermsAndCondition::create([
                'term_name' => $validatedData['term_name'],
                'term_detail' => $validatedData['term_detail'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'],
                'company_id' => $validatedData['company_id'],
                'group_id' => $validatedData['group_id']
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Record created successfully.',
                'data' => $term,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating Term and Condition: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the term and condition.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $term = TermsAndCondition::findOrFail($id);
        $statuses = ConstantHelper::STATUS;
        return view('termConditions.edit', compact('term', 'statuses'));
    }

    public function update(TermsAndConditionRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::TERMS_CONDITION_SERVICE_ALIAS;
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
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        try {
            $term = TermsAndCondition::findOrFail($id);
            $term->update([
                'term_name' => $validatedData['term_name'],
                'term_detail' => $validatedData['term_detail'],
                'organization_id' => $validatedData['organization_id'], 
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
                'status' => $validatedData['status']
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully.',
                'data' => $term,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating Term and Condition: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $term = TermsAndCondition::findOrFail($id);
            $result = $term->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error deleting Term and Condition: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the term and condition.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
