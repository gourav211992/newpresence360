<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Organization;

class ErpMeetingObjective  extends Model
{

    protected $fillable = [
        'title',
        'status',
        'organization_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
