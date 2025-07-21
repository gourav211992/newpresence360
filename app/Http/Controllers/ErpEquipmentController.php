<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErpEquipmentRequest;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\ErpMaintenanceType;
use App\Models\ErpEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErpEquipmentController extends Controller
{
    public function create()
    {
        $userOrganizations = Helper::getAuthenticatedUser()->organizations;
        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $categories = InventoryHelper::getAccessibleLocations();
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

        // dd($organizations, $locations);

        // You can fetch dropdowns via AJAX or here (for demo, keeping empty)
        return view('equipment.create', compact('userOrganizations', 'locations', 'categories', 'maintenanceTypes', 'approval_history'));
    }

    public function store(ErpEquipmentRequest $request)
    {
        DB::beginTransaction();
        try {
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
                'upload_document'   => $request->upload_document ?? null, // handle file upload below
                'final_remarks'     => $request->final_remarks,
                'book_id'           => 1, // Or get from elsewhere
                'document_number'   => '', // Generate as needed
                'document_status'   => 'Draft', // Or from request
                'created_by'        => auth()->id(),
            ]);

            // If document uploaded
            if ($request->hasFile('upload_document')) {
                $file = $request->file('upload_document');
                $path = $file->store('equipment_documents', 'public');
                $equipment->upload_document = $path;
                $equipment->save();
            }

            // Maintenance Details
            if ($request->maintenance) {
                foreach ($request->maintenance as $mRow) {
                    $maintenance = $equipment->maintenanceDetails()->create([
                        'type' => $mRow['type'],
                        'frequency' => $mRow['frequency'],
                        'time' => $mRow['time'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    // Checklist for this maintenance
                    if (!empty($mRow['checklists'])) {
                        foreach ($mRow['checklists'] as $check) {
                            $maintenance->checklists()->create([
                                'name' => $check['name'],
                                'description' => $check['description'] ?? null,
                                'type' => $check['type'] ?? null,
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            // Spare Parts
            if ($request->spareparts) {
                foreach ($request->spareparts as $sRow) {
                    $equipment->spareParts()->create([
                        'item_code' => $sRow['item_code'],
                        'item_name' => $sRow['item_name'],
                        'attributes' => json_encode($sRow['attributes'] ?? []),
                        'uom' => $sRow['uom'],
                        'qty' => $sRow['qty'],
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('equipment.index')->with('success', 'Equipment created successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
