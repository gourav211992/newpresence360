<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DynamicFieldsTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\UserStampTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpSaleReturn extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;


    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'store_id',
        'store_code',
        'department_id',
        'department_code',
        'gst_invoice_type',
        'gst_status',
        'document_number',
        'document_type',
        'doc_number_type',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_date',
        'reference_number',
        'revision_number',
        'customer_id',
        'customer_email',
        'customer_phone_no',
        'customer_gstin',
        'customer_code',
        'consignee_name',
        'consignment_no',
        'eway_bill_master_id',
        'eway_bill_no',
        'transporter_name',
        'transportation_mode',
        'vehicle_no',
        'billing_address',
        'shipping_address',
        'currency_id',
        'currency_code',
        'payment_term_id',
        'payment_term_code',
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

    protected $appends = [
        'taxable_amount',
        'expense_amount',
        'display_status'
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

    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    public function cust()
    {
        return $this -> hasOne(Customer::class, 'id', 'customer_id');
    }
    public function customer()
    {
        return $this -> hasOne(ErpCustomer::class, 'id', 'customer_id');
    }
        //For GStIn - EInvoice
    public function vendor()
    {
        return $this -> hasOne(Customer::class, 'id', 'customer_id');
    }
    public function media()
    {
        return $this->morphMany(ErpSrMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpSrMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
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
        return $this -> hasMany(ErpSaleReturnItem::class, 'sale_return_id');
    }

    public function expense_ted()
    {
        return $this -> hasMany(ErpSaleReturnTed::class, 'sale_return_id') -> where('ted_level', 'H') -> where('ted_type', 'Expense');
    }

    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleReturnTed::class, 'sale_return_id') -> where('ted_level', 'H') -> where('ted_type', 'Discount');
    }    
    public function addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }

    public function billing_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'billing');
    }

    public function shipping_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping');
    }
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }   
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function location()
    {
        return $this-> belongsTo(ErpStore::class,'store_id','id');
    }
    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'location');
    }
    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
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
    public function getTaxableAmountAttribute()
    {
        return (float)$this -> total_return_value - (float) $this -> total_discount_value;
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
        return $this -> belongsTo(Voucher::class, 'id', 'reference_doc_id') -> where('reference_service', ConstantHelper::SR_SERVICE_ALIAS);
    }
     public function dynamic_fields()
    {
        return $this -> hasMany(ErpSrDynamicField::class, 'header_id');
    }
}
