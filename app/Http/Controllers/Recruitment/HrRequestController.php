<?php

namespace App\Http\Controllers\Recruitment;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobRequestLog;
use App\Models\Recruitment\ErpRecruitmentJobRequests;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentSkill;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrRequestController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $requests = ErpRecruitmentJobRequests::with('recruitmentSkills')
            ->where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('organization_id',$user->organization_id)
            ->orderBy('created_at','desc')
            ->paginate($length);

        $masterData = self::masterData();
        $summaryData = self::getRequestSummary($request, $user);

        return view('recruitment.hr-request.index',[
            'requests' => $requests,
            'user' => $user,
            'jobTitles' => $masterData['jobTitles'],
            'skills' => $masterData['skills'],
            'status' => CommonHelper::JOB_REQUEST_STATUS,
            'requestCount' => $summaryData['requestCount'],
            'rejectedRequestCount' => $summaryData['rejectedRequestCount'],
            'approvedRequestCount' => $summaryData['approvedRequestCount'],
            'pendingRequestCount' => $summaryData['pendingRequestCount'],
            'jobcreated' => $summaryData['jobcreated'],
        ]);
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

    private function masterData(){
        $user = Helper::getAuthenticatedUser();
        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $skills = ErpRecruitmentSkill::select('name','id')
            ->where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        return [
            'jobTitles' => $jobTitles,
            'skills' => $skills,
        ];

    }

    private function getRequestSummary($request, $user){
        $requestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('organization_id',$user->organization_id)
            ->count();

        $rejectedRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::REJECTED)
            ->count();
        
        $approvedRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::FINAL_APPROVED)
            ->count();

        $pendingRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::PENDING)
            ->count();

        $jobcreated = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('organization_id',$user->organization_id)
            ->whereNotNull('job_id')
            ->count();
        
        return [
            'requestCount' => $requestCount,
            'rejectedRequestCount' => $rejectedRequestCount,
            'approvedRequestCount' => $approvedRequestCount,
            'pendingRequestCount' => $pendingRequestCount,
            'jobcreated' => $jobcreated,
        ];
    }

    public function show($id){
        $jobRequest = ErpRecruitmentJobRequests::find($id);
        $requestCertifications = $jobRequest->recruitmentCertifications->pluck('name')->toArray();
        $requestSkills = $jobRequest->recruitmentSkills->pluck('name')->toArray();
        $jobRequestLogs = ErpRecruitmentJobRequestLog::where('job_request_id',$id)->orderBy('id','desc')->get();

        $job = NULL;
        $jobSkills = [];
        if($jobRequest->job_id){
            $job = ErpRecruitmentJob::withCount([
                        'assignedCandidates as newCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::ASSIGNED);
                        },'assignedCandidates as qualifiedCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::QUALIFIED);
                        },'assignedCandidates as notqualifiedCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::NOT_QUALIFIED);
                        },'assignedCandidates as onholdCanidatesCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::ONHOLD);
                        },'assignedCandidates as scheduledInterviewCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::SCHEDULED);
                        },'assignedCandidates as selectedCandidateCount' => function ($q) {
                            $q->where('erp_recruitment_assigned_candidates.status', CommonHelper::SELECTED);
                        },'assignedCandidates as totalAssginedCandidate'
                    ])->where('job_id',$jobRequest->job_id)->first();
            $jobSkills = $job->jobSkills->pluck('name')->toArray();

        }

        return view('recruitment.hr-request.show',[
            'jobRequest' => $jobRequest,
            'requestSkills' => $requestSkills,
            'requestCertifications' => $requestCertifications,
            'jobRequestLogs' => $jobRequestLogs,
            'job' => $job,
            'jobSkills' => $jobSkills,
        ]);
    }
}