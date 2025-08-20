<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class DefectNotificationHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_defect_notification_histories';

    protected $fillable = [
        'source_id',
        'type',
        'organization_id',
        'group_id',
        'company_id',
        'approval_level',
        'revision_number',
        'revision_date',
        'book_code',
        'book_id',
        'document_number',
        'document_date',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'doc_number_type',
        'doc_reset_pattern',
        'document_status',
        'location_id',
        'equipment_id',
        'category',
        'defect_type',
        'problem',
        'priority',
        'report_date_time',
        'attachment',
        'default_oberservation',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'revision_date' => 'date',
        'report_date_time' => 'datetime',
        'approval_level' => 'integer',
        'revision_number' => 'integer',
        'doc_no' => 'integer',
    ];

    /**
     * Relationship to the original defect notification
     */
    public function defectNotification()
    {
        return $this->belongsTo(DefectNotification::class, 'source_id');
    }

    /**
     * Relationship to the book/series
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Relationship to the location
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Relationship to the equipment
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Relationship to the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to the updater
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship to organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function media()
    {
        return $this->morphMany(DefectNotificationMedia::class, 'model');
    }

    public function media_files()
    {
        return $this->morphMany(DefectNotificationMedia::class, 'model')->select('id', 'model_type', 'model_id', 'file_name');
    }
}
