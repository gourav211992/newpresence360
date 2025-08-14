<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;

class Overhead extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;

    protected $table = 'erp_overheads';

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'name',
        'alias',
        'perc',
        'ledger_id',
        'ledger_group_id',
        'is_waste',
        'status'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->created_by = $user->auth_user_id;
            }
        });

        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->auth_user_id;
            }
        });

        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->auth_user_id;
            }
        });
    }

    public function erpLedger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id','id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class,'ledger_group_id','id' ); 
    }
}
