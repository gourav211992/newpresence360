<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use App\Models\ErpStore;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobCandidate extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_candidates';
    protected $append = ['creator_name','education_name','location_name'];

    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id');
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

    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }

    public function getLocationNameAttribute()
    {
        return optional($this->location)->store_name;
    }


    public function candidateSkills()
    {
        return $this->hasManyThrough(
            ErpRecruitmentSkill::class,           
            ErpRecruitmentJobCandidateSkill::class,        
            'candidate_id',                             
            'id',                                         
            'id',                                         
            'skill_id'                                    
        );
    }

    public function jobDetail()
    {
        return $this->hasOneThrough(
            ErpRecruitmentJob::class,                 
            ErpRecruitmentAssignedCandidate::class,   
            'candidate_id',                           
            'id',                                     
            'id',                                     
            'job_id'                                  
        );
    }

    public function assignedJob(){
        return $this->hasOne(ErpRecruitmentAssignedCandidate::class,'candidate_id');
    }

    public function scheduledInterview(){
        return $this->hasOne(ErpRecruitmentJobInterview::class,'candidate_id');
    }

    public function referalDetail(){
        return $this->hasOne(ErpRecruitmentJobReferral::class,'candidate_id');
    }
}
