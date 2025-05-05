<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDetail extends Model
{
    protected $table = 'erp_item_details';

    use HasFactory;
    protected $guarded = [];
    protected $appends = [
        'ledger_code',
        'ledger_name',
        'ledger_group_code'
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class,'voucher_id','id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class,'ledger_id')->where('status', 1);
    }
    public function ledger_group()
    {
        return $this->belongsTo(Group::class, 'ledger_parent_id');
    }
    

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
    public function getLedgerGroupCodeAttribute()
    {
        return optional($this -> ledger_group() -> first()) -> name;
    }
    public function getLedgerNameAttribute()
    {
        return optional($this->ledger()->first())->name;
    }
    public function getLedgerCodeAttribute()
{
    return optional($this->ledger()->first())->code;
}
    public function reference(){
        return $this->hasMany(VoucherReference::class,'voucher_id','voucher_id');
    }



}
