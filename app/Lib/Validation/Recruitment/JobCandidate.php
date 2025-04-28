<?php

namespace App\Lib\Validation\Recruitment;

use App\Exceptions\ApiGenericException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class JobCandidate
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			// 'job_id' => [
			// 	'nullable'
			// ],
			'name' => [
				'required','string','max:250'
			],
			'email' => [
				'required',
				'email',
				'max:50',
				Rule::unique('erp_recruitment_job_candidates', 'email')
					->where('job_id', isset($this->request->job_id) ? $this->request->job_id : 0)
			],
			'mobile_no' => [
				'required',
				'string',
				'min:8',
				'max:10',
				'regex:/^([0-9\s\-\+\(\)]*)$/',
				Rule::unique('erp_recruitment_job_candidates', 'mobile_no')
				->where('job_id', isset($this->request->job_id) ? $this->request->job_id : 0)
			],
			'education_id' => [
				'required'
			],
			'work_exp' => [
				'required','integer'
			],
			'current_organization' => [
				'required','string','max:250'
			],
			'exp_salary' => [
				'required','numeric'
			],
			'location_id' => [
				'required'
			],
			'status' => [
				'required'
			],
			'potential_type' => [
				'required'
			],
			'resume' => [
				'required',
                'mimes:pdf',
				'max:2000',
			],
			'skill' => [
				'required'
			],
		],[
			'name.required' => 'Name field is required!',
			'email.required' => 'Email field is required!',
			'mobile_no.required' => 'Mobile no field is required!',
			'education_id.required' => 'Education field is required!',
			'work_exp.required' => 'Work experienc field is required!',
			'current_organization.required' => 'Current organization field is required!',
			'exp_salary.required' => 'Expected salary field is required!',
			'location_id.required' => 'Location detail field is required!',
			'status.required' => 'Status field is required!',
			'potential_type.required' => 'Potential field is required!',
			'resume.required' => 'Resume field is required!',
			'skill.required' => 'Skill field is required!',
		]);

		return $validator;
	}



	public function update() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			// 'job_id' => [
			// 	'nullable'
			// ],
			'name' => [
				'required','string','max:250'
			],
			'email' => [
				'required',
				'email',
				'max:50',
				Rule::unique('erp_recruitment_job_candidates', 'email')
					->where('job_id', isset($this->request->job_id) ? $this->request->job_id : 0)
					->ignore($this->request->id, 'id')
			],
			'mobile_no' => [
				'required',
				'string',
				'min:8',
				'max:10',
				'regex:/^([0-9\s\-\+\(\)]*)$/',
				Rule::unique('erp_recruitment_job_candidates', 'mobile_no')
				->where('job_id', isset($this->request->job_id) ? $this->request->job_id : 0)
				->ignore($this->request->id, 'id')
			],
			'education_id' => [
				'required'
			],
			'work_exp' => [
				'required','integer'
			],
			'current_organization' => [
				'required','string','max:250'
			],
			'exp_salary' => [
				'required','numeric'
			],
			'location_id' => [
				'required'
			],
			'status' => [
				'required'
			],
			'potential_type' => [
				'required'
			],
			'resume' => [
				'nullable',
                'mimes:pdf',
				'max:5000',
			],
			'skill' => [
				'required'
			],
		],[
			'name.required' => 'Name field is required!',
			'email.required' => 'Email field is required!',
			'mobile_no.required' => 'Mobile no field is required!',
			'education_id.required' => 'Education field is required!',
			'work_exp.required' => 'Work experienc field is required!',
			'current_organization.required' => 'Current organization field is required!',
			'exp_salary.required' => 'Expected salary field is required!',
			'location_id.required' => 'Location detail field is required!',
			'status.required' => 'Status field is required!',
			'potential_type.required' => 'Potential field is required!',
			'resume.required' => 'Resume field is required!',
			'skill.required' => 'Skill field is required!',
		]);

		return $validator;
	}

}
