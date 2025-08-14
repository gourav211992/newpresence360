<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentSkill extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_skills';

    protected $fillable = [
        'id', 'name', 'organization_id', 'status', 'created_by', 'created_by_type'
    ];

}
