<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;

use App\Traits\Deletable;

class ErpStore extends Model
{
    use HasFactory,Deletable,DefaultGroupCompanyOrg;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'erp_stores';
    
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'store_code',
        'store_name',
        'store_location_type',
        'status',
        'contact_person', 
        'contact_phone_no',
        'contact_email'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function racks()
    {
        return $this->hasMany(ErpRack::class);
    }
    public function store_racks()
    {
        return $this->hasMany(ErpRack::class)->select('erp_racks.id', 'erp_store_id', 'rack_code');
    }
    
    public function shelfs()
    {
        return $this->hasManyThrough(ErpShelf::class, ErpRack::class);
    }
    
    public function bins()
    {
        return $this->hasMany(ErpBin::class);
    }
    public function store_bins()
    {
        return $this->hasMany(ErpBin::class) -> select('id', 'erp_store_id', 'bin_code');
    }

    public function address() {
        return $this->morphOne(ErpAddress::class, 'addressable');
    }

    public function vendor_stores()
    {
        return $this -> hasMany(VendorLocation::class, 'store_id');
    }

    public function subStores()
    {
        return $this->belongsToMany(
            ErpSubStore::class,
            'erp_sub_store_parents',
            'store_id',
            'sub_store_id'
        );
    }

    public function getCostCentersAttribute()
    {
        return CostCenter::where('status', 'active')
        ->orderByDesc('id') 
        ->get() 
        ->filter(function ($costCenter) {
            return in_array($this->id, $costCenter->locations ?? []);
        });
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'erp_employee_stores', 'location_id', 'employee_id');
    }
}
