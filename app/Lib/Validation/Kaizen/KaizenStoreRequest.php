<?php

namespace App\Lib\Validation\Kaizen;

use App\Helpers\CommonHelper;
use App\Models\Kaizen\ErpKaizenDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class KaizenStoreRequest
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store() : ValidationValidator
	{

		$rules = [
			'department_id'     => ['required'],
			'team_id'           => ['required','array'],
			'date'              => ['required', 'date', 'before_or_equal:today'],
			'before_kaizen'     => ['required', 'array', 'max:2'],
            'before_kaizen.*'   => ['file', 'mimes:png,jpg,jpeg,pdf,gif', 'max:5120'],
            'after_kaizen'      => ['required', 'array', 'max:2'],
            'after_kaizen.*'    => ['file', 'mimes:png,jpg,jpeg,pdf,gif', 'max:5120'],
			'counter_measure'   => ['required'],
			'problem'   => ['required'],
			'benefits'          => ['required'],
			'occurence'          => ['required'],
            'improvement_type'  => ['required', 'array', 'min:1'],
		];

        $messages = [
            'department_id.required' => 'Department is required.',
            'team_id.required' => 'Kaizen Team is required.',
            'date.required' => 'Date is required.',
            'before_kaizen.required' => 'Please upload before Kaizen files.',
            'after_kaizen.required' => 'Please upload after Kaizen files.',
            'counter_measure.required' => 'Countermeasure is required.',
            'problem.required' => 'Problem are required.',
            'benefits.required' => 'Benefits are required.',
            'occurence.required' => 'Occurence is required.',
            'improvement_type.required' => 'Select at least one improvement category.',
        ];

		$validator = Validator::make($this->request->all(), $rules, $messages);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
            $data = $validator->getData();

            if (!isset($data['improvement_type'])) {
                return;
            }

            foreach ($data['improvement_type'] as $key) {
                if (empty($data['improvement'][$key] ?? null)) {
                    $validator->errors()->add("improvement.$key", 'The improvement selection for this category is required.');
                }

                if ($key === CommonHelper::COST && empty($data['improvement']['cost_saving_amt'])) {
                    $validator->errors()->add("improvement.cost_saving_amt", 'The saving amount is required when Cost is selected.');
                }
            }
		});

		return $validator;
	}

    public function update() : ValidationValidator
	{
       $beforeCount = ErpKaizenDocument::where('kaizen_id', $this->request->id)
            ->where('type', CommonHelper::BEFORE_KAIZEN)
            ->count();

        $afterCount = ErpKaizenDocument::where('kaizen_id', $this->request->id)
            ->where('type', CommonHelper::AFTER_KAIZEN)
            ->count();

        $beforeRemaining = 2 - $beforeCount;
        $afterRemaining = 2 - $afterCount;

		$rules = [
			'department_id'     => ['required'],
			'team_id'           => ['required','array'],
			'date'              => ['required', 'date', 'before_or_equal:today'],
			// 'before_kaizen'     => [$hasBeforeKaizen ? 'nullable' : 'required', 'array', 'max:2'],
            'before_kaizen.*'   => ['file', 'mimes:png,jpg,jpeg,pdf,gif', 'max:5120'],
            // 'after_kaizen'      => [$hasAfterKaizen ? 'nullable' : 'required', 'array', 'max:2'],
            'after_kaizen.*'    => ['file', 'mimes:png,jpg,jpeg,pdf,gif', 'max:5120'],
			'counter_measure'   => ['required'],
			'problem'   => ['required'],
			'benefits'          => ['required'],
            'improvement_type'  => ['required', 'array', 'min:1'],
		];

        // Before Kaizen validation
        if ($beforeCount <= 0) {
            $rules['before_kaizen'] = ['required', 'array', 'max:2'];
        } else {
            $rules['before_kaizen'] = ['nullable', 'array', 'max:' . $beforeRemaining];
        }

        // After Kaizen validation
        if ($afterCount <= 0) {
            $rules['after_kaizen'] = ['required', 'array', 'max:2'];
        } else {
            $rules['after_kaizen'] = ['nullable', 'array', 'max:' . $afterRemaining];
        }

        $messages = [
            'department_id.required' => 'Department is required.',
            'team_id.required' => 'Kaizen Team is required.',
            'date.required' => 'Date is required.',
            'before_kaizen.required' => 'Please upload before Kaizen files.',
            'after_kaizen.required' => 'Please upload after Kaizen files.',
            'counter_measure.required' => 'Countermeasure is required.',
            'problem.required' => 'Problem are required.',
            'benefits.required' => 'Benefits are required.',
            'improvement_type.required' => 'Select at least one improvement category.',
            'before_kaizen.prohibited' => 'Maximum 2 before Kaizen files allowed.',
            'after_kaizen.prohibited'  => 'Maximum 2 after Kaizen files allowed.',
            'after_kaizen.max'  => 'The after kaizen field must not have more than 2 files.',
            'before_kaizen.max'  => 'The before kaizen field must not have more than 2 files.',
        ];

		$validator = Validator::make($this->request->all(), $rules, $messages);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
            $data = $validator->getData();

            if (!isset($data['improvement_type'])) {
                return;
            }

            foreach ($data['improvement_type'] as $key) {
                if (empty($data['improvement'][$key] ?? null)) {
                    $validator->errors()->add("improvement.$key", 'The improvement selection for this category is required.');
                }

                if ($key === CommonHelper::COST && empty($data['improvement']['cost_saving_amt'])) {
                    $validator->errors()->add("improvement.cost_saving_amt", 'The saving amount is required when Cost is selected.');
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
			'remarks' => [
				'required',
			],
		],[
            'remarks.required' => 'Remark field is required!'
		]);

		return $validator;
	}
}