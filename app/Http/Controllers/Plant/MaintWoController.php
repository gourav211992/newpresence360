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
use App\Models\DefectNotification;
use Exception;
use Illuminate\Support\Facades\DB;

class MaintWoController extends Controller
{
    public function index()
    {
        $data = PlantMaintWo::all();
        return view('plant.maint_wo.index', compact('data'));
    }
    
    public function show(Request $request, string $id)
    {
        $data = PlantMaintWo::find($id);
        $currNumber = $request->has('revisionNumber');
        
        // If revision number is provided and different from current
        if ($currNumber && $data->revision_number != $request->revisionNumber) {
            $currNumber = $request->revisionNumber;
            $data = PlantMaintWoHistory::where('source_id', $id)
                ->where('revision_number', $currNumber)
                ->first();
        } else {
            $data = PlantMaintWo::findOrFail($id);
        }

        $parentURL = "plant_maint-wo";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;

        // Get action buttons based on document status and user permissions
        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $id,
            0,  // For module ID, 0 if not applicable
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );

        // Determine which revision to show
        $revNo = $request->has('revisionNumber') 
            ? intval($request->revisionNumber) 
            : $data->revision_number;

        // Get approval history
        $approvalHistory = Helper::getApprovalHistory(
            $data->book_id, 
            $id, 
            $revNo, 
            0,  // Module ID, 0 if not applicable
            $data->created_by
        );

        // Get document status CSS class
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';

        // Load items for the view
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
                        'attribute_group_id' => $attribute->attribute_group_id
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

        // Get locations for the view
        $locations = \App\Helpers\InventoryHelper::getAccessibleLocations();

        return view('plant.maint_wo.show', compact(
            'series', 
            'items', 
            'data', 
            'buttons', 
            'docStatusClass', 
            'revision_number', 
            'currNumber', 
            'approvalHistory',
            'locations'
        ));
    }

    public function create()
    {
        $parentURL = "plant_maint-wo";
        $series = [];
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

            $locations = InventoryHelper::getAccessibleLocations();
          
        // Get defect notifications for the modal
        $defectNotifications = DefectNotification::with(['book', 'equipment', 'location', 'category', 'defectType'])
            ->where('document_status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();
        
        if (count($servicesBooks['services']) > 0) {
            $firstService = $servicesBooks['services'][0];
            $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        }  
        return view('plant.maint_wo.create', compact('series', 'locations','items', 'defectNotifications'));
    }

    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'book_id' => 'required',
            'document_number' => 'required|string|max:100',
            'document_date' => 'required|date',
            'document_status' => 'required|string',
        ];

        // Add reference type validation only if not saving as draft
        if ($request->document_status !== 'draft') {
            $rules['reference_type'] = 'required|string';
        }

        // Add file validation if file is uploaded
        if ($request->hasFile('upload_file')) {
            $rules['upload_file'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'; // 10MB max
        }

        $request->validate($rules);

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

        try {
            DB::transaction(function () use ($data, $request) {
                $workOrder = PlantMaintWo::create($data);

                // Handle file upload
                if ($request->hasFile('upload_file')) {
                    $file = $request->file('upload_file');
                    $originalName = $file->getClientOriginalName();
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
        $parentURL = "plant_maint-wo";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        // Load items with attributes
        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();
            
        foreach ($items as $item) {
            $itemId = $item->id;
            $itemAttributes = $itemId ? ItemAttribute::where('item_id', $itemId)->get() : [];
            
            $processedData = [];
            foreach ($itemAttributes as $attribute) {
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)
                    ->select('id', 'value')
                    ->where('status', 'active')
                    ->get();

                array_push($processedData, [
                    'id' => $attribute->id, 
                    'group_name' => $attribute->group_name, 
                    'values_data' => $attributeValueData, 
                    'attribute_group_id' => $attribute_group_id
                ]);
            }
            
            $item->attributes = collect($processedData);
        }
        
        // Transform items for the view
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

        // Get locations
        $locations = InventoryHelper::getAccessibleLocations();
        
        // Get defect notifications for the modal
        $defectNotifications = DefectNotification::with(['book', 'equipment', 'location', 'category', 'defectType'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get user type and set up action buttons
        $userType = Helper::userCheck();
        $revision_number = $workOrder->revision_number;
        
        $buttons = Helper::actionButtonDisplay(
            $workOrder->book_id,
            $workOrder->document_status,
            $id,
            0, // module_id
            $workOrder->approval_level,
            $workOrder->created_by ?? 0,
            $userType['type'],
            $revision_number
        );

        // Get approval history
        $approvalHistory = Helper::getApprovalHistory(
            $workOrder->book_id, 
            $id, 
            $revision_number, 
            0, // Module ID, 0 if not applicable
            $workOrder->created_by
        );

        // Get document status CSS class
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$workOrder->document_status] ?? '';

        return view('plant.maint_wo.edit', compact(
            'workOrder',
            'series',
            'items',
            'locations',
            'defectNotifications',
            'buttons',
            'approvalHistory',
            'docStatusClass',
            'revision_number'
        ));
    }

    public function update(Request $request, string $id)
    {
        // Validation rules
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

        // Check for duplicate document number
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
            // Handle amendment
            if ($request->action_type == "amendment") {
                $revisionData = [
                    [
                        "model_type" => "header",
                        "model_name" => "PlantMaintWo",
                        "relation_column" => "",
                    ],
                ];
                
                // Create revision history
                Helper::documentAmendment($revisionData, $id);
                
                // Process the amendment
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
                
                // Update revision number
                $request->merge([
                    'revision_number' => $workOrder->revision_number + 1,
                    'revision_date' => now()
                ]);
            }

            // Update the work order
            $workOrder->update($request->all());

            // Handle file upload
            if ($request->hasFile('upload_file')) {
                $file = $request->file('upload_file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = 'maint_wo_' . $workOrder->id . '_' . time() . '.' . $extension;
                $path = $file->storeAs('maint_wo_documents', $fileName, 'public');
                $workOrder->upload_file = $path;
                $workOrder->save();
            }

            // Handle submission for approval if not draft
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

    /**
     * Handle document approval actions
     */
    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
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
            $actionType = $request->action_type; // 'approve' or 'reject'
            $modelName = get_class($doc);

            // Process the approval
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

            // Update document status and approval level
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

    public function destroy(string $id)
    {
        // Add your delete logic here
    }
}
