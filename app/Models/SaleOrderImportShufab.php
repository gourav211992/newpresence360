<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrderImportShufab extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "erp_sale_order_import_shufab";

    protected $fillable = [
        'order_no',
        'document_date',
        'customer_code',
        'customer_id',
        'consignee_name',
        'item_id',
        'item_code',
        'uom_id',
        'uom_code',
        'size_1',
        'size_2',
        'size_3',
        'size_4',
        'size_5',
        'size_6',
        'size_7',
        'size_8',
        'size_9',
        'size_10',
        'size_11',
        'size_12',
        'size_13',
        'size_14',
        'rate',
        'delivery_date',
        'remarks',
        'is_migrated',
        'reason',
        'created_by'
    ];

    protected $casts = [
        'reason' => 'array'
    ];

    
    public function dynamic_fields()
    {
        return $this -> hasMany(SoImportShufabDynField::class, 'import_id');
    }
}
