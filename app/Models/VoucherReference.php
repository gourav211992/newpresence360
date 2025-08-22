<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherReference extends Model
{
    use HasFactory;
    protected $table = 'erp_voucher_references';

    protected $fillable = [
        'payment_voucher_id',
        'voucher_details_id',
        'party_id',
        'voucher_id',
        'amount'
    ];

    public function voucher(){
        return $this->belongsTo(Voucher::class,'voucher_id');
    }

    public function voucherDetail()
    {
        return $this->belongsTo(PaymentVoucherDetails::class,'voucher_details_id');
    }
    public function voucherPayRec(){
        return $this->belongsTo(PaymentVoucher::class,'payment_voucher_id');
    

    } 
    public function ledger(){
        return $this->belongsTo(Ledger::class, 'party_id');
    

    } 


    
     
}
