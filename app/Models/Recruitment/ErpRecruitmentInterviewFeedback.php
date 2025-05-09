<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentInterviewFeedback extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_interview_feedbacks';
    protected $fillable = [
        'job_id',
        'interview_id',
        'candidate_id',
        'panel_id',
        'round_id',
        'rating',
        'behavior',
        'skills',
        'status',
        'remarks',
    ];
    
    protected $append = ['panel_name','round_name'];


    public function round(){
        return $this->belongsTo(ErpRecruitmentRound::class, 'round_id');
    }

    public function getRoundNameAttribute()
    {
        return optional($this->round)->name;
    }

    public function panel(){
        return $this->belongsTo(Employee::class, 'panel_id');
    }

    public function getPanelNameAttribute()
    {
        return optional($this->panel)->name;
    }

    public function job(){
        return $this->belongsTo(ErpRecruitmentJob::class, 'job_id');
    }

    public function candidate(){
        return $this->belongsTo(ErpRecruitmentJobCandidate::class, 'candidate_id');
    }
}
