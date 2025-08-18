<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\UserStampTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ErpPickupSchedule extends Model
{
     use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DynamicFieldsTrait, DateFormatTrait, UserStampTrait;
    protected $TABLE = 'erp_pickup_schedules';
    protected $fillable = [
        'organization_id',
        'group_id',
        'rgr_id',
        'company_id',
        'book_id',
        'book_code',
        'store_id',
        'store_code',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_number',
        'document_date',
        'due_date',
        'document_status',
        'revision_number',
        'revision_date',
        'approval_level',
        'reference_number',
        'trip_no',
        'vehicle_no',
        'champ',
        'total_item_count',
        'instructions',
        'remark',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
