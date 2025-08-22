<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Http\Requests\ErpEquipmentRequest;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Http\Requests\ErpMaintenanceRequest;
use App\Models\Category;
use App\Models\ErpDefectType;
use App\Models\ErpEquipMaintenanceChecklist;
use App\Models\ErpEquipMaintenanceDetail;
use App\Models\ErpMaintenance;
use App\Models\ErpMaintenanceChecklistDetail;
use App\Models\ErpMaintenanceDefectDetail;
use App\Models\ErpMaintenanceHistory;
use App\Models\ErpMaintenanceType;
use App\Models\ErpEquipment;
use App\Models\InspectionChecklist;
use App\Models\Item;
use App\Models\OrganizationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErpMaintananceController extends Controller
{
    public function index()
    {
        $maintenances = ErpMaintenance::query()
            ->with(['equipment.category', 'book'])
            ->withCount('defectDetails', 'checklistDetails')
            ->orderBy('id', 'desc')
            ->get();

        return view('equipment.maintenance.index', compact('maintenances'));
    }
    
    public function create()
    {
        $parentURL = request()->segments()[0];
        
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        // dd($servicesBooks);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $userOrganizations = Helper::getAuthenticatedUser()->organization;
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $checklists = InspectionChecklist::where('type', ConstantHelper::MAINTENANCE_INSPECTION_CHECKLIST_TYPE)->get();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);

        $items = Item::get();
        $categories = Category::query()
            ->where('type', ConstantHelper::EQUIPMENT)
            ->orderBy('id', 'desc')
            ->get();
        //   dd($userOrganizations);  
        $equipments = ErpEquipment::query()
            ->with(['maintenanceDetails.checklists', 'spareParts', 'maintenanceDetails.maintenanceType'])
            ->whereIn('organization_id', $userOrganizations->pluck('id'))
            ->where('document_status', '!=', 'draft')
            ->get();

        $defectTypes = ErpDefectType::query()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();


        return view('equipment.maintenance.create', compact('userOrganizations', 'series', 'defectTypes', 'equipments', 'categories', 'maintenanceTypes','items', 'checklists'));
    }

    public function store(ErpMaintenanceRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $org = $user->organization;
            
            // Store Equipment
            $auth_user_id = $user->auth_user_id;
            $maintenance = ErpMaintenance::create([
                'organization_id' => $org->id,
                'group_id' => $org->group_id ?? null,
                'company_id' => $org->company_id ?? null,
                'category_id'       => $request->category,
                'equipment_id'      => $request->equipment,
                'doc_date'=> $request->doc_date,
                'upload_document'     => null,
                'final_remarks'       => $request->final_remarks,
                'book_id' => $request->book_id,
                'document_number' => $request->document_number,
                'doc_number_type' => $request->doc_number_type,
                'doc_prefix' => $request->doc_prefix,
                'doc_suffix' => $request->doc_suffix,
                'doc_no' => $request->doc_no,
                'document_status' => $request->status,
                'created_by' => $user->auth_user_id,
            ]);

            if ($maintenance->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($maintenance->book_id, $maintenance->id, 0, $request->remarks, null, 1, 'submit', 0, get_class($maintenance));
                $maintenance->document_status = $doc['approvalStatus'] ?? $maintenance->document_status;
                $maintenance->save();
            }

            // If document uploaded
            if ($request->hasFile('upload_document')) {
                $file = $request->file('upload_document');
                $path = $file->store('maintenance_documents', 'public');
                $maintenance->upload_document = $path;
                $maintenance->save();
            }

            foreach ($request->input('checklist_answers', []) as $index => $checklist) {
                $eqp_maintenance_checklist = ErpEquipMaintenanceChecklist::query()->find($index);
                ErpMaintenanceChecklistDetail::create([
                    'erp_maintenance_id' => $maintenance->id,
                    'erp_equip_maintenance_checklist_id' => $index,
                    'checklist_name'     => $checklist['name'] ?? null,
                    'checklist_answer'   => $eqp_maintenance_checklist->type === 'text' ? $checklist['text'] : ($checklist['checkbox'] ? 'yes' : 'no'),
                    'created_by'         => $auth_user_id,
                ]);
            }

            foreach ($request->input('defects', []) as $index => $defect) {
                if ( $index === 'custom_final' && empty($defect['deduct_type']) ) {
                    continue;
                }
                ErpMaintenanceDefectDetail::create([
                    'erp_maintenance_id' => $maintenance->id,
                    'erp_equip_sparepart_id' => $index === 'custom_final' ? null : $index,
                    'defect_type_id'     => $defect['deduct_type'] ?? null,
                    'priority'           => $defect['priority'] ?? null,
                    'due_date'           => $defect['due_date'] ?? null,
                    'description'        => $defect['description'] ?? null,
                    'created_by'         => $auth_user_id,
                ]);
            }

            DB::commit();
            
            $message = $request->status == 'draft' ? 'Maintenance saved as draft successfully' : 'Maintenance submitted successfully';
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $parentURL = request()->segments()[0];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }

        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $user = Helper::getAuthenticatedUser();
        

        $userOrganizations = Helper::access_org();

        $userOrganizations = $userOrganizations->unique(function ($item) {
            return $item->organization->id;
        });

        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
            $maintenance = ErpMaintenanceHistory::with([
                'equipment',
                'defectDetails.erpEquipSparepart',
                'checklistDetails.erpEquipMaintenanceChecklist'
            ])->where('source_id', $id)
                ->where('revision_number', $revNo)->firstOrFail();
        } else {
            $maintenance = ErpMaintenance::with([
                'equipment',
                'defectDetails.erpEquipSparepart',
                'checklistDetails.erpEquipMaintenanceChecklist'
            ])->findOrFail($id);

            $revNo = $maintenance->revision_number;

        }

        $checklists = InspectionChecklist::get();

        $userOrganizations = Helper::getAuthenticatedUser()->organization;

        $userType = Helper::userCheck();

        $buttons = Helper::actionButtonDisplay(
            $maintenance->book_id,
            $maintenance->document_status,
            $maintenance->id,
            0,
            $maintenance->approval_level,
            $maintenance->created_by ?? 0,
            $userType['type'],
            $revNo
        );
        // dd($buttons);

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$maintenance->document_status] ?? '';

        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $equipments = ErpEquipment::query()
            ->with(['maintenanceDetails.checklists', 'spareParts', 'maintenanceDetails.maintenanceType'])
            ->whereIn('organization_id', $userOrganizations->pluck('id'))
            ->where('document_status', '!=', 'draft')
            ->get();

        $categories = Category::where('type', 'Equipment')->get();
        $defectTypes = ErpDefectType::query()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();

        $approvalHistory = [];
        if (!empty($maintenance->book_id))
            $approvalHistory = Helper::getApprovalHistory($maintenance->book_id, $maintenance->id, $revNo, 0, $maintenance->created_by);

        return view('equipment.maintenance.edit', compact(
            'maintenance',
            'userOrganizations',
            'categories',
            'maintenanceTypes',
            'approvalHistory',
            'checklists',
            'series',
            'buttons',
            'docStatusClass',
            'equipments',
            'defectTypes'
        ));
    }

    public function update(ErpMaintenanceRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $maintenance = ErpMaintenance::findOrFail($id);
            // Update Equipment
            $maintenance->update([
                'category_id'       => $request->category,
                'equipment_id' => $request->equipment,
                'doc_date' => $request->doc_date,
                'final_remarks' => $request->final_remarks,
                'document_status' => $request->status,
            ]);

            if ($maintenance->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($maintenance->book_id, $maintenance->id, 0, $request->remarks, null, 1, 'submit', 0, get_class($maintenance));
                $maintenance->document_status = $doc['approvalStatus'] ?? $maintenance->document_status;
                $maintenance->save();
            }

            // If document uploaded
            if ($request->hasFile('upload_document')) {
                $file = $request->file('upload_document');
                $path = $file->store('maintenance_documents', 'public');
                $maintenance->upload_document = $path;
                $maintenance->save();
            }

            // Delete old checklist and defect details
            ErpMaintenanceChecklistDetail::where('erp_maintenance_id', $maintenance->id)->delete();
            ErpMaintenanceDefectDetail::where('erp_maintenance_id', $maintenance->id)->delete();

            // Save checklist answers
            foreach ($request->input('checklist_answers', []) as $index => $checklist) {
                $eqp_maintenance_checklist = ErpEquipMaintenanceChecklist::find($index);
                if($eqp_maintenance_checklist){
                    ErpMaintenanceChecklistDetail::create([
                        'erp_maintenance_id' => $maintenance->id,
                        'erp_equip_maintenance_checklist_id' => $index,
                        'checklist_name' => $checklist['name'] ?? null,
                        'checklist_answer' => $eqp_maintenance_checklist->type === 'text'
                            ? $checklist['text']
                            : ($checklist['checkbox'] ?? null ? 'yes' : 'no'),
                        'created_by' => $user->auth_user_id,
                    ]);
                }
            }

            // Save defect details
            foreach ($request->input('defects', []) as $index => $defect) {
                if ($index === 'custom_final' && empty($defect['deduct_type'])) {
                    continue;
                }
                ErpMaintenanceDefectDetail::create([
                    'erp_maintenance_id' => $maintenance->id,
                    'erp_equip_sparepart_id' => $index === 'custom_final' ? null : $index,
                    'defect_type_id' => $defect['deduct_type'] ?? null,
                    'priority' => $defect['priority'] ?? null,
                    'due_date' => $defect['due_date'] ?? null,
                    'description' => $defect['description'] ?? null,
                    'created_by' => $user->auth_user_id,
                ]);
            }
            DB::commit();

            $message = $request->status == 'draft' ? 'Equipment updated as draft successfully' : 'Equipment updated successfully';
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = ErpMaintenance::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments') ?? null;
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function amendment(Request $request, $id)
    {
        $maintenance = ErpMaintenance::find($id);
        if (!$maintenance) {
            return response()->json([
                "data" => [],
                "message" => "Maintenance not found.",
                "status" => 404,
            ]);
        }

        $revisionData = [
            [
                "model_type" => "header",
                "model_name" => "ErpMaintenance",
                "relation_column" => "",
            ],
            [
                "model_type" => "detail",
                "model_name" => "ErpMaintenanceChecklistDetail",
                "relation_column" => "erp_maintenance_id",
            ],
            [
                "model_type" => "detail",
                "model_name" => "ErpMaintenanceDefectDetail",
                "relation_column" => "erp_maintenance_id",
            ],
        ];

        $a = Helper::documentAmendment($revisionData, $id);
        DB::beginTransaction();
        try {
            if ($a) {
                Helper::approveDocument(
                    $maintenance->book_id,
                    $maintenance->id,
                    $maintenance->revision_number,
                    "Amendment",
                    $request->file("attachment") ?? null,
                    $maintenance->approval_level,
                    "amendment"
                );

                $maintenance->document_status = ConstantHelper::DRAFT;
                $maintenance->revision_number = $maintenance->revision_number + 1;
                $maintenance->revision_date = now();
                $maintenance->save();
            }

            DB::commit();
            return response()->json([
                "data" => [],
                "message" => "Amendment done!",
                "status" => 200,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Amendment Submit Error: " . $e->getMessage());
            return response()->json([
                "data" => [],
                "message" => "An unexpected error occurred. Please try again.",
                "status" => 500,
            ]);
        }
    }
}
