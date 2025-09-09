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

class ErpTripPlanHeader extends Model
{
  use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, DynamicFieldsTrait, UserStampTrait;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'store_id',
        'store_code',
        'main_sub_store_id',
        'main_sub_store_code',
        'staging_sub_store_id',
        'staging_sub_store_code',
        'enforce_uic_scanning',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_number',
        'document_date',
        'document_status',
        'revision_number',
        'revision_date',
        'approval_level',
        'reference_number',
        'transport_mode_id',
        'transport_mode',
        'vehicle_number',
        'transporter_name',
        'challan_number',
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
        'total_verified_count',
        'total_discrepancy_count',
        'remark',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function media()
    {
        return $this->morphMany(ErpTripPlanMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpTripPlanMedia::class, 'model')->select('id', 'model_type', 'model_id', 'file_name');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function currency()
    {
        return $this->hasOne(ErpCurrency::class, 'id', 'currency_id');
    }
    public function items()
    {
        return $this->hasMany(ErpTripPlanDetail::class, 'trip_header_id');
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
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }
    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpTripDynamicField::class, 'header_id');
    }
    public function main_sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'main_sub_store_id');
    }
    public function staging_sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'staging_sub_store_id');
    }
}
