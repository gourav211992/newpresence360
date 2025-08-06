<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Storage;

class CustomerHistory extends Model
{
    use HasFactory, Deletable, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_customers_history';

    protected $fillable = [
        'source_id',
        'organization_type_id',
        'category_id',
        'subcategory_id',
        'currency_id',
        'payment_terms_id',
        'create_ledger',
        'ledger_group_id',
        'ledger_id',
        'contra_ledger_id',
        'reld_customer_id',
        'sales_person_id',
        'book_id',
        'book_code',
        'customer_code_type',
        'customer_code',
        'customer_type',
        'customer_initial',
        'company_name',
        'display_name',
        'legal_name',
        'taxpayer_type',
        'gst_status',
        'block_status',
        'deregistration_date',
        'gst_state_id',
        'pan_number',
        'tin_number',
        'aadhar_number',
        'pan_attachment',
        'tin_attachment',
        'aadhar_attachment',
        'other_documents',
        'email',
        'phone',
        'mobile',
        'whatsapp_number',
        'notification',
        'opening_balance',
        'pricing_type',
        'credit_limit',
        'credit_days',
        'credit_days_editable',
        'on_account_required',
        'interest_percent',
        'related_party',
        'status',
        'document_status',
        'approval_level',
        'revision_number',
        'revision_date',
        'is_prospect',
        'product_category_id', 
        'lead_source_id',
        'industry_id',
        'lead_status', 
        'sales_figure', 
        'city', 
        'contact_person',
        'stop_billing',
        'stop_purchasing',
        'stop_payment',
        'customer_address',
        'country_id',
        'state_id',
        'city_id',
        'customer_pincode',
        'group_id',
        'company_id',
        'enter_company_org_id',
        'organization_id',
        'created_by',
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'notification' => 'array',
        'other_documents' => 'array',
    ];

    // ========================= RELATIONSHIPS ========================= //

    public function erpOrganizationType()
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(Employee::class, 'sales_person_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class);
    }

    public function bankInfos()
    {
        return $this->morphMany(BankInfo::class, 'morphable');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable');
    }

    public function compliances()
    {
        return $this->morphOne(Compliance::class, 'morphable');
    }

    public function approvedItems()
    {
        return $this->hasMany(CustomerItemHistory::class, 'customer_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class);
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function contraLedger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function parentdCustomer()
    {
        return $this->belongsTo(CustomerHistory::class, 'reld_customer_id');
    }

    public function other_details()
    {
        return $this->hasOne(ErpCustomerOtherDetails::class, 'customer_id', 'id');
    }

    public function paymentVoucherDetails()
    {
        return $this->morphMany(PaymentVoucherDetails::class, 'party');
    }

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

    // ========================= FILE URL GETTERS ========================= //

    public function getPanAttachmentUrlAttribute()
    {
        return $this->generateFileUrl($this->pan_attachment);
    }

    public function getTinAttachmentUrlAttribute()
    {
        return $this->generateFileUrl($this->tin_attachment);
    }

    public function getAadharAttachmentUrlAttribute()
    {
        return $this->generateFileUrl($this->aadhar_attachment);
    }

    public function getOtherDocumentsUrlsAttribute()
    {
        return $this->generateFileUrls($this->other_documents);
    }

    protected function generateFileUrl($filePath)
    {
        return $filePath ? Storage::url($filePath) : null;
    }

    protected function generateFileUrls($filePaths)
    {
        if (is_array($filePaths)) {
            return array_map(fn($filePath) => Storage::url($filePath), $filePaths);
        }
        return [];
    }

    // ========================= SCOPES & ACCESSORS ========================= //

    public function scopeSearchByKeywords($query, $term): mixed
    {
        $keywords = preg_split('/\s+/', trim($term));
        return $query->where(function($q) use ($keywords) {
            foreach ($keywords as $word) {
                $q->orWhere('company_name', 'LIKE', "%{$word}%")
                  ->orWhere('customer_code', 'LIKE', "%{$word}%");
            }
        });
    }

    public function getDocumentStatusAttribute()
    {
        return $this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED
            ? ConstantHelper::APPROVED
            : $this->attributes['document_status'];
    }

    public function getDisplayStatusAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->document_status));
    }
}
