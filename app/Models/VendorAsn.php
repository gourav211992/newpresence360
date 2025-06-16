<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAsn extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;

    protected $table = "erp_vendor_asn";
}
