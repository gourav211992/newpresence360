<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobReferral extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_referrals';
    protected $fillable = [
        'id', 'candidate_id', 'job_id','status','remarks'
    ];

    public function candidate(){
        return $this->belongsTo(ErpRecruitmentJobCandidate::class, 'candidate_id');
    }

    public function job(){
        return $this->belongsTo(ErpRecruitmentJob::class, 'job_id');
    }
}
