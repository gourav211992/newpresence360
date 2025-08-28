<?php

namespace App\Models\Scrap;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function item()
    {
        return $this->belongsTo(ErpItem::class, 'item_id', 'id');
    }

    public function hsn()
    {
        return $this->belongsTo(ErpHsn::class, 'hsn_id', 'id');
    }

    public function uom()
    {
        return $this->belongsTo(ErpUom::class, 'uom_id', 'id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(ErpUom::class, 'inventory_uom_id', 'id');
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
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this->item_id)->get();
        } else {
            $itemAttributes = [];
        }
        foreach ($itemAttributes as $attribute) {
            $attributesArray = array();
            $attribute_ids = json_decode($attribute->attribute_id);
            $attribute->group_name = $attribute->group?->name;
            foreach ($attribute_ids as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue)->select('id', 'value')->where('status', 'active')->first();
                if (isset($attributeValueData)) {
                    $attributeValueData->selected = false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
            $attribute->values_data = $attributesArray;
            $attribute->only(['id', 'group_name', 'values_data']);
        }
        return $itemAttributes;
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
