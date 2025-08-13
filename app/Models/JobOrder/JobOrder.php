<?php

namespace App\Models\JobOrder;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\AuthUser;
use App\Models\Book;
use App\Models\Currency;
use App\Models\Department;
use App\Models\ErpAddress;
use App\Models\ErpPoDynamicField;
use App\Models\ErpStore;
use App\Models\GateEntryHeader;
use App\Models\JobOrder\JoTerm;
use App\Models\Organization;
use App\Models\PaymentTerm;
use App\Models\PiPoMapping;
use App\Models\Vendor;
use App\Models\VendorAsn;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    use HasFactory,DateFormatTrait,DynamicFieldsTrait,FileUploadTrait,DefaultGroupCompanyOrg;
    protected $table = 'erp_job_orders';
    protected $fillable = [
        'job_order_type',
        'organization_id',
        'group_id',
        'company_id',
        'department_id',
        'store_id',
        'book_id',
        'book_code',
        'document_number',
        'document_date',
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
        'tnc',
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
        'partial_delivery'
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

    public $referencingRelationships = [
        'vendor' => 'vendor_id',
        'bill_address' => 'billing_address',
        'ship_address' => 'shipping_address',
        'currency' => 'currency_id',
        'paymentTerm' => 'payment_term_id',
        'org_currency' => 'org_currency_id',
        'comp_currency' => 'comp_currency_id',
    ];
    public function getSoIdAttribute()
    {
        return $this->joProducts
        ->pluck('so_id')
        ->filter()
        ->unique()
        ->values()
        ->toArray();
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }
    public function store_location()
    {
        return $this->belongsTo(ErpStore::class, 'store_id', 'id');
    }
    public function media()
    {
        return $this->morphMany(JobOrderMedia::class, 'model');
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
    public function joProducts()
    {
        return $this->hasMany(JoProduct::class, 'jo_id');
    }
    public function items() //For SO PULL only
    {
        return $this->hasMany(JoProduct::class, 'jo_id');
    }
    public function joItems()
    {
        return $this->hasMany(JoItem::class, 'jo_id');
    }
    public function terms()
    {
        return $this->hasMany(JoTerm::class,'jo_id');
    }
    public function jo_items_delivery()
    {
        return $this->hasMany(JoProductDelivery::class,'jo_id');
    }
    public function jo_ted()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id');
    }
    public function jo_ted_tax()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id')->where('ted_type','Tax');
    }
    public function getTotalAmountAttribute()
    {
        return ($this->total_item_value - $this->total_discount_value);
    }
    public function getGrandTotalAmountAttribute()
    {
        return ($this->total_item_value - $this->total_discount_value + $this->total_tax_value + $this->total_expense_value);
    }
    public function term()
    {
        return $this->belongsTo(JoTerm::class,'jo_id');
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
    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_term_id');
    }
    public function TermsCondition()
    {
        return $this->hasOne(JoTerm::class,'jo_id');
    }
    public function TermsConditions()
    {
        return $this->hasMany(JoTerm::class,'jo_id');
    }
    // After item total assessment amount
    // public function getTotalAssessmentAmountHeaderAttribute()
    // {
    //     return $this->jo_items()->itemDiscount()->sum('ted_amount');
    // }
    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }
    public function discount_ted()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }
    public function itemDiscount()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }
    /*Total discount header level total_header_disc_amount*/
    public function getTotalItemDiscAmountAttribute()
    {
        return $this->itemDiscount()->sum('ted_amount');
    }
    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }
    public function headerExpenses()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id')->where('ted_type','Expense')->where('ted_level','H');
    }
    public function expense_ted()
    {
        return $this->hasMany(JobOrderTed::class,'jo_id')->where('ted_type','Expense')->where('ted_level','H');
    }
    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_value + $this->total_tax_value - $this->total_discount_value);
    }
    // public function approvals()
    // {
    //     return $this->morphMany(DocumentApproval::class, 'document', 'document_name', 'document_id');
    // }
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
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function pi_item_mappings()
    {
        return $this->hasMany(PiPoMapping::class,'jo_id','id');
    }
    public function dynamic_fields()
    {
        return $this->hasMany(ErpPoDynamicField::class, 'header_id');
    }

    public function gateEntry()
    {
        return $this->hasMany(GateEntryHeader::class, 'job_order_id');
    }

    public function vendorAsn()
    {
        return $this->hasMany(VendorAsn::class, 'job_order_id');
    }
}
