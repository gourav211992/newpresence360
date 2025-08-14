<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApproval extends Model
{
    protected $table = 'erp_loan_approval';
    protected $fillable = ['loan_application_id', 'status', 'doc', 'on_behalf', 'remarks'];

    use HasFactory;
    use SoftDeletes;
}
