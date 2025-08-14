<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentRound extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_rounds';

    public function allocateRounds(){
        return $this->hasOne(ErpRecruitmentJobPanelAllocation::class,'round_id');
    }

    public function jobInterview(){
        return $this->hasOne(ErpRecruitmentJobInterview::class,'round_id');
    }
}
