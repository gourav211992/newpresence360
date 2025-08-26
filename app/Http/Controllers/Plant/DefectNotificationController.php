<?php

namespace App\Http\Controllers\Plant;

use Illuminate\Http\Request;
use App\Models\DefectNotification;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ErpAttribute;
use App\Models\ItemAttribute;
use App\Helpers\Helper;
use App\Models\ErpEquipment;
use App\Models\Category;
use App\Models\ErpDefectType;
use App\Helpers\ConstantHelper;
use App\Models\DefectNotificationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DefectNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('plant.defect-notification.index');
    }
    public function filter(Request $request)
    {
        $query = DefectNotification::query();

        // Apply filters only if values are provided
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        if ($request->filled('defect_type_id')) {
            $query->where('defect_type_id', $request->defect_type_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('series')) {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('book_code', $request->series);
            });
        }

        // Load related models for display
        $defects = $query->with(['equipment', 'defectType', 'book'])->get();

        return response()->json([
            'status' => true,
            'data' => $defects
        ]);
    }


    public function getDefectNotification($id)
    {
        $defectNotification = DefectNotification::with([
            'book', 
            'equipment.maintenanceDetails.maintenanceType', 
            'location', 
            'category', 
            'defectType',
        ])->findOrFail($id);

        // Get maintenance types using Eloquent relationships
        $maintenanceTypes = [];
        if ($defectNotification->equipment && $defectNotification->equipment->maintenanceDetails) {
            $maintenanceTypes = $defectNotification->equipment->maintenanceDetails
                ->map(function ($detail) {
                    return $detail->maintenanceType;
                })
                ->filter() 
                ->unique('id') 
                ->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name
                    ];
                })
                ->values(); 
        }

        // Get all checklists for this equipment (grouped by maintenance type)
        $checklistsByMaintenanceType = [];
        if ($defectNotification->equipment && $defectNotification->equipment->maintenanceDetails) {
            foreach ($defectNotification->equipment->maintenanceDetails as $detail) {
                if ($detail->maintenanceType && $detail->checklists->count() > 0) {
                    $checklistsByMaintenanceType[$detail->maintenance_type_id] = [
                        'maintenance_type_name' => $detail->maintenanceType->name,
                        'checklists' => $detail->checklists->map(function ($checklist) {
                            return [
                                'id' => $checklist->id,
                                'name' => $checklist->name,
                                'description' => $checklist->description,
                                'type' => $checklist->type,
                                'status' => $checklist->status
                            ];
                        })
                    ];
                }
            }
        }

        return response()->json([
            'status' => true,
            'data' => array_merge(
                $defectNotification->toArray(),
                ['reported_by' => auth()->user()->name ?? 'N/A']
            ),
            'maintenance_types' => $maintenanceTypes,
            'checklists_by_maintenance_type' => $checklistsByMaintenanceType
        ]);
    }



    /**
     * Get defect notifications data for DataTables Ajax
     */
    public function getDefectNotificationsData(Request $request)
    {
        $query = DefectNotification::with(['equipment', 'location', 'category', 'defectType'])
            ->select([
                'id', 'document_date', 'equipment_id', 'category_id', 'location_id', 
                'defect_type_id', 'problem', 'priority', 'document_status', 'revision_number'
            ]);

            // Apply search filter
            if ($request->has('search') && $request->search['value']) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('problem', 'like', "%{$searchValue}%")
                    ->orWhere('priority', 'like', "%{$searchValue}%")
                    ->orWhere('document_status', 'like', "%{$searchValue}%")
                    ->orWhereHas('equipment', function($eq) use ($searchValue) {
                        $eq->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('category', function($cat) use ($searchValue) {
                        $cat->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('defectType', function($dt) use ($searchValue) {
                        $dt->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('location', function($loc) use ($searchValue) {
                        $loc->where('name', 'like', "%{$searchValue}%");
                    });
                });
            }

        $totalRecords = DefectNotification::count();
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
            
            $columns = ['id', 'document_date', 'equipment_id', 'category_id', 'location_id', 'defect_type_id', 'problem', 'priority', 'document_status'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->start)->take($request->length);
        }

        $defectNotifications = $query->get();

        

        $data = [];
        foreach ($defectNotifications as $index => $notification) {
            $statusClass = 'badge-light-secondary';
            if (isset(ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$notification->document_status ?? 'draft'])) {
                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$notification->document_status ?? 'draft'];
            }

            $statusText = $notification->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED ? 'Approved' : ucfirst($notification->document_status ?? 'draft');
            
            $statusBadge = "<span class='badge rounded-pill {$statusClass} badgeborder-radius'>{$statusText}</span>";

            // Conditional routing based on document status
            $route = ($notification->document_status == 'draft') 
                ? route('defect-notification.edit', $notification->id)
                : route('defect-notification.show', $notification->id);
            
            $actions = '
                <div class="d-flex align-items-center justify-content-end">
                    ' . $statusBadge . '
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . $route . '">
                                <i data-feather="edit" class="me-50"></i>
                                <span>View</span>
                            </a>
                        </div>
                    </div>
                </div>';

            $data[] = [
                $request->start + $index + 1, // Row number
                $notification->document_date ? \Carbon\Carbon::parse($notification->document_date)->format('d-m-Y') : '-',
                $notification->equipment?->name ?? ($notification->equipment_id ? 'Equipment ID: ' . $notification->equipment_id : '-'),
                $notification->category?->name ?? ($notification->category_id ? 'Category ID: ' . $notification->category_id : '-'),
                $notification->location?->store_name ?? ($notification->location_id ? 'Location ID: ' . $notification->location_id : '-'),
                $notification->defectType?->name ?? ($notification->defect_type_id ? 'Defect Type ID: ' . $notification->defect_type_id : '-'),
                $notification->problem ?? '-',
                $notification->priority ?? '-',
                $actions
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "plant_defect-noti";
        $series = [];
        $defectTypes = ErpDefectType::select('id', 'name')->get();
        $equipments = ErpEquipment::select('id', 'name')->get();
        $categories = Category::orderBy('id', 'desc')
             ->with('parent', 'subCategories')
             ->where('type', strtolower(ConstantHelper::EQUIPMENT))
             ->get();
      

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
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
            } else {
                $itemAttributes = [];
            }
            
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                    ->select('id', 'value')
                    ->where('status', 'active')
                    ->get();

                $attribute->values_data = $attributeValueData;
                $attribute = $attribute->only(['id', 'group_name', 'values_data', 'attribute_group_id']);

                array_push($processedData, [
                    'id' => $attribute['id'], 
                    'group_name' => $attribute['group_name'], 
                    'values_data' => $attributeValueData, 
                    'attribute_group_id' => $attribute['attribute_group_id']
                ]);
            }
            
            $processedData = collect($processedData);
            $item->attributes = $processedData;
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

        $locations = \App\Helpers\InventoryHelper::getAccessibleLocations();

        return view('plant.defect-notification.create', compact('series', 'items', 'locations','defectTypes','categories','equipments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base rules that are always required
        $rules = [
            'book_id' => 'required',
            'document_number' => 'required|string|max:100',
            'document_date' => 'required|date',
            'location_id' => 'required', // Location is always required
        ];

        // If not saving as draft, add additional required fields
        if ($request->document_status !== 'draft') {
            $rules = array_merge($rules, [
                'equipment_id' => 'required',
                'category_id' => 'required',
                'defect_type_id' => 'required',
                'priority' => 'required|in:Low,Medium,High,Critical',
                'problem' => 'required|string',
                'report_date_time' => 'required|date',
            ]);
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();
            
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
            
            $defectNotification = new DefectNotification();
            $defectNotification->fill($data);
            $defectNotification->document_status = $request->document_status ?? 'draft';

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = 'defect_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('defect_notifications/documents', $fileName, 'public');
                $defectNotification->attachment = $path;
            }

            $defectNotification->save();

            // Create DefectNotificationHistory record if document is submitted (not draft)
           if ($defectNotification->document_status != ConstantHelper::DRAFT) {
                    $doc = Helper::approveDocument(
                        $defectNotification->book_id,
                        $defectNotification->id,
                        $defectNotification->revision_number,
                        "",
                        null,
                        1,
                        'submit',
                        0,
                        get_class($defectNotification)
                    );

                    $defectNotification->document_status = $doc['approvalStatus'] ?? $defectNotification->document_status;
                    $defectNotification->save();
                }

            DB::commit();
            
            return redirect()
                ->route('defect-notification.index')
                ->with('success', 'Defect Notification created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating defect notification: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create defect notification: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
     /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $defectNotification = DefectNotification::findOrFail($id);
       
        $currNumber = $request->has('revisionNumber');
        
        if ($currNumber && $defectNotification->revision_number != $request->revisionNumber) {
            $currNumber = $request->revisionNumber;
            $defectNotification = DefectNotificationHistory::where('source_id', $id)
                ->where('revision_number', $currNumber)->first();
        }
      

        $parentURL = "plant_defect-noti";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $userType = Helper::userCheck();
        $revision_number = $defectNotification->revision_number;
      

        $buttons = Helper::actionButtonDisplay(
            $defectNotification->book_id,
            $defectNotification->document_status,
            $id,
            0,
            $defectNotification->approval_level,
            $defectNotification->created_by ?? 0,
            $userType['type'],
            $revision_number
        );

             
        $revNo = $request->has('revisionNumber') 
            ? intval($request->revisionNumber) 
            : $defectNotification->revision_number;
            
        $approvalHistory = Helper::getApprovalHistory(
            $defectNotification->book_id, 
            $id, 
            $revNo, 
            0,
            $defectNotification->created_by
        );

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$defectNotification->document_status] ?? '';
        
        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();
        foreach ($items as $item) {
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
            } else {
                $itemAttributes = [];
            }
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attributesArray = array();
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)->select('id', 'value')->where('status', 'active')->get();

                $attribute->values_data = $attributeValueData;
                $attribute = $attribute->only(['id', 'group_name', 'values_data', 'attribute_group_id']);

                array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributeValueData, 'attribute_group_id' => $attribute['attribute_group_id']]);
            }
            $processedData = collect($processedData);

            $item->attributes = $processedData;
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
        // Load categories, equipments, and defect types for the show view
        $categories = Category::orderBy('id', 'desc')
            ->with('parent', 'subCategories')
            ->where('type', strtolower(ConstantHelper::EQUIPMENT))
            ->get();
            
        $equipments = ErpEquipment::orderBy('id', 'desc')->get();
        $defectTypes = ErpDefectType::orderBy('id', 'desc')->get();
        $locations = \App\Helpers\InventoryHelper::getAccessibleLocations();

        return view('plant.defect-notification.show', compact(
            'series', 
            'items', 
            'defectNotification', 
            'buttons', 
            'docStatusClass', 
            'revision_number', 
            'currNumber', 
            'approvalHistory',
            'categories',
            'equipments', 
            'defectTypes',
            'locations'
        ));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $defectNotification = DefectNotification::findOrFail($id);

        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay(
            $defectNotification->book_id,
            $defectNotification->document_status,
            $id,
            0,
            $defectNotification->approval_level,
            $defectNotification->created_by ?? 0,
            $userType['type'],
            $defectNotification->revision_number
        );
        
        if ($defectNotification->document_status === ConstantHelper::DRAFT || $defectNotification->document_status === ConstantHelper::SUBMITTED)
            $buttons['cancel'] = true;
        else
            $buttons['cancel'] = false;

        if ($defectNotification->document_status === ConstantHelper::POSTED)
            $buttons['amend'] = false;


        $defectTypes = ErpDefectType::select('id', 'name')->get();
        $equipments = ErpEquipment::select('id', 'name')->get();
        $categories = Category::orderBy('id', 'desc')
             ->with('parent', 'subCategories')
             ->where('type', strtolower(ConstantHelper::EQUIPMENT))
             ->get();
        
        $parentURL = "plant_defect-noti";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'uom_name' => optional($item->uom)->name,
                    'uom_id' => optional($item->uom)->id,
                ];
            });

        $locations = \App\Helpers\InventoryHelper::getAccessibleLocations();

        $data = $defectNotification;
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

        $approvalHistory = Helper::getApprovalHistory(
            $data->book_id,
            $id,
            $revision_number,
            0,
            $data->created_by ?? 0
        );

        return view('plant.defect-notification.edit', compact('defectNotification', 'series','buttons','approvalHistory','items', 'locations', 'defectTypes', 'equipments', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $defectNotification = DefectNotification::findOrFail($id);
        $rules = [
            'document_date' => 'required|date',
            'location_id' => 'required',
        ];


        if ($request->document_status !== 'draft') {
            $rules = array_merge($rules, [
                'equipment_id' => 'required',
                'category_id' => 'required',
                'defect_type_id' => 'required',
                'priority' => 'required|in:Low,Medium,High,Critical',
                'problem' => 'required|string',
                'report_date_time' => 'required|date',
            ]);
        }

        // Add file validation if file is uploaded
        if ($request->hasFile('attachment')) {
            $rules['attachment'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'; // 10MB max
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = 'defect_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('defect_notifications/documents', $fileName, 'public');
            $defectNotification->attachment = $path;
        }

        $request->validate($rules);

        // Check for duplicate document number
        $documentNumber = $request->doc_no;
        $existingDefect = DefectNotification::where('doc_no', $documentNumber)
            ->where('id', '!=', $id)
            ->first();

        if ($existingDefect) {
            return redirect()
                ->route('defect-notification.edit', $id)
                ->withInput()
                ->withErrors("Document Number '{$documentNumber}' already exists.");
        }

        DB::beginTransaction();

        try {
            if ($request->action_type == "amendment") {
                // Validate amendment remarks
                $request->validate([
                    'amend_remarks' => 'required|string|max:1000',
                    'amend_attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
                ]);

                $revisionData = [
                    [
                        "model_type" => "header",
                        "model_name" => "DefectNotification",
                        "relation_column" => "",
                    ],
                ];
                
                // Create DefectNotificationHistory record before amendment
                $amendmentResult = Helper::documentAmendment($revisionData, $id);
                
                if (!$amendmentResult) {
                    throw new \Exception('Failed to create DefectNotificationHistory record during amendment');
                }
                
                Helper::approveDocument(
                    $defectNotification->book_id,
                    $defectNotification->id,
                    $defectNotification->revision_number,
                    $request->amend_remarks,
                    $request->file('amend_attachment'),
                    $defectNotification->approval_level,
                    'amendment',
                    0,
                    get_class($defectNotification)
                );
                
                // Update defect notification status and revision
                $defectNotification->document_status = ConstantHelper::DRAFT;
                $defectNotification->revision_number = $defectNotification->revision_number + 1;
                $defectNotification->approval_level = 1;
                $defectNotification->revision_date = now();
                $defectNotification->save();

                DB::commit();
                
                return redirect()
                    ->route('defect-notification.index')
                    ->with('success', 'Amendment submitted successfully! Document has been reset to draft status.');
            }
            

            $defectNotification->fill($request->except(['_token', '_method', 'upload_document']));
            
        
            $defectNotification->save();

             if ($defectNotification->document_status != ConstantHelper::DRAFT) {
                    $doc = Helper::approveDocument(
                        $defectNotification->book_id,
                        $defectNotification->id,
                        $defectNotification->revision_number,
                        "",
                        null,
                        1,
                        'submit',
                        0,
                        get_class($defectNotification)
                    );

                    $defectNotification->document_status = $doc['approvalStatus'] ?? $defectNotification->document_status;
                    $defectNotification->save();
                }

            DB::commit();
            
            return redirect()
                ->route('defect-notification.index')
                ->with('success', 'Defect Notification updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating defect notification: ' . $e->getMessage());
            
            return redirect()
                ->route('defect-notification.edit', $id)
                ->withInput()
                ->with('error', 'Failed to update defect notification: ' . $e->getMessage());
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $defectNotification = DefectNotification::findOrFail($id);
            
            if ($defectNotification->document_status !== 'draft') {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete submitted defect notifications.');
            }
            
            $defectNotification->delete();
            
            return redirect()
                ->route('defect-notification.index')
                ->with('success', 'Defect Notification deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Error deleting defect notification: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete defect notification.');
        }
    }
}
