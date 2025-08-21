<?php

namespace App\Models\Kaizen;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpKaizen extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'department_id',
        'kaizen_no',
        'kaizen_date',
        'problem',
        'counter_measure',
        'benefits',
        'productivity_imp_id',
        'quality_imp_id',
        'moral_imp_id',
        'delivery_imp_id',
        'cost_imp_id',
        'cost_saving_amt',
        'innovation_imp_id',
        'safety_imp_id',
        'status',
        'approver_id',
        'approved_at',
        'created_by',
        'updated_by',
        'occurence',
        'remarks',
        'score',
        'total_score',
    ];

    public function kaizenTeam()
    {
        return $this->hasManyThrough(
            Employee::class,           
            ErpKaizenTeam::class,        
            'kaizen_id',                             
            'id',                                         
            'id',                                         
            'team_id'                                    
        );
    }

    public function approver()
    {
        return $this->belongsTo(
            Employee::class,           
            'approver_id',                             
            'id'                                    
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(
            Employee::class,           
            'created_by',                             
            'id'                                    
        );
    }

    public function department()
    {
        return $this->belongsTo(
            Department::class,           
            'department_id',                             
            'id'                                    
        );
    }

    public function attachments()
    {
        return $this->hasMany(
            ErpKaizenDocument::class,        
            'kaizen_id',                           
            'id'                                  
        );
    }
    
    public function teams()
    {
        return $this->hasMany(
            ErpKaizenTeam::class,        
            'kaizen_id',                           
            'id'                                  
        );
    }

    public function productivity()
    {
        return $this->improvementRelation('productivity_imp_id');
               
    }

    public function safety()
    {    
        return $this->improvementRelation('safety_imp_id');
    }
    

    public function innovation()
    {
               
        return $this->improvementRelation('innovation_imp_id');
    }

    public function cost()
    {
        return $this->improvementRelation('cost_imp_id');
    }

    public function delivery()
    {    
        return $this->improvementRelation('delivery_imp_id');
    }

    public function moral()
    {          
        return $this->improvementRelation('moral_imp_id');
    }

    public function quality()
    {   
        return $this->improvementRelation('quality_imp_id');
    }

    private function improvementRelation($foreignKey)
    {
        return $this->hasOne(
            ErpKaizenImprovement::class,
            'id',
            $foreignKey
        );
    }

    public function getCreatedByDesignationMarksAttribute()
    {
        return $this->createdBy?->designation?->marks;
    }

}
