<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Models\ErpEquipment;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantMaintWo extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable, FileUploadTrait;

    protected $table = 'erp_plant_maint_wo';

    protected $fillable = [
        'created_by',
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
        'spare_parts',
        'checklist_data',
        'equipment_details',
        'final_remark',
        'upload_file',
        'status',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = [
        'document_date',
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



}
