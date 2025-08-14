<?php

namespace App\Http\Controllers\Recruitment;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobLog;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use App\Models\Recruitment\ErpRecruitmentJobRequests;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentUserConfiguration;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $summaryData = self::getJobSummary($request, $user);
        $configuration = ErpRecruitmentUserConfiguration::where([
                'user_id' => $user->id, 
                'user_type' => $user->authenticable_type
            ])->first();

        // Active jobs
        $activeJobs = ErpRecruitmentJob::withCount(['assignedCandidates as qualifiedCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::QUALIFIED);
                        },'assignedCandidates as onholdCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::ONHOLD);
                        },'assignedCandidates as selectedCandidateCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::SELECTED);
                        },'assignedCandidates as totalAssginedCandidate'
                    ])
                    ->where(function($query) use($request){
                        self::filter($request, $query);
                    })
                    ->whereHas('requests', function($q) use($user){
                        $q->where('created_by',$user->id)
                        ->where('created_by_type',$user->authenticable_type);
                    })
                    ->where('status',CommonHelper::OPEN)
                    ->get();

        // Job Applications
        $jobTitles = ErpRecruitmentJobTitle::withCount([
                        'jobs as openJobCount' =>  function($q) use($request){
                            $q->where('status', CommonHelper::OPEN)
                            ->where(function($query) use($request){
                                self::filter($request, $query);
                            });
                        },
                        'jobs as closedJobCount' =>  function($q) use($request){
                            $q->where('status', CommonHelper::CLOSED)
                            ->where(function($query) use($request){
                                self::filter($request, $query);
                            });
                        },'requests as requestCount' =>  function($q) use($request){
                            $q->where(function($query) use($request){
                                self::filter($request, $query);
                            });
                        }
                    ])
                    ->orderBy('requestCount', 'desc')
                    ->where('status',CommonHelper::ACTIVE)
                    ->where('organization_id',$user->organization_id)
                    ->get();

        $interviewLogs = self::interviewLog($user->id);

        return view('recruitment.index',[
            'totalRequestCount' => $summaryData['requestCount'],
            'requestForApprovalCount' => $summaryData['requestForApprovalCount'],
            'currentOpeningCount' => $summaryData['currentOpeningCount'],
            'selectedCount' => $summaryData['selectedCount'],
            'activeJobs' => $activeJobs,
            'jobTitles' => $jobTitles,
            'user' => $user,
            'interviewLogs' => $interviewLogs,
            'configuration' => $configuration,
        ]);
    }

    public function hrDashboard(){
        // return view('recruitment.index');
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

        $currentOpeningCount = ErpRecruitmentJob::where(function($query) use($request){
                        self::filter($request, $query);
                    })
                    ->where('status',CommonHelper::OPEN)
                    ->where('organization_id',$user->organization_id)
                    ->where('publish_for',CommonHelper::INTERNAL)
                    ->count();

        $selectedCount = ErpRecruitmentJobInterview::where(function($query) use($request){
                            self::interviewFilter($request, $query);
                        })
                        ->whereHas('job.requests', function ($q) use ($user) {
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

    public function fetchApplicants(Request $request){
        $user = Helper::getAuthenticatedUser();
        $jobIds = ErpRecruitmentJob::whereHas('requests', function($q) use($user){
                        $q->where('created_by',$user->id)
                        ->where('created_by_type',$user->authenticable_type);
                    })
                    ->where('status',CommonHelper::OPEN)
                    ->pluck('id')
                    ->toArray();

        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        // Check if there's an applied date filter
        if ($request->has('type') && $request->type == 'last_week') {
            $startDate = Carbon::now()->subWeek()->startOfWeek();
            $endDate = Carbon::now()->subWeek()->endOfWeek();
        }elseif($request->has('type') && $request->type == 'last_month'){
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();
        }

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
                            // dd($applicants,$startDate,$endDate);

        return view('recruitment.partials.applicants-list', [
            'applicants' => $applicants
        ])->render();
    }

    public function interviewSummary(Request $request){
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if($request->type == 'last_month'){
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();
        }

        if($request->type == 'last_3_month'){
            $startDate = Carbon::now()->subMonths(3)->startOfMonth();
            $endDate = Carbon::now()->subMonths(3)->endOfMonth(); 
        }

        $user = Helper::getAuthenticatedUser();
        $jobIds = ErpRecruitmentJobPanelAllocation::where('panel_id',$user->id)->pluck('job_id')->toArray();
        $roundIds = ErpRecruitmentJobPanelAllocation::where('panel_id', $user->id)->pluck('round_id')->toArray();
        
        $scheduledCount = ErpRecruitmentJobInterview::whereIn('job_id',$jobIds)
                ->whereIn('round_id', $roundIds)
                ->where('status',CommonHelper::SCHEDULED)
                ->whereBetween('date_time',[$startDate,$endDate])
                ->count();

        $selectedCount = ErpRecruitmentJobInterview::whereIn('job_id',$jobIds)
                ->whereIn('round_id', $roundIds)
                ->where('status',CommonHelper::SELECTED)
                ->whereBetween('date_time',[$startDate,$endDate])
                ->count();

        $rejectCount = ErpRecruitmentJobInterview::whereIn('job_id',$jobIds)
                ->whereIn('round_id', $roundIds)
                ->where('status',CommonHelper::REJECTED)
                ->whereBetween('date_time',[$startDate,$endDate])
                ->count();
        
        $holdCount = ErpRecruitmentJobInterview::whereIn('job_id',$jobIds)
                ->whereIn('round_id', $roundIds)
                ->where('status',CommonHelper::ONHOLD)
                ->whereBetween('date_time',[$startDate,$endDate])
                ->count();

        return response()->json([
            'scheduledCount' => $scheduledCount,
            'selectedCount' => $selectedCount,
            'rejectCount' => $rejectCount,
            'holdCount' => $holdCount,
            'html' => view('recruitment.partials.interview-summary', [
                'scheduledCount' => $scheduledCount,
                'selectedCount' => $selectedCount,
                'rejectCount' => $rejectCount,
                'holdCount' => $holdCount
            ])->render()
        ]);
    }

    public function getInterviewEvents(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $user = Helper::getAuthenticatedUser();

        $jobIds = ErpRecruitmentJobPanelAllocation::where('panel_id', $user->id)->pluck('job_id');
        $roundIds = ErpRecruitmentJobPanelAllocation::where('panel_id', $user->id)->pluck('round_id');

        $interviews = ErpRecruitmentJobInterview::whereIn('job_id', $jobIds)
            ->whereIn('round_id', $roundIds)
            ->where('status', CommonHelper::SCHEDULED) // Or other statuses if needed
            ->whereBetween('date_time', [$start, $end])
            ->get();

        $events = [];

        foreach ($interviews as $interview) {
            $date = Carbon::parse($interview->date_time)->toDateString();

            $events[] = [
                'title' => 'Interview - ' . optional($interview->candidate)->name,
                'start' => $date,
                'color' => Carbon::parse($interview->date_time)->isPast() ? 'previous' : 'present',
                'interviewLink' => $interview->meeting_link,  // Add the interview link here
                'interviewTime' => Carbon::parse($interview->date_time)->format('H:i'),  // Add the interview time here
                'status' => $interview->status,                // Add the status here
                'candidateName' => optional($interview->candidate)->name, 
            ];
        }

        // Add Holidays (Sat/Sun)
        $current = $start->copy();
        while ($current <= $end) {
            if (in_array($current->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $events[] = [
                    'title' => 'Holiday',
                    'start' => $current->toDateString(),
                    'color' => '',
                    'holiday' => true
                ];
            }
            $current->addDay();
        }

        return response()->json($events);
    }

    private function interviewLog($userId){
        $jobIds = ErpRecruitmentJobPanelAllocation::where('panel_id', $userId)->pluck('job_id');
        $roundIds = ErpRecruitmentJobPanelAllocation::where('panel_id', $userId)->pluck('round_id');
       
        $logs = ErpRecruitmentJobLog::with(['interview' => function($q){
                    $q->select('id','round_id','job_id','candidate_id','date_time');
                }, 'job' => function($query){
                    $query->select('id','job_title_id');
                },'panels' => function($q) use($jobIds,$roundIds){
                    $q->select('employees.id','name')
                    ->whereIn('job_id', $jobIds)
                    ->whereIn('round_id',$roundIds);
                }])->whereHas('interview', function($query) use ($roundIds) {
                    $query->whereIn('round_id', $roundIds);
                })
                ->whereIn('job_id', $jobIds)
                ->where('log_type', CommonHelper::INTERVIEW)
                ->where('status', CommonHelper::SCHEDULED)
                ->get();

        return $logs;
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

        $query->whereBetween('created_at', [$startDate, $endDate]);

        return $query;
    }

    private function interviewFilter($request, $query){
        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }

        $query->whereBetween('date_time', [$startDate, $endDate]);

        return $query;
    }


    public function fetchEmployees(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
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
                        ->where('organization_id',$user->organization_id)
                        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'more' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ]);
    }

    public function fetchCandidates(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $search = $request->get('search'); // The search term from the select2
        $page = $request->get('page', 1);  // The current page from select2

        if ($request->has('id')) {
            $employee = ErpRecruitmentJobCandidate::select('id','name','email','mobile_no')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee ? [ $employee ] : [],
            ]);
        }

        $employees = ErpRecruitmentJobCandidate::select('id','name','email','mobile_no')
                        ->where('name', 'like', '%' . $search . '%')
                        ->where('organization_id',$user->organization_id)
                        ->paginate(10);
                        // dd($employees,$user->organization_id);

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

    public function userConfiguration(Request $request){
        $user = Helper::getAuthenticatedUser();

        $configuration = ErpRecruitmentUserConfiguration::where(['user_id' => $user->id, 'user_type' => $user->authenticable_type])->first();
        if(!$configuration){
            $configuration = new ErpRecruitmentUserConfiguration();
        }

        $configuration->user_id = $user->id;
        $configuration->user_type = $user->authenticable_type;
        $configuration->current_opening = $request->current_opening ? $request->current_opening : 0;
        $configuration->interview_summary = $request->interview_summary ? $request->interview_summary : 0;
        $configuration->my_scheduled = $request->my_scheduled ? $request->my_scheduled : 0;
        $configuration->activity = $request->activity ? $request->activity : 0;
        $configuration->new_applicants = $request->new_applicants ? $request->new_applicants : 0;
        $configuration->save();
        return [
            "data" => null,
            "message" => "Configuration save successfully!"
        ];
    }

    public function fetchTeam(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
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
                        ->where('manager_id',$user->id)
                        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'more' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ]);
    }

    public function getLocations($groupId){
        $organizations = Organization::select('id','name')->where('group_id',$groupId)->get();
        return [
            'data' => $organizations
        ];
    }
}
