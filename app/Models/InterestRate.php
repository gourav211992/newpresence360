<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterestRate extends Model
{
    protected $table = 'erp_interest_rates';

    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'base_rate',
        'effective_from'
    ];
}
