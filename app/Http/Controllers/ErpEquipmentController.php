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
use App\Models\InspectionChecklistDetail;
use App\Models\InspectionChecklist;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ConstantHelper;
use App\Models\PlantMaintBom;
use App\Models\FixedAssetRegistration;

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
        $fixedAssetRegistration = FixedAssetRegistration::select('id', 'asset_name','asset_code')->get();
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $organization = Helper::getAuthenticatedUser()->organization;
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $dataTypes = ConstantHelper::DATA_TYPES;
        
        $user = Helper::getAuthenticatedUser();
        $userOrganizations = Helper::access_org();
        $userOrganizations = $userOrganizations->unique(function ($item) {
            return $item->organization->id;
        });
        $organizationId = Helper::getAuthenticatedUser()->organization_id;

        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $maintenanceBOM = PlantMaintBom::all(['id', 'bom_name as name']);

        $checklists = InspectionChecklist::where('type','maintenance')->get();

        $items = Item::get();
        $categories = Category::where('type', 'Equipment')->get();
        return view('equipment.create', compact('maintenanceBOM','series', 'organizationId', 'userOrganizations', 'locations', 'categories', 'maintenanceTypes', 'items', 'checklists', 'fixedAssetRegistration','dataTypes'));
    }

    public function store(ErpEquipmentRequest $request)
    {
        // dd($request->all());
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
                'asset_code_id' => $request->asset_code_id,
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
                            if (empty($check['checklist_id'])) {
                                continue;
                            }

                           

                            $checkListName = InspectionChecklist::where('id', $check['checklist_id'])->select('id','name','description','type')->first();

                            ErpEquipMaintenanceChecklist::create([
                                'erp_equip_maintenance_id' => $maintenance_detail_item->id,
                                'checklist_id' => $checkListName->id ?? null,
                                'name' => $checkListName->name,
                                'description' => $checkListName->description,
                                'type' => $checkListName->type,
                                'created_by' => $user->auth_user_id,
                                'checklist_detail'=>json_encode($check),
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

        

        $user = Helper::getAuthenticatedUser();
        $userOrganizations = Helper::access_org();
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


        $checklists = InspectionChecklist::where('type','maintenance')->get();
        

        $fixedAssetRegistration = FixedAssetRegistration::select('id', 'asset_name','asset_code')->get();
        $maintenanceDetails = ErpEquipMaintenanceDetail::where('erp_equipment_id', $equipment->id)->value('id');
       
        $checkListData = ErpEquipMaintenanceChecklist::where('erp_equip_maintenance_id', $maintenanceDetails)->select('id','checklist_detail')->get();
        $checkListIds = [];

        foreach($checkListData as $checkListId){
            $checkListId = json_decode($checkListId->checklist_detail);
            if(!empty($checkListId->checklist_detail_id)){
                $checkListIds[] = $checkListId->checklist_detail_id;
            }
           
        }
        
      
       

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
            'checklists',
            'fixedAssetRegistration',
            'checkListIds'
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

                           Log::info('Processing checklist item:', $check);

                            ErpEquipMaintenanceChecklist::create([
                                'erp_equip_maintenance_id' => $maintenance_detail_item->id,
                                'name' => $check['name'],
                                'description' => $check['description'] ?? null,
                                'type' => $check['type'] ?? null,
                                'created_by' => $user->auth_user_id,
                                'checklist_detail'=>json_encode($check),
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

    /**
     * Get fixed asset codes by book ID via AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFixedAssetCodesByBookId(Request $request)
    {
        try {
            $bookId = $request->book_id;
            
            if (!$bookId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book ID is required',
                    'data' => []
                ], 400);
            }

            // Import the FixedAssetRegistration model at the top if not already imported
            $assetCodes = \App\Models\FixedAssetRegistration::where('book_id', $bookId)
                        ->whereNotNull('asset_code')
                        ->where('asset_code', '!=', '')
                        ->select('id', 'asset_code', 'asset_name', 'status')
                        ->orderBy('asset_code')
                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Fixed asset codes fetched successfully',
                'data' => $assetCodes
            ], 200);

        } catch (\Exception $e) {
            Log::error("Get Fixed Asset Codes Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching asset codes',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get checklist details by checklist ID via AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChecklistDetails(Request $request)
    {
        // try {
            $checklistId = $request->checklist_id;
            
            if (!$checklistId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checklist ID is required',
                    'data' => []
                ], 400);
            }

            // Get checklist with its details
            $checklist = InspectionChecklistDetail::where('header_id', $checklistId)
                        ->get();
       

            if (!$checklist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checklist not found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Checklist details fetched successfully',
                'data' => [
                    'checklist' => $checklist,
                    'details' => $checklist
                ]
            ], 200);

        // } catch (\Exception $e) {
        //     Log::error("Get Checklist Details Error: " . $e->getMessage());
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'An error occurred while fetching checklist details',
        //         'data' => []
        //     ], 500);
        // }
    }

    /**
     * Search checklists via AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchChecklists(Request $request)
    {
        try {
            // Get all checklists (you can add search filters here if needed)
            $checklists = \App\Models\InspectionChecklist::select('id', 'name', 'description', 'type')
                        ->orderBy('name')
                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Checklists found successfully',
                'data' => $checklists
            ], 200);

        } catch (\Exception $e) {
            Log::error("Search Checklists Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching checklists',
                'data' => []
            ], 500);
        }
    }

}
