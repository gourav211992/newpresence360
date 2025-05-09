<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


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
        'fy_close' => 'boolean',
        'fy_lock' => 'boolean'
    ];

    public function authorizedUsers()
    {
        $access = collect($this->access_by);

        $allAuthorized = $access->every(fn($item) => $item['authorized'] === true);

        $userIds = $access
            ->where('authorized', true)
            ->pluck('user_id')
            ->toArray();

        return [
            'users' => AuthUser::whereIn('id', $userIds)->get(),
            'all' => $allAuthorized ?? true
        ];
    }

    protected static function booted()
    {
        static::creating(function ($financialYear) {
            if (Auth::check()) {
                $financialYear->created_by = Auth::id();
            }
        });

        static::updating(function ($financialYear) {
            if (Auth::check()) {
                $financialYear->updated_by = Auth::id();
            }
        });
    }
}
