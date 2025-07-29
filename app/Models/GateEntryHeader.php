<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\JobOrder\JobOrder;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryHeader extends Model
{
    use HasFactory, DateFormatTrait, SoftDeletes, FileUploadTrait,DefaultGroupCompanyOrg, DynamicFieldsTrait;

    protected $table = 'erp_gate_entry_headers';

    protected $fillable = [
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
        'gate_entry_no',
        'gate_entry_date',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'eway_bill_no',
        'consignment_no',
        'transporter_name',
        'vehicle_no',
        'manual_entry_no',
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

    public $referencingRelationships = [
        'vendor' => 'vendor_id',
        'bill_address' => 'billing_address',
        'ship_address' => 'shipping_address',
        'currency' => 'currency_id',
        'paymentTerm' => 'payment_term_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    public function items()
    {
        return $this->hasMany(GateEntryDetail::class, 'header_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'series_id');
    }

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'gst_details' => 'array',
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
        return $this->morphMany(GateEntryMedia::class, 'model');
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
        return $this->hasOne(GateEntryHeaderHistory::class, 'source_id');
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
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

    public function erpBook()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
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

    public function getGateEntryAmountAttribute()
    {
        return ($this->total_item_amount - $this->total_discount);
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(GateEntryTed::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }

    public function expenses()
    {
        return $this->hasMany(GateEntryTed::class,'header_id')->where('ted_type', '=', 'Expense')
            ->where('ted_level', '=', 'H');
    }

    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_amount + $this->total_taxes - $this->total_discount);
    }

    public function gate_entry_ted()
    {
        return $this->hasMany(GateEntryTed::class,'header_id');
    }

    public function gate_entry_ted_tax()
    {
        return $this->hasMany(GateEntryTed::class,'header_id')->where('ted_type','Tax');
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
    public function pruchase()
    {
        return $this->belongsTo(PurchaseOrder::class,'purchase_order_id');
    }

    public function latestBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->latest()->first();
    }

    public function latestShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->latest()->first();
    }

    public function dynamic_fields()
    {
        return $this -> hasMany(ErpGeDynamicField::class, 'header_id');
    }

    // Item Unique Codes
    public function itemUniqueCodes()
    {
        $job = $this->deviationJob;

        if (!$job) {
            return [
                'total_unique_codes' => 0,
                'scanned_unique_codes' => 0,
                'pending_unique_codes' => 0,
            ];
        }

        $itemIds = GateEntryDetail::where('header_id', $this->id)->pluck('id')->toArray();

        if (empty($itemIds)) {
            return [
                'total_unique_codes' => 0,
                'scanned_unique_codes' => 0,
                'pending_unique_codes' => 0,
            ];
        }

        $baseQuery = ErpItemUniqueCode::where('job_id', $job->id)
            ->where('morphable_type', GateEntryDetail::class)
            ->whereIn('morphable_id', $itemIds);

        $total = $baseQuery->count();

        if ($total === 0) {
            return [
                'total_unique_codes' => 0,
                'scanned_unique_codes' => 0,
                'pending_unique_codes' => 0,
            ];
        }

        $scanned = (clone $baseQuery)->where('status', 'scanned')->count();

        return [
            'total_unique_codes' => $total,
            'scanned_unique_codes' => $scanned,
            'pending_unique_codes' => $total - $scanned,
        ];
    }

    public function deviationJob()
    {
        return $this->morphOne(ErpWhmJob::class, 'morphable')
                    ->where('status', 'deviation');
    }

    public function closedJob()
    {
        return $this->morphOne(ErpWhmJob::class, 'morphable')
                    ->where('status', 'closed');
    }

    public function job()
    {
        return $this->morphOne(ErpWhmJob::class, 'morphable');
    }

}

