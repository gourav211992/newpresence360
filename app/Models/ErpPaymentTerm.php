<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;

class ErpPaymentTerm extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;
}
