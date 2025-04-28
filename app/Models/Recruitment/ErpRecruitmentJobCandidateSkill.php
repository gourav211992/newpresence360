<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobCandidateSkill extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_candidate_skills';
    protected $fillable = [
        'id', 'candidate_id', 'skill_id'
    ];
}
