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
        'gate_entry_detail_id',
        'job_order_item_id',
        'po_id',
        'jo_id',
        'ge_id',
        'vendor_asn_id',
        'procurement_type',
        'vendor_asn_item_id',
        'so_id',
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
        'putaway_qty',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'accepted_inv_uom_id',
        'accepted_inv_uom_code',
        'accepted_inv_uom_qty',
        'rejected_inv_uom_id',
        'rejected_inv_uom_code',
        'rejected_inv_uom_qty',
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
        'is_inspection',
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

    public function header()
    {
        return $this->belongsTo(MrnHeader::class, 'mrn_header_id');
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

    public function item_attributes_array()
    {
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = MrnAttributeHistory::where('mrn_detail_history_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = [];
            if ($attribute -> all_checked) {
                $attribute_ids = ErpAttribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
            } else {
                $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            }
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute -> group ?-> short_name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = MrnAttributeHistory::where('mrn_detail_history_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attr_value', $attributeValueData -> id) -> first();
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'short_name' ,'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id'],'short_name' => $attribute['short_name']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }
}
