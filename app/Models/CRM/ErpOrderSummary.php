<?php

namespace App\Models\CRM;

use App\Models\ErpCustomer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class ErpOrderSummary  extends Model
{

    protected $fillable = [
        'organization_id',
        'customer_id',
        'date',
        'total_order_value',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class,'customer_code','customer_code');
    }
}
