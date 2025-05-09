<?php

namespace App\Lib\Validation\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Models\Recruitment\ErpRecruitmentJob;
use App\Models\Recruitment\ErpRecruitmentJobInterview;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class JobInterview
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

	public function scheduledInterview() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'candidate_id' => [
				'required',
			],
			'round_id' => [
				'required',
			],
			'date_time' => [
				'required',
				function($attribute, $value, $fail) {
					$inputDateTime = Carbon::parse($value);
					$currentDateTime = Carbon::now();

					if ($inputDateTime <= $currentDateTime) {
						$fail("The date & time must be after the current date and time.");
					}
				}
			],
			'remarks' => [
				'required','max:250'
			],
			'meeting_link' => [
				'required','url'
			]
		],[
            'candidate_id.required' => 'Remark field is required!',
            'round_id.required' => 'Round field is required!',
            'date_time.required' => 'Interview Date & Time field is required!',
            'date_time.after' => 'The date & time must be after the current date and time.',
            'remarks.required' => 'Remark field is required!',
            'meeting_link.required' => 'Meeting link field is required!',
		]);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
			$round = ErpRecruitmentJobInterview::where('job_id', $this->request->job_id)
				->where('candidate_id', $this->request->candidate_id)
				->where('round_id', $this->request->round_id)
				->first();

			// If the round has feedback (meaning it's completed), add an error
			if ($round) {
				$validator->errors()->add('round_id', 'This round has already been completed and cannot be selected.');
			}

		});

		return $validator;
		
	}

	public function feedback() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			// 'candidate_id' => [
			// 	'required',
			// ],
			'job_interview_id' => [
				'required',
			],
			// 'status' => [
			// 	'required',
			// ],
			'rating' => [
				'required',
			],
			'behavior' => [
				'required',
			],
			'skills' => [
				'required',
			],
			// 'attachment' => [
			// 	'nullable',
			// 	'mimes:pdf,png,jpeg,jpg',
			// 	'max:2000',
			// ],
			'remarks' => [
				'required','max:250'
			]
		],[
            // 'candidate_id.required' => 'Candidate field is required!',
            'job_interview_id.required' => 'Job interview field is required!',
            'status.required' => 'Status field is required!',
            'rating.required' => 'Rating field is required!',
            'behavior.required' => 'Behavior field is required!',
            'skills.required' => 'Skills field is required!',
            'remarks.required' => 'Remark field is required!',
		]);

		return $validator;
	}


	public function hrfeedback() : ValidationValidator
	{
		$validator = Validator::make($this->request->all(),[
			'job_interview_id' => [
				'required',
			],
			'status' => [
				'required',
			],
			'attachment' => [
				'nullable',
				'mimes:pdf,png,jpeg,jpg',
				'max:2000',
			],
			'remarks' => [
				'required','max:250'
			]
		],[
            // 'candidate_id.required' => 'Candidate field is required!',
            'job_interview_id.required' => 'Job interview field is required!',
            'status.required' => 'Status field is required!',
            'remarks.required' => 'Remark field is required!',
		]);

		return $validator;
	}

}
