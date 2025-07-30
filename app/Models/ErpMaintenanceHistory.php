<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMaintenanceHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_maintenance_histories';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'category_id',
        'equipment_id',
        'doc_date',
        'upload_document',
        'final_remarks',
        'book_id',
        'document_number',
        'document_date',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_status',
        'revision_number',
        'revision_date',
        'reference_number',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];


    public function equipment(): BelongsTo
    {
        return $this->belongsTo(
            related: ErpEquipment::class,
            foreignKey: 'equipment_id',
        );
    }

    public function defectDetails(): HasMany
    {
        return $this->hasMany(
            related: ErpMaintenanceDefectDetail::class,
            foreignKey: 'erp_maintenance_id',
        );
    }

    public function checklistDetails(): HasMany
    {
        return $this->hasMany(
            related: ErpMaintenanceChecklistDetail::class,
            foreignKey: 'erp_maintenance_id',
        );
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(
            related: Book::class,
            foreignKey: 'book_id',
        );
    }
}
