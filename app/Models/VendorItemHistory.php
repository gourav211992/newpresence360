<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorItemHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_vendor_items_history';

    protected $fillable = [
        'source_id',
        'item_id',
        'vendor_id',
        'uom_id',
        'vendor_code',
        'item_code',
        'item_name',
        'part_number',
        'item_details',
        'cost_price',
        'group_id',
        'company_id',
        'organization_id'
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
