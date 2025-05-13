<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCustomerDetail extends Model
{
    use HasFactory;
    protected $table = 'erp_cash_customer_details';
    protected $fillable = [
        'customer_id',
        'email',
        'name',
        'phone_no',
        'gstin'
    ];

    public function customer()
    {
        return $this -> belongsTo(Customer::class, 'customer_id');
    }
    public function billing_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }
    public function shipping_address()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id');
    }
}
