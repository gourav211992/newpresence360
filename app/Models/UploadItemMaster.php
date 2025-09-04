<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
class UploadItemMaster extends Model
{
    use HasFactory,DefaultGroupCompanyOrg;

    protected $table = 'upload_item_masters';


    protected $fillable = [
        'item_name',
        'item_code',
        'category',
        'subcategory',
        'hsn',
        'uom',
        'item_code_type',
        'cost_price',
        'cost_price_currency',
        'sell_price',
        'sell_price_currency',
        'type',
        'min_stocking_level',
        'max_stocking_level',
        'reorder_level',
        'min_order_qty',
        'lead_days',
        'safety_days',
        'shelf_life_days',
        'po_positive_tolerance',
        'po_negative_tolerance',
        'so_positive_tolerance',
        'so_negative_tolerance',
        'is_serial_no',
        'is_batch_no',
        'is_expiry',
        'is_inspection',
        'inspection_checklist',
        'storage_uom',
        'storage_uom_conversion',
        'storage_uom_count',
        'storage_weight',
        'storage_volume',
        'status',
        'group_id',
        'company_id',
        'organization_id',
        'attributes',
        'specifications',
        'alternate_uoms',
        'sub_type',
        'is_traded_item',
        'is_asset',
        'is_scrap',
        'asset_category_id',
        'brand_name',
        'model_no',
        'remarks',
        'batch_no',
        'user_id', 
    ];
}
