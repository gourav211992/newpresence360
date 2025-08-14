<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobRequestSkill extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_request_skills';
    
    protected $fillable = [
        'id', 'job_request_id', 'skill_id'
    ];
}
