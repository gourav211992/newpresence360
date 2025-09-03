<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class UnloadingJob
{
    public function createJob($id, $namespace)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);
        
        $type = $jobType ?? CommonHelper::getJobType($namespace);
        $trnstype = CommonHelper::getJobTransactionType($namespace);

        // Step 2: Get or Create Job (prevents duplicate job on edit)
        $job = ErpWhmJob::firstOrCreate(
            [
                'morphable_type' => $namespace,
                'morphable_id' => $header->id,
                'type' => $type,
            ],
            [
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'status' => CommonHelper::PENDING,
                'trns_type' => $trnstype,
                'store_id' => $header->store_id ?? null,
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
            $this->generateUniqueQRCodes($header, $job, $detalNamespace, $detail, $type, $trnstype);
        }

    }

    private function generateUniqueQRCodes($header, $job, $namespace, $detail, $type, $trnstype)
    {
        $attributes = $this->getAttributes($detail);
        $qty = intval($detail->inventory_uom_qty);

        // ❗ Fresh creation logic (same as before)
        $existingCount = $detail->uniqueCodes()
            ->where('job_id', $job->id)
            ->count();

        if ($qty > $existingCount) {
            $diff = $qty - $existingCount;
            $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $diff);
        } elseif ($qty < $existingCount) {
            $diff = $existingCount - $qty;

            ErpItemUniqueCode::where('job_id', $job->id)
                ->where('item_id', $detail->item_id)
                ->where('morphable_type', $namespace)
                ->where('morphable_id', $detail->id)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->limit($diff)
                ->delete();
        }
    }

    private function createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $qty){
        $records = [];

        for ($i = 0; $i < $qty; $i++) {
            $records[] = [
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $namespace,
                'morphable_id' => $detail->id,
                'job_type' => $type,
                'trns_type' => $trnstype,
                'doc_type' => CommonHelper::RECEIPT,
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
                'item_uid' => $this->generateUniqueUid(),
                'type' => 'qr',
                'qty' => 1,
                'status' => CommonHelper::PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($records, 500) as $chunk) {
            ErpItemUniqueCode::insert($chunk);
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

    private function generateUniqueUid($length = 15)
    {
        $raw = str_replace('-', '', Str::uuid()); // 15-character hex
        $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        return $uid;
    }
}