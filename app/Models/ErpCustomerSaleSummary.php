<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpCustomerSaleSummary extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;
    protected $table = "erp_customer_sale_summary";

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'customer_id',
        'fy_id',
        'fy_code',
        'currency_id',
        'total_invoice_value',
        'total_return_value'
    ];
}
