<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobRequestLog extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_request_log';
    protected $append = ['action_by_name'];

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

    public function request(){
        return $this->belongsTo(ErpRecruitmentJobRequests::class, 'job_request_id');
    }
}
