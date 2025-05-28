<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;



class Customer  extends Model
{
    use HasFactory,Deletable,SoftDeletes,DefaultGroupCompanyOrg;

    protected $table = 'erp_customers';

     protected $fillable = [
        'organization_type_id',
        'category_id',
        'item_id',
        'subcategory_id',
        'customer_code',
        'customer_type',
        'company_name',
        'customer_initial'=>'required',
        'customer_code_type'=>'nullable',
        'customer_initial',
        'customer_code_type',
        'display_name',
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
        'reld_customer_id',
        'email',
        'phone',
        'mobile',
        'whatsapp_number',
        'notification',
        'pan_number',
        'tin_number',
        'aadhar_number',
        'opening_balance',
        'ledger_id',
        'ledger_group_id',
        'sales_person_id',
        'pricing_type',
        'credit_limit',
        'credit_days',
        'on_account_required',
        'interest_percent',
        'status',
        'created_by',
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
        return $this->morphone(Compliance::class, 'morphable');
    }

    public function approvedItems()
    {
        return $this->hasMany(CustomerItem::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class,'payment_terms_id');
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class);
    }
    public function payment_terms()
    {
        return $this->belongsTo(PaymentTerm::class);
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
    public function other_details()
    {
        return $this -> hasOne(ErpCustomerOtherDetails::class, 'customer_id', 'id');
    }

    public function sales_person()
    {
        return $this->belongsTo(Employee::class);
    }

    public function paymentVoucherDetails()
    {
        return $this->morphMany(PaymentVoucherDetails::class, 'party');
    }

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
}
