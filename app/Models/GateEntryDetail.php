<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_details';

    protected $fillable = [
        'header_id',
        'purchase_order_item_id',
        'so_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
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
        'store_code',
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $appends = [
        'cgst_value',
        'sgst_value',
        'igst_value'
    ];

    public function gateEntryHeader()
    {
        return $this->belongsTo(GateEntryHeader::class, 'header_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function attributes()
    {
        return $this->hasMany(GateEntryAttribute::class, 'detail_id');
    }

    public function attributeHistories()
    {
        return $this->hasMany(GateEntryAttributeHistory::class, 'detail_id');
    }

    public function gateEntryItemLocations()
    {
        return $this->hasMany(GateEntryItemLocation::class, 'detail_id');
    }

    public function gateEntryItemLocationHistories()
    {
        return $this->hasMany(GateEntryItemLocationHistory::class, 'detail_id');
    }

    public function extraAmounts()
    {
        return $this->hasMany(GateEntryTed::class, 'detail_id');
    }

    public function extraAmountHistories()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'purchase_order_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function getAssessmentAmountTotalAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount - $this->header_discount_amount);
    }

    public function getAssessmentAmountItemAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount);
    }

    // After item discount
    public function getAssessmentAmountHeaderAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount);
    }

    public function getTotalItemValueAttribute()
    {
        return ($this->accepted_qty * $this->rate);
    }

    public function getTotalDiscValueAttribute()
    {
        return ($this->discount_amount + $this->header_discount_amount);
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function storeLocations()
    {
        return $this->belongsTo(GateEntryItemLocation::class, 'detail_id');
    }

    public function itemDiscount()
    {
        return $this->hasMany(GateEntryTed::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    public function itemDiscountHistory()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(GateEntryTed::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function headerDiscountHistory()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'header_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(GateEntryTed::class, 'detail_id')->where('ted_type','Tax');
    }

    public function taxHistories()
    {
        return $this->hasMany(GateEntryTedHistory::class, 'detail_id')->where('ted_type','Tax');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = GateEntryTed::where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = GateEntryTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getSgstValueAttribute()
    {
        $tedRecords = GateEntryTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = GateEntryTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getIgstValueAttribute()
    {
        $tedRecords = GateEntryTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = GateEntryTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function ted_tax()
    {
        return $this->hasOne(GateEntryTed::class,'detail_id')->where('ted_type','Tax')->latest();
    }

    // public function item_attributes_array()
    // {
    //     $itemId = $this -> getAttribute('item_id');
    //     if (isset($itemId)) {
    //         $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
    //     } else {
    //         $itemAttributes = [];
    //     }
    //     $processedData = [];
    //     foreach ($itemAttributes as $attribute) {
    //         $existingAttribute = GateEntryAttribute::where('detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
    //         if (!isset($existingAttribute)) {
    //             continue;
    //         }
    //         $attributesArray = array();
    //         $attribute_ids = [];
    //         if ($attribute -> all_checked) {
    //             $attribute_ids = ErpAttribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
    //         } else {
    //             $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
    //         }
    //         $attribute -> group_name = $attribute -> group ?-> name;
    //         $attribute -> short_name = $attribute -> group ?-> short_name;
    //         foreach ($attribute_ids as $attributeValue) {
    //                 $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
    //                 if (isset($attributeValueData))
    //                 {
    //                     $isSelected = GateEntryAttribute::where('detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attr_value', $attributeValueData -> id) -> first();
    //                     $attributeValueData -> selected = $isSelected ? true : false;
    //                     array_push($attributesArray, $attributeValueData);
    //                 }
    //         }
    //        $attribute -> values_data = $attributesArray;
    //        $attribute = $attribute -> only(['id','group_name', 'short_name' ,'values_data', 'attribute_group_id']);
    //        array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id'],'short_name' => $attribute['short_name']]);
    //     }
    //     $processedData = collect($processedData);
    //     return $processedData;
    // }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = GateEntryAttribute::where('detail_id', $this->getAttribute('id'))
        ->select(['item_attribute_id as attribute_id', 'attr_value as attribute_value_id'])
        ->get()
        ->toArray();
        foreach ($itemAttributes as $attribute) {
            $attributeIds = is_array($attribute->attribute_id) ? $attribute->attribute_id : [$attribute->attribute_id];
            $attribute->group_name = $attribute->group?->name;
            $valuesData = [];
            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = ErpAttribute::where('id', $attributeValueId)
                    ->where('status', 'active')
                    ->select('id', 'value')
                    ->first();
                if ($attributeValueData) {
                    $isSelected = collect($mappingAttributes)->contains(function ($itemAttr) use ($attribute, $attributeValueData) {
                        return $itemAttr['attribute_id'] == $attribute->id &&
                            $itemAttr['attribute_value_id'] == $attributeValueData->id;
                    });
                    $attributeValueData->selected = $isSelected;
                    $valuesData[] = $attributeValueData;
                }
            }
            $processedData[] = [
                'id' => $attribute->id,
                'group_name' => $attribute->group_name,
                'values_data' => $valuesData,
                'attribute_group_id' => $attribute->attribute_group_id,
            ];
        }
        return collect($processedData);
    }
}

