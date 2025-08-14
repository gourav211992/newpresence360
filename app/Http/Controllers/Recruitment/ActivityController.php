<?php

namespace App\Http\Controllers\Recruitment;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ErpStore;
use App\Models\Recruitment\ErpRecruitmentCertification;
use App\Models\Recruitment\ErpRecruitmentEducation;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use App\Models\Recruitment\ErpRecruitmentJobReferral;
use App\Models\Recruitment\ErpRecruitmentJobRequestLog;
use App\Models\Recruitment\ErpRecruitmentJobRequests;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentSkill;
use App\Models\Recruitment\ErpRecruitmentWorkExperience;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $query = ErpRecruitmentJobRequestLog::with('request')
                ->where('action_by',$user->id)
                ->where('action_by_type',$user->authenticable_type);

        self::filter($request, $query);    

        $requestLog = $query->orderBy('created_at','desc')->paginate($length);

        $masterData = self::masterData();
        $summaryData = CommonHelper::getSummaryData($request, $user);
        return view('recruitment.activity.index',[
            'requestLog' => $requestLog,
            'jobTitles' => $masterData['jobTitles'],
            'skills' => $masterData['skills'],
            'status' => CommonHelper::JOB_REQUEST_STATUS,
            'requestCount' => $summaryData['requestCount'],
            'referralCount' => $summaryData['referralCount'],
        ]);
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
            $query->whereHas('request', function ($q) use($request) {
                $q->where('job_title_id', $request->job_title);
            });
        }

        if ($request->skill) {
            $query->whereHas('request', function ($q) use($request) {
                $q->whereHas('recruitmentSkills', function ($query) use($request) {
                    $query->where('skill_id', $request->skill);
                });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use($request){
                $q->where('status', 'like', '%'.$request->search.'%')
                ->orWhereHas('request', function($q) use($request){
                    $q->where('job_id', 'like', '%'.$request->search.'%')
                    ->orWhere('request_id', 'like', '%'.$request->search.'%')
                    ->orWhere('job_type', 'like', '%'.$request->search.'%')
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
            });
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);

        return $query;
    }
}