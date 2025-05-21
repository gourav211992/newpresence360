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

class ErpMaterialReturnHeader extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DynamicFieldsTrait, DateFormatTrait, UserStampTrait;

    protected $table = "erp_material_return_header";

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'return_type',
        'document_type',
        'document_number',
        'document_date',
        'revision_number',
        'prefix',
        'suffix',
        'doc_no',
        'revision_date',
        'reference_number',
        'store_id',
        'store_code',
        'to_store_id',
        'to_store_code',
        'from_sub_store_id',
        'from_sub_store_code',
        'to_sub_store_id',
        'to_sub_store_code',
        'user_id',
        'user_name',
        'department_id',
        'department_code',
        'vendor_id',
        'vendor_code',
        'consignee_name',
        'consignment_no',
        'eway_bill_no',
        'transporter_name',
        'vehicle_no',
        'billing_address',
        'shipping_address',
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
        'total_item_value',
        'total_discount_value',
        'total_tax_value',
        'total_expense_value',
        'total_amount'
    ];

    protected $hidden = ['deleted_at'];

    public $referencingRelationships = [
        'book' => 'book_id',
        'vendor' => 'vendor_id',
        'store' => 'store_id',
        'department' => 'department_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
        'group_currency' => 'group_currency_id',
    ];


    public function media()
    {
        return $this->morphMany(ErpMaterialReturnMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpMaterialReturnMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function vendor()
    {
        return $this -> belongsTo(ErpVendor::class, 'vendor_id');
    }
    public function items()
    {
        return $this -> hasMany(ErpMrItem::class, 'material_return_id');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function toErpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'to_store_id');
    }
    public function fromSubStore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'from_sub_store_id');
    }
    public function toSubStore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'to_sub_store_id');
    }
    public function department()
    {
        return $this -> belongsTo(Department::class, 'department_id');
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
    public function vendor_shipping_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping');
    }
    public function requester_name()
    {
        $firstItem = $this->items->first();

        if ($firstItem?->department_id) {
            $modelType = "Department";
        } elseif ($firstItem?->user_id) {
            $modelType = "User";
        } else {
            $modelType = "";
        }

        if (!$modelType) {
            return null;
        }

        // Map type to actual model class
        $modelMap = [
            'User' => \App\Models\AuthUser::class,
            'Department' => \App\Models\Department::class,
            // Add other mappings as needed
        ];

        $modelClass = $modelMap[$modelType] ?? "App\\Models\\$modelType";
        if (!class_exists($modelClass)) {
            return null;
        }

        $foreignKey = strtolower($modelType) . '_id';
        
        if (!isset($this->items->first()->$foreignKey)) {
            return null;
        }
        
        return optional($modelClass::find($this->items->first()->$foreignKey))->name;
    }
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
      public function dynamic_fields()
    {
        return $this -> hasMany(ErpMrDynamicField::class, 'header_id');
    }
}
