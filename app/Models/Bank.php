<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class Bank extends Model
{
    use HasFactory, SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_banks'; 

    protected $fillable = [
        'bank_name',
        'bank_code',
        'company_id',
        'organization_id',
        'ledger_id',
        'ledger_group_id',
        'group_id',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(BankDetail::class);
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }
    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class); 
    }
}
