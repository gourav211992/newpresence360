<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class VendorItem extends Model
{
    use SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_vendor_items';

    protected $fillable = [
        'item_id',
        'vendor_id',
        'vendor_code',
        'item_code',
        'item_name',
        'part_number',
        'item_details',
        'cost_price',
        'uom_id',
        'organization_id', 
        'group_id',       
        'company_id'       
    ];


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class); 
    }

    public function approvedVendors()
    {
        return $this->hasMany(VendorItem::class);
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}
