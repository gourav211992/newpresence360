<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\UserStampTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleOrder extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'document_type',
        'document_number',
        'doc_number_type',
        'doc_reset_pattern',
        'vendor_id',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_date',
        'revision_number',
        'revision_date',
        'reference_number',
        'order_type',
        'store_id',
        'store_code',
        'department_id',
        'department_code',
        'customer_id',
        'customer_email',
        'customer_phone_no',
        'customer_gstin',
        'customer_code',
        'consignee_name',
        'billing_address',
        'shipping_address',
        'currency_id',
        'currency_code',
        'payment_term_id',
        'payment_term_code',
        'credit_days',
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


    public $referencingRelationships = [
        'customer' => 'customer_id',
        'currency' => 'currency_id',
        'payment_terms' => 'payment_term_id'
    ];

    public function getFullDocumentNumberAttribute()
    {
        $fdn = strtoupper($this->book_code) . '-' . $this->document_number;
        return $fdn;
    }

    public function media()
    {
        return $this->morphMany(ErpSoMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpSoMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }

    public function cust()
    {
        return $this -> hasOne(Customer::class, 'id', 'customer_id');
    }
    public function book()
    {
        return $this -> hasOne(Book::class, 'id', 'book_id');
    }
    public function customer()
    {
        return $this -> hasOne(ErpCustomer::class, 'id', 'customer_id');
    }

    public function currency()
    {
        return $this -> hasOne(ErpCurrency::class, 'id', 'currency_id');
    }

    public function payment_terms()
    {
        return $this -> hasOne(ErpPaymentTerm::class, 'id', 'payment_term_id');
    }
    public function items()
    {
        return $this -> hasMany(ErpSoItem::class, 'sale_order_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function expense_ted()
    {
        return $this -> hasMany(ErpSaleOrderTed::class, 'sale_order_id') -> where('ted_level', 'H') -> where('ted_type', 'Expense');
    }
    public function tax_ted()
    {
        return $this->hasMany(ErpSaleOrderTed::class,'sale_order_id')->where('ted_type','Tax');
    }
    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleOrderTed::class, 'sale_order_id') -> where('ted_level', 'H') -> where('ted_type', 'Discount');
    }
    public function billing_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'billing');
    }
    public function shipping_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping')->with(['city', 'state', 'country']);
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
    public function addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpSoDynamicField::class, 'header_id');
    }
    public function getDisplayDocumentNumberAttribute()
    {
        return $this -> book_code . ' - ' . $this -> document_number;
    }
}
