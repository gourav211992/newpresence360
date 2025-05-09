<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRateContractItemHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_rate_contract_items_history';
    protected $fillable=[
        'id',
        'source_id',
        'rate_contract_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'moq',
        'lead_time',
        'from_qty',
        'to_qty',
        'rate',
        'currency_id',
        'currency_code',
        'from_date',
        'to_date',
        'remarks',
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

    public function source()
    {
        return $this->belongsTo(ErpRateContractItem::class, 'source_id', 'id');
    }
    public function rateContract()
    {
        return $this->belongsTo(ErpRateContract::class, 'rate_contract_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function item_attributes()
    {
        return $this -> hasMany(ErpRateContractItemAttribute::class, 'rate_contract_item_id','id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');

        $itemAttributes = isset($itemId)
            ? ErpItemAttribute::where('item_id', $itemId)->get()
            : collect();

        $processedData = [];
        $rateContractItemId = $this->getAttribute('id');

        foreach ($itemAttributes as $attribute) {
            $existingAttribute = ErpRateContractItemAttribute::where('rate_contract_item_id', $rateContractItemId)
                ->where('item_attribute_id', $attribute->id)
                ->first();

            $attributeIds = [];

            // Get all attribute IDs
            if (!empty($attribute->all_checked)) {
                $attributeIds = ErpAttribute::where('attribute_group_id', $attribute->attribute_group_id)
                    ->pluck('id')
                    ->toArray();
            } else {
                $attributeIds = $attribute->attribute_id ? json_decode($attribute->attribute_id, true) : [];
            }

            $valuesData = [];

            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = ErpAttribute::where('id', $attributeValueId)
                    ->where('status', 'active')
                    ->select('id', 'value', 'attribute_group_id')
                    ->first();

                if ($attributeValueData) {
                    $group = ErpAttributeGroup::select('id', 'name')->find($attributeValueData->attribute_group_id);

                    $isSelected = ErpRateContractItemAttribute::where('rate_contract_item_id', $rateContractItemId)
                        ->where('item_attribute_id', $attribute->id)
                        ->where('attribute_value', $attributeValueData->value)
                        ->exists();

                    $attributeValueData->selected = $isSelected;
                    $attributeValueData->group_name = $group?->name ?? '';
                    $attributeValueData->attribute_group_id = $group?->id;
                    $attributeValueData->attribute_id = $attributeValueData->id;
                    $attributeValueData->attribute_name = $attributeValueData->value;

                    $valuesData[] = $attributeValueData;
                }
            }

            // ✅ Only push to processedData if valuesData is not empty
            if (!empty($valuesData)) {
                $processedData[] = [
                    'id' => $attribute->id,
                    'group_name' => $attribute->group?->name ?? '',
                    'values_data' => $valuesData,
                    'attribute_group_id' => $attribute->attribute_group_id ?? null,
                ];
            }
        }

        return collect($processedData);
    }

}
