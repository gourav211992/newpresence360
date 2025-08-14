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

class ErpPqcHeader extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DynamicFieldsTrait, DateFormatTrait, UserStampTrait;

    protected $fillable = [
        'rfq_id',
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'store_id',
        'sub_store_id',
        'store_code',
        'sub_store_code',
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
        'currency_id',
        'currency_code',
        'org_currency_id',
        'org_currency_code',
        'org_currency_exg_rate',
        'comp_currency_id',
        'comp_currency_code',
        'comp_currency_exg_rate',
        'group_currency_id',
        'group_currency_code',
        'group_currency_exg_rate',
        'total_item_count',
        'instructions',
        'selected_pq',
        'selected_vendor',
        'vendor_email',
        'vendor_phone',
        'remark',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function selectedPQ()
    {
        return $this->hasOne(ErpPqHeader::class, 'id', 'selected_pq');
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
    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
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
        return $this -> hasMany(ErpPqDynamicField::class, 'header_id');
    }
    public function media()
    {
        return $this->morphMany(ErpPqMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpPqMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function suppliers()
    {
        return $this->belongsTo(ErpVendor::class , 'selected_vendor');
    }
    public function rfq()
    {
        return $this->belongsTo(ErpRfqHeader::class,'rfq_id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'selected_vendor');
    }
}

