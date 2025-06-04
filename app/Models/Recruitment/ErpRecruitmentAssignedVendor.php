<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentAssignedVendor extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_assigned_vendors';
    protected $fillable = [
        'id', 'vendor_id', 'job_id','remark','created_by','created_by_type'
    ];
}
