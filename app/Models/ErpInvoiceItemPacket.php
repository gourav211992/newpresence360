<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpInvoiceItemPacket extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_item_id',
        'plist_detail_id',
        'package_number'
    ];
}
