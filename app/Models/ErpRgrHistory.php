<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\FileUploadTrait;
use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRgrHistory extends Model
{
     use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, SoftDeletes;

    protected $table = 'erp_rgrs_history';

    protected $fillable = [
        'source_id',
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'pickup_schdule_id',
        'book_code',
        'store_id',
        'store_name',
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
        'champ_name',
        'pickup_schdule_no',
        'remark',
        'final_remark',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'due_date'      => 'date',
        'revision_date' => 'date',
    ];

    // 🔗 Relationships

    public function source()
    {
        return $this->belongsTo(ErpRgr::class, 'source_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

     public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
    
    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function pickupSchedule()
    {
        return $this->belongsTo(ErpPickupSchedule::class, 'pickup_schdule_id');
    }
    public function items()
    {
        return $this->hasMany(ErpRgrItemHistory::class, 'rgr_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpRgrItemAttributeHistory::class, 'rgr_id');
    }

   public function media()
    {
        return $this->morphMany(ErpRgrMedia::class, 'model');
    }

    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class, 'created_by','id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(AuthUser::class, 'updated_by','id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(AuthUser::class, 'deleted_by','id');
    }
}
