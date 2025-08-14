<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class UploadVendorMaster extends Model
{
    use HasFactory, SoftDeletes,DefaultGroupCompanyOrg;

    protected $table = 'upload_vendor_masters';

    protected $fillable = [
        'company_name', 
        'vendor_initial',
        'vendor_code', 
        'category', 
        'subcategory', 
        'currency', 
        'payment_term', 
        'vendor_type', 
        'vendor_sub_type', 
        'organization_type', 
        'vendor_code_type',
        'country', 
        'state', 
        'city', 
        'address', 
        'pin_code', 
        'email', 
        'phone', 
        'mobile', 
        'whatsapp_number', 
        'notification_mode', 
        'pan_number', 
        'tin_number', 
        'aadhar_number', 
        'ledger_code', 
        'ledger_group', 
        'credit_limit', 
        'credit_days', 
        'gst_applicable', 
        'gstin_no', 
        'gst_registered_name', 
        'gstin_registration_date', 
        'tds_applicable', 
        'wef_date', 
        'tds_certificate_no', 
        'tds_tax_percentage', 
        'tds_category', 
        'tds_value_cab', 
        'tan_number', 
        'msme_registered', 
        'msme_no', 
        'msme_type', 
        'status', 
        'group_id', 
        'company_id', 
        'organization_id', 
        'remarks', 
        'batch_no', 
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
