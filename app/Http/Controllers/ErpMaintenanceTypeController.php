<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\ErpMaintenanceType;
use App\Models\Organization;
use App\Http\Requests\ErpMaintenanceTypeRequest;

class ErpMaintenanceTypeController extends Controller
{
   public function index(Request $request)
    {
        // Get the authenticated user and their organization
        $maintenanceTypes = ErpMaintenanceType::get();
        if ($request->ajax()) {
            return response()->json(['data' => $maintenanceTypes]);
        }

        return view('maintenance-types.index', compact('maintenanceTypes'));
    }

    public function store(ErpMaintenanceTypeRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $rows = $request->input('rows', []);
        $errors = [];

        foreach ($rows as $row) {
            // Unique name validation per organization, skipping current id if updating
            $query = ErpMaintenanceType::where('name', $row['name']);

            if (!empty($row['id'])) {
                $query->where('id', '!=', $row['id']);
            }

            if ($query->exists()) {
                $errors[] = "The name '<strong style='color:red'>{$row['name']}</strong>' has already been added in maintenance types.";
                continue;
            }

            ErpMaintenanceType::updateOrCreate(
                [
                    'id' => $row['id'] ?? null,
                    'organization_id' => $organization->id,
                ],
                [
                    'group_id'      => $organization->group_id ?? null,
                    'company_id'    => $organization->company_id ?? null,
                    'name'          => $row['name'],
                    'description'   => $row['description'] ?? null,
                    'status'        => $row['status'] ?? 'Active',
                    'created_by'    => $user->id,
                    'updated_by'    => $user->id,
                ]
            );
        }

        if ($errors) {
            if ($request->ajax()) {
                return response()->json(['errors' => $errors], 422);
            }
            return back()->withErrors(['rows' => $errors])->withInput();
        }

        if ($request->ajax()) {
            return response()->json(['success' => 'Maintenance Types saved successfully!'], 200);
        }

        return redirect()->route('maintenance-types.index')->with('success', 'Maintenance Types saved successfully!');

    }

    public function delete(Request $request)
    {
        // $user = Helper::getAuthenticatedUser();
        
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            ErpMaintenanceType::whereIn('id', $ids)->delete();
        }
        return response()->json(['success' => 'Maintenance Types deleted successfully'], 200);
    }
}
