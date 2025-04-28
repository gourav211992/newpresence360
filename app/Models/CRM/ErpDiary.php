<?php

namespace App\Models\CRM;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
use App\Models\ErpCustomer;
use App\Models\ErpDiaryAttachment;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

class ErpDiary  extends Model
{
    use HasFactory,SoftDeletes,Deletable;

    protected $fillable = [
        'customer_type',
        'customer_id',
        'customer_name',
        'customer_code',
        'contact_person',
        'organization_id',
        'sales_figure',
        'email',
        'location',
        'subject',
        'description',
        'document_path',
        'created_by_type',
        'created_by',
        'sales_figure',
        'industry_id',
    ];

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class);
    }

    public function tagPeople()
    {
        return $this->belongsToMany(Employee::class, 'erp_diary_tag_people', 'diary_id', 'tag_people_id')
            ->select('employees.id', 'employees.name', 'employees.email');
    }


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function attachments()
    {
        return $this->hasMany(ErpDiaryAttachment::class);
    }

    public function createdByEmployee()
    {
        return $this->belongsTo(Employee::class,'created_by','id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function meetingStatus()
    {
        return $this->belongsTo(ErpMeetingStatus::class);
    }

    public function industry()
    {
        return $this->belongsTo(ErpIndustry::class);
    }
}
