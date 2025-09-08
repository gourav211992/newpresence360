<?php

namespace App\Models\Scrap;

use App\Models\Book;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Traits\UserStampTrait;
use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Models\ErpPslipItem;
use App\Traits\DynamicFieldsTrait;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErpScrap extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'store_id',
        'store_name',
        'sub_store_id',
        'sub_store_name',
        'book_id',
        'book_code',

        'reference_type',
        'document_number',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_date',
        'document_status',
        'approval_level',

        'total_cost',
        'total_qty',

        'revision_number',
        'revision_date',

        'remarks',

        'user_id',
        'user_type',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public $referencingRelationships = [
        "store" => "store_id",
        "subStore" => "sub_store_id",
    ];

    /* -------------------------
     | Accessors
     |-------------------------- */
    public function getFullDocumentNumberAttribute()
    {
        return strtoupper($this->book_code) . '-' . $this->document_number;
    }

    public function getDisplayDocumentNumberAttribute()
    {
        return $this->book_code . ' - ' . $this->document_number;
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

    /* -------------------------
     | Relationships
     |-------------------------- */
    public function items()
    {
        return $this->hasMany(ErpScrapItem::class, 'erp_scrap_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function pslipItems()
    {
        return $this->hasMany(ErpPslipItem::class, 'erp_scrap_id');
    }

    public function roItems()
    {
        return $this->hasMany(ErpPslipItem::class, 'erp_scrap_id');
        // return $this->hasMany(RepairOrder::class, 'scrap_id');
    }

    public function dynamicFields()
    {
        return $this->hasMany(ErpScrapDynamicField::class, 'header_id');
    }

    public function media()
    {
        return $this->morphMany(ErpScrapMedia::class, 'model');
    }

    public function media_files()
    {
        return $this->morphMany(ErpScrapMedia::class, 'model')
            ->select('id', 'model_type', 'model_id', 'file_name');
    }

    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
    public function applyReference(string $type): void
    {
        $items = match ($type) {
            'pslip' => $this->pslipItems ?? $this->pslipItems()->get(['id', 'pslip_id']),
            'ro'    => $this->roItems ?? $this->roItems()->get(['id', 'pslip_id']),
            default => collect(),
        };

        $ids      = $items->pluck('pslip_id')->unique()->values()->toArray();
        $itemIds  = $items->pluck('id')->values()->toArray();

        $this->{$type . '_ids'}      = json_encode($ids, JSON_THROW_ON_ERROR);
        $this->{$type . '_item_ids'} = json_encode($itemIds, JSON_THROW_ON_ERROR);
    }
}
