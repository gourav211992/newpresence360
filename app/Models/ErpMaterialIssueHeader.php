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

class ErpMaterialIssueHeader extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DynamicFieldsTrait, DateFormatTrait, UserStampTrait;

    protected $table = "erp_material_issue_header";

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'issue_type',
        'enforce_uic_scanning',
        'document_type',
        'document_number',
        'document_date',
        'revision_number',
        'prefix',
        'suffix',
        'doc_no',
        'revision_date',
        'reference_number',
        'from_store_id',
        'from_sub_store_id',
        'from_station_id',
        'from_store_code',
        'to_store_id',
        'to_sub_store_id',
        'to_station_id',
        'to_store_code',
        'station_id',
        'department_id',
        'department_code',
        'requester_type',
        'user_id',
        'user_name',
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
        'from_store' => 'from_store_id',
        'to_store' => 'to_store_id',
        'department' => 'department_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
        'group_currency' => 'group_currency_id',
    ];


    public function media()
    {
        return $this->morphMany(ErpMaterialIssueMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpMaterialIssueMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
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
        return $this -> hasMany(ErpMiItem::class, 'material_issue_id');
    }
    public function pickingItems()
    {
        return $this->hasMany(ErpMiItem::class, 'material_issue_id') -> select('id', 'material_issue_id', 'inventory_uom_qty');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'from_store_id');
    }
    public function erpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'from_store_id');
    }
    public function from_store()
    {
        return $this -> belongsTo(ErpStore::class, 'from_store_id');
    }
    public function from_sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'from_sub_store_id');
    }
    public function from_station()
    {
        return $this -> belongsTo(Station::class, 'from_station_id');
    }
    public function to_store()
    {
        return $this -> belongsTo(ErpStore::class, 'to_store_id');
    }
    public function to_sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'to_sub_store_id');
    }
    public function to_station()
    {
        return $this -> belongsTo(Station::class, 'to_station_id');
    }
    public function requester_name()
    {
        $modelType = $this->requester_type; // e.g., 'User', 'Department'
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

        if (!isset($this->$foreignKey)) {
            return null;
        }

        return optional($modelClass::find($this->$foreignKey))->name;
    }


    public function department()
    {
        return $this -> belongsTo(Department::class, 'department_id');
    }
    public function requester()
    {
        $modelType = $this->requester_type; // e.g., 'User', 'Department'
        if ($modelType=='User') {
            return $this -> belongsTo(AuthUser::class, 'user_id');
        }
        else{
            return $this -> belongsTo(Department::class, 'department_id');
        }        
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
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
    public function vendor_shipping_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping');
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpMiDynamicField::class, 'header_id');
    }
}
