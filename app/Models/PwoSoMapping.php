<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwoSoMapping extends Model
{
    use HasFactory,DateFormatTrait;

    protected $table = 'erp_pwo_so_mapping';
    protected $fillable = [
        'mo_id',
        'so_id',
        'so_item_id',
        'bom_id',
        'production_route_id',
        'item_id',
        'created_by',
        'pwo_id',
        'item_code',
        'qty',
        'attributes',
        'uom_id',
        'uom_code',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'mo_product_qty'
    ];

    protected $appends = [
        'pslip_balance_qty',
        'customer_code',
    ];
    protected $casts = [
        'attributes' => 'array'
    ];
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->created_by = $user->auth_user_id;
            }
        });

        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->auth_user_id;
            }
        });

        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->auth_user_id;
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function soItem()
    {
        return $this->belongsTo(ErpSoItem::class,'so_item_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class,'so_id');
    }

    public function pwo()
    {
        return $this->belongsTo(ErpProductionWorkOrder::class,'pwo_id');
    }
    public function header()
    {
        return $this->belongsTo(ErpProductionWorkOrder::class,'pwo_id');
    }

    public function soAttributes()
    {
        return $this->hasMany(ErpSoItemAttribute::class,'so_item_id','so_item_id');
    }
    public function attributes()
    {
        return $this->hasMany(ErpSoItemAttribute::class,'so_item_id','so_item_id');
    }
    public function stations()
    {
        return $this->hasMany(PwoStationConsumption::class,'pwo_mapping_id');
    }
    public function item_attributes_array()
    {
        $itemId = $this -> getAttribute('item_id');
        $attributesRawArray = $this -> getAttribute('attributes');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            // $existingAttribute = ErpSoItemAttribute::where('so_item_id', $this -> getAttribute('so_item_id')) -> where('item_attribute_id', $attribute -> id) -> first();
            $existingAttribute = array_filter($attributesRawArray, function ($itemAttr) use($attribute) {
                return $itemAttr['item_attribute_id'] == $attribute -> id;
            });
            if (count($existingAttribute) == 0) {
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
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        // $isSelected = ErpSoItemAttribute::where('so_item_id', $this -> getAttribute('so_item_id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                        $isSelectedValue = array_filter($attributesRawArray, function ($itemAttr) use($attribute, $attributeValueData) {
                            return ($itemAttr['item_attribute_id'] == $attribute -> id && $itemAttr['attribute_name'] == $attributeValueData -> value);
                        });
                        $isSelected = false;
                        if (count($isSelectedValue) > 0) {
                            $isSelected = true;
                        }
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

    public function pwo_so_mapping_item()
    {
        return $this->hasOne(PwoSoMappingItem::class,'pi_so_mapping_id');
    }
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function getPslipBalanceQtyAttribute()
    {
        return max($this -> mo_product_qty - $this -> pslip_qty, 0);
    }
    public function getCustomerCodeAttribute()
    {
        return $this?->so?->customer?->company_name ?? '';
    }
    public function bom()
    {
        return $this->belongsTo(Bom::class,'bom_id');
    }

    public function pwoBomMapping()
    {
        return $this->hasMany(PwoBomMapping::class,'pwo_mapping_id');
    }

    public function pwoStationConsumption()
    {
        return $this->hasMany(PwoStationConsumption::class,'pwo_mapping_id');
    }
    public function getAvlStock($storeId = null)
    {
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId,null,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return min($stockBalanceQty, $this -> qty);
    }
}
