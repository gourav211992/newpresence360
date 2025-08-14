<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanGuarantorPartyAddress extends Model
{
    protected $table = 'erp_loan_guarantor_party_addresses';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(LoanGuarantorPartyAddress::class, 'vehicle_id');
    }
}
