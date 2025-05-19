<?php

namespace App\Models\CRM;

use App\Models\ErpCustomer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class ErpCustomerTarget  extends Model
{

    protected $fillable = [
        'customer_code',
        'customer_id',
        'channel_partner_name',
        'location_code',
        'location',
        'sales_rep_code',
        'ly_sale',
        'cy_sale',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec',
        'jan',
        'feb',
        'mar',
        'year',
        'total_target',
        'organization_id',
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
