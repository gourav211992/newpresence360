<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantMaintWo extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_plant_maint_wo';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'document_number',
        'document_date',
        'location_id',
        'equipment_id',
        'defect_notification_id',
        'maintenance_type',
        'priority',
        'detailed_observations',
        'scheduled_date',
        'completion_date',
        'work_description',
        'work_performed',
        'spare_parts_used',
        'estimated_cost',
        'actual_cost',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'status',
        'document_status',
        'approval_level',
        'revision_number',
        'revision_date',
        'assigned_to',
        'completed_by',
        'completion_notes',
        'remarks',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = [
        'document_date',
        'scheduled_date',
        'completion_date',
        'revision_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function defectNotification()
    {
        return $this->belongsTo(DefectNotification::class, 'defect_notification_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
