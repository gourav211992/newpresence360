<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucherHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_payment_vouchers_history';

    public function series()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id')->where('status', 1);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function details()
    {
        return $this->hasMany(PaymentVoucherDetailsHistory::class,'payment_voucher_id');
    }

    public function approvals()
    {
        return $this->hasMany(ApprovalProcess::class);
    }
}
