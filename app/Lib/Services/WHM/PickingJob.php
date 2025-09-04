<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Models\InspectionDetail;
use App\Models\MrnDetail;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class PickingJob
{
    protected $referenceNo;  
    protected $referenceHeader; 
    protected $storeId; 
    protected $subStoreId; 

    public function createJob($id, $namespace, $jobType = null, $subStoreType = null)
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
                'store_id' => isset($header->store_id) ? $header->store_id : NULL,
                'sub_store_id' => isset($header->main_sub_store_id) ? $header->main_sub_store_id : NULL,
            ]
        );
    }
    
    private function generateUniqueUid($length = 15)
    {
        $raw = str_replace('-', '', Str::uuid()); // 15-character hex
        $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        return $uid;
    }

    public function scanQRCodes($detail, $header, $jobId, $packetIds, $storagePointId, $userId, $jobType, $trnstype)
    {
        $attributes = $detail->attributes;

        $packets = ErpItemUniqueCode::whereIn('item_uid', $packetIds)
            // ->where('storage_point_id',$storagePointId)
            ->when($storagePointId, function ($query) use ($storagePointId) {
                $query->where('storage_point_id', $storagePointId);
            })
            ->whereNull('utilized_id')
            ->whereIn('trns_type', $trnstype)
            ->where('status', CommonHelper::SCANNED)
            ->get();

        $namespace = get_class($detail);
        $storeId = $header->store_id;
        $subStoreId = $header->main_sub_store_id;
        
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
                'trns_type' => ConstantHelper::PL_SERVICE_ALIAS,
                'doc_type' => CommonHelper::ISSUE,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' =>$storeId ?? null,
                'sub_store_id' => $subStoreId ?? null,
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
                'action_by' => $userId,
                'action_at' => now()
            ]);

            $packet->utilized_id = $newRecord->uid;
            $packet->save();
        }
    }

    public function generateQRCodes($subStoreId,$job)
    {
        $packets = $job->itemUniqueCodes()
            ->where('status', CommonHelper::SCANNED)
            ->where('doc_type', CommonHelper::ISSUE)
            ->whereNull('utilized_id')
            ->get();

        if($packets->isNotEmpty()){
            foreach ($packets as $packet) {
                $newRecord = ErpItemUniqueCode::create([
                    'uid' => $this->generateUniqueUid(),
                    'job_id' => $packet->job_id,
                    'organization_id' => $packet->organization_id,
                    'group_id' => $packet->group_id,
                    'company_id' => $packet->company_id,
                    'morphable_type' => $packet->morphable_type,
                    'morphable_id' => $packet->morphable_id,
                    'job_type' => $packet->job_type,
                    'trns_type' => ConstantHelper::PL_SERVICE_ALIAS,
                    'doc_type' => CommonHelper::RECEIPT,
                    'doc_no' => $packet->doc_no ?? null,
                    'doc_date' => $packet->doc_date ?? null,
                    'book_id' => $packet->book_id ?? null,
                    'store_id' => $packet->store_id ?? null,
                    'sub_store_id' => $subStoreId ?? null,
                    'book_code' => $packet->book_code ?? null,
                    'item_attributes' => json_encode($packet->item_attributes),
                    'item_id' => $packet->item_id,
                    'item_name' => $packet->item_name,
                    'item_code' => $packet->item_code,
                    'vendor_id' => $packet->vendor_id,
                    'item_uid' => $packet->item_uid, 
                    'storage_point_id' => Null, 
                    'type' => 'qr',
                    'qty' => 1,
                    'status' => CommonHelper::SCANNED,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'action_by' => $packet->action_by,
                    'action_at' => now()
                ]);
    
                $packet->utilized_id = $newRecord->uid;
                $packet->save();
            }
        }
    }
}