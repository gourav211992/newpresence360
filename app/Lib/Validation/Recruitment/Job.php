<?php

namespace App\Lib\Validation\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class Job
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'employement_type' => [
				'required'
			],
			'job_title_id' => [
				'required'
			],
			'no_of_position' => [
				'required'
			],
			'job_request' => [
				'required', 'array', 'min:1'
			],
			'status' => [
				'required'
			],
			'last_apply_date' => [
				'nullable','date','after:today'
			],
			'third_party_assessment' => [
				'required'
			],
			'assessment_url' => [
				'required_if:third_party_assessment,yes',
				'nullable',
				'url'
			],
			'publish_for' => [
				'required'
			],
			'industry_id' => [
				'required'
			],
			'work_mode' => [
				'required'
			],
			'company_detail' => [
				'required'
			],
			'education_id' => [
				'required'
			],
			'work_exp_min' => [
				'required'
			],
			'work_exp_max' => [
				'required'
			],
			'location_id' => [
				'required'
			],
			'company_id' => [
				'required'
			],
			'working_hour_id' => [
				'required'
			],
			'annual_salary_min' => [
				'required'
			],
			'annual_salary_max' => [
				'required'
			],
			'notice_peroid_id' => [
				'required'
			],
			'description' => [
				'required'
			],
			'skill' => [
				'required'
			],
			'notification_email' => [
				'required'
			],
			"data" => [
                "nullable",
                "array"
            ],
            "data.*.round" => [
                "required",
                "max:255",
            ],
            "data.*.panel_ids" => [
                "required",
                "max:255",
            ],
            "data.*.external_email" => [
                "nullable",
            ],

		],[
			'no_of_position.required' => 'No of position field is required!',
			'employement_type.required' => 'Employement type field is required!',
			'job_request.required' => 'Request field is required!',
			'job_title_id.required' => 'Job title field is required!',
			'status.required' => 'Status field is required!',
			'third_party_assessment.required' => 'Third party assessment field is required!',
			'assessment_url.required_if' => 'Assessment link field is required!',
			'assessment_url.url' => 'The assessment link field must be a valid URL.',
			'publish_for.required' => 'Publish for field is required!',
			'industry_id.required' => 'Industry field is required!',
			'work_mode.required' => 'Work mode field is required!',
			'company_detail.required' => 'Company detail field is required!',
			'education_id.required' => 'Education field is required!',
			'work_exp_min.required' => 'Work experience min field is required!',
			'work_exp_max.required' => 'Work experience max field is required!',
			'company_id.required' => 'Company field is required!',
			'location_id.required' => 'Location field is required!',
			'working_hour_id.required' => 'Working hour field is required!',
			'annual_salary_min.required' => 'Annual salary min field is required!',
			'annual_salary_max.required' => 'Annual salary max field is required!',
			'notice_peroid_id.required' => 'Notice peroid field is required!',
			'description.required' => 'Description field is required!',
			'skill.required' => 'Skill field is required!',
			'notification_email.required' => 'Notification email field is required!',
		]);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
			$data = $validator->getData();
			if (isset($data['annual_salary_min']) && isset($data['annual_salary_max'])) {
				if ($data['annual_salary_max'] <= $data['annual_salary_min']) {
					$validator->errors()->add('annual_salary_max', 'Annual salary max must be greater than annual salary min.');
				}
			}

			if (isset($data['work_exp_min']) && isset($data['work_exp_max'])) {
				if ($data['work_exp_max'] <= $data['work_exp_min']) {
					$validator->errors()->add('work_exp_max', 'Working experience max must be greater than working experience min.');
				}
			}
			
			if (isset($data['data']) && is_array($data['data'])) {
				$rounds = [];
				foreach ($data['data'] as $index => $item) {
					if (in_array($item['round'], $rounds)) {
						throw new ApiGenericException('Each round must be unique.');
					} else {
						$rounds[] = $item['round'];
					}
				}
			}
		});


		return $validator;
	}

	public function update() : ValidationValidator
	{
		$panelAllocationsCount = ErpRecruitmentJobPanelAllocation::where('job_id', $this->request->job_id)->count();

		$validator = Validator::make($this->request->all(),[
			'employement_type' => [
				'required'
			],
			'status' => [
				'required'
			],
			'third_party_assessment' => [
				'required'
			],
			'no_of_position' => [
				'required'
			],
			'assessment_url' => [
				'required_if:third_party_assessment,yes',
				'nullable',
				'url'
			],
			'last_apply_date' => [
				'nullable','date','after:today'
			],
			'publish_for' => [
				'required'
			],
			'industry_id' => [
				'required'
			],
			'work_mode' => [
				'required'
			],
			'company_detail' => [
				'required'
			],
			'education_id' => [
				'required'
			],
			'work_exp_min' => [
				'required'
			],
			'work_exp_max' => [
				'required'
			],
			'company_id' => [
				'required'
			],
			'location_id' => [
				'required'
			],
			'working_hour_id' => [
				'required'
			],
			'annual_salary_min' => [
				'required'
			],
			'annual_salary_max' => [
				'required'
			],
			'notice_peroid_id' => [
				'required'
			],
			'description' => [
				'required'
			],
			'skill' => [
				'required'
			],
			"data" => [
                "nullable",
                "array"
            ],
            "data.*.round" => [
                $panelAllocationsCount > 0 ? "nullable" : "required",
                "max:255",
            ],
            "data.*.panel_ids" => [
                $panelAllocationsCount > 0 ? "nullable" : "required",
                "max:255",
            ],
            "data.*.external_email" => [
                "nullable",
            ],

		],[
			'no_of_position.required' => 'No of position field is required!',
			'employement_type.required' => 'Employement type field is required!',
			'job_request.required' => 'Request field is required!',
			'job_title_id.required' => 'Job title field is required!',
			'status.required' => 'Status field is required!',
			'third_party_assessment.required' => 'Third party assessment field is required!',
			'assessment_url.required_if' => 'Assessment link field is required!',
			'assessment_url.url' => 'The assessment link field must be a valid URL.',
			'publish_for.required' => 'Publish for field is required!',
			'industry_id.required' => 'Industry field is required!',
			'work_mode.required' => 'Work mode field is required!',
			'company_detail.required' => 'Company detail field is required!',
			'education_id.required' => 'Education field is required!',
			'work_exp_min.required' => 'Work experience min field is required!',
			'work_exp_max.required' => 'Work experience max field is required!',
			'company_id.required' => 'Company field is required!',
			'location_id.required' => 'Location field is required!',
			'working_hour_id.required' => 'Working hour field is required!',
			'annual_salary_min.required' => 'Annual salary min field is required!',
			'annual_salary_max.required' => 'Annual salary max field is required!',
			'notice_peroid_id.required' => 'Notice peroid field is required!',
			'description.required' => 'Description field is required!',
			'skill.required' => 'Skill field is required!',
			'notification_email.required' => 'Notification email field is required!',
		]);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
			$data = $validator->getData();
			if (isset($data['annual_salary_min']) && isset($data['annual_salary_max'])) {
				if ($data['annual_salary_max'] <= $data['annual_salary_min']) {
					$validator->errors()->add('annual_salary_max', 'Annual salary max must be greater than annual salary min.');
				}
			}

			if (isset($data['work_exp_min']) && isset($data['work_exp_max'])) {
				if ($data['work_exp_max'] <= $data['work_exp_min']) {
					$validator->errors()->add('work_exp_max', 'Working experience max must be greater than working experience min.');
				}
			}
			
			if (isset($data['data']) && is_array($data['data'])) {
				$rounds = [];
				foreach ($data['data'] as $index => $item) {
					if (in_array($item['round'], $rounds)) {
						throw new ApiGenericException('Each round must be unique.');
					} else {
						$rounds[] = $item['round'];
					}
				}
			}
		});


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

	public function assgnCandidates() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			"candidate_ids" => [
                "nullable",
                "array"
            ]

		]);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
			$candidateIds = $this->request->input('candidate_ids', []);

			// Check if there are no candidates selected
			if (count($candidateIds) < 1) {
				throw new ApiGenericException('At least one candidate must be selected.');
			}
		});


		return $validator;
	}

	public function updateCandidateStatus() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'status' => [
				'required',
			],
			'job_id' => [
				'required',
			],
			'candidate_id' => [
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

	public function assgnVendors() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			"vendor_ids" => [
                "required",
                "array"
            ],

		],[
			'vendor_ids.required' => 'Vendor is required.'
		]);


		return $validator;
	}

}
