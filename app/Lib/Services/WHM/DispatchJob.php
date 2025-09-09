<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class DispatchJob
{

    public function createJob($id, $namespace, $jobType = null, $subStoreType = null)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);

        $type = $jobType ?? CommonHelper::getJobType($namespace);
        $trnstype = CommonHelper::getJobTransactionType($namespace);

        // Step 2: Get or Create Job (prevents duplicate job on edit)
        $job = (new WhmJob())->createJob($header, $namespace, $type, $trnstype, $header->store_id, $header->sub_store_id, NULL, NULL, NULL);

    }

    private function generateUniqueUid($length = 15)
    {
        $raw = str_replace('-', '', Str::uuid()); // 15-character hex
        $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        return $uid;
    }

    public function scanQRCodes($header, $jobId, $packets, $userId, $invoiceItems, $namespace)
    {
        
        foreach ($packets as $packet) {
            $attributes = $packet->item_attributes ?? [];

            $newRecord = ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $jobId,
                'organization_id' => $packet->organization_id,
                'group_id' => $packet->group_id,
                'company_id' => $packet->company_id,
                'morphable_type' => $namespace,
                'morphable_id' => $invoiceItems[$packet->morphable_id],
                'job_type' => CommonHelper::DISPATCH,
                'trns_type' => ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS,
                'doc_type' => CommonHelper::ISSUE,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $header->store_id ?? null,
                'sub_store_id' => $header->sub_store_id ?? null,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($attributes),
                'item_id' => $packet->item_id,
                'item_name' => $packet->item->item_name,
                'item_code' => $packet->item_code,
                'vendor_id' => $packet->vendor_id,
                'batch_id' => $packet->batch_id,
                'batch_number' => $packet->batch_number,
                'manufacturing_year' => $packet->manufacturing_year,
                'expiry_date' => $packet->expiry_date,
                'serial_no' => $packet->serial_no,
                'item_uid' => $packet->item_uid, 
                'storage_point_id' => Null, 
                'type' => 'qr',
                'qty' => 1,
                'status' => CommonHelper::SCANNED,
                'created_at' => now(),
                'updated_at' => now(),
                'action_by' => $userId,
                'action_at' => now()
            ]);

            $packet->utilized_id = $newRecord->uid;
            $packet->save();
        }
    }
}