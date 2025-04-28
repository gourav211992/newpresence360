<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpLoanAppraisalDpr extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_appraisal_id',
        'dpr_template_id',
        'dpr_id',
        'dpr_value',
    ];

    public function loanAppraisal()
    {
        return $this->belongsTo(ErpLoanAppraisal::class, 'loan_appraisal_id');
    }

    public function template()
    {
        return $this->belongsTo(ErpDprTemplateMaster::class, 'dpr_template_id');
    }

    public function dpr()
    {
        return $this->belongsTo(ErpDprMaster::class, 'dpr_id');
    }
}
