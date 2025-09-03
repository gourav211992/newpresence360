<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\User;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Organization;

use App\Models\JobOrder\JobOrder;
use App\Models\WHM\ErpWhmJob;
use App\Models\WHM\ErpItemUniqueCode;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;

class MrnHeader extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait,DefaultGroupCompanyOrg, DynamicFieldsTrait;
    protected $table = 'erp_mrn_headers';
    // public bool $disableDefaultGroupCompanyOrgScope = true;
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'vendor_id',
        'purchase_order_id',
        'job_order_id',
        'sale_order_id',
        'cost_center_id',
        'mrn_date',
        'document_date',
        'document_status',
        'revision_number',
        'revision_date',
        'approval_level',
        'reference_number',
        'store_id',
        'sub_store_id',
        'rejected_sub_store_id',
        'department_id',
        'mrn_type',
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
        'payment_term_id',
        'bill_to_follow',
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
        'discount',
        'discount_amount',
        'gst',
        'gst_details',
        'taxable_amount',
        'expense_amount',
        'total_amount',
        'is_warehouse_required',
        'is_enforce_uic_scanning',
        'is_inspection_completion',
        'item_remark',
        'final_remarks',
        'status'
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
        return $this->morphMany(MrnMedia::class, 'model');
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
        return $this->hasOne(MrnHeaderHistory::class, 'mrn_header_id');
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    public function saleOrder()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'sale_order_id');
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

    public function erpSubStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function rejectedSubStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'rejected_sub_store_id');
    }

    public function costCenters()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'series_id');
    }

    public function erpBook()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(MrnDetail::class, 'mrn_header_id');
    }

    public function assetDetails()
    {
        return $this->hasMany(MrnAssetDetail::class, 'header_id');
    }

    public function batches()
    {
        return $this->hasMany(MrnBatchDetail::class, 'header_id');
    }

    public function itemLocations()
    {
        return $this->hasMany(MrnItemLocation::class, 'mrn_header_id');
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

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_term_id');
    }

    public function getMrnAmountAttribute()
    {
        return ($this->total_item_amount - $this->total_discount);
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(MrnExtraAmount::class, 'mrn_header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    /*Total discount header level total_header_disc_amount*/
    public function getTotalHeaderDiscAmountAttribute()
    {
        return $this->headerDiscount()->sum('ted_amount');
    }

    public function expenses()
    {
        return $this->hasMany(MrnExtraAmount::class,'mrn_header_id')->where('ted_type', '=', 'Expense')
            ->where('ted_level', '=', 'H');
    }

    public function getTotalExpAssessmentAmountAttribute()
    {
        return ($this->total_item_amount + $this->total_taxes - $this->total_discount);
    }

    public function mrn_ted()
    {
        return $this->hasMany(MrnExtraAmount::class,'mrn_header_id');
    }

    public function mrn_ted_tax()
    {
        return $this->hasMany(MrnExtraAmount::class,'mrn_header_id')->where('ted_type','Tax');
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
        return $this -> hasMany(ErpMrnDynamicField::class, 'header_id');
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

        $itemIds = MrnDetail::where('mrn_header_id', $this->id)->pluck('id')->toArray();

        if (empty($itemIds)) {
            return [
                'total_unique_codes' => 0,
                'scanned_unique_codes' => 0,
                'pending_unique_codes' => 0,
            ];
        }

        $baseQuery = ErpItemUniqueCode::where('job_id', $job->id)
            ->where('morphable_type', MrnDetail::class)
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

    public function payment_term_schedules()
    {
        return $this -> hasMany(ErpMrnPaymentTerm::class, 'mrn_header_id');
    }

}
