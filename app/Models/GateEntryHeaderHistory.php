<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Models\JobOrder\JobOrder;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryHeaderHistory extends Model
{
    use HasFactory, DateFormatTrait, SoftDeletes, FileUploadTrait, DefaultGroupCompanyOrg, DynamicFieldsTrait;

    protected $table = 'erp_gate_entry_headers_history';

    protected $fillable = [
        'source_id',
        'organization_id',
        'group_id',
        'company_id',
        'purchase_order_id',
        'job_order_id',
        'sale_order_id',
        'series_id',
        'book_id',
        'book_code',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'vendor_id',
        'vendor_code',
        'customer_id',
        'customer_code',
        'store_id',
        'document_number',
        'document_date',
        'document_status',
        'revision_number',
        'revision_date',
        'approval_level',
        'reference_number',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'billing_to',
        'ship_to',
        'billing_address',
        'shipping_address',
        'currency_id',
        'currency_code',
        'payment_term_id',
        'payment_term_code',
        'transaction_currency',
        'org_currency_id',
        'org_currency_code',
        'org_currency_exg_rate',
        'comp_currency_id',
        'comp_currency_code',
        'comp_currency_exg_rate',
        'group_currency_id',
        'group_currency_code',
        'group_currency_exg_rate',
        'sub_total',
        'total_item_amount',
        'item_discount',
        'header_discount',
        'total_discount',
        'gst',
        'gst_details',
        'taxable_amount',
        'total_taxes',
        'total_after_tax_amount',
        'expense_amount',
        'total_amount',
        'final_remark',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'gst_details' => 'array',
    ];


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function gateEntry()
    {
        return $this->belongsTo(GateEntryHeader::class, 'header_id');
    }

    public function gateEntryHeader()
    {
        return $this->belongsTo(GateEntryHeader::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'series_id');
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(GateEntryDetailHistory::class, 'header_id');
    }

    public function attributes()
    {
        return $this->hasMany(GateEntryAttributeHistory::class, 'source_id');
    }

    public function mrn_ted()
    {
        return $this->hasMany(GateEntryTedHistory::class,'source_id');
    }

    public function mrn_ted_tax()
    {
        return $this->hasMany(GateEntryTedHistory::class,'source_id')->where('ted_type','Tax');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_to');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'ship_to');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    public function attachment(): void
    {
        $this->addMediaCollection('attachment');
    }

    public function organizationAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'default');
    }

    public function billingPartyAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'billing');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'source_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }

    /*Header Level Expense*/
    public function expenses()
    {
        return $this->hasMany(GateEntryTedHistory::class,'source_id')->where('ted_type', '=', 'Expense')
            ->where('ted_level', '=', 'H');
    }

    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_amount + $this->total_taxes - $this->total_discount);
    }

    public function addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }
    public function latestBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->latest()->first();
    }

    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }

    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }

    public function latestShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->latest()->first();
    }

    public function media()
    {
        return $this->morphMany(GateEntryMedia::class, 'model');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_term_id');
    }

    public function dynamic_fields()
    {
        return $this -> hasMany(ErpGeDynamicField::class, 'header_id');
    }

    public function bill_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'billing')->with(['city', 'state', 'country']);
    }

    public function ship_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping')->with(['city', 'state', 'country']);
    }
}

