<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\FeedbackProcessRequest;
use App\Http\Requests\PublicOutreachRequest;
use App\Models\AuthUser;
use App\Models\Employee;
use App\Models\ErpInteractionType;
use App\Models\ErpPublicOutreachAndCommunication;
use App\Models\ErpStakeholderUserType;
use App\Models\FeedbackProcess;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ErpPublicOutreachAndCommunicationController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $stakeholders = Helper::getOrgWiseUserAndEmployees($organizationId);
        $interactionTypes = ErpInteractionType::where('main', 'public-outreach')->get();
        if ($request->ajax()) {
            $query = ErpPublicOutreachAndCommunication::with(['userable', 'interactionType', 'book'])
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
                    $editUrl = route('public-outreach.edit', $row->id);
                    $deleteUrl = route('public-outreach.destroy', $row->id);

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

        return view('public-outreach.index',compact('stakeholders','interactionTypes'));
    }

    public function create() {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization->id;
        $parentURL = request()->segments()[0];

        $interactionTypes = ErpInteractionType::where('main', 'public-outreach')->get();
        $stakeholders = Helper::getOrgWiseUserAndEmployees($organizationId);
        $userTypes = ErpStakeholderUserType::all();
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);

        if (empty($servicesBooks['services'])) {
            return redirect()->route('/');
        }

        $series = Helper::getBookSeriesNew($servicesBooks['services'][0]->alias, $parentURL)->get();
        return view('public-outreach.create', compact('interactionTypes', 'series', 'userTypes', 'stakeholders'));
    }

    public function store(PublicOutreachRequest $request) {
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

        $complaint = ErpPublicOutreachAndCommunication::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $complaint,
        ]);
    }

    public function edit($id) {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization->id;
        $parentURL = request()->segments()[0];

        $publicOutreach = ErpPublicOutreachAndCommunication::findOrFail($id);
        $interactionTypes = ErpInteractionType::where('main', 'public-outreach')->get();
        $stakeholders = Helper::getOrgWiseUserAndEmployees($organizationId);
        $userTypes = ErpStakeholderUserType::all();
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);

        if (empty($servicesBooks['services'])) {
            return redirect()->route('/');
        }

        $series = Helper::getBookSeriesNew($servicesBooks['services'][0]->alias, $parentURL)->get();

        return view('public-outreach.edit', compact('publicOutreach', 'interactionTypes', 'series', 'userTypes', 'stakeholders'));
    }

    public function update(PublicOutreachRequest $request, $id) {
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

        $feedback = ErpPublicOutreachAndCommunication::findOrFail($id);
        $feedback->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $feedback,
        ]);
    }

    public function destroy($id) {
        try {
            $document = ErpPublicOutreachAndCommunication::findOrFail($id);
            $document->delete();

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
