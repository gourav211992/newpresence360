<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRfqItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'rfq_header_id',
        'item_id',
        'item_code',
        'item_name',
        'uom_id',
        'uom_code',
        'request_qty',
        'pi_item_ids',
        'rfq_item_id',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = [
        'pq_balance_qty'
    ];
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function getPqBalanceQtyAttribute()
    {
        return max(($this->request_qty) - $this->pq_qty, 0);
    }

    public function item_attributes_array()
    {
        $rfqItemId = $this->getAttribute('id');
        if (isset($rfqItemId)) {
            $rfqItemAttributes = ErpItemAttribute::where('item_id', $this->item_id)->get();
        } else {
            $rfqItemAttributes = [];
        }
        $processedData = [];
        foreach ($rfqItemAttributes as $attribute) {
            $existingAttribute = ErpRfqItemAttribute::where('rfq_item_id', $rfqItemId)
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
                    $isSelected = ErpRfqItemAttribute::where('rfq_item_id', $rfqItemId)
                        ->where('item_attribute_id', $attribute->id)
                        ->where('attr_value', $attributeValueData->value)
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

    public function piItem()
    {
        return $this->belongsTo(PiItem::class, 'pi_item_id');
    }

    public function pi_ids()
    {
        $piitemIds = $this->getAttribute('pi_item_ids');
        if (isset($piitemIds)) {
            $items =  PiItem::whereIn('id', json_decode($piitemIds))->pluck('pi_id')->toArray();
            $pi_ids = array_unique($items);
            return $pi_ids;
        }
        return [];
    }

    public function get_attributes_array()
    {
        $rfqItemId = $this->getAttribute('id');

        if (!$rfqItemId) {
            return collect();
        }

        // Step 1: Load all item attributes for this item
        $rfqItemAttributes = ErpRfqItemAttribute::where('rfq_item_id', $rfqItemId)->get();

        if ($rfqItemAttributes->isEmpty()) {
            return collect();
        }

        // Step 2: Get all unique attribute group IDs and preload them
        $groupIds = $rfqItemAttributes->pluck('attr_name')->unique()->filter();
        $attributeGroups = AttributeGroup::whereIn('id', $groupIds)->get()->keyBy('id');

        // Step 3: Process data
        $processedData = $rfqItemAttributes->map(function ($attribute) use ($attributeGroups) {
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
        return $this -> belongsTo(ErpRfqHeader::class, 'rfq_header_id');
    }
    // public function fromErpStore()
    // {
    //     return $this -> belongsTo(ErpStore::class, 'store_id');
    // }
    // public function fromErpSubStore()
    //     {
    //         return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    //     }

    public function pqItems()
    {
        return $this->hasMany(ErpPqItem::class,'rfq_item_id','id');
    }
    public function attributes()
    {
        return $this -> hasMany(ErpRfqItemAttribute::class, 'rfq_item_id');
    }
}
