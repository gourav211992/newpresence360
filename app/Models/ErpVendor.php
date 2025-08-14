<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVendor extends Model
{
    use HasFactory;
 
    public function other_details()
    {
        return $this -> hasOne(ErpVendorOtherDetail::class, 'vendor_id', 'id');
    }
}
