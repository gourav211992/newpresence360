<?php

namespace App\Http\Controllers\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ErpStore;
use App\Models\Recruitment\ErpRecruitmentCertification;
use App\Models\Recruitment\ErpRecruitmentEducation;
use App\Models\Recruitment\ErpRecruitmentJobRequestLog;
use App\Models\Recruitment\ErpRecruitmentJobRequests;
use App\Models\Recruitment\ErpRecruitmentJobRequestSkill;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentSkill;
use App\Models\Recruitment\ErpRecruitmentWorkExperience;
use Illuminate\Http\Request;
use App\Lib\Validation\Recruitment\JobRequest as Validator;
use App\Models\Recruitment\ErpRecruitmentJob;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


class RequestController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $query = ErpRecruitmentJobRequests::with('recruitmentSkills')
            ->where(function($query) use($request){
                self::filter($request, $query);
            });

            if (\Request::route()->getName() == "recruitment.requests.for-approval") {
                $query->where('approval_authority',$user->id)
                ->whereIn('status',['pending','approved-forward']);
            } elseif (\Request::route()->getName() == "recruitment.requests.assigned-candidate") {
            
            } elseif (\Request::route()->getName() == "recruitment.requests.interview-scheduled") {
               
            } else {
                $query->where('created_by',$user->id)
                    ->where('created_by_type',$user->authenticable_type);
            }

        $requests = $query->orderBy('created_at','desc')->paginate($length);
            
        $requestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->count();

        $rejectedRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->where('status',CommonHelper::REJECTED)
            ->count();
        
        $requestForApprovalCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('approval_authority',$user->id)
            ->whereIn('status',['pending','approved-forward'])
            ->count();

        $openRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->where('status',CommonHelper::PENDING)
            ->count();

        $interviewScheduledCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->where('status',CommonHelper::INTERVIEW_SCHEDULED)
            ->count();

        $candidateAssignedRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->where('status',CommonHelper::ASSIGNED)
            ->count();

        $masterData = self::masterData();

        $dateRange = explode(' to ', $request->date_range);
        if (count($dateRange) < 2) {
            $start = Carbon::parse($dateRange[0])->format('d-m-Y');
            $formattedDateRange = $start;
        } else {
            $start = Carbon::parse($dateRange[0])->format('d-m-Y');
            $end = Carbon::parse($dateRange[1])->format('d-m-Y');
            $formattedDateRange = "$start to $end";
        }

        return view('recruitment.request.index',[
            'requests' => $requests,
            'user' => $user,
            'jobTitles' => $masterData['jobTitles'],
            'skills' => $masterData['skills'],
            'status' => CommonHelper::JOB_REQUEST_STATUS,
            'requestCount' => $requestCount,
            'formattedDateRange' => $formattedDateRange,
            'requestForApprovalCount' => $requestForApprovalCount,
            'rejectedRequestCount' => $rejectedRequestCount,
            'openRequestCount' => $openRequestCount,
            'interviewScheduledCount' => $interviewScheduledCount,
            'candidateAssignedRequestCount' => $candidateAssignedRequestCount,
        ]);
    }
    
    public function create(){
        $masterData = self::masterData();

        return view('recruitment.request.create',[
            'jobTitles' => $masterData['jobTitles'],
            'eduactions' => $masterData['eduactions'],
            'certifications' => $masterData['certifications'],
            'workExperiences' => $masterData['workExperiences'],
            'priorities' => CommonHelper::PRIORITY,
            'skills' => $masterData['skills'],
            'locations' => $masterData['locations'],
        ]);
    }

    public function store(Request $request){
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();

            $jobRequest = new ErpRecruitmentJobRequests();
            $jobRequest->job_type = $request->job_type;
            $jobRequest->organization_id = $user->organization_id;
            $jobRequest->job_title_id = $request->job_title_id;
            $jobRequest->no_of_position = $request->no_of_position;
            $jobRequest->education_id = $request->education_id;
            $jobRequest->certification_id = $request->certification_id;
            $jobRequest->work_exp_id = $request->work_exp_id; 
            $jobRequest->expected_doj = $request->expected_doj;
            $jobRequest->priority = $request->priority; 
            $jobRequest->job_description = $request->job_description; 
            $jobRequest->reason = $request->reason; 
            $jobRequest->status = CommonHelper::PENDING; 
            $jobRequest->assessment_required = 'no'; 
            $jobRequest->location_id = $request->location_id; 
            $jobRequest->emp_id = $request->emp_id ?? NULL; 
            $jobRequest->approval_authority = $user->manager_id; 
            $jobRequest->created_by = $user->id; 
            $jobRequest->created_by_type = $user->authenticable_type; 
            $jobRequest->save();

            foreach($request->skill as $skill){
                $skill = ErpRecruitmentSkill::firstOrCreate(
                    [
                        'name' => $skill, 
                        'organization_id' => $user->organization_id],
                    [
                        'name' => $skill, 
                        'organization_id' => $user->organization_id, 
                        'status' => 'active',
                        'created_by_type' => $user->authenticable_type,
                        'created_by' => $user->id 
                    ]
                );

                $jobRequestSkill = new ErpRecruitmentJobRequestSkill();
                $jobRequestSkill->job_request_id = $jobRequest->id;
                $jobRequestSkill->skill_id = $skill ? $skill->id : null;
                $jobRequestSkill->created_at = date('Y-m-d h:i:s');
                $jobRequestSkill->save();

            }

            $jobRequestLog = new ErpRecruitmentJobRequestLog();
            $jobRequestLog->organization_id = $user->organization_id;
            $jobRequestLog->next_approval_authority = $user->manager_id; 
            $jobRequestLog->job_request_id = $jobRequest->id;
            $jobRequestLog->status = $jobRequest->status;
            $jobRequestLog->log_message = 'Job request created'; 
            $jobRequestLog->action_by = $user->id;
            $jobRequestLog->action_by_type = $user->authenticable_type;
            $jobRequestLog->save();
        

            \DB::commit();
            return [
                "data" => null,
                "message" => "Job request created successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    private function masterData(){
        $user = Helper::getAuthenticatedUser();
        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();
            
        $eduactions = ErpRecruitmentEducation::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $certifications = ErpRecruitmentCertification::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $workExperiences = ErpRecruitmentWorkExperience::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $skills = ErpRecruitmentSkill::select('name','id')
            ->where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $locations = ErpStore::select('store_name','id')
            ->where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        return [
            'jobTitles' => $jobTitles,
            'eduactions' => $eduactions,
            'certifications' => $certifications,
            'workExperiences' => $workExperiences,
            'skills' => $skills,
            'locations' => $locations,
        ];

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

    public function edit($id){
        $masterData = self::masterData();

        $jobRequest = ErpRecruitmentJobRequests::find($id);
        $requestSkills = ErpRecruitmentJobRequestSkill::where('job_request_id',$id)->pluck('skill_id')->toArray();

        return view('recruitment.request.edit',[
            'jobTitles' => $masterData['jobTitles'],
            'eduactions' => $masterData['eduactions'],
            'certifications' => $masterData['certifications'],
            'workExperiences' => $masterData['workExperiences'],
            'priorities' => CommonHelper::PRIORITY,
            'skills' => $masterData['skills'],
            'locations' => $masterData['locations'],
            'jobRequest' => $jobRequest,
            'requestSkills' => $requestSkills,
        ]);
    }

    public function update(Request $request,$id){
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();

            $jobRequest = ErpRecruitmentJobRequests::find($id);
            $jobRequest->job_type = $request->job_type;
            $jobRequest->organization_id = $user->organization_id;
            $jobRequest->job_title_id = $request->job_title_id;
            $jobRequest->no_of_position = $request->no_of_position;
            $jobRequest->education_id = $request->education_id;
            $jobRequest->certification_id = $request->certification_id;
            $jobRequest->work_exp_id = $request->work_exp_id; 
            $jobRequest->expected_doj = $request->expected_doj;
            $jobRequest->priority = $request->priority; 
            $jobRequest->job_description = $request->job_description; 
            $jobRequest->reason = $request->reason; 
            // $jobRequest->assessment_required = $request->assessment_required; 
            $jobRequest->location_id = $request->location_id; 
            $jobRequest->emp_id = $request->emp_id ?? NULL; 
            $jobRequest->save();

            foreach($request->skill as $skill){
                $skill = ErpRecruitmentSkill::firstOrCreate(
                    [
                        'name' => $skill, 
                        'organization_id' => $user->organization_id],
                    [
                        'name' => $skill, 
                        'organization_id' => $user->organization_id, 
                        'status' => 'active',
                        'created_by_type' => $user->authenticable_type,
                        'created_by' => $user->id 
                    ]
                );

                ErpRecruitmentJobRequestSkill::updateOrCreate([
                    'job_request_id' => $jobRequest->id,
                    'skill_id' => $skill->id
                ]);

            }
        

            \DB::commit();
            return [
                "data" => null,
                "message" => "Job request updated successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function updateStatus(Request $request,$id){
        $validator = (new Validator($request))->updatestatus();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();
            $jobRequest = ErpRecruitmentJobRequests::find($id);
            $oldStatus = $jobRequest->status;
            $status = $request->status;

            if($status != $oldStatus){
                $jobRequest->status = $status;
                $jobRequest->reason = $request->log_message; 
                $managerId = null;
                
                if($status == CommonHelper::FINAL_APPROVED){
                    $jobRequest->approved_at = NOW();
                }

                if($status == CommonHelper::APPROVED_FORWARD){
                    $jobRequest->approval_authority = $user->manager_id;
                    $managerId = $user->manager_id ? $user->manager_id : null;
                }

                // if($status == CommonHelper::REJECTED){
                //     $jobRequest->approval_authority = null;
                // }

                $jobRequest->approval_authority = $managerId;
                $jobRequest->action_by = $user->id;
                $jobRequest->action_by_type = $user->authenticable_type;
                $jobRequest->save();

                // Job Requisition Log
                $jobRequestLog = new ErpRecruitmentJobRequestLog();
                $jobRequestLog->organization_id = $jobRequest->organization_id;
                $jobRequestLog->next_approval_authority = $managerId; 
                $jobRequestLog->job_request_id = $jobRequest->id;
                $jobRequestLog->status = $jobRequest->status;
                $jobRequestLog->log_message = $request->log_message; 
                $jobRequestLog->action_by = $user->id;
                $jobRequestLog->action_by_type = $user->authenticable_type;
                $jobRequestLog->save();
            }

            \DB::commit();
            return [
                'message' => "Job request is $status",
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function show($id){
        $user = Helper::getAuthenticatedUser();
        $jobRequest = ErpRecruitmentJobRequests::find($id);
        $requestSkills = $jobRequest->recruitmentSkills->pluck('name')->toArray();
        $jobRequestLogs = ErpRecruitmentJobRequestLog::where('job_request_id',$id)->orderBy('id','desc')->get();

        $job = NULL;
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
                        },'assignedCandidates as totalAssginedCandidate'
                    ])->where('job_id',$jobRequest->job_id)->first();
        }

        return view('recruitment.request.show',[
            'jobRequest' => $jobRequest,
            'requestSkills' => $requestSkills,
            'jobRequestLogs' => $jobRequestLogs,
            'user' => $user,
            'job' => $job,
        ]);
    }
}
