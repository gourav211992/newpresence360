<?php

namespace App\Http\Controllers\Recruitment;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobLog;
use App\Models\Recruitment\ErpRecruitmentJobReferral;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReferalController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $referrals = ErpRecruitmentJobReferral::with('job','candidate')
            ->where(function($query) use($request){
                self::filter($request, $query);
            })
            ->where('created_by',$user->id)
            ->paginate($length);

        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $summaryData = CommonHelper::getSummaryData($request, $user);
        return view('recruitment.referal.index',[
            'referrals' => $referrals,
            'jobTitles' => $jobTitles,
            'requestCount' => $summaryData['requestCount'],
            'referralCount' => $summaryData['referralCount'],
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
            $query->whereHas('job', function ($q) use($request) {
                $q->where('job_title_id', $request->job_title);
            });
        }

        if ($request->search) {
            $query->where(function($q) use($request){
                $q->where('applied_for', 'like', '%'.$request->search.'%')
                ->orWhereHas('job', function($query) use($request){
                    $query->where('job_id', 'like', '%'.$request->search.'%');
                })
                ->orWhereHas('candidate', function($query) use($request){
                    $query->where('name', 'like', '%'.$request->search.'%');
                });
            });
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);

        return $query;
    }

    public function show($id, $jobId){
        $job = ErpRecruitmentJob::find($jobId);
        $referral = ErpRecruitmentJobCandidate::with('referalDetail')
                ->whereHas('referalDetail', function($q) use($id){
                    $q->where('id',$id);
                })->first();

        $jobSkills = [];
        if ($job) {
            $jobSkills = $job->jobSkills->pluck('name')->toArray();
        }

        $jobLogs = ErpRecruitmentJobLog::where('job_id',$jobId)
                    ->whereIn('log_type',[CommonHelper::CANDIDATE,CommonHelper::INTERVIEW])
                    ->where('candidate_id',$referral->id)
                    // ->with(['interview' => function($q){
                    //     $q->select('id','round_id');
                    // }])
                    ->orderby('id','DESC')
                    ->get();
        
        return view('recruitment.referal.show',[
            'job' => $job,
            'jobSkills' => $jobSkills,
            'referral' => $referral,
            'jobLogs' => $jobLogs,
        ]);
    }
}