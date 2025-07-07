<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use App\Models\JobOrder\JoProduct;

use App\Traits\DateFormatTrait;
use App\Traits\DynamicFieldsTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAsnItem extends Model
{
    use HasFactory, DateFormatTrait, DynamicFieldsTrait ,FileUploadTrait;

    protected $table = 'erp_vendor_asn_items';
    
    protected $fillable = [
        'vendor_asn_id', 
        'po_item_id', 
        'jo_prod_id', 
        'item_id', 
        'item_code', 
        'item_name', 
        'hsn_id', 
        'hsn_code', 
        'uom_id', 
        'uom_code', 
        'order_qty', 
        'supplied_qty', 
        'balance_qty', 
        'grn_qty', 
        'expense_advise_qty', 
        'invoice_quantity', 
        'short_close_qty', 
        'ge_qty', 
        'so_id', 
        'inventory_uom_id', 
        'inventory_uom_code', 
        'inventory_uom_qty', 
        'rate', 
        'item_discount_amount', 
        'header_discount_amount', 
        'tax_amount', 
        'expense_amount', 
        'company_currency_id', 
        'company_currency_exchange_rate', 
        'group_currency_id', 
        'group_currency_exchange_rate', 
        'remarks', 
        'delivery_date'
    ];

    public function po_item()
    {
        return $this->belongsTo(PoItem::class, 'po_item_id', 'id');
    }

    public function jo_item()
    {
        return $this->belongsTo(JoProduct::class, 'jo_prod_id', 'id');
    }

    public function vendorAsn()
    {
        return $this->belongsTo(VendorAsn::class, 'vendor_asn_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
