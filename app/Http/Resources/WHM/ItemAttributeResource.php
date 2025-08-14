<?php

namespace App\Http\Resources\WHM;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemAttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Decode the JSON string
        $attributeIds = json_decode($this->attribute_id, true);

        if (!is_array($attributeIds)) {
            $attributeIds = [];
        }

        // Fetch related attributes
        $attributes = Attribute::whereIn('id', $attributeIds)
            ->select('id', 'value')
            ->get();

        return [
            'item_id'    => $this->item_id,
            'group_id'    => $this->attribute_group_id,
            'group_name'  => optional($this->group)->name,
            'attributes'  => $attributes
        ];
    }
}
