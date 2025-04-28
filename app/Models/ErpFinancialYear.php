<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpFinancialYear extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;
    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'alias',
        'start_date',
        'end_date',
        'status'
    ];
}
