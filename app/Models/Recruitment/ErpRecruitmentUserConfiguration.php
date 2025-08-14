<?php

namespace App\Models\Recruitment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRecruitmentUserConfiguration extends Model
{
    use HasFactory;
    protected $table = 'erp_recruitment_user_configuration';
    protected $append = [
                            'id',	
                            'user_id',	
                            'user_type',	
                            'current_opening',	
                            'interview_summary',	
                            'my_scheduled',	
                            'activity',	
                            'new_applicants',	
                            'created_at',	
                            'updated_at'
                        ];
}
