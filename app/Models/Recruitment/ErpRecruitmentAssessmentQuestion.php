<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentAssessmentQuestion extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_assessment_questions';
    protected $fillable = [
        'organization_id',
        'assessment_id',
        'question',
        'type',
        'image',
        'is_dropdown',
        'is_required',
        'score_from',
        'score_to',
        'low_score',
        'high_score',
    ];

    public function options(){
        return $this->hasMany(ErpRecruitmentAssessmentQuestionOption::class,'assessment_question_id', 'id');
    }
}
