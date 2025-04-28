<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobSkill extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_skills';
    protected $fillable = [
        'id', 'job_id', 'skill_id'
    ];
}
