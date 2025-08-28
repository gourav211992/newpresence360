<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class BankDetail extends Model
{
    use HasFactory,SoftDeletes,Deletable;

    protected $table = 'erp_bank_details'; 

    protected $fillable = [
        'bank_id',
        'account_number',
        'branch_name',
        'branch_address',
        'ifsc_code',
        'ledger_id',
        'ledger_group_id',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function address()
    {
        return $this->belongsTo(ErpAddress::class, 'branch_address_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class); 
    }

    public function bankInfo()
    {
        return $this->belongsTo(Bank::class,'bank_id','id');
    }
    
}
