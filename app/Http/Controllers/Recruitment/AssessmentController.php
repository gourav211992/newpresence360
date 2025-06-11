<?php

namespace App\Http\Controllers\Recruitment;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Recruitment\ErpRecruitmentJobTitle;
use Illuminate\Http\Request;
use App\Lib\Validation\Recruitment\Assessment as Validator;
use App\Models\Designation;
use App\Models\Recruitment\ErpRecruitmentAssessment;
use App\Models\Recruitment\ErpRecruitmentAssessmentQuestion;
use App\Models\Recruitment\ErpRecruitmentAssessmentQuestionOption;
use App\Models\Recruitment\ErpRecruitmentJob;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $assessments = ErpRecruitmentAssessment::with([
                                        'jobTitle' => function($q){
                                            $q->select('id','title');
                                        },
                                        'department' => function($q){
                                            $q->select('id','name');
                                        },
                                        'designation' => function($q){
                                            $q->select('id','name');
                                        }
                                    ])
                                    ->when($request->search, function($query, $request) {
                                        $query->where(function($q) use ($request) {
                                            $q->where('task_title', 'like', "%{$request->search}%")
                                            ->orWhere('task_type', 'like', "%{$request->search}%")
                                            ->orWhere('template_name', 'like', "%{$request->search}%")
                                            ->orWhereHas('jobTitle', function($q) use ($request) {
                                                $q->where('title', 'like', "%{$request->search}%");
                                            })
                                            ->orWhereHas('department', function($q) use ($request) {
                                                $q->where('name', 'like', "%{$request->search}%");
                                            });
                                        });
                                    })
                                    ->when($request->job_title, function($query, $value) {
                                        $query->where('job_title_id', $value);
                                    })
                                    ->when($request->task_type, function($query, $value) {
                                        $query->where('task_type', $value);
                                    })
                                    ->when($request->date_range, function($query, $value) {
                                        $dates = explode(' to ', $value);
                                        $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
                                        $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
                                        $query->whereBetween('created_at', [$startDate, $endDate]);
                                    })
                                    ->withCount('questions')
                                    ->where('organization_id',$user->organization_id)
                                    ->orderBy('created_at','desc')
                                    ->paginate($length);
                                    // dd($assessments->toArray());

        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        return view('recruitment.assessment.index',[
            'assessments' => $assessments,
            'jobTitles' => $jobTitles,
        ]);
    }
    
    public function create(){
        $user = Helper::getAuthenticatedUser();
        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $departments = Department::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $designations = Designation::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $templates = ErpRecruitmentAssessment::where('save_as_template','1')
                    ->where('organization_id',$user->organization_id)
                    ->get();

        return view('recruitment.assessment.create',[
            'jobTitles' => $jobTitles,
            'departments' => $departments,
            'templates' => $templates,
            'designations' => $designations,
        ]);
    }

    public function store(Request $request) {
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();

            $assessment = new ErpRecruitmentAssessment();
            if($request->template_id){
                $assessment = ErpRecruitmentAssessment::find($request->template_id);
            }
            $assessment->organization_id = $user->organization_id;
            $assessment->task_type = $request->task_type;
            $assessment->job_title_id = $request->job_title_id;
            $assessment->task_title = $request->task_title;
            $assessment->passing_percentage = $request->passing_percentage;
            $assessment->description = $request->description;
            $assessment->department_id = $request->department_id;
            $assessment->designation_id = $request->designation_id;
            $assessment->min_exp = $request->min_exp;
            $assessment->max_exp = $request->max_exp;
            $assessment->save_as_template = $request->save_as_template ? $request->save_as_template : 0;
            $assessment->template_name = $request->template_name;
            $assessment->status = CommonHelper::ACTIVE;
            $assessment->save();

            if(isset($request->questions)){
                foreach ($request->questions as $qIndex => $qData) {
                    $question = new ErpRecruitmentAssessmentQuestion();
                    $question->organization_id = $user->organization_id;
                    $question->assessment_id = $assessment->id;
                    $question->question = $qData['title'];
                    $question->type = $qData['type'];
                    $question->is_dropdown = isset($qData['is_dropdown']) ? $qData['is_dropdown'] : NULL;
                    $question->is_required = isset($qData['is_required']) ? $qData['is_required'] : NULL;
                    $question->score_from = isset($qData['score_from']) ? $qData['score_from'] : NULL;
                    $question->score_to = isset($qData['score_to']) ? $qData['score_to'] : NULL;
                    $question->low_score = isset($qData['low_score']) ? $qData['low_score'] : NULL;
                    $question->high_score = isset($qData['high_score']) ? $qData['high_score'] : NULL;
                    $question->save();

                    if (isset($qData['attachment']) && $qData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                        $attachment = $qData['attachment'];
                        $documentName = time() . '-' . $attachment->getClientOriginalName();
                        $attachment->move(public_path('attachments/assessment'), $documentName);
                        $documentPath = 'attachments/assessment/' . $documentName;

                        $question->image = $documentPath;
                        $question->save();
                    }

                    foreach ($qData['options'] ?? [] as $optionIndex => $optionText) {
                        $option = new ErpRecruitmentAssessmentQuestionOption();
                        $option->organization_id = $user->organization_id;
                        $option->assessment_id = $assessment->id;
                        $option->assessment_question_id = $question->id;
                        $option->option = $optionText;

                        if ($qData['type'] === 'single choice' || $qData['type'] === 'dropdown') {
                            $option->is_correct = (isset($qData['correct_option']) && $qData['correct_option'] == $optionIndex) ? 1 : 0;
                        } elseif ($qData['type'] === 'multiple choice') {
                            $option->is_correct = (isset($qData['correct_options']) && in_array($optionIndex, $qData['correct_options'])) ? 1 : 0;
                        } else {
                            $option->is_correct = 0;
                        }

                        $option->save();
                    }

                    foreach ($qData['options_images'] ?? [] as $imageIndex => $image) {
                        if ($image instanceof \Illuminate\Http\UploadedFile) {
                            $documentName = time() . '-' . $image->getClientOriginalName();
                            $image->move(public_path('attachments/assessment/options'), $documentName);
                            $documentPath = 'attachments/assessment/options/' . $documentName;

                            $option = new ErpRecruitmentAssessmentQuestionOption();
                            $option->image = $documentPath;
                            $option->organization_id = $user->organization_id;
                            $option->assessment_id = $assessment->id;
                            $option->assessment_question_id = $question->id;
                            if ($qData['type'] === 'image') {
                                $option->is_correct = isset($qData['correct_option']) && $qData['correct_option'] == $imageIndex ? 1 : 0;
                            }
                            $option->save();
                        }
                    }
                }
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Assessment created successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function edit($id){
        $user = Helper::getAuthenticatedUser();
        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $departments = Department::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $designations = Designation::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $templates = ErpRecruitmentAssessment::where('save_as_template','1')
                    ->where('organization_id',$user->organization_id)
                    ->get();

        $assessment = ErpRecruitmentAssessment::find($id);
        $questions = ErpRecruitmentAssessmentQuestion::with('options')
                        ->where('assessment_id',$id)
                        ->get();
        return view('recruitment.assessment.edit',[
            'jobTitles' => $jobTitles,
            'departments' => $departments,
            'designations' => $designations,
            'templates' => $templates,
            'questions' => $questions,
            'assessment' => $assessment,
        ]);
    }

    public function update(Request $request, $id) {
        $request->merge(['id' => $id]);
        $validator = (new Validator($request))->edit();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();

            $assessment = ErpRecruitmentAssessment::find($id);
            if($request->template_id){
                $assessment = ErpRecruitmentAssessment::find($request->template_id);
            }
            $assessment->organization_id = $user->organization_id;
            $assessment->task_type = $request->task_type;
            $assessment->job_title_id = $request->job_title_id;
            $assessment->task_title = $request->task_title;
            $assessment->passing_percentage = $request->passing_percentage;
            $assessment->description = $request->description;
            $assessment->department_id = $request->department_id;
            $assessment->designation_id = $request->designation_id;
            $assessment->min_exp = $request->min_exp;
            $assessment->max_exp = $request->max_exp;
            $assessment->save_as_template = $request->save_as_template ? $request->save_as_template : 0;
            $assessment->template_name = $request->template_name;
            $assessment->save();
            
            if(isset($request->questions)){
                foreach ($request->questions as $qIndex => $qData) {
                    $question = new ErpRecruitmentAssessmentQuestion();
                    $question->organization_id = $user->organization_id;
                    $question->assessment_id = $assessment->id;
                    $question->question = $qData['title'];
                    $question->type = $qData['type'];
                    $question->is_dropdown = isset($qData['is_dropdown']) ? $qData['is_dropdown'] : NULL;
                    $question->is_required = isset($qData['is_required']) ? $qData['is_required'] : NULL;
                    $question->score_from = isset($qData['score_from']) ? $qData['score_from'] : NULL;
                    $question->score_to = isset($qData['score_to']) ? $qData['score_to'] : NULL;
                    $question->low_score = isset($qData['low_score']) ? $qData['low_score'] : NULL;
                    $question->high_score = isset($qData['high_score']) ? $qData['high_score'] : NULL;
                    $question->save();
    
                    if (isset($qData['attachment']) && $qData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                        $attachment = $qData['attachment'];
                        $documentName = time() . '-' . $attachment->getClientOriginalName();
                        $attachment->move(public_path('attachments/assessment'), $documentName);
                        $documentPath = 'attachments/assessment/' . $documentName;
    
                        $question->image = $documentPath;
                        $question->save();
                    }
    
                    foreach ($qData['options'] ?? [] as $optionIndex => $optionText) {
                        $option = new ErpRecruitmentAssessmentQuestionOption();
                        $option->organization_id = $user->organization_id;
                        $option->assessment_id = $assessment->id;
                        $option->assessment_question_id = $question->id;
                        $option->option = $optionText;

                        if ($qData['type'] === 'single choice' || $qData['type'] === 'dropdown') {
                            $option->is_correct = (isset($qData['correct_option']) && $qData['correct_option'] == $optionIndex) ? 1 : 0;
                        } elseif ($qData['type'] === 'multiple choice') {
                            $option->is_correct = (isset($qData['correct_options']) && in_array($optionIndex, $qData['correct_options'])) ? 1 : 0;
                        } else {
                            $option->is_correct = 0;
                        }

                        $option->save();
                    }

                    foreach ($qData['options_images'] ?? [] as $imageIndex => $image) {
                        if ($image instanceof \Illuminate\Http\UploadedFile) {
                            $documentName = time() . '-' . $image->getClientOriginalName();
                            $image->move(public_path('attachments/assessment/options'), $documentName);
                            $documentPath = 'attachments/assessment/options/' . $documentName;

                            $option = new ErpRecruitmentAssessmentQuestionOption();
                            $option->image = $documentPath;
                            $option->organization_id = $user->organization_id;
                            $option->assessment_id = $assessment->id;
                            $option->assessment_question_id = $question->id;
                            if ($qData['type'] === 'image') {
                                $option->is_correct = isset($qData['correct_option']) && $qData['correct_option'] == $imageIndex ? 1 : 0;
                            }
                            $option->save();
                        }
                    }
                }
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Assessment updated successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function templateData(Request $request){
        $template = ErpRecruitmentAssessment::where('id',$request->template_id)->first();
        $questions = ErpRecruitmentAssessmentQuestion::with('options')
                        ->where('assessment_id',$request->template_id)
                        ->get();

        return response()->json([
            'template' => $template,
            'html' => view('recruitment.partials.template-view', [
                'questions' => $questions,
            ])->render()
        ]);
    }

    public function removeQuestion($id){
        ErpRecruitmentAssessmentQuestionOption::where('assessment_question_id',$id)->delete();
        ErpRecruitmentAssessmentQuestion::where('id',$id)->delete();
        return [
            "message" => 'Question deleted successfully!'
        ];
    }

    public function removeOption($id){
        ErpRecruitmentAssessmentQuestionOption::where('id',$id)->delete();
        return [
            "message" => 'Option deleted successfully!'
        ];
    }

    public function destroy($id){
        ErpRecruitmentAssessmentQuestionOption::where('assessment_id',$id)->delete();
        ErpRecruitmentAssessmentQuestion::where('assessment_id',$id)->delete();
        ErpRecruitmentAssessment::where('id',$id)->delete();
        return [
            "message" => 'Assessment deleted successfully!'
        ];
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:erp_recruitment_assessment,id',
            'status' => 'required'
        ]);

        $assessment = ErpRecruitmentAssessment::find($request->id);
        $assessment->status = $request->status;
        $assessment->save();

        return [
            "message" => 'Assessment status updated successfully!'
        ];
    }

    public function show($id){
        $user = Helper::getAuthenticatedUser();
        $jobTitles = ErpRecruitmentJobTitle::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $departments = Department::where('status','active')
            ->where('organization_id',$user->organization_id)
            ->get();

        $templates = ErpRecruitmentAssessment::where('save_as_template','1')
                    ->where('organization_id',$user->organization_id)
                    ->get();

        $assessment = ErpRecruitmentAssessment::find($id);
        $questions = ErpRecruitmentAssessmentQuestion::with('options')
                        ->where('assessment_id',$id)
                        ->get();
        return view('recruitment.assessment.view',[
            'jobTitles' => $jobTitles,
            'departments' => $departments,
            'templates' => $templates,
            'questions' => $questions,
            'assessment' => $assessment,
        ]);
    }

    public function result(){
        return view('recruitment.assessment.index');
    }
    
}