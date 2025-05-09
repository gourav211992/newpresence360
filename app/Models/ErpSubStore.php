<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpSubStore extends Model
{
    use HasFactory, Deletable, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'station_wise_consumption',
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

}
