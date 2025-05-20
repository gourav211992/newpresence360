<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempErpOrderItem extends Model
{
    use HasFactory;

    protected $table = "erp_order_items_bk";
}
