<?php

namespace App\Models\CRM;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class ErpCustomerAchievementSummary  extends Model
{

    protected $fillable = [
        'organization_id',
        'customer_id',
        'target',
        'achievement',
        'month',
        'year'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
