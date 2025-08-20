<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\ErpEquipmentRequest;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\Category;
use App\Models\ErpEquipMaintenanceChecklist;
use App\Models\ErpEquipMaintenanceDetail;
use App\Models\ErpMaintenanceType;
use App\Models\ErpEquipment;
use App\Models\ErpEquipmentHistory;

use App\Models\InspectionChecklist;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ConstantHelper;
use App\Models\PlantMaintBom;

class ErpEquipmentController extends Controller
{
    public function index()
    {
        $equipments = ErpEquipment::with(['organization', 'location', 'spareParts', 'maintenanceDetails.checklists'])->get();
        return view('equipment.index', compact('equipments'));
    }
    public function create()
    {
        $parentURL = request()->segments()[0];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $organization = Helper::getAuthenticatedUser()->organization;
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $userOrganizations = Helper::getAuthenticatedUser()->access_rights_org;
        $userOrganizations = $userOrganizations->unique(function ($item) {
            return $item->organization->id;
        });
        $organizationId = Helper::getAuthenticatedUser()->organization_id;

        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $maintenanceBOM = PlantMaintBom::all(['id', 'bom_name as name']);

        $checklists = InspectionChecklist::get();

        $items = Item::get();
        $categories = Category::where('type', 'Equipment')->get();
        return view('equipment.create', compact('maintenanceBOM','series', 'organizationId', 'userOrganizations', 'locations', 'categories', 'maintenanceTypes', 'items', 'checklists'));
    }

    public function store(ErpEquipmentRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $org = $user->organization;
            $parentUrl = ConstantHelper::EQPT;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
          
            $book_id = null;
            if ($services && $services['current_book']) {
                if (isset($services['current_book'])) {
                    $book = $services['current_book'];
                    $book_id = $services['current_book']->id;
                }
            }

            // Store Equipment
            $equipment = ErpEquipment::create([
                'organization_id' => $request->organization_id,
                'group_id' => $org->group_id ?? null,
                'company_id' => $org->company_id ?? null,
                'category_id' => $request->category_id,
                'location_id' => $request->location_id,
                'name' => $request->name,
                'alias' => $request->alias,
                'description' => $request->description,
                'upload_document' => null, // Will handle file upload below
                'final_remarks' => $request->final_remarks,
                'book_id' => $book_id, // Or get from elsewhere
                'document_status' => $request->status, // From request
                'created_by' => $user->auth_user_id,
            ]);
            if ($equipment->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($equipment->book_id, $equipment->id, 0, $request->remarks, null, 1, 'submit', 0, get_class($equipment));
                $equipment->document_status = $doc['approvalStatus'] ?? $equipment->document_status;
                $equipment->save();
            }

            // If document uploaded
            if ($request->hasFile('upload_document')) {
                $file = $request->file('upload_document');
                $path = $file->store('equipment_documents', 'public');
                $equipment->upload_document = $path;
                $equipment->save();
            }

            // Maintenance Details
            if ($request->has('maintenance') && is_array($request->maintenance)) {
                foreach ($request->maintenance as $rowId => $mRow) {
                    // Skip rows without required fields
                    if (empty($mRow['type']) || empty($mRow['frequency'])) {
                        continue;
                    }

                    $maintenance_detail_item = ErpEquipMaintenanceDetail::create([
                        'erp_equipment_id' => $equipment->id,
                        'maintenance_type_id' => $mRow['type'],
                        'frequency' => $mRow['frequency'],
                        'time' => $mRow['time'] ?? null,
                        'start_date' => $mRow['date'] ?? null,
                        'maintenance_bom_id' => $mRow['bom'] ?? null,
                        'created_by' => $user->auth_user_id,
                    ]);

                    // Checklist for this maintenance
                    if (!empty($mRow['checklists']) && is_array($mRow['checklists'])) {
                        foreach ($mRow['checklists'] as $check) {
                            // Skip if no ID or name
                            if (empty($check['id']) && empty($check['name'])) {
                                continue;
                            }

                            ErpEquipMaintenanceChecklist::create([
                                'erp_equip_maintenance_id' => $maintenance_detail_item->id,
                                'checklist_id' => $check['id'] ?? null,
                                'name' => $check['name'],
                                'description' => $check['description'] ?? null,
                                'type' => $check['type'] ?? null,
                                'created_by' => $user->auth_user_id,
                            ]);
                        }
                    }
                }
            }

            // Spare Parts
            if ($request->has('spareparts') && is_array($request->spareparts)) {
                foreach ($request->spareparts as $rowId => $sRow) {
                    // Skip rows without required fields
                    if (empty($sRow['item_code']) || empty($sRow['item_name'])) {
                        continue;
                    }

                    // Parse attributes JSON if it exists
                    $attributes = [];
                    if (!empty($sRow['attributes'])) {
                        try {
                            if (is_string($sRow['attributes'])) {
                                $attributes = json_decode($sRow['attributes'], true) ?? [];
                            } else {
                                $attributes = $sRow['attributes'];
                            }
                        } catch (\Exception $e) {
                            // If JSON parsing fails, use empty array
                            $attributes = [];
                        }
                    }

                    $equipment->spareParts()->create([
                        'item_code' => $sRow['item_code'],
                        'item_name' => $sRow['item_name'],
                        'attributes' => json_encode($attributes),
                        'uom' => $sRow['uom'] ?? '',
                        'qty' => $sRow['qty'] ?? 0,
                        'created_by' => $user->auth_user_id,
                    ]);
                }
            }

            DB::commit();

            $message = $request->status == 'draft' ? 'Equipment saved as draft successfully' : 'Equipment submitted successfully';
            return redirect()->route("equipment.index")->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Request $r, $id)
    {
        $parentURL = request()->segments()[0];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $organization = Helper::getAuthenticatedUser()->organization;
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        

        $userOrganizations = Helper::getAuthenticatedUser()->access_rights_org;
        $userOrganizations = $userOrganizations->unique(function ($item) {
            return $item->organization->id;
        });
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
            $equipment = ErpEquipmentHistory::with([
                'spareParts',
                'maintenanceDetails.checklists'
            ])->where('source_id', $id)
                ->where('revision_number', $revNo)->firstOrFail();
        } else {
            $equipment = ErpEquipment::with([
                'spareParts',
                'maintenanceDetails.checklists'
            ])->findOrFail($id);
            $revNo = $equipment->revision_number;
            
        }

