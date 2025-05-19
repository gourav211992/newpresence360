<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_vendor_stores';

    protected $fillable = [
        'vendor_id',
        'store_id'
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
