<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Storage;

class VendorHistory extends Model
{
    use HasFactory, Deletable, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_vendors_history';

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
        'reld_vendor_id',
        'book_id',
        'book_code',
        'vendor_code_type',
        'vendor_code',
        'vendor_type',
        'vendor_sub_type',
        'company_name',
        'vendor_initial',
        'display_name',
        'legal_name',
        'taxpayer_type',
        'gst_status',
        'block_status',
        'deregistration_date',
        'pan_number',
        'tin_number',
        'aadhar_number',
        'gst_state_id',
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
        'stop_billing',
        'stop_purchasing',
        'stop_payment',
        'group_id',
        'company_id',
        'enter_company_org_id',
        'organization_id',
        'created_by',
        'book_codes', 
    ];
    

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'notification' => 'array',
        'other_documents' => 'array',
    ];

    // Relationships
    public function erpOrganizationType()
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id');
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

    public function approvedItems()
    {
        return $this->hasMany(VendorItemHistory::class, 'vendor_id');
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

    public function shipping_addresses()
    {
        return $this->morphMany(ErpAddress::class, 'addressable')->whereIn('type', ['billing', 'both']);
    }

    public function latestBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->latest()->first();
    }

    public function latestShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->latest()->first();
    }

    public function compliances()
    {
        return $this->morphOne(Compliance::class, 'morphable');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class);
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

    public function paymentVoucherDetails()
    {
        return $this->morphMany(PaymentVoucherDetails::class, 'party');
    }

    public function supplier_users()
    {
        return $this->hasMany(VendorPortalUserHistory::class, 'vendor_id');
    }

    public function supplier_books()
    {
        return $this->hasMany(VendorPortalBookHistory::class, 'vendor_id');
    }

    public function other_details()
    {
        return $this->hasOne(ErpVendorOtherDetail::class, 'vendor_id', 'id');
    }

    public function locations()
    {
        return $this->hasMany(VendorLocationHistory::class, 'vendor_id');
    }

    public function parentVendor()
    {
        return $this->belongsTo(VendorHistory::class, 'reld_vendor_id');
    }

    public function syncLocations(array $storeIds)
    {
        VendorLocationHistory::where('vendor_id', $this->id)
            ->whereNotIn('store_id', $storeIds)
            ->delete();

        $referencedStore = VendorLocationHistory::where('vendor_id', '!=', $this->id)
            ->whereIn('store_id', $storeIds)
            ->get();

        if (count($referencedStore) > 0) {
            $storeNames = $referencedStore->pluck('store.store_name')->join(', ');
            return ['status' => false, 'message' => $storeNames . ' already used'];
        }

        foreach ($storeIds as $storeId) {
            VendorLocationHistory::updateOrCreate([
                'vendor_id' => $this->id,
                'store_id' => $storeId,
            ]);
        }

        return ['status' => true, 'message' => ''];
    }

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

    public function scopeSearchByKeywords($query, $term): mixed
    {
        $keywords = preg_split('/\s+/', trim($term));
        return $query->where(function($q) use ($keywords) {
            foreach ($keywords as $word) {
                $q->orWhere('company_name', 'LIKE', "%{$word}%")
                  ->orWhere('vendor_code', 'LIKE', "%{$word}%");
            }
        });
    }

    public function getDocumentStatusAttribute()
    {
        return $this->attributes['document_status'] === ConstantHelper::APPROVAL_NOT_REQUIRED
            ? ConstantHelper::APPROVED
            : $this->attributes['document_status'];
    }

    public function getDisplayStatusAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->document_status));
    }
}
