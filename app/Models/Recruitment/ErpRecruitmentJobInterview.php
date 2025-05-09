<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobInterview extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_interviews';
    protected $fillable = [
        'organization_id',
        'job_id',
        'candidate_id',
        'round_id',
        'meeting_link',
        'rating',
        'remarks',
        'status',
        'created_by',
        'created_by_type',
        'feedback_by',
        'feedback_by_type',
        'feedback_at',
    ];

    protected $casts = [
        'feedback_at' => 'datetime',
    ];
    
    protected $append = ['creator_name','feedback_by_name','round_name'];

    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function round(){
        return $this->belongsTo(ErpRecruitmentRound::class, 'round_id');
    }

    public function job(){
        return $this->belongsTo(ErpRecruitmentJob::class, 'job_id');
    }

    public function candidate(){
        return $this->belongsTo(ErpRecruitmentJobCandidate::class, 'candidate_id');
    }

    public function getRoundNameAttribute()
    {
        return optional($this->round)->name;
    }

    public function createdByAdmin(){
        return $this->belongsTo(User::class, 'created_by');
    }

	public function createdByEmployee(){
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function getCreatorNameAttribute()
    {
        if ($this->created_by_type === 'employee') {
            return optional($this->createdByEmployee)->name;
        }

        return optional($this->createdByAdmin)->name;
    }

    public function feedbackByAdmin(){
        return $this->belongsTo(User::class, 'feedback_by');
    }

	public function feedbackByEmployee(){
        return $this->belongsTo(Employee::class, 'feedback_by');
    }

    public function getFeedbackByNameAttribute()
    {
        if ($this->feedback_by_type === 'employee') {
            return optional($this->feedbackByEmployee)->name;
        }

        return optional($this->feedbackByAdmin)->name;
    }

    public function interviewFeedback(){
        return $this->hasOne(ErpRecruitmentInterviewFeedback::class, 'interview_id');
    }
}
