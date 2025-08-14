<?php

namespace App\Lib\Validation\Recruitment;

use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobCandidate;
use App\Models\Recruitment\ErpRecruitmentJobReferral;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class JobReferral
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'applied_for' => [
				'required'
			],
			'name' => [
				'required_if:applied_for,self','nullable','string','max:250'
			],
			'candidate_id' => [
				'required_if:applied_for,refer','nullable'
			],
			'email' => [
				'nullable',
				'email',
				'max:50',
				// Rule::unique('erp_recruitment_job_candidates', 'email')
			],
			'mobile_no' => [
				'required',
				'string',
				'min:8',
				'max:10',
				'regex:/^([0-9\s\-\+\(\)]*)$/',
				// Rule::unique('erp_recruitment_job_candidates', 'mobile_no')
			],
			'resume' => [
				'nullable',
                'mimes:pdf',
				'max:2000',
			],
		],[
			'name.required_if' => 'Name field is required!',
			'candidate_id.required_if' => 'Name field is required!',
			'email.required' => 'Email field is required!',
			'mobile_no.required' => 'Mobile no field is required!',
			'resume.required' => 'Resume field is required!',
		]);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
			$candidate = ErpRecruitmentJobCandidate::where('mobile_no',$this->request->mobile_no)->first();
			$candidateId = $this->request->candidate_id ? $this->request->candidate_id : @$candidate->id;
			$isexist = ErpRecruitmentJobReferral::where('job_id', $this->request->job_id)
				->where('candidate_id', $candidateId)
				->first();
				
			// If the round has feedback (meaning it's completed), add an error
			if ($isexist) {
				$validator->errors()->add('mobile_no', 'The request candidate has already been added in refered for this job.');
			}
			
			if(!$candidate && !$this->request->resume){
				$validator->errors()->add('resume', 'Resume field is required!.');
			}

			if($candidate && !$candidate->resume_path && !$this->request->resume){
				$validator->errors()->add('resume', 'Resume field is required!.');
			}

		});


		return $validator;
	}

}
