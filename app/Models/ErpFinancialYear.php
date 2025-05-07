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
        'status',
        'fy_status',
        'access_by',
        'fy_close',
        'fy_lock'
    ];

    protected $casts = [
        'access_by' => 'array',
    ];

    public function authorizedUsers()
    {
        return AuthUser::whereIn('id', $this->access_by ?? []);
    }
}
