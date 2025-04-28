<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmendmentWorkflow extends Model
{
    use HasFactory;

    public $table = 'erp_amendment_workflows';

    protected $fillable = [
        'book_id',
        'company_id',
        'organization_id',
        "min_value",
        'approval_required',
        "max_value"
    ];

    public function approvers()
    {
        return $this->hasMany(AmendmentWorkflowUsers::class, 'amendment_workflow_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
