<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadItemMaster extends Model
{
    use HasFactory;

    protected $table = 'upload_item_masters';


    protected $fillable = [
        'item_name',
        'item_code',
        'category',
        'subcategory',
        'hsn',
        'uom',
        'currency',
        'item_code_type',
        'cost_price',
        'sell_price',
        'type',
        'min_stocking_level',
        'max_stocking_level',
        'reorder_level',
        'min_order_qty',
        'lead_days',
        'safety_days',
        'shelf_life_days',
        'status',
        'group_id',
        'company_id',
        'organization_id',
        'attributes',
        'specifications',
        'alternate_uoms',
        'sub_type',
        'remarks',
        'batch_no',
        'user_id', 
    ];
}
