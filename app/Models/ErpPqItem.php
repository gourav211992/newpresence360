<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpPqItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'pq_header_id',
        'rfq_id',
        'item_id',
        'item_code',
        'item_name',
        'uom_id',
        'uom_code',
        'request_qty',
        'rate',
        'item_discount_amount',
        'header_discount_amount',
        'item_expense_amount',
        'header_expense_amount',
        'tax_amount',
        'total_item_amount',
        'rfq_item_id',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item_attributes_array()
    {
        $pqItemId = $this->getAttribute('id');
        if (isset($pqItemId)) {
            $pqItemAttributes = ErpItemAttribute::where('item_id', $this->item_id)->get();
        } else {
            $pqItemAttributes = [];
        }
        $processedData = [];
        foreach ($pqItemAttributes as $attribute) {
            $existingAttribute = ErpPqItemAttribute::where('pq_item_id', $pqItemId)
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
                    $isSelected = ErpPqItemAttribute::where('pq_item_id', $pqItemId)
                        ->where('item_attribute_id', $attribute->id)
                        ->where('attribute_value', $attributeValueData->value)
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

    public function pqItem()
    {
        return $this->belongsTo(ErpPqItem::class, 'pq_item_id');
    }

    public function pq_ids()
    {
        $pqitemIds = $this->getAttribute('pq_item_ids');
        if (isset($pqitemIds)) {
            $items =  ErpPqItem::whereIn('id', json_decode($pqitemIds))->pluck('pq_id')->toArray();
            $pq_ids = array_unique($items);
            return $pq_ids;
        }
        return [];
    }

    public function get_attributes_array()
    {
        $pqItemId = $this->getAttribute('id');

        if (!$pqItemId) {
            return collect();
        }

        // Step 1: Load all item attributes for this item
        $pqItemAttributes = ErpPqItemAttribute::where('pq_item_id', $pqItemId)->get();

        if ($pqItemAttributes->isEmpty()) {
            return collect();
        }

        // Step 2: Get all unique attribute group IDs and preload them
        $groupIds = $pqItemAttributes->pluck('attr_name')->unique()->filter();
        $attributeGroups = AttributeGroup::whereIn('id', $groupIds)->get()->keyBy('id');

        // Step 3: Process data
        $processedData = $pqItemAttributes->map(function ($attribute) use ($attributeGroups) {
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
        return $this -> belongsTo(ErpPqHeader::class, 'pq_header_id');
    }
    // public function fromErpStore()
    // {
    //     return $this -> belongsTo(ErpStore::class, 'store_id');
    // }
    // public function fromErpSubStore()
    //     {
    //         return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    //     }
    public function attributes()
    {
        return $this -> hasMany(ErpPqItemAttribute::class, 'pq_item_id');
    }
    public function teds()
    {
        return $this -> hasMany(ErpPqItemTed::class, 'pq_item_id');
    }
}
