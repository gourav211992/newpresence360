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
use App\Models\OrganizationCompany;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobLog;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use App\Models\Recruitment\ErpRecruitmentJobRequestCertification;
use App\Models\Recruitment\ErpRecruitmentRound;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


class RequestController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $query = ErpRecruitmentJobRequests::with('recruitmentSkills');

        self::filter($request, $query);   // Filtering
        self::sorting($request, $query);  // Sorting
        
        $query->when(\Request::route()->getName() === "recruitment.requests.for-approval", function($q) use ($user) {
            $q->where('approval_authority', $user->id)
            ->whereIn('status', ['pending', 'approved-forward']);
        }, function($q) use ($user) {
            $q->where('created_by', $user->id)
            ->where('created_by_type', $user->authenticable_type);
        });

        $requests = $query->paginate($length);

        $masterData = self::masterData();
        $summaryData = self::getJobSummary($request, $user);

        return view('recruitment.request.index',[
            'requests' => $requests,
            'user' => $user,
            'jobTitles' => $masterData['jobTitles'],
            'skills' => $masterData['skills'],
            'status' => CommonHelper::JOB_REQUEST_STATUS,
            'requestCount' => $summaryData['requestCount'],
            'requestForApprovalCount' => $summaryData['requestForApprovalCount'],
            'rejectedRequestCount' => $summaryData['rejectedRequestCount'],
            'openRequestCount' => $summaryData['openRequestCount'],
            'interviewScheduledCount' => $summaryData['interviewScheduledCount'],
            'candidateAssignedRequestCount' => $summaryData['candidateAssignedRequestCount'],
        ]);
    }

    private function masterData(){
        $user = Helper::getAuthenticatedUser();
        $groupId = $user->group_id;

        $companies = OrganizationCompany::where('group_id',$groupId)->get();

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
            'companies' => $companies,
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
                ->orWhere('job_type', 'like', '%'.$request->search.'%')
                ->orWhere('status', 'like', '%'.$request->search.'%')
                ->orWhereHas('jobTitle', function($q) use($request){
                    $q->where('title', 'like', '%'.$request->search.'%');
                })
                ->orWhereHas('education', function($q) use($request){
                    $q->where('name', 'like', '%'.$request->search.'%');
                })
                ->orWhereHas('recruitmentSkills', function($q) use($request){
                    $q->where('name', 'like', '%'.$request->search.'%');
                });
            });
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);

        return $query;
    }

    private function sorting($request, $query){
        $query->when($request->column && $request->sort, function ($query) use ($request) {
            if (in_array($request->column, ['job_id','request_id','job_type','created_at','status','expected_doj'])) {
                $query->orderBy($request->column, $request->sort);
            }

            // if ($request->column == 'job_title') {
            //     $query->whereHas('jobTitle', function ($q) use ($request) {
            //         $q->orderBy('title', $request->sort);
            //     });
            // }

            // if ($request->column == 'education') {
            //     $query->whereHas('education', function ($q) use ($request) {
            //         $q->orderBy('name', $request->sort);
            //     });
            // }

            // if ($request->column == 'skill') {
            //     $query->whereHas('recruitmentSkills', function ($q) use ($request) {
            //         $q->orderBy('name', $request->sort);
            //     });
            // }

            // dd($query->toSql(), $query->getBindings());
        }, function ($query) {
            // Default sort
            $query->orderBy('created_at', 'desc');
        });
        
        return $query;
    }

    public function jobInterviewList(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $masterData = self::masterData();
        $summaryData = self::getJobSummary($request, $user);
        
        $jobIds = ErpRecruitmentJobPanelAllocation::where('panel_id',$user->id)->pluck('job_id')->toArray();
        $roundIds = ErpRecruitmentJobPanelAllocation::where('panel_id', $user->id)->pluck('round_id')->toArray();
        $jobInterviews = ErpRecruitmentJobInterview::with([
                    'job' =>function($q){
                        $q->select('id','job_id','job_title_id','status');
                    },
                    'candidate' =>function($q){
                        $q->select('id','name');
                    },'interviewFeedback'  => function($q) use($user){
                        $q->where('panel_id',$user->id);
                    },
                ])
                ->where(function($query) use($request){
                    self::interviewFilter($request, $query);
                })
                ->whereIn('job_id',$jobIds)
                ->whereIn('round_id', $roundIds) 
                ->orderBy('date_time','desc')
                ->paginate($length);
        
        return view('recruitment.request.interview-scheduled-list',[
            'jobInterviews' => $jobInterviews,
            'user' => $user,
            'jobTitles' => $masterData['jobTitles'],
            'status' => CommonHelper::INTERVIEW_STATUS,
            'requestCount' => $summaryData['requestCount'],
            'requestForApprovalCount' => $summaryData['requestForApprovalCount'],
            'rejectedRequestCount' => $summaryData['rejectedRequestCount'],
            'openRequestCount' => $summaryData['openRequestCount'],
            'interviewScheduledCount' => $summaryData['interviewScheduledCount'],
            'candidateAssignedRequestCount' => $summaryData['candidateAssignedRequestCount'],
        ]);
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

    private function jobFilter($request, $query){
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


    public function assignedCandidateList(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $masterData = self::masterData();
        $summaryData = self::getJobSummary($request, $user);
        
        $jobIds = ErpRecruitmentJobPanelAllocation::where('panel_id',$user->id)->pluck('job_id');

        $jobs = ErpRecruitmentJob::with('jobSkills')
                ->withCount(['assignedCandidates'])
                ->where(function($query) use($request){
                    self::filter($request, $query);
                })
                ->whereIn('id',$jobIds)
                ->whereHas('assignedCandidates', function($q){
                    $q->whereIn('status',CommonHelper::CANDIDATE_STATUS);
                })
                ->orderBy('created_at','desc')->paginate($length);
            
        return view('recruitment.request.assigned-candidate-list',[
            'jobs' => $jobs,
            'user' => $user,
            'jobTitles' => $masterData['jobTitles'],
            'status' => CommonHelper::JOB_STATUS,
            'requestCount' => $summaryData['requestCount'],
            'requestForApprovalCount' => $summaryData['requestForApprovalCount'],
            'rejectedRequestCount' => $summaryData['rejectedRequestCount'],
            'openRequestCount' => $summaryData['openRequestCount'],
            'interviewScheduledCount' => $summaryData['interviewScheduledCount'],
            'candidateAssignedRequestCount' => $summaryData['candidateAssignedRequestCount'],
        ]);
    }

    private function getJobSummary($request, $user){

        $requestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->count();

        $rejectedRequestCount = ErpRecruitmentJobRequests::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->when(\Request::route()->getName() === "recruitment.requests.for-approval", function($q) use ($user) {
                $q->where('approval_authority', $user->id);
            }, function($q) use ($user) {
                $q->where('created_by', $user->id)
                ->where('created_by_type', $user->authenticable_type);
            })
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

        $jobIds = ErpRecruitmentJobPanelAllocation::where('panel_id',$user->id)->pluck('job_id');
        $roundIds = ErpRecruitmentJobPanelAllocation::where('panel_id',$user->id)->pluck('round_id');

        $interviewScheduledCount = ErpRecruitmentJobInterview::where(function($query) use($request){
                                                self::interviewFilter($request, $query);
                                            })
                                    ->whereIn('job_id',$jobIds)
                                    ->whereIn('round_id', $roundIds) 
                                    ->where('status',CommonHelper::SCHEDULED)
                                    ->count();

        $candidateAssignedRequestCount = ErpRecruitmentJob::with('jobSkills')
                                        ->where(function($query) use($request){
                                            self::jobFilter($request, $query);
                                        })
                                        ->whereIn('id',$jobIds)
                                        ->whereHas('assignedCandidates', function($q){
                                            $q->whereIn('status',CommonHelper::CANDIDATE_STATUS);
                                        })
                                        ->count();
        
        return [
            'requestCount' => $requestCount,
            'rejectedRequestCount' => $rejectedRequestCount,
            'requestForApprovalCount' => $requestForApprovalCount,
            'openRequestCount' => $openRequestCount,
            'candidateAssignedRequestCount' => $candidateAssignedRequestCount,
            'interviewScheduledCount' => $interviewScheduledCount,
        ];
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
            'companies' => $masterData['companies'],
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
            $jobRequest->company_id = $request->company_id;
            $jobRequest->job_title_id = $request->job_title_id;
            $jobRequest->no_of_position = $request->no_of_position;
            $jobRequest->education_id = $request->education_id;
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

            if (!empty($request->skill) && is_array($request->skill)) {
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
            }

            if (!empty($request->certification_id) && is_array($request->certification_id)) {
                foreach($request->certification_id as $certification){
                    $certification = ErpRecruitmentCertification::firstOrCreate(
                        [
                            'name' => $certification, 
                            'organization_id' => $user->organization_id],
                        [
                            'name' => $certification, 
                            'organization_id' => $user->organization_id, 
                            'status' => 'active',
                            'created_by_type' => $user->authenticable_type,
                            'created_by' => $user->id 
                        ]
                    );

                    $jobRequestCertification = new ErpRecruitmentJobRequestCertification();
                    $jobRequestCertification->job_request_id = $jobRequest->id;
                    $jobRequestCertification->certification_id = $certification ? $certification->id : null;
                    $jobRequestCertification->created_at = date('Y-m-d h:i:s');
                    $jobRequestCertification->save();

                }
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

    public function edit($id){
        $masterData = self::masterData();

        $jobRequest = ErpRecruitmentJobRequests::find($id);
        $requestSkills = ErpRecruitmentJobRequestSkill::where('job_request_id',$id)->pluck('skill_id')->toArray();
        $requestCertifications = ErpRecruitmentJobRequestCertification::where('job_request_id',$id)->pluck('certification_id')->toArray();

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
            'requestCertifications' => $requestCertifications,
            'companies' => $masterData['companies'],
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
            $jobRequest->status = CommonHelper::PENDING; 
            $jobRequest->company_id = $request->company_id;
            $jobRequest->location_id = $request->location_id; 
            $jobRequest->emp_id = $request->emp_id ?? NULL; 
            $jobRequest->save();

            if (!empty($request->skill) && is_array($request->skill)) {
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
            }

            if (!empty($request->certification_id) && is_array($request->certification_id)) {
                foreach($request->certification_id as $certification){
                    $certification = ErpRecruitmentCertification::firstOrCreate(
                        [
                            'name' => $certification, 
                            'organization_id' => $user->organization_id],
                        [
                            'name' => $certification, 
                            'organization_id' => $user->organization_id, 
                            'status' => 'active',
                            'created_by_type' => $user->authenticable_type,
                            'created_by' => $user->id 
                        ]
                    );

                    ErpRecruitmentJobRequestCertification::updateOrCreate([
                        'job_request_id' => $jobRequest->id,
                        'certification_id' => $certification->id
                    ]);
                }
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

                if($status == CommonHelper::SEND_BACK){
                    $managerId = $jobRequest->approval_authority;
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

            $status = ucwords(str_replace('-', ' ', $status));

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
        $requestCertifications = $jobRequest->recruitmentCertifications->pluck('name')->toArray();
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

        return view('recruitment.request.show',[
            'jobRequest' => $jobRequest,
            'requestSkills' => $requestSkills,
            'requestCertifications' => $requestCertifications,
            'jobRequestLogs' => $jobRequestLogs,
            'user' => $user,
            'job' => $job,
            'jobSkills' => $jobSkills,
        ]);
    }

    public function jobView($id){
        $user = Helper::getAuthenticatedUser();
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
                ])->find($id);
        
        $jobSkills = [];
        $rounds = [];
        if ($job) {
            $jobSkills = $job->jobSkills->pluck('name')->toArray();
            $rounds = ErpRecruitmentRound::whereHas('allocateRounds',function($q) use($job){
                $q->where('job_id',$job->id);
            })
            ->select('id','name')
            ->orderby('id','ASC')
            ->get();
        }

        $jobLogs = ErpRecruitmentJobLog::where('job_id',$id)->orderby('id','DESC')->get();
        return view('recruitment.request.job-view',[
            'job' => $job,
            'jobSkills' => $jobSkills,
            'user' => $user,
            'jobLogs' => $jobLogs,
            'rounds' => $rounds,
        ]);
    }
}
