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

    public function pickupItems()
    {
        return $this->hasMany(ErpPickupItem::class, 'pickup_schedule_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }
    public function media()
    {
        return $this->morphMany(ErpPickupMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpPickupMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    
    
    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpPickupDynamicField::class, 'header_id');
    }

}
