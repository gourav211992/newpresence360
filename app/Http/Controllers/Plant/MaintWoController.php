<?php

namespace App\Http\Controllers\Plant;

use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ErpAttribute;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\PlantMaintWo;
use App\Models\PlantMaintBom;
use App\Models\DefectNotification;
use App\Models\ErpEquipMaintenanceDetail;
use App\Models\ErpEquipment;
use App\Models\ErpMaintenanceType;
use App\Models\ErpDefectType;
use App\Models\ErpItem;
use Carbon\Carbon;
use App\Models\StockLedger;
use App\Models\ErpEquipMaintenanceChecklist;
use Exception;
use Illuminate\Support\Facades\DB;

class MaintWoController extends Controller
{
    public function index()
    {
        $data = PlantMaintWo::select('id', 'equipment_details','document_number','document_date','document_status','book_id')
            ->get()
            ->map(function ($row) {
                $details = $row->equipment_details;

                if (is_string($details)) {
                    $details = json_decode($details, true);
                }

                // Default values
                $row->equipment_category = $details['equipment_category'] ?? null;
                $row->equipment_defect_type = $details['equipment_defect_type'] ?? null;

                $row->equipment_name = null;
                if (!empty($details['equipment_id'])) {
                    $row->equipment_name = ErpEquipment::where('id', $details['equipment_id'])
                        ->value('name');
                }

                return $row;
            });

        return view('plant.maint_wo.index', compact('data'));
    }


