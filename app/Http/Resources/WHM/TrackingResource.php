<?php

namespace App\Http\Resources\WHM;

use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "uid"=> $this->uid,
            "packet_id"=> $this->item_uid,
            "action_at"=> $this->action_at ? CommonHelper::dateTimeFormat($this->action_at) : NULL,
            "action_by"=> optional($this->actionBy)->name,
            "job_type"=> $this->job_type ? ucfirst($this->job_type) : NULL,
            "status"=> $this->status,
            "book_code"=> $this->book_code,
            "doc_no"=> $this->doc_no,
            "store_name"=> isset($this->store->store_name) ? $this->store->store_name : NULL,
            "storagePoint"=> $this->storagePoint,
        ];
    }
}
