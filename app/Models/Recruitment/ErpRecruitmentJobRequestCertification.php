<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobRequestCertification extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_request_certifications';
    
    protected $fillable = [
        'id', 'job_request_id', 'certification_id'
    ];
}
