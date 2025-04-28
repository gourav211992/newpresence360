<?php

namespace App\Models;

use App\Models\Employee;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory,DateFormatTrait,FileUploadTrait;

    public $table = 'erp_document_approvals';

    public function user()
    {
        // if ($this->user_type == 'employee') {
        //     return $this->belongsTo(Employee::class, 'user_id');
        // }
        
        // if ($this->user_type == 'user') {
        //     return $this->belongsTo(User::class, 'user_id');
        // }

        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function media()
    {
        return $this->morphMany(DocumentApprovalMedia::class, 'model');
    }

    public function document()
    {
        if (is_null($this->document_name)) {
            return null;
        }
        return $this->morphTo(null, 'document_name', 'document_id');
    }

}
