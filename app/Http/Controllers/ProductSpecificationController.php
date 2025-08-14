<?php

namespace App\Http\Controllers;
use Yajra\DataTables\DataTables;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductSpecificationRequest;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Auth;
use App\Models\Organization;

class ProductSpecificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
    
        if ($request->ajax()) {
            $query = ProductSpecification::query();
            $productSpecifications = $query->orderBy('id', 'desc'); 
    
            return DataTables::of($productSpecifications)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill badge-light-' . ($row->status === 'active' ? 'success' : 'danger') . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('product-specifications.edit', $row->id);
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
    
        return view('procurement.product-specification.index');
    }
    
    
    public function create()
    {
        $status = ConstantHelper::STATUS;
        return view('procurement.product-specification.create', compact('status'));
    }

    public function store(ProductSpecificationRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::PRODUCT_SPECIFICATION_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        try {
            DB::beginTransaction();
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
        $productSpecification = ProductSpecification::create($validatedData);
    
        if ($request->has('specification_details')) {
            $specificationDetails = $request->input('specification_details');
            foreach ($specificationDetails as $detail) {
                if (!empty($detail['name'])) {
                    $productSpecification->details()->create($detail);
                }
            }
        }
        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $productSpecification,
        ]);
        } catch (\Exception $e) {
            DB::rollBack();  
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while creating the product specification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ProductSpecification $productSpecification)
    {
        // Implement this method if needed
    }

    public function edit($id)
    {
        $productSpecification = ProductSpecification::findOrFail($id);
        $status = ConstantHelper::STATUS;
        return view('procurement.product-specification.edit', compact('productSpecification', 'status'));
    }

    public function update(ProductSpecificationRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $productSpecification = ProductSpecification::findOrFail($id);
        $parentUrl = ConstantHelper::PRODUCT_SPECIFICATION_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        try {
            DB::beginTransaction();
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
        $productSpecification->update($validatedData);

        if ($request->has('specification_details')) {
            $specificationDetails = $request->input('specification_details');
            $newDetailIds = [];
            foreach ($specificationDetails as $detail) {
                $detailId = $detail['id'] ?? null;
                if ($detailId) {
                    $existingDetail = $productSpecification->details()->find($detailId);
                    if ($existingDetail) {
                        $existingDetail->update($detail);
                        $newDetailIds[] = $detailId;
                    }
                } else {
                    $newDetail = $productSpecification->details()->create($detail);
                    $newDetailIds[] = $newDetail->id;
                }
            }
            $productSpecification->details()->whereNotIn('id', $newDetailIds)->delete();
        } else {
            $productSpecification->details()->delete();
        }
        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $productSpecification,
        ]);
        } catch (\Exception $e) {
            DB::rollBack();  
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while updating the product specification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function deleteSpecificationDetail($id)
    {
        try {
            $specificationDetail = ProductSpecificationDetail::findOrFail($id);
            $result = $specificationDetail->deleteWithReferences();
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
            $productSpecification = ProductSpecification::findOrFail($id);
            $referenceTables = [
                'erp_product_specification_details' => ['product_specification_id'],
            ];
            $result = $productSpecification->deleteWithReferences($referenceTables);
            
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
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function getSpecificationDetails($id)
    {
        try {
            $specification = ProductSpecification::findOrFail($id);
            $specificationDetails = ProductSpecificationDetail::where('product_specification_id', $id)
                ->get(['id', 'name']);
            return response()->json([
                'specifications' => $specificationDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Specification not found'], 404);
        }
    }
}
