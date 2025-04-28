<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PaymentVoucherDetails extends Model
{
    use HasFactory;
    protected $table = 'erp_payment_voucher_details';
    protected $fillable = ['party_type', 'party_id'];

    public function party()
    {
        return $this->morphTo();
    }
    public function vendor(){
        return $this->belongsTo(Vendor::class, 'party_id');
    }

    public function voucher(){
        return $this->belongsTo(PaymentVoucher::class, 'payment_voucher_id');
    }
    
    public function partyName(){
        
            return $this->morphTo(__FUNCTION__, 'party_type', 'party_id');
        }
        public function ledger(){
            return $this->belongsTo(Ledger::class, 'ledger_id');
        }
        public function ledger_group()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }

        public function invoice()
        {
            return $this->hasMany(VoucherReference::class,'voucher_details_id');
        }
    
}
