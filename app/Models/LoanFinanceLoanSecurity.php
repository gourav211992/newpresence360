<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanFinanceLoanSecurity extends Model
{
    protected $table = 'erp_loan_finance_loan_securities';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(LoanFinanceLoanSecurity::class, 'vehicle_id');
    }
}
