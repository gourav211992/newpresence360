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
}