        $userType = Helper::userCheck();

        $buttons = Helper::actionButtonDisplay(
            $equipment->book_id,
            $equipment->document_status,
            $equipment->id,
            0,
            $equipment->approval_level,
            $equipment->created_by ?? 0,
            $userType['type'],
            $revNo
        );
        
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$equipment->document_status] ?? '';
        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $maintenanceBOM = PlantMaintBom::all(['id', 'bom_name as name']);
        $items = Item::get();
       $categories = Category::where('type', 'Equipment')->get();
        $approvalHistory = [];
        if (!empty($equipment->book_id))
            $approvalHistory = Helper::getApprovalHistory($equipment->book_id, $equipment->id, $revNo, 0, $equipment->created_by);


        $checklists = InspectionChecklist::get();

        return view('equipment.edit', compact(
            'equipment',
            'series',
            'userOrganizations',
            'locations',
            'categories',
            'maintenanceTypes',
            'maintenanceBOM',
            'approvalHistory',
            'buttons',
            'docStatusClass',
            'items',
            'checklists'
        ));
    }

    public function update(ErpEquipmentRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $equipment = ErpEquipment::findOrFail($id);

            // Update Equipment
            $equipment->update([
                'organization_id' => $request->organization_id,
                'category_id' => $request->category_id,
                'location_id' => $request->location_id,
                'name' => $request->name,
                'alias' => $request->alias,
                'description' => $request->description,
                'final_remarks' => $request->final_remarks,
                'document_status' => $request->status,
            ]);

            if ($equipment->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($equipment->book_id, $equipment->id, $equipment->revision_number, $request->remarks, null, 1, 'submit', 0, get_class($equipment));
                $equipment->document_status = $doc['approvalStatus'] ?? $equipment->document_status;
                $equipment->save();
            }

            // If document uploaded
            if ($request->hasFile('upload_document')) {
                $file = $request->file('upload_document');
                $path = $file->store('equipment_documents', 'public');
                $equipment->upload_document = $path;
                $equipment->save();
            }

            // Remove old maintenance details and checklists
            $equipment->maintenanceDetails()->each(function ($detail) {
                $detail->checklists()->delete();
            });
            $equipment->maintenanceDetails()->delete();

            // Maintenance Details
            if ($request->has('maintenance') && is_array($request->maintenance)) {
                foreach ($request->maintenance as $rowId => $mRow) {
                    if (empty($mRow['type']) || empty($mRow['frequency'])) {
                        continue;
                    }

                    $maintenance_detail_item = ErpEquipMaintenanceDetail::create([
                        'erp_equipment_id' => $equipment->id,
                        'maintenance_type_id' => $mRow['type'],
                        'frequency' => $mRow['frequency'],
                        'start_date' => $mRow['date'] ?? null,
                        'maintenance_bom_id' => $mRow['bom'] ?? null,
                        'time' => $mRow['time'] ?? null,
                    ]);

                    if (!empty($mRow['checklists']) && is_array($mRow['checklists'])) {
                        foreach ($mRow['checklists'] as $check) {
                            if (empty($check['id']) && empty($check['name'])) {
                                continue;
                            }

                            ErpEquipMaintenanceChecklist::create([
                                'erp_equip_maintenance_id' => $maintenance_detail_item->id,
                                'checklist_id' => $check['id'] ?? null,
                                'name' => $check['name'],
                                'description' => $check['description'] ?? null,
                                'type' => $check['type'] ?? null,
                                'created_by' => $user->auth_user_id,
                            ]);
                        }
                    }
                }
            }

            // Remove old spare parts
            $equipment->spareParts()->delete();

            // Spare Parts
            if ($request->has('spareparts') && is_array($request->spareparts)) {
                foreach ($request->spareparts as $rowId => $sRow) {
                    if (empty($sRow['item_code']) || empty($sRow['item_name'])) {
                        continue;
                    }

                    $equipment->spareParts()->create([
                        'item_code' => $sRow['item_code'],
                        'item_name' => $sRow['item_name'],
                        'uom' => $sRow['uom'] ?? '',
                        'qty' => $sRow['qty'] ?? 0,
                        'created_by' => $user->auth_user_id,
                    ]);
                }
            }

            DB::commit();

            $message = $request->status == 'draft' ? 'Equipment updated as draft successfully' : 'Equipment updated successfully';
            return redirect()->route("equipment.index")->with('success', $message);
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
            $doc = ErpEquipment::find($request->id);
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
        $eqpt_id = ErpEquipment::find($id);
        if (!$eqpt_id) {
            return response()->json([
                "data" => [],
                "message" => "Equipment not found.",
                "status" => 404,
            ]);
        }

        $revisionData = [
            [
                "model_type" => "header",
                "model_name" => "ErpEquipment",
                "relation_column" => "",
            ],
            [
                "model_type" => "detail",
                "model_name" => "ErpEquipSparepartDetail",
                "relation_column" => "erp_equipment_id",
            ],
            [
                "model_type" => "detail",
                "model_name" => "ErpEquipMaintenanceDetail",
                "relation_column" => "erp_equipment_id",
            ],
            [
                "model_type" => "sub_detail",
                "model_name" => "ErpEquipMaintenanceChecklist",
                "relation_column" => "erp_equip_maintenance_id",
            ],
        ];

        $a = Helper::documentAmendment($revisionData, $id);
        DB::beginTransaction();
        try {
            if ($a) {
                Helper::approveDocument(
                    $eqpt_id->book_id,
                    $eqpt_id->id,
                    $eqpt_id->revision_number,
                    "Amendment",
                    $request->file("attachment") ?? null,
                    $eqpt_id->approval_level,
                    "amendment"
                );

                $eqpt_id->document_status = ConstantHelper::DRAFT;
                $eqpt_id->revision_number = $eqpt_id->revision_number + 1;
                $eqpt_id->revision_date = now();
                $eqpt_id->save();
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
