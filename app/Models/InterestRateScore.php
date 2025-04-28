<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterestRateScore extends Model
{
    protected $table = 'erp_interest_rate_scores';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];
}
