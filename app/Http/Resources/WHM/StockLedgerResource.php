<?php

namespace App\Http\Resources\WHM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockLedgerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'company_id' => $this->company_id,
            'organization_id' => $this->organization_id,
            'store_id' => $this->store_id,
            'sub_store_id' => $this->sub_store_id,
            'item_id' => $this->item_id,
            'hold_qty' => $this->hold_qty,
            'reserved_qty' => $this->reserved_qty,
            'confirmed_stock' => $this->confirmed_stock,
            'unconfirmed_stock' => $this->unconfirmed_stock,
            'confirmed_stock_value' => $this->confirmed_stock_value,
            'unconfirmed_stock_value' => $this->unconfirmed_stock_value,
            'putaway_pending_qty' => $this->putaway_pending_qty,
            'store' => $this->whenLoaded('location'),
            'sub_store' => $this->whenLoaded('store'),
            'item' => $this->whenLoaded('item'),
            'item_attributes' => json_decode($this->item_attributes, true),
        ];
    }
}
