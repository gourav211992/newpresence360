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
        'mo_product_qty',
        'store_id',
        'main_so_item',
        'jo_qty'
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

    public function mo()
    {
        return $this->belongsTo(MfgOrder::class,'mo_id');
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
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = $this->getAttribute('attributes');
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
                        return $itemAttr['item_attribute_id'] == $attribute->id &&
                            $itemAttr['attribute_id'] == $attributeValueData->id;
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

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    # make getter for number of sheet
    // public function getNumberOfSheetAttribute()
    // {
    //     // return $this->pwo_so_mapping_item?->number_of_sheet ?? 0;
    // }
}
