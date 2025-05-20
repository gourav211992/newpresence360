<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\GovRelationManagementRequest;
use App\Http\Requests\RelationManagementRequest;
use App\Models\AuthUser;
use App\Models\Employee;
use App\Models\ErpGovRelationManagement;
use App\Models\ErpInteractionType;
use App\Models\ErpInvestorRelationManagement;
use App\Models\ErpStakeholderUserType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ErpGovRelationManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $stakeholders = Helper::getOrgWiseUserAndEmployees($organizationId);
        $interactionTypes = ErpInteractionType::where('main', 'gov-relation-management')->get();
        if ($request->ajax()) {
            $query = ErpGovRelationManagement::with(['userable', 'interactionType', 'book'])
                ->where('organization_id', $organizationId);
            if ($request->has('date_range') && $request->date_range != '') {
                $dates = explode(' to ', $request->date_range);
                $startDate = $dates[0] ?? null;
                $endDate = $dates[1] ?? null;
                if ($startDate && $endDate) {
                    $query->whereBetween('document_date', [$startDate, $endDate]);
                }
            }
            if ($request->has('customer_name') && $request->customer_name != '') {
                $userIds = AuthUser::where('name', 'LIKE', '%' . $request->customer_name . '%')->pluck('id')->toArray();
                $query->whereIn('userable_id', $userIds);
            }
            if ($request->has('interaction_type') && $request->interaction_type != '') {
                $query->where('interaction_type_id', $request->interaction_type);
            }
            $documents = $query->get();
            return DataTables::of($documents)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user_name ?? 'N/A';
                })
                ->addColumn('interaction_type_name', function ($row) {
                    return $row->interactionType->name ?? 'N/A';
                })
                ->addColumn('document_date', function ($row) {
                    return $row->document_date ? Carbon::parse($row->document_date)->format('d-m-Y') : 'N/A';
                })
                ->addColumn('book_name', function ($row) {
                    return $row->book->book_name ?? 'N/A';
                })
                ->addColumn('action_buttons', function ($row) {
                    $editUrl = route('gov-relation-management.edit', $row->id);
                    $deleteUrl = route('gov-relation-management.destroy', $row->id);

                    return '<div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="' . $editUrl . '">
                                   <i data-feather="edit-3" class="me-50"></i>
                                    <span>Edit</span>
                                </a>
                                <a href="#" class="dropdown-item text-danger delete-btn"
                                   data-url="' . $deleteUrl . '"
                                   data-message="Are you sure you want to delete this record?">
                                    <i data-feather="trash-2" class="me-50"></i>
                                    <span>Delete</span>
                                </a>
                            </div>
                        </div>';
                })
                ->rawColumns(['action_buttons'])
                ->make(true);
        }

        return view('gov-relation-management.index',compact('stakeholders','interactionTypes'));
    }

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $interactionTypes = ErpInteractionType::where('main', 'gov-relation-management')->get();
        $stakeholders = Helper::getOrgWiseUserAndEmployees($organization->id);
        $userTypes = ErpStakeholderUserType::all();
        $parentURL = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);

        if (empty($servicesBooks['services'])) {
            return redirect()->route('/');
        }

        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        return view('gov-relation-management.create', compact('interactionTypes', 'series', 'userTypes', 'stakeholders'));
    }

    public function store(GovRelationManagementRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $validated = array_merge($request->validated(), [
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
            'userable_id' => $request->userable_id,
            'created_by' => $user->auth_user_id,
            'type' => $user->authenticable_type,
        ]);

        $govRelation = ErpGovRelationManagement::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $govRelation,
        ]);
    }

    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $govRelationManagement = ErpGovRelationManagement::findOrFail($id);
        $interactionTypes = ErpInteractionType::where('main', 'gov-relation-management')->get();
        $stakeholders = Helper::getOrgWiseUserAndEmployees($organization->id);
        $userTypes = ErpStakeholderUserType::all();
        $parentURL = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);

        if (empty($servicesBooks['services'])) {
            return redirect()->route('/');
        }

        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        return view('gov-relation-management.edit', compact('govRelationManagement', 'interactionTypes', 'series', 'userTypes', 'stakeholders'));
    }

    public function update(GovRelationManagementRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $validated = array_merge($request->validated(), [
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
            'userable_id' => $request->userable_id,
            'created_by' => $user->auth_user_id,
            'type' => $user->authenticable_type,
        ]);

        $govRelation = ErpGovRelationManagement::findOrFail($id);
        $govRelation->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $govRelation,
        ]);
    }

    public function destroy($id)
    {
        try {
            $govRelation = ErpGovRelationManagement::findOrFail($id);
            $govRelation->delete();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
