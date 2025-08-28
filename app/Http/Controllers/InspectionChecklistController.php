<?php

namespace App\Http\Controllers;

use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistDetail;
use App\Models\InspectionChecklistDetailValue;
use App\Models\UploadInspectionChecklist;
use App\Http\Requests\InspectionChecklistRequest; 
use App\Imports\InspectionChecklistImport;
use Maatwebsite\Excel\Facades\Excel; 
use App\Mail\ImportComplete;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Yajra\DataTables\DataTables;
use App\Exports\InspectionChecklistExport;
use App\Exports\FailedInspectionChecklistExport;

class InspectionChecklistController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
        $currentUrlSegment = request()->segment(1);
        if ($request->ajax()) {
            $query = InspectionChecklist::query();
            if ($currentUrlSegment === 'maintenance-inspection-checklists') {
                $query->where('type', ConstantHelper::MAINTENANCE_INSPECTION_CHECKLIST_TYPE);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('type')
                    ->orWhere('type', ConstantHelper::ITEM_INSPECTION_CHECKLIST_TYPE);
                });
            }
            $inspectionChecklists = $query->orderBy('id', 'desc');

            return DataTables::of($inspectionChecklists)
                ->addIndexColumn()
                    ->addColumn('status', function ($row) {
                        return '<span class="badge rounded-pill badge-light-' . ($row->status === 'active' ? 'success' : 'danger') . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                    })
                   ->addColumn('action', function ($row) use ($currentUrlSegment) {
                    if ($currentUrlSegment === 'maintenance-inspection-checklists') {
                        $editUrl = route('maintenance-inspection-checklists.edit', $row->id);
                    } else {
                        $editUrl = route('inspection-checklists.edit', $row->id);
                    }
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

        return view('inspection-checklist.index',compact('currentUrlSegment'));
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $currentUrlSegment = request()->segment(1);
        $dataTypes = ConstantHelper::DATA_TYPES;
       return view('inspection-checklist.create', [
            'status' => $status,
            'dataTypes' => $dataTypes,
            'currentUrlSegment'=>$currentUrlSegment
        ]);
    }

    public function store(InspectionChecklistRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
    
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $currentUrlSegment = $request->input('current_url_segment');
           if ($currentUrlSegment === 'maintenance-inspection-checklists') {
            $validatedData['type'] = ConstantHelper::MAINTENANCE_INSPECTION_CHECKLIST_TYPE;
            } else {
                $validatedData['type'] = ConstantHelper::ITEM_INSPECTION_CHECKLIST_TYPE;
            }
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
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
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
        $currentUrlSegment = request()->segment(1);
        $dataTypes = ConstantHelper::DATA_TYPES;
        return view('inspection-checklist.edit', [
            'inspectionChecklist' => $inspectionChecklist,
            'status' => $status,
            'currentUrlSegment'=>$currentUrlSegment,
            'dataTypes' => $dataTypes,
        ]);
    }

    public function update(InspectionChecklistRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $inspectionChecklist = InspectionChecklist::findOrFail($id);
    
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $currentUrlSegment = $request->input('current_url_segment');
            if ($currentUrlSegment === 'maintenance-inspection-checklists') {
                $validatedData['type'] = ConstantHelper::MAINTENANCE_INSPECTION_CHECKLIST_TYPE;
            } else {
                $validatedData['type'] = ConstantHelper::ITEM_INSPECTION_CHECKLIST_TYPE;
            }
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
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
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

    public function showImportForm()
    {
        return view('inspection-checklist.import');
    }

   public function import(Request $request)
    {
       $user = Helper::getAuthenticatedUser();

        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:30720',
            ]);

            if (!$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }

            $file = $request->file('file');

            //Check excel file is correct or not
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(filename: $file);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file format is incorrect or corrupted. Please upload a valid Excel file.',
                ], 400);
            }

            $sheet = $spreadsheet->getActiveSheet();
            $rowCount = $sheet->getHighestRow() - 1;

            if ($rowCount > 10000) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file contains more than 10000 items. Please upload a file with 10000 or fewer items.',
                ], 400);
            }
            if ($rowCount < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.',
                ], 400);
            }
             // Remove previous uploads for this user 
            $deleteQuery = UploadInspectionChecklist::where('user_id', $user->id);
            $deleteQuery->delete();

            // Import file
            $import = new InspectionChecklistImport();
            Excel::import($import, $request->file('file'));

            $successfulItems = $import->getSuccessful();
            $failedItems = $import->getFailed();

            $status = count($failedItems) > 0 ? 'failure' : 'success';
            $message = count($failedItems) > 0
                ? 'Checklist import completed with some failures.'
                : 'Checklists imported successfully.';

            // Optional: send mail
            $mailData = [
                'modelName' => 'Inspection Checklist',
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
                'export_successful_url' => route('inspection-checklists.export.successful'),
                'export_failed_url' => route('inspection-checklists.export.failed'),
            ];

            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new ImportComplete($mailData));
                } catch (\Exception $e) {
                    $message .= " However, there was an error sending the email notification.";
                }
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid Excel file (.xlsx/.xls) up to 30MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import checklists: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export all successful rows for the logged-in user
     */
    public function exportSuccessful()
    {
       $user = Helper::getAuthenticatedUser();

        $uploadRows = UploadInspectionChecklist::where('status', 'Success')
            ->where('user_id', $user->id)
            ->get();

        return Excel::download(
            new InspectionChecklistExport($uploadRows),
            "successful-checklists.xlsx"
        );
    }

    /**
     * Export all failed rows for the logged-in user
     */
    public function exportFailed()
    {
       $user = Helper::getAuthenticatedUser();

        $failedRows = UploadInspectionChecklist::where('status', 'Failed')
            ->where('user_id', $user->id)
            ->get();

        return Excel::download(
            new FailedInspectionChecklistExport($failedRows),
            "failed-checklists.xlsx"
        );
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
