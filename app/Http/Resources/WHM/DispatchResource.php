<?php

namespace App\Http\Resources\WHM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    
    public function toArray(Request $request): array
    {
        $morphable = $this->whenLoaded('morphable'); // ensure it's loaded safely
        $items = $morphable && isset($morphable->items) ? $morphable->items : collect();
        // $itemUniqueCodes = $this->whenLoaded('itemUniqueCodes');

        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'company_id' => $this->company_id,
            'organization_id' => $this->organization_id,
            'status' => $this->status ? ucwords(str_replace('_',' ',$this->status)) : '',
            'header_id' => $this->morphable_id,
            'store_id' => $this->store_id,
            'store_name' => optional($this->store)->store_name,
            'sub_store_id' => $this->sub_store_id,
            'sub_store_name' => optional($this->subStore)->name,
            'doc_no' => optional($morphable)->document_number,
            'doc_date' => optional($morphable)->document_date,
            'book_id' => optional($morphable)->book_id,
            'series' => optional(optional($morphable)->book)->book_code,
            'total_item' => $items ? $items->count() : 0,
            'total_packets' => $items ? $items->sum('inventory_uom_qty') : 0,
        ];
    }
}
