<?php

namespace App\Http\Controllers\Recruitment;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use App\Models\Recruitment\ErpRecruitmentJobRequests;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $summaryData = self::getJobSummary($request, $user);

        // Active jobs
        $activeJobs = ErpRecruitmentJob::withCount(['assignedCandidates as qualifiedCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::QUALIFIED);
                        },'assignedCandidates as onholdCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::ONHOLD);
                        },'assignedCandidates as selectedCandidateCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::SELECTED);
                        },'assignedCandidates as totalAssginedCandidate'
                    ])
                    ->whereHas('requests', function($q) use($user){
                        $q->where('created_by',$user->id)
                        ->where('created_by_type',$user->authenticable_type);
                    })
                    ->where('status',CommonHelper::OPEN)
                    ->get();

        // Job Applications
        $jobTtitles = ErpRecruitmentJobTitle::withCount([
                        'jobs as openJobCount' =>  function($q){
                            $q->where('status', CommonHelper::OPEN);
                        },
                        'jobs as closedJobCount' =>  function($q){
                            $q->where('status', CommonHelper::CLOSED);
                        },'requests as requestCount'
                    ])
                    ->orderBy('requestCount', 'desc')
                    ->where('status',CommonHelper::ACTIVE)
                    ->where('organization_id',$user->organization_id)
                    ->get();

        // Applicants list
        $jobIds = $activeJobs->pluck('id')->toArray();
        $applicants = self::getApplicants($jobIds);
        // dd($assignedCandidates->toArray());

        return view('recruitment.index',[
            'totalRequestCount' => $summaryData['requestCount'],
            'requestForApprovalCount' => $summaryData['requestForApprovalCount'],
            'currentOpeningCount' => $summaryData['currentOpeningCount'],
            'selectedCount' => $summaryData['selectedCount'],
            'activeJobs' => $activeJobs,
            'jobTtitles' => $jobTtitles,
            'applicants' => $applicants,
        ]);
    }

    public function hrDashboard(){
        return view('recruitment.index');
    }

    private function getJobSummary($request, $user){
        $requestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->count();
        
        $requestForApprovalCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('approval_authority',$user->id)
            ->whereIn('status',[CommonHelper::PENDING,CommonHelper::APPROVED_FORWARD])
            ->count();

        $currentOpeningCount = ErpRecruitmentJob::where('status',CommonHelper::OPEN)
                    ->where('organization_id',$user->organization_id)
                    ->where('publish_for',CommonHelper::INTERNAL)
                    ->count();

        $selectedCount = ErpRecruitmentJobInterview::whereHas('job.requests', function ($q) use ($user) {
                            $q->where('created_by', $user->id)
                            ->where('created_by_type', $user->authenticable_type);
                        })
                        ->where('status', CommonHelper::SELECTED)
                        ->count();
        
        return [
            'requestCount' => $requestCount,
            'requestForApprovalCount' => $requestForApprovalCount,
            'currentOpeningCount' => $currentOpeningCount,
            'selectedCount' => $selectedCount,
        ];
    }

    private function getApplicants($jobIds){
        $startDate = Carbon::today();
        $endDate = Carbon::today();

        // Check if there's an applied date filter
        // if ($request->has('type') && $request->type == 'last week') {
        //     $startDate = Carbon::now()->subWeek()->startOfWeek();
        //     $endDate = Carbon::now()->subWeek()->endOfWeek();
        // }elseif($request->has('type') && $request->type == 'last month'){
        //     $startDate = Carbon::now()->subMonth()->startOfMonth();
        //     $endDate = Carbon::now()->subMonth()->endOfMonth();
        // }

        $applicants = ErpRecruitmentJobCandidate::with(['jobDetail' => function($q){
                                $q->select('erp_recruitment_job.id','erp_recruitment_job.job_title_id');
                            }])
                            ->whereHas('assignedJob',function($q) use($jobIds,$startDate,$endDate){
                                $q->whereIn('job_id',$jobIds)
                                ->whereBetween('created_at',[$startDate,$endDate])
                                ->where('status',CommonHelper::ASSIGNED);
                            })
                            ->select('id','name')
                            ->get();

        return $applicants;
    }

    private function filter($request, $query){
        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }

        if ($request->job_title) {
            $query->where('job_title_id', $request->job_title);
        }

        if ($request->skill) {
            $query->whereHas('recruitmentSkills', function ($q) use($request) {
                $q->where('skill_id', $request->skill);
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use($request){
                $q->where('job_id', 'like', '%'.$request->search.'%')
                ->orWhere('request_id', 'like', '%'.$request->search.'%')
                ->orWhere('status', 'like', '%'.$request->search.'%');
            });
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);

        return $query;
    }

    public function fetchEmployees(Request $request)
    {
        $search = $request->get('search'); // The search term from the select2
        $page = $request->get('page', 1);  // The current page from select2

        if ($request->has('id')) {
            $employee = Employee::select('id','name','email','mobile')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee ? [ $employee ] : [],
            ]);
        }

        $employees = Employee::select('id','name','email','mobile')
                        ->where('name', 'like', '%' . $search . '%')
                        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'more' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ]);
    }

    public function fetchEmails(Request $request)
    {
        $search = $request->get('search'); // The search term from the select2
        $page = $request->get('page', 1);  // The current page from select2

        if ($request->has('id')) {
            $employee = Employee::select('id','email')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee ? [ $employee ] : [],
            ]);
        }

        $employees = Employee::select('id','email')
                        ->where('email', 'like', '%' . $search . '%')
                        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'more' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ]);
    }
}
