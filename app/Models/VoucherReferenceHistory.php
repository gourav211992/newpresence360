<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherReferenceHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_voucher_reference_histories';

    protected $fillable = [
        'source_id',
        'voucher_details_id',
        'party_id',
        'voucher_id',
        'amount'
    ];

    public function voucherDetail()
    {
        return $this->belongsTo(PaymentVoucherDetailsHistory::class,'voucher_details_id');
    }
    public function voucherPayRec(){
        return $this->belongsTo(PaymentVoucherHistory::class,'payment_voucher_id');
    

    }
}
