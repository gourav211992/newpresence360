<?php

namespace App\Http\Resources\WHM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PicklistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $morphable = $this->morphable;
        $items = $morphable && isset($morphable->pickingItems) ? $morphable->pickingItems : collect();
        
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'company_id' => $this->company_id,
            'organization_id' => $this->organization_id,
            'status' => $this->status ? ucwords(str_replace('_',' ',$this->status)) : '',
            'header_id' => $this->morphable_id,
            'type' => $this->type,
            'store_id' => $this->store_id,
            'store_name' => optional($this->store)->store_name,
            'sub_store_id' => $this->sub_store_id,
            'sub_store_name' => optional($this->subStore)->name,
            'is_warehouse_required' => optional($this->subStore)->is_warehouse_required,
            'doc_no' => optional($morphable)->document_number,
            'doc_date' => optional($morphable)->document_date,
            'book_id' => optional($morphable)->book_id,
            'series' => optional($morphable)->book_code,
            'total_item' => $items ? $items->count() : 0,
            'total_packets' => $this->morphable->total_quantity,
        ];
    }
}
