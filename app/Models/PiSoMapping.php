<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiSoMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_id',
        'so_item_id',
        'item_id',
        'created_by',
        'bom_id',
        'bom_detail_id',
        'item_code',
        'order_qty',
        'bom_qty',
        'qty',
        'pi_item_qty',
        'attributes',
        'child_bom_id',
        'vendor_id'
    ];

    protected $table = 'erp_pi_so_mapping';

    protected $appends = [
        'bom_item_qty'
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function soItem()
    {
        return $this->belongsTo(ErpSoItem::class,'so_item_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class,'so_id');
    }

    public function bomDetail()
    {
        return $this->belongsTo(BomDetail::class,'bom_detail_id');
    }

    public function soAttributes()
    {
        return $this->hasMany(ErpSoItemAttribute::class,'so_item_id','so_item_id');
    }

    public function getBomItemQtyAttribute()
    {
        $qty = 0;
        if($this?->bomDetail) {
            $qty = floatval($this->bomDetail->qty);
            return $qty;  
        }
        return $qty;
    }

    public function pi_so_mapping_item()
    {
        return $this->hasOne(PiSoMappingItem::class,'pi_so_mapping_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = is_array($this->getAttribute('attributes')) ? $this->getAttribute('attributes') : json_decode($this->getAttribute('attributes'),true);
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
                            $itemAttr['attribute_value'] == $attributeValueData->id;
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

    // public function getPendingPoAttribute()
    // {   
    //     $itemId       = $this->item_id;
    //     $selectedAttr = $this->attributes()->get();
    //     $uomId        = $this->uom_id;
    //     $storeId      = $this?->pi?->store_id;
    //     return InventoryHelper::getPendingPo($itemId, $uomId, $selectedAttr, $storeId);
    // }
}
