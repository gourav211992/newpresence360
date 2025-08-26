<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;

use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpProductionSlip extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, DateFormatTrait, UserStampTrait,DynamicFieldsTrait,FileUploadTrait;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'bom_id',
        'book_id',
        'book_code',
        'document_number',
        'lot_number',
        'manufacturing_year',
        'expiry_date',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_date',
        'revision_number',
        'revision_date',
        'reference_number',
        'store_id',
        'store_code',
        'fg_sub_store_id',
        'rg_sub_store_id',
        'document_status',
        'approval_level',
        'remarks',
        'org_currency_id',
        'org_currency_code',
        'org_currency_exg_rate',
        'comp_currency_id',
        'comp_currency_code',
        'comp_currency_exg_rate',
        'group_currency_id',
        'group_currency_code',
        'group_currency_exg_rate',
        'shift_id',
        'sub_store_id',
        'mo_id',
        'is_last_station',
        'station_id'
    ];


    protected $hidden = ['deleted_at'];

    public $referencingRelationships = [
        'book' => 'book_id',
        'store' => 'store_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
        'group_currency' => 'group_currency_id',
    ];

    protected $appends = [
        'currency_code',
        'display_status'
    ];

    public function media_files()
    {
        return $this->morphMany(ErpPslipMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function mo()
    {
        return $this -> belongsTo(MfgOrder::class, 'mo_id');
    }
    public function items()
    {
        return $this -> hasMany(ErpPslipItem::class, 'pslip_id');
    }
    public function pslip_items()
    {
        return $this -> hasMany(ErpPslipItem::class, 'pslip_id');
    }
    public function last_so()
    {
        $pslipItem = $this->pslip_items?->count() ? $this->pslip_items()->whereNotNull('so_id')->first() : null;
        if($pslipItem) {
            return $pslipItem?->so;
        }
        return null;
    }
    public function shift()
    {
        return $this -> belongsTo(Shift::class, 'shift_id');
    }
    public function station()
    {
        return $this -> belongsTo(Station::class, 'station_id');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function consumptions()
    {
        return $this -> hasMany(PslipBomConsumption::class, 'pslip_id');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function sub_fg_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'fg_sub_store_id');
    }
    public function sub_rg_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'rg_sub_store_id');
    }
    public function currency()
    {
        return $this -> hasOne(ErpCurrency::class, 'id', 'org_currency_id');
    }
    public function org_currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'org_currency_id');
    }
    public function comp_currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'comp_currency_id');
    }
    public function group_currency()
    {
        return $this -> belongsTo(ErpCurrency::class, 'group_currency_id');
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

    public function getCurrencyCodeAttribute()
    {
        return $this -> org_currency() ?-> first() ?-> short_name;
    }
       public function dynamic_fields()
    {
        return $this -> hasMany(ErpPslipDynamicField::class, 'header_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }

    public function media()
    {
        return $this->morphMany(DocumentApprovalMedia::class, 'model');
    }
}
