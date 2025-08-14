<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentAssessmentQuestionOption extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_assessment_question_options';
    protected $fillable = [
        'organization_id',
        'assessment_id',
        'assessment_question_id',
        'option',
        'image',
        'is_correct',
    ];
}
