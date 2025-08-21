<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVendorPurchaseSummary extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;
    protected $table = "erp_vendor_purchase_summary";

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'vendor_id',
        'fy_id',
        'fy_code',
        'currency_id',
        'total_purchase_value',
        'total_return_value'
    ];
}
