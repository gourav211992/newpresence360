<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadInspectionChecklist extends Model
{
    use HasFactory;

    protected $table = 'upload_inspection_checklists';

    protected $fillable = [
        // Fields from erp_inspection_checklists
        'group_id',
        'company_id',
        'organization_id',
        'name',
        'description',
        'status',

        // Fields from erp_inspection_checklist_details
        'detail_name',
        'data_type',
        'detail_description',
        'mandatory',

        // Fields from erp_inspection_checklist_detail_values
        'values',
        'batch_no',
        'user_id',
        'remarks',
    ];

    protected $casts = [
        'values' => 'array', 
        'mandatory' => 'boolean',
    ];
}
