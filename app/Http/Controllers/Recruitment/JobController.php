<?php

namespace App\Http\Controllers\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ErpStore;
use App\Models\Recruitment\ErpRecruitmentEducation;
use App\Models\Recruitment\ErpRecruitmentIndustry;
use App\Models\Recruitment\ErpRecruitmentJobRequests;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentNoticePeriod;
use App\Models\Recruitment\ErpRecruitmentSkill;
use App\Models\Recruitment\ErpRecruitmentWorkingHour;
use Illuminate\Http\Request;
use App\Lib\Validation\Recruitment\Job as Validator;
use App\Models\Recruitment\ErpRecruitmentAssignedCandidate;
use App\Models\Recruitment\ErpRecruitmentInterviewFeedback;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobLog;
use App\Models\Recruitment\ErpRecruitmentJobNotification;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use App\Models\Recruitment\ErpRecruitmentJobSkill;
use App\Models\Recruitment\ErpRecruitmentRound;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class JobController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $masterData = self::masterData();
        $summaryData = self::getJobSummary($request, $user);

        $jobs = ErpRecruitmentJob::with('jobSkills')
                ->withCount([
                    'panelAllocations as totalJobRounds' => function ($q) {
                        $q->select(\DB::raw('COUNT(DISTINCT round_id)'));
                    },'assignedCandidates'
                ])
            ->where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->whereDoesntHave('assignedCandidates')
            ->orderBy('created_at','desc')->paginate($length);
        
        return view('recruitment.job.index',[
            'jobs' => $jobs,
            'jobTitles' => $masterData['jobTitles'],
            'status' => CommonHelper::JOB_STATUS,
            'skills' => $masterData['skills'],
            'jobCount' => $summaryData['jobCount'],
            'interviewCount' => $summaryData['interviewCount'],
            'user' => $user,
            'candidatesCount' => $summaryData['candidatesCount'],
            'selectedCandidatesCount' => $summaryData['selectedCandidatesCount'],
            'closedJobCount' => $summaryData['closedJobCount'],
        ]);
    }

    public function getJobInterviewList(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $masterData = self::masterData();
        $summaryData = self::getJobSummary($request, $user);
        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }
    
        $jobInterviews = ErpRecruitmentJobInterview::with([
                    'job' =>function($q) use($user){
                        $q->select('id','job_id','job_title_id','status');
                    },
                    'candidate' =>function($q) use($user){
                        $q->select('id','name');
                    }
                ])->where(function($query) use($request, $startDate, $endDate){
                    if ($request->job_title) {
                        $query->whereHas('job', function($q) use($request){
                            $q->where('job_title_id',$request->job_title);
                        }); 
                    }
    
                    if ($request->status) {
                        $query->where('status', $request->status);
                    }
    
                    if ($request->search) {
                        $query->where(function($q) use($request){
                            $q->whereHas('candidate', function($q) use($request){
                                $q->where('name','like', '%'.$request->search.'%');
                            })
                            ->orWhereHas('job', function($q) use($request){
                                $q->where('job_id','like', '%'.$request->search.'%')
                                ->orWhere('status', 'like', '%'.$request->search.'%');
                            })                            
                            ->orWhere('status', 'like', '%'.$request->search.'%');
                        });
                    }
                    $query->whereBetween('date_time', [$startDate, $endDate]);
                })
                ->whereHas('job',function($q) use($user){
                    $q->where('created_by',$user->id)
                    ->where('created_by_type',$user->authenticable_type);
                })
                ->orderBy('date_time','desc')
                ->paginate($length);
        
        return view('recruitment.job.interview-scheduled',[
            'jobInterviews' => $jobInterviews,
            'jobTitles' => $masterData['jobTitles'],
            'status' => CommonHelper::INTERVIEW_STATUS,
            'jobCount' => $summaryData['jobCount'],
            'interviewCount' => $summaryData['interviewCount'],
            'user' => $user,
            'candidatesCount' => $summaryData['candidatesCount'],
            'selectedCandidatesCount' => $summaryData['selectedCandidatesCount'],
            'closedJobCount' => $summaryData['closedJobCount'],
        ]);
    }


    public function getAssignedCandidateList(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $masterData = self::masterData();
        $summaryData = self::getJobSummary($request, $user);

        $jobs = ErpRecruitmentJob::with('jobSkills')
                ->withCount([
                    'panelAllocations as totalJobRounds' => function ($q) {
                        $q->select(\DB::raw('COUNT(DISTINCT round_id)'));
                    },'assignedCandidates'
                ])
            ->where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->whereHas('assignedCandidates', function($q){
                $q->where('status',CommonHelper::ASSIGNED);
            })
            ->orderBy('created_at','desc')->paginate($length);
        
        return view('recruitment.job.assgined-candidate',[
            'jobs' => $jobs,
            'jobTitles' => $masterData['jobTitles'],
            'status' => CommonHelper::JOB_STATUS,
            'skills' => $masterData['skills'],
            'jobCount' => $summaryData['jobCount'],
            'user' => $user,
            'candidatesCount' => $summaryData['candidatesCount'],
            'interviewCount' => $summaryData['interviewCount'],
            'selectedCandidatesCount' => $summaryData['selectedCandidatesCount'],
            'closedJobCount' => $summaryData['closedJobCount'],
        ]);
    }

    private function getJobSummary($request, $user){
        $jobCount = ErpRecruitmentJob::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->whereDoesntHave('assignedCandidates')
            ->count();

        $closedJobCount = ErpRecruitmentJob::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->where('status',CommonHelper::CLOSED)
            ->count();

        $candidatesCount = ErpRecruitmentJob::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->whereHas('assignedCandidates', function($q){
                $q->where('status',CommonHelper::ASSIGNED);
            })
            ->count();

        $interviewCount = ErpRecruitmentJob::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->whereHas('jobInterview', function($q){
                    $q->where('status',CommonHelper::SCHEDULED);
                })
            ->count();

        $selectedCandidatesCount = ErpRecruitmentJob::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->where('created_by_type',$user->authenticable_type)
            ->whereHas('assignedCandidates', function($q){
                $q->where('status',CommonHelper::SELECTED);
            })
            ->count();
        
        return [
            'jobCount' => $jobCount,
            'candidatesCount' => $candidatesCount,
            'interviewCount' => $interviewCount,
            'selectedCandidatesCount' => $selectedCandidatesCount,
            'closedJobCount' => $closedJobCount,
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
            $query->whereHas('jobSkills', function ($q) use($request) {
                $q->where('skill_id', $request->skill);
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use($request){
                $q->where('job_id', 'like', '%'.$request->search.'%')
                ->orWhere('status', 'like', '%'.$request->search.'%');
            });
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);

        return $query;
    }

    public function create(){
        $masterData = self::masterData();
        return view('recruitment.job.create',[
            'jobTitles' => $masterData['jobTitles'],
            'skills' => $masterData['skills'],
            'industries' => $masterData['industries'],
            'locations' => $masterData['locations'],
            'educations' => $masterData['educations'],
            'workingHours' => $masterData['workingHours'],
            'noticePeriods' => $masterData['noticePeriods'],
            'rounds' => $masterData['rounds'],
        ]);
    }

    private function masterData(){
        $user = Helper::getAuthenticatedUser();
        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $industries = ErpRecruitmentIndustry::where('status','active')
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

        $educations = ErpRecruitmentEducation::where('status','active')
        ->where('organization_id',$user->organization_id)
        ->get();

        $workingHours = ErpRecruitmentWorkingHour::where('status','active')
        ->where('organization_id',$user->organization_id)
        ->get();

        $noticePeriods = ErpRecruitmentNoticePeriod::where('status','active')
        ->where('organization_id',$user->organization_id)
        ->get();

        $rounds = ErpRecruitmentRound::where('status','active')
        ->where('organization_id',$user->organization_id)
        ->get();

        return [
            'jobTitles' => $jobTitles,
            'industries' => $industries,
            'skills' => $skills,
            'locations' => $locations,
            'educations' => $educations,
            'workingHours' => $workingHours,
            'noticePeriods' => $noticePeriods,
            'rounds' => $rounds,
        ];

    }

    public function getJobRequestsByTitle(Request $request){
        $jobTitleId = $request->job_title_id;
        $query = ErpRecruitmentJobRequests::with('recruitmentSkills');
                        
        if($request->job_id){
            $query->where('job_id',$request->job_id);
        }else{
            $query->where('job_id',NULL);
        }

        $jobRequests = $query->where('job_title_id', $jobTitleId)
                        ->where('status',CommonHelper::FINAL_APPROVED)
                        ->get();

        return view('recruitment.job.job-requests-table-rows', [
            'jobRequests' => $jobRequests,
            'jobId' => $request->job_id,
        ])->render();
    }

    public function store(Request $request){
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();

            $job = new ErpRecruitmentJob();
            $job->job_title_id = $request->job_title_id;
            $job->organization_id = $user->organization_id;
            $job->status = $request->status;
            $job->third_party_assessment = $request->third_party_assessment;
            $job->assessment_url = $request->third_party_assessment == 'yes' ? $request->assessment_url : NULL;
            $job->last_apply_date = $request->last_apply_date ? $request->last_apply_date : NULL;
            $job->publish_for = $request->publish_for;
            $job->no_of_position = $request->no_of_position;
            $job->employement_type = $request->employement_type;
            $job->industry_id = $request->industry_id;
            $job->work_mode = $request->work_mode;
            $job->location_id = $request->location_id;
            $job->company_detail = $request->company_detail;
            $job->education_id = $request->education_id;
            $job->work_exp_min = $request->work_exp_min;
            $job->work_exp_max = $request->work_exp_max;
            $job->working_hour_id = $request->working_hour_id;
            $job->annual_salary_min = $request->annual_salary_min;
            $job->hide_from_candidate = $request->hide_from_candidate;
            $job->annual_salary_max = $request->annual_salary_max;
            $job->notice_peroid_id = $request->notice_peroid_id;
            $job->job_description = $request->description;
            $job->created_by = $user->id; 
            $job->created_by_type = $user->authenticable_type; 
            $job->save();
            
            // ✅ Handle skills
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

                $jobSkill = new ErpRecruitmentJobSkill();
                $jobSkill->job_id = $job->id;
                $jobSkill->skill_id = $skill ? $skill->id : null;
                $jobSkill->created_at = now();
                $jobSkill->save();

            }

            // ✅ Attach job to job requests
            ErpRecruitmentJobRequests::whereIn('id',$request->job_request)->update([
                'job_id' => $job->job_id
            ]);


            // ✅ Notification emails
            foreach($request->notification_email as $email){
                ErpRecruitmentJobNotification::updateOrCreate(
                    [
                        'job_id' => $job->id,
                        'email' => $email,
                    ],
                    [
                        'status' => CommonHelper::PENDING,
                    ]
                );
            }

            // ✅ Panel allocation
            foreach ($request->data as $data) {
                $panelIds = is_array($data['panel_ids']) ? $data['panel_ids'] : [$data['panel_ids']];
                
                foreach ($panelIds as $panelId) {
                    ErpRecruitmentJobPanelAllocation::firstOrCreate([
                        'job_id' => $job->id,
                        'round_id' => $data['round'],
                        'panel_id' => $panelId,
                    ]);
                }

                if (!empty($data['external_email'])) {
                    ErpRecruitmentJobPanelAllocation::firstOrCreate([
                        'job_id' => $job->id,
                        'round_id' => $data['round'],
                        'external_email' => $data['external_email'],
                    ]);
                }
            }

            // ✅ Log the job creation
            $jobLog = new ErpRecruitmentJobLog();
            $jobLog->organization_id = $user->organization_id;
            $jobLog->job_id = $job->id;
            $jobLog->status = $job->status;
            $jobLog->log_type = CommonHelper::JOB; 
            $jobLog->log_message = 'Job created'; 
            $jobLog->action_by = $user->id;
            $jobLog->action_by_type = $user->authenticable_type;
            $jobLog->save();
            
            \DB::commit();
            return [
                "data" => null,
                "message" => "Job created successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function edit($id){
        $masterData = self::masterData();

        $job = ErpRecruitmentJob::find($id);
        $jobSkills = ErpRecruitmentJobSkill::where('job_id',$id)->pluck('skill_id')->toArray();
        
        $panelAllocations = ErpRecruitmentJobPanelAllocation::with(['panel' => function($q){
            $q->select('id','name');
        }, 'round' => function($q){
            $q->select('id','name');
        }])->where('job_id', $id)->get()->groupBy('round_id');

        return view('recruitment.job.edit',[
            'jobTitles' => $masterData['jobTitles'],
            'skills' => $masterData['skills'],
            'industries' => $masterData['industries'],
            'locations' => $masterData['locations'],
            'educations' => $masterData['educations'],
            'workingHours' => $masterData['workingHours'],
            'noticePeriods' => $masterData['noticePeriods'],
            'rounds' => $masterData['rounds'],
            'jobSkills' => $jobSkills,
            'job' => $job,
            'panelAllocations' => $panelAllocations,
        ]);
    }

    public function update(Request $request,$id){
        $request->merge(['job_id' => $id]);
        $validator = (new Validator($request))->update();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            
            $job = ErpRecruitmentJob::find($id);
            // $job->job_title_id = $request->job_title_id;
            $job->organization_id = $user->organization_id;
            $job->status = $request->status;
            $job->third_party_assessment = $request->third_party_assessment;
            $job->assessment_url = $request->third_party_assessment == 'yes' ? $request->assessment_url : NULL;
            $job->last_apply_date = $request->last_apply_date ? $request->last_apply_date : NULL;
            $job->publish_for = $request->publish_for;
            $job->employement_type = $request->employement_type;
            $job->industry_id = $request->industry_id;
            $job->work_mode = $request->work_mode;
            $job->location_id = $request->location_id;
            $job->company_detail = $request->company_detail;
            $job->education_id = $request->education_id;
            $job->work_exp_min = $request->work_exp_min;
            $job->work_exp_max = $request->work_exp_max;
            $job->working_hour_id = $request->working_hour_id;
            $job->annual_salary_min = $request->annual_salary_min;
            $job->hide_from_candidate = $request->hide_from_candidate;
            $job->annual_salary_max = $request->annual_salary_max;
            $job->notice_peroid_id = $request->notice_peroid_id;
            $job->job_description = $request->description;
            $job->no_of_position = $request->no_of_position;
            $job->created_by = $user->id; 
            $job->created_by_type = $user->authenticable_type; 
            $job->save();
            
            // ✅ Handle skills
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

                ErpRecruitmentJobSkill::updateOrCreate([
                    'job_id' => $job->id,
                    'skill_id' => $skill->id
                ]);
            }

            // ✅ Panel allocation
            foreach ($request->data as $data) {
                if (isset($data['panel_ids']) && !empty($data['panel_ids'])) {
                    $panelIds = is_array($data['panel_ids']) ? $data['panel_ids'] : [$data['panel_ids']];
                    foreach ($panelIds as $panelId) {
                        ErpRecruitmentJobPanelAllocation::firstOrCreate([
                            'job_id' => $job->id,
                            'round_id' => $data['round'],
                            'panel_id' => $panelId,
                        ]);
                    }
                }

                if (!empty($data['external_email'])) {
                    ErpRecruitmentJobPanelAllocation::firstOrCreate([
                        'job_id' => $job->id,
                        'round_id' => $data['round'],
                        'external_email' => $data['external_email'],
                    ]);
                }
            }
            
            \DB::commit();
            return [
                "data" => null,
                "message" => "Job updated successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }


    public function show($id){
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

        $jobLogs = ErpRecruitmentJobLog::where('job_id',$id)
                    // ->where('log_type',CommonHelper::CANDIDATE)
                    ->orderby('id','DESC')
                    ->get();
        return view('recruitment.job.show',[
            'job' => $job,
            'jobSkills' => $jobSkills,
            'user' => $user,
            'jobLogs' => $jobLogs,
            'rounds' => $rounds,
        ]);
    }

    public function removePanel($id, $roundId){
        ErpRecruitmentJobPanelAllocation::where('job_id', $id)->where('round_id', $roundId)->delete();
        return [
            "data" => null,
            "message" => "Panel removed successfully!"
        ];
    }

    public function candidates(Request $request, $jobId){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $job = ErpRecruitmentJob::find($jobId);
        $skillIds = $job->jobSkills()->pluck('skill_id')->toArray();

        $query = ErpRecruitmentJobCandidate::with('candidateSkills')
            ->where(function($query) use($request){
                if ($request->date_range) {
                    $dateRange = explode(' to ', $request->date_range);
                    $startDate = Carbon::parse($dateRange[0])->startOfDay();
                    $endDate = isset($dateRange[1])
                        ? Carbon::parse($dateRange[1])->endOfDay()
                        : Carbon::parse($dateRange[0])->endOfDay(); // single day

                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
                if ($request->skill) {
                    $query->whereHas('candidateSkills', function ($q) use($request) {
                        $q->where('skill_id', $request->skill);
                    });
                }

                if ($request->location_id) {
                    $query->where('location_id', $request->location_id);
                }

                if ($request->search) {
                    $query->where(function($q) use($request){
                        $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('mobile_no', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%')
                        ->orWhere('current_organization', 'like', '%'.$request->search.'%');
                    });
                }
            })
            ->where('organization_id',$user->organization_id)
            ->whereHas('candidateSkills', function ($q) use($skillIds) {
                    $q->whereIn('skill_id', $skillIds);
                });

        $candidates = $query->paginate($length);
        $assignedCandidateIds = ErpRecruitmentAssignedCandidate::where('job_id', $job->id)
                                ->pluck('candidate_id')
                                ->toArray();

        $masterData = self::masterData();
        return view('recruitment.job.candidates',[
            'candidates' => $candidates,
            'skills' => $masterData['skills'],
            'locations' => $masterData['locations'],
            'job' => $job,
            'assignedCandidateIds' => $assignedCandidateIds,
        ]);
    }

    public function assignCandidate(Request $request, $id){
        $validator = (new Validator($request))->assgnCandidates();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $candidateIds = $request->input('candidate_ids', []);
            $user = Helper::getAuthenticatedUser();
            
            ErpRecruitmentAssignedCandidate::where('job_id',$id)->delete();
            foreach($candidateIds as $candidateId){
                $candidate = new ErpRecruitmentAssignedCandidate();
                $candidate->candidate_id = $candidateId;
                $candidate->job_id = $id;
                $candidate->status = CommonHelper::ASSIGNED;
                $candidate->save();

                $jobActivityLog = new ErpRecruitmentJobLog();
                $jobActivityLog->organization_id = $user->organization_id;
                $jobActivityLog->job_id = $id;
                $jobActivityLog->candidate_id = $candidateId;
                $jobActivityLog->log_type = CommonHelper::CANDIDATE;
                $jobActivityLog->log_message = 'Candidate Assigned';
                $jobActivityLog->status = CommonHelper::ASSIGNED;
                $jobActivityLog->action_by = $user->id;
                $jobActivityLog->action_by_type = $user->authenticable_type;
                $jobActivityLog->save();
            }
            
            \DB::commit();
            return [
                "data" => null,
                "message" => "Candidate assigned successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function candidateDetail(Request $request, $id, $jobId){
        $candidate = ErpRecruitmentJobCandidate::with([
            'assignedJob' => function($q) use($jobId){
                $q->where('job_id',$jobId)
                ->select('job_id','candidate_id','created_at','status');
            },'jobDetail'  => function($q) use($jobId){
                $q->select('erp_recruitment_job.id','erp_recruitment_job.job_title_id');
            }
        ])->find($id);
        
        return view('recruitment.partials.candidate-view', [
            'candidate' => $candidate,
            'page' => $request->page,
        ])->render();
    }

    public function candidateInterviewDetail(Request $request, $id, $jobId){
        $candidate = ErpRecruitmentJobCandidate::with([
            'assignedJob' => function($q) use($jobId, $request){
                $q->where('job_id',$jobId)
                    ->where('status',$request->status)
                    ->select('job_id','candidate_id','created_at','status');
            },'jobDetail'  => function($q) use($jobId){
                $q->select('erp_recruitment_job.id','erp_recruitment_job.job_title_id');
            },
        ])->find($id);
        
        $interviewDetail = ErpRecruitmentJobInterview::where('job_id', $jobId)
                            ->where('candidate_id', $id)
                            ->where('status',$request->status)
                            ->first();
        
        $interviewRounds = ErpRecruitmentRound::with(['jobInterview' => function($q){
                $q->select('id','round_id','status');
            }])
            ->whereHas('allocateRounds',function($q) use($jobId){
                $q->where('job_id',$jobId);
            })
            ->select('id','name')
            ->orderby('id','ASC')
            ->get();

        $feedbackLog = ErpRecruitmentInterviewFeedback::where('job_id',$jobId)
                    ->where('candidate_id',$id)
                    ->where('round_id',$interviewDetail->round_id)
                    ->select('id','round_id','rating','behavior','skills','panel_id','remarks','created_at','attachment_path')
                    ->orderby('id','DESC')
                    ->get();
        
        $pendingRoundsCount = ErpRecruitmentRound::whereHas('allocateRounds', function($q) use($jobId) {
                                $q->where('job_id', $jobId);
                            })
                            ->whereNotIn('id', function($q) use ($jobId, $id) {
                                // Exclude rounds that already have feedback or are completed
                                $q->select('round_id')
                                ->from('erp_recruitment_job_interviews')
                                ->where('job_id', $jobId)
                                ->where('candidate_id', $id);
                            })
                            ->count();

        $user = Helper::getAuthenticatedUser();
       
        $isPanelist = ErpRecruitmentJobPanelAllocation::where('job_id', $jobId)->where('panel_id', $user->id)->exists();
        $hasGivenFeedback = false;
        if ($isPanelist) {
            $hasGivenFeedback = ErpRecruitmentInterviewFeedback::where('panel_id', $user->id)
                ->where('interview_id', $interviewDetail->id)
                ->exists();
        }

        return view('recruitment.partials.candidate-interview-detail', [
            'candidate' => $candidate,
            'interviewDetail' => $interviewDetail,
            'page' => $request->page,
            'interviewRounds' => $interviewRounds,
            'feedbackLog' => $feedbackLog,
            'status' => $request->status,
            'pendingRoundsCount' => $pendingRoundsCount,
            'hasGivenFeedback' => $hasGivenFeedback,
        ])->render();
    }

    public function fetchCandidates(Request $request,$jobId,$status){
        $candidates = ErpRecruitmentJobCandidate::with(['scheduledInterview' => function($q) use($jobId,$status){
            $q->where('job_id',$jobId)
                ->where('status',$status);
        },'assignedJob'])
        ->whereHas('assignedJob',function($q) use($jobId,$status){
            $q->where('job_id',$jobId)
            ->where('status',$status);
        })->get();
        
        return view('recruitment.partials.candidates-list', [
            'candidates' => $candidates,
            'jobId' => $jobId,
            'status' => $status,
            'page' => $request->page,
        ])->render();

    }
    
    public function updateCandidateStatus(Request $request){
        $validator = (new Validator($request))->updateCandidateStatus();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();
            ErpRecruitmentAssignedCandidate::where('job_id',$request->job_id)
                ->where('candidate_id',$request->candidate_id)
                ->update([
                    'status' => $request->status,
                    'remark' => $request->log_message
                ]);

            $jobRequestLog = new ErpRecruitmentJobLog();
            $jobRequestLog->organization_id = $user->organization_id;
            $jobRequestLog->job_id = $request->job_id;
            $jobRequestLog->candidate_id = $request->candidate_id;
            $jobRequestLog->log_type = CommonHelper::CANDIDATE; 
            $jobRequestLog->log_message = $request->log_message; 
            $jobRequestLog->status = $request->status;
            $jobRequestLog->action_by = $user->id;
            $jobRequestLog->action_by_type = $user->authenticable_type;
            $jobRequestLog->save();

            \DB::commit();
            return [
                'message' => "Candidate is $request->status",
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function updateStatus(Request $request,$id){
        $validator = (new Validator($request))->updateStatus();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();
            ErpRecruitmentJob::where('id',$id)
                ->update([
                    'status' => $request->status,
                ]);

            $jobRequestLog = new ErpRecruitmentJobLog();
            $jobRequestLog->organization_id = $user->organization_id;
            $jobRequestLog->job_id = $id;
            $jobRequestLog->log_type = CommonHelper::JOB; 
            $jobRequestLog->log_message = $request->log_message; 
            $jobRequestLog->status = $request->status;
            $jobRequestLog->action_by = $user->id;
            $jobRequestLog->action_by_type = $user->authenticable_type;
            $jobRequestLog->save();

            \DB::commit();
            return [
                'message' => "Job is $request->status",
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }
}
