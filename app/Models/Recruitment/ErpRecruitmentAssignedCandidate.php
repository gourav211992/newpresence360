<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentAssignedCandidate extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_assigned_candidates';
    protected $fillable = [
        'id', 'candidate_id', 'job_id','status','remark','created_by','created_by_type'
    ];
}
