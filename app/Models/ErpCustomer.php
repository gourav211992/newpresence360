<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use App\Models\CRM\ErpDiary;
use App\Models\CRM\ErpIndustry;
use App\Models\CRM\ErpLeadContacts;
use App\Models\CRM\ErpLeadSource;
use App\Models\CRM\ErpMeetingStatus;
use App\Models\CRM\ErpProductCategory;
use App\Models\CRM\ErpSupplySplit;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpCustomer extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, Deletable, SoftDeletes;
    protected $appends = ['full_address'];


    public function currency()
    {
        return $this->belongsTo(ErpCurrency::class, 'currency_id', 'id');
    }

    public function payment_terms()
    {
        return $this -> belongsTo(ErpPaymentTerm::class, 'payment_terms_id', 'id');
    }

    public function erpDiaries()
    {
        return $this->hasMany(ErpDiary::class, 'customer_code', 'customer_code');
    }

    public function latestDiary()
    {
        return $this->hasOne(ErpDiary::class, 'customer_code', 'customer_code')
                ->orderBy('id', 'desc');
    }
    // public function erpDiaries()
    // {
    //     return $this->hasMany(ErpDiary::class, 'customer_id');
    // }

    public function salesRepresentative()
    {
        return $this->belongsTo(Employee::class, 'sales_person_id', 'id');
    }

    public function productCategory()
    {
        return $this->belongsTo(ErpProductCategory::class, 'product_category_id', 'id');
    }

    public function leadSource()
    {
        return $this->belongsTo(ErpLeadSource::class, 'lead_source_id', 'id');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function getFullAddressAttribute()
    {
        $addressParts = [
            $this->getAttribute('customer_address'),
            $this->country?->name,
            $this->state?->name,
            $this->city?->name,
            $this->customer_pincode ? 'Pincode - ' . $this->customer_pincode : null,
        ];
        return implode(', ', array_filter($addressParts));
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function orderHeader()
    {
        return $this->hasOne(ErpOrderHeader::class, 'customer_code','customer_code');
    }

    public function meetingStatus()
    {
        return $this->belongsTo(ErpMeetingStatus::class,'lead_status','alias');
    }

    public function industry()
    {
        return $this->belongsTo(ErpIndustry::class,'industry_id','id');
    }

    public function supplierSplit()
    {
        return $this->hasMany(ErpSupplySplit::class,'customer_code','customer_code');
    }

    public function leadContacts()
    {
        return $this->hasMany(ErpLeadContacts::class,'customer_code','customer_code');
    }
    
    public function compliances()
    {
        return $this->morphone(Compliance::class, 'morphable');
    }

}
