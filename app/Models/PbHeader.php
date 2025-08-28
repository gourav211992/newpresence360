<?php
namespace App\Models;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PbHeader extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait,DefaultGroupCompanyOrg, DynamicFieldsTrait;

    protected $table = 'erp_pb_headers';
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'vendor_id',
        'vendor_code',
        'customer_id',
        'customer_code',
        'mrn_header_id',
        'series_id',
        'book_code',
        'document_number',
        'cost_center_id',
        'document_date',
        'document_status',
        'revision_number',
        'revision_date',
        'approval_level',
        'reference_number',
        'store_id',
        'department_id',
        'gate_entry_no',
        'gate_entry_date',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'eway_bill_no',
        'consignment_no',
        'transporter_name',
        'vehicle_no',
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
        'deleted_by'
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'gst_details' => 'array',
    ];

    public $referencingRelationships = [
        'vendor' => 'vendor_id',
        'bill_address' => 'billing_address',
        'ship_address' => 'shipping_address',
        'currency' => 'currency_id',
        'paymentTerm' => 'payment_term_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->created_by = $user->auth_user_id;
            }
        });

        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->auth_user_id;
            }
        });

        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->auth_user_id;
            }
        });
    }

    public function media()
    {
        return $this->morphMany(PbMedia::class, 'model');
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

    public function source()
    {
        return $this->hasOne(PbHeaderHistory::class, 'header_id');
    }

    public function mrn()
    {
        return $this->belongsTo(MrnHeader::class, 'mrn_header_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(PbDetail::class, 'header_id');
    }

    public function ship_address()
    {
        return $this->belongsTo(ErpAddress::class,'shipping_address');
    }

    public function bill_address()
    {
        return $this->belongsTo(ErpAddress::class,'billing_address');
    }

    public function billingAddress()
    {
        return $this->belongsTo(ErpAddress::class, 'billing_to')->with(['city', 'state', 'country']);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ErpAddress::class, 'ship_to')->with(['city', 'state', 'country']);
    }

    public function bill_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'billing')->with(['city', 'state', 'country']);
    }

    public function ship_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping')->with(['city', 'state', 'country']);
    }

    public function store_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id')->where('type','location')->with(['city', 'state', 'country']);
    }

    public function addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }

    public function organizationAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'default');
    }

    public function billingPartyAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'billing');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_term_id');
    }

    // public function getTotalAmountAttribute()
    // {
    //     return ($this->total_item_amount - $this->total_discount);
    // }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PbTed::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }

    public function expenses()
    {
        return $this->hasMany(PbTed::class,'header_id')->where('ted_type', '=', 'Expense')
            ->where('ted_level', '=', 'H');
    }

    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_amount + $this->total_taxes - $this->total_discount);
    }

    public function pb_ted()
    {
        return $this->hasMany(PbTed::class,'header_id');
    }

    public function pb_ted_tax()
    {
        return $this->hasMany(PbTed::class,'header_id')->where('ted_type','Tax');
    }

    public function getGrandTotalAmountAttribute()
    {
        return ($this->total_item_amount - $this->total_discount + $this->total_taxes + $this->expense_amount);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function latestBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->latest()->first();
    }

    public function latestShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->latest()->first();
    }

    public function costCenters()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function dynamic_fields()
    {
        return $this -> hasMany(ErpPbDynamicField::class, 'header_id');
    }

    public function payment_term_schedules()
    {
        return $this -> hasMany(ErpPbPaymentTerm::class, 'pb_header_id');
    }
}
