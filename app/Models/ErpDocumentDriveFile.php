<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDocumentDriveFile extends Model
{
    use HasFactory, SoftDeletes,Deletable;

    protected $table = 'erp_document_drive_files';

    protected $fillable = [
        'name',
        'folder_id',
        'path',
        'organization_id',
        'created_by',
        'created_by_type',
        'size',
        'mime_type',
        'tags',
    ];

    // Relationships
    public function folder()
    {
        return $this->belongsTo(ErpDocumentDriveFolder::class, 'folder_id');
    }
    public function owner()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
    

    public function creator()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function sharedResources()
    {
        return $this->morphMany(ErpDocumentDriveSharedResource::class, 'entity');
    }
}
