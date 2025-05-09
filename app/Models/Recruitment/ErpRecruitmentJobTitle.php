<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobTitle extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_title';

    public function requests(){
        return $this->hasMany(ErpRecruitmentJobRequests::class,'job_title_id');
    }

    public function jobs(){
        return $this->hasMany(ErpRecruitmentJob::class,'job_title_id');
    }

}
