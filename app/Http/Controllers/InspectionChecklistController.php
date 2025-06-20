<?php

namespace App\Http\Controllers;

use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistDetail;
use App\Models\InspectionChecklistDetailValue;
use App\Http\Requests\InspectionChecklistRequest; 
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Yajra\DataTables\DataTables;

class InspectionChecklistController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $query = InspectionChecklist::withDefaultGroupCompanyOrg();
            $inspectionChecklists = $query->orderBy('id', 'desc');

            return DataTables::of($inspectionChecklists)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill badge-light-' . ($row->status === 'active' ? 'success' : 'danger') . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('inspection-checklists.edit', $row->id);
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

        return view('inspection-checklist.index');
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $dataTypes = ConstantHelper::DATA_TYPES;
        return view('inspection-checklist.create', compact('status','dataTypes'));
    }

    // Use InspectionChecklistRequest for validation
    public function store(InspectionChecklistRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
    
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $parentUrl = ConstantHelper::INSPECTION_CHECKLIST_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
    
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
            $inspectionChecklist = InspectionChecklist::create($validatedData);
            if ($request->has('checklist_details')) {
                $checklistDetails = $request->input('checklist_details');
            
                foreach ($checklistDetails as $detail) {
                    if (!empty($detail['name'])) {
                        $checklistDetail = $inspectionChecklist->details()->create([
                            'name' => $detail['name'],
                            'description' => $detail['description'],
                            'data_type' => $detail['data_type'],
                            'mandatory' => $detail['mandatory']
                        ]);
                        
                        if (isset($detail['value']) && !empty($detail['value'])) {
                            $values = explode(',', $detail['value']);
                            foreach ($values as $value) {
                                $trimmedValue = trim($value); 
                                InspectionChecklistDetailValue::create([
                                    'inspection_checklist_detail_id' => $checklistDetail->id, 
                                    'value' => $trimmedValue, 
                                ]);
                            }
                        }
                    }
                }
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $inspectionChecklist,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while creating the inspection checklist',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function edit($id)
    {
        $inspectionChecklist = InspectionChecklist::findOrFail($id);
        $status = ConstantHelper::STATUS;
        $dataTypes = ConstantHelper::DATA_TYPES;
        return view('inspection-checklist.edit', compact('inspectionChecklist', 'status','dataTypes'));
    }

    public function update(InspectionChecklistRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $inspectionChecklist = InspectionChecklist::findOrFail($id);
    
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $parentUrl = ConstantHelper::INSPECTION_CHECKLIST_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
    
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
    
            $inspectionChecklist->update($validatedData);

            if ($request->has('checklist_details')) {
                $checklistDetails = $request->input('checklist_details');
                $newDetailIds = [];
    
                foreach ($checklistDetails as $detail) {
                    $detailId = $detail['id'] ?? null;
    
                    if ($detailId) {
                        $existingDetail = $inspectionChecklist->details()->find($detailId);
                        if ($existingDetail) {
                            $existingDetail->update([
                                'name' => $detail['name'],
                                'description' => $detail['description'],
                                'data_type' => $detail['data_type'],
                                'mandatory' => $detail['mandatory']
                            ]);
                            $newDetailIds[] = $detailId;

                            $existingValues = $existingDetail->values()->pluck('value')->toArray();

                            $newValues = isset($detail['value']) && !empty($detail['value']) ? array_map('trim', explode(',', $detail['value'])) : [];
                          
                            $valuesToDelete = array_diff($existingValues, $newValues);
                          
                            InspectionChecklistDetailValue::where('inspection_checklist_detail_id', $existingDetail->id)
                                ->whereIn('value', $valuesToDelete)
                                ->delete();

                            $valuesToAdd = array_diff($newValues, $existingValues);
                             foreach ($valuesToAdd as $value) {
                                    InspectionChecklistDetailValue::create([
                                        'inspection_checklist_detail_id' => $existingDetail->id,
                                        'value' => $value,
                                    ]);
                                }
                        }
                    } else {
                        $newDetail = $inspectionChecklist->details()->create([
                            'name' => $detail['name'],
                            'description' => $detail['description'],
                            'data_type' => $detail['data_type'],
                            'mandatory' => $detail['mandatory']
                        ]);
                        $newDetailIds[] = $newDetail->id;

                        if (isset($detail['value']) && !empty($detail['value'])) {
                            $values = explode(',', $detail['value']);
                            foreach ($values as $value) {
                                $trimmedValue = trim($value);
                                InspectionChecklistDetailValue::create([
                                    'inspection_checklist_detail_id' => $newDetail->id,
                                    'value' => $trimmedValue,
                                ]);
                            }
                        }
                    }
                }
    
                $inspectionChecklist->details()->whereNotIn('id', $newDetailIds)->delete();
            } else {
                $inspectionChecklist->details()->delete();
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while updating the dynamic field',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteChecklistDetail($id)
    {
        try {
            $inspectionChecklistDetail = inspectionChecklistDetail::findOrFail($id);
            $result = $inspectionChecklistDetail->deleteWithReferences();

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
            $inspectionChecklist = InspectionChecklist::findOrFail($id);
            $referenceTables = [
                'erp_inspection_checklist_details' => ['header_id'],
            ];
            $result = $inspectionChecklist->deleteWithReferences($referenceTables);

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

    public function deleteChecklistValue($id)
    {
        try {
            $checklistValue = InspectionChecklistDetailValue::findOrFail($id);
           
            $result = $checklistValue->deleteWithReferences();

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

}
