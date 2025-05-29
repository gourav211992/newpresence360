<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;
use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\DynamicFieldsTrait;
use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    protected $table = 'erp_packing_lists';
    use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'store_id',
        'sub_store_id',
        'document_number',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_date',
        'document_status',
        'approval_level',
        'remarks'
    ];

    public function details()
    {
        return $this -> hasMany(PackingListDetail::class, 'plist_id');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
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
    public function media()
    {
        return $this->morphMany(PackingListMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(PackingListMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
}
