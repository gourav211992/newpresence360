<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Organization;
use App\Models\Employee;
use App\Models\CRM\ErpDiary;

class ErpDiaryTagPeople  extends Model
{

    protected $fillable = [
        'diary_id',
        'tag_people_id',
        'organization_id',
    ];

    public function diary()
    {
        return $this->belongsTo(ErpDiary::class);
    }


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
