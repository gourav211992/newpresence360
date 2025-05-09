<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentCertification extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_certifications';

    protected $fillable = [
        'id', 'name', 'organization_id', 'status', 'created_by', 'created_by_type'
    ];
}
