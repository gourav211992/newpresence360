<?php

namespace App\Http\Controllers;

use App\Models\DynamicField;
use App\Models\DynamicFieldDetail;
use App\Models\DynamicFieldDetailValue;
use App\Http\Requests\DynamicFieldRequest; 
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Yajra\DataTables\DataTables;

class DynamicFieldController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $query = DynamicField::query();
            $dynamicFields = $query->orderBy('id', 'desc');

            return DataTables::of($dynamicFields)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill badge-light-' . ($row->status === 'active' ? 'success' : 'danger') . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('dynamic-fields.edit', $row->id);
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

        return view('dynamic-field.index');
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $dataTypes = ConstantHelper::DATA_TYPES;
        return view('dynamic-field.create', compact('status','dataTypes'));
    }

    // Use DynamicFieldRequest for validation
    public function store(DynamicFieldRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
    
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $parentUrl = ConstantHelper::DYNAMIC_FIELD_ALIAS;
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
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
            $dynamicField = DynamicField::create($validatedData);
            if ($request->has('field_details')) {
                $fieldDetails = $request->input('field_details');
            
                foreach ($fieldDetails as $detail) {
                    if (!empty($detail['name'])) {
                        $fieldDetail = $dynamicField->details()->create([
                            'name' => $detail['name'],
                            'description' => $detail['description'],
                            'data_type' => $detail['data_type'],
                            'mandatory' => $detail['mandatory']
                        ]);
                        
                        if (isset($detail['value']) && !empty($detail['value'])) {
                            $values = explode(',', $detail['value']);
                            foreach ($values as $value) {
                                $trimmedValue = trim($value); 
                                DynamicFieldDetailValue::create([
                                    'dynamic_field_detail_id' => $fieldDetail->id, 
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
                'data' => $dynamicField,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while creating the dynamic field',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function edit($id)
    {
        $dynamicField = DynamicField::findOrFail($id);
        $status = ConstantHelper::STATUS;
        $dataTypes = ConstantHelper::DATA_TYPES;
        return view('dynamic-field.edit', compact('dynamicField', 'status','dataTypes'));
    }

    public function update(DynamicFieldRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $dynamicField = DynamicField::findOrFail($id);
    
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $parentUrl = ConstantHelper::DYNAMIC_FIELD_ALIAS;
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
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
    
            $dynamicField->update($validatedData);

            if ($request->has('field_details')) {
                $fieldDetails = $request->input('field_details');
                $newDetailIds = [];
    
                foreach ($fieldDetails as $detail) {
                    $detailId = $detail['id'] ?? null;
    
                    if ($detailId) {
                        $existingDetail = $dynamicField->details()->find($detailId);
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
                          
                            DynamicFieldDetailValue::where('dynamic_field_detail_id', $existingDetail->id)
                                ->whereIn('value', $valuesToDelete)
                                ->delete();

                            $valuesToAdd = array_diff($newValues, $existingValues);
                             foreach ($valuesToAdd as $value) {
                                    DynamicFieldDetailValue::create([
                                        'dynamic_field_detail_id' => $existingDetail->id,
                                        'value' => $value,
                                    ]);
                                }
                        }
                    } else {
                        $newDetail = $dynamicField->details()->create([
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
                                DynamicFieldDetailValue::create([
                                    'dynamic_field_detail_id' => $newDetail->id,
                                    'value' => $trimmedValue,
                                ]);
                            }
                        }
                    }
                }
    
                $dynamicField->details()->whereNotIn('id', $newDetailIds)->delete();
            } else {
                $dynamicField->details()->delete();
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $dynamicField,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while updating the dynamic field',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteFieldDetail($id)
    {
        try {
            $fieldDetail = DynamicFieldDetail::findOrFail($id);
            $result = $fieldDetail->deleteWithReferences();

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
            $dynamicField = DynamicField::findOrFail($id);
            $referenceTables = [
                'erp_dynamic_field_details' => ['header_id'],
            ];
            $result = $dynamicField->deleteWithReferences($referenceTables);

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

    public function dynamicValueDestroy($id)
    {
        try {
            $dynamicFieldValue = DynamicFieldDetailValue::findOrFail($id);
           
            $result = $dynamicFieldValue->deleteWithReferences();

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
    public function getDynamicFieldDetails(Request $request)
    {
        try {
            $dynamicFieldIds = $request -> dynamic_field_ids ?? [];
            $dynamicFields = DynamicFieldDetail::select('id', 'header_id', 'name', 'data_type') -> whereIn('header_id', $dynamicFieldIds) 
            -> whereHas('header', function ($headerQuery) {
               $headerQuery->where('status', ConstantHelper::ACTIVE);
            }) -> get();
            return response() -> json([
                'status' => 'success',
                'message' => 'Data Found',
                'data' => $dynamicFields
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'error',
                'message' => $ex -> getMessage()
            ], 500);
        }
    }
}
