<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPickupItem extends Model
{
    use HasFactory;
     protected $table = 'erp_pickup_items';
    protected $fillable = [
        'pickup_schedule_id',
        'item_id',
        'item_code',
        'item_name',
        'uom_id',
        'uom_code',
        'customer_id',
        'uid',
        'customer_name',
        'customer_email',
        'customer_phone',
        'type',
        'delivery_cancelled',
        'qty',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function get_attributes_array()
    {
        $pickupItemId = $this->getAttribute('id');

        if (!$pickupItemId) {
            return collect();
        }

        // Step 1: Load all item attributes for this item
        $pickupItemAttributes = ErpPickupItemAttribute::where('pickup_item_id', $pickupItemId)->get();

        if ($pickupItemAttributes->isEmpty()) {
            return collect();
        }

        // Step 2: Get all unique attribute group IDs and preload them
        $groupIds = $pickupItemAttributes->pluck('attr_name')->unique()->filter();
        $attributeGroups = AttributeGroup::whereIn('id', $groupIds)->get()->keyBy('id');

        // Step 3: Process data
        $processedData = $pickupItemAttributes->map(function ($attribute) use ($attributeGroups) {
            $group = $attributeGroups->get($attribute->attr_name);

            return [
                'id' => $attribute->item_attribute_id,
                'group_name' => $group?->name,
                'short_name' => $group?->short_name,
                'values_data' => [
                    [
                        'id' => $attribute->attr_value,
                        'value' => $attribute->attribute_value,
                        'selected' => true,
                    ]
                ],
                'attribute_group_id' => $group?->id,
            ];
        });

        return $processedData;
    }


    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }

    public function header()
    {
        return $this -> belongsTo(ErpPickupSchedule::class, 'pickup_schedule_id');
    }
    
    public function item_attributes_array()
    {
        $pickupItemId = $this->getAttribute('id');
        if (isset($pickupItemId)) {
            $pickupItemAttributes = ErpItemAttribute::where('item_id', $this->item_id)->get();
        } else {
            $pickupItemAttributes = [];
        }
        $processedData = [];
        foreach ($pickupItemAttributes as $attribute) {
            $existingAttribute = ErpPickupItemAttribute::where('pickup_item_id', $pickupItemId)
                ->where('item_attribute_id', $attribute->id)
                ->first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = [];
            $attribute_ids = [];
            if ($attribute->all_checked) {
                $attribute_ids = Attribute::where('attribute_group_id', $attribute->attribute_group_id)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            } else {
                $attribute_ids = $attribute->attribute_id ? json_decode($attribute->attribute_id) : [];
            }
            $attribute->group_name = $attribute->group?->name;
            $attribute->short_name = $attribute->group?->short_name;

            foreach ($attribute_ids as $attributeValue) {
                $attributeValueData = Attribute::where('id', $attributeValue)
                    ->select('id', 'value')
                    ->where('status', 'active')
                    ->first();
                if (isset($attributeValueData)) {
                    $isSelected = ErpPickupItemAttribute::where('pickup_item_id', $pickupItemId)
                        ->where('item_attribute_id', $attribute->id)
                        ->where('attr_value', $attributeValueData->id)
                        ->first();
                    $attributeValueData->selected = $isSelected ? true : false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
            $attribute->values_data = $attributesArray;
            $attribute = $attribute->only(['id', 'group_name', 'short_name', 'values_data', 'attribute_group_id']);
            array_push($processedData, [
                'id' => $attribute['id'],
                'group_name' => $attribute['group_name'],
                'short_name' => $attribute['short_name'],
                'values_data' => $attributesArray,
                'attribute_group_id' => $attribute['attribute_group_id'],
            ]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }


}