    public function show(Request $request, string $id)
    {
        $data = PlantMaintWo::find($id);
        
        // Enrich spare parts with complete attribute structure including values_data
        if (!empty($data->spare_parts)) {
            $sparePartsData = json_decode($data->spare_parts, true);
            $enrichedSpareParts = [];
            
            foreach ($sparePartsData as $sparePart) {
                $enrichedSparePart = $sparePart;
                
                // Enrich item_attributes with complete structure for attribute modal
                if (isset($sparePart['item_id'])) {
                    $item = Item::with(['itemAttributes'])->find($sparePart['item_id']);
                    if ($item && $item->itemAttributes) {
                        $processedAttributes = [];
                        foreach ($item->itemAttributes as $attribute) {
                            $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                                ->select('id', 'value')
                                ->where('status', 'active')
                                ->get();

                            $processedAttributes[] = [
                                'id' => $attribute->id,
                                'group_name' => $attribute->group?->name,
                                'values_data' => $attributeValueData,
                                'attribute_group_id' => $attribute->attribute_group_id,
                            ];
                        }
                        $enrichedSparePart['item_attributes'] = json_encode($processedAttributes);
                    }
                }
                
                // Also enrich existing attribute data with value names for display
                if (isset($sparePart['attribute']) && !empty($sparePart['attribute'])) {
                    $attributeData = json_decode($sparePart['attribute'], true);
                    
                    if (is_array($attributeData)) {
                        foreach ($attributeData as &$attr) {
                            if (isset($attr['value_id']) && isset($attr['item_attribute_id'])) {
                                // Get item attribute for group name
                                $itemAttribute = \App\Models\ErpItemAttribute::with('group')->find($attr['item_attribute_id']);
                                // Get attribute value for value name
                                $attributeValue = \App\Models\ErpAttribute::find($attr['value_id']);
                                
                                if ($itemAttribute && $attributeValue) {
                                    $attr['name'] = $itemAttribute->group->name ?? 'N/A';
                                    $attr['value'] = $attributeValue->value ?? 'N/A';
                                }
                            }
                        }
                        
                        // Update the attribute field with enriched data
                        $enrichedSparePart['attribute'] = json_encode($attributeData);
                    }
                }
                $enrichedSpareParts[] = $enrichedSparePart;
            }
            $data->spare_parts = json_encode($enrichedSpareParts);
        }
        
        $currNumber = $request->has('revisionNumber');

        $parentURL = "plant_maint-wo";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }

        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;

        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $id,
            0,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );

        $revNo = $request->has('revisionNumber') ? intval($request->revisionNumber) : $data->revision_number;

        $approvalHistory = Helper::getApprovalHistory(
            $data->book_id,
            $id,
            $revNo,
            0,
            $data->created_by
        );

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';

        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get()
            ->map(function ($item) {
                $itemAttributes = $item->itemAttributes ?? [];
                $processedData = collect($itemAttributes)->map(function ($attribute) {
                    $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                        ->select('id', 'value')
                        ->where('status', 'active')
                        ->get();

                    return [
                        'id' => $attribute->id,
                        'group_name' => $attribute->group?->name,
                        'values_data' => $attributeValueData,
                        'attribute_group_id' => $attribute->attribute_group_id,
                    ];
                });

                return [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'uom_name' => optional($item->uom)->name,
                    'uom_id' => optional($item->uom)->id,
                    'item_attributes' => $processedData,
                ];
            });

        $locations = InventoryHelper::getAccessibleLocations();
        $defectTypes = ErpDefectType::select('id', 'name')->get();
        $equipments = ErpEquipment::select('id', 'name')->get();

        $maintenanceTypesByEquipment = [];
        $equipmentMaintenanceDetails = ErpEquipMaintenanceDetail::with(['equipment', 'maintenanceType'])
            ->get()
            ->groupBy('erp_equipment_id');

        foreach ($equipmentMaintenanceDetails as $equipmentId => $details) {
            $maintenanceTypes = $details->pluck('maintenanceType')
                ->filter()
                ->unique('id')
                ->map(fn($type) => ['id' => $type->id, 'name' => $type->name])
                ->values();

            $maintenanceTypesByEquipment[$equipmentId] = $maintenanceTypes;
        }

        return view('plant.maint_wo.show', compact(
            'series',
            'items',
            'data',
            'buttons',
            'docStatusClass',
            'revision_number',
            'currNumber',
            'approvalHistory',
            'locations',
            'maintenanceTypesByEquipment',
            'defectTypes',
            'equipments'
        ));
    }

    public function create()
    {
        $parentURL = "plant_maint-wo";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }

        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();
        

        
        foreach ($items as $item) {
            $itemAttributes = ItemAttribute::where('item_id', $item->id)->get();
            $processedData = [];
            foreach ($itemAttributes as $attribute) {
                $attribute->group_name = $attribute->group?->name;
               
                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                    ->select('id', 'value')
                    ->where('status', 'active')
                    ->get();

                $processedData[] = [
                    'id' => $attribute->id,
                    'group_name' => $attribute->group_name,
                    'values_data' => $attributeValueData,
                    'attribute_group_id' => $attribute->attribute_group_id,
                ];
            }

            $item->attributes = collect($processedData);
        }

        $items = $items->map(function ($item) {
            $confirmedStock = StockLedger::query()
                ->selectRaw("
                    SUM(
                        CASE 
                            WHEN document_status IN ('approved', 'approval_not_required', 'posted') 
                            THEN receipt_qty - reserved_qty
                            ELSE 0
                        END
                    ) as confirmed_stock
                ")
                ->where('item_code', $item->item_code) // yaha fix kiya
                ->value('confirmed_stock');
        
            return [
                'id'              => $item->id,
                'item_code'       => $item->item_code,
                'item_name'       => $item->item_name,
                'uom_name'        => optional($item->uom)->name,
                'uom_id'          => optional($item->uom)->id,
                'item_attributes' => $item->attributes,
                'available_stock' => $confirmedStock ?? 0, // agar null ho to 0
            ];
        });

        

        
        $locations = InventoryHelper::getAccessibleLocations();

        $defectNotifications = DefectNotification::with(['book', 'equipment', 'location', 'category', 'defectType'])
            ->where('document_status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        $defectTypes = ErpDefectType::select('id', 'name')->get();
        $equipments = ErpEquipment::select('id', 'name')->get();

        $maintenanceTypesByEquipment = [];
        $equipmentMaintenanceDetails = ErpEquipMaintenanceDetail::with(['equipment', 'maintenanceType', 'bom'])
            ->get()
            ->groupBy('erp_equipment_id');

        foreach ($equipmentMaintenanceDetails as $equipmentId => $details) {
            $maintenanceTypes = $details->pluck('maintenanceType')
                ->filter()
                ->unique('id')
                ->map(fn($type) => ['id' => $type->id, 'name' => $type->name])
                ->values();

            $maintenanceTypesByEquipment[$equipmentId] = $maintenanceTypes;
        }

        // Get only BOMs that are used in equipment maintenance details
        $usedBomIds = ErpEquipMaintenanceDetail::whereNotNull('maintenance_bom_id')
            ->pluck('maintenance_bom_id')
            ->unique();

        $maintenanceBoms = PlantMaintBom::with(['book'])
            ->whereIn('id', $usedBomIds)
            ->select('id', 'bom_name', 'document_number', 'book_id')
            ->orderBy('bom_name')
            ->get()
            ->map(function($bom) {
                return [
                    'id' => $bom->id,
                    'bom_name' => $bom->bom_name,
                    'document_number' => $bom->document_number,
                    'display_name' => $bom->bom_name ,
                ];
            });
       

        return view('plant.maint_wo.create', compact(
            'series',
            'locations',
            'items',
            'defectNotifications',
            'defectTypes',
            'equipments',
            'maintenanceTypesByEquipment',
            'maintenanceBoms'
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = [
            'book_id' => 'required',
            'document_number' => 'required|string|max:100',
            'document_date' => 'required|date',
            'document_status' => 'required|string',
            'location_id' => 'required|integer',
        ];

        if ($request->document_status !== 'draft') {
            $rules['reference_type'] = 'required|string';
        }

        if ($request->hasFile('upload_file')) {
            $rules['upload_file'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240';
        }

        $messages = [
            'book_id.required' => 'The series field is required.',
            'document_number.required' => 'The document number field is required.',
            'document_date.required' => 'The document date field is required.',
            'document_status.required' => 'The document status field is required.',
            'location_id.required' => 'The location field is required.',
            'location_id.integer' => 'The location field must be a valid selection.',
        ];

        $attributes = [
            'book_id' => 'series',
            'document_number' => 'document number',
            'document_date' => 'document date',
            'document_status' => 'document status',
            'location_id' => 'location',
            'reference_type' => 'reference type',
            'upload_file' => 'uploaded file',
        ];

        $request->validate($rules, $messages, $attributes);

        $documentNumber = $request->document_number;
        $existingWo = PlantMaintWo::where('document_number', $documentNumber)->first();
        if ($existingWo) {
            return redirect()
                ->route('maint-wo.create')
                ->withInput()
                ->withErrors("Work Order Number '{$documentNumber}' already exists.");
        }

        $user = Helper::getAuthenticatedUser();
        $additionalData = [
            'created_by' => $user->auth_user_id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
            'approval_level' => 1,
            'revision_number' => 0,
        ];

        $data = array_merge($request->all(), $additionalData);
      

        if (isset($data['spare_parts']) && is_array($data['spare_parts'])) {
            $data['spare_parts'] = json_encode($data['spare_parts']);
        }

        if (isset($data['equipment_details']) && is_array($data['equipment_details'])) {
            $data['equipment_details'] = json_encode($data['equipment_details']);
        }

        unset($data['checklist_data']);

        try {
            DB::transaction(function () use ($data, $request) {
                $workOrder = PlantMaintWo::create($data);

                if ($request->hasFile('upload_file')) {
                    $file = $request->file('upload_file');
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'maint_wo_' . $workOrder->id . '_' . time() . '.' . $extension;
                    $path = $file->storeAs('maint_wo_documents', $fileName, 'public');
                    $workOrder->upload_file = $path;
                }

                if ($request->has('checklist_data') && !empty($request->checklist_data)) {
                    try {
                        $checklistData = null;

                        if (is_string($request->checklist_data)) {
                            $checklistData = json_decode($request->checklist_data, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                \Log::error('JSON decode error: ' . json_last_error_msg());
                                $checklistData = null;
                            }
                        } elseif (is_array($request->checklist_data)) {
                            $checklistData = $request->checklist_data;
                        }

                        if (is_array($checklistData) && !empty($checklistData)) {
                            $processedChecklistData = $this->processChecklistData($checklistData);
                            $workOrder->checklist_data = json_encode($processedChecklistData);
                            $this->saveChecklistRecords($workOrder->id, $processedChecklistData);
                            $workOrder->save();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error processing checklist data: ' . $e->getMessage());
                    }
                }

                $workOrder->save();

                if ($workOrder->document_status != ConstantHelper::DRAFT) {
                    $doc = Helper::approveDocument(
                        $workOrder->book_id,
                        $workOrder->id,
                        $workOrder->revision_number,
                        "",
                        null,
                        1,
                        'submit',
                        0,
                        get_class($workOrder)
                    );

                    $workOrder->document_status = $doc['approvalStatus'] ?? $workOrder->document_status;
                    $workOrder->save();
                }
            });

            return redirect()
                ->route("maint-wo.index")
                ->with('success', 'Maintenance Work Order created!');
        } catch (\Throwable $e) {
            return redirect()
                ->route("maint-wo.create")
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $workOrder = PlantMaintWo::findOrFail($id);
        
        // Enrich spare parts with complete attribute structure including values_data for edit blade
        if ($workOrder->spare_parts) {
            $sparePartsData = json_decode($workOrder->spare_parts, true);
            $enrichedSpareParts = [];
            
            foreach ($sparePartsData as $sparePart) {
                $enrichedSparePart = $sparePart;
                
                // Enrich item_attributes with complete structure for attribute modal
                if (isset($sparePart['item_id'])) {
                    $item = Item::with(['itemAttributes'])->find($sparePart['item_id']);
                    if ($item && $item->itemAttributes) {
                        $processedAttributes = [];
                        foreach ($item->itemAttributes as $attribute) {
                            $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                                ->select('id', 'value')
                                ->where('status', 'active')
                                ->get();

                            $processedAttributes[] = [
                                'id' => $attribute->id,
                                'group_name' => $attribute->group?->name,
                                'values_data' => $attributeValueData,
                                'attribute_group_id' => $attribute->attribute_group_id,
                            ];
                        }
                        $enrichedSparePart['item_attributes'] = json_encode($processedAttributes);
                    }
                }
                
                // Also enrich existing attribute data with value names for display
                if (isset($sparePart['attribute']) && !empty($sparePart['attribute'])) {
                    $attributeData = json_decode($sparePart['attribute'], true);
                    
                    if (is_array($attributeData)) {
                        foreach ($attributeData as &$attr) {
                            if (isset($attr['value_id']) && isset($attr['item_attribute_id'])) {
                                // Get item attribute for group name
                                $itemAttribute = \App\Models\ErpItemAttribute::with('group')->find($attr['item_attribute_id']);
                                // Get attribute value for value name
                                $attributeValue = \App\Models\ErpAttribute::find($attr['value_id']);
                                
                                if ($itemAttribute && $attributeValue) {
                                    $attr['name'] = $itemAttribute->group->name ?? 'N/A';
                                    $attr['value'] = $attributeValue->value ?? 'N/A';
                                }
                            }
                        }
                        
                        // Update the attribute field with enriched data
                        $enrichedSparePart['attribute'] = json_encode($attributeData);
                    }
                }
                $enrichedSpareParts[] = $enrichedSparePart;
            }
            $workOrder->spare_parts = json_encode($enrichedSpareParts);
        }

        $parentURL = "plant_maint-wo";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }

        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay(
            $workOrder->book_id,
            $workOrder->document_status,
            $id,
            0,
            $workOrder->approval_level,
            $workOrder->created_by ?? 0,
            $userType['type'],
            $workOrder->revision_number
        );

        if ($workOrder->document_status === ConstantHelper::DRAFT || $workOrder->document_status === ConstantHelper::SUBMITTED) {
            $buttons['cancel'] = true;
        } else {
            $buttons['cancel'] = false;
        }

        if ($workOrder->document_status === ConstantHelper::POSTED) {
            $buttons['amend'] = false;
        }

        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();

        foreach ($items as $item) {
            $itemAttributes = $item->id ? ItemAttribute::where('item_id', $item->id)->get() : [];
            $processedData = [];

            foreach ($itemAttributes as $attribute) {
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                    ->select('id', 'value')
                    ->where('status', 'active')
                    ->get();

                $processedData[] = [
                    'id' => $attribute->id,
                    'group_name' => $attribute->group_name,
                    'values_data' => $attributeValueData,
                    'attribute_group_id' => $attribute_group_id,
                ];
            }

            $item->attributes = collect($processedData);
        }

        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'uom_name' => optional($item->uom)->name,
                'uom_id' => optional($item->uom)->id,
                'item_attributes' => $item->attributes,
            ];
        });

        $locations = InventoryHelper::getAccessibleLocations();

        $revision_number = $workOrder->revision_number;

        $approvalHistory = Helper::getApprovalHistory(
            $workOrder->book_id,
            $id,
            $revision_number,
            0,
            $workOrder->created_by
        );

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$workOrder->document_status] ?? '';

        $defectTypes = ErpDefectType::select('id', 'name')->get();
        $equipments = ErpEquipment::select('id', 'name')->get();

        $defectNotifications = DefectNotification::with(['book', 'equipment', 'location', 'category', 'defectType'])
            ->where('document_status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        $maintenanceTypesByEquipment = [];
        $equipmentsWithMaintenance = ErpEquipment::with(['maintenanceDetails.maintenanceType'])
            ->whereHas('maintenanceDetails')
            ->get();

        foreach ($equipmentsWithMaintenance as $equipment) {
            $maintenanceTypes = [];
            foreach ($equipment->maintenanceDetails as $detail) {
                if ($detail->maintenanceType) {
                    $maintenanceTypes[] = [
                        'id' => $detail->maintenanceType->id,
                        'name' => $detail->maintenanceType->name,
                    ];
                }
            }
            if (!empty($maintenanceTypes)) {
                $maintenanceTypesByEquipment[$equipment->id] = array_unique($maintenanceTypes, SORT_REGULAR);
            }
        }

        $usedBomIds = ErpEquipMaintenanceDetail::whereNotNull('maintenance_bom_id')
            ->pluck('maintenance_bom_id')
            ->unique();

        $maintenanceBoms = PlantMaintBom::with(['book'])
            ->whereIn('id', $usedBomIds)
            ->select('id', 'bom_name', 'document_number', 'book_id')
            ->orderBy('bom_name')
            ->get()
            ->map(function($bom) {
                return [
                    'id' => $bom->id,
                    'bom_name' => $bom->bom_name,
                    'document_number' => $bom->document_number,
                    'display_name' => $bom->bom_name ,
                ];
            });

          

        return view('plant.maint_wo.edit', compact(
            'workOrder',
            'series',
            'items',
            'locations',
            'defectNotifications',
            'buttons',
            'approvalHistory',
            'docStatusClass',
            'revision_number',
            'defectTypes',
            'equipments',
            'maintenanceTypesByEquipment',
            'maintenanceBoms',
        ));
    }

    public function update(Request $request, string $id)
    {
     
        $rules = [
            'book_id' => 'required',
            'document_number' => 'required|string|max:100',
            'document_date' => 'required|date',
            'document_status' => 'required|string',
        ];

        if ($request->document_status !== 'draft') {
            $rules['reference_type'] = 'required|string';
        }

        if ($request->hasFile('upload_file')) {
            $rules['upload_file'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240';
        }

        $request->validate($rules);

        $workOrder = PlantMaintWo::findOrFail($id);

        $documentNumber = $request->document_number;
        $existingWo = PlantMaintWo::where('document_number', $documentNumber)
            ->where('id', '!=', $id)
            ->first();

        if ($existingWo) {
            return redirect()
                ->route('maint-wo.edit', $id)
                ->withInput()
                ->withErrors("Work Order Number '{$documentNumber}' already exists.");
        }

        DB::beginTransaction();

        try {
            if ($request->action_type == "amendment") {
                $revisionData = [
                    [
                        "model_type" => "header",
                        "model_name" => "PlantMaintWo",
                        "relation_column" => "",
                    ],
                ];

                Helper::documentAmendment($revisionData, $id);

                Helper::approveDocument(
                    $workOrder->book_id,
                    $workOrder->id,
                    $workOrder->revision_number,
                    $request->amend_remarks,
                    $request->file('amend_attachment'),
                    $workOrder->approval_level,
                    'amendment',
                    0,
                    get_class($workOrder)
                );

                $request->merge([
                    'revision_number' => $workOrder->revision_number + 1,
                    'revision_date' => now(),
                ]);
            }

            // Prepare update data
            $updateData = $request->all();
            
            // Only update checklist_data if it's not empty
            if (empty($request->checklist_data) || $request->checklist_data === 'null' || $request->checklist_data === '[]') {
                unset($updateData['checklist_data']);
            }
            
            $workOrder->update($updateData);
            

            if ($request->hasFile('upload_file')) {
                $file = $request->file('upload_file');
                $extension = $file->getClientOriginalExtension();
                $fileName = 'maint_wo_' . $workOrder->id . '_' . time() . '.' . $extension;
                $path = $file->storeAs('maint_wo_documents', $fileName, 'public');
                $workOrder->upload_file = $path;
                $workOrder->save();
            }

            if ($workOrder->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument(
                    $workOrder->book_id,
                    $workOrder->id,
                    $workOrder->revision_number,
                    "",
                    null,
                    1,
                    'submit',
                    0,
                    get_class($workOrder)
                );

                $workOrder->document_status = $doc['approvalStatus'] ?? $workOrder->document_status;
                $workOrder->save();
            }

            DB::commit();

            return redirect()
                ->route("maint-wo.index")
                ->with('success', 'Maintenance Work Order updated!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route("maint-wo.edit", $id)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            $doc = PlantMaintWo::findOrFail($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($doc);

            $approveDocument = Helper::approveDocument(
                $bookId,
                $docId,
                $revisionNumber,
                $remarks,
                $attachments,
                $currentLevel,
                $actionType,
                $docValue,
                $modelName
            );

            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();

            return response()->json([
                'message' => "Work Order {$actionType}d successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => "Error occurred while processing {$request->action_type}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function populateModal(Request $r)
    {
        $type = $r->type;
        $data = [];

        $usedEquipmentIds = PlantMaintWo::pluck('equipment_details')
        ->map(function ($details) {
            $decoded = json_decode($details, true);
            return $decoded['equipment_id'] ?? null;
        })
        ->filter() 
        ->toArray();

       


        if ($type == 'defect') {
            $query = DefectNotification::with([
                'book',
                'equipment.maintenanceDetails.maintenanceType',
                'location',
                'category',
                'defectType',
            ])->where('document_status', '!=', 'draft')
              ->orderBy('created_at', 'desc');

            $totalDefects = DefectNotification::where('document_status', '!=', 'draft')->count();
           

            if ($r->book_code && is_array($r->book_code) && count($r->book_code) > 0) {
                $query->whereHas('book', function ($q) use ($r) {
                    $q->whereIn('book_code', $r->book_code);
                });
               
            } 

            $results = $query->get();
           

            $data = $results->map(function ($defectNotification) {
                $maintenanceTypes = [];
                if ($defectNotification->equipment && $defectNotification->equipment->maintenanceDetails) {
                    $maintenanceTypes = $defectNotification->equipment->maintenanceDetails
                        ->map(fn($detail) => $detail->maintenanceType)
                        ->filter()
                        ->unique('id')
                        ->map(fn($type) => ['id' => $type->id, 'name' => $type->name])
                        ->values();
                }

                $checklistsByMaintenanceType = [];

                $defectNotification->maintenance_types = $maintenanceTypes;
                $defectNotification->checklists_by_maintenance_type = $checklistsByMaintenanceType;

                return $defectNotification;
            });
        } elseif ($type == 'eqpt') {
            $query = ErpEquipMaintenanceDetail::with([
                'equipment',
                'bom.book',
                'maintenanceType',
                'checklists',
                'equipment.book',
                'equipment.location',   
                'equipment.category',
                'equipment.spareParts'
            ])
            ->whereHas('bom')
            ->whereNotIn('erp_equipment_id', $usedEquipmentIds)
            ->whereHas('equipment', function ($q) use ($r) {
                $q->where('document_status', '!=', 'draft') 
                    ->whereHas('book', function ($qu) use ($r) {
                        $qu->whereIn('book_code', $r->book_code);
                    });
            });

            $equipmentData = $query->get();
            //Need to optimize this query acording to only requied field for each relation

            foreach ($equipmentData as $eqpt) {

                $dueDate = null;

                if ($eqpt->equipment && $eqpt->equipment->document_status === 'approved') {
                    $base = $eqpt->start_date ? Carbon::parse($eqpt->start_date) : null;
                    if ($base) {
                        $freqType = $eqpt->frequency ?? '';
            
                        switch ($freqType) {
                            case 'Daily':
                                $dueDate = $base->copy()->addDay();
                                break;
                            case 'Weekly':
                                $dueDate = $base->copy()->addWeek();
                                break;
                            case 'Monthly':
                                $dueDate = $base->copy()->addMonth();
                                break;
                            case 'Quarterly':
                                $dueDate = $base->copy()->addMonths(3);
                                break;
                            case 'Semi-Annually':
                                $dueDate = $base->copy()->addMonths(6);
                                break;
                            case 'Annually':
                                $dueDate = $base->copy()->addYear();
                                break;
                            case 'Yearly':
                                $dueDate = $base->copy()->addYear();
                                break;
                            default:
                                $dueDate = $base;
                        }
                    }
                } else {
                    $dueDate = $eqpt->start_date ? Carbon::parse($eqpt->start_date) : null;
                }
            
                $eqpt->due_date = $dueDate ? $dueDate->format('Y-m-d') : null;

                $maintenance_type_id = $eqpt->maintenance_type_id;

                $maintenanceChecklists = ErpEquipMaintenanceChecklist::where('erp_equip_maintenance_id', $eqpt->id)
                    ->select('erp_equip_maintenance_id', 'name')
                    ->get();

               
                

                

                $checklistsData = [];

                foreach ($maintenanceChecklists as $maintenanceChecklist) {
                    $checklistName = $maintenanceChecklist->name;
                    $inspectionChecklist = \App\Models\InspectionChecklist::where('name', $checklistName)->first();

                    if ($inspectionChecklist) {
                        $checklistDetails = \App\Models\InspectionChecklistDetail::where('header_id', $inspectionChecklist->id)
                            ->select('id', 'name', 'data_type', 'description', 'mandatory')
                            ->get();

                        $detailsWithValues = [];

                        foreach ($checklistDetails as $detail) {
                            $detailData = [
                                'name' => $detail->name,
                                'data_type' => $detail->data_type,
                                'description' => $detail->description,
                                'mandatory' => $detail->mandatory,
                                'value' => '',
                            ];

                            $detailValues = \App\Models\InspectionChecklistDetailValue::where('inspection_checklist_detail_id', $detail->id)
                                ->pluck('value')
                                ->toArray();

                            if ($detail->data_type === 'list') {
                                $detailData['values'] = $detailValues;
                                $detailData['value'] = !empty($detailValues) ? $detailValues[0] : '';
                            } else {
                                $detailData['value'] = !empty($detailValues) ? $detailValues[0] : '';
                            }

                            $detailsWithValues[] = $detailData;
                        }

                        $checklistsData[] = [
                            'main_name' => $checklistName,
                            'checklist' => $detailsWithValues,
                        ];
                    }
                }

                $eqpt->checklistsData = $checklistsData;
                $eqpt->checklistsIdsName = $maintenanceChecklists;
            }
           
         
            

            $data = [];
            
            foreach ($equipmentData as $detail) {
                if ($detail->equipment) {
                    $checklistsData = isset($detail->checklistsData) ? $detail->checklistsData : [];

                    $equipment = $detail->equipment;
                    $equipment->checklists_data = $checklistsData;
                    $equipment->due_date = $detail->due_date;

                    $data[] = [
                        'equipment' => $equipment,
                        'maintenance_type' => $detail->maintenanceType,
                        'bom' => $detail->bom,
                        'maintenance_detail_id' => $detail->id,
                    ];
                }
            }
        }

        
      

        return response()->json($data);
    }

    public function ajaxData(Request $request)
    {
        $query = PlantMaintWo::with(['book']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('document_date', [$request->start_date, $request->end_date]);
        }

        $totalRecords = $query->count();

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('document_number', 'like', "%{$searchValue}%")
                    ->orWhere('maintenance_type', 'like', "%{$searchValue}%")
                    ->orWhere('equipment_details', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            switch ($orderColumn) {
                case 1:
                    $query->orderBy('document_date', $orderDir);
                    break;
                case 2:
                    $query->orderBy('book_id', $orderDir);
                    break;
                case 3:
                    $query->orderBy('document_number', $orderDir);
                    break;
                case 4:
                    $query->join('erp_equipments', 'erp_plant_maint_wo.equipment_id', '=', 'erp_equipments.id')
                        ->orderBy('erp_equipments.name', $orderDir);
                    break;
                case 6:
                    $query->orderBy('maintenance_type', $orderDir);
                    break;
                default:
                    $query->orderBy('document_date', 'desc');
            }
        } else {
            $query->orderBy('document_date', 'desc');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $workOrders = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($workOrders as $index => $wo) {
            $formattedDate = $wo->document_date
                ? \Carbon\Carbon::parse($wo->document_date)->format('d-m-Y')
                : '-';

            $series = $wo->book?->book_code ?? 'MAINT_WO';

            $equipmentDetails = json_decode($wo->equipment_details, true);
            $equipmentName = $equipmentDetails['equipment_name'] ?? 'Default Equipment';
            $categoryName = $equipmentDetails['equipment_category'] ?? 'Machinery';

            $maintenanceType = $wo->maintenance_type ?? 'Preventive';
            $typeClass = $maintenanceType == "Preventive" ? "info" : ($maintenanceType == "Corrective" ? "warning" : "secondary");
            $typeBadge = "<span class='badge rounded-pill badge-light-{$typeClass} badgeborder-radius'>{$maintenanceType}</span>";

            $statusClass = 'badge-light-secondary';
            if (isset(ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$wo->document_status ?? 'draft'])) {
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$wo->document_status ?? 'draft'];
            }
            $statusText = $wo->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED ? 'Approved' : ucfirst($wo->document_status ?? 'draft');

            $actions = '<div class="d-flex align-items-center justify-content-end">';
            $actions .= "<span class='badge rounded-pill {$statusClass} badgeborder-radius'>{$statusText}</span>";
            $actions .= '<div class="dropdown ml-2">';
            $actions .= '<button type="button" class="btn btn-sm dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">';
            $actions .= '<i data-feather="more-vertical"></i>';
            $actions .= '</button>';
            $actions .= '<div class="dropdown-menu dropdown-menu-end">';

            if ($wo->document_status == 'draft') {
                $actions .= '<a class="dropdown-item" href="' . route('maint-wo.edit', $wo->id) . '">';
                $actions .= '<i data-feather="edit" class="me-50"></i><span>Edit</span>';
                $actions .= '</a>';
            } else {
                $actions .= '<a class="dropdown-item" href="' . route('maint-wo.show', $wo->id) . '">';
                $actions .= '<i data-feather="eye" class="me-50"></i><span>View</span>';
                $actions .= '</a>';
            }

            $actions .= '</div></div></div>';

            $data[] = [
                $start + $index + 1,
                $formattedDate,
                $series,
                $wo->document_number ?? '-',
                $equipmentName,
                $categoryName,
                $typeBadge,
                $actions,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function destroy(string $id)
    {
        //
    }

    private function processChecklistData($checklistData)
    {
        $processedData = [];

        foreach ($checklistData as $checklistGroup) {
            $processedGroup = [
                'main_name' => $checklistGroup['main_name'] ?? '',
                'checklist' => [],
            ];

            if (isset($checklistGroup['checklist']) && is_array($checklistGroup['checklist'])) {
                foreach ($checklistGroup['checklist'] as $checklistItem) {
                    $processedItem = [
                        'name' => $checklistItem['name'] ?? '',
                        'data_type' => $checklistItem['data_type'] ?? 'text',
                        'mandatory' => (bool)($checklistItem['mandatory'] ?? false),
                        'value' => $this->sanitizeChecklistValue($checklistItem['value'] ?? '', $checklistItem['data_type'] ?? 'text'),
                        'completed_at' => now()->toDateTimeString(),
                        'completed_by' => auth()->id(),
                    ];

                    $processedGroup['checklist'][] = $processedItem;
                }
            }

            $processedData[] = $processedGroup;
        }

        return $processedData;
    }

    private function sanitizeChecklistValue($value, $dataType)
    {
        switch ($dataType) {
            case 'number':
                return is_numeric($value) ? (float)$value : 0;
            case 'boolean':
            case 'checkbox':
                return in_array($value, ['1', 'true', true, 1], true);
            case 'date':
                try {
                    return \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Exception $e) {
                    return null;
                }
            default:
                return (string)$value;
        }
    }

    private function saveChecklistRecords($workOrderId, $checklistData)
    {
        try {
            \Log::info('Checklist data saved for Work Order ID: ' . $workOrderId, [
                'work_order_id' => $workOrderId,
                'checklist_count' => count($checklistData),
                'completed_at' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving checklist records: ' . $e->getMessage());
        }
    }

    public function getEquipmentSpareParts(Request $request)
    {
        try {
            $equipmentId = $request->equipment_id;
            $maintenanceTypeId = $request->maintenance_type_id;

            $equipment = ErpEquipment::find($equipmentId);
            
            $maintenanceDetail = ErpEquipMaintenanceDetail::where('erp_equipment_id', $equipmentId)
                ->where('maintenance_type_id', $maintenanceTypeId)
                ->with('bom')
                ->first();
           
            
            if (!$maintenanceDetail || !$maintenanceDetail->bom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maintenance BOM not found for this equipment and maintenance type'
                ], 404);
            }

            $bomData = $maintenanceDetail->bom;
            $sparePartsData = [];
            
            if ($bomData->spare_parts) {
                $rawSparePartsData = json_decode($bomData->spare_parts, true);
                
                foreach ($rawSparePartsData as $sparePart) {
                    
                    $confirmedStock = StockLedger::query()
                    ->selectRaw("
                        SUM(
                            CASE 
                                WHEN document_status IN ('approved', 'approval_not_required', 'posted') 
                                THEN receipt_qty - reserved_qty
                                ELSE 0
                            END
                        ) as confirmed_stock
                    ")
                    ->where('item_code', $sparePart['item_code']) // yaha fix kiya
                    ->value('confirmed_stock');
                    
                    $sparePartData = [
                        'item_id' => $sparePart['item_id'],
                        'item_code' => $sparePart['item_code'] ?? 'N/A',
                        'item_name' => $sparePart['item_name'] ?? 'N/A',
                        'qty' => $sparePart['qty'] ?? 0,
                        'uom' => $sparePart['uom_name'] ?? 'N/A',
                        'uom_id' => $sparePart['uom_id'] ?? null,
                        'attribute' => $sparePart['attribute'] ?? '[]',
                        'attributes' => [],
                        'confirmed_stock' => $confirmedStock ?? 0,
                    ];

                    // Process attributes if they exist
                    if (isset($sparePart['attribute']) && !empty($sparePart['attribute'])) {
                        $attributeData = json_decode($sparePart['attribute'], true);
                        \Log::info('Processing attributes for item: ' . $sparePart['item_id'], [
                            'raw_attribute' => $sparePart['attribute'],
                            'decoded_attribute' => $attributeData
                        ]);
                        
                        if (is_array($attributeData)) {
                            foreach ($attributeData as $attr) {
                                if (isset($attr['item_attribute_id']) && isset($attr['value_id'])) {
                                    // Get item attribute details
                                    $itemAttribute = \App\Models\ErpItemAttribute::with('group')->find($attr['item_attribute_id']);
                                    
                                    // Get selected attribute value
                                    $selectedAttributeValue = \App\Models\ErpAttribute::find($attr['value_id']);
                                    
                                    \Log::info('Attribute lookup results:', [
                                        'item_attribute_id' => $attr['item_attribute_id'],
                                        'value_id' => $attr['value_id'],
                                        'item_attribute_found' => $itemAttribute ? true : false,
                                        'selected_value_found' => $selectedAttributeValue ? true : false,
                                        'group_id' => $itemAttribute ? $itemAttribute->attribute_group_id : null
                                    ]);

                                    if ($itemAttribute && $selectedAttributeValue) {
                                        // Get all possible attribute values for this group
                                        $allAttributeValues = \App\Models\ErpAttribute::where('attribute_group_id', $itemAttribute->attribute_group_id)
                                            ->orderBy('value')
                                            ->get();

                                        $sparePartData['attributes'][] = [
                                            'item_attribute_id' => $attr['item_attribute_id'],
                                            'group_id' => $itemAttribute->attribute_group_id,
                                            'group_name' => $itemAttribute->group->name ?? 'N/A',
                                            'group_short_name' => $itemAttribute->group->short_name ?? 'N/A',
                                            'selected_value_id' => $attr['value_id'],
                                            'selected_value_name' => $selectedAttributeValue->value ?? 'N/A',
                                            'all_values' => $allAttributeValues->map(function($value) {
                                                return [
                                                    'id' => $value->id,
                                                    'value' => $value->value
                                                ];
                                            })->toArray()
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    $sparePartsData[] = $sparePartData;
                }
            }

            // Debug: Log final spare parts data
            \Log::info('Final spare parts data being returned:', [
                'equipment_id' => $equipmentId,
                'maintenance_type_id' => $maintenanceTypeId,
                'bom_id' => $maintenanceDetail->maintenance_bom_id,
                'spare_parts_count' => count($sparePartsData),
                'spare_parts_sample' => $sparePartsData ? $sparePartsData[0] : null
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'equipment_id' => $equipmentId,
                    'maintenance_type_id' => $maintenanceTypeId,
                    'bom_id' => $maintenanceDetail->maintenance_bom_id,
                    'equipment_name' => $equipment ? $equipment->name : '',
                    'spare_parts' => $sparePartsData
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching equipment spare parts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching spare parts data'
            ], 500);
        }
    }

    public function filter(Request $request)
    {
        try {
            $type = $request->input('type');
            
            switch ($type) {
                case 'equipment':
                    return $this->filterByEquipment($request);
                case 'defect':
                    return $this->filterByDefectNotification($request);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid filter type'
                    ], 400);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in filter method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing filter request'
            ], 500);
        }
    }

    private function filterByEquipment(Request $request)
    {
        $equipmentId = $request->input('equipment_id');
        $maintenanceTypeId = $request->input('maintenance_type_id');
        $bomId = $request->input('bom_id');

        $equipment = ErpEquipment::find($equipmentId);
        
        if (!$equipment) {
            return response()->json([
                'success' => false,
                'message' => 'Equipment not found'
            ], 404);
        }

        $query = ErpEquipMaintenanceDetail::where('erp_equipment_id', $equipmentId)
            ->whereNotNull('maintenance_bom_id')
            ->with(['bom.book', 'maintenanceType', 'equipment.category', 'checklists']);

        if ($maintenanceTypeId) {
            $query->where('maintenance_type_id', $maintenanceTypeId);
        }
        if ($bomId) {
            $query->where('maintenance_bom_id', $bomId);
        }

        $maintenanceDetails = $query->get();

        if ($maintenanceDetails->isEmpty()) {
            return response()->json([]);
        }

        $data = [];
        $equipmentGroups = $maintenanceDetails->groupBy('erp_equipment_id');

        foreach ($equipmentGroups as $equipmentId => $details) {
            $firstDetail = $details->first();
            if ($firstDetail->equipment) {
                // Maintenance types for this equipment
                $maintenanceTypes = $details->map(function ($detail) {
                    return [
                        'id' => $detail->maintenanceType->id,
                        'name' => $detail->maintenanceType->name,
                    ];
                })->unique('id')->values();

                // ✅ Due date & Last Maint Date logic
                $dueDate = null;
                $lastMaintDate = null;
                // dd($firstDetail->equipment->document_status);
                if ($firstDetail->equipment->document_status === 'approved') {
                    if ($firstDetail->start_date) {
                        $lastMaintDate = Carbon::parse($firstDetail->start_date);
                        $base = $lastMaintDate->copy();
                        $freqType = trim($firstDetail->frequency ?? '');

                        switch ($freqType) {
                            case 'Daily':
                                $dueDate = $base->copy()->addDay();
                                break;

                            case 'Weekly':
                                $dueDate = $base->copy()->addWeek();
                                break;

                            case 'Monthly':
                                $dueDate = $base->copy()->addMonth();
                                break;

                            case 'Quarterly':
                                $dueDate = $base->copy()->addMonths(3);
                                break;

                            case 'Semi-Annually':
                            case 'Semi Annually':
                            case 'Semi Annualy':
                                $dueDate = $base->copy()->addMonths(6);
                                break;

                            case 'Annually':
                            case 'Annualy':
                            case 'Yearly':
                                $dueDate = $base->copy()->addYear();
                                break;

                            default:
                                $dueDate = $base;
                        }
                    }
                } else {
                    $lastMaintDate = null;
                    $dueDate = $firstDetail->start_date ? Carbon::parse($firstDetail->start_date) : null;
                }

                // Checklists logic
                $maintenance_type_id = $firstDetail->maintenance_type_id;
                $maintenanceChecklists = ErpEquipMaintenanceChecklist::where('erp_equip_maintenance_id', $maintenance_type_id)
                    ->select('erp_equip_maintenance_id', 'name')
                    ->get();

                $checklistsData = [];
                foreach ($maintenanceChecklists as $maintenanceChecklist) {
                    $checklistName = $maintenanceChecklist->name;
                    $inspectionChecklist = \App\Models\InspectionChecklist::where('name', $checklistName)->first();

                    if ($inspectionChecklist) {
                        $checklistDetails = \App\Models\InspectionChecklistDetail::where('header_id', $inspectionChecklist->id)
                            ->select('id', 'name', 'data_type', 'description', 'mandatory')
                            ->get();

                        $detailsWithValues = [];
                        foreach ($checklistDetails as $detail) {
                            $detailData = [
                                'name' => $detail->name,
                                'data_type' => $detail->data_type,
                                'description' => $detail->description,
                                'mandatory' => $detail->mandatory,
                                'value' => '',
                            ];

                            $detailValues = \App\Models\InspectionChecklistDetailValue::where('inspection_checklist_detail_id', $detail->id)
                                ->pluck('value')
                                ->toArray();

                            if ($detail->data_type === 'list') {
                                $detailData['values'] = $detailValues;
                                $detailData['value'] = !empty($detailValues) ? $detailValues[0] : '';
                            } else {
                                $detailData['value'] = !empty($detailValues) ? $detailValues[0] : '';
                            }

                            $detailsWithValues[] = $detailData;
                        }

                        $checklistsData[] = [
                            'main_name' => $checklistName,
                            'checklist' => $detailsWithValues,
                        ];
                    }
                }

                $equipment = $firstDetail->equipment;
                $equipment->checklists_data = $checklistsData;
                $equipment->last_maint_date = $lastMaintDate ? $lastMaintDate->format('Y-m-d') : null;
                $equipment->due_date = $dueDate ? $dueDate->format('Y-m-d') : null;

                $data[] = [
                    'equipment' => $equipment,
                    'maintenance_type' => $maintenanceTypes->first(),
                    'maintenance_types' => $maintenanceTypes,
                    'bom' => $firstDetail->bom,
                ];
            }
        }

        return response()->json($data);
    }

    private function filterByDefectNotification(Request $request)
    {
        $equipmentId = $request->input('equipment_id');
        $defectTypeId = $request->input('defect_type_id');
        $priority = $request->input('priority');
        $seriesCode = $request->input('series_code');

        // Use exact same query as populateModal defect case
        $query = DefectNotification::with([
            'book',
            'equipment.maintenanceDetails.maintenanceType',
            'location',
            'category',
            'defectType',
        ])->where('document_status', '!=', 'draft')
          ->orderBy('created_at', 'desc');

        // Apply filters based on provided parameters
        if ($equipmentId) {
            $query->where('equipment_id', $equipmentId);
        }

        if ($defectTypeId) {
            $query->where('defect_type_id', $defectTypeId);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($seriesCode) {
            $query->whereHas('book', function ($q) use ($seriesCode) {
                $q->where('book_code', 'LIKE', "%{$seriesCode}%");
            });
        }

        $results = $query->get();

        // If no data found, return empty array for modal
        if ($results->isEmpty()) {
            return response()->json([]);
        }

        // Use exact same data processing as populateModal method
        $data = $results->map(function ($defectNotification) {
            $maintenanceTypes = [];
            if ($defectNotification->equipment && $defectNotification->equipment->maintenanceDetails) {
                $maintenanceTypes = $defectNotification->equipment->maintenanceDetails
                    ->map(fn($detail) => $detail->maintenanceType)
                    ->filter()
                    ->unique('id')
                    ->map(fn($type) => ['id' => $type->id, 'name' => $type->name])
                    ->values();
            }

            $checklistsByMaintenanceType = [];

            $defectNotification->maintenance_types = $maintenanceTypes;
            $defectNotification->checklists_by_maintenance_type = $checklistsByMaintenanceType;

            return $defectNotification;
        });

        return response()->json($data);
    }

    private function formatAttributesForDisplay($attributes)
    {
        if (empty($attributes)) {
            return 'No attributes';
        }

        $formatted = [];
        foreach ($attributes as $attr) {
            $groupName = $attr['group_short_name'] ?? $attr['group_name'] ?? 'Attribute';
            $selectedValue = $attr['selected_value'] ?? 'N/A';
            $totalValues = $attr['values_count'] ?? 0;

            if ($totalValues > 1) {
                $allValuesText = collect($attr['all_values'] ?? [])->pluck('value')->implode(', ');
                $formatted[] = "{$groupName}: {$selectedValue} (Available: {$allValuesText})";
            } else {
                $formatted[] = "{$groupName}: {$selectedValue}";
            }
        }

        return implode(' | ', $formatted);
    }


    //Close modal
    public function closeWorkOrder(Request $request){
        try{
            $remarks = $request->remarks??"";
            $workOrder =PlantMaintWo::find($request->workorder_id);
            $workOrder->document_status='closed';
            $workOrder->save();
           
            Helper::approveDocument(
                $workOrder->book_id,
                $workOrder->id,
                $workOrder->revision_number,
                $request->remarks,
                $request->file('closed_attachment'),
                $workOrder->approval_level,
                'closed',
                0,
                get_class($workOrder)
            );
           
            return response()->json([
                'message' => 'Maintenance Work Order Closed Successfully.',
                'title' =>'Success !',
                'type' => 'success'
            ], 200);
        }
        catch(Exception $ex){
            return response()->json([
                'message' => 'Some Error Occured.',
                'title' =>'Error !',
                'type' => 'error'
            ], 500);
        }
    }
}
