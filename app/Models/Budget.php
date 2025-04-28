<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    // You can specify the table name if it's not the plural of the model name
    protected $table = 'erp_budgets';


    // Specify the fillable attributes
    protected $fillable = [
        'series',
        'documentno',
        'type',
        'unit',
        'companies',
        'branch',
        'ledger',
        'budget',
        'period',
        'details',
        'total_percent',
        'total_value',
        // Add any other fields as necessary
    ];

    // You can define any relationships here if needed
}

