<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use App\Models\ErpStore;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJob extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job';
    protected $append = ['creator_name','education_name','Job_title_name','location_name', 'industry_name'];

    protected static function booted()
    {
        static::created(function ($job) {
            $orgCode = $job->organization->alias ? ucwords(str_replace('-','_',$job->organization->alias)) : 'ORG';
            $job->job_id = $orgCode . '_' . $job->id . '_JOB';
            $job->save();
        });
    }

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

    public function education()
    {
        return $this->belongsTo(ErpRecruitmentEducation::class, 'education_id');
    }

    public function getEducationNameAttribute()
    {
        return optional($this->education)->name;
    }

    public function jobTitle()
    {
        return $this->belongsTo(ErpRecruitmentJobTitle::class, 'job_title_id');
    }

    public function getJobTitleNameAttribute()
    {
        return optional($this->jobTitle)->title;
    }


    public function location()
    {
        return $this->belongsTo(Organization::class, 'location_id');
    }

    public function getLocationNameAttribute()
    {
        return optional($this->location)->name;
    }

    public function industry()
    {
        return $this->belongsTo(ErpRecruitmentIndustry::class, 'industry_id');
    }

    public function getIndustryNameAttribute()
    {
        return optional($this->industry)->name;
    }

    public function jobSkills()
    {
        return $this->hasManyThrough(
            ErpRecruitmentSkill::class,           
            ErpRecruitmentJobSkill::class,        
            'job_id',                             
            'id',                                         
            'id',                                         
            'skill_id'                                    
        );
    }

    public function panelAllocations()
    {
        return $this->hasMany(ErpRecruitmentJobPanelAllocation::class, 'job_id');
    }

    public function assignedCandidates()
    {
        return $this->hasMany(ErpRecruitmentAssignedCandidate::class, 'job_id');
    }

    public function candidates()
    {
        return $this->hasManyThrough(
            ErpRecruitmentJobCandidate::class,           
            ErpRecruitmentAssignedCandidate::class,        
            'job_id',                             
            'id',                                         
            'id',                                         
            'candidate_id'                                    
        );
    }

    public function jobInterview(){
        return $this->hasMany(ErpRecruitmentJobInterview::class, 'job_id');
    }
    
    public function jobReferral(){
        return $this->hasMany(ErpRecruitmentJobReferral::class, 'job_id');
    }

    public function requests(){
        return $this->hasMany(ErpRecruitmentJobRequests::class, 'job_id','job_id');
    }

    // public function jobReferralCandidate(){
    //     return $this->hasOneThrough(
    //         ErpRecruitmentJobCandidate::class,           
    //         ErpRecruitmentJobReferral::class,        
    //         'job_id',                             
    //         'id',                                         
    //         'id',                                         
    //         'candidate_id'                                    
    //     );
    // }

    public function assignedVendors()
    {
        return $this->hasMany(ErpRecruitmentAssignedVendor::class, 'job_id');
    }
}
