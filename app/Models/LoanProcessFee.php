<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanProcessFee extends Model
{
    protected $table = 'erp_loan_process_fee';
    protected $guarded = ['id'];
    use HasFactory;
    use SoftDeletes;
}
