<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Models\MrnDetail;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class WhmJob
{
    public function createJob($id, $namespace)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);

        // ✅ Conditionally skip MRN headers with no is_inspection = 0
        if ($namespace === \App\Models\MrnHeader::class) {
            $hasInspectionItems = $header->items()->where('is_inspection', 0)->exists();
            if (!$hasInspectionItems) {
                return; // ⛔ No job creation
            }
        }

        $type = CommonHelper::getJobType($namespace);

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
                'type' => $type,
            ]
        );

        // ❗ Skip unique code generation if it's ErpPlHeader
        if ($namespace === \App\Models\ErpPlHeader::class) {
            return;
        }

        // Step 3: Fetch Details with attributes
        if (!method_exists($header, 'items')) {
            throw new \Exception("Model does not have 'items' relationship defined.");
        }

        $detailsQuery = $header->items()->with('attributes');
        
        if ($namespace === \App\Models\MrnHeader::class) {
            $detailsQuery->where('is_inspection', 0);
        }

        $details = $detailsQuery->get();

        // Step 3: Loop through each detail and create unique item codes
        foreach ($details as $detail) {
            $detalNamespace = get_class($detail);
            $this->generateUniqueQRCodes($header, $job, $detalNamespace, $detail, $type);
        }

    }

    private function generateUniqueQRCodes($header, $job, $namespace, $detail, $type)
    {
        $attributes = $this->getAttributes($detail);

        // Check if this is ErpInvoiceItem and has pl_item_id
        if ($namespace === \App\Models\ErpInvoiceItem::class && isset($detail->pl_item_id) && $detail->pl_item_id) {
            $qty = intval($detail->order_qty);
            $existingQRCodes = $this->getPickingQr($detail->plItem, $qty);
            $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::ISSUE);
            return; // exit after copying
        }

        $qty = intval($detail->accepted_qty);

        // Check if this is MrnDetail and has gate_entry_detail_id
        if ($namespace === \App\Models\MrnDetail::class && isset($detail->gate_entry_detail_id) && $detail->gate_entry_detail_id) {
            $existingQRCodes = $this->getUnloadingQr($detail->geItem, $qty);
            $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT);
            return; // exit after copying
        }

        // Check if this is InspectionDetail and has mrn_header_id
        if ($namespace === \App\Models\InspectionDetail::class && isset($detail->mrn_detail_id) && $detail->mrn_detail_id) {
            $mrnDetail = MrnDetail::find($detail->mrn_detail_id);
            if (isset($mrnDetail->gate_entry_detail_id) && $mrnDetail->gate_entry_detail_id) {
                $existingQRCodes = $this->getUnloadingQr($detail->geItem, $qty);
                $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT);
                return; // exit after copying
            }
        }

        // ❗ Fresh creation logic (same as before)
        $existingCount = $detail->uniqueCodes()->where('job_id', $job->id)
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
                    'job_type' => $type,
                    'doc_type' => CommonHelper::RECEIPT,
                    'doc_no' => $header->document_number ?? null,
                    'doc_date' => $header->document_date ?? null,
                    'book_id' => $header->book_id ?? null,
                    'store_id' => $header->store_id ?? null,
                    'sub_store_id' => isset($header->sub_store_id) ? $header->sub_store_id : null,
                    'book_code' => $header->book_code ?? null,
                    'item_attributes' => json_encode($attributes),
                    'item_id' => $detail->item_id,
                    'item_name' => $detail->item->item_name,
                    'item_code' => $detail->item_code,
                    'vendor_id' => $header->vendor_id,
                    'item_uid' => $this->generateUniqueUid(),
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
                ->where('item_id', $detail->item_id)
                ->where('morphable_type', $namespace)
                ->where('morphable_id', $detail->id)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->limit($diff)
                ->delete();
        }
    }

    private function getUnloadingQr($geDetail, $qty)
    {
        $existingGateQRCodes = $geDetail->uniqueCodes()->where('status', CommonHelper::SCANNED)
            ->limit($qty)
            ->get();

        return $existingGateQRCodes;
    }

    private function getPickingQr($plItem, $qty)
    {
        $existingQRCodes = $plItem->uniqueCodes()->where('status', CommonHelper::SCANNED)
            ->limit($qty)
            ->get();

        return $existingQRCodes;
    }

    private function copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, $status, $docType = CommonHelper::RECEIPT){
        foreach ($existingQRCodes as $code) {
            $newRecord = ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $namespace,
                'morphable_id' => $detail->id,
                'job_type' => $type,
                'doc_type' => $docType,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $header->store_id ?? null,
                'sub_store_id' => $header->sub_store_id ?? null,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($attributes),
                'item_id' => $detail->item_id,
                'item_name' => $detail->item->item_name,
                'item_code' => $detail->item_code,
                'vendor_id' => isset($header->vendor_id) ? $header->vendor_id : NULL,
                'item_uid' => $code->item_uid, 
                'type' => 'qr',
                'qty' => 1,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $code->utilized_id = $newRecord->uid;
            $code->save();
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

    public function generateQRCodesForPickList($detail, $header, $jobId, $packetIds, $storagePointId, $user, $jobType)
    {
        $attributes = $detail->attributes;

        $packets = ErpItemUniqueCode::whereIn('item_uid', $packetIds)
            ->where('storage_point_id',$storagePointId)
            ->whereNull('utilized_id')
            ->where('job_type', CommonHelper::PUTAWAY)
            ->where('status', CommonHelper::SCANNED)
            ->get();

        $namespace = get_class($detail);

        foreach ($packets as $packet) {
            $newRecord = ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $jobId,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $namespace,
                'morphable_id' => $detail->id,
                'job_type' => $jobType,
                'doc_type' => CommonHelper::RECEIPT,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $header->store_id ?? null,
                'sub_store_id' => $header->staging_sub_store_id ?? null,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($attributes),
                'item_id' => $detail->item_id,
                'item_name' => $detail->item->item_name,
                'item_code' => $detail->item_code,
                'vendor_id' => $header->vendor_id,
                'item_uid' => $packet->item_uid, 
                'storage_point_id' => Null, 
                'type' => 'qr',
                'qty' => 1,
                'status' => CommonHelper::SCANNED,
                'created_at' => now(),
                'updated_at' => now(),
                'action_by' => $user->id,
                'action_at' => now()
            ]);

            $packet->utilized_id = $newRecord->uid;
            $packet->save();
        }
    }

}