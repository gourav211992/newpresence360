<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErpEquipmentRequest;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\Category;
use App\Models\ErpEquipMaintenanceChecklist;
use App\Models\ErpEquipMaintenanceDetail;
use App\Models\ErpMaintenanceType;
use App\Models\ErpEquipment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErpEquipmentController extends Controller
{
    public function index()
    {
        $equipments = ErpEquipment::with(['organization', 'location', 'spareParts', 'maintenanceDetails.checklists'])->get();
        return view('equipment.index', compact('equipments'));
    }
    public function create()
    {
        $userOrganizations = Helper::getAuthenticatedUser()->organizations;
        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        // $categories = InventoryHelper::getAccessibleLocations();
        $approval_history = [
            [
                'name' => 'Deepak Kumar',
                'status' => 'Amendment',
                'badge' => 'primary',
                'desc' => 'Description will come here',
                'time' => '2 min ago',
            ],
            [
                'name' => 'Aniket Singh',
                'status' => 'Rejected',
                'badge' => 'danger',
                'desc' => 'Description will come here',
                'time' => '2 min ago',
            ],
            [
                'name' => 'Deewan Singh',
                'status' => 'Pending',
                'badge' => 'warning',
                'desc' => 'Description will come here',
                'time' => '4 min ago',
            ],
        ];

        $checklists = [
            (object)[
                'id' => 1,
                'name' => 'Checklist 1',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 2,
                'name' => 'Checklist 2',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 3,
                'name' => 'Checklist 3',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 4,
                'name' => 'Checklist 4',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 5,
                'name' => 'Checklist 5',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
        ];

        $items = Item::get();
        $categories = Category::orderBy('id', 'desc'); // only pass equipment type later will add this check
        // dd($organizations, $locations);

        // You can fetch dropdowns via AJAX or here (for demo, keeping empty)
        return view('equipment.create', compact('userOrganizations', 'locations', 'categories', 'maintenanceTypes', 'approval_history','items', 'checklists'));
    }

    public function store(ErpEquipmentRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            
            // Store Equipment
            $equipment = ErpEquipment::create([
                'organization_id'   => $request->organization_id,
                'group_id'          => $request->group_id ?? null,
                'company_id'        => $request->company_id ?? null,
                'category_id'       => $request->category_id,
                'location_id'       => $request->location_id,
                'name'              => $request->name,
                'alias'             => $request->alias,
                'description'       => $request->description,
                'upload_document'   => null, // Will handle file upload below
                'final_remarks'     => $request->final_remarks,
                'book_id'           => 1, // Or get from elsewhere
                'document_number'   => '', // Generate as needed
                'document_status'   => $request->status, // From request
                'created_by'        => $user->auth_user_id,
            ]);

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
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $equipment = ErpEquipment::with([
            'spareParts',
            'maintenanceDetails.checklists'
        ])->findOrFail($id);

        $userOrganizations = Helper::getAuthenticatedUser()->organizations;
        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $items = Item::get();
        $categories = Category::orderBy('id', 'desc')->get();

        // Dummy approval history (replace as needed)
        $approval_history = [
            [
                'name' => 'Deepak Kumar',
                'status' => 'Amendment',
                'badge' => 'primary',
                'desc' => 'Description will come here',
                'time' => '2 min ago',
            ],
            [
                'name' => 'Aniket Singh',
                'status' => 'Rejected',
                'badge' => 'danger',
                'desc' => 'Description will come here',
                'time' => '2 min ago',
            ],
            [
                'name' => 'Deewan Singh',
                'status' => 'Pending',
                'badge' => 'warning',
                'desc' => 'Description will come here',
                'time' => '4 min ago',
            ],
        ];

        $checklists = [
            (object)[
                'id' => 1,
                'name' => 'Checklist 1',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 2,
                'name' => 'Checklist 2',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 3,
                'name' => 'Checklist 3',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 4,
                'name' => 'Checklist 4',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
            (object)[
                'id' => 5,
                'name' => 'Checklist 5',
                'description' => 'Description will come here',
                'type' => 'Text'
            ],
        ];

        return view('equipment.edit', compact(
            'equipment',
            'userOrganizations',
            'locations',
            'categories',
            'maintenanceTypes',
            'approval_history',
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
                'organization_id'   => $request->organization_id,
                'group_id'          => $request->group_id ?? null,
                'company_id'        => $request->company_id ?? null,
                'category_id'       => $request->category_id,
                'location_id'       => $request->location_id,
                'name'              => $request->name,
                'alias'             => $request->alias,
                'description'       => $request->description,
                'final_remarks'     => $request->final_remarks,
                'document_status'   => $request->status,
            ]);

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
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
