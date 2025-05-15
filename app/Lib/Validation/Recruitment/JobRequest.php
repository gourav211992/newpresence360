<?php

namespace App\Lib\Validation\Recruitment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class JobRequest
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'job_type' => [
				'required'
			],
            'emp_id' => [
				'required_if:job_type,==,replacement'
			],
			'job_title_id' => [
				'required'
			],
			'no_of_position' => [
				'required'
			],
			'education_id' => [
				'required'
			],
			'work_exp_id' => [
				'required'
			],
			'expected_doj' => [
				'required','date','after:today'
			],
			'priority' => [
				'required'
			],
			'location_id' => [
				'required'
			],
			'company_id' => [
				'required'
			],
			'job_description' => [
				'required'
			],
			'reason' => [
				'required'
			],
			// 'status' => [
			// 	'required'
			// ],
			// 'assessment_required' => [
			// 	'required'
			// ],
			'skill' => [
				'required'
			],
		],[
			'emp_id.required_if' => 'Employee name field is required!',
            'job_type.required' => 'Job type field is required!',
            'job_title_id.required' => 'Job title field is required!',
            'no_of_position.required' => 'No of position field is required!',
            'education_id.required' => 'Education field is required!',
            'work_exp_id.required' => 'Work experience field is required!',
            'expected_doj.required' => 'Expected DOJ field is required!',
            'priority.required' => 'Priority field is required!',
            'location_id.required' => 'Placed location field is required!',
            'company_id.required' => 'Company field is required!',
            'job_description.required' => 'Job description field is required!',
            'reason.required' => 'Reason field is required!',
            'status.required' => 'Status field is required!',
            'assessment_required.required' => 'Assessment field is required!',
            'skill.required' => 'Skill field is required!',
		]);

		return $validator;
	}

	public function updatestatus() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'status' => [
				'required',
			],
			'log_message' => [
				'required',
			],
		],[
            'log_message.required' => 'Remark field is required!'
		]);

		return $validator;
	}


}
