<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobNotification extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_notifications';

    protected $fillable = [
        'id', 'job_id', 'email', 'status'
    ];
}
