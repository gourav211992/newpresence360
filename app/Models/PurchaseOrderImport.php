<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderImport extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "erp_purchase_order_imports";

    protected $fillable = [
        'order_no',
        'procurement_type',
        'document_date',
        'vendor_code',
        'vendor_id',
        'consignee_name',
        'item_id',
        'item_code',
        'uom_id',
        'uom_code',
        'attributes',
        'qty',
        'rate',
        'delivery_date',
        'remarks',
        'is_migrated',
        'reason',
        'created_by'
    ];

    protected $casts = [
        'reason' => 'array',
        'attributes' => 'array'
    ];
}
