<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Models\GateEntryDetail;
use App\Models\GateEntryHeader;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class WhmJob
{
    public function createJob($id, $namespace)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);

        // Step 2: Get or Create Job (prevents duplicate job on edit)
        $job = ErpWhmJob::firstOrCreate(
            [
                'morphable_type' => $namespace,
                'morphable_id' => $header->id,
            ],
            [
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'status' => 'pending',
            ]
        );

        // Step 3: Fetch Details with attributes
        if (!method_exists($header, 'items')) {
            throw new \Exception("Model does not have 'items' relationship defined.");
        }

        $details = $header->items()->with('attributes')->get();

        // Step 3: Loop through each detail and create unique item codes
        foreach ($details as $detail) {
            $detalNamespace = get_class($detail);
            $this->generateUniqueQRCodes($header, $job, $detalNamespace, $detail);
        }

    }

    private function generateUniqueQRCodes($header, $job, $namespace, $detail)
    {
        $qty = intval($detail->accepted_qty);
        $attributes = $this->getAttributes($detail);
        $itemUid = $this->generateUniqueItemUid(); // safe UID

        // Check if this is MrnDetail and has gate_entry_detail_id
        if ($namespace === \App\Models\MrnDetail::class && isset($detail->gate_entry_detail_id) && $detail->gate_entry_detail_id) {
            $this->copyQRCodesFromGateEntryDetail($detail, $header, $job, $namespace, $attributes, $qty, $itemUid);
            return; // exit after copying
        }

        // ❗ Fresh creation logic (same as before)
        $existingCount = ErpItemUniqueCode::where('job_id', $job->id)
            ->where('item_id', $detail->id)
            ->count();
        if ($qty > $existingCount) {
            $diff = $qty - $existingCount;
            $records = [];

            for ($i = 0; $i < $diff; $i++) {
                $records[] = [
                    'uid' => $this->generateUniqueUid(),
                    'job_id' => $job->id,
                    'organization_id' => $header->organization_id,
                    'group_id' => $header->group_id,
                    'company_id' => $header->company_id,
                    'morphable_type' => $namespace,
                    'morphable_id' => $detail->id,
                    'doc_type' => $header->doc_number_type ?? null,
                    'doc_no' => $header->document_number ?? null,
                    'doc_date' => $header->document_date ?? null,
                    'book_id' => $header->book_id ?? null,
                    'store_id' => $header->store_id ?? null,
                    'book_code' => $header->book_code ?? null,
                    'item_attributes' => json_encode($attributes),
                    'item_id' => $detail->item_id,
                    'item_name' => $detail->item->item_name,
                    'item_code' => $detail->item_code,
                    'vendor_id' => $header->vendor_id,
                    'item_uid' => $itemUid,
                    'type' => 'qr',
                    'qty' => 1,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($records, 500) as $chunk) {
                ErpItemUniqueCode::insert($chunk);
            }

            // ErpItemUniqueCode::insert($records);

        } elseif ($qty < $existingCount) {
            $diff = $existingCount - $qty;

            ErpItemUniqueCode::where('job_id', $job->id)
                ->where('item_id', $detail->id)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->limit($diff)
                ->delete();
        }
    }

    private function copyQRCodesFromGateEntryDetail($detail, $header, $job, $namespace, $attributes, $qty, $itemUid)
    {
        $gateDetailId = $detail->gate_entry_detail_id;
        $existingGateQRCodes = ErpItemUniqueCode::where('morphable_type', GateEntryDetail::class)
            ->where('morphable_id', $gateDetailId)
            ->where('status', CommonHelper::SCANNED)
            ->limit($qty)
            ->get();

        $records = [];
        foreach ($existingGateQRCodes as $code) {
            $records[] = [
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $namespace,
                'morphable_id' => $detail->id,
                'doc_type' => $header->doc_number_type ?? null,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $header->store_id ?? null,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($attributes),
                'item_id' => $detail->item_id,
                'item_name' => $detail->item->item_name,
                'item_code' => $detail->item_code,
                'vendor_id' => $header->vendor_id,
                'item_uid' => $itemUid, 
                'utilized_id' => $code->uid,
                'type' => 'qr',
                'qty' => 1,
                'status' => CommonHelper::PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            foreach (array_chunk($records, 500) as $chunk) {
                ErpItemUniqueCode::insert($chunk);
            }
        }
    }

    private function getAttributes($detail){
        
        $attributeJsonArray = [];
        if(isset($detail->attributes) && !empty($detail->attributes)){
            foreach($detail->attributes as $key1 => $attribute) {
                $attributeJsonArray[] = [
                    "attr_name" => (string)$attribute->attr_name,
                    "attribute_name" => (string)@$attribute->attributeName->name,
                    "attr_value" => (string)@$attribute->attr_value,
                    "attribute_value" => (string)@$attribute->attributeValue->value,
                ];
            }
        }

        return $attributeJsonArray;
    }

    private function generateUniqueItemUid($length = 15)
    {
        do {
            $raw = str_replace('-', '', Str::uuid()); // 32-character hex
            $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        } while (ErpItemUniqueCode::where('item_uid', $uid)->exists());

        return $uid;
    }

    private function generateUniqueUid($length = 15)
    {
        do {
            $raw = str_replace('-', '', Str::uuid()); // 32-character hex
            $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        } while (ErpItemUniqueCode::where('uid', $uid)->exists());

        return $uid;
    }

}