<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDocumentDriveFolder extends Model
{
    use HasFactory, SoftDeletes,Deletable;

    protected $table = 'erp_document_drive_folders';

    protected $fillable = [
        'name',
        'parent_id',
        'organization_id',
        'created_by',
        'created_by_type',
        'tags',
        'status',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function owner()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function creator()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(ErpDocumentDriveFile::class, 'folder_id');
    }
    public function sharedResources()
    {
        return $this->morphMany(ErpDocumentDriveSharedResource::class, 'entity');
    }
}
