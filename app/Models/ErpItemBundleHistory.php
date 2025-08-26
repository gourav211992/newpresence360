<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpItemBundlesHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_item_bundles_history';

    protected $fillable = [
        'source_id',
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
        'deleted_by',
    ];
}
