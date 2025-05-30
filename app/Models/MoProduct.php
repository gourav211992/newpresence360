<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProduct extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_products';
    protected $fillable = [
        'production_bom_id',
        'mo_id',
        'item_id',
        'customer_id',
        'item_code',
        'uom_id',
        'qty',
        'pwo_mapping_id',
        'so_id',
        'so_item_id',
        'pslip_qty',
        'machine_id',
        'number_of_sheet'
    ]; 
    
    protected $appends = [
        'item_name',
        'customer_code',
        'pslip_bal_qty',
        // 'short_closed_qty'
    ];

    public function getPslipBalQtyAttribute()
    {
        // return $this->qty-$this->pslip_qty;
        return $this->qty-$this->pslip_qty-($this->short_closed_qty ?? 0);
    }

    public function getCustomerCodeAttribute()
    {
        return $this?->customer?->customer_code ?? null;
    }

    public function getItemNameAttribute()
    {
        return $this?->item?->item_name ?? null;
    }

    public function mo()
    {
        return $this->belongsTo(MfgOrder::class, 'mo_id');
    }

    public function machine()
    {
        return $this->belongsTo(ErpMachine::class, 'machine_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function soItem()
    {
        return $this->belongsTo(ErpSoItem::class, 'so_item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function attributes()
    {
        return $this->hasMany(MoProductAttribute::class,'mo_product_id');
    }
    
    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = MoProductAttribute::where('mo_product_id', $this->getAttribute('id'))
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

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class, 'customer_id');
    }

    public function pwoMapping()
    {
        return $this->belongsTo(PwoSoMapping::class,'pwo_mapping_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class,'production_bom_id');
    }

    public function productionRoute()
    {
        return $this->belongsTo(ProductionRoute::class,'production_route_id');
    }

    public function consumptions()
    {
        return $this->hasMany(PwoBomMapping::class,'pwo_mapping_id','pwo_mapping_id')->where('station_id', $this->mo->station_id);
    }

    public function pwoStationConsumption()
    {
        return $this->belongsTo(PwoStationConsumption::class,'pwo_mapping_id','pwo_mapping_id')->where('station_id', $this->mo->station_id);
    }

}
