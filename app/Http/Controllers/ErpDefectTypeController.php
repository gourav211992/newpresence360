<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\ErpDefectType;
use App\Models\Organization;
use App\Http\Requests\ErpDefectTypeRequest;

class ErpDefectTypeController extends Controller
{
    public function index(Request $request)
    {
        // dd('This is the index method of ErpDefectTypeController');
        $defectTypes = ErpDefectType::get();
        if ($request->ajax()) {
            return response()->json(['data' => $defectTypes]);
        }

        return view('defect-types.index', compact('defectTypes'));
    }

    public function store(ErpDefectTypeRequest $request)
    {
        
        $rows = $request->input('rows', []);
        $errors = [];
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        foreach ($rows as $row) {
            // Unique name validation per organization, skipping current id if updating
            $query = ErpDefectType::where('name', $row['name']);

            if (!empty($row['id'])) {
                $query->where('id', '!=', $row['id']);
            }

            if ($query->exists()) {
                $errors[] = "The name '<strong style='color:red'>{$row['name']}</strong>' has already been added in defect types.";
                continue;
            }

            ErpDefectType::updateOrCreate(
                [
                    'id' => $row['id'] ?? null,
                    'organization_id' => $organization->id,
                ],
                [
                    'group_id'      => $organization->group_id ?? null,
                    'company_id'    => $organization->company_id ?? null,
                    'name'          => $row['name'],
                    'priority'      => $row['priority'],
                    'estimated_time'=> $row['estimated_time'],
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
            return response()->json(['success' => 'Defect Types saved successfully!'], 200);
        }

        return redirect()->route('defect-types.index')->with('success', 'Defect Types saved successfully!');
    }

    public function delete(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            ErpDefectType::whereIn('id', $ids)->update(['deleted_by' => $user->id]);
            ErpDefectType::whereIn('id', $ids)->delete();
        }
        return response()->json(['success' => 'Defect Types deleted successfully'], 200);
    }
}

