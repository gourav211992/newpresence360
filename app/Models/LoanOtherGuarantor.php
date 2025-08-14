<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanOtherGuarantor extends Model
{
    protected $table = 'erp_loan_other_guarantors';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }
}
