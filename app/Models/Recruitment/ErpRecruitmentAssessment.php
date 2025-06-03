<?php

namespace App\Models\Recruitment;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentAssessment extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_assessment';
    protected $fillable = [
        'organization_id',
        'task_type',
        'job_title_id',
        'task_title',
        'passing_percentage',
        'department_id',
        'designation_id',
        'description',
        'min_exp',
        'max_exp',
        'save_as_template',
        'template_name',
        'status',
    ];

    public function jobTitle()
    {
        return $this->belongsTo(ErpRecruitmentJobTitle::class, 'job_title_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function questions()
    {
        return $this->hasMany(ErpRecruitmentAssessmentQuestion::class, 'assessment_id');
    }
}
