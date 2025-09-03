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
        // dd($morphable);
        $itemUniqueCodes = $this->whenLoaded('itemUniqueCodes');

        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'company_id' => $this->company_id,
            'organization_id' => $this->organization_id,
            'status' => $this->status ? ucwords(str_replace('_',' ',$this->status)) : '',
            'header_id' => $this->morphable_id,
            'reference_type' => $this->reference_type,
            'reference_no' => $this->reference_no,
            'store_id' => $this->store_id,
            'store_name' => optional($this->store)->store_name,
            'sub_store_id' => $this->sub_store_id,
            'sub_store_name' => optional($this->subStore)->name,
            'doc_no' => optional($morphable)->document_number,
            'doc_date' => optional($morphable)->document_date,
            'book_id' => optional($morphable)->book_id,
            'series' => optional(optional($morphable)->book)->book_code,
            'consignment_no' => optional($morphable)->consignment_no,
            'supplier_invoice_no' => optional($morphable)->supplier_invoice_no,
            'is_warehouse_required' => optional($this->subStore)->is_warehouse_required,
            'total_item' => $itemUniqueCodes ? $itemUniqueCodes->unique('item_id')->count() : 0,
            'total_packets' => $itemUniqueCodes ? $itemUniqueCodes->count() : 0,
        ];
    }
}
