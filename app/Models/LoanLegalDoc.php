<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanLegalDoc extends Model
{
    protected $table = 'erp_legal_doc';
    protected $fillable = ['loan_application_id', 'status', 'doc', 'remarks'];

    use HasFactory;
    use SoftDeletes;
}
