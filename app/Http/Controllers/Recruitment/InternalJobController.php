<?php

namespace App\Http\Controllers\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Lib\Validation\Recruitment\JobReferral as Validator;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobReferral;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentSkill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InternalJobController extends Controller
{
    public function index(Request $request){
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $jobs = ErpRecruitmentJob::where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('publish_for',CommonHelper::INTERNAL)->paginate($length);

        $masterData = self::masterData();
        return view('recruitment.internal-jobs.index',[
            'jobs' => $jobs,
            'jobTitles' => $masterData['jobTitles'],
            'status' => CommonHelper::JOB_STATUS,
            'skills' => $masterData['skills']
        ]);
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

    public function apply($jobId){
        $user = Helper::getAuthenticatedUser();
        return view('recruitment.internal-jobs.apply-for-job',[
            'jobId' => $jobId,
            'user' => $user
        ]);
    }

    public function storeReferrals(Request $request, $jobId){
        $request->merge(['job_id' => $jobId]);
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            
            $user = Helper::getAuthenticatedUser();

            $jobCandidate = ErpRecruitmentJobCandidate::where('mobile_no',$request->mobile_no)->first();
            if(!$jobCandidate){
                $jobCandidate = new ErpRecruitmentJobCandidate();
            }

            $jobCandidate->organization_id = $user->organization_id;
            $jobCandidate->name = $request->name;
            $jobCandidate->email = $request->email;
            $jobCandidate->mobile_no = $request->mobile_no;
            $jobCandidate->status = CommonHelper::ACTIVE;
            $jobCandidate->created_by = $user->id; 
            $jobCandidate->created_by_type = $user->authenticable_type; 
            $jobCandidate->save();


            if($request->hasFile('resume')){
                $attachment = $request->resume;
                $documentName = time() . ''.$jobCandidate->id.'-' . $attachment->getClientOriginalName();
                $attachment->move(public_path('attachments/candidate_attchments'), $documentName);
                $documentPath = 'attachments/candidate_attchments/'.$documentName;

                $jobCandidate->resume_path = $documentPath;
                $jobCandidate->save();
            }

            $referral = ErpRecruitmentJobReferral::where('job_id',$jobId)->where('candidate_id',$jobCandidate->id)->first();
            if(!$referral){
                $referral = new ErpRecruitmentJobReferral();
            }

            $referral->job_id = $jobId;
            $referral->candidate_id = $jobCandidate->id;
            $referral->applied_for = $request->applied_for;
            $referral->remarks = $request->remarks;
            $referral->created_by = $user->id; 
            $referral->created_by_type = $user->authenticable_type; 
            $referral->save();
            
        \DB::commit();
            return [
                "data" => null,
                "message" => "Job referral created successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    
}