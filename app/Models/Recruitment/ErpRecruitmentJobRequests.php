<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use App\Models\ErpStore;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobRequests extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_requests';
    protected $append = ['creator_name','education_name','Job_title_name','work_experience_name','placed_location_name','certification_name','approvar_name','action_by_name'];

    protected static function booted()
    {
        static::created(function ($jobRequest) {
            $orgCode = $jobRequest->organization->alias ? ucwords(str_replace('-','_',$jobRequest->organization->alias)) : 'ORG';
            $jobRequest->request_id = $orgCode . '_' . $jobRequest->id . '_REQUEST';
            $jobRequest->save();
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

    public function workExperience()
    {
        return $this->belongsTo(ErpRecruitmentWorkExperience::class, 'work_exp_id');
    }

    public function getWorkExperienceNameAttribute()
    {
        return optional($this->workExperience)->name;
    }


    public function placedLocation()
    {
        return $this->belongsTo(Organization::class, 'location_id');
    }

    public function getPlacedLocationNameAttribute()
    {
        return optional($this->placedLocation)->name;
    }


    public function certification()
    {
        return $this->belongsTo(ErpRecruitmentCertification::class, 'certification_id');
    }

    public function getCertificationNameAttribute()
    {
        return optional($this->certification)->name;
    }

    public function approvalAuthority(){
        return $this->belongsTo(Employee::class, 'approval_authority');
    }

    public function getApprovarNameAttribute()
    {
        return optional($this->approvalAuthority)->name;
    }

    public function recruitmentSkills()
    {
        return $this->hasManyThrough(
            ErpRecruitmentSkill::class,                   // Final model
            ErpRecruitmentJobRequestSkill::class,         // Intermediate model
            'job_request_id',                             // Foreign key on intermediate table
            'id',                                          // Foreign key on final model (ErpRecruitmentSkill)
            'id',                                          // Local key on this model
            'skill_id'                                     // Local key on intermediate that matches skill
        );
    }

    public function recruitmentCertifications()
    {
        return $this->hasManyThrough(
            ErpRecruitmentCertification::class,                   // Final model
            ErpRecruitmentJobRequestCertification::class,         // Intermediate model
            'job_request_id',                             // Foreign key on intermediate table
            'id',                                          // Foreign key on final model (ErpRecruitmentCertification)
            'id',                                          // Local key on this model
            'certification_id'                                     // Local key on intermediate that matches skill
        );
    }

    public function actionByAdmin(){
        return $this->belongsTo(User::class, 'action_by');
    }

	public function actionByEmployee(){
        return $this->belongsTo(Employee::class, 'action_by');
    }

    public function job(){
        return $this->belongsTo(ErpRecruitmentJob::class, 'job_id','job_id');
    }

    public function getActionByNameAttribute()
    {
        if ($this->action_by_type === 'employee') {
            return optional($this->actionByEmployee)->name;
        }

        return optional($this->actionByAdmin)->name;
    }
}
