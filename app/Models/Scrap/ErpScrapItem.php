<?php

namespace App\Models\Scrap;

use App\Models\Hsn;
use App\Models\Item;
use App\Models\Unit;
use App\Models\ErpAttribute;
use App\Models\ItemAttribute;
use App\Models\Scrap\ErpScrap;
use App\Models\ErpItemAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErpScrapItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_scrap_items';

    protected $fillable = [
        'erp_scrap_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'qty',
        'rate',
        'total_cost',
        'cost_center_id',
        'cost_center_name',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'remarks',
    ];

    /**************************
     * Relationships
     **************************/

    public function scrap()
    {
        return $this->belongsTo(ErpScrap::class, 'erp_scrap_id', 'id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(ErpCostCenter::class, 'cost_center_id', 'id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpScrapItemAttribute::class, 'scrap_item_id', 'id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = ErpScrapItemAttribute::where('scrap_item_id', $this->getAttribute('id'))
            ->select(['item_attribute_id as attribute_id', 'attribute_value as attribute_value_id'])
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

    /**************************
     * Accessors / Mutators
     **************************/

    public function getFormattedQtyAttribute(): string
    {
        return number_format((float) $this->qty, 4);
    }

    public function getFormattedInventoryQtyAttribute(): string
    {
        return number_format((float) $this->inventory_uom_qty, 4);
    }
}
