<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDocumentDriveActionLog extends Model
{
    use HasFactory,SoftDeletes,Deletable;

    protected $table = 'erp_document_drive_action_logs';

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'performed_by',
        'timestamp',
    ];

    public $timestamps = false; // Timestamp managed manually

    // Relationships
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
