<?php

namespace App\Http\Controllers\Kaizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Organization;
use App\Helpers\Helper;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;

        if ($request->ajax()) {
            $designationMasters = Designation::
                // where('organization_id', $organizationId)->
                orderBy('id', 'desc');

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

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            // $validated = $request->validated();
            $discountMaster = Designation::findOrFail($id);
            $discountMaster->update([
                'marks' => $request->input('marks'),
                'status' => $request->input('status'),
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $discountMaster,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the Discount Master: ' . $e->getMessage()
            ], 500);
        }
    }
}
