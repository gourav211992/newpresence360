<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefectNotification extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable, FileUploadTrait;

    protected $table = 'erp_defect_notifications';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'document_number',
        'document_date',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_status',
        'approval_level',
        'revision_number',
        'revision_date',
        'equipment_id',
        'location_id',
        'category_id',
        'defect_type_id',
        'problem',
        'priority',
        'report_date_time',
        'attachment',
        'detailed_oberservation',
        'upload_document',
        'final_remarks',
        'reference_number',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'document_date' => 'date',
        'revision_date' => 'date',
        'report_date_time' => 'datetime',
    ];

    protected $dates = [
        'document_date',
        'revision_date',
        'report_date_time',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function equipment()
    {
        return $this->belongsTo(ErpEquipment::class, 'equipment_id');
    }

    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function defectType()
    {
        return $this->belongsTo(ErpDefectType::class, 'defect_type_id');
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
