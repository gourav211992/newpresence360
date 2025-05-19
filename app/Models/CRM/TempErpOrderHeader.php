<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempErpOrderHeader extends Model
{
    use HasFactory;

    protected $table = "erp_order_headers_bk";
}
