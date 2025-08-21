<?php

namespace App\Http\Controllers\Kaizen;

use App\Helpers\Helper;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Kaizen\DesignationRequest;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;

        if ($request->ajax()) {
            $designationMasters = Designation::where('organization_id', $organizationId)
                // ->where('status','active')
                ->orderBy('id', 'desc');

            return DataTables::of($designationMasters)
                ->addIndexColumn()
                ->editColumn('name', function ($designation) {
                    return $designation->name ?? 'N/A';
                })->editColumn('marks', function ($designation) {
                    return $designation->marks ?? 0;
                })
                ->editColumn('status', function ($designation) {
                    $statusClass = 'badge-light-secondary';

                    if ($designation->status == 'active') {
                        $statusClass = 'badge-light-success';
                    } elseif ($designation->status == 'inactive') {
                        $statusClass = 'badge-light-danger';
                    } elseif ($designation->status == 'draft') {
                        $statusClass = 'badge-light-warning';
                    }

                    return '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($designation->status ?? 'Unknown') . '</span>';
                })
                ->addColumn('actions', function ($designation) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item text-warning edit-btn"
                                    data-id="' . $designation->id . '"
                                    data-name="' . $designation->name . '"
                                    data-marks="' . $designation->marks . '"
                                    data-status="' . $designation->status . '">
                                    <i data-feather="edit" class="me-50"></i> Edit
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }
        $status = ConstantHelper::STATUS;

        return view('kaizen.designation.index', compact('organizationId', 'status'));
    }

    public function update(DesignationRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // $validated = $request->validated();
            $designation = Designation::findOrFail($id);
            $designation->update([
                'marks' => $request->input('marks'),
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $designation,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the designation: ' . $e->getMessage()
            ], 500);
        }
    }
}
