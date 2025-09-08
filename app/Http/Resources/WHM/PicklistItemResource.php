<?php

namespace App\Http\Resources\WHM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PicklistItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pl_item_id' => $this->id,
            'pl_header_id' => $this->pl_header_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'item_code' => $this->item_code,
            'quantity' => $this->quantity,
            'attributes' => $this->attributes,
            'storage_points' => $this->storage_points ?? [],
        ];
    }
}
