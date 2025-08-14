<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialSetup extends Model
{
    // Specify the table associated with the model
    protected $table = 'erp_financial_setup';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'name',
        'ledger_id',
        'ledger_group_id'
    ];

    // If you have timestamps (created_at, updated_at) columns
    public $timestamps = true;

}

