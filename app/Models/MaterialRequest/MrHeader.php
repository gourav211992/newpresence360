<?php
namespace App\Models\MaterialRequest;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use App\Models\Book;
use App\Models\Currency;
use App\Models\ErpAddress;
use App\Models\Group;
use App\Models\OrganizationCompany;
use App\Models\PaymentTerm;
use App\Models\Vendor;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrHeader extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait,DefaultGroupCompanyOrg;
    protected $table = 'erp_mr_headers';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'store_id',
        'store_code',
        'document_number',
        'document_date',
        'document_status',
        'revision_number',
        'revision_date',
        'approval_level',
        'reference_number',
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
        'total_item_amount',
        'item_discount',
        'header_discount',
        'total_discount',
        'taxable_amount',
        'total_taxes',
        'total_after_tax_amount',
        'expense_amount',
        'total_amount',
        'remark',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function boot()
    {
        parent::boot();
        $user = Helper::getAuthenticatedUser();
        if($user) {
            static::creating(function ($model) use($user) {
                $model->created_by = $user->auth_user_id;
            });

            static::updating(function ($model) use($user) {
                $model->updated_by = $user->auth_user_id;
            });

            static::deleting(function ($model) use($user) {
                $model->deleted_by = $user->auth_user_id;
            });
        }
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
        return $this->hasMany(MrDetail::class, 'header_id');
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
        return $this->belongsTo(ErpAddress::class, 'billing_to');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ErpAddress::class, 'ship_to')->with(['city', 'state', 'country']);
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

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_term_id');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(MrTed::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }

    public function expenses()
    {
        return $this->hasMany(MrTed::class,'header_id')->where('ted_type', '=', 'MR')
            ->where('ted_level', '=', 'H');
    }

    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_amount - $this->total_discount);
    }

    public function mr_ted()
    {
        return $this->hasMany(MrTed::class,'header_id');
    }

    public function mr_ted_tax()
    {
        return $this->hasMany(MrTed::class,'header_id')->where('ted_type','Tax');
    }

    public function getGrandTotalAmountAttribute()
    {
        return ($this->total_item_amount - $this->total_discount  + $this->expense_amount);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}


