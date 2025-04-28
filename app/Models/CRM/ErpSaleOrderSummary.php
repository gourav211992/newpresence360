<?php

namespace App\Models\CRM;

use App\Models\ErpCustomer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class ErpSaleOrderSummary  extends Model
{

    protected $fillable = [
        'organization_id',
        'customer_id',
        'customer_code',
        'date',
        'total_sale_value',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class,'customer_code','customer_code');
    }

    public function customerTarget()
    {
        return $this->belongsTo(ErpCustomerTarget::class,'customer_code','customer_code');
    }
}
