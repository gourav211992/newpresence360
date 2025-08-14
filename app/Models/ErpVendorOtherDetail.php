<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVendorOtherDetail extends Model
{
    use HasFactory;

    protected $table = "erp_vendor_other_detail";

    public function currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'currency_id', 'id');
    }

    public function payment_terms()
    {
        return $this -> belongsTo(ErpPaymentTerm::class, 'payment_terms_id', 'id');
    }
}
