<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress  extends Model 
{
    use HasFactory;

    protected $table = 'erp_customer_addresses';
    protected $fillable = [
        'customer_id',
        'is_billing',
        'is_shipping'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function addresses()
    {
        return $this->morphOne(ErpAddress::class, 'addressable');
    }
    
  
}
