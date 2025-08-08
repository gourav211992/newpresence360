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

class ErpRfqHeader extends Model
{
     use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DynamicFieldsTrait, DateFormatTrait, UserStampTrait;

    protected $table = "erp_rfq_headers";
    protected $fillable = [
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
        'contact_name',
        'contact_email',
        'contact_phone',
        'suppliers',
        'selected_supplier',
        'selected_pq',
        'remark'
    ];

    protected $hidden = ['deleted_at'];

    
    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function vendors()
    {
        return Vendor::whereIn('id', json_decode($this->suppliers) ?? [])->get();
    }
    public function pqs()
    {
        return $this->hasMany(ErpPqHeader::class, 'rfq_id');
    }

    public function sub_store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function org_currency()
    {
        return $this->belongsTo(Currency::class, 'org_currency_id');
    }

    public function comp_currency()
    {
        return $this->belongsTo(Currency::class, 'comp_currency_id');
    }

    public function group_currency()
    {
        return $this->belongsTo(Currency::class, 'group_currency_id');
    }

    public function items()
    {
        return $this->hasMany(ErpRfqItem::class, 'rfq_header_id');
    }
    public function media()
    {
        return $this->morphMany(ErpRfqMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpRfqMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'location');
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
        return $this -> hasMany(ErpRfqDynamicField::class, 'header_id');
    }
    
}
