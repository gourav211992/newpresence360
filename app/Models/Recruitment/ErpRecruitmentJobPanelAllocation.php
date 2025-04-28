<?php

namespace App\Models\Recruitment;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentJobPanelAllocation extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_job_panel_allocations';
    
    protected $fillable = [
        'id', 'job_id', 'panel_id', 'external_email','round_id'
    ];

    public function panel()
    {
        return $this->belongsTo(Employee::class, 'panel_id');
    }

    public function round()
    {
        return $this->belongsTo(ErpRecruitmentRound::class, 'round_id');
    }
}
