<?php
namespace App\Models\MaterialRequest;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Book;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Organization;
use App\Models\PaymentTerm;
use App\Models\Vendor;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MrHeaderHistory extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;
    protected $table = 'erp_mr_header_histories';

    protected $fillable = [
                'organization_id',
                'group_id',
                'company_id',
                'book_id',
                'book_code',
                'header_id',
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function mrn()
    {
        return $this->belongsTo(MrHeader::class, 'mrn_header_id');
    }

    public function mrnHeader()
    {
        return $this->belongsTo(MrHeader::class);
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
        return $this->hasMany(MrDetailHistory::class, 'mrn_header_history_id');
    }

    public function attributes()
    {
        return $this->hasMany(MrItemAttributeHistory::class, 'mrn_header_history_id');
    }

    public function mrn_ted()
    {
        return $this->hasMany(MrTedHistory::class,'mrn_header_history_id');
    }

    public function mrn_ted_tax()
    {
        return $this->hasMany(MrTedHistory::class,'mrn_header_history_id')->where('ted_type','Tax');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_to');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'ship_to');
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
        return $this->hasMany(MrTedHistory::class, 'mrn_header_history_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }

    /*Header Level Expense*/
    public function expenses()
    {
        return $this->hasMany(MrTedHistory::class,'mrn_header_history_id')->where('ted_type', '=', 'Expense')
            ->where('ted_level', '=', 'H');
    }

    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_amount + $this->total_taxes - $this->total_discount);
    }
}



