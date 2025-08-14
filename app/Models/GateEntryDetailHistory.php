<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryDetailHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_details_history';

    protected $fillable = [
        'source_id',
        'header_id',
        'purchase_order_item_id',
        'job_order_item_id',
        'po_id',
        'jo_id',
        'so_id',
        'vendor_asn_id',
        'vendor_asn_item_id',
        'sale_order_item_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'po_id',
        'jo_id',
        'uom_code',
        'store_id',
        'order_qty',
        'receipt_qty',
        'accepted_qty',
        'mrn_qty',
        'rejected_qty',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'basic_value',
        'discount_percentage',
        'discount_amount',
        'header_discount_amount',
        'net_value',
        'tax_value',
        'taxable_amount',
        'item_exp_amount',
        'header_exp_amount',
        'total_item_amount',
        'remark',
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

    public function GateEntryHeaderHistory()
    {
        return $this->belongsTo(GateEntryHeaderHistory::class, 'header_id');
    }

    public function attributes()
    {
        return $this->hasMany(GateEntryAttributeHistory::class, 'detail_id');
    }

    public function extraAmounts()
    {
        return $this->belongsTo(MrnExtraAmountHistory::class, 'mrn_detail_history_id');
    }

    public function mrnItemLocations()
    {
        return $this->hasMany(GateEntryItemLocationHistory::class, 'detail_id');
    }

    public function itemDiscount()
    {
        return $this->hasMany(MrnExtraAmountHistory::class, 'mrn_detail_history_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    public function gateEntryItemLocations()
    {
        return $this->hasMany(GateEntryItemLocation::class, 'detail_id');
    }

    public function taxes()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id')->where('ted_type','Tax');
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
            $existingAttribute = GateEntryAttributeHistory::where('detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
                        $isSelected = GateEntryAttributeHistory::where('detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attr_value', $attributeValueData -> id) -> first();
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
