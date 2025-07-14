<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ErpFinancialYear;

class ErpFyMonth extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table='erp_fy_months';
    protected $fillable = [
        'fy_id',
        'start_date',
        'end_date',
        'fy_month',
        'lock_fy',
        'access_by',
        'organization_id',
        'company_id',
        'group_id',
    ];

     protected $casts = [
        'access_by' => 'array',
        'lock_fy' => 'boolean'
    ];

    public function financialYear()
    {
        return $this->belongsTo(ErpFinancialYear::class, 'fy_id');
    }

    public function authorizedUsers()
    {
        $access = collect($this->access_by);
        if (empty($this->access_by)) {
            return null;
        }

        if (empty($this->access_by)) {
            return null;
        }

        $allAuthorized = $access->every(fn($item) => $item['authorized'] == true);
        $allLocked = $access->every(fn($item) => isset($item['locked']) && $item['locked'] == true);

        $userIds = $access
            ->where('authorized', true)
            ->pluck('user_id')
            ->toArray();

        $access_by = [
            'users' => AuthUser::whereIn('id', $userIds)->get(),
            'all' => $allAuthorized,
            'locked' => $allLocked
        ];
        return $access_by;
    }

    protected static function booted()
    {
        static::creating(function ($financialYear)
        {
            if (Auth::check())
            {
                $financialYear->created_by = Auth::id();
            }
        });

        static::updating(function ($financialYear)
        {
            if (Auth::check())
            {
                $financialYear->updated_by = Auth::id();
            }
        });
    }
}
