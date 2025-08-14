<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrDrReportScheduler extends Model
{
    use HasFactory;

    public $table = 'erp_crdr_report_schedulers';

    protected $guarded = [];


    public function to()
    {
        return $this->morphTo('toable', 'toable_type', 'toable_id');
    }
}
