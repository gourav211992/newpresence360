<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowScheduler extends Model
{
    use HasFactory;

    public $table = 'erp_cashflow_schedulers';

    protected $guarded = [];


    public function to()
    {
        return $this->belongsTo(AuthUser::class, 'toable_id');
    }
}
