<?php

namespace App\Http\Controllers\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lib\Validation\Recruitment\JobInterview as Validator;
use App\Models\Recruitment\ErpRecruitmentAssignedCandidate;
use App\Models\Recruitment\ErpRecruitmentInterviewFeedback;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobLog;
use App\Models\Recruitment\ErpRecruitmentRound;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class JobInterviewController extends Controller
{
    public function scheduled(Request $request,$jobId){
        $request->merge(['job_id' => $jobId]);
        $validator = (new Validator($request))->scheduledInterview();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            
            $datetime = Carbon::parse($request->input('date_time'));
            $formattedDateTime = $datetime->format('Y-m-d H:i:s');
            
            $jobInterview = new ErpRecruitmentJobInterview();
            $jobInterview->fill($request->all());
            $jobInterview->organization_id = $user->organization_id;
            $jobInterview->job_id = $jobId;
            $jobInterview->status = CommonHelper::SCHEDULED;
            $jobInterview->date_time = $formattedDateTime;
            $jobInterview->created_by = $user->id; 
            $jobInterview->created_by_type = $user->authenticable_type; 
            $jobInterview->save();

            ErpRecruitmentAssignedCandidate::where([
                    'job_id' => $jobId, 
                    'candidate_id' => $request->candidate_id
                ])->update([
                    'status' => CommonHelper::SCHEDULED,
                    'remark' => $jobInterview->remarks,
                ]);

            // ✅ Log the job creation
            $jobLog = new ErpRecruitmentJobLog();
            $jobLog->organization_id = $user->organization_id;
            $jobLog->job_id = $jobId;
            $jobLog->candidate_id = $request->candidate_id;
            $jobLog->interview_id = $jobInterview->id;
            $jobLog->status = CommonHelper::SCHEDULED;
            $jobLog->log_type = CommonHelper::INTERVIEW; 
            $jobLog->log_message = $jobInterview->remarks; 
            $jobLog->action_by = $user->id;
            $jobLog->action_by_type = $user->authenticable_type;
            $jobLog->save();
        \DB::commit();
            return [
                "data" => null,
                "message" => "Interview scheduled successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function feedback(Request $request){
        $validator = (new Validator($request))->feedback();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $jobInterview = ErpRecruitmentJobInterview::find($request->job_interview_id);
            
            $feedback = new ErpRecruitmentInterviewFeedback();

            $feedback->interview_id = $jobInterview->id;
            $feedback->job_id = $jobInterview->job_id;
            $feedback->candidate_id = $jobInterview->candidate_id;
            $feedback->panel_id = $user->id;
            $feedback->round_id = $jobInterview->round_id;
            $feedback->rating = $request->rating;
            $feedback->behavior = $request->behavior;
            $feedback->skills = $request->skills;
            $feedback->remarks = $request->remarks;
            // $feedback->status = $request->status;
            $feedback->save();

            // if($request->hasFile('attachment')){
            //     $attachment = $request->attachment;
            //     $documentName = time() . ''.$jobInterview->id.''.$jobInterview->candidate_id.'-' . $attachment->getClientOriginalName();
            //     $attachment->move(public_path('attachments/interview_attchments'), $documentName);
            //     $documentPath = 'attachments/interview_attchments/'.$documentName;

            //     $feedback->attachment_path = $documentPath;
            //     $feedback->save();
            // }         

            // ✅ Log the job interviews
            // $jobLog = new ErpRecruitmentJobLog();
            // $jobLog->organization_id = $jobInterview->organization_id;
            // $jobLog->job_id = $jobInterview->job_id;
            // $jobLog->candidate_id = $jobInterview->candidate_id;
            // $jobLog->interview_id = $jobInterview->id;
            // $jobLog->status = $request->status;
            // $jobLog->log_type = CommonHelper::INTERVIEW; 
            // $jobLog->log_message = $request->remarks; 
            // $jobLog->action_by = $user->id;
            // $jobLog->action_by_type = $user->authenticable_type;
            // $jobLog->save();

            \DB::commit();
            return [
                "data" => null,
                "message" => "Feedback submitted successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function hrFeedback(Request $request){
        $validator = (new Validator($request))->hrfeedback();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $jobInterview = ErpRecruitmentJobInterview::find($request->job_interview_id);
            $oldStatus = $jobInterview->status;

            $jobInterview->remarks = $request->remarks;
            $jobInterview->status = $request->status;
            $jobInterview->save();

            if($request->hasFile('attachment')){
                $attachment = $request->attachment;
                $documentName = time() . ''.$jobInterview->id.''.$jobInterview->candidate_id.'-' . $attachment->getClientOriginalName();
                $attachment->move(public_path('attachments/interview_attchments'), $documentName);
                $documentPath = 'attachments/interview_attchments/'.$documentName;

                $jobInterview->attachment_path = $documentPath;
                $jobInterview->save();
            }         

            ErpRecruitmentAssignedCandidate::where([
                'job_id' => $jobInterview->job_id, 
                'candidate_id' => $jobInterview->candidate_id
            ])->update([
                'status' => $jobInterview->status,
                'remark' => $jobInterview->remarks,
            ]);

            // ✅ Log the job interviews
            if($oldStatus != $request->status){
                $jobLog = new ErpRecruitmentJobLog();
                $jobLog->organization_id = $jobInterview->organization_id;
                $jobLog->job_id = $jobInterview->job_id;
                $jobLog->candidate_id = $jobInterview->candidate_id;
                $jobLog->interview_id = $jobInterview->id;
                $jobLog->status = $request->status;
                $jobLog->log_type = CommonHelper::INTERVIEW; 
                $jobLog->log_message = $request->remarks; 
                $jobLog->action_by = $user->id;
                $jobLog->action_by_type = $user->authenticable_type;
                $jobLog->save();
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Feedback submitted successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }
}
