<?php

namespace App\Lib\Validation\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Models\Recruitment\ErpRecruitmentAssessment;
use App\Models\Recruitment\ErpRecruitmentJobPanelAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class Assessment
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store() : ValidationValidator
	{
		$existingQuestionsCount = 0;
		if($this->request->template_id){
			$assessment = ErpRecruitmentAssessment::withCount('questions')->find($this->request->template_id);
			$existingQuestionsCount = $assessment?->questions_count;
		}

		$rules = [
			'task_type'         => ['required','string','in:assessment,questionaries'],
			'job_title_id'      => ['required','integer','exists:erp_recruitment_job_title,id'],
			'task_title'        => ['required','string','max:255'],
			'passing_percentage'=> ['required','numeric','min:0','max:100'],
			'description'       => ['nullable','string'],
			'department_id'     => ['required','integer','exists:departments,id'],
			'designation_id'     => ['required','integer','exists:designations,id'],
			'min_exp'           => ['required','integer','min:0'],
			'max_exp'           => ['required','integer','gte:min_exp'],
			'save_as_template'  => ['nullable','string','max:255'],
			'template_name'     => ['nullable','string','max:255'],
		];

		$rules['questions'] = [$existingQuestionsCount == 0 ? 'required' : 'nullable', 'array'];

		$rules['questions.*.title'] = ['required_with:questions.*.type','string','max:255'];
		$rules['questions.*.type']  = ['required_with:questions.*.title','string','in:single choice,multiple choice,dropdown,file upload,short answer,rating,image'];
		$rules['questions.*.is_required'] = ['nullable','in:0,1'];
		$rules['questions.*.is_dropdown'] = ['nullable','in:0,1'];
		$rules['questions.*.options'] = ['nullable','array'];
		$rules['questions.*.options.*'] = ['required_with:questions.*.options','string','max:255'];
		$rules['questions.*.score_from'] = ['required_if:questions.*.type,rating','integer'];
		$rules['questions.*.score_to']   = ['required_if:questions.*.type,rating','integer','gte:questions.*.rating_from'];
		$rules['questions.*.low_score']  = ['required_if:questions.*.type,rating','string','max:255'];
		$rules['questions.*.high_score'] = ['required_if:questions.*.type,rating','string','max:255'];

		$messages = [
			'task_type.required' => 'Task type is required.',
			'job_title_id.required' => 'Job title is required.',
			'task_title.required' => 'Task title is required.',
			'passing_percentage.required' => 'Passing percentage is required.',
			'department_id.required' => 'Department is required.',
			'designation_id.required' => 'Designation is required.',
			'min_exp.required' => 'Minimum experience is required.',
			'max_exp.required' => 'Maximum experience is required.',
			'questions.*.title.required_with' => 'Title is required.',
			'questions.*.type.required_with' => 'Type is required.',
			'questions.*.options.required_with' => 'Option is required.',
			'questions.*.score_from' => 'Score is required.',
			'questions.*.score_to' => 'Score is required.',
			'questions.*.low_score' => 'Low score is required.',
			'questions.*.high_score' => 'High score is required.',
		];

		$validator = Validator::make($this->request->all(), $rules, $messages);

		if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
			$data = $validator->getData();

			if (!empty($data['questions'])) {
				foreach ($data['questions'] as $index => $question) {
					$type = $question['type'];

					if (in_array($type, ['single choice', 'multiple choice', 'dropdown'])) {

						// Check if 'options' is missing or not an array
						if (empty($question['options']) || !is_array($question['options'])) {
							$validator->errors()->add("questions.$index.options", "Options are required for question type: {$type}");
							continue; // Skip to next question to avoid looping over non-existent options
						}

						// Now it's safe to loop over the options
						foreach ($question['options'] as $optIndex => $option) {
							if (!is_string($option) || trim($option) === '') {
								$validator->errors()->add("questions.$index.options.$optIndex", "Each option must be a non-empty string.");
							}
						}
					}

					if ($type == 'image') {
						if (!isset($this->request['questions'][$index]['options_images']) || !is_array($this->request['questions'][$index]['options_images'])) {
							$validator->errors()->add("questions.$index.options_images", "Image options are required.");
							continue;
						}

						foreach ($this->request['questions'][$index]['options_images'] as $imgIndex => $image) {
							if (!$image || !is_file($image)) {
								$validator->errors()->add("questions.$index.options_images.$imgIndex", "Image file is required.");
								continue;
							}

							$mime = $image->getMimeType();
							$allowed = ['image/jpeg', 'image/png', 'image/jpg'];

							if (!in_array($mime, $allowed)) {
								$validator->errors()->add("questions.$index.options_images.$imgIndex", "Only JPG, JPEG, PNG images are allowed.");
							}

							if ($image->getSize() > 10 * 1024 * 1024) { // 10MB
								$validator->errors()->add("questions.$index.options_images.$imgIndex", "Image must not exceed 10MB.");
							}
						}
					}

					// ✅ Validate correct option(s) selection
					if (in_array($type, ['single choice', 'dropdown', 'image'])) {
						if (!isset($question['correct_option']) || $question['correct_option'] === '') {
							$validator->errors()->add("questions.$index.correct_option", "Please select the correct option for question: {$question['title']}");
						}
					}

					if ($type === 'multiple choice') {
						if (empty($question['correct_options']) || !is_array($question['correct_options'])) {
							$validator->errors()->add("questions.$index.correct_options", "Please select at least one correct option for question: {$question['title']}");
						}
					}

				}
			}
		});

		return $validator;
	}

	public function edit() : ValidationValidator
	{
		// Get the assessment if ID is passed
		$assessment = ErpRecruitmentAssessment::withCount('questions')->find($this->request->id);
		$existingQuestionsCount = $assessment?->questions_count ?? 0;

		$rules = [
			'task_type'         => 'required|string|in:assessment,questionaries',
			'job_title_id'      => 'required|integer|exists:erp_recruitment_job_title,id',
			'task_title'        => 'required|string|max:255',
			'passing_percentage'=> 'required|numeric|min:0|max:100',
			'description'       => 'nullable|string',
			'department_id'     => 'required|integer|exists:departments,id',
			'designation_id'     => 'required|integer|exists:designations,id',
			'min_exp'           => 'required|integer|min:0',
			'max_exp'           => 'required|integer|gte:min_exp',
			'save_as_template'  => 'nullable|string|max:255',
			'template_name'     => 'nullable|string|max:255',
		];

		$rules['questions'] = [$existingQuestionsCount == 0 ? 'required' : 'nullable', 'array'];
		$rules['questions.*.title'] = 'required|string|max:255';
		$rules['questions.*.type'] = 'required|string|in:single choice,multiple choice,dropdown,file upload,short answer,rating,image';
		$rules['questions.*.is_required'] = 'nullable|in:0,1';
		$rules['questions.*.is_dropdown'] = 'nullable|in:0,1';
		$rules['questions.*.options'] = 'nullable|array';
		$rules['questions.*.options.*'] = 'required_with:questions.*.options|string|max:255';
		$rules['questions.*.score_from'] = 'required_if:questions.*.type,rating|integer';
		$rules['questions.*.score_to'] = 'required_if:questions.*.type,rating|integer|gte:questions.*.score_from';
		$rules['questions.*.low_score'] = 'required_if:questions.*.type,rating|string|max:255';
		$rules['questions.*.high_score'] = 'required_if:questions.*.type,rating|string|max:255';

		$messages = [
			'task_type.required' => 'Task type is required.',
			'job_title_id.required' => 'Job title is required.',
			'task_title.required' => 'Task title is required.',
			'passing_percentage.required' => 'Passing percentage is required.',
			'department_id.required' => 'Department is required.',
			'designation_id.required' => 'Designation is required.',
			'min_exp.required' => 'Minimum experience is required.',
			'max_exp.required' => 'Maximum experience is required.',
			'questions.*.title.required' => 'Title is required.',
			'questions.*.type.required' => 'Type is required.',
			'questions.*.options.required_with' => 'Option is required.',
			'questions.*.score_from' => 'Score is required.',
			'questions.*.score_to' => 'Score is required.',
			'questions.*.low_score' => 'Low score is required.',
			'questions.*.high_score' => 'High score is required.',
		];

		$validator = Validator::make($this->request->all(), $rules, $messages);

		if ($validator->fails()) {
			return $validator;
		}

		// Custom logic to validate individual question options
		$validator->after(function ($validator) use ($existingQuestionsCount) {
			$data = $validator->getData();
			
			// Only run custom validation if no questions already exist
			if (!empty($data['questions'])) {
				foreach ($data['questions'] as $index => $question) {
					if (in_array($question['type'], ['single choice', 'multiple choice', 'dropdown'])) {

						// Check if 'options' is missing or not an array
						if (empty($question['options']) || !is_array($question['options'])) {
							$validator->errors()->add("questions.$index.options", "Options are required for question type: {$question['type']}");
							continue; // Skip to next question to avoid looping over non-existent options
						}

						// Now it's safe to loop over the options
						foreach ($question['options'] as $optIndex => $option) {
							if (!is_string($option) || trim($option) === '') {
								$validator->errors()->add("questions.$index.options.$optIndex", "Each option must be a non-empty string.");
							}
						}
					}

					if ($question['type'] == 'image') {
						if (!isset($this->request['questions'][$index]['options_images']) || !is_array($this->request['questions'][$index]['options_images'])) {
							$validator->errors()->add("questions.$index.options_images", "Image options are required.");
							continue;
						}

						foreach ($this->request['questions'][$index]['options_images'] as $imgIndex => $image) {
							if (!$image || !is_file($image)) {
								$validator->errors()->add("questions.$index.options_images.$imgIndex", "Image file is required.");
								continue;
							}

							$mime = $image->getMimeType();
							$allowed = ['image/jpeg', 'image/png', 'image/jpg'];

							if (!in_array($mime, $allowed)) {
								$validator->errors()->add("questions.$index.options_images.$imgIndex", "Only JPG, JPEG, PNG images are allowed.");
							}

							if ($image->getSize() > 10 * 1024 * 1024) { // 10MB
								$validator->errors()->add("questions.$index.options_images.$imgIndex", "Image must not exceed 10MB.");
							}
						}
					}

					// ✅ Validate correct option(s) selection
					if (in_array($question['type'], ['single choice', 'dropdown', 'image'])) {
						if (!isset($question['correct_option']) || $question['correct_option'] === '') {
							$validator->errors()->add("questions.$index.correct_option", "Please select the correct option for question: {$question['title']}");
						}
					}

					if ($question['type'] === 'multiple choice') {
						if (empty($question['correct_options']) || !is_array($question['correct_options'])) {
							$validator->errors()->add("questions.$index.correct_options", "Please select at least one correct option for question: {$question['title']}");
						}
					}

				}
			}
		});

		return $validator;
	}


}
