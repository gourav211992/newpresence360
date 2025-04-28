<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanSanctionLetter extends Model
{
    protected $table = 'erp_loan_accept';
    protected $fillable = ['loan_application_id', 'status', 'doc', 'remarks'];

    use HasFactory;
    use SoftDeletes;
}
