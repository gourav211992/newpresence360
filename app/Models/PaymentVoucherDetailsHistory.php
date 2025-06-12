<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucherDetailsHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_payment_voucher_details_history';
    protected $fillable = ['party_type', 'party_id'];

    public function party()
    {
        return $this->morphTo();
    }
    public function voucher()
    {
        return $this->belongsTo(PaymentVoucherHistory::class, 'payment_voucher_id');
    }
    public function partyName()
    {

        return $this->morphTo(__FUNCTION__, 'party_type', 'party_id');
    }
    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id')->where('status', 1);
    }
    public function ledger_group()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }

    public function invoice()
    {
        return $this->hasMany(VoucherReferenceHistory::class, 'voucher_details_id');
    }
}
