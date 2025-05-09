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
        'pslip_qty'
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
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = MoProductAttribute::where('mo_product_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = $attribute -> attribute_id ? ($attribute -> attribute_id) : [];
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute -> group ?-> short_name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = MoProductAttribute::where('mo_product_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> id) -> first();
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
