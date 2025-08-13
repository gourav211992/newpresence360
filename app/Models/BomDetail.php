<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomDetail extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_details';

    protected $fillable = [
        'bom_id',
        'uom_id',
        'item_id',
        'item_code',
        'qty',
        'item_cost',
        'item_value',
        'superceeded_cost',
        'waste_perc',
        'waste_amount',
        'overhead_amount',
        'total_amount',
        'section_id',
        'sub_section_id',
        'section_name',
        'sub_section_name',
        'station_id',
        'station_name',
        'remark',
        'vendor_id',
        'sequence_no',
        'is_inherit_batch_item',
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'section' => 'section_id',
        'subSection' => 'sub_section_id',
        'station' => 'station_id'
    ];

    public function getQtnAttribute()
    {
        $formattedQty = sprintf("%.6f", (float) $this->attributes['qty']);
        return $formattedQty;
    }

    public function getSuperceededCostAttribute()
    {
        $formattedQty = sprintf("%.6f", (float) $this->attributes['superceeded_cost']);
        return $formattedQty;
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function attributes()
    {
        return $this->hasMany(BomAttribute::class,'bom_detail_id');
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
            $existingAttribute = BomAttribute::where('bom_detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
            $attributesArray = array();
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach (isset($attribute_ids) ? $attribute_ids : [] as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                if (isset($attributeValueData))
                {
                    $isSelected = BomAttribute::where('bom_detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> id) -> first();
                    $attributeValueData -> selected = $isSelected ? true : false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);

        }
        $processedData = collect($processedData);
        return $processedData;
    }

    # only get selected attribute for view purpose
    public function selected_item_attributes_array()
    {
        return $this->item_attributes_array()
            ->map(function ($group) {
                $selected = collect($group['values_data'])
                    ->first(function ($attr) {
                        return $attr->selected === true;
                    });
                if ($selected) {
                    return [
                        'attribute_group_id' => $group['attribute_group_id'],
                        'attribute_group_name' => $group['group_name'],
                        'attribute_id' => $selected->id,
                        'attribute_value' => $selected->value,
                    ];
                }
                return null;
            })
            ->filter()
            ->values();
    }

    public function getStockBalanceQty($storeId = null)
    {
        $itemId = $this -> getAttribute('item_id');
        $selectedAttributeIds = [];
        if($this->selected_item_attributes_array()->count()){
            $selectedAttributeIds = $this->selected_item_attributes_array()->pluck('attribute_id')->toArray();
        }
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,null,$storeId,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        $stockBalanceQty = ItemHelper::convertToAltUom($this->getAttribute(('item_id')),$this->getAttribute('uom_id'),(float)$stockBalanceQty);
        return $stockBalanceQty;
    }

    public function overheads()
    {
        return $this->hasMany(BomOverhead::class,'bom_detail_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class,'bom_id');
    }

    public function subSection()
    {
        return $this->belongsTo(ProductSectionDetail::class, 'sub_section_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function section()
    {
        return $this->belongsTo(ProductSection::class, 'section_id');
    }

    /*Bom header item*/
    public function bomHeader()
    {
        return $this->hasOne(Bom::class, 'item_id', 'item_id');
    }

    public function bomHeaders()
    {
        return $this->hasMany(Bom::class, 'item_id', 'item_id');
    }

    public function norm()
    {
        return $this->hasOne(BomNormsCalculation::class,'bom_detail_id');
    }

}
