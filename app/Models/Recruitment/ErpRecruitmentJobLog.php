<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobLog extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_logs';
    protected $append = ['action_by_name','candidate_name'];

    public function actionByAdmin(){
        return $this->belongsTo(User::class, 'action_by');
    }

	public function actionByEmployee(){
        return $this->belongsTo(Employee::class, 'action_by');
    }

    public function getActionByNameAttribute()
    {
        if ($this->action_by_type === 'employee') {
            return optional($this->actionByEmployee)->name;
        }

        return optional($this->actionByAdmin)->name;
    }

    public function candidate(){
        return $this->belongsTo(ErpRecruitmentJobCandidate::class, 'candidate_id');
    }

    public function interview(){
        return $this->belongsTo(ErpRecruitmentJobInterview::class, 'interview_id');
    }

    public function job(){
        return $this->belongsTo(ErpRecruitmentJob::class, 'job_id');
    }

    public function getCandidateNameAttribute()
    {
        return optional($this->candidate)->name;
    }

    public function panels(){
        return $this->hasManyThrough(
            Employee::class,                   
            ErpRecruitmentJobPanelAllocation::class,         
            'job_id',                             
            'id',                                          
            'job_id',                                         
            'panel_id'                                 
        );
    }
}
