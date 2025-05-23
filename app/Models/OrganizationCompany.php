<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationCompany extends Model
{
    use HasFactory,DefaultGroupCompanyOrg;

    public function organizations()
    {
        return $this->hasMany(Organization::class,'company_id');
    }
    public function currency() {
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
