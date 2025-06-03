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

class ErpSaleOrderHistory extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;
    protected $table = 'erp_sale_orders_history';

    public $referencingRelationships = [
        'customer' => 'customer_id',
        'currency' => 'currency_id',
        'paymentTerms' => 'payment_term_id'
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
        return $this -> hasMany(ErpSoItemHistory::class, 'sale_order_id');
    }

    public function expense_ted()
    {
        return $this -> hasMany(ErpSaleOrderTedHistory::class, 'sale_order_id') -> where('ted_level', 'H') -> where('ted_type', 'Expense');
    }
    public function tax_ted()
    {
        return $this->hasMany(ErpSaleOrderTedHistory::class,'sale_order_id')->where('ted_type','Tax');
    }
    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleOrderTedHistory::class, 'sale_order_id') -> where('ted_level', 'H') -> where('ted_type', 'Discount');
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
        return $this -> hasMany(ErpSoDynamicFieldHistory::class, 'header_id');
    }
    public function getDisplayDocumentNumberAttribute()
    {
        return $this -> book_code . ' - ' . $this -> document_number;
    }
}
