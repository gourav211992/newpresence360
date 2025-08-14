<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanReportScheduler extends Model
{
    use HasFactory;

    public $table = 'erp_loan_report_schedulers';

    protected $guarded = [];


    public function to()
    {
        return $this->morphTo();
    }
}
