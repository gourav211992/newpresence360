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

class ErpTransportInvoice extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;

 protected $guarded = ['id'];
    protected $appends = [
        'taxable_amount',
        'expense_amount'
    ];

    public $referencingRelationships = [
        'customer' => 'customer_id',
        'currency' => 'currency_id',
        'payment_terms' => 'payment_term_id'
    ];

    protected $hidden = ['deleted_at'];

    public function media()
    {
        return $this->morphMany(ErpSiMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpSiMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function cust()
    {
        return $this -> hasOne(Customer::class, 'id', 'customer_id');
    }
    public function customer()
    {
        return $this -> hasOne(Customer::class, 'id', 'customer_id');
    }
    //For GStIn - EInvoice
    public function vendor()
    {
        return $this -> hasOne(Customer::class, 'id', 'customer_id');
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
        return $this -> hasMany(ErpTIInvoiceItem::class, 'ti_invoice_id');
    }

    public function expense_ted()
    {
        return $this -> hasMany(ErpTransportInvoiceTed::class, 'transport_invoice_id') -> where('ted_level', 'H') -> where('ted_type', 'Expense');
    }
    public function discount_ted()
    {
        return $this -> hasMany(ErpTransportInvoiceTed::class, 'transport_invoice_id') -> where('ted_level', 'H') -> where('ted_type', 'Discount');
    }
    public function billing_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'billing');
    }
    public function shipping_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping');
    }
    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type',  'location');
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
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }

    public function latestBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->latest()->first();
    }

    public function latestShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->latest()->first();
    }
    public function erpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function subStore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function getTaxableAmountAttribute()
    {
        return (float)$this -> total_item_value - (float) $this -> total_discount_value;
    }
    public function getExpenseAmountAttribute()
    {
        return (float)$this -> total_expense_value;
    }
    public function irnDetail()
    {
        return $this->morphOne(ErpEinvoice::class, 'morphable', 'morphable_type', 'morphable_id');
    }
    public function organization_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'organization');
    }
    public function transportationMode()
    {
        return $this->belongsTo(EwayBillMaster::class, 'eway_bill_master_id');
    }
    public function voucher()
    {
        return $this -> belongsTo(Voucher::class, 'id', 'reference_doc_id') -> where('reference_service', $this -> document_type);
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpSiDynamicField::class, 'header_id');
    }
    public function customerTermDetails()
    {
        return $this -> belongsTo(TermsAndCondition::class, 'customer_terms_id');
    }
}
