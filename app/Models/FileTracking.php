<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileTracking extends Model
{
    use HasFactory, DefaultGroupCompanyOrg,Deletable;

    protected $table = 'erp_file_tracking';

    protected $guarded = ['id'];

    protected $casts = [
        'document_date' => 'date',
        'expected_completion_date' => 'date',
        'signed_by' => 'array',
    ];
    public function teams()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }

}
