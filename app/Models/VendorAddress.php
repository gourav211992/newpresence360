<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class VendorAddress  extends Model
{
    use HasFactory;

    protected $table = 'erp_vendor_addresses';
    protected $fillable = [
        'vendor_id',
        'is_billing',
        'is_shipping'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function addresses()
    {
        return $this->morphOne(ErpAddress::class, 'addressable');
    }
    
  
}
