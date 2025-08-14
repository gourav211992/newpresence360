<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDocumentDriveSharedResource extends Model
{
    use HasFactory,SoftDeletes,Deletable;

    protected $table = 'erp_document_drive_shared_resources';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'file_id',
        'folder_id',
        'shared_with',
        'permissions',
        'shared_by',

    ];

    // Relationships
    public function sharedEntity()
    {
        return $this->morphTo(null, 'entity_type', 'entity_id');
    }

    public function sharedBy()
    {
        return $this->belongsTo(AuthUser::class, 'shared_by');
    }

    public function sharedWith()
    {
        return $this->belongsTo(AuthUser::class, 'shared_with_id');
    }
}
