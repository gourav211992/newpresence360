<?php

namespace App\Models;

use App\Models\PO\PoHeader;
use App\Models\PO\PoDetail;
use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnDetailHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_mrn_detail_histories';
    protected $fillable = [
        'mrn_header_history_id', 
        'mrn_header_id', 
        'purchase_order_item_id', 
        'mrn_detail_id', 
        'item_id', 
        'item_code', 
        'item_name', 
        'hsn_code', 
        'store_location', 
        'rack', 
        'shelf', 
        'bin', 
        'uom_id', 
        'order_qty', 
        'receipt_qty', 
        'accepted_qty', 
        'purchase_bill_qty',
        'pr_qty', 
        'rejected_qty',
        'pr_rejected_qty', 
        'inventory_uom', 
        'inventory_uom_id', 
        'order_qty_inventory_uom', 
        'receipt_qty_inventory_uom', 
        'accepted_qty_inventory_uom', 
        'rejected_qty_inventory_uom', 
        'rate', 
        'basic_value', 
        'discount_percentage', 
        'discount_amount', 
        'header_discount_percentage', 
        'header_discount_amount', 
        'net_value', 
        'sgst_percentage', 
        'cgst_percentage', 
        'igst_percentage', 
        'tax_value', 
        'taxable_amount', 
        'sub_total', 
        'item_exp_amount', 
        'header_exp_amount', 
        'company_currency', 
        'exchange_rate_to_company_currency', 
        'group_currency', 
        'exchange_rate_to_group_currency', 
        'selected_item', 
        'remark'
    ];

    protected $reportHeaders = [
        [
            "header" => ["mrn", "Mrn"],
            "components" => [
                "mrn_code" => 'Mrn Code',
                "mrn_type" => 'Mrn Type',
                "mrn_number" => 'Mrn Number',
                "mrn_date" => 'Mrn Date',
                "invoice_number" => 'Invoice Number',
                "invoice_date" => 'Invoice Date',
                "transporter_name" => 'Transporter Name',
                "vehicle_number" => 'Vehicle No.',
            ],
        ],
        
        [
            "header" => ["item", "Item"],
            "components" => [
                "item_name" => 'Item Name',
                "item_quantity" => 'Item Quantity',
                "item_uom" => 'Item UOM',
            ]
        ]
    ];

    public function getReportHeaders()
    {
        return $this->reportHeaders;
    }

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class);
    }

    public function mrnHeaderHistory()
    {
        return $this->belongsTo(MrnHeaderHistory::class);
    }

    public function attributes()
    {
        return $this->hasMany(MrnAttributeHistory::class, 'mrn_detail_history_id');
    }

    public function extraAmounts()
    {
        return $this->belongsTo(MrnExtraAmountHistory::class, 'mrn_detail_history_id');
    }

    public function mrnItemLocations()
    {
        return $this->belongsTo(MrnItemLocationHistory::class, 'mrn_detail_history_id');
    }

    public function itemDiscount()
    {
        return $this->hasMany(MrnExtraAmountHistory::class, 'mrn_detail_history_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(MrnExtraAmountHistory::class, 'mrn_detail_history_id')->where('ted_type','Tax');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}
