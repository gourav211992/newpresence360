<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorLocationHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_vendor_stores_history';

    protected $fillable = [
        'source_id',
        'vendor_id',
        'store_id',
        'organization_id',
        'location_id'
    ];

    public function vendor()
    {
        return $this -> belongsTo(Vendor::class, 'vendor_id');
    }

    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
}
