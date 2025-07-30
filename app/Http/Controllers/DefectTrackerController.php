<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Models\ErpMaintenanceDefectDetail;
use Illuminate\Http\Request;

class DefectTrackerController extends Controller
{
    public function index(Request $request)
    {
        $defects = ErpMaintenanceDefectDetail::query()
            ->with(['erpEquipSparepart.equipment.category', 'defectType', 'erpMaintenance'])
            ->orderBy('id', 'desc')
            ->where('tracking_status', '=', ConstantHelper::OPEN)
            ->get();

        return view('equipment.maintenance.defect-tracker.index', compact('defects'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tracking_status' => 'required',
            'tracking_remarks' => 'required|string',
            'tracking_attachment' => 'nullable|file|max:2048'
        ]);

        $defect = ErpMaintenanceDefectDetail::findOrFail($id);

        if ($defect->tracking_status === 'closed') {
            return response()->json(['message' => 'Defect is already closed'], 400);
        }

        if ($request->hasFile('tracking_attachment')) {
            $path = $request->file('tracking_attachment')->store('tracking_attachments', 'public');
            $defect->tracking_attachment = $path;
        }

        $defect->tracking_status = $request->tracking_status;
        $defect->tracking_remarks = $request->tracking_remarks;
        $defect->save();

        return response()->json(['message' => 'Defect updated successfully']);
    }
}
