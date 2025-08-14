<?php

namespace App\Http\Controllers\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ErpStore;
use App\Models\Recruitment\ErpRecruitmentEducation;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use App\Models\Recruitment\ErpRecruitmentSkill;
use App\Lib\Validation\Recruitment\JobCandidate as Validator;
use App\Models\Recruitment\ErpRecruitmentAssignedCandidate;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobCandidateSkill;
use App\Models\Recruitment\ErpRecruitmentJobLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobCandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;
        $candidateQuery = ErpRecruitmentJobCandidate::with('candidateSkills')
            ->where(function($query) use($request){
                self::filter($request, $query);
            });

            if ($user->user_type === CommonHelper::IAM_VENDOR) {
                $candidateQuery->where('created_by', $user->id)
                        ->where('created_by_type', $user->authenticable_type);
            } else {
                $candidateQuery->where('organization_id',$user->organization_id);
            }

        $candidates = $candidateQuery->orderBy('created_at','desc')
            ->paginate($length);
            
        $masterData = self::masterData();
        return view('recruitment.job-candidate.index',[
            'candidates' => $candidates,
            'skillsData' => $masterData['skills'],
            'locations' => $masterData['locations'],
        ]);
    }

    private function filter($request, $query){
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
                ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        return $query;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jobs = ErpRecruitmentJob::where('status',CommonHelper::OPEN)->get();
        $masterData = self::masterData();
        return view('recruitment.job-candidate.create',[
            'skills' => $masterData['skills'],
            'locations' => $masterData['locations'],
            'educations' => $masterData['educations'],
            'jobs' => $jobs,
        ]);
    }

    private function masterData(){
        $user = Helper::getAuthenticatedUser();

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

        return [
            'skills' => $skills,
            'locations' => $locations,
            'educations' => $educations,
        ];

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();

            $jobCandidate = new ErpRecruitmentJobCandidate();
            $jobCandidate->organization_id = $user->organization_id;
            $jobCandidate->name = $request->name;
            $jobCandidate->email = $request->email;
            $jobCandidate->mobile_no = $request->mobile_no;
            $jobCandidate->education_id = $request->education_id;
            $jobCandidate->work_exp = $request->work_exp;
            $jobCandidate->current_organization = $request->current_organization;
            $jobCandidate->exp_salary = $request->exp_salary;
            $jobCandidate->location_id = $request->location_id;
            $jobCandidate->status = $request->status;
            $jobCandidate->potential_type = $request->potential_type;
            $jobCandidate->refered_by = $request->refered_by ? $request->refered_by : NULL;
            $jobCandidate->created_by = $user->id; 
            $jobCandidate->created_by_type = $user->authenticable_type; 
            $jobCandidate->save();
            
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

                $jobCandidateSkill = new ErpRecruitmentJobCandidateSkill();
                $jobCandidateSkill->candidate_id = $jobCandidate->id;
                $jobCandidateSkill->skill_id = $skill ? $skill->id : null;
                $jobCandidateSkill->created_at = date('Y-m-d h:i:s');
                $jobCandidateSkill->save();

            }

            if($request->hasFile('resume')){
                $attachment = $request->resume;
                $documentName = time() . ''.$jobCandidate->id.'-' . $attachment->getClientOriginalName();
                $attachment->move(public_path('attachments/candidate_attchments'), $documentName);
                $documentPath = 'attachments/candidate_attchments/'.$documentName;

                $jobCandidate->resume_path = $documentPath;
                $jobCandidate->save();
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Job candidate created successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id){
        $masterData = self::masterData();

        $jobCandidate = ErpRecruitmentJobCandidate::find($id);
        $candidateSkills = ErpRecruitmentJobCandidateSkill::where('candidate_id',$id)->pluck('skill_id')->toArray();
        $jobIds = ErpRecruitmentAssignedCandidate::where('candidate_id',$id)->pluck('job_id')->toArray();
        $jobs = ErpRecruitmentJob::where('status',CommonHelper::OPEN)->get();

        return view('recruitment.job-candidate.edit',[
            'skills' => $masterData['skills'],
            'locations' => $masterData['locations'],
            'educations' => $masterData['educations'],
            'jobs' => $jobs,
            'jobCandidate' => $jobCandidate,
            'candidateSkills' => $candidateSkills,
            'jobIds' => $jobIds,
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->merge(['job_id' => $id]);
        $validator = (new Validator($request))->update();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {

            $user = Helper::getAuthenticatedUser();

            $jobCandidate = ErpRecruitmentJobCandidate::find($id);
            $jobCandidate->organization_id = $user->organization_id;
            $jobCandidate->name = $request->name;
            $jobCandidate->email = $request->email;
            $jobCandidate->mobile_no = $request->mobile_no;
            $jobCandidate->education_id = $request->education_id;
            $jobCandidate->work_exp = $request->work_exp;
            $jobCandidate->current_organization = $request->current_organization;
            $jobCandidate->exp_salary = $request->exp_salary;
            $jobCandidate->location_id = $request->location_id;
            $jobCandidate->status = $request->status;
            $jobCandidate->potential_type = $request->potential_type;
            $jobCandidate->refered_by = $request->refered_by ? $request->refered_by : NULL;
            $jobCandidate->created_by = $user->id; 
            $jobCandidate->created_by_type = $user->authenticable_type; 
            $jobCandidate->save();

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

                ErpRecruitmentJobCandidateSkill::updateOrCreate([
                    'candidate_id' => $jobCandidate->id,
                    'skill_id' => $skill->id
                ]);
            }

            if($request->hasFile('resume')){
                if ($jobCandidate->resume_path && file_exists(public_path($jobCandidate->resume_path))) {
                    unlink(public_path($jobCandidate->resume_path));
                }
                
                $attachment = $request->resume;
                $documentName = time() . ''.$jobCandidate->id.'-' . $attachment->getClientOriginalName();
                $attachment->move(public_path('attachments/candidate_attchments'), $documentName);
                $documentPath = 'attachments/candidate_attchments/'.$documentName;

                $jobCandidate->resume_path = $documentPath;
                $jobCandidate->save();
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Job candidate updated successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        ErpRecruitmentJobCandidateSkill::where('candidate_id',$id)->delete();
        ErpRecruitmentJobLog::where('candidate_id',$id)->delete();
        $jobCandidate = ErpRecruitmentJobCandidate::find($id);
        if ($jobCandidate->resume_path && file_exists(public_path($jobCandidate->resume_path))) {
            unlink(public_path($jobCandidate->resume_path));
        }
        $jobCandidate->delete();
        return [
            "data" => null,
            "message" => "Candidate delete successfully!"
        ];
    }
}
