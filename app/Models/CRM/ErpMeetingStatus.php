<?php

namespace App\Models\CRM;

use App\Models\ErpCustomer;
use Illuminate\Database\Eloquent\Model;
use App\Models\Organization;

class ErpMeetingStatus  extends Model
{
    protected $table = 'erp_meeting_status';
    protected $fillable = [
        'title',
        'alias',
        'status',
        'organization_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    

    public function customers()
    {
        return $this->hasMany(ErpCustomer::class,'lead_status','alias');
    }

    public function diaries()
    {
        return $this->hasMany(ErpDiary::class,'meeting_status_id','id');
    }
    
}
