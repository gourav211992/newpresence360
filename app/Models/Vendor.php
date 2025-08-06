<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Helpers\ConstantHelper;

class Vendor extends Model
{
    use HasFactory,Deletable,SoftDeletes,DefaultGroupCompanyOrg;

    protected $table = 'erp_vendors';
    protected $fillable = [
        'organization_type_id',
        'category_id',
        'subcategory_id',
        'book_id',
        'book_code',
        'vendor_code',
        'vendor_type',
        'vendor_sub_type',
        'company_name',
        'vendor_initial',
        'vendor_code_type',
        'display_name',
        'taxpayer_type',
        'gst_status',    
        'block_status',  
        'deregistration_date',
        'legal_name',   
        'gst_state_id',
        'currency_id',
        'payment_terms_id',
        'related_party',
        'contra_ledger_id',
        'reld_vendor_id',
        'email',
        'phone',
        'mobile',
        'whatsapp_number',
        'notification',
        'pan_number',
        'tin_number',
        'aadhar_number',
        'opening_balance',
        'create_ledger',
        'ledger_id',
        'ledger_group_id',
        'pricing_type',
        'credit_limit',
        'credit_days',
        'credit_days_editable',
        'on_account_required',
        'interest_percent',
        'stop_billing',
        'stop_purchasing',
        'stop_payment',
        'group_id',            
        'company_id',        
        'organization_id',
        'pan_attachment', 
        'tin_attachment', 
        'aadhar_attachment', 
        'other_documents', 
        'enter_company_org_id',
        'status',
        'document_status',
        'approver_level', 
        'revision_number',
        'revision_date',
        'created_by'
    ];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'notification' => 'array',
        'other_documents' => 'array',
    ];

    public function erpOrganizationType()
    {
        return $this->belongsTo(OrganizationType::class,'organization_type_id');
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
        return $this->hasMany(VendorItem::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function addresses() {
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

    // public function address() {
    //     return $this->morphOne(ErpAddress::class, 'addressable')
    //         ->with('city','state','country');
    // }

    public function compliances()
    {
        return $this->morphone(Compliance::class, 'morphable');
    }

    public function items()
    {
        return $this->hasMany(Item::class); 
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, );
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_terms_id');
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
            return array_map(function ($filePath) {
                return Storage::url($filePath);
            }, $filePaths);
        }
        return [];
    }

    public function paymentVoucherDetails()
    {
        return $this->morphMany(PaymentVoucherDetails::class, 'party');
    }

    public function supplier_users()
    {
        return $this->hasMany(VendorPortalUser::class,'vendor_id');
    }

    public function supplier_books()
    {
        return $this->hasMany(VendorPortalBook::class,'vendor_id');
    }

    public function other_details()
    {
        return $this -> hasOne(ErpVendorOtherDetail::class, 'vendor_id', 'id');
    }

    public function locations()
    {
        return $this -> hasMany(VendorLocation::class, 'vendor_id');
    }

    public function parentdVendor()
    {
        return $this->belongsTo(Vendor::class, 'reld_vendor_id');
    }

    public function syncLocations(array $storeIds)
    {
        $orgIds = [];
        $locIds = [];
        $subLocIds = [];
        foreach ($storeIds as $storeId) {
            if (isset($storeId['organization_id']) && isset($storeId['location_id']) && isset($storeId['store_id'])) {
                VendorLocation::updateOrCreate([
                    'vendor_id' => $this -> id,
                    'organization_id' => $storeId['organization_id'],
                    'location_id' => $storeId['location_id'],
                    'store_id' => $storeId['store_id'],
                ]);
                array_push($orgIds, $storeId['organization_id']);
                array_push($locIds, $storeId['location_id']);
                array_push($subLocIds, $storeId['store_id']);
            }
        }
        VendorLocation::where('vendor_id', $this -> id) -> whereNotIn('organization_id', $orgIds) 
        -> whereNotIn('location_id', $locIds) -> whereNotIn('store_id', $subLocIds) -> delete();

        return array(
            'status' => true,
            'message' => ''
        );
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
                $q->where(function($subQ) use ($word) {
                    $subQ->where('company_name', 'LIKE', "%{$word}%")
                        ->orWhere('vendor_code', 'LIKE', "%{$word}%");
                });
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
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }

    public function getStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->attributes['status'] ?? '');
        return ucwords($status);
    }

}
