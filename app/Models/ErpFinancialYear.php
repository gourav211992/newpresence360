<?php
namespace App\Models;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ErpFinancialYear extends Model
{

    use HasFactory, DefaultGroupCompanyOrg;

    protected $connection = 'mysql';
    protected $table='erp_financial_years';
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
        if (empty($this->access_by)) {
            return null;
        }

        $access = collect($this->access_by);

        $allAuthorized = $access->every(fn($item) => $item['authorized'] === true);

        $userIds = $access
            ->where('authorized', true)
            ->pluck('user_id')
            ->toArray();

        $users = AuthUser::whereIn('id', $userIds)->get();

        // If all authorized is true and users exist, return null
        if ($allAuthorized && $users->isNotEmpty()) {
            return null;
        }

        // If all authorized is false and users exist, return the array
        if (!$allAuthorized && $users->isNotEmpty()) {
            return [
                'users' => $users,
                'all' => false
            ];
        }

        // For all other cases, return null
        return null;
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
