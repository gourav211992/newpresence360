<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'application_number',
        'loan_application_id',
        'organization_id',
        'module_type',
        'description',
        'created_by',
        'document',
        'user_type',
        'revision_number',
        'revision_date',
        'active_status'
    ];

    public function employee()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }
}
