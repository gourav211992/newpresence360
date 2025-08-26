<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ErpItemBundle extends Model
{
    use SoftDeletes;

    protected $table = 'erp_item_bundles';

    protected $fillable = [
        'sku_code',
        'sku_name',
        'sku_initial',
        'front_sku_code',
        'code_type',
        'book_id',
        'category_id', 
        'group_id',
        'company_id',
        'organization_id',
        'status',
        'document_status',
        'doc_no',
        'approver_level',
        'revision_number',
        'revision_date',
        'upload_document',
        'final_remarks',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'revision_date' => 'datetime',
    ];

    /** Relationships */

    public function bundleItems()
    {
        return $this->hasMany(ErpBundleItemDetail::class, 'bundle_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpBundleItemAttribute::class, 'bundle_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id'); 
    }

    protected function generateFileUrl($filePath)
    {
        return $filePath ? Storage::url($filePath) : null;
    }

    public function getUploadDocumentUrl()
    {
        return $this->generateFileUrl($this->upload_document);
    }

    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
}
