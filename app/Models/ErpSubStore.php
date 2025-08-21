<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\SubStore\Constants as SubStoreConstants;

class ErpSubStore extends Model
{
    use HasFactory, Deletable, SoftDeletes;

    protected $table = 'erp_sub_stores';

    protected $fillable = [
        'code',
        'name',
        'type',
        'station_wise_consumption',
        'is_warehouse_required',
        'uic_scan_for_issue',
        'status'
    ];

    public function parents()
    {
        return $this -> hasMany(ErpSubStoreParent::class, 'sub_store_id');
    }

    public function store_names()
    {
        $stores = $this -> parents;
        $storesName = '';
        foreach ($stores as $storeKey => $store) {
            $storesName .=  (($storeKey === 0 ? '' : ', ') . $store ?-> store?-> store_name);
        }
        return $storesName;
    }

    public function vendor_stores()
    {
        return $this -> hasMany(VendorLocation::class, 'store_id');
    }
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'erp_employee_sub_store', 'location_id', 'employee_id');
    }
    public function sub_type()
    {
        return $this -> hasOne(SubStoreType::class, 'sub_store_id');
    }
}
