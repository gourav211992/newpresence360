<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmendmentWorkflowUsers extends Model
{
    use HasFactory;

    public $table = 'erp_amendment_workflow_users';

    protected $fillable = [
        'book_id',
        'company_id',
        'organization_id',
        'user_id',
        "user_type",
        'amendment_workflow_id'
    ];

    public function amendment()
    {
        return $this->belongsTo(AmendmentWorkflow::class);
    }
}
