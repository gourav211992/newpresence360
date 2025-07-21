<?php

namespace App\Http\Controllers;

use App\Models\ProductSection;
use Yajra\DataTables\DataTables;
use App\Models\ProductSectionDetail;
use Illuminate\Http\Request;
use App\Http\Requests\ProductSectionRequest;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Station;
use App\Helpers\Helper; 
use App\Models\Organization;


class ProductSectionController extends Controller
{

   public function index(Request $request)
{        
    $user = Helper::getAuthenticatedUser();
    $organization = Organization::where('id', $user->organization_id)->first(); 
    $organizationId = $organization?->id ?? null;
    $companyId = $organization?->company_id ?? null;

    if ($request->ajax()) {
        $query = ProductSection::query();
        $productSections = $query->orderBy('id', 'desc'); 
    
        return DataTables::of($productSections)
            ->addIndexColumn()
            ->addColumn('id', function ($row) {
                return $row->id ?? 'N/A';
            })
            ->addColumn('name', function ($row) {
                return $row->name ?? 'N/A';
            })
            ->addColumn('status', function ($row) {
                return '<span class="badge rounded-pill badge-light-' . ($row->status == 'active' ? 'success' : 'danger') . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . route('product-sections.edit', $row->id) . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
    
    $status = ConstantHelper::STATUS;
    return view('procurement.product-section.index');
}

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $stations = Station::where('status', 'active')->get();
        return view('procurement.product-section.create', compact('status','stations'));
    }

    public function store(ProductSectionRequest $request)
    {
        $validatedData = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $parentUrl = ConstantHelper::PRODUCT_SECTION_SERVICE_ALIAS;
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

            $productSection = ProductSection::create([
                'name' => $validatedData['name'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'],
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
                'description' => $validatedData['description'] ?? null,
            ]);

            $details = $validatedData['details'] ?? [];
            foreach ($details as $detail) {
                if (!empty($detail['name'])) {
                    $productSection->details()->create([
                        'name' => $detail['name'],
                        'description' => $detail['description'] ?? null,
                        'station_id' => $detail['station_id'] ?? null,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $productSection,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while creating the product section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $productSection = ProductSection::with('details')->findOrFail($id);
        $status = ConstantHelper::STATUS;
        $stations = Station::where('status', 'active')->get();
        return view('procurement.product-section.edit', compact('productSection', 'status','stations'));
    }

    public function update(ProductSectionRequest $request, $id)
    {
        $validatedData = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $parentUrl = ConstantHelper::PRODUCT_SECTION_SERVICE_ALIAS;
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

            $productSection = ProductSection::findOrFail($id);
            $productSection->update([
                'name' => $validatedData['name'],
                'status' => $validatedData['status'],
                'description' => $validatedData['description'] ?? $productSection->description,
                'organization_id' => $validatedData['organization_id'],
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
            ]);

            $details = $validatedData['details'] ?? [];
            $newDetailIds = [];

            foreach ($details as $detail) {
                $detailId = $detail['id'] ?? null;

                if ($detailId) {
                    $existingDetail = $productSection->details()->find($detailId);
                    if ($existingDetail) {
                        $existingDetail->update([
                            'name' => $detail['name'],
                            'description' => $detail['description'] ?? $existingDetail->description,
                            'station_id' => $detail['station_id'] ?? $existingDetail->station_id,
                        ]);
                    }
                } else {
                    $newDetail = $productSection->details()->create([
                        'name' => $detail['name'],
                        'description' => $detail['description'] ?? null,
                        'station_id' => $detail['station_id'] ?? null,
                    ]);
                }

                $newDetailIds[] = $newDetail->id;
                
            }

            $productSection->details()->whereNotIn('id', $newDetailIds)->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $productSection,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while updating the product section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSectionDetail($id)
    {
        try {
            $sectionDetail = ProductSectionDetail::findOrFail($id);
            $result = $sectionDetail->deleteWithReferences();
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
            $productSection = ProductSection::findOrFail($id);
            $referenceTables = [
                'erp_product_section_details' => ['section_id'], 
            ];
            $result = $productSection->deleteWithReferences($referenceTables);
            
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
                'message' => 'An error occurred while deleting the product section: ' . $e->getMessage(),
            ], 500);
        }
    }
}
