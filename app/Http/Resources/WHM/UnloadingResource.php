<?php

namespace App\Http\Resources\WHM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnloadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    
    public function toArray(Request $request): array
    {
        $morphable = $this->whenLoaded('morphable'); // ensure it's loaded safely

        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'company_id' => $this->company_id,
            'organization_id' => $this->organization_id,
            'status' => $this->status ? ucwords(str_replace('_',' ',$this->status)) : '',
            'header_id' => $this->morphable_id,
            'store_id' => optional($morphable)->store_id,
            'doc_no' => optional($morphable)->document_number,
            'doc_date' => optional($morphable)->document_date,
            'book_id' => optional($morphable)->book_id,
            'series' => optional(optional($morphable)->book)->book_code,
            'consignment_no' => optional($morphable)->consignment_no,
            'supplier_invoice_no' => optional($morphable)->supplier_invoice_no,
            'total_item' => optional($morphable->items ?? null)->count() ?? 0,
            'total_packets' => optional($morphable->items ?? null)->sum('accepted_qty') ?? 0,
        ];
    }
}
