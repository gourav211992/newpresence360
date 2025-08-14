<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use App\Models\JobOrder\JobOrder;

use App\Traits\FileUploadTrait;
use App\Traits\DateFormatTrait;
use App\Traits\DynamicFieldsTrait;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorAsn extends Model
{
    use HasFactory, DateFormatTrait, DynamicFieldsTrait ,FileUploadTrait, DefaultGroupCompanyOrg;

    protected $table = 'erp_vendor_asn';
    
    protected $fillable = [
        'purchase_order_id', 
        'reference_id', 
        'type', 
        'organization_id', 
        'group_id', 
        'company_id', 
        'department_id', 
        'store_id', 
        'book_id', 
        'book_code', 
        'document_number', 
        'doc_number_type', 
        'doc_reset_pattern', 
        'doc_prefix', 
        'doc_suffix', 
        'doc_no', 
        'document_date', 
        'eway_bill_no', 
        'consignment_no', 
        'suppl_invoice_no', 
        'suppl_invoice_date', 
        'transporter_name', 
        'vehicle_no', 
        'revision_number', 
        'revision_date', 
        'reference_number', 
        'vendor_id', 
        'vendor_code', 
        'billing_address', 
        'shipping_address', 
        'currency_id', 
        'currency_code', 
        'document_status', 
        'approval_level', 
        'remarks', 
        'payment_term_id', 
        'payment_term_code', 
        'total_item_value', 
        'total_discount_value', 
        'total_tax_value', 
        'total_expense_value', 
        'org_currency_id', 
        'org_currency_code', 
        'org_currency_exg_rate', 
        'comp_currency_id', 
        'comp_currency_code', 
        'comp_currency_exg_rate', 
        'group_currency_id', 
        'group_currency_code', 
        'group_currency_exg_rate', 
        'gate_entry_required', 
        'supp_invoice_required', 
        'partial_delivery', 
        'invoice_file_path', 
        'created_by', 
        'updated_by', 
        'deleted_by'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function store_location()
    {
        return $this->belongsTo(ErpStore::class, 'store_id', 'id');
    }
    
    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function jo()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(VendorAsnItem::class, 'vendor_asn_id');
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

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    public function getTotalAmountAttribute()
    {
        return ($this->total_item_value - $this->total_discount_value);
    }

    public function getGrandTotalAmountAttribute()
    {
        return ($this->total_item_value - $this->total_discount_value + $this->total_tax_value + $this->total_expense_value);
    }

    public function ship_address()
    {
        // shipping_address addresses tbl id
        return $this->belongsTo(ErpAddress::class,'shipping_address');
    }

    public function bill_address()
    {
        // billing_address addresses tbl id
        return $this->belongsTo(ErpAddress::class,'billing_address');
    }

    public function bill_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id')->where('type', 'billing');
    }
    
    public function ship_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id')->where('type', 'shipping')->with(['city', 'state', 'country']);
    }

    public function store_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id')->where('type','location')->with(['city', 'state', 'country']);
    }

    public function billingAddress()
    {
        return $this->belongsTo(ErpAddress::class, 'billing_to');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ErpAddress::class, 'ship_to')->with(['city', 'state', 'country']);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function org_currency()
    {
        return $this->belongsTo(Currency::class,'org_currency_id');
    }

    public function comp_currency()
    {
        return $this->belongsTo(Currency::class,'comp_currency_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_term_id');
    }
    public function addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }

    public function latestBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->latest()->first();
    }

    public function latestShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->latest()->first();
    }

    public function latestDeliveryAddress()
    {
        return $this->addresses()->where('type', 'location')->latest()->first();
    }
    
    public function createdBy()
    {
        return $this->belongsTo(Employee::class,'created_by','id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
